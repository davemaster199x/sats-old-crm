<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$pt_field = mysql_real_escape_string($_REQUEST['pt_field']);
$chk_val = mysql_real_escape_string($_REQUEST['chk_val']);

// update agency printing tracking fields
$sql = "
	UPDATE `agency`
	SET `{$pt_field}` = {$chk_val}
	WHERE `agency_id` = {$agency_id}
";
mysql_query($sql);
?>