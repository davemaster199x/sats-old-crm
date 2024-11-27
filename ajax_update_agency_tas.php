<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$tas_id = mysql_real_escape_string($_POST['tas_id']);

$sql = "
	UPDATE `agency`
	SET 
		`trust_account_software` = '{$tas_id}',
		`tas_connected` = 0
	WHERE `agency_id` = {$agency_id}
";
mysql_query($sql);
?>