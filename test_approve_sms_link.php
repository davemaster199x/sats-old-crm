<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;
$country_id = $_SESSION['country_default'];

// intercept entry notice link
// orig url
$job_id = 2;
$encrypt = new cast128();
$encrypt->setkey(SALT);
$job_enc = utf8_encode($encrypt->encrypt($job_id));
$job_url_enc = rawurlencode($job_enc);
$orig_url = "{$_SERVER['SERVER_NAME']}/confirm_sms.php?job_id={$job_url_enc}";
// short url generated
$short_url = convertToGoogleUrlShortener($orig_url);
echo '
SATS would just like to confirm the appointment made today for the 08/12/2017 @ 8am to service the Smoke Alarms at 8A Pacific Highway Pinny Beach. 
Please ensure someone is home to  provide access. 
SATS 1300 55 21 99. To confirm click <a href="'.$short_url.'">here</a>
';
?>



