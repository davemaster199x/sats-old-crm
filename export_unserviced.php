<?php

include('inc/init.php');
//include('inc/unserviced_functions.php');


// file name
$filename = "Unserviced_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");


// headers
$str = "Property ID,Address,Last Job\n";


$u_sql = getUnservicedProperties(getExcludedProperties());

while($u = mysql_fetch_array($u_sql)){
	$str .= "{$u['property_id']},\"{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}\",".((getGetLastJob($u['property_id'])!="")?date("d/m/Y",strtotime(getGetLastJob($u['property_id']))):'')."\n";
}


echo $str;


?>