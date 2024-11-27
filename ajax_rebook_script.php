<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_id_arr = $_POST['job_id'];
$is_240v = mysql_real_escape_string($_POST['is_240v']);
$isDHA = mysql_real_escape_string($_POST['isDHA']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

foreach($job_id_arr as $job_id){
	
	// get job type
	$job_sql = mysql_query("
		SELECT `key_access_required`, `status`, `job_reason_id`, `assigned_tech`, `date`, `key_access_details`, `job_type`, `comments`
		FROM `jobs`
		WHERE `id` = {$job_id}
	");
	$j = mysql_fetch_array($job_sql);
	
	
	// Tech Run Keys - Key Access Required Marker	
	$kar_sql_str = '';
	$append_kar_update = '';
	if( $j['key_access_required']==1 ){
		
		// if rebooked job is no show then add a marker to show in on tech keys page
		if( $j['status'] == 'Pre Completion' && $j['job_reason_id']==1 ){
			$append_kar_update = ',`rebooked_no_show` = 1';
		}
		
		$kar_sql_str = "
			,`trk_kar` = '1'
			,`trk_tech` = '{$j['assigned_tech']}'
			,`trk_date` = '{$j['date']}'
			,`tkr_approved_by` = '{$j['key_access_details']}'
			,`rebooked_show_on_keys` = 1
			{$append_kar_update}
		";
	}
	
	$status_txt = '';
	$jl_ct_txt = '';
	
	if( $isDHA==1 ){ // DHA
		$status_txt = 'DHA';
		$jl_ct_txt = '(DHA)';
	}else{
		$status_txt = 'To Be Booked';
	}
	
	$update_job_comments = null;
	if( $is_240v==1 ){ // 240v rebook
		$job_type_txt = " `is_eo` = 1, ";
		$jl_ct_txt = '(240v)';

		// this needs to logged like it was updated to 240v rebook
		$crm->insert_job_markers($job_id,'240v Rebook');
		$update_job_comments = " `comments` = '240v Rebook Job - ".mysql_real_escape_string($j['comments'])."', ";

	}else{
		$job_type_txt = '';
	}
		
	// update job
	mysql_query("
		UPDATE `jobs`
		SET 
			{$job_type_txt}
			`status` = '{$status_txt}',
			{$update_job_comments}
			`date` = NULL,
			`time_of_day` = NULL,
			`assigned_tech` = NULL,
			`ts_completed` = 0,
			`ts_techconfirm` = NULL,
			`cw_techconfirm` = NULL,
			`ss_techconfirm` = NULL,
			`job_reason_id` = 0,
			`door_knock` = 0,
			`completed_timestamp` = NULL,
			`tech_notes` = NULL,
			`job_reason_comment` = NULL,
			`booked_with` = NULL,
			`booked_by` = NULL,
			`key_access_required` = 0,
			`key_access_details` = NULL,
			`call_before` = NULL,
			`call_before_txt` = NULL,
			`sms_sent` = NULL,
			`client_emailed` = NULL,
			`sms_sent_merge` = NULL,
			`allocate_response` = NULL,
			`job_entry_notice` = 0,
			`job_priority` = NULL
			{$kar_sql_str}
		WHERE `id` = {$job_id}
	");

	if($is_240v==1){
		$jl_msg = " 'Job is marked as Electrician Only(EO)', ";
	}else{
		$jl_msg = " 'Job status updated from <strong>{$j['status']}</strong> to <strong>{$status_txt}</strong>', ";
	}
		
	$date = date("Y-m-d");
	$time = date("H:i");


	// clear airtable
	mysql_query("
	DELETE 
	FROM `airtable`
	WHERE `job_id` = {$job_id}
	");

	
	// insert job log
	mysql_query("
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
			{$jl_msg}
			'$date', 
			'Rebook {$jl_ct_txt}',
			'{$job_id}',
			'$time'
		)
	");	
	
}



?>