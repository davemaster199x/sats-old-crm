<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// cron variables
$cron_type_id = 16; // DELETE CRON LOGS
$current_week = intval(date('W'));
$current_year = date('Y');

$country_id = 2; // country ID !!! UPDATE IMPORTANT


$last_30_days = date('Y-m-d',strtotime('-30 days'));
echo "last 30 days: {$last_30_days}";
echo "<br />";

// Delete All SMS that are 30 days old or more except TY SMS
echo $sql_str = "
	DELETE 
	FROM `cron_log` 
	WHERE CAST( `started` AS Date ) < '{$last_30_days}'
	AND `country_id` = {$country_id}
";
mysql_query($sql_str);
echo "<br /><br />";
$first_cron = mysql_affected_rows();

if( $first_cron ){
	
	// insert cron logs
	echo $cron_log = "INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
	mysql_query($cron_log);
	
}


?>


