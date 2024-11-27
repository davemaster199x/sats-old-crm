<?php
include('inc/init.php');

$job_id = mysql_real_escape_string($_POST['job_number']);
$staff = mysql_real_escape_string($_POST['staff']);
$amount = mysql_real_escape_string($_POST['amount']);
$agency = mysql_real_escape_string($_POST['agency']);
$reason = mysql_real_escape_string($_POST['reason']);
$country_id = $_SESSION['country_default'];	

$sql = "
	INSERT INTO
	`credit_requests` (
		`job_id`,
		`date_of_request`,
		`requested_by`,
		`reason`,
		`country_id`,
		`deleted`,
		`active`
	)
	VALUES (
		{$job_id},
		'".date('Y-m-d H:i:s')."',
		{$staff},
		'{$reason}',
		{$country_id},
		0,
		1
	)
";
mysql_query($sql);


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
		'Credit Request',
		'".date('Y-m-d')."',
		'Credit request for <strong>{$amount}</strong>',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
");	


header("location:create_credit_request.php?success=1");

?>