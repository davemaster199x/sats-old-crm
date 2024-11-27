<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$exp_sum_id = mysql_real_escape_string($_POST['exp_sum_id']);
$exp_sum_status = mysql_real_escape_string($_POST['exp_sum_status']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql_str = "
	UPDATE `expense_summary` 
	SET
		`exp_sum_status` = {$exp_sum_status},
		`who` = {$staff_id}
	WHERE `expense_summary_id` = {$exp_sum_id}
";
mysql_query($sql_str);
?>