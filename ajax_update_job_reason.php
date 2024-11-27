<?php

include('inc/init_for_ajax.php');

$job_id_arr = $_POST['job_id_arr'];

$jr_id = mysql_real_escape_string($_POST['jr_id']);
$comment = mysql_real_escape_string($_POST['comment']);

foreach( $job_id_arr as $job_id ){

	$job_id = mysql_real_escape_string($job_id);
				
	// get job reason
	$jr_sql = mysql_query("
		SELECT *
		FROM `job_reason`
		WHERE `job_reason_id` = {$jr_id}
	");
	$jr = mysql_fetch_array($jr_sql);
	
	// job logs
	$log_msg = $jr['log_message'];
	$ct = $jr['name'];

	// update job
	$query = "
		UPDATE jobs 
		SET 
			`status` = 'Pre Completion', 
			`job_reason_id` = '{$jr_id}',
			`job_reason_comment` = '{$comment}',
			`completed_timestamp` = '".date("Y-m-d H:i:s")."'
		WHERE `id` = '{$job_id}'
	";	
	mysql_query($query);
	
	// insert logs
	$log_date = date('Y-m-d');
	$log_message = "{$comment}";
	$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
	
	$dk_sql = mysql_query("
		SELECT `door_knock`
		FROM `jobs`
		WHERE `id` = '{$job_id}'
	");
	$dk = mysql_fetch_array($dk_sql);
	
	if($dk['door_knock']==1){
		$ct2 = $ct." DK";
	}else{
		$ct2 = $ct;
	}



	// insert logs	
	$log_sql_str = "
	INSERT INTO 
		job_log (
			`staff_id`, 
			`comments`, 
			`eventdate`, 
			`contact_type`, 
			`job_id`,
			`eventtime`
		) 
		VALUES (
			'{$staff_id}', 
			'{$log_message}', 
			'{$log_date}', 
			'".mysql_real_escape_string($ct2)."', 
			'{$job_id}',
			'".date("H:i")."'
		)
	";	
	mysql_query($log_sql_str);
	
	


	//INSERT TO NEW TABLE CALLED 'jobs_not_completed' (by: gherx)
	//get current tech 
	$tech_sql = mysql_query("
		SELECT  *
		FROM `jobs`
		WHERE `id` = {$job_id}
	");
	$tech = mysql_fetch_array($tech_sql);
	$tech_id = $tech['assigned_tech'];

	//insert to jobs_not_completed table
	$query3 = "
		INSERT INTO 
		jobs_not_completed (
			`job_id`, 
			`reason_id`, 
			`reason_comment`, 
			`tech_id`, 
			`date_created`,
			`door_knock`
		) 
		VALUES (
			'{$job_id}', 
			'{$jr_id}', 
			'{$log_message}', 
			'{$tech_id}', 
			'".date("Y-m-d H:i:s")."',
			'{$dk['door_knock']}'
		)
	";
	mysql_query($query3);
	

}	

?>