<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$staff_id = mysql_real_escape_string($_POST['staff_id']);
$home_display = mysql_real_escape_string($_POST['home_display']);
$chk_val = mysql_real_escape_string($_POST['chk_val']);

$sql_str = "
	UPDATE `staff_accounts`
	SET `{$home_display}` = {$chk_val}
	WHERE `StaffID` = {$staff_id}
";
mysql_query($sql_str);

?>