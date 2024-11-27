<?php

include('inc/init_for_ajax.php');

$landlord_firstname = mysql_prep($_POST['landlord_firstname_api']);
$landlord_lastname = mysql_prep($_POST['landlord_lastname_api']);
$landlord_email = addslashes($_POST['landlord_email_api']);
$ll_mobile = addslashes($_POST['ll_mobile_api']);
$ll_landline = addslashes($_POST['ll_landline_api']);
$id = addslashes($_POST['id']);

$updateQuery = "UPDATE property set 
    landlord_firstname=\"$landlord_firstname\", 
    landlord_lastname=\"$landlord_lastname\", 
    landlord_email='$landlord_email', 
	`landlord_mob` = '{$ll_mobile}',
	`landlord_ph` = '{$ll_landline}'
    WHERE (property_id=$id);";

$result = mysql_query($updateQuery, $connection) or die("An Error Occured, please copy and paste this into an email: Update Query is: $updateQuery");
?>