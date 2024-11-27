<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$ct_id = mysql_real_escape_string($_POST['ct_id']);
$page_link = mysql_real_escape_string($_POST['page_link']);
$describe_issue = mysql_real_escape_string($_POST['describe_issue']);
$response = mysql_real_escape_string($_POST['response']);
$status = mysql_real_escape_string($_POST['status']);

$update_str = ( $response != '' )?",`response` = '{$response}'":'';

mysql_query("
	UPDATE `crm_tasks`
	SET 
		`page_link` = '{$page_link}',
		`describe_issue` = '{$describe_issue}',
		`status` = '{$status}'
		{$update_str}
	WHERE `crm_task_id` = {$ct_id}
");

?>