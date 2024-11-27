<?php

include('inc/init.php');

$company_name = $_POST['company_name'];
$service_provided = $_POST['service_provided'];
$address = $_POST['address'];
$contact_name = $_POST['contact_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$website = $_POST['website'];
$notes = $_POST['notes'];


$coor = getGoogleMapCoordinates("{$address}");


mysql_query("
	INSERT INTO 
	`suppliers`(
		`company_name`,
		`service_provided`,
		`address`,
		`contact_name`,
		`phone`,
		`email`,
		`website`,
		`notes`,
		`country_id`,
		`lat`,
		`lng`,
		`on_map`
	)
	VALUES(
		'".mysql_real_escape_string($company_name)."',
		'".mysql_real_escape_string($service_provided)."',
		'".mysql_real_escape_string($address)."',
		'".mysql_real_escape_string($contact_name)."',
		'".mysql_real_escape_string($phone)."',
		'".mysql_real_escape_string($email)."',
		'".mysql_real_escape_string($website)."',
		'".mysql_real_escape_string($notes)."',
		{$_SESSION['country_default']},
		'{$coor['lat']}',
		'{$coor['lng']}',
		1
	)
");

header("Location: suppliers.php?success=1");

?>
