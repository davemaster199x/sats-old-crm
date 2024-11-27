<?php

include('inc/init_for_ajax.php');

$ccd_id = mysql_real_escape_string($_POST['ccd_id']);
$shift_from = ($_POST['shift_from']=="")?'NULL':mysql_real_escape_string($_POST['shift_from']);
$shift_to = ($_POST['shift_to']=="")?'NULL':mysql_real_escape_string($_POST['shift_to']);
$check_in = mysql_real_escape_string($_POST['check_in']);
$first_call = mysql_real_escape_string($_POST['first_call']);
$last_call = mysql_real_escape_string($_POST['last_call']);
$time_7_8_am = ($_POST['time_7_8_am']=="")?'NULL':mysql_real_escape_string($_POST['time_7_8_am']);
$time_8_9_am = ($_POST['time_8_9_am']=="")?'NULL':mysql_real_escape_string($_POST['time_8_9_am']);
$time_9_10_am = ($_POST['time_9_10_am']=="")?'NULL':mysql_real_escape_string($_POST['time_9_10_am']);
$time_10_11_am = ($_POST['time_10_11_am']=="")?'NULL':mysql_real_escape_string($_POST['time_10_11_am']);
$time_11_12_pm = ($_POST['time_11_12_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_11_12_pm']);
$time_12_1_pm = ($_POST['time_12_1_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_12_1_pm']);
$time_1_2_pm = ($_POST['time_1_2_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_1_2_pm']);
$time_2_3_pm = ($_POST['time_2_3_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_2_3_pm']);
$time_3_4_pm = ($_POST['time_3_4_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_3_4_pm']);
$time_4_5_pm = ($_POST['time_4_5_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_4_5_pm']);
$time_5_6_pm = ($_POST['time_5_6_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_5_6_pm']);
$time_6_7_pm = ($_POST['time_6_7_pm']=="")?'NULL':mysql_real_escape_string($_POST['time_6_7_pm']);

$sql = "
	UPDATE `call_centre_data`
	SET 
		`shift_from` = {$shift_from},
		`shift_to` = {$shift_to},
		`check_in` = '{$check_in}',
		`first_call` = '{$first_call}',
		`last_call` = '{$last_call}',
		`7-8_am` = {$time_7_8_am},
		`8-9_am` = {$time_8_9_am},
		`9-10_am` = {$time_9_10_am},
		`10-11_am` = {$time_10_11_am},
		`11-12_pm` = {$time_11_12_pm},
		`12-1_pm` = {$time_12_1_pm},
		`1-2_pm` = {$time_1_2_pm},
		`2-3_pm` = {$time_2_3_pm},
		`3-4_pm` = {$time_3_4_pm},
		`4-5_pm` = {$time_4_5_pm},
		`5-6_pm` = {$time_5_6_pm},
		`6-7_pm` = {$time_6_7_pm}
	WHERE `call_centre_data_id` = {$ccd_id}
";
// update
mysql_query($sql);

?>