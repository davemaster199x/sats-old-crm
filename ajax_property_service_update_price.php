<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$psid = $_POST['psid'];
$ajt = $_POST['ajt'];
$price = $_POST['price'];
$price_reason = $_POST['price_reason'];
$price_details = $_POST['price_details'];

$updated_price = false;
$insert_new_price = false;

$serv = "";
switch($ajt){
	case 2:
		$serv = "Smoke Alarms";
	break;
	case 5:
		$serv = "Safety Switch";
	break;
	case 6:
		$serv = "Corded Windows";
	break;
	case 7:
		$serv = "Pool Barriers";
	break;
}

if( $psid > 0 ){

	// update price
	mysql_query("
		UPDATE `property_services`
		SET `price` = ".mysql_real_escape_string($price)."
		WHERE `property_services_id` = ".$psid."
	");	

	$updated_price = true;

}else{

	// insert new property service and set service to NR
	$ps_insert_sql_str = "
	INSERT INTO 
	`property_services` (
		`property_id`,
		`alarm_job_type_id`,
		`service`,
		`price`,
		`status_changed`
	)
	VALUES(
		{$property_id},
		{$ajt},
		2,
		".mysql_real_escape_string($price).",
		'" . date('Y-m-d H:i:s') . "'	
	)
	";
	mysql_query($ps_insert_sql_str);

	$insert_new_price = true;

}

if( $updated_price == true || $insert_new_price == true ){

	// insert logs
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
		'".mysql_real_escape_string($property_id)."',
		'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
		'{$serv} Price Changed',					
		'New Price $".$price.", Reason- ".$price_reason.", Details- ".$price_details."', 
		'" . date('Y-m-d H:i:s') . "'				
	)
	");

}

?>