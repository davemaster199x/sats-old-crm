<?php 

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$tenant_mobile = mysql_real_escape_string($_POST['tenant_mobile']);
$sms_message = mysql_real_escape_string($_POST['sms_message']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// get phone prefix
$p_sql = mysql_query("
	SELECT *
	FROM `property` AS p
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
	WHERE p.`property_id` ={$property_id}
");
$p = mysql_fetch_array($p_sql);

// get phone prefix
$prefix = $p['phone_prefix'];

// tenant mobile 
$trim = str_replace(' ', '', trim($tenant_mobile));

// reformat number
$remove_zero = substr($trim ,1);
$mob = $prefix.$remove_zero;

//$sms_provider = '@email.smsglobal.com';
//$sms_provider = '@app.wholesalesms.com.au';
$sms_provider = SMS_PROVIDER;

$to = "{$mob}{$sms_provider}";
//$to = "vaultdweller123@gmail.com";


echo "To: {$to}<br />
Message: {$sms_message}";



// intercept entry notice link
// orig url
$encrypt = new cast128();
$encrypt->setkey(SALT);
$job_enc = utf8_encode($encrypt->encrypt($job_id));
$job_url_enc = rawurlencode($job_enc);
$orig_url = "{$_SERVER['SERVER_NAME']}/view_entry_notice.php?letterhead=1&job_id={$job_url_enc}";
// short url generated
$short_url = convertToGoogleUrlShortener($orig_url);
$sms_message2 = str_replace("<link>","{$short_url}",$sms_message);

/*
// send sms
if(mail($to,'',$sms_message2)){
	
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
			'SMS Sent, Details- ".mysql_real_escape_string($sms_message2)."', 
			'{$job_id}',
			'{$staff_id}',
			'".date("H:i")."'
		)
	");
	
	echo 1;
}else{
	echo 0;
}
*/

?>

