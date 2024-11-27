<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$time_of_day = mysql_real_escape_string($_POST['time_of_day']);

mysql_query("
	UPDATE `jobs`
	SET 
		`time_of_day` = '{$time_of_day}'
	WHERE `id` = {$job_id}
");

?>