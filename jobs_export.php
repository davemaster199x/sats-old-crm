<?php

include('inc/init.php');

// Initiate job class
$jc = new Job_Class();

// file name
$filename = "export_jobs_".date("d/m/Y").".csv";


// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");


// headers
$export_str .= "Date,Job Type,Age,Service,Price,Address,State,Region,Agency,Job Number,Last Contact\n";

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string(urldecode($_REQUEST['job_type']));
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$is_urgent = ($_REQUEST['is_urgent']!="")?mysql_real_escape_string($_REQUEST['is_urgent']):'';
$job_status = 'To be Booked';

// get jobs list
$jsql = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','',$is_urgent);


// body
while( $row = mysql_fetch_array($jsql) ){
	
	$jdate = ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):'';
	$job_type = getJobTypeAbbrv($row['job_type']);
	
	// Age
	$date1=date_create($row['jcreated']);
	$date2=date_create(date('Y-m-d'));
	$diff=date_diff($date1,$date2);
	$age = $diff->format("%r%a");
	$age = (((int)$age)!=0)?$age:0;
	
	$service = getServiceName($row['jservice']);
	$jprice = $row['job_price'];
	$paddress =  "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	$paddress2 = str_replace('"',"'",$paddress);
	//echo "<br />";
	$pstate = $row['p_state'];
	
	// region				
	$pr_sql = mysql_query("
		SELECT `postcode_region_name`
		FROM `postcode_regions` 
		WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
		AND `country_id` = {$_SESSION['country_default']}
		AND `deleted` = 0
	");
	$pr = mysql_fetch_array($pr_sql);
	$pregion = $pr['postcode_region_name'];
	
	$agency_name = $row['agency_name'];
	$jid =  $row['jid'];
	
	// last contact
	$lc_sql = $jc->getLastContact($row['jid']);	
	$lc = mysql_fetch_array($lc_sql);
	$lc_val = ( $lc['eventdate']!="" && $lc['eventdate']!="0000-00-00 00:00:00" )?date("d/m/Y",strtotime($lc['eventdate'])):'';
	
	$export_str .= "\"{$jdate}\",\"{$job_type}\",\"{$age}\",\"{$service}\",\"{$jprice}\",\"{$paddress2}\",\"{$pstate}\",\"{$pregion}\",\"{$agency_name}\",\"{$jid}\",\"{$lc_val}\"\n";
	
}


echo $export_str;

?>