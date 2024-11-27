<?php

include('inc/init.php');

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$crm = new Sats_Crm_Class;

// renew all
$jparams = array(
	'job_status' => 'Pending',
	'auto_renew' => 1,
	'country_id' => $_SESSION['country_default']
);
$renew_sql = $crm->getJobsData($jparams);


if( mysql_num_rows($renew_sql)>0 ){
	
	// loop auto renew enabled jobs
	while( $renew = mysql_fetch_array($renew_sql) ){
		
		$property_id = $renew['property_id'];
		$job_id = $renew['jid'];
		$job_service = $renew['jservice'];
		$ajt_type = $renew['ajt_type'];
		
		// updates to on hold
		$Query = "
		UPDATE jobs 
		SET 				
			status='On Hold', 
			auto_renew=2 
		WHERE `id` = {$job_id}
		";

		mysql_query($Query);

		
		// add logs
		$insertLogQuery = "
		INSERT INTO 
		property_event_log (
			property_id, 
			staff_id, 
			event_type, 
			event_details, 
			log_date
		) 
		VALUES (
			".$property_id.", 
			'{$staff_id}', 
			'{$ajt_type} Service Auto Renewed', 
			'By SATS', 
			'".date('Y-m-d H:i:s')."'
		)
		";
		mysql_query($insertLogQuery);
		
		
		
		// insert job logs
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
				'Service Due',
				'" . date('Y-m-d') . "',
				'Job Auto Created on ".date('d/m/Y')." by SATS', 
				'{$job_id}',
				'{$staff_id}',
				'".date("H:i")."'
			)
		");
		
	}
	
	
	
	$auto_renew_all = 1;
	
}else{
	
	$auto_renew_all = 0;
	
}


header("location: /service_due_jobs.php?auto_renew_all={$auto_renew_all}");


?>