<?php

include('inc/init.php');
include('inc/servicedue_functions.php');

// file name
$filename = "service_due_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
$str = "Agency,Sales Rep, Service Due\n";


// data
$state = $_REQUEST['searchstate'];
$salesrep = $_REQUEST['searchsalesrep'];
$region = $_REQUEST['searchregion'];
$phrase = $_REQUEST['phrase'];

$a_sql = getAgencies('','',$state,$salesrep,$region,$phrase);

while($a = mysql_fetch_array($a_sql)){
	$str .= "{$a['agency_name']},{$a['FirstName']} {$a['LastName']},".getServiceDue($a['agency_id'])."\n";
}

echo $str;

?>