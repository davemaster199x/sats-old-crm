<?php
include('inc/init_for_ajax.php');
include('inc/Sats_Crm_Class.php');
$crm = new Sats_Crm_Class;

$tech_id = mysql_real_escape_string($_REQUEST['tech_id']);
$date = $crm->formatDate(mysql_real_escape_string($_REQUEST['date']));


$sa_sql = mysql_query("
	SELECT * 
	FROM `staff_accounts` 
	WHERE `StaffID` ={$tech_id}
");
$sa = mysql_fetch_array($sa_sql);


// get calendar data
$cal_sql = mysql_query("
	SELECT *
	FROM `calendar`
	WHERE staff_id = {$sa['StaffID']}
	AND `date_start` = '{$date}'
	AND `date_finish` = '{$date}'
	ORDER BY `calendar_id` DESC
");
$cal = mysql_fetch_array($cal_sql);
$cal_id = $cal['calendar_id'];
$cal_name = $cal['region'];
	

$arr = array(
	"cal_id"=>$cal_id,
	"cal_name"=>$cal_name
);
echo json_encode($arr);
?>