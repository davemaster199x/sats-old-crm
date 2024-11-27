<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$al_date = $crm->formatDate(mysql_real_escape_string(trim($_POST['al_date'])));
$al_comment = mysql_real_escape_string($_POST['al_comment']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log
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
		'Accounts Notes',
		'".date('Y-m-d',strtotime($al_date))."',
		'{$al_comment}',
		{$job_id}, 
		'{$logged_user}',
		'{$al_time}',
		{$job_log_type},
		'".date("Y-m-d {$al_time_full}",strtotime($al_date))."'
	)
";

// job log
mysql_query($sql);	


?>