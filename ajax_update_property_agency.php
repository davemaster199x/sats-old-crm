<?php

include('inc/init_for_ajax.php');

$current_agency = $_POST['current_agency'];
$new_agency = $_POST['new_agency'];
$props = $_POST['props'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

function ajax_getAgencyName($agency_id){
	$a_sql = mysql_query("
		SELECT `agency_name`
		FROM `agency`
		WHERE `agency_id` = {$agency_id}
	");
	$a = mysql_fetch_array($a_sql);
	return $a['agency_name'];
}

echo $current_agency_name = ajax_getAgencyName($current_agency);
echo " - ";
echo $new_agency_name = ajax_getAgencyName($new_agency);


foreach($props as $prop_id){
	
	// vehicles
	echo $p_sql = "
		UPDATE `property`
		SET
			`agency_id` = ".mysql_real_escape_string($new_agency).",			
			`propertyme_prop_id` = NULL,
			`palace_prop_id` = NULL
		WHERE `property_id` = ".mysql_real_escape_string($prop_id)."
	";
	mysql_query($p_sql);
	
	// insert logs
	echo $l_sql = "
		INSERT INTO 
		property_event_log(
			`property_id`, 
			`staff_id`, 
			`event_type`, 
			`event_details`, 
			`log_date`
		)
		VALUES(
			{$prop_id}, 
			{$staff_id}, 
			'Agency Changed', 
			'Property changed from {$current_agency_name} to {$new_agency_name}', 
			'".date("Y-m-d H:i:s")."'
			
		)
	";
	mysql_query($l_sql);
	
}


?>