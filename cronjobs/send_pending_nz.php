<?

include('server_hardcoded_values.php');

$country_id = 2;
$email_cc = 'cc@sats.co.nz';

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
define("CRON_TYPE_ID", 1);
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
	
	# Get emails to send
	/*
	$query = "SELECT a.agency_id, a.agency_emails, COUNT(*) AS p_count
	FROM 
	property p, agency a, jobs j
	WHERE 
	p.agency_id = a.agency_id
	AND
	p.deleted = 0
	AND
	p.service = 1 
	AND	
	j.`status` = 'Pending'
	AND
	j.`job_type` = 'Yearly Maintenance'
	AND
	p.`property_id` = j.`property_id`
	AND a.status = 'active'	
	AND a.agency_emails LIKE '%@%'
	GROUP BY a.agency_id
	ORDER BY a.agency_id ASC";
	*/
	
	$query = "
		SELECT DISTINCT(a.agency_id), a.agency_emails, a.`auto_renew`, a.`propertyme_agency_id`
		FROM jobs AS j
		LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN agency AS a ON p.agency_id = a.agency_id
		WHERE j.`status` = 'Pending'
		AND a.status = 'active'		
		AND a.agency_emails LIKE '%@%'
		AND p.deleted = '0'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND a.`auto_renew` = 1
		ORDER BY a.agency_id ASC
	";
	
	$clients_to_email = mysqlMultiRows($query);
	
	if(is_array($clients_to_email))
	{
		
		foreach($clients_to_email as $client)
		{
			/*
			$to_emails = explode("\n", $client['agency_emails']);
			#$to_emails = "adam@4mation.com.au";
			*/
			
			unset($to_emails);
			$to_emails = array();
			$temp = explode("\n",trim($client['agency_emails']));
			foreach($temp as $val){
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$to_emails[] = $val2;
				}				
			}
			
			//if( $client['propertyme_agency_id'] == '' ){ // If propertyme_agency_id is present then do not send this email.
				
				if(!sendPendingEmail($client, $to_emails, $country_id, $email_cc))
				{
					# send debug info
					mail(DEBUG_EMAIL, "Cron Error", time() . " " . $client['agency_emails']);
				}
				
			//}
		}
	}
	else {
		echo "No emails to send";
	}
	
	# Finish off
	$query = "UPDATE cron_log SET finished = NOW() WHERE log_id = '" .$log_id . "' AND `country_id` = {$country_id}";
	mysql_query($query) or die(mysql_error());

}
else
{
	echo "Cron job has already ran this week";
}


?>
