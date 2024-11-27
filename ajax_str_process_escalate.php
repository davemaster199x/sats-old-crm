<?php
include('inc/init.php');

//$crm = new Sats_Crm_Class;

$job_id_arr = $_POST['job_id'];

foreach( $job_id_arr as $job_id ){
			
	// update job to escalate
	mysql_query("
		UPDATE `jobs`
		SET 
			`status` = 'Escalate'
		WHERE `id` = {$job_id}
	");
	
	// clear selected job escalate reason first
	mysql_query("
		DELETE 
		FROM `selected_escalate_job_reasons`
		WHERE `job_id` = {$job_id}
	");
	
	// set escalate reason 
	$escalate_job_reasons = 1; // verify tenant details
	
	// set
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
			{$escalate_job_reasons},
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
		WHERE `escalate_job_reasons_id` = {$escalate_job_reasons}
	");
	$ejr = mysql_fetch_array($ejr_sql);
	
	// insert job log
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
			'Job marked <strong>Escalate</strong> due to <strong>{$ejr['reason_short']}</strong>',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	");	
	
	
}

?>