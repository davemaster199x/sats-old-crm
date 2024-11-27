<?php 


include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$ejr_id_arr = $_POST['ejr_id_arr'];


// clear all selected
mysql_query("
	DELETE 
	FROM `selected_escalate_job_reasons`
	WHERE `job_id` = {$job_id}
");


if( count($ejr_id_arr)>0 ){

	foreach( $ejr_id_arr as $ejr_id ){
		
		// insert selected job escalate reason			
		mysql_query("
			INSERT INTO 
			`selected_escalate_job_reasons` (
				`job_id`,
				`escalate_job_reasons_id`,
				`date_created`,
				`deleted`,
				`active`
			)
			VALUES (
				{$job_id},
				{$ejr_id},
				'".date('Y-m-d H:i:s')."',
				0,
				1					
			)
		");


		// insert job log

		// get escalate job reason
		$ejr_sql = mysql_query("
			SELECT `reason_short`
			FROM `escalate_job_reasons`
			WHERE `escalate_job_reasons_id` = {$ejr_id}
		");
		$ejr = mysql_fetch_array($ejr_sql);

		// job log
		mysql_query("
			INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`comments`,
				`job_id`, 
				`staff_id`,
				`eventtime`
			) 
			VALUES (
				'Escalate Job',
				'".date('Y-m-d')."',
				'Job marked escalate due to {$ejr['reason_short']}',
				{$job_id}, 
				'".$_SESSION['USER_DETAILS']['StaffID']."',
				'".date('H:i')."'
			)
		");
		
	}
	
	// update status = escalate
	mysql_query("
		UPDATE `jobs`
		SET `status` = 'Escalate'
		WHERE `id` = {$job_id}
	");
	
}else{
	
	// update status = to be booked
	mysql_query("
		UPDATE `jobs`
		SET `status` = 'To Be Booked'
		WHERE `id` = {$job_id}
	");
	
}	

?>