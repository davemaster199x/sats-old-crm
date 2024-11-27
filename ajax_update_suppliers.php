<?php

include('inc/init_for_ajax.php');

$suppliers_id = $_POST['suppliers_id'];
$company_name = $_POST['company_name'];
$service_provided = $_POST['service_provided'];
$address = $_POST['address'];
$contact_name = $_POST['contact_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$website = $_POST['website'];
$notes = $_POST['notes'];
$on_map = $_POST['on_map'];


// check supplier lat/lng
$sup_str = "
	SELECT *
	FROM `suppliers`
	WHERE `suppliers_id` = {$suppliers_id}
	AND `lat` IS NULL
	AND `lng` IS NULL
	AND `address` != ''
";
$sup_sql = mysql_query($sup_str);
if(mysql_num_rows($sup_sql)>0){
		
	$sup = mysql_fetch_array($sup_sql);
	// get geocode
	$coor = getGoogleMapCoordinates("{$sup['address']}");
	// update supplier lat/lng
	$update_str = "
		UPDATE `suppliers`
		SET 
			`lat` = '{$coor['lat']}',
			`lng` = '{$coor['lng']}'
		WHERE `suppliers_id` = {$suppliers_id}
	";
	mysql_query($update_str);
}

$update_str2 = "
	UPDATE `suppliers`
	SET
		`company_name` = '".mysql_real_escape_string($company_name)."',
		`service_provided` = '".mysql_real_escape_string($service_provided)."',
		`address` = '".mysql_real_escape_string($address)."',
		`contact_name` = '".mysql_real_escape_string($contact_name)."',
		`phone` = '".mysql_real_escape_string($phone)."',
		`email` = '".mysql_real_escape_string($email)."',
		`website` = '".mysql_real_escape_string($website)."',
		`notes` = '".mysql_real_escape_string($notes)."',
		`on_map` = '".mysql_real_escape_string($on_map)."'
	WHERE `suppliers_id` = {$suppliers_id}
";
// vehicles
mysql_query($update_str2);


?>