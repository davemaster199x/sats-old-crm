<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = date("Y-m-d",strtotime(str_replace("/","-",$date)));

foreach($job_id as $val){
	
	// update job
	$sql = "
		UPDATE `jobs`
		SET 
			`status` = 'To Be Booked',
			`assigned_tech` = {$tech_id},
			`date` = '{$date2}'
		WHERE `id` = {$val}
	";
	mysql_query($sql);
	
}


?>