<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$ajt_id = $_POST['ajt_id'];

// update price
$ps_sql = mysql_query("
	SELECT ps.`price` AS ps_price
	FROM `property_services` AS ps 
	LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
	WHERE ps.`property_id` = {$property_id}
	AND ps.`alarm_job_type_id` = {$ajt_id}
");	
$ps_row = mysql_fetch_array($ps_sql);
echo $ps_row['ps_price'];

?>