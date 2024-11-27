<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$property_id = mysql_real_escape_string($_POST['property_id']);
$hid_smoke_price = $_POST['hid_smoke_price'];
$dummy_date = date("Y-m-d",strtotime(str_replace("/","-",$_POST['dummy_date'])));
$dummy_date2 = date("Y-m-d H:i:s",strtotime(str_replace("/","-",$_POST['dummy_date'])));
$agency_id = mysql_real_escape_string($_POST['agency_id']);

//echo "Agency ID: {$agency_id}";


// get Franchise Group
$agen_sql = mysql_query("
	SELECT `franchise_groups_id`
	FROM `agency`
	WHERE `agency_id` = {$agency_id}
");
$agen = mysql_fetch_array($agen_sql);

// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
$dha_need_processing = 0;
if( isDHAagenciesV2($agen['franchise_groups_id'])==true || agencyHasMaintenanceProgram($agency_id)==true ){
	$dha_need_processing = 1;
}

$sql = "
	INSERT INTO 
	`jobs` (
		`status`, 
		`retest_interval`, 
		`auto_renew`, 
		`job_type`, 
		`property_id`, 
		`sort_order`, 
		`job_price`, 
		`service`,
		`date`,
		`assigned_tech`,
		`created`,
		`dha_need_processing`
	)
	VALUES(
		'Completed', 
		365, 
		1, 
		'Yearly Maintenance', 
		{$property_id}, 
		1, 
		0, 
		2,
		'{$dummy_date}',
		1,
		'{$dummy_date2}',
		'{$dha_need_processing}'
	)
";


mysql_query($sql);

// job id
$job_id = mysql_insert_id();

// AUTO - UPDATE INVOICE DETAILS
$crm->updateInvoiceDetails($job_id);

// insert job logs
mysql_query("
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`eventtime`,
		`comments`,
		`job_id`,
		`staff_id`
	) 
	VALUES (
		'Job Created',
		'" . date('Y-m-d') . "',
		'" . date('H:i') . "',
		'Other Supplier Job Created', 
		'{$job_id}',
		'{$_SESSION['USER_DETAILS']['StaffID']}'
	)
");

// add property logs
$service_name = $_POST['service_name'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
mysql_query("
	INSERT INTO 
	`property_event_log` (
		`property_id`, 
		`staff_id`, 
		`event_type`, 
		`event_details`, 
		`log_date`
	) 
	VALUES (
		".$property_id.",
		".$staff_id.",
		'Job Created',
		'Other Supplier Job Created',
		'".date('Y-m-d H:i:s')."'
	)
");


?>