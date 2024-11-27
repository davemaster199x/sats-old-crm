<?php
include('inc/init.php');

// Leave Request Form
$exp_sum_id = mysql_real_escape_string($_POST['exp_sum_id']);
$line_manager = mysql_real_escape_string($_POST['line_manager']);
$exp_sum_status = mysql_real_escape_string($_POST['exp_sum_status']);
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql_str = "
	UPDATE `expense_summary` 
	SET
		`line_manager` = '{$line_manager}',
		`exp_sum_status` = '{$exp_sum_status}',
		`who` = {$loggedin_staff_id}
	WHERE `expense_summary_id` = {$exp_sum_id}
";
mysql_query($sql_str);

header("location: expense_summary_details.php?id={$exp_sum_id}&update_success=1");
?>