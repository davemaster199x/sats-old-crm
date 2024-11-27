<?php
include('inc/init.php');

//$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$access_note = mysql_real_escape_string($_POST['access_note']);

// update job to escalate
mysql_query("
	UPDATE `jobs`
	SET 
		`access_notes` = '{$access_note}'
	WHERE `id` = {$job_id}
");

?>