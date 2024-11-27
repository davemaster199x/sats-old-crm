<?php

include('inc/init_for_ajax.php');

$job_arr = $_POST['job_id'];
$job_time = $_POST['job_time'];

foreach($job_arr as $job_id){
	// update job time of day
	mysql_query("
		UPDATE `jobs`
		SET
			`time_of_day` = '".mysql_real_escape_string($job_time)."'
		WHERE `id` = '".mysql_real_escape_string($job_id)."'
	");
}


?>