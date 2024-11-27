<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$job_price = $_POST['job_price'];
$price_reason = $_POST['price_reason'];
$price_detail = $_POST['price_detail'];
$staff_id = $_POST['staff_id'];

// update price
mysql_query("
	UPDATE `jobs`
	SET 
		`job_price` = ".mysql_real_escape_string($job_price).",
		`price_reason` = '".mysql_real_escape_string($price_reason)."',
		`price_detail` = '".mysql_real_escape_string($price_detail)."'
	WHERE `id` = ".$job_id."
");

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
		'New Price- $".$job_price.", Reason- ".$price_reason.", Details- ".$price_detail."', 
		'{$job_id}',
		'{$staff_id}',
		'".date("H:i")."'
	)
");

?>