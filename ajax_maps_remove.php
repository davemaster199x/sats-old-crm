<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];

/*
foreach($job_id as $val){
	
	// update job
	mysql_query("
		UPDATE `jobs`
		SET 
			`status` = 'To Be Booked',
			`date` = NULL,
			`time_of_day` = NULL,
			`tech_id` = 1,
			`ts_completed` = 0,
			`job_reason_id` = 0,
			`door_knock` = 0,
			`completed_timestamp` = NULL,
			`tech_notes` = NULL
		WHERE `id` = {$val}
	");
	
}
*/

//echo print_r($job_id);

echo "<h1>Property Id</h1>";
echo implode(",",$job_id);


?>