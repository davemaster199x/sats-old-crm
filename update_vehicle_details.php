<?php
include('inc/init.php');
$crm = new Sats_Crm_Class;

$vehicles_id = $_POST['vehicles_id'];
$make = $_POST['make'];
$model = $_POST['model'];
$year = $_POST['year'];
$number_plate = $_POST['number_plate'];
$rego_expires = ($_POST['rego_expires']!="")?$crm->formatDate($_POST['rego_expires']):null;
$warranty_expires = ($_POST['warranty_expires']!="")?$crm->formatDate($_POST['warranty_expires']):null;
$fuel_type = $_POST['fuel_type'];
$etag_num = $_POST['etag_num'];
$serviced_by = $_POST['serviced_by'];
$fuel_card_num = $_POST['fuel_card_num'];
$purchase_date = ($_POST['purchase_date']!="")?$crm->formatDate($_POST['purchase_date']):null;
$purchase_price = $_POST['purchase_price'];
$ra_num = $_POST['ra_num'];
$ins_pol_num = $_POST['ins_pol_num'];
$policy_expires = ($_POST['policy_expires']!="")?$crm->formatDate($_POST['policy_expires']):null;
$staff_id = $_POST['staff_id'];
$og_driver = $_POST['og_driver'];
$fuel_card_pin = $_POST['fuel_card_pin'];
$vin_num = $_POST['vin_num'];
$plant_id = $_POST['plant_id'];
$cust_reg_num = $_POST['cust_reg_num'];
$serviced_booked = $_POST['serviced_booked'];
$key_number = $_POST['key_number'];

$kms = $_POST['kms'];
$next_service = $_POST['next_service'];

$engine_number = $_POST['engine_number'];

// finance
$finance_bank = $_POST['finance_bank'];
$finance_loan_num = $_POST['finance_loan_num'];
$finance_loan_terms = $_POST['finance_loan_terms'];
$finance_monthly_repayments = $_POST['finance_monthly_repayments'];
$finance_start_date = ($_POST['finance_start_date']!="")?$crm->formatDate($_POST['finance_start_date']):null;
$finance_end_date = ($_POST['finance_end_date']!="")?$crm->formatDate($_POST['finance_end_date']):null;


$vehicle_ownership = $_POST['vehicle_ownership'];
$insurer = $_POST['insurer'];


// vehicle
mysql_query("
	UPDATE `vehicles`
	SET
		`make` = '".mysql_real_escape_string($make)."',
		`model` = '".mysql_real_escape_string($model)."',
		`year` = '".mysql_real_escape_string($year)."',
		`number_plate` = '".mysql_real_escape_string($number_plate)."',
		`rego_expires` = '".mysql_real_escape_string($rego_expires)."',
		`warranty_expires` = '".mysql_real_escape_string($warranty_expires)."',
		`fuel_type` = '".mysql_real_escape_string($fuel_type)."',
		`etag_num` = '".mysql_real_escape_string($etag_num)."',
		`serviced_by` = '".mysql_real_escape_string($serviced_by)."',
		`next_service` = '".mysql_real_escape_string($next_service)."',
		`serviced_booked` = '".mysql_real_escape_string($serviced_booked)."',
		`fuel_card_num` = '".mysql_real_escape_string($fuel_card_num)."',
		`purchase_date` = '".mysql_real_escape_string($purchase_date)."',
		`purchase_price` = '".mysql_real_escape_string($purchase_price)."',
		`ra_num` = '".mysql_real_escape_string($ra_num)."',
		`ins_pol_num` = '".mysql_real_escape_string($ins_pol_num)."',
		`policy_expires` = '".mysql_real_escape_string($policy_expires)."',
		`StaffID` = '".mysql_real_escape_string($staff_id)."',
		`fuel_card_pin` = '".mysql_real_escape_string($fuel_card_pin)."',
		`vin_num` = '".mysql_real_escape_string($vin_num)."',
		`plant_id` = '".mysql_real_escape_string($plant_id)."',
		`cust_reg_num` = '".mysql_real_escape_string($cust_reg_num)."',
		`key_number` = '".mysql_real_escape_string($key_number)."',
		`engine_number` = '".mysql_real_escape_string($engine_number)."',		
		`finance_bank` = '".mysql_real_escape_string($finance_bank)."',
		`finance_loan_num` = '".mysql_real_escape_string($finance_loan_num)."',
		`finance_loan_terms` = '".mysql_real_escape_string($finance_loan_terms)."',
		`finance_monthly_repayments` = '".mysql_real_escape_string($finance_monthly_repayments)."',
		`finance_start_date` = '".mysql_real_escape_string($finance_start_date)."',
		`finance_end_date` = '".mysql_real_escape_string($finance_end_date)."',
		`vehicle_ownership` = '".mysql_real_escape_string($vehicle_ownership)."',
		`insurer` = '".mysql_real_escape_string($insurer)."'
	WHERE `vehicles_id` = {$vehicles_id}
");


// kms
$kms = mysql_real_escape_string($kms);
$date_updated = date('Y-m-d H:i:s');
mysql_query("INSERT INTO kms(vehicles_id,kms,kms_updated) VALUES($vehicles_id, $kms, '$date_updated')");

// image
$files = $_FILES['vehicle_image'];
// dont upload if empty
if( $files['name']!='' && $vehicles_id!='' ){
	
	// delete old image
	$c_sql = mysql_query("
		SELECT `image`
		FROM `vehicles`
		WHERE `vehicles_id` = {$vehicles_id}
	");
	$c = mysql_fetch_array($c_sql);

	if( $c['image']!='' ){
		$file_to_delete = 'vehicle/'.$c['image'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
	
	
	// upload image
	$params = array(
		'files' => $files,
		'id' => $vehicles_id,
		'upload_folder' => 'vehicle',
		'image_size' => 350
	);
	$upload_ret = $crm->masterDynamicUpload($params);
	
	
	// store image path
	mysql_query("
		UPDATE `vehicles`
		SET `image` = '{$upload_ret['image_name']}'
		WHERE `vehicles_id` = {$vehicles_id}
	");
	
	
}

//add log
if($og_driver!=$staff_id){
	
	$old_params = array(
		'staff_id' => $og_driver
	);
	$old_driver_name_q = $crm->getStaffAccount($old_params);
	$old_driver_row = mysql_fetch_array($old_driver_name_q);
	$old_driver_name = ($old_driver_row['StaffID']=="") ? "NULL" :"{$old_driver_row['FirstName']} {$old_driver_row['LastName']}";

	$new_params = array(
		'staff_id' => $staff_id
	);
	$new_driver_name_q = $crm->getStaffAccount($new_params);
	$new_driver_row = mysql_fetch_array($new_driver_name_q);
	$new_driver_name = "{$new_driver_row['FirstName']} {$new_driver_row['LastName']}";

	$log_details = "Driver changed from {$old_driver_name} to $new_driver_name";
	mysql_query("
		INSERT INTO 
		`vehicles_log`(
			`vehicles_id`,
			`date`,
			`details`,
			`staff_id`
		)
		VALUES(
			'".mysql_real_escape_string($vehicles_id)."',
			'".date('Y-m-d H:i:s')."',
			'".mysql_real_escape_string($log_details)."',
			'{$_SESSION['USER_DETAILS']['StaffID']}'
		)
	");
}

//add user details log
if($og_driver!=""){
	mysql_query("
		INSERT INTO 
		`user_log`(
			`date`,
			`details`,
			`staff_id`,
			`added_by`
		)
		VALUES(
			'".date('Y-m-d H:i:s')."',
			'".mysql_real_escape_string($log_details)."',
			'".mysql_real_escape_string($og_driver)."',
			'".$_SESSION['USER_DETAILS']['StaffID']."'
		)
	");
}

if($staff_id!=""){
	mysql_query("
		INSERT INTO 
		`user_log`(
			`date`,
			`details`,
			`staff_id`,
			`added_by`
		)
		VALUES(
			'".date('Y-m-d H:i:s')."',
			'".mysql_real_escape_string($log_details)."',
			'".mysql_real_escape_string($staff_id)."',
			'".$_SESSION['USER_DETAILS']['StaffID']."'
		)
	");
}


header("location: view_vehicle_details.php?id={$vehicles_id}&success=2");
?>