<?php

include('inc/init_for_ajax.php');


$alarm_pwr = mysql_real_escape_string($_POST['name']);
$alarm_make = mysql_real_escape_string($_POST['make']);
$alarm_model = mysql_real_escape_string($_POST['model']);
$alarm_expiry = mysql_real_escape_string($_POST['expiry']);
$alarm_price_ex = mysql_real_escape_string($_POST['price_ex_gst']);
$alarm_price_inc = mysql_real_escape_string($_POST['price_inc_gst']);

$sql = "
	INSERT INTO 
	`alarm_pwr` (
		`alarm_pwr`,
		`alarm_make`,
		`alarm_model`,
		`alarm_expiry`,
		`alarm_price_ex`,
		`alarm_price_inc`,
		`alarm_job_type_id`,
		`alarm_type`,
		`active`
	)
	VALUES (
		'{$alarm_pwr}',
		'{$alarm_make}',
		'{$alarm_model}',
		'{$alarm_expiry}',
		'{$alarm_price_ex}',
		'{$alarm_price_inc}',
		2,
		2,
		1
	)
";
mysql_query($sql);

header("location: alarm_pricing_page.php?success=1");

?>