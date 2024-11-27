<?php

include('inc/init.php');

// file name
$filename = "Export_Vehicle_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
echo "Make,Model,Plant ID,Year,Number Plate,Rego Expires,Warranty Expires,Fuel Type,eTag Number,Serviced By,Next Service,Fuel Card Number,Purchase Date,Purchase Price,Roadside Assistance Number,Insurance Policy Number,Policy Expires,Driver,Fuel Card Pin,VIN Number\n";

// get vehicles
$v_sql = mysql_query("
	SELECT *
	FROM `vehicles` AS v
	LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = v.`StaffID`
	WHERE v.`country_id` = {$_SESSION['country_default']}
");

// body
while($v=mysql_fetch_array($v_sql)){
	echo "{$v['make']},{$v['model']},{$v['plant_id']},{$v['year']},{$v['number_plate']},".(($v['rego_expires']!=""&&$v['rego_expires']!="0000-00-00")?date("d/m/Y",strtotime($v['rego_expires'])):'').",".(($v['warranty_expires']!=""&&$v['warranty_expires']!="0000-00-00"&&$v['warranty_expires']!="1970-01-01")?date("d/m/Y",strtotime($v['warranty_expires'])):'').",{$v['fuel_type']},{$v['etag_num']},{$v['serviced_by']},{$v['next_service']},{$v['fuel_card_num']},".(($v['purchase_date']!=""&&$v['purchase_date']!="0000-00-00"&&$v['purchase_date']!="1970-01-01")?date("d/m/Y",strtotime($v['purchase_date'])):'').",{$v['purchase_price']},{$v['ra_num']},{$v['ins_pol_num']},".(($v['policy_expires']!=""&&$v['policy_expires']!="0000-00-00 00:00:00"&&$v['policy_expires']!="1970-01-01 00:00:00")?date("d/m/Y",strtotime($v['policy_expires'])):'').",{$v['FirstName']} {$v['LastName']},{$v['fuel_card_pin']},{$v['vin_num']}\n";
}

?>