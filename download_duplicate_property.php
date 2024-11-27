<?php

include('inc/init.php');

$csv_array = $_SESSION['import_property_duplicates'];

// file name
$filename = "Duplicates_Properties_Template_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
echo "Street Number,Street Name,Suburb,State,Postcode,Landlord Firstname,Landlord Lastname,Landlord Email,Tenant 1 Firstname,Tenant 1 Lastname,Tenant 1 Phone,Tenant 1 Mobile,Tenant 1 Email,Tenant 2 Firstname,Tenant 2 Lastname,Tenant 2 Phone,Tenant 2 Mobile,Tenant 2 Email,Key Number,Comments\n";

foreach($csv_array as $prop){
	echo "\"".$prop['street_num']."\",\"".$prop['street_name']."\",\"".$prop['suburb']."\",\"".$prop['state']."\",\"".$prop['postcode']."\",\"".$prop['landlord_fname']."\",\"".$prop['landlord_lname']."\",\"".$prop['landlord_email']."\",\"".$prop['tenant_fname1']."\",\"".$prop['tenant_lname1']."\",\"".$prop['tenant_phone1']."\",\"".$prop['tenant_mobile1']."\",\"".$prop['tenant_email1']."\",\"".$prop['tenant_fname2']."\",\"".$prop['tenant_lname2']."\",\"".$prop['tenant_phone2']."\",\"".$prop['tenant_mobile2']."\",\"".$prop['tenant_email2']."\",\"".$prop['key_number']."\",\"".$prop['comments']."\"\n";
}

unset($_SESSION['import_property_duplicates']); 

?>