<?php

include('inc/init.php');

$tech_id = $_GET['tech_id'];
$day = $_GET['day'];
$month = $_GET['month'];
$year = $_GET['year'];
$date = "{$year}-{$month}-{$day}";

$str = "
	SELECT * , j.status AS j_status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, sa.FirstName, sa.LastName, p.property_id, j.id AS j_id, j.service
	FROM jobs AS j
	LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	WHERE j.`assigned_tech` ={$tech_id}
	AND j.date = '{$date}'
	AND p.deleted =0
	AND a.`country_id` = {$_SESSION['country_default']}
	ORDER BY j.`sort_order`
";

$sql = mysql_query($str);

$i = 2;
while($row = mysql_fetch_array($sql)){
	$str2 = "
		UPDATE `jobs`
		SET 
			`sort_order` = {$i},
			`sort_date` = '{$date}'
		WHERE `id` = {$row['j_id']}
	";
	mysql_query($str2);
	$i++;
}

// KEYS
$tot_map_routes = getJobsTotalRoutes($tech_id,$date)+2;
$kr_sql_str = "
	SELECT *
	FROM `key_routes` AS kr
	LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
	WHERE kr.`tech_id` = {$tech_id}
	AND kr.`date` = '{$date}'
	AND kr.`deleted` IS NULL
	AND a.`country_id` = {$_SESSION['country_default']}
";
$kr_sql = mysql_query($kr_sql_str);
if(mysql_num_rows($kr_sql)>0){
	
	while($kr = mysql_fetch_array($kr_sql)){
		//echo "<br />";
		$sql2 = "
			UPDATE `key_routes`
			SET `sort_order` = {$tot_map_routes}
			WHERE `key_routes_id` = {$kr['key_routes_id']}
		";
		mysql_query($sql2);
		$tot_map_routes++;
	}
	
}

if($_GET['tech_schedule']==1){
	header("location: view_tech_schedule_day.php?id={$tech_id}&day={$day}&month={$month}&year={$year}");
}else{
	header("location: maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}");
}


?>