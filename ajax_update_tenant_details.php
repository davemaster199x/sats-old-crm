<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);

$tenant_changed  = date('Y-m-d H:i:s');

$is_escalate = mysql_real_escape_string($_POST['is_escalate']);
$tenants_changed = mysql_real_escape_string($_POST['tenants_changed']);


$tenants_arr = $_POST['tenants_arr'];
	
foreach( $tenants_arr as $tnt ){

	// decodes json string to actual json object
	$json_enc = json_decode($tnt);
	
	$pt_id = $json_enc->pt_id;
	$tenant_firstname = $json_enc->tenant_firstname;
	$tenant_lastname = $json_enc->tenant_lastname;
	$tenant_mobile = $json_enc->tenant_mobile;
	$tenant_landline = $json_enc->tenant_landline;
	$tenant_email = $json_enc->tenant_email;
	
	if( $pt_id != '' ){
			
		// update
		$tenants_sql_str = "
			UPDATE `property_tenants`
			SET
				`tenant_firstname` = '{$tenant_firstname}',
				`tenant_lastname` = '{$tenant_lastname}',
				`tenant_mobile` = '{$tenant_mobile}',
				`tenant_landline` = '{$tenant_landline}',
				`tenant_email` = '{$tenant_email}'
			WHERE `property_tenant_id` = {$pt_id}
		";					
		mysql_query($tenants_sql_str);
		
	}else{
		
		// insert
		$tenants_sql_str = "
			INSERT INTO
			`property_tenants`(
				`property_id`,
				`tenant_firstname`,
				`tenant_lastname`,
				`tenant_mobile`,
				`tenant_landline`,
				`tenant_email`
			)
			VALUES(
				{$property_id},
				'{$tenant_firstname}',
				'{$tenant_lastname}',
				'{$tenant_mobile}',
				'{$tenant_landline}',
				'{$tenant_email}'
			)
		";					
		mysql_query($tenants_sql_str);
		
	}
	
}


if($tenants_changed==1){
		
	// update job
	$sql_str = "
		UPDATE `property`
		SET 
			`no_en` = NULL, 
			`tenant_changed` = '{$tenant_changed}'
		WHERE `property_id` = {$property_id}
	";
	mysql_query($sql_str);
	
	echo "Last Updated: ".date("d/m/Y",strtotime($tenant_changed));
	
}




// for escalate job only
if( $is_escalate==1 ){
	
	// clear escalate job reason
	mysql_query("
		DELETE
		FROM `selected_escalate_job_reasons`
		WHERE `job_id` = {$job_id}
	");
	
	// insert logs
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
			'" . date('Y-m-d') . "',
			'Tenant Details updated', 
			'{$job_id}',
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date("H:i")."'
		)
	");
	
	
	// update status = to be booked
	mysql_query("
		UPDATE jobs
		SET `status` = 'To Be Booked'
		WHERE `id` = {$job_id}
	");
	
	
}


?>