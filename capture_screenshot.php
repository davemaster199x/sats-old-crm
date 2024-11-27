<?php

include('inc/init.php');

$country_id = $_POST['tr_country_id'];
$crm = new Sats_Crm_Class;

// data
$image_data = $_POST['image_data'];
$tr_id = $_POST['tr_id'];
$bulk_screenshot = $_POST['bulk_screenshot'];
$tech_name = $_POST['tech_name'];
$tr_date = $_POST['tr_date'];

// get country
$cntry_sql = getCountryViaCountryId($country_id);
$cntry = mysql_fetch_array($cntry_sql);
$country_iso_uc = strtoupper($cntry['iso']);
$country_iso_lw = strtolower($cntry['iso']);


$country_folder = "{$country_iso_lw}/";

//Show the image
//echo '<img src="'.$image_data.'" />';

//Get the base-64 string from data
$filteredData=substr($image_data, strpos($image_data, ",")+1);
 
//Decode the string
$unencodedData=base64_decode($filteredData);
 
//Save the image
$image_name = "vts_screenshot_{$country_iso_lw}_{$tr_id}_".date('YmdHis').".png";

// path to screenshot image
$path_to_img = "{$country_folder}{$image_name}";

file_put_contents("images/screenshot/{$path_to_img}", $unencodedData);

// email starts here
$to = 'vaultdweller123@gmail.com';
$from = "Smoke Alarm Testing Services <{$cntry['outgoing_email']}>";
$cc = 'danielk@sats.com.au';
$subject = "Tech Run({$country_iso_uc}) - {$tech_name} - ".date('d/m/Y',strtotime($tr_date));



// message
$message = '
<html>
<head>
  <title>'.$subject.'</title>
</head>
<body>
	<h1>Screenshots</h1>
  <img src="https://'.$crm->getDynamicDomain().'.sats.com.au/images/screenshot/'.$path_to_img.'" />
</body>
</html>
';


$params = array(
	'to' => $to,
	'from' => $from,
	'subject' => $subject,
	'message' => $message,
	'cc' => $cc
);
$crm->nativeEmail($params);


// capture screenshot on bulk
if($bulk_screenshot==1){
	
	mysql_query("
		UPDATE `tech_run`
		SET `screenshot` = 1
		WHERE `tech_run_id` = {$tr_id}
		AND `country_id` = {$country_id}
	");
	
	// run the CRON
	//header("location: /cron_tech_run_screenshot_bulk.php");
	header("location: /cron_tech_run_screenshot_bulk_{$country_iso_lw}.php");
	
}else{
	
	header("location: /view_tech_schedule_day2.php?tr_id={$tr_id}&screenshot_success=1");
	
}


?>