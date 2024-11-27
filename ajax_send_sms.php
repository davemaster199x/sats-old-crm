<?php

include('inc/init_for_ajax.php');
//include('inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;

// GET BLINK ACCESS TOKEN
$blink_access_token = $crm->getBlinkAccessToken();

$property_id = mysql_real_escape_string($_POST['property_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$tenant_mobile = mysql_real_escape_string($_POST['tenant_mobile']);
$sms_message = urldecode($_POST['sms_message']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$jr_no_show = mysql_real_escape_string($_POST['jr_no_show']);
$country_id = $_SESSION['country_default'];
$sent_by = $staff_id;
$sms_type = mysql_real_escape_string($_POST['sms_type']);
$sms_sent_to_tenant = mysql_real_escape_string($_POST['sms_sent_to_tenant']);

// get phone prefix
$j_sql = mysql_query("
	SELECT 
		*, 
		ajt.`type` AS serv_name, 
		j.`date` AS jdate,
		a.`address_1` AS a_address_1,
		a.`address_2` AS a_address_2,
		a.`address_3` AS a_address_3,
		a.`state` AS a_state,
		a.`postcode` AS a_postcode,
		a.`phone` AS a_phone
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
	LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
	WHERE j.`id` = {$job_id}
");
$j = mysql_fetch_array($j_sql);

// job date
$jdate = $j['jdate'];
// time of day
$time_of_day = $j['time_of_day'];
$serv_name = $j['serv_name'];

// agency 
$agency_id = $j['agency_id'];
$a_name = $j['agency_name'];
// address
$a_address_1 = $j['a_address_1'];
$a_address_2 = $j['a_address_2'];
$a_address_3 = $j['a_address_3'];
$a_state = $j['a_state'];
$a_postcode = $j['a_postcode'];
// phone
$a_phone = $j['a_phone'];

// country
// tenant number
$ctry_tenant_number = $j['tenant_number'];
// phone
$ctry_outgoing_email = $j['outgoing_email'];

// get phone prefix
$prefix = $j['phone_prefix'];

// tenant mobile 
$trim = str_replace(' ', '', trim($tenant_mobile));

// reformat number
$remove_zero = substr($trim, 1);
$mob = $prefix . $remove_zero;



//$sms_provider = '@email.smsglobal.com';
//$sms_provider = '@app.wholesalesms.com.au';
$sms_provider = SMS_PROVIDER;

$to = "{$mob}{$sms_provider}";
//$to = "vaultdweller123@gmail.com";

/*
  echo "To: {$to}<br />
  Message: {$sms_message}";
 */

// if link tag is provided
$short_url = '';
if (strpos($sms_message, '<link>') !== false) {

    if ($sms_type == 9 || $sms_type == 10) { // Entry Notice (EN)
        // auto insert en_date_issued
        mysql_query("
		UPDATE jobs
		SET `en_date_issued` = '" . date("Y-m-d") . "'
		WHERE `id` = {$job_id}
		");

        // intercept EN link
        // orig url
        $encrypt = new cast128();
        $encrypt->setkey(SALT);
        $job_enc = utf8_encode($encrypt->encrypt($job_id));
        $job_url_enc = rawurlencode($job_enc);
        //$orig_url = "https://{$_SERVER['SERVER_NAME']}/view_entry_notice.php?letterhead=1&job_id={$job_url_enc}";
        // short url generated
        //$short_url = $crm->shortenLink($orig_url,$blink_access_token);

        $orig_url = "https://{$_SERVER['SERVER_NAME']}/view_entry_notice_new.php?letterhead=1&i={$job_id}&m=" . md5($agency_id . $job_id);
//        $short_url = $orig_url;
        $short_url = $crm->getFDynamicLink($country_id, $orig_url);
        if (!$short_url) {
            $short_url = $orig_url;
        }
    }
}
$sms_message2 = str_replace("<link>", "{$short_url}", $sms_message);

//echo "orig url: {$orig_url}<br />";
//echo "short url: {$short_url}<br />";
// send SMS via API
$ws_sms = new WS_SMS($country_id, $sms_message2, $mob);
$sms_res = $ws_sms->sendSMS();
$ws_sms->captureSMSdata($sms_res, $job_id, $sms_message2, $mob, $sent_by, $sms_type);


// mark job sms as sms sent
if ($jr_no_show == 1) {
    $update_sms_sent = '`sms_sent_no_show`';
} else {
    $update_sms_sent = '`sms_sent`';
}
mysql_query("
	UPDATE jobs
	SET {$update_sms_sent} = '" . date("Y-m-d H:i:s") . "'
	WHERE `id` = {$job_id}
");

$sms_message3 = mysql_real_escape_string($sms_message2);
// insert logs
mysql_query("
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
		'SMS to {$sms_sent_to_tenant} <strong>\"{$sms_message3}\"</strong>',
		'{$job_id}',
		'{$staff_id}',
		'" . date("H:i") . "'
	)
");
?>

