<?php

include('inc/init.php');

$user_id = $_POST['user_id'];
$logged_user = $_POST['logged_user'];
$user_log_date = ($_POST['user_log_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['user_log_date']))):'';
$user_log_details = $_POST['user_log_details'];

mysql_query("
	INSERT INTO 
	`user_log`(
		`date`,
		`details`,
		`staff_id`,
		`added_by`
	)
	VALUES(
		'".mysql_real_escape_string($user_log_date)."',
		'".mysql_real_escape_string($user_log_details)."',
		'".mysql_real_escape_string($user_id)."',
		'".mysql_real_escape_string($logged_user)."'
	)
");

header("location: /sats_users_details.php?id={$user_id}&success=3");

?>