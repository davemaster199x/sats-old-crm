<?php

include('inc/init_for_ajax.php');

// Initiate job class
$jc = new Job_Class();

// data
$map_routes_id	 = mysql_real_escape_string($_POST['map_routes_id']);
$sub_regions = mysql_real_escape_string($_POST['sub_regions']);
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$country_id = $_SESSION['country_default'];


echo $str = "
	UPDATE `map_routes`
	SET `sub_regions` = '{$sub_regions}'
	WHERE `map_routes_id` = {$map_routes_id}
";

mysql_query($str);





$job_count = getJobsTotalRoutes($tech_id,$date,$country_id);
$key_count = getTotalKeyRoutes($tech_id,$date,$country_id);

$last_index = ($job_count+$key_count)+2;

$j_sql = getJobsByRegionSort($tech_id,$date,$sub_regions,$country_id);

while( $j = mysql_fetch_array($j_sql) ){
	echo $str3 = "
		UPDATE `jobs`
		SET `sort_order` = ".($last_index++)."
		WHERE id = {$j['jid']}
	";
	mysql_query($str3);
}

?>