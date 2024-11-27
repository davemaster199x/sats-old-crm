<?php

include('inc/init.php');

// file name
$filename = "Import_Properties_Template_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
echo "Street Number,Street Name,Suburb,State,Postcode,Landlord Firstname,Landlord Lastname,Landlord Email,Tenant 1 Firstname,Tenant 1 Lastname,Tenant 1 Phone,Tenant 1 Mobile,Tenant 1 Email,Tenant 2 Firstname,Tenant 2 Lastname,Tenant 2 Phone,Tenant 2 Mobile,Tenant 2 Email,Tenant 3 Firstname,Tenant 3 Lastname,Tenant 3 Phone,Tenant 3 Mobile,Tenant 3 Email,Tenant 4 Firstname,Tenant 4 Lastname,Tenant 4 Phone,Tenant 4 Mobile,Tenant 4 Email,Key Number,Comments\n";


?>