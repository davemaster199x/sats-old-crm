<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$sa_id = mysql_real_escape_string($_POST['sa_id']);
$make  = mysql_real_escape_string($_POST['make']);
$model = mysql_real_escape_string($_POST['model']);
$power_type = mysql_real_escape_string($_POST['power_type']);
$detection_type = mysql_real_escape_string($_POST['detection_type']);
$expiry_manuf_date 	= mysql_real_escape_string($_POST['expiry_manuf_date']);
$loc_of_date = mysql_real_escape_string($_POST['loc_of_date']);
$remove_battery = mysql_real_escape_string($_POST['remove_battery']);
$hush_button = mysql_real_escape_string($_POST['hush_button']);
$common_faults  = mysql_real_escape_string($_POST['common_faults']);
$how_to_rem_al  = mysql_real_escape_string($_POST['how_to_rem_al']);
$adntl_notes = mysql_real_escape_string($_POST['adntl_notes']);
$country_id = $_SESSION['country_default'];


$sql = "
	UPDATE `smoke_alarms` 
	SET 
		`make` = '{$make}',
		`model` = '{$model}',
		`power_type` = '{$power_type}',
		`detection_type` = '{$detection_type}',
		`expiry_manuf_date` = '{$expiry_manuf_date}',
		`loc_of_date` = '{$loc_of_date}',
		`remove_battery` = '{$remove_battery}',
		`hush_button` = '{$hush_button}',
		`common_faults` = '{$common_faults}',
		`how_to_rem_al` = '{$how_to_rem_al}',
		`adntl_notes` = '{$adntl_notes}'
	WHERE `smoke_alarm_id` = {$sa_id}
";
mysql_query($sql);




// upload image
$front_image = $_FILES['front_image'];	
// dont upload if empty
if( $front_image['name']!='' ){
	
	
	// delete old image
	$del_sql = mysql_query("
		SELECT `front_image`
		FROM `smoke_alarms`
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	$del = mysql_fetch_array($del_sql);

	if( $del['front_image']!='' ){
		$file_to_delete = 'smoke_alarms/'.$del['front_image'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
				
	
	// upload image
	$uparams = array(
		'files' => $front_image,
		'id' => $sa_id,
		'upload_folder' => 'smoke_alarms',
		'image_size' => 760
	);
	$upload_ret = $crm->masterDynamicUpload($uparams);	

	// store image path
	mysql_query("
		UPDATE `smoke_alarms`
		SET `front_image` = '{$upload_ret['image_name']}'
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	
}


// upload image
$rear_image_1 = $_FILES['rear_image_1'];	
// dont upload if empty
if( $rear_image_1['name']!='' ){
				
	// delete old image
	$del_sql = mysql_query("
		SELECT `rear_image_1`
		FROM `smoke_alarms`
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	$del = mysql_fetch_array($del_sql);

	if( $del['rear_image_1']!='' ){
		$file_to_delete = 'smoke_alarms/'.$del['rear_image_1'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
	
	// upload image
	$uparams = array(
		'files' => $rear_image_1,
		'id' => $sa_id,
		'upload_folder' => 'smoke_alarms',
		'image_size' => 760
	);
	$upload_ret = $crm->masterDynamicUpload($uparams);	

	// store image path
	mysql_query("
		UPDATE `smoke_alarms`
		SET `rear_image_1` = '{$upload_ret['image_name']}'
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	
}



// upload image
$rear_image_2 = $_FILES['rear_image_2'];	
// dont upload if empty
if( $rear_image_2['name']!='' ){
		
	// delete old image
	$del_sql = mysql_query("
		SELECT `rear_image_2`
		FROM `smoke_alarms`
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	$del = mysql_fetch_array($del_sql);

	if( $del['rear_image_2']!='' ){
		$file_to_delete = 'smoke_alarms/'.$del['rear_image_2'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
	
	// upload image
	$uparams = array(
		'files' => $rear_image_2,
		'id' => $sa_id,
		'upload_folder' => 'smoke_alarms',
		'image_size' => 760
	);
	$upload_ret = $crm->masterDynamicUpload($uparams);	

	// store image path
	mysql_query("
		UPDATE `smoke_alarms`
		SET `rear_image_2` = '{$upload_ret['image_name']}'
		WHERE `smoke_alarm_id` = {$sa_id}
	");
	
}


header("location: /view_alarm_details.php?id={$sa_id}&success=1");

?>