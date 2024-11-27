<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$vehicles_id = $_POST['vehicles_id'];
$make = $_POST['make'];
$model = $_POST['model'];
$plant_id = $_POST['plant_id'];
$key_number = $_POST['key_number'];
$vin_num = $_POST['vin_num'];
$number_plate = $_POST['number_plate'];
$staff_id = $_POST['staff_id'];
$tech_vehicle = $_POST['tech_vehicle'];
$kms = $_POST['kms'];
$next_service = $_POST['next_service'];
$active = $_POST['active'];
$rego_expires = ( $crm->isDateNotEmpty($_POST['rego_expires']) == true )?"'".$crm->formatDate(mysql_real_escape_string($_POST['rego_expires']))."'":'NULL';

/*
$update_kms_str = "";
if($kms!=""){
	$update_kms_str = ($kms!=$orig_kms)?" `kms_updated` = '".date("Y-m-d H:i:s")."', ":"";
}
*/

// vehicles
mysql_query("
	UPDATE `vehicles`
	SET
		`make` = '".mysql_real_escape_string($make)."',
		`model` = '".mysql_real_escape_string($model)."',
		`plant_id` = '".mysql_real_escape_string($plant_id)."',		
		`key_number` = '".mysql_real_escape_string($key_number)."',
		`vin_num` = '".mysql_real_escape_string($vin_num)."',
		`number_plate` = '".mysql_real_escape_string($number_plate)."',
		`StaffID` = '".mysql_real_escape_string($staff_id)."',
		`next_service` = '".mysql_real_escape_string($next_service)."',
		`tech_vehicle` = '".mysql_real_escape_string($tech_vehicle)."',
		`active` = '".mysql_real_escape_string($active)."',
		`rego_expires` = {$rego_expires}
	WHERE `vehicles_id` = {$vehicles_id}
");


// kms
mysql_query("
	INSERT INTO 
	`kms`(
		`vehicles_id`,
		`kms`,
		`kms_updated`
	)
	VALUES(
		{$vehicles_id},
		'".mysql_real_escape_string($kms)."',
		'".date("Y-m-d H:i:s")."'		
	)
");


?>