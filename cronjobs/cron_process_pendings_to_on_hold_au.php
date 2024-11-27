<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 1; // country ID !!! UPDATE IMPORTANT
$staff_id = -1;

// cron variables
$cron_type_id = 18; // CRON Process Pending
$current_week = intval(date('W'));
$current_year = date('Y');

$jsql = mysql_query("
	SELECT j.`id` AS jid, j.`property_id`, ajt.`type` AS ajt_type
	FROM `jobs` AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id` 
	WHERE p.`deleted` =0 
	AND a.`status` = 'active' 
	AND j.`del_job` = 0 
	AND a.`country_id` = {$country_id}
	AND j.`status` = 'Pending' 
	AND a.`auto_renew` = 1
");

while( $j = mysql_fetch_array($jsql) ){
	
	$property_id = $j['property_id'];
	$job_id = $j['jid'];

	// updates to on hold
	mysql_query("
		UPDATE jobs 
		SET 
			status='On Hold', 
			auto_renew=2 
		WHERE `status` = 'Pending' 
		AND property_id = {$property_id} 
		AND `id` = {$job_id}
	");


	// add logs
	$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
	VALUES (".$property_id.", '{$staff_id}', '{$j['ajt_type']} Service Auto Renewed', 'By System', '".date('Y-m-d H:i:s')."')";

	mysql_query($insertLogQuery);



	// insert job logs
	mysql_query("
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`, 
			`staff_id`,
			`eventtime`,
			`auto_process`
		) 
		VALUES (
			'Service Due',
			'" . date('Y-m-d') . "',
			'Job Auto Created on ".date('d/m/Y')." by System', 
			'{$job_id}',
			'{$staff_id}',
			'".date("H:i")."',
			1
		)
	");
	
	
	
}


// insert cron logs
echo $cron_log = "INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
mysql_query($cron_log);


?>