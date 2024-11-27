<?php
$domain = 'https://tradie.ourtradie.com.au';
$state = 'DCEeFWf45A53sdfKef524';
$client_id = 'A7FbQGPldqLVk4zjvHmy';
$client_secret = 'UGtw6cBNrCMQMABfrUbMOrVXQG0CH5D5';
$redirect_uri = 'https://crmdev.sats.com.au/test_api.php';

$authorize_url = 'https://tradie.ourtradie.com.au/api/authorize?response_type=code&authorize=1&state='.$state.'&client_id='.$client_id.'&redirect_uri='.$redirect_uri;
//$authorize_url = 'https://tradie.ourtradie.com.au/api/authorize?response_type=code&authorize=1&state=DCEeFWf45A53sdfKef524&client_id=A7FbQGPldqLVk4zjvHmy&redirect_uri=https://crmdev.sats.com.au/test_api.php';


function get_token() {
	$url = 'https://tradie.ourtradie.com.au/api/token';	

	$code = $_GET['code'];
	$client_id = 'A7FbQGPldqLVk4zjvHmy';
	$client_secret = 'UGtw6cBNrCMQMABfrUbMOrVXQG0CH5D5';
	$grant_type = 'authorization_code';

	$post = array(
		'code' 			   => $code,	
		'client_id' 	   => 'A7FbQGPldqLVk4zjvHmy',
		'client_secret' => 'UGtw6cBNrCMQMABfrUbMOrVXQG0CH5D5',
		'grant_type'    => 'authorization_code',
		'redirect_uri'    => 'https://crmdev.sats.com.au/test_api.php'
	);
		
	$url1= $url.'?grant_type='.$grant_type.'&code='.$code.'&client_id='.$client_id.'&client_secret='.$client_secret;

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST,true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

	$exec = curl_exec($ch);
	$info = curl_getinfo($ch);

	echo '<pre>';
	print_r($info);
	echo '</pre>';

	curl_close($ch);

	$json = json_decode($exec);
	
	echo '<pre>';
	print_r($json);
	echo '<pre>';
	
	return $json->refresh_token;
}

function refresh_token($token) {
	$url = 'https://tradie.ourtradie.com.au/api/token';	
	echo $token;
	$post = array(
		'refresh_token' => $token,	
		'client_id' 	   => 'A7FbQGPldqLVk4zjvHmy',
		'client_secret' => 'UGtw6cBNrCMQMABfrUbMOrVXQG0CH5D5',
		'grant_type'    => 'refresh_token',
		'redirect_uri'    => 'https://crmdev.sats.com.au/test_api.php'
	);
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST,true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);

	$exec = curl_exec($ch);
	curl_close($ch);

	$json = json_decode($exec);
	
	echo '<pre>';
	print_r($json);
	echo '<pre>';
}

function reject_job()
{
	
	$url = 'https://tradie.ourtradie.com.au/api/TradieJobRejectCancel';
	$Id = '290816';

	$post = array(
		'Id' 			   		=> $id,
		'CancelReason' => 'Test'
	);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Accept: text/html'
	));

	$exec = curl_exec($ch);
	$info = curl_getinfo($ch);

	echo '<pre>';
	print_r($info);
	echo '</pre>';

	curl_close($ch);

	$json = json_decode($exec);
	
	echo '<pre>';
	print_r($json);
	echo '<pre>';
}

//$ref_token = get_token();
//refresh_token($ref_token);
//get_token();

reject_job();