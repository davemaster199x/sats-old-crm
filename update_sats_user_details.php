<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;



//uplaod file
//param files - files
//param id - id (staff/user id)
//param size - upload size
function uploadFileNew($params){
    
        // path
        //$upload_path = $_SERVER['DOCUMENT_ROOT'].'images/';
        $newFileName = "{$params['id']}_".rand()."_".date("YmdHis")."_".$_FILES["{$params['files']}"]['name'];
        
        // Check file size limit 4mb
        if ($_FILES["{$params['files']}"]["size"] > $params['size']) {
            return false;
        }
        
        if (move_uploaded_file($_FILES["{$params['files']}"]['tmp_name'], $params['uploadPath']."_".$newFileName)) {
            $ret['fileName'] = "_".$newFileName;
            return $ret;
        } else {
            return false;
        }
    
}


$user_id = $_POST['user_id'];
// name
$fname = $_POST['fname'];
$lname = $_POST['lname'];
// ipod
$ipad_mn = $_POST['ipad_mn'];
$ipad_sn = $_POST['ipad_sn'];
$ipad_imei = $_POST['ipad_imei'];
$ipad_ppsn = $_POST['ipad_ppsn'];
$ipad_expiry_date = ($_POST['ipad_expiry_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['ipad_expiry_date']))):'';
// phone
$phone_mn = $_POST['phone_mn'];
$phone_sn = $_POST['phone_sn'];
$phone_imei = $_POST['phone_imei'];
// latop
$laptop_make = $_POST['laptop_make'];
$laptop_sn = $_POST['laptop_sn'];
// other
$other_idn = $_POST['other_idn'];
$other_kn = $_POST['other_kn'];
$other_pid = $_POST['other_pid'];
$other_sz = $_POST['other_sz'];
$other_cc = $_POST['other_cc'];
// date
$start_date = ($_POST['start_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['start_date']))):'';
$dob = ($_POST['hid_dob']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['hid_dob']))):'';

// debit card
$debit_card_num = $_POST['debit_card_num'];
$debit_expiry_date = ($_POST['debit_expiry_date']!="")?date('Y-m-d',strtotime(str_replace("/","-",$_POST['debit_expiry_date']))):'';

//newly added
$email = $_POST['email'];
$number = $_POST['number'];
$pass = mysql_real_escape_string($_POST['pass']);
$status = $_POST['status'];
$class = $_POST['class'];
$working_days = $_POST['working_days'];
$working_days = implode(",",$_POST['working_days']);
$license_num = $_POST['license_num'];
$sa_position = $_POST['sa_position'];

$blue_card_num = $_POST['blue_card_num'];
$blue_card_expiry = ($_POST['blue_card_expiry']!="")?"'".$crm->formatDate($_POST['blue_card_expiry'])."'":'NULL';
$licence_expiry = ($_POST['licence_expiry']!="")?"'".$crm->formatDate($_POST['licence_expiry'])."'":'NULL';

// ICE
$ice_name = $_POST['ice_name'];
$ice_phone = $_POST['ice_phone'];

//Electrician's License
$electrician_license_num = $_POST['electrician_license_num'];
$electrician_license_expiry = ($_POST['electrician_license_expiry']!="")?"'".$crm->formatDate($_POST['electrician_license_expiry'])."'":'NULL';

// address
$address = $_POST['address'];
$is_electrician = $_POST['is_electrician'];

$display_on_wsr = ( $_POST['display_on_wsr'] )?$_POST['display_on_wsr']:0;
$recieve_wsr = ( $_POST['recieve_wsr'] )?$_POST['recieve_wsr']:0;

// accomodation
$accomodation_id = $_POST['accomodation_id'];

// encrypt password
$encrypt = new cast128();
$encrypt->setkey(SALT);
$pass2 = utf8_encode($encrypt->encrypt($pass));

if( $class!="" ){
	$class_str = " `ClassID` = '".mysql_real_escape_string($class)."', ";
}


// update tech accomodation address
if( $accomodation_id!='' ){
	
	$coordinate = getGoogleMapCoordinates($address);
	$add_acco_update_str = null;

	if( $number != '' ){
		$add_acco_update_str = "`phone` = '".mysql_real_escape_string($number)."',";
	}
	
	$update_acco_sql_str = "
	UPDATE `accomodation`
	SET 
		`address` = '".mysql_real_escape_string($address)."',
		{$add_acco_update_str}
		`lat` = '{$coordinate['lat']}',
		`lng` = '{$coordinate['lng']}'
	WHERE `accomodation_id` = ".mysql_real_escape_string($accomodation_id)."
	";

	mysql_query($update_acco_sql_str);
}



// vehicle
$update_sql_str = "
UPDATE `staff_accounts`
SET
	`FirstName` = '".mysql_real_escape_string($fname)."',
	`LastName` = '".mysql_real_escape_string($lname)."',
	`dob` = '".mysql_real_escape_string($dob)."',
	`phone_model_num` = '".mysql_real_escape_string($phone_mn)."',
	`phone_serial_num` = '".mysql_real_escape_string($phone_sn)."',
	`phone_imei` = '".mysql_real_escape_string($phone_imei)."',
	`other_id_num` = '".mysql_real_escape_string($other_idn)."',
	`other_key_num` = '".mysql_real_escape_string($other_kn)."',
	`other_plant_id` = '".mysql_real_escape_string($other_pid)."',
	`other_shirt_size` = '".mysql_real_escape_string($other_sz)."',
	`other_call_centre` = '".mysql_real_escape_string($other_cc)."',
	`ipad_model_num` = '".mysql_real_escape_string($ipad_mn)."',
	`ipad_serial_num` = '".mysql_real_escape_string($ipad_sn)."',
	`ipad_imei` = '".mysql_real_escape_string($ipad_imei)."',
	`ipad_prepaid_serv_num` = '".mysql_real_escape_string($ipad_ppsn)."',
	`laptop_make` = '".mysql_real_escape_string($laptop_make)."',
	`laptop_serial_num` = '".mysql_real_escape_string($laptop_sn)."',
	`start_date` = '".mysql_real_escape_string($start_date)."',
	`debit_card_num` = '".mysql_real_escape_string($debit_card_num)."',
	`ipad_expiry_date` = '".mysql_real_escape_string($ipad_expiry_date)."',
	`debit_expiry_date` = '".mysql_real_escape_string($debit_expiry_date)."',
	`Email` = '".mysql_real_escape_string($email)."',
	`ContactNumber` = '".mysql_real_escape_string($number)."',
	`Password` = '".mysql_real_escape_string($pass2)."',
	`active` = '".mysql_real_escape_string($status)."',
	{$class_str}
	`working_days` = '".mysql_real_escape_string($working_days)."',
	`license_num` = '".mysql_real_escape_string($license_num)."',
	`sa_position` = '".mysql_real_escape_string($sa_position)."',
	
	`blue_card_num` = '".mysql_real_escape_string($blue_card_num)."',
	`blue_card_expiry` = {$blue_card_expiry},
	`licence_expiry` = {$licence_expiry},
	
	`address` = '".mysql_real_escape_string($address)."',
	
	`ice_name` = '".mysql_real_escape_string($ice_name)."',
	`ice_phone` = '".mysql_real_escape_string($ice_phone)."',
	
	`elec_license_num` = '".mysql_real_escape_string($electrician_license_num)."',
	`elec_licence_expiry` = {$electrician_license_expiry},
	`is_electrician` = {$is_electrician},
	`display_on_wsr` = {$display_on_wsr},
	`recieve_wsr` = {$recieve_wsr}
	
WHERE `StaffID` = {$user_id}
";
mysql_query($update_sql_str);



$staff_sql = mysql_query("
	SELECT `TechID`
	FROM `staff_accounts`
	WHERE `StaffID` = {$user_id}
");
$s =  mysql_fetch_array($staff_sql);



// remove all states 
mysql_query("
	DELETE 
	FROM `staff_states` 
	WHERE `StaffID` = {$user_id}
");

// remove all country 
mysql_query("
	DELETE 
	FROM `country_access` 
	WHERE `staff_accounts_id` = {$user_id}
");



$countries = $_POST['countries'];
			
foreach($countries as $index=>$country_id){
	
	$default = $_POST['c_default'];
				
	if($country_id==$default){
		$def_set = '`default`, ';
		$def_val = '1, ';
	}else{
		$def_set = '';
		$def_val = '';
	}
	
	// add new country
	mysql_query("
		INSERT INTO
		`country_access` (
			`staff_accounts_id`,
			`country_id`,
			{$def_set}
			`status`
		)
		VALUES(
			{$user_id},
			{$country_id},
			{$def_val}
			1
		)
	");
	
	// add new state
	$state = $_POST["state_{$country_id}"];
	foreach($state as $val){
		$query = "
			INSERT INTO 
			staff_states(
				`StaffID`, 
				`country_id`,
				`StateID`
			) 
			VALUES (
				{$user_id},
				{$country_id},
				{$val}
			)";
			mysql_query($query);
	}
	
}



// dont upload if empty
// profile pic
// photo
$profile_pic = $_FILES['profile_pic'];
if($profile_pic['name']!=''){
	
	// delete old image
	$c_sql = mysql_query("
		SELECT `profile_pic`
		FROM `staff_accounts`
		WHERE `StaffID` = {$user_id}
	");
	$c = mysql_fetch_array($c_sql);

	if( $c['profile_pic']!='' ){
		$file_to_delete = 'staff_profile/'.$c['profile_pic'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
	
	
	// upload image
	$params = array(
		'files' => $profile_pic,
		'id' => $user_id,
		'upload_folder' => 'staff_profile',
		'image_size' => 200
	);
	$upload_ret = $crm->masterDynamicUpload($params);
	
	
	// store image path
	mysql_query("
		UPDATE `staff_accounts`
		SET `profile_pic` = '{$upload_ret['image_name']}'
		WHERE `StaffID` = {$user_id}
	");
	
	
}


// electrical licence
$electrical_license = $_FILES['electrical_license'];
$newFiles = 'electrical_license';
$electricUploadPath = $_SERVER['DOCUMENT_ROOT'].'images/electrical_license/';
if($electrical_license['name']!=''){
	
	// delete old image
	$c_sql = mysql_query("
		SELECT `electrical_license`
		FROM `staff_accounts`
		WHERE `StaffID` = {$user_id}
	");
	$c = mysql_fetch_array($c_sql);

	if( $c['electrical_license']!='' ){
		$file_to_delete = 'electrical_license/'.$c['electrical_license'];
		if( $file_to_delete!="" ){
			$crm->deleteFile($file_to_delete);
		}
	}
	
    
    $params = array(
		'files' => $newFiles,
		'id' => $user_id,
		'size' => 4000000,
        'uploadPath' => $electricUploadPath
	);
    $upload_ret = uploadFileNew($params); //return image name (fileName)
	
	
	// store image path
	mysql_query("
		UPDATE `staff_accounts`
		SET `electrical_license` = '{$upload_ret['fileName']}'
		WHERE `StaffID` = {$user_id}
	");
	
	
}

header("location: sats_users_details.php?id={$user_id}&success=2");



?>