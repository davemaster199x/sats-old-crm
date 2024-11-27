<?php

include('inc/init_for_ajax.php');

// data
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$start = mysql_real_escape_string($_POST['start']);
$end = mysql_real_escape_string($_POST['end']);

// start point
if($start!=""){
	
	// start
	// get accomodation address
	$a_sql = mysql_query("
		SELECT *
		FROM `accomodation`
		WHERE `accomodation_id` = {$start}
		AND `lat` IS NULL
		AND `lng` IS NULL
	");

	if(mysql_num_rows($a_sql)>0){
		$a = mysql_fetch_array($a_sql);
		// get geocode
		$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
		// update agency lat/lng
		mysql_query("
			UPDATE `accomodation`
			SET 
				`lat` = '{$coor['lat']}',
				`lng` = '{$coor['lng']}'
			WHERE `accomodation_id` = {$start}
		");
	}
	
}

// end point
if($end!=""){
	
	// end
	// get accomodation address
	$a_sql = mysql_query("
		SELECT *
		FROM `accomodation`
		WHERE `accomodation_id` = {$end}
		AND `lat` IS NULL
		AND `lng` IS NULL
	");

	if(mysql_num_rows($a_sql)>0){
		$a = mysql_fetch_array($a_sql);
		// get geocode
		$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
		// update agency lat/lng
		mysql_query("
			UPDATE `accomodation`
			SET 
				`lat` = '{$coor['lat']}',
				`lng` = '{$coor['lng']}'
			WHERE `accomodation_id` = {$end}
		");
	}

	
}


// check if route already set
$mp_sql = mysql_query("
	SELECT *
	FROM `map_routes`
	WHERE `tech_id` = {$tech_id}
	AND `date` = '{$date}'
");

// map route data already created, just update it start and end point then
if( $start!="" || $end!="" ){
	
	if(mysql_num_rows($mp_sql)>0){
	
		// update start and end point
		$sql = "UPDATE `map_routes`
			SET `start` = '{$start}',
				`end` = '{$end}'
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'";
		mysql_query($sql);
		
	}else{
		
		// add map route
		$sql = "
			INSERT INTO
			`map_routes` (
				`tech_id`,
				`date`,
				`start`,
				`end`
			)
			VALUES(
				{$tech_id},
				'{$date}',
				'{$start}',
				'{$end}'
			)
		";
		mysql_query($sql);
		
	}
	
}
	





?>