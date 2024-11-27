<?php

if(isset($_REQUEST['search'])){
	$search = urlencode($_REQUEST['search']);
}
if(isset($_REQUEST['operation'])){
	$operation = urlencode($_REQUEST['operation']);
}


// init curl object        
$ch = curl_init();

//$url = 'http://api.addressify.com.au/address/autoComplete?api_key=7530f9a6-0cbb-413c-986b-a47603e528a7&term=1+George+st+t&state=nsw&max_results=5';

$api_key = '0EE75AC7-F8F2-47CE-8893-2F82E246E6B2';

$url = "http://api.addressify.com.au/address/{$operation}?api_key={$api_key}&term={$search}";

// define options
$optArray = array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => false
);

// apply those options
curl_setopt_array($ch, $optArray);

//echo $url;

// execute request and get response
echo $result = curl_exec($ch);


/*
$result_json = json_decode($result);

echo json_encode($result_json);
*/

/*
echo "<pre>";
print_r($result_json);
echo "</pre>";
*/

curl_close($ch);

?>