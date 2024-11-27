<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$job_price = $_POST['job_price'];
$price_reason = $_POST['price_reason'];
$price_detail = $_POST['price_detail'];
$staff_id = $_POST['staff_id'];
$alarm_job_type_id = $_POST['alarm_job_type_id'];
$property_id = $_POST['property_id'];
$orig_price = $_POST['orig_price'];

// update price
mysql_query("
	UPDATE `jobs`
	SET 
		`job_price` = ".mysql_real_escape_string($job_price).",
		`price_reason` = '".mysql_real_escape_string($price_reason)."',
		`price_detail` = '".mysql_real_escape_string($price_detail)."'
	WHERE `id` = ".$job_id."
");

//by Gherx > update also Property Services
if($alarm_job_type_id!="" && $property_id!=""){
    mysql_query("
	UPDATE `property_services`
	SET 
		`price` = ".mysql_real_escape_string($job_price)."
	WHERE `property_id` = ".$property_id." AND `alarm_job_type_id` = ".$alarm_job_type_id."
");
}

// insert logs
mysql_query("
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`comments`,
		`job_id`, 
		`staff_id`,
		`eventtime`
	) 
	VALUES (
		'Price Changed',
		'" . date('Y-m-d') . "',
		'Price changed from <strong>".$orig_price."</strong> to <strong>".$job_price."</strong>. Reason: <strong>".$price_reason."</strong>. Details: <strong>".mysql_real_escape_string($price_detail)."</strong>.',
		'{$job_id}',
		'{$staff_id}',
		'".date("H:i")."'
	)
");

	// insert property log
	mysql_query("
	INSERT INTO 
	property_event_log (
		property_id, 
		staff_id, 
		event_type, 
		event_details, 
		log_date
	) 
	VALUES (
		'{$property_id}',
		'{$staff_id}',
		'Price Changed', 
		'Price changed on VJD from <strong>".$orig_price."</strong> to <strong>".$job_price."</strong>. Reason: <strong>".$price_reason."</strong>. Details: <strong>".mysql_real_escape_string($price_detail)."</strong>.',
		'".date('Y-m-d H:i:s')."'
	)
	");
	

?>