<?php

include('inc/init.php');
include('inc/future_pendings_functions.php');

// file name
$filename = "future_pendings_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
$str = "Property ID,Address,Agency\n";

$agency = mysql_real_escape_string($_GET['agency']);	
$phrase = mysql_real_escape_string(urldecode($_GET['phrase']));	

$u_sql = getFuturePendings('','',$agency,$phrase);

while($u = mysql_fetch_array($u_sql)){
	$str .= "{$u['property_id']},\"{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}\",\"{$u['agency_name']}\"\n";
}

echo $str;

?>