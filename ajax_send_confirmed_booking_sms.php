<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$booked_with_tenant_name = trim(mysql_real_escape_string($_POST['booked_with_tenant_name']));
$booked_with_tenant_mob = trim(mysql_real_escape_string($_POST['booked_with_tenant_mob']));
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$sent_by = $staff_id;
$country_id = $_SESSION['country_default'];

$sms_type = 16; // SMS Reply (Booking Confirmed)
$sms_temp_params = array(
    'sms_api_type_id' => $sms_type,
    'job_id' => $job_id
);        
$sms_message = $crm->get_parsed_sms_template($sms_temp_params);

// get country data
$cntry_sql = getCountryViaCountryId();
$cntry = mysql_fetch_array($cntry_sql);
$prefix = $cntry['phone_prefix'];


if ( $booked_with_tenant_mob != '' ) {

    // trimmed
    $trimmed_mob = str_replace(' ', '', $booked_with_tenant_mob);

    // reformat number
    $remove_zero = substr($trimmed_mob, 1);
    $tent_mob = $prefix . $remove_zero;
    
    // send SMS via API
    $ws_sms = new WS_SMS($country_id, $sms_message, $tent_mob);
    $sms_res = $ws_sms->sendSMS();
    $ws_sms->captureSMSdata($sms_res, $job_id, $sms_message, $tent_mob, $sent_by, $sms_type);
    

    // insert logs
    $job_log_str1 = "
    INSERT INTO 
    `job_log` (
        `contact_type`,
        `eventdate`,
        `comments`,
        `job_id`, 
        `staff_id`,
        `eventtime`
    ) 
    VALUES (
        'SMS sent',
        '" . date('Y-m-d') . "',
        'SMS to {$booked_with_tenant_name} <strong>\"" . mysql_real_escape_string(trim($sms_message)) . "\"</strong>', 
        '{$job_id}',
        '{$staff_id}',
        '" . date("H:i") . "'
    )
    ";

    mysql_query($job_log_str1);

}

?>