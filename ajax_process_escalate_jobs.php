<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);

// clear escalate job reason
mysql_query("
	DELETE
	FROM `selected_escalate_job_reasons`
	WHERE `job_id` = {$job_id}
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
		'Escalate Job',
		'" . date('Y-m-d') . "',
		'Tenant Details updated', 
		'{$job_id}',
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date("H:i")."'
	)
");


// update status = to be booked
mysql_query("
	UPDATE jobs
	SET `status` = 'To Be Booked'
	WHERE `id` = {$job_id}
");

?>