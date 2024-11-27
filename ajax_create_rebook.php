<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$job_type = mysql_real_escape_string($_POST['job_type']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// update job
mysql_query("
	UPDATE `jobs`
	SET 
		`status` = 'To Be Booked',
		`date` = NULL,
		`time_of_day` = NULL,
		`assigned_tech` = NULL,
		`ts_completed` = 0,
		`ts_techconfirm` = NULL,
		`cw_techconfirm` = NULL,
		`ss_techconfirm` = NULL,
		`job_reason_id` = 0,
		`door_knock` = 0,
		`completed_timestamp` = NULL,
		`tech_notes` = NULL,
		`job_reason_comment` = NULL,
		`booked_with` = NULL,
		`booked_by` = NULL,
		`key_access_required` = 0,
		`key_access_details` = NULL,
		`call_before` = NULL,
		`call_before_txt` = NULL
	WHERE `id` = {$job_id}
");

// insert job log
mysql_query("
	INSERT INTO 
	job_log (
		`staff_id`, 
		`comments`, 
		`eventdate`, 
		`contact_type`, 
		`job_id`,
		`eventtime`
	) 
	VALUES (
		'{$staff_id}', 
		'Status Changed to To Be Booked', 
		'".date("Y-m-d")."', 
		'Rebook', 
		'{$job_id}',
		'".date("H:i")."'
	)
");

?>