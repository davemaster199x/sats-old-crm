<?php

include('inc/init.php');

$vehicles_id = $_POST['vehicles_id'];
$current_time = date("H:i:s");
$log_date = ($_POST['log_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['log_date']))):null;
$log_price = $_POST['log_price'];
$log_details = $_POST['log_details'];

mysql_query("
	INSERT INTO 
	`vehicles_log`(
		`vehicles_id`,
		`date`,
		`price`,
		`details`,
		`staff_id`
	)
	VALUES(
		'".mysql_real_escape_string($vehicles_id)."',
		'".mysql_real_escape_string($log_date)." {$current_time}',
		'".mysql_real_escape_string($log_price)."',
		'".mysql_real_escape_string($log_details)."',
		'{$_SESSION['USER_DETAILS']['StaffID']}'
	)
");

header("location: /view_vehicle_details.php?id={$vehicles_id}&success=3");

?>