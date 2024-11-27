<?php


include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getGoogleMapCoordinates($address){
	
	// init curl object        
	$ch = curl_init();
	
	$API_key = 'AIzaSyCSwCK4xGMN-k8We4-GHKD3p2CccCKn78E';

	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".rawurlencode($address)."&key={$API_key}";

	// define options
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);

	// apply those options
	curl_setopt_array($ch, $optArray);

	// execute request and get response
	$result = curl_exec($ch);


	$result_json = json_decode($result);
	
	$lat = $result_json->results[0]->geometry->location->lat;
	$lng = $result_json->results[0]->geometry->location->lng;
	
	//$coordinates = "{ lat: {$lat}, lng: {$lng} }";
	//$coordinates = $result_json->results[0]->geometry->location;
	
	$coordinates = array();
	
	$coordinates['lat'] = $lat;
	$coordinates['lng'] = $lng;
	
	curl_close($ch);
	
	return $coordinates;
	
}

// variables
$tech_id = $_GET['tech_id'];
$day = $_GET['day'];
$month = $_GET['month'];
$year = $_GET['year'];
$date = "{$year}-{$month}-{$day}";

$sql = "
	SELECT j.`id`, j.`property_id`, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`
	FROM jobs AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	WHERE j.`assigned_tech` ={$tech_id}
	AND j.date = '".$date."'
	AND p.deleted =0
	AND `lat` IS NULL
	AND `lng` IS NULL
	ORDER BY j.`sort_order`
";

$result = mysql_query($sql);

if(mysql_num_rows($result)>0){
	
	$ctr = 2;
	while($row = mysql_fetch_array($result)){
		
		
		echo $address = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}, Australia";
		$coordinate = getGoogleMapCoordinates($address);
		
		echo " ---- lat: {$coordinate['lat']} lng {$coordinate['lng']}<br />";

		// update lat lng
		mysql_query("
			UPDATE `property`
			SET `lat` = {$coordinate['lat']},
				`lng` = {$coordinate['lng']}
			WHERE `property_id` = {$row['property_id']}
		");
		
		// update sort
		mysql_query("
			UPDATE `jobs`
			SET `sort_order` = {$ctr}
			WHERE `id` = {$row['id']}
		");
		
		$ctr++;

	}
	
	//echo "has data";
	echo "<script>window.location='/lat_lng_caching.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}';</script>";
	
}else{
	
	//echo "finished";
	echo "<script>window.location='/maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}';</script>";

}





?>