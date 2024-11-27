<?

include('server_hardcoded_values.php');

$country_id = 2;
$email_cc = REPORTS_EMAIL;

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
define("CRON_TYPE_ID", 6);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));

$cl_sql = mysql_query("
	SELECT * 
	FROM cron_log 
	WHERE `type_id` = '".CRON_TYPE_ID."' 
	AND `week_no` = '".CURR_WEEK."' 
	AND `year` = '".CURR_YEAR."' 
	AND `country_id` = {$country_id}
");

if(mysql_num_rows($cl_sql)==0){

// except other, vacant and Short Term Rental
$esc_sql = mysql_query("
	SELECT DISTINCT (
		a.`agency_id`
	)
	FROM `selected_escalate_job_reasons` AS sejr
	LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
	LEFT JOIN `jobs` AS j ON sejr.`job_id` = j.`id`
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE j.status =  'Escalate'
	AND p.`deleted` =0
	AND a.`status` =  'active'
	AND j.`del_job` =0
	AND a.`country_id` ={$country_id}
	AND ejr.`active` = 1
	AND (
		sejr.`escalate_job_reasons_id` != 3 AND
		sejr.`escalate_job_reasons_id` != 4 AND
		sejr.`escalate_job_reasons_id` != 5 
	)
");

while( $esc = mysql_fetch_array($esc_sql) ){
	
	sendEscalatePropertiesToAgency($esc['agency_id'],$country_id);
	
}


mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), NOW(), {$country_id})");

}

?>
