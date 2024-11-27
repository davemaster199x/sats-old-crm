<?

include('server_hardcoded_values.php');

$country_id = 2;
$email_cc = 'reports@sats.co.nz';

# add trailing slash
if ($_SERVER['DOCUMENT_ROOT'][strlen($_SERVER['DOCUMENT_ROOT'])-1] != "/") $_SERVER['DOCUMENT_ROOT'] .= "/";

# include files
include($_SERVER['DOCUMENT_ROOT'] . 'inc/config.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/encryption.class.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/functions.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/email_functions.php');
include($_SERVER['DOCUMENT_ROOT'] . 'inc/swiftmailer/lib/swift_required.php');

# Connect to Database
$connection = mysql_connect(HOST, USERNAME, PASSWORD) or die("Could not connect to database:" . mysql_error());
mysql_select_db(DATABASE, $connection) or die("Unable to Select database");

define("IS_CRON", 1);
define("CRON_TYPE_ID", 2);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));

#error_reporting(E_ALL);

# Determine if cron has already been started?
$query = "SELECT log_id FROM cron_log WHERE type_id = '" . CRON_TYPE_ID . "' AND week_no = '" . CURR_WEEK . "' AND year = '" . CURR_YEAR . "' AND `country_id` = {$country_id}";
$result = mysqlSingleRow($query);

if(!is_numeric($result['log_id']))
{
	# Start IT
	$query = "INSERT INTO cron_log (type_id, week_no, year, started, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), {$country_id})";
	mysql_query($query) or die(mysql_error());
	$log_id = mysql_insert_id();
	
	$now = date("Y-m-d");
	
	# List of jobs completed
	$report_query = "
		(
			SELECT a.agency_id, a.agency_emails, a.agency_name ,j.id, j.date, NULL AS jl_date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, '0' AS NumLogs, '1' AS ReportType
			FROM jobs j, property p, agency a 
			WHERE j.status IN ('Completed', 'Merged Certificates')
			AND j.property_id = p.property_id
			AND p.agency_id  = a.agency_id
			AND j.`date` BETWEEN '".date('Y-m-d',strtotime("-7 day"))."' AND '".$now."'
			AND a.status = 'active'			
			AND a.agency_emails LIKE '%@%'
			AND a.`country_id` = {$country_id}
			ORDER BY agency_id, id ASC
		)
		UNION
		(
			
			SELECT a.agency_id, a.agency_emails, a.agency_name, j.id, j.date, NULL AS jl_date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, '0' AS NumLogs, '2' AS ReportType
			FROM jobs j, property p, agency a 
			WHERE j.status IN ('Booked')
			AND j.property_id = p.property_id
			AND p.agency_id  = a.agency_id
			AND j.`date` BETWEEN '".$now."' AND '".date('Y-m-d',strtotime("+7 day"))."'
			AND a.status = 'active'			
			AND a.agency_emails LIKE '%@%'
			AND a.`country_id` = {$country_id}
			ORDER BY agency_id, id ASC
		)
		ORDER BY agency_id, ReportType, `date` ASC";
		
	$clients_to_email = mysqlMultiRows($report_query);
	
	# We are going to put the data in a better format
	
	$curr_agencyid = 0;
	$report_tree = array();
	
	
	if(is_array($clients_to_email))
	{
		foreach($clients_to_email as $row)
		{
			if($row['agency_id'] != $curr_agencyid)
			{
				$curr_agencyid = $row['agency_id'];
				# Define new node - sub nodes = 1: Completed, 2: Booked, 3: Problematic
				$report_tree[$curr_agencyid] = array('1' => array(), '2' => array(), '3' => array());
				
				# Add emails to node too
				$report_tree[$curr_agencyid]['emails'] = $row['agency_emails'];
			}
	
			$report_tree[$curr_agencyid][$row['ReportType']][] = array(
				"Address" => $row['address_1'] . " " . $row['address_2'],
				"Suburb" => $row['address_3'],
				"State" => $row['state'],
				"Postcode" => $row['postcode'],
				"Date" => $row['date'],
				"jl_date" => $row['jl_date']
			);
			
		}
		
		# Now go through and send the email
		
		foreach($report_tree as $agency_id => $nodes)
		{
			/*
			$to_emails = explode("\n", $nodes['emails']);
			#$to_emails = "adam@4mation.com.au";
			*/
			
			// filter valid email, only save that are valid
			unset($to_emails);
			$to_emails = array();
			$temp = explode("\n",trim($nodes['emails']));
			foreach($temp as $val){
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$to_emails[] = $val2;
				}				
			}
			
			if(!sendReportEmail($nodes, $to_emails, $country_id, $email_cc))
			{
				# send debug info
				mail(DEBUG_EMAIL, "Cron Error", time() . " " . $client['agency_emails']);
			}
		}
		
		
	}
	else
	{
		echo "Nothing to do";
	}

	/*
	# Additional functions.. add a log to the comments for any job that is marked as problematic
	
	$query = "
		SELECT  a.agency_id, a.agency_emails, a.agency_name, j.id, j.date, jl.`eventdate` AS jl_date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, count(DISTINCT jl.eventdate) AS NumLogs, '3' AS ReportType 
		FROM job_log jl, jobs j, property p, agency a 
		WHERE jl.deleted = 0
		AND jl.job_id = j.id
		AND j.property_id = p.property_id
		AND p.agency_id = a.agency_id 
		AND jl.eventdate >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 14 DAY), '%Y-%m-%d') 
		AND jl.eventdate <= NOW()
		AND a.status = 'active'		
		AND a.agency_emails LIKE '%@%'
		GROUP BY jl.job_id
		ORDER BY jl.job_id ASC	
	";
					
	$problematic_jobs = mysqlMultiRows($query);
	
	if(is_array($problematic_jobs))
	{
		$prob_comment = "\n" . "Agency alerted to problematic property by Email - " . date('d/m/Y');	
		
		foreach($problematic_jobs as $job)
		{
			$query = "
			UPDATE jobs SET 
			comments = IF((comments IS NULL OR comments = \"\"), '" . $prob_comment . "', CONCAT(`comments`, '" . $prob_comment . "' ))
			WHERE id = '" . $job['id'] . "' LIMIT 1";
			
			#echo $query . "<br>";
			
			mysql_query($query) or die(mysql_error_no());
		}
	}
	*/	

	$query = "UPDATE cron_log SET finished = NOW() WHERE log_id = '" .$log_id . "' AND `country_id` = {$country_id}";
	mysql_query($query) or die(mysql_error());

}
else
{
	echo "Cron job has already ran this week";
}	

?>
