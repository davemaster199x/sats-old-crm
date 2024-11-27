<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$ct_id = mysql_real_escape_string($_POST['ct_id']);

mysql_query("
	UPDATE `crm_tasks`
	SET 
		`active` = 0
	WHERE `crm_task_id` = {$ct_id}
");
?>