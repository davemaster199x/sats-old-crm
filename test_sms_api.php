<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');
include($_SERVER['DOCUMENT_ROOT'].'/inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];



// DATA
$sms_msg = "What came first the chicken or the egg?";

//$job_id = 9; // tes job ID
//$to_mob = '+64211933538'; // Ashley

$job_id = 45; // test job ID
$to_mob = '+61421449656'; // Thalia

$sent_by = $staff_id;
$sms_type = 13; // confirm booking




$ws_sms = new WS_SMS($country_id,$sms_msg,$to_mob);	

echo $ws_sms;
echo "<br /><br />";


$sms_res = $ws_sms->sendSMS();
$ws_sms->captureSMSdata($sms_res,$job_id,$sms_msg,$to_mob,$sent_by,$sms_type);
?>

