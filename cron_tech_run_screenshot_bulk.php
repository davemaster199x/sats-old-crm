<?php

include('inc/init_for_ajax.php');

// date
// tommorow
//$date = date('Y-m-d',strtotime('+1 day'));
$country_id = 1;
//$date = date('Y-m-d');
$date = '2016-09-16';

$tr_str = "
	SELECT * 
	FROM  `tech_run` 
	WHERE  `date` =  '{$date}'
	AND `screenshot` = 0
	AND `country_id` = {$country_id}
	LIMIT 1
";
$tr_sql = mysql_query($tr_str);


if( mysql_num_rows($tr_sql)>0 ){
	
	$tr = mysql_fetch_array($tr_sql);
	header("location: /view_tech_schedule_day2.php?tr_id={$tr['tech_run_id']}&bulk_screenshot=1");
	
}else{
	echo 'Cron finished executing';
	die();
}  



?>