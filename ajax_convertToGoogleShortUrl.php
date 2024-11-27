<?php 

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;
// GET BLINK ACCESS TOKEN
$blink_access_token = $crm->getBlinkAccessToken();

// orig url
$en_hidden_orig_url = $_POST['en_hidden_orig_url'];
$tr_id = $_POST['tr_id'];
$en_time = $_POST['en_time'];
$country_id = $_SESSION['country_default'];
$fin_url = 'https://'.$en_hidden_orig_url."&tr_id={$tr_id}&en_time={$en_time}";

// short url generated
//echo $short_url = $crm->shortenLink($fin_url,$blink_access_token);
$short_url = $crm->getFDynamicLink($country_id, $fin_url);
if(!$short_url) {
    $short_url=$fin_url;
}
echo $short_url;


?>