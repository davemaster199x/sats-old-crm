<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// cron variables
$cron_type_id = 14; // DELETE SMS CRON
$current_week = intval(date('W'));
$current_year = date('Y');

$country_id = 1; // country ID !!! UPDATE IMPORTANT
$sms_type = 18; // TY SMS type



echo "Find 7 days old SMS, except TY SMS";
echo "<br />";

$seven_days_old = date('Y-m-d',strtotime('-7 days'));
echo "last 7 days: {$seven_days_old}";
echo "<br />";

// Delete All SMS that are 7 days old or more except TY SMS
echo $sql_str = "
	DELETE sas,sar
	FROM `sms_api_sent` AS sas 
	LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
	WHERE CAST( sas.`created_date` AS Date ) < '{$seven_days_old}'
	AND sas.`sms_type` != {$sms_type}
";
mysql_query($sql_str);
echo "<br /><br />";
$first_cron = mysql_affected_rows();


// Delete 1 year old TY SMS
echo "Find 1 year old TY SMS";
echo "<br />";

$one_year_old = date('Y-m-d',strtotime('-1 year'));
echo "One Year Old: {$one_year_old}";
echo "<br />";

echo $sql_str = "
	DELETE sas,sar
	FROM `sms_api_sent` AS sas 
	LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
	WHERE CAST( sas.`created_date` AS Date ) < '{$one_year_old}'
	AND sas.`sms_type` = {$sms_type}
";
mysql_query($sql_str);
echo "<br /><br />";
$second_cron = mysql_affected_rows();

if( $first_cron >0 || $second_cron >0 ){
	
	// insert cron logs
	echo $cron_log = "INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
	mysql_query($cron_log);
	
}


?>


