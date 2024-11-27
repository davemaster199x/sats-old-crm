<?php

include('inc/init_for_ajax.php');

$accomodation_id = $_POST['accomodation_id'];
$name = $_POST['name'];
$area = $_POST['area'];
$address = mysql_real_escape_string($_POST['address']);
$phone = $_POST['phone'];
$email = $_POST['email'];
$rate = $_POST['rate'];
$comment = $_POST['comment'];

$acco_update_str = '';

if( $address!='' ){
	
	$address2 = "{$address}";
	$coordinate = getGoogleMapCoordinates($address2);
	$acco_update_str = "
		,`address` = '".$address."',
		`lat` = '{$coordinate['lat']}',
		`lng` = '{$coordinate['lng']}'
	";
}



// vehicles
mysql_query("
	UPDATE `accomodation`
	SET
		`name` = '".mysql_real_escape_string($name)."',
		`area` = '".mysql_real_escape_string($area)."',				
		`phone` = '".mysql_real_escape_string($phone)."',
		`email` = '".mysql_real_escape_string($email)."',
		`rate` = '".mysql_real_escape_string($rate)."',
		`comment` = '".mysql_real_escape_string($comment)."'
		{$acco_update_str}
	WHERE `accomodation_id` = {$accomodation_id}
");


?>