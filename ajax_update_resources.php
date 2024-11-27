<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$resources_id = $_POST['resources_id'];
$title = $_POST['title'];
$heading = $_POST['heading'];
$state = $_POST['state'];
$due_date = mysql_real_escape_string($_POST['due_date']);
$due_date2 = ($crm->isDateNotEmpty($due_date)==true)?"'".$crm->formatDate($due_date)."'":'NULL';

$state2 = implode(",",$state);

// vehicles
mysql_query("
	UPDATE `resources`
	SET
		`title` = '".mysql_real_escape_string($title)."',
		`resources_header_id` = '".mysql_real_escape_string($heading)."',		
		`states` = '".$state2."',
		`date` = '".date("Y-m-d H:i:s")."',
		`due_date` = {$due_date2}
	WHERE `resources_id` = {$resources_id}
");


?>