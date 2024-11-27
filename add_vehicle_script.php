<?php

include('inc/init.php');

$make = $_POST['make'];
$model = $_POST['model'];
$year = $_POST['year'];
$number_plate = $_POST['number_plate'];
$rego_expires = ($_POST['rego_expires']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['rego_expires']))):null;
$warranty_expires = ($_POST['warranty_expires']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['warranty_expires']))):null;
$fuel_type = $_POST['fuel_type'];
$etag_num = $_POST['etag_num'];
$serviced_by = $_POST['serviced_by'];
$fuel_card_num = $_POST['fuel_card_num'];
$purchase_date = ($_POST['purchase_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['purchase_date']))):null;
$purchase_price = $_POST['purchase_price'];
$ra_num = $_POST['ra_num'];
$ins_pol_num = $_POST['ins_pol_num'];
$pol_exp = ($_POST['purchase_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['pol_exp']))):null;
$staff_id = $_POST['staff_id'];
$fuel_card_pin = $_POST['fuel_card_pin'];
$vin_num = $_POST['vin_num'];
$plant_id = $_POST['plant_id'];
$tech_vehicle = $_POST['tech_vehicle'];
$key_number = $_POST['key_number'];

// vehicle
$insert = mysql_query("
	INSERT INTO
	`vehicles`(
		`make`,
		`model`,
		`year`,
		`number_plate`,
		`rego_expires`,
		`warranty_expires`,
		`fuel_type`,
		`etag_num`,
		`serviced_by`,
		`fuel_card_num`,
		`purchase_date`,
		`purchase_price`,
		`ra_num`,
		`ins_pol_num`,
		`policy_expires`,
		`StaffID`,
		`fuel_card_pin`,
		`vin_num`,
		`plant_id`,
		`tech_vehicle`,
		`country_id`,
		`key_number`
	)
	VALUES(
		'".mysql_real_escape_string($make)."',
		'".mysql_real_escape_string($model)."',
		'".mysql_real_escape_string($year)."',
		'".mysql_real_escape_string($number_plate)."',
		'".mysql_real_escape_string($rego_expires)."',
		'".mysql_real_escape_string($warranty_expires)."',
		'".mysql_real_escape_string($fuel_type)."',
		'".mysql_real_escape_string($etag_num)."',
		'".mysql_real_escape_string($serviced_by)."',
		'".mysql_real_escape_string($fuel_card_num)."',
		'".mysql_real_escape_string($purchase_date)."',
		'".mysql_real_escape_string($purchase_price)."',
		'".mysql_real_escape_string($ra_num)."',
		'".mysql_real_escape_string($ins_pol_num)."',
		'".mysql_real_escape_string($pol_exp)."',
		'".mysql_real_escape_string($staff_id)."',
		'".mysql_real_escape_string($fuel_card_pin)."',
		'".mysql_real_escape_string($vin_num)."',
		'".mysql_real_escape_string($plant_id)."',
		'".mysql_real_escape_string($tech_vehicle)."',
		{$_SESSION['country_default']},
		'".mysql_real_escape_string($key_number)."'
	)
");

header("location: /view_vehicles.php?add_success=1");

?>