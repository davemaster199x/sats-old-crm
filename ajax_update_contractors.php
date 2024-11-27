<?php

include('inc/init_for_ajax.php');

$contractors_id = $_POST['contractors_id'];
$name = $_POST['name'];
$area = $_POST['area'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$rate = $_POST['rate'];
$comment = $_POST['comment'];


// vehicles
mysql_query("
	UPDATE `contractors`
	SET
		`name` = '".mysql_real_escape_string($name)."',
		`area` = '".mysql_real_escape_string($area)."',		
		`address` = '".mysql_real_escape_string($address)."',
		`phone` = '".mysql_real_escape_string($phone)."',
		`email` = '".mysql_real_escape_string($email)."',
		`rate` = '".mysql_real_escape_string($rate)."',
		`comment` = '".mysql_real_escape_string($comment)."'
	WHERE `contractors_id` = {$contractors_id}
");


?>