<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

// ID
$iai_id = mysql_real_escape_string($_POST['iai_id']);

// The incident
$date_of_incident = mysql_real_escape_string($_POST['date_of_incident']);
$date_of_incident2 = $crm->formatDate($date_of_incident);
$time_of_incident = mysql_real_escape_string($_POST['time_of_incident']);
$datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";

$nature_of_incident = mysql_real_escape_string($_POST['nature_of_incident']);
$loc_of_inci = mysql_real_escape_string($_POST['loc_of_inci']);
$desc_inci = mysql_real_escape_string($_POST['desc_inci']);

// Injured Person Details
$ip_name = mysql_real_escape_string($_POST['ip_name']);
$ip_address = mysql_real_escape_string($_POST['ip_address']);
$ip_occu = mysql_real_escape_string($_POST['ip_occu']);
$ip_dob = mysql_real_escape_string($_POST['ip_dob']);
$ip_dob2 = $crm->formatDate($ip_dob);
$ip_tel_num = mysql_real_escape_string($_POST['ip_tel_num']);
$ip_employer = mysql_real_escape_string($_POST['ip_employer']);
$ip_noi = mysql_real_escape_string($_POST['ip_noi']);
$ip_loi = mysql_real_escape_string($_POST['ip_loi']);
$ip_onsite_treatment = mysql_real_escape_string($_POST['ip_onsite_treatment']);
$ip_fur_treat = mysql_real_escape_string($_POST['ip_fur_treat']);

// Witness Details
$witness_name = mysql_real_escape_string($_POST['witness_name']);
$witness_contact = mysql_real_escape_string($_POST['witness_contact']);

// Outcome
$loss_time_injury = mysql_real_escape_string($_POST['loss_time_injury']);
$reported_to = mysql_real_escape_string($_POST['reported_to']);

$confirm_chk = mysql_real_escape_string($_POST['confirm_chk']);

$country_id = $_SESSION['country_default'];

// ID
$delete_existing_photos = mysql_real_escape_string($_POST['delete_existing_photos']);

// upload
$upload = $crm->uploadIncidentReportUpload($_FILES);
$photo_of_incident = $upload['photo_of_incident'];

$sql_str = "
	UPDATE `incident_and_injury` 
	SET
		`datetime_of_incident` = '{$datetime_of_incident}',
		`nature_of_incident` = '{$nature_of_incident}',
		`location_of_incident` = '{$loc_of_inci}',
		`describe_incident` = '{$desc_inci}',
		`photo_of_incident` = '{$photo_of_incident}',
		`ip_name` = '{$ip_name}',
		`ip_address` = '{$ip_address}',
		`ip_occupation` = '{$ip_occu}',
		`ip_dob` = '{$ip_dob2}',
		`ip_tel_num` = '{$ip_tel_num}',
		`ip_employer` = '{$ip_employer}',
		`ip_noi` = '{$ip_noi}',
		`ip_loi` = '{$ip_loi}',
		`ip_onsite_treatment` = '{$ip_onsite_treatment}',
		`ip_further_treatment` = '{$ip_fur_treat}',
		`witness_name` = '{$witness_name}',
		`witness_contact` = '{$witness_contact}',
		`loss_time_injury` = '{$loss_time_injury}',
		`reported_to` = '{$reported_to}',
		`confirm_chk` = '{$confirm_chk}'
	WHERE `incident_and_injury_id` = {$iai_id}
";

mysql_query($sql_str);


// Multiple upload
/*
echo "<pre>";
print_r($_FILES);
echo "</pre>";
*/

$files = array(); 
foreach ($_FILES['photo_of_incident'] as $k => $l) {  
	$x = 0;
	foreach ($l as $i => $v) {    
		if (!array_key_exists($i, $files))
		$files[$i] = array();    
		$files[$i][$k] = $v;  		
	} 
}


/*
echo "<pre>";
print_r($files);
echo "</pre>";
*/


// delete all incident photos
mysql_query("
	DELETE
	FROM `incident_photos`
	WHERE `incident_and_injury_id` = {$iai_id}
	AND `incident_photos_id` IN ({$delete_existing_photos})	
");

$photo_of_incident_arr = []; 
foreach ($files as $file){

	
	if( $file['name'] !='' ){
		$upload = $crm->uploadIncidentReportUpload($file);
		$photo_of_incident_arr[] = $upload['photo_of_incident'];		
		// insert incident photo
		mysql_query("
			INSERT INTO 
			`incident_photos` (
				`incident_and_injury_id`,
				`image_name`
			)
			VALUES(
				{$iai_id},
				'{$upload['photo_of_incident']}'
			)
		");
	} 	
	
	
	
}


/*
echo "<pre>";
print_r($photo_of_incident_arr);
echo "</pre>";
*/




header("location: /incident_and_injury_report_details.php?id={$iai_id}&success=1");


?>