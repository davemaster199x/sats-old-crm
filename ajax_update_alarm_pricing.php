<?php

include('inc/init_for_ajax.php');

$alarm_pwr_id = mysql_real_escape_string($_POST['alarm_pwr_id']);
$alarm_pwr = mysql_real_escape_string($_POST['alarm_pwr']);
$alarm_make = mysql_real_escape_string($_POST['alarm_make']);
$alarm_model = mysql_real_escape_string($_POST['alarm_model']);
$alarm_expiry = mysql_real_escape_string($_POST['alarm_expiry']);
$alarm_price_ex = mysql_real_escape_string($_POST['alarm_price_ex']);
$alarm_price_inc = mysql_real_escape_string($_POST['alarm_price_inc']);
$active = mysql_real_escape_string($_POST['active']);

$sql = "
	UPDATE `alarm_pwr`
	SET
		`alarm_pwr` = '{$alarm_pwr}',
		`alarm_make` = '{$alarm_make}',
		`alarm_model` = '{$alarm_model}',
		`alarm_expiry` = '{$alarm_expiry}',
		`alarm_price_ex` = '{$alarm_price_ex}',
		`alarm_price_inc` = '{$alarm_price_inc}',
		`active` = '{$active}'
	WHERE `alarm_pwr_id` = {$alarm_pwr_id}
";
mysql_query($sql);

?>