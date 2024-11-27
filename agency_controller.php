<?php
/*
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
*/

include('inc/init.php');
include ('inc/agency_class.php');


// profile
$agency_name = $_POST['agency_name'];
$franchise_group = $_POST['franchise_group'];
$abn = $_POST['abn'];
$street_number = $_POST['street_number'];
$street_name = $_POST['street_name'];
$suburb = $_POST['suburb'];
$phone = $_POST['phone'];
$state = $_POST['state'];
$postcode = $_POST['postcode'];
$region = $_POST['region'];
$country = $_POST['country'];
$totprop = $_POST['totprop'];
$user = $_POST['user'];
$pass = mysql_real_escape_string($_POST['pass']);
$encrypt = new cast128();
$encrypt->setkey(SALT);
$pass2 = addslashes(utf8_encode($encrypt->encrypt($pass)));
$agency_using = $_POST['agency_using'];
$pm_name = $_POST['pm_name'];

// agency contact
$ac_fname = $_POST['ac_fname'];
$ac_lname = $_POST['ac_lname'];
$ac_phone = $_POST['ac_phone'];
$ac_email = $_POST['ac_email'];
$agency_emails = $_POST['agency_emails'];
$account_emails = $_POST['account_emails'];

// preferences
$send_emails = $_POST['send_emails'];
$combined_invoice = $_POST['combined_invoice'];
$send_entry = $_POST['send_entry'];
$workorder_required = $_POST['workorder_required'];
$allow_indiv_pm = $_POST['allow_indiv_pm'];
$comment = $_POST['comment'];
$agency_specific_notes = $_POST['agency_specific_notes'];
$agency_hours = $_POST['agency_hours'];
$auto_renew = $_POST['auto_renew'];
$key_allowed = $_POST['key_allowed'];
$key_email_req = $_POST['key_email_req'];
$phone_call_req = $_POST['phone_call_req'];
$allow_dk = $_POST['allow_dk'];
$allow_en = $_POST['allow_en'];
$new_job_email_to_agent = $_POST['new_job_email_to_agent'];

$maintenance = $_POST['maintenance'];
$m_price = $_POST['m_price'];
$m_surcharge = $_POST['m_surcharge'];
$m_disp_surcharge = $_POST['m_disp_surcharge'];
$m_surcharge_msg = $_POST['m_surcharge_msg'];
$acc_name = $_POST['acc_name'];
$acc_phone = $_POST['acc_phone'];
$website = $_POST['website'];
$agency_special_deal = $_POST['agency_special_deal'];

$display_bpay = $_POST['display_bpay'];

// sales rep
$salesrep = $_POST['salesrep'];

// sales rep
$agen_stat = $_POST['agen_stat'];

// legal name
$legal_name = $_POST['legal_name'];

// instantiate class
$agency = new Agency_Class();

$address = "{$street_number} {$street_name} {$suburb} {$state} {$postcode}, {$_SESSION['country_name']}";
$coordinate = getGoogleMapCoordinates($address);

// add agency
if($agen_stat=='active'){
	$agency_id = $agency->add_agency($agency_name,$franchise_group,$street_number,$street_name,$suburb,$phone,$state,$postcode,$region,$country,$coordinate['lat'],$coordinate['lng'],$totprop,$agency_hours,$comment,$user,$pass2,$ac_fname,$ac_lname,$ac_phone,$ac_email,$agency_emails,$account_emails,$send_emails,$combined_invoice,$send_entry,$workorder_required,$allow_indiv_pm,$salesrep,$agen_stat,'',$legal_name,$auto_renew,$key_allowed,$key_email_req,$phone_call_req,$abn,$acc_name,$acc_phone,$allow_dk,$website,$allow_en,$agency_specific_notes,$new_job_email_to_agent,$display_bpay,$allow_upfront_billing,$agency_special_deal);	
	$agency->add_agency_maintenance($agency_id,$maintenance,$m_price,$m_surcharge,$m_disp_surcharge,$m_surcharge_msg);
	// send email
	$agency->send_mail($agency_name,$street_number,$street_name,$suburb,$phone,$state,$postcode,$region,$totprop,$ac_fname,$ac_lname,$ac_phone,$ac_email,$agency_emails,$account_emails,$send_emails,$combined_invoice,$send_entry,$workorder_required,$allow_indiv_pm,$auto_renew,$key_allowed,$key_email_req,$salesrep,$phone_call_req,$legal_name,$abn,$acc_name,$acc_phone,$allow_dk,$allow_en,$agency_specific_notes,$new_job_email_to_agent,$display_bpay,$allow_upfront_billing,$agency_special_deal);
}else{
	$agency_id = $agency->add_agency($agency_name,$franchise_group,$street_number,$street_name,$suburb,$phone,$state,$postcode,$region,$country,$coordinate['lat'],$coordinate['lng'],$totprop,$agency_hours,$comment,$user,$pass2,$ac_fname,$ac_lname,$ac_phone,$ac_email,$agency_emails,'','','','','','',$salesrep,$agen_stat,$agency_using,'','','','','',$abn,$acc_name,$acc_phone,$allow_dk,$website,$allow_en);	
}
// add pm
foreach($pm_name as $val){
	if($val!=""){
		$agency->add_property_managers($agency_id,$val);
	}
}

// add agency alarms
$alarm_pwr_id = $_POST['alarm_pwr_id'];
$alarm_is_approved = $_POST['alarm_is_approved'];
$alarm_price = $_POST['alarm_price'];

//print_r($alarm_is_approved);

foreach($alarm_pwr_id as $index=>$val){
	if($alarm_is_approved[$index]==1){
		$agency->add_agency_alarms($agency_id,$val,$alarm_price[$index]);
	}
}

// add agency sevices
$service_id = $_POST['service_id'];
$service_is_approved = $_POST['service_is_approved'];
$service_price = $_POST['service_price'];
foreach($service_id as $index=>$val){
	if($service_is_approved[$index]==1){
		$agency->add_agency_services($agency_id,$val,$service_price[$index]);
	}
}

header("location:/view_agency_details.php?id={$agency_id}&add_agency_ty_msg=1");




// test send mail
//echo $agency->send_mail($agency_name,$street_number,$street_name,$suburb,$phone,$state,$postcode,$region,$totprop,$ac_fname,$ac_lname,$ac_phone,$ac_email,$agency_emails,$account_emails,$send_emails,$combined_invoice,$send_entry,$workorder_required,$allow_indiv_pm,$salesrep);

?>