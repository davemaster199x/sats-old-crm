<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 2;

//$today = date('Y-m-d');
$date_30_days_old = date('Y-m-d',strtotime("-30 days"));

$sql = "
	DELETE trr
	FROM `tech_run_rows` AS trr
	LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
	WHERE tr.`date` < '{$date_30_days_old}'
";
mysql_query($sql);

//echo "<br /><br />";

$sql = "
	DELETE 
	FROM `tech_run`
	WHERE `date` < '{$date_30_days_old}'
";
mysql_query($sql);

if( mysql_affected_rows() > 0 ){
	
	// insert cron logs
	$cron_type_id = 11;
	$current_week = intval(date('W'));
	$current_year = date('Y');
	mysql_query("INSERT INTO cron_log (`type_id`, `week_no`, `year`, `started`, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})");
	
}
?>


