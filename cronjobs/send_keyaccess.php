<?

include('server_hardcoded_values.php');

$country_id = 1;
$email_cc = KEYS_EMAIL;

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
define("CRON_TYPE_ID", 3);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));

#error_reporting(E_ALL);
$data = array();
$sendcount = 0;
$day = date('D');
#$day = 'Fri';

# Determine if cron has already been started?

$query = "SELECT log_id FROM cron_log WHERE type_id = '" . CRON_TYPE_ID . "' AND week_no = '" . CURR_WEEK . "' AND year = '" . CURR_YEAR . "' AND CAST(`started` AS DATE) = '".date('Y-m-d')."' AND `country_id` = {$country_id}";
$result = mysqlSingleRow($query);

$todays_day = date("l");

if( !is_numeric($result['log_id']) && $todays_day!='Saturday' && $todays_day!='Sunday' )
{


	# Start It - note not logging for now since it's a manual process
	mysql_query("INSERT INTO cron_log (type_id, week_no, year, started, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), {$country_id})");
	//$query = "INSERT INTO cron_log (type_id, week_no, year, started) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW())";
	#mysql_query($query) or die(mysql_error());
	$log_id = mysql_insert_id();
	
	# tomorrows date in required format
	$tomorrow = date('Y-m-d', (strtotime('+1 days')));
	
	$dates_formatted = date('d/m/Y', (strtotime('+1 days')));
	if($day == 'Fri') $dates_formatted .= ' and ' . date('d/m/Y', (strtotime('+3 days')));

	# Get emails to send
	// improved the join query - JC, also added deleted check
	$query = "
		SELECT j.id, a.agency_emails, a.agency_name, j.time_of_day,  j.date, 
		DATE_FORMAT(j.date, '%d/%m/%Y') AS date_formatted, p.address_1, p.address_2, 
		p.address_3, j.`date` AS jdate, j.`assigned_tech` AS jtech_id, j.`key_access_details`, 
		p.`key_number`, a.`agency_id`, pm.`name` AS pm_name, p.`pm_id_new` 
		FROM jobs AS j
		LEFT JOIN property AS p ON j.property_id = p.`property_id`
		LEFT JOIN property_managers AS pm ON p.`property_managers_id` = pm.`property_managers_id`
		LEFT JOIN agency AS a ON p.agency_id = a.agency_id
		WHERE j.`key_access_required` = 1
		AND j.status = 'Booked'
		AND a.agency_emails LIKE '%@%'
		AND a.`country_id` = {$country_id}
		AND j.`del_job` = 0
		AND p.`deleted` = 0
		AND a.`status` = 'active'	
	";

	

	if($day == 'Fri')
	{
		# Also get mondays keys
		$query .= " AND (j.date = '{$tomorrow}' OR j.date = '" . date('Y-m-d', (strtotime('+3 days'))) . "') ";
	}
	else
	{
		$query .= " AND j.date = '{$tomorrow}' ";
	}


	$query .= " ORDER BY a.agency_name ASC, j.date ASC, j.`sort_order` ASC  ";
	
	$clients_to_email = mysqlMultiRows($query);

	# If 


	# Put data into format for sending function... array[<agency name>][<properties>][x][<property details>]

	if(is_array($clients_to_email))
	{

		foreach($clients_to_email as $record)
		{
			
			// sanitze email
			unset($to_emails);
			$to_emails = array();
			$temp = explode("\n",trim($record['agency_emails']));
			foreach($temp as $val){
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$to_emails[] = $val2;
				}				
			}
			$to_imp = mysql_real_escape_string(implode(", ",$to_emails));
			
			$data[$record['agency_name']]['agency_id'] = $record['agency_id'];
			$data[$record['agency_name']]['agency_name'] = $record['agency_name'];
			$data[$record['agency_name']]['agency_emails'] = $to_emails;
			$data[$record['agency_name']]['jdate'] = $record['jdate'];
			$data[$record['agency_name']]['properties'][] = array(	
																	'date' => $record['date_formatted'],
																	'job_id' => $record['id'],
																	'time_of_day' => $record['time_of_day'],
																	'address_1' => $record['address_1'],
																	'address_2' => $record['address_2'],
																	'address_3' => $record['address_3'],
																	'jdate' => $record['jdate'],
																	'jtech_id' => $record['jtech_id'],
																	'key_access_details' => $record['key_access_details'],
																	'key_number' => $record['key_number'],
																	'pm_name' => $record['pm_name'],
																	'pm_id_new' => $record['pm_id_new']
																	);
		}

		# now send the emails
		$sent_to_imp = '';
		foreach($data as $packet)
		{
			
			$sent_to_imp = implode(", ",$packet['agency_emails']);
			
			if(!sendKeyAccessEmail($packet, $dates_formatted, $country_id, $email_cc))
			{
				mail(DEBUG_EMAIL, "Cron Error", time() . " " . $client['agency_emails']);
			}
			else
			{
				$sendcount++;
			}

			# Add Job Logs
			foreach($packet['properties'] as $property)
			{
				$insertQuery = "INSERT INTO job_log ( contact_type,eventdate,comments,job_id, staff_id, `eventtime`, `auto_process` ) VALUES ( 'Auto Email','" . date('Y-m-d') . "','Key access email for " . $dates_formatted . " sent to: <strong>{$sent_to_imp}</strong> " . "','" . $property['job_id'] . "','SATS', '".date("H:i")."', 1 );";
				mysql_query($insertQuery);
			}
		}

		echo "Sent {$sendcount} emails";
		//exit();
	}
	else
	{
		echo "No emails to send";
		//exit();
	}
	
	
	// # Finish off
	$query = "UPDATE cron_log SET finished = NOW() WHERE log_id = '" .$log_id . "' AND `country_id` = {$country_id}";
	mysql_query($query) or die(mysql_error());
	exit();

}
else
{
	echo "Cron job has already ran this week";
	exit();
}



?>
