<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$leave_id = mysql_real_escape_string($_POST['leave_id']);

// Leave Request Form
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$employee = mysql_real_escape_string($_POST['employee']);
$type_of_leave = mysql_real_escape_string($_POST['type_of_leave']);
$lday_of_work = mysql_real_escape_string($_POST['lday_of_work']);
$lday_of_work2 = $crm->formatDate($lday_of_work);
$fday_back = mysql_real_escape_string($_POST['fday_back']);
$fday_back2 = $crm->formatDate($fday_back);
$num_of_days = mysql_real_escape_string($_POST['num_of_days']);
$reason_for_leave = mysql_real_escape_string($_POST['reason_for_leave']);

// Office Use Only
$line_manager = ( $_POST['line_manager']!='' )?mysql_real_escape_string($_POST['line_manager']):'NULL';
$line_manager_app = is_numeric($_POST['line_manager_app'])?mysql_real_escape_string($_POST['line_manager_app']):'NULL';
$hr_app = is_numeric($_POST['hr_app'])?mysql_real_escape_string($_POST['hr_app']):'NULL';
$added_to_cal = is_numeric($_POST['added_to_cal'])?mysql_real_escape_string($_POST['added_to_cal']):'NULL';
$added_to_cal_changed = mysql_real_escape_string($_POST['added_to_cal_changed']);
$staff_notified = is_numeric($_POST['staff_notified'])?mysql_real_escape_string($_POST['staff_notified']):'NULL';
$staff_notified_changed = mysql_real_escape_string($_POST['staff_notified_changed']);

$comments = mysql_real_escape_string($_POST['comments']);

$backup_leave = mysql_real_escape_string($_POST['backup_leave']);

//echo "{$hr_app} - {$line_manager_app}<br />";
if( $line_manager_app=='1' && $hr_app=='1' ){
	$status = 'Approved';
}else if( $line_manager_app==='0' || $hr_app==='0' ){
	$status = 'Denied';
}else{
	$status = 'Pending';
}

//echo $status;

$country_id = $_SESSION['country_default'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$now = date("Y-m-d H:i:s");

$radio_str = '';

// get old settings
$jparams = array(
	'leave_id' => $leave_id,
	'country_id' => $country_id
);
$leave_sql = $crm->getLeave($jparams);
$leave = mysql_fetch_array($leave_sql);
$old_line_manager_app = $leave['line_manager_app'];
$old_hr_app = $leave['hr_app'];
$old_added_to_cal = $leave['added_to_cal'];
$old_staff_notified = $leave['staff_notified'];


if( is_numeric($line_manager_app) && $line_manager_app != $old_line_manager_app ){
	$radio_str .= "
		line_manager_app_by = {$staff_id},
		line_manager_app_timestamp = '".$now."',
	";
}

if( is_numeric($hr_app) && $hr_app != $old_hr_app ){
	$radio_str .= "
		hr_app_by = {$staff_id},
		hr_app_timestamp = '".$now."',
	";
}

if( $added_to_cal_changed==1 && ( $added_to_cal != $old_added_to_cal ) ){
	$radio_str .= "
		`added_to_cal` = {$added_to_cal},
		`added_to_cal_by` = {$staff_id},
		`added_to_cal_timestamp` = '".$now."',
	";
}

if( $staff_notified_changed==1 && ( $staff_notified != $old_staff_notified ) ){
	$radio_str .= "
		`staff_notified` = {$staff_notified},
		`staff_notified_by` = {$staff_id},
		`staff_notified_timestamp` = '".$now."',
	";
}


$sql_str = "
	UPDATE `leave` 
	SET
		`date` = '{$date2}',
		`employee` = '{$employee}',
		`type_of_leave` = '{$type_of_leave}',
		`lday_of_work` = '{$lday_of_work2}',
		`fday_back` = '{$fday_back2}',
		`num_of_days` = '{$num_of_days}',
		`reason_for_leave` = '{$reason_for_leave}',
		`line_manager` = {$line_manager},
		`line_manager_app` = {$line_manager_app},
		`hr_app` = {$hr_app},
		`comments` = '{$comments}',
		{$radio_str}
		`status` = '{$status}',
		`backup_leave` = '{$backup_leave}'
	WHERE `leave_id`= {$leave_id}
";

mysql_query($sql_str);

header("location: /leave_details.php?id={$leave_id}&success=1");

?>