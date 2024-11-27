<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$unpaid = mysql_real_escape_string($_POST['unpaid']);

$sql_str = "
	UPDATE `jobs` 
	SET
		`unpaid` = '{$unpaid}'
	WHERE `id` = {$job_id}
";
mysql_query($sql_str);

//insert account job log
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$job_log_type = 2; // job accounts log
$log_comment = ($unpaid==1) ? 'Ticked unpaid checkbox' : 'Unticked unpaid checkbox' ;
$al_time = date('H:i');
$al_time_full = date('H:i:s');
$sql = "
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`comments`,
		`job_id`, 
		`staff_id`,
		`eventtime`,
		`log_type`,
		`created_date`
	) 
	VALUES (
		'Paid/Unpaid',
		'".date('Y-m-d')."',
		'{$log_comment}',
		{$job_id}, 
		'{$logged_user}',
		'{$al_time}',
		{$job_log_type},
		'".date("Y-m-d {$al_time_full}")."'
	)
";
mysql_query($sql);

//header("location: /figures.php?success=1");

?>