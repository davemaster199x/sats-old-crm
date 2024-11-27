<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$dha_need_processing = $_POST['dha_need_processing'];
$main_prog = mysql_real_escape_string($_POST['main_prog']);

mysql_query("
	UPDATE `jobs`
	SET
		`dha_need_processing` = '".mysql_real_escape_string($dha_need_processing)."'
	WHERE `id` = '".mysql_real_escape_string($job_id)."'
");

// job log
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
		'Maintenance Program',
		'".date('Y-m-d')."',
		'{$main_prog} Processed',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
");	

?>