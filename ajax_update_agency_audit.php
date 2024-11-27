<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$au_id = mysql_real_escape_string($_POST['au_id']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$assigned_to = ( $_POST['assigned_to']!='' )?mysql_real_escape_string($_POST['assigned_to']):'NULL';
$ad_comments = mysql_real_escape_string($_POST['ad_comments']);
$ad_status = mysql_real_escape_string($_POST['ad_status']);
$ad_comp_date = mysql_real_escape_string($_POST['ad_comp_date']);
$ad_comp_date = mysql_real_escape_string($_POST['ad_comp_date']);
$ad_comp_date2 = ($ad_comp_date!="")?"'".$crm->formatDate($ad_comp_date)."'":'NULL';


$sql = "
	UPDATE `agency_audits`
	SET 
		`agency_id` = {$agency_id},
		`assigned_to` = {$assigned_to},
		`comments` = '{$ad_comments}',
		`status` = {$ad_status},
		`completion_date` = {$ad_comp_date2}
	WHERE `agency_audit_id` = {$au_id}
";

mysql_query($sql);

?>