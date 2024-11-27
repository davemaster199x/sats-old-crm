<?php

include('inc/init_for_ajax.php');

// init curl object        
$ch = curl_init();

// API key
// LIVE - SATS gmail
//$API_key = GOOGLE_DEV_API;
// TEST - my personal gmail
//$API_key = 'AIzaSyAlg-wLGSmPTbQ1Fgi5UXOPOhdLLtcbkdY';
$API_key = 'AIzaSyCBTFejS6It4Z4hIWzNNwlwN1mBzR_1MuU';

// google url shortener API to call
$c_url = "https://www.googleapis.com/urlshortener/v1/url?key={$API_key}";

$job_id = 69;
$encrypt = new cast128();
$encrypt->setkey(SALT);
$job_enc = utf8_encode($encrypt->encrypt($job_id));
$job_url_enc = rawurlencode($job_enc);
//$orig_url = "https://{$_SERVER['SERVER_NAME']}/view_entry_notice.php?letterhead=1&job_id={$job_url_enc}";
//$orig_url = "crmdev.sats.com.au/test_google_shortener.php";
$orig_url = "https://www.google.com";
$orig_url = "https://sats.com.au";
echo $url = $orig_url;

echo "<br /><br />";


//$url = "https://www.google.com/";

// POST data
$data = array("longUrl" => $url);                                                                   
$data_string = json_encode($data);   

// define options
$optArray = array(
	CURLOPT_URL => $c_url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => $data_string,
	CURLOPT_HTTPHEADER => array(                                                                          
		'Content-Type: application/json',                                                                                
		'Content-Length: ' . strlen($data_string)
	)   
);

// apply those options
curl_setopt_array($ch, $optArray);

// execute request and get response
echo $result = curl_exec($ch);

$result_json = json_decode($result);

$short_url = $result_json->id;

curl_close($ch);

//return $short_url;

?>