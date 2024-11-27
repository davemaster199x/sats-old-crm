<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$credits_arr = $_POST['credits_arr'];
$credit_id = mysql_real_escape_string($_POST['credit_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$credit_date = ( $_POST['credit_date']!='' )?"'".$crm->formatDate(mysql_real_escape_string(trim($_POST['credit_date'])))."'":"NULL";
$credit_paid = mysql_real_escape_string($_POST['credit_paid']);
$orig_credit_paid = mysql_real_escape_string($_POST['orig_credit_paid']);
$approved_by = mysql_real_escape_string($_POST['approved_by']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log
$payment_reference = "'".mysql_real_escape_string($_POST['payment_reference'])."'";

foreach( $credits_arr as $cred ){
	
	// decodes json string to actual json object
	$json_enc = json_decode($cred);
	
	$credit_date = ( $json_enc->credit_date!='' )?"'".$crm->formatDate(mysql_real_escape_string(trim($json_enc->credit_date)))."'":"NULL";
	$credit_paid = $json_enc->credit_paid;
	$credit_reason = $json_enc->credit_reason;
	$approved_by = $json_enc->approved_by;
	$credit_id = $json_enc->credit_id;
	$orig_amount_paid = $json_enc->orig_amount_paid;
	$edited = $json_enc->edited;
	$payment_reference = "'".mysql_real_escape_string($json_enc->payment_reference)."'";
	$payment_reference2 = $json_enc->payment_reference;
	
	echo "
	credit_date: {$credit_date}<br />
	credit_paid: {$credit_paid}<br />
	credit_reason: {$credit_reason}<br />
	approved_by: {$approved_by}<br />
	credit_id: {$credit_id}<br />
	orig_amount_paid: {$orig_amount_paid}<br />
	edited: {$edited}<br />
	payment_reference: {$payment_reference}<br />
	";
	
	
	if( $credit_id != '' ){
	
		// UPDATED
		mysql_query("
			UPDATE `invoice_credits` 
			SET
				`credit_date` = {$credit_date},
				`credit_paid` = {$credit_paid},
				`credit_reason` = '{$credit_reason}',
				`approved_by` = {$approved_by},
				`payment_reference` = {$payment_reference}
			WHERE `invoice_credit_id` = {$credit_id}
		");
		
		if( $orig_credit_paid != $credit_paid ){
			
		
			// job log
			mysql_query("
				INSERT INTO 
				`job_log` (
					`contact_type`,
					`eventdate`,
					`comments`,
					`job_id`, 
					`staff_id`,
					`eventtime`,
					`log_type`,
					`created_date`
				) 
				VALUES (
					'Credit',
					'".date('Y-m-d')."',
					'Credit Updated From <strong>\${$orig_credit_paid}</strong> to <strong>\${$credit_paid}</strong><br/>Payment Reference: ".mysql_real_escape_string($payment_reference2)."',
					{$job_id}, 
					'{$logged_user}',
					'".date('H:i')."',
					{$job_log_type},
					'{$today}'
				)
			");	
			
			
		}
		
		
		
	}else{
		
		

		// ADD
		mysql_query("
			INSERT INTO
			`invoice_credits`(
				`job_id`,
				`credit_date`,
				`credit_paid`,
				`credit_reason`,
				`approved_by`,
				`created_by`,
				`created_date`,
				`payment_reference`
			)
			VALUES(
				{$job_id},
				{$credit_date},
				{$credit_paid},
				'{$credit_reason}',
				{$approved_by},
				{$logged_user},
				'{$today}',
				{$payment_reference}
			)
		");
		
		// job log
		mysql_query("
			INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`comments`,
				`job_id`, 
				`staff_id`,
				`eventtime`,
				`log_type`,
				`created_date`
			) 
			VALUES (
				'Credit',
				'".date('Y-m-d')."',
				'Credit <strong>\${$credit_paid}</strong><br/>Payment Reference: ".mysql_real_escape_string($payment_reference2)."',
				{$job_id}, 
				'{$logged_user}',
				'".date('H:i')."',
				{$job_log_type},
				'{$today}'
			)
		");	
		
		
	}
	
	// AUTO - UPDATE INVOICE DETAILS
	$crm->updateInvoiceDetails($job_id);
	
	
}



?>