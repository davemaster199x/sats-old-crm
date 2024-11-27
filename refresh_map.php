<?php

include('inc/init.php');

$tech_id = $_GET['tech_id'];
$day = $_GET['day'];
$month = $_GET['month'];
$year = $_GET['year'];
$date = "{$year}-{$month}-{$day}";


$sql = mysql_query("
	SELECT j.`id` 
	FROM jobs AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE j.`assigned_tech` = {$tech_id}
	AND j.date =  '{$date}'
	AND p.deleted =0
	AND a.`country_id` = {$_SESSION['country_default']}
	AND (
		j.sort_date !=  '{$date}'
		OR j.`sort_date` IS NULL
	)
");

$job_count = getJobsTotalRoutes($tech_id,$date);
$key_count = getTotalKeyRoutes($tech_id,$date);

$last_index = ($job_count+$key_count)+2;

while($row = mysql_fetch_array($sql)){
			
	// update sort
	mysql_query("
		UPDATE `jobs`
		SET 
			`sort_order` = {$last_index},
			`sort_date` = '{$date}'
		WHERE `id` = {$row['id']}
	");
	
	$last_index++;
	
}


header("location: maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}");

?>