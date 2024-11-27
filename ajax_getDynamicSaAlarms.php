<?php

include('inc/init_for_ajax.php');

$is_new = $_POST['is_new'];
$agency_id = $_POST['agency_id'];

if($is_new==1){
	$sql = mysql_query("
		SELECT * 
		FROM `agency_alarms` AS aa
		LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
		WHERE aa.`agency_id` = {$agency_id}
	");		
}else{
	// exclude batteries
	$sql = mysql_query("
		SELECT * 
		FROM `alarm_pwr`
		WHERE `alarm_pwr_id` != 6
	");	
}

$str = "";

while($row = mysql_fetch_array($sql)){
	$str .= "<option value='{$row['alarm_pwr_id']}'>{$row['alarm_pwr']}</option>";
}

echo $str;

?>


