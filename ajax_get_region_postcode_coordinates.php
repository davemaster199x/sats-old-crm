<?php

include('inc/init_for_ajax.php');

// Get Region Postcodes
$pr_id = mysql_real_escape_string($_POST['pr_id']);
$country_id = $_SESSION['country_default'];

$pr_sql = mysql_query("
	SELECT * 
	FROM  `postcode_regions` 
	WHERE  `postcode_region_id` ={$pr_id}
	AND `country_id` = {$country_id}
");
$pr = mysql_fetch_array($pr_sql);
$region_pc = str_replace(',,',',',$pr['postcode_region_postcodes']);
$gm_polygon_points = $pr['gm_polygon_points'];
$display_pins = $pr['display_pins'];

// get custom pins
$cust_pins_sql = mysql_query("
	SELECT * 
	FROM  `postcode_regions_custom_pins` 
	WHERE `postcode_region_id` = {$pr_id}
	AND `country_id` = {$country_id}
	ORDER BY `postcode_regions_custom_pins_id` ASC
");
while( $cust_pins = mysql_fetch_array($cust_pins_sql) ){
	// append custom pin id, needed for drag position update
	$custom_pins_arr[] = str_replace('{','{"prcp_id":"'.$cust_pins['postcode_regions_custom_pins_id'].'", ',$cust_pins['coordinates']);
}
$custom_pins_merged = implode(',',$custom_pins_arr);
$custom_pins = stripslashes($custom_pins_merged);


// get to be booked jobs
$job_sql = mysql_query("
	SELECT COUNT(j.`id`) AS jcount
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$country_id}
	AND j.`status` = 'To Be Booked'
	AND p.`postcode` IN ( {$region_pc} )
");
$job = mysql_fetch_array($job_sql);


// get postcode coordinates
$pcg_sql = mysql_query("
	SELECT DISTINCT (
	`postcode`
	),  `latitude` ,  `longitude` 
	FROM  `postcodes_geo` 
	WHERE  `postcode` 
	IN ( {$region_pc} ) 
");

$coor_arr = array();
while( $pcg = mysql_fetch_array($pcg_sql) ){ 
	$coor_arr[] = array(
		'lat' => $pcg['latitude'],
		'lng' => $pcg['longitude']		
	);
	$i++;
}

$coor_arr_merged = array(
	'coordinates' => $coor_arr,
	'gm_polygon_points' => $gm_polygon_points,
	'display_pins' => $display_pins,
	'to_be_booked_job' => $job['jcount'],
	'custom_pins' => $custom_pins
);


echo json_encode($coor_arr_merged);


?>