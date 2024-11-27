<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;


$exp_sum_id = mysql_real_escape_string($_POST['exp_sum_id']);
$date_reimbursed = mysql_real_escape_string($_POST['date_reimbursed']);
$date_reimbursed2 = $crm->formatDate($date_reimbursed);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql_str = "
	UPDATE `expense_summary` 
	SET
		`date_reimbursed` = '{$date_reimbursed2}',
		`who` = {$staff_id}
	WHERE `expense_summary_id` = {$exp_sum_id}
";
mysql_query($sql_str);

?>