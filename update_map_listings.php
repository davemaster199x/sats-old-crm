<?php

include('inc/init.php');

$tech_id = $_GET['tech_id'];
$day = $_GET['day'];
$month = $_GET['month'];
$year = $_GET['year'];
$date = "{$year}-{$month}-{$day}";

function checkKeySameSortOrder($tech_id,$date,$i){
	return mysql_query("
		SELECT *
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`tech_id` = {$tech_id}
		AND kr.`date` = '{$date}'
		AND kr.`sort_order` = {$i}
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$_SESSION['country_default']}
	");
}

$str = "
	SELECT j.`id` 
	FROM jobs AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE j.`assigned_tech` ={$tech_id}
	AND j.date = '{$date}'
	AND p.`deleted` =0
	AND a.`country_id` = {$_SESSION['country_default']}
	ORDER BY j.`sort_order`
";

$sql = mysql_query($str);

$i = 2;
while($row = mysql_fetch_array($sql)){
	
	
	$key_sql = checkKeySameSortOrder($tech_id,$date,$i);
	
	// there is key that has that sort number, so avoid overwriting
	while(mysql_num_rows($key_sql)>0){
		++$i; //increment current index to avoid overwriting keys existing sort order
		$key_sql = checkKeySameSortOrder($tech_id,$date,$i);
	}
	
	// update sort order
	$str2 = "
		UPDATE `jobs`
		SET 
			`sort_order` = {$i},
			`sort_date` = '{$date}'
		WHERE `id` = {$row['id']}
	";
	mysql_query($str2);
	$i++;
	
}

$kr_sql_str = "
	SELECT *
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`tech_id` = {$tech_id}
		AND kr.`date` = '{$date}'
		AND kr.`sort_order` >= {$i}
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$_SESSION['country_default']}
";
$kr_sql = mysql_query($kr_sql_str);
if(mysql_num_rows($kr_sql)>0){
	
	while($kr = mysql_fetch_array($kr_sql)){
		//echo "<br />";
		$sql2 = "
			UPDATE `key_routes`
			SET `sort_order` = {$i}
			WHERE `key_routes_id` = {$kr['key_routes_id']}
		";
		mysql_query($sql2);
		$i++;
	}
	
}

if($_GET['tech_schedule']==1){
	header("location: view_tech_schedule_day.php?id={$tech_id}&day={$day}&month={$month}&year={$year}");
}else{
	header("location: maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}");
}

?>