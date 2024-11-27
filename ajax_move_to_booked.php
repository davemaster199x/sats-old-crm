<?php
include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$curr_status = mysql_real_escape_string($_POST['curr_status']);
$to_status = 'Booked';

// update job to merged
mysql_query("
	UPDATE `jobs`
	SET 
		`status` = '{$to_status}',
		`precomp_jobs_moved_to_booked` = 1,
		`ts_completed` = NULL
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
	'Job status updated from <strong>{$curr_status}</strong> to <strong>{$to_status}</strong>', 
	'".date("Y-m-d")."', 
	'Moved to {$to_status}', 
	'{$job_id}',
	'".date("H:i")."'
	)
");
?>