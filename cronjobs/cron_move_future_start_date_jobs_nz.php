<?php

include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 2;

define("IS_CRON", 1);
define("CRON_TYPE_ID", 10);
define("CURR_WEEK", intval(date('W')));
define("CURR_YEAR", date('Y'));



echo $sql_str = "
SELECT *
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
WHERE j.`status` = 'To Be Booked'
AND CAST( j.`start_date` AS Date ) >= '".date('Y-m-d',strtotime('+ 5 days'))."'
AND j.`del_job` =0
AND p.`deleted` = 0
AND a.`status` = 'active'
AND a.`country_id` = {$country_id}
";
$sql = mysql_query($sql_str);

//echo "<br />";


while( $j = mysql_fetch_array($sql) ){
	
	$job_id = $j['id'];
	
	// update job to on hold
	$str2 = "
	UPDATE `jobs`
	SET `status` = 'On Hold'
	WHERE `id` = {$job_id}
	";
	mysql_query($str2);
	//echo "<br />";
	
	
	// insert job logs
	$jlog_str = "
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`, 
			`eventtime`,
			`auto_process`
		) 
		VALUES (
			'Auto Move',
			'" . date('Y-m-d') . "',
			'Job moved from <strong>To Be Booked</strong> to <strong>On Hold</strong>' , 
			'{$job_id}',
			'".date("H:i")."',
			1
		)
	";
	mysql_query($jlog_str);	
	//echo "<br />";
	
	
}
	



	
// insert cron log
// AU
$cron_log_str1 = "INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . CRON_TYPE_ID . "," . CURR_WEEK . ", " . CURR_YEAR . ", NOW(), NOW(), {$country_id})";
mysql_query($cron_log_str1);



?>

