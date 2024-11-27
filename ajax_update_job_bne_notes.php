<?php
include('inc/init.php');

//$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$bne_note = mysql_real_escape_string($_POST['bne_note']);

// update job to escalate
mysql_query("
	UPDATE `jobs`
	SET 
		`bne_to_call_notes` = '{$bne_note}'
	WHERE `id` = {$job_id}
");

?>