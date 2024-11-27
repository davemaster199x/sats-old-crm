<?php

include('inc/init_for_ajax.php');

//$crm = new Sats_Crm_Class();

$alarm_pwr_id = mysql_real_escape_string($_REQUEST['alarm_pwr_id']);

$alarm_arr = [];

$alarm_sql = mysql_query("
	SELECT *
	FROM `alarm_pwr` AS a_pwr
	LEFT JOIN `alarm_type` AS a_typ ON a_pwr.`alarm_type` = a_typ.`alarm_type_id`
	WHERE a_pwr.`alarm_pwr_id` = {$alarm_pwr_id}
");

$alarm_row = mysql_fetch_array($alarm_sql);

$alarm_arr['alarm_make'] = $alarm_row['alarm_make'];
$alarm_arr['alarm_model'] = $alarm_row['alarm_model'];
$alarm_arr['alarm_expiry'] = $alarm_row['alarm_expiry'];
$alarm_arr['alarm_type_id'] = $alarm_row['alarm_type_id'];


echo json_encode($alarm_arr);

?>