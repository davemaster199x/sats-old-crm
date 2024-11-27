<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$au_id = mysql_real_escape_string($_POST['au_id']);

$sql = "
	UPDATE `agency_audits`
	SET 
		`active` = 0
	WHERE `agency_audit_id` = {$au_id}
";

mysql_query($sql);
?>