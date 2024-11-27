<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$country_id = 2; // country ID !!! UPDATE IMPORTANT

// cron variables
$cron_type_id = 15; 
$current_week = intval(date('W'));
$current_year = date('Y');

$last_31_days = date('Y-m-d',strtotime('-31 days'));

echo $sql_str = "
	DELETE
	FROM `agency_tracking`
	WHERE CAST( `logged_in_datetime` AS Date ) < '{$last_31_days}'
";
mysql_query($sql_str);
$first_cron = mysql_affected_rows();

if( $first_cron >0 ){
	
	// insert cron logs
	echo $cron_log = "INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
	mysql_query($cron_log);
	
}


?>


