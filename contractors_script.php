<?php

include('inc/init.php');

$name = $_POST['name'];
$area = $_POST['area'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$rate = $_POST['rate'];
$comment = $_POST['comment'];

mysql_query("
	INSERT INTO 
	`contractors`(
		`name`,
		`area`,
		`address`,
		`phone`,
		`email`,
		`rate`,
		`comment`,
		`country_id`
	)
	VALUES(
		'".mysql_real_escape_string($name)."',
		'".mysql_real_escape_string($area)."',
		'".mysql_real_escape_string($address)."',
		'".mysql_real_escape_string($phone)."',
		'".mysql_real_escape_string($email)."',
		'".mysql_real_escape_string($rate)."',
		'".mysql_real_escape_string($comment)."',
		{$_SESSION['country_default']}
	)
");

header("Location: contractors.php?success=1");

?>
