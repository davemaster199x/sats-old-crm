<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$refunds_arr = $_POST['refunds_arr'];
$ir_id = mysql_real_escape_string($_POST['ir_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$payment_date = ( $_POST['payment_date']!='' )?"'".$crm->formatDate(mysql_real_escape_string(trim($_POST['payment_date'])))."'":"NULL";
$amount_paid = mysql_real_escape_string($_POST['amount_paid']);
$orig_amount_paid = mysql_real_escape_string($_POST['orig_amount_paid']);
$type_of_payment = mysql_real_escape_string($_POST['type_of_payment']);
$edited = mysql_real_escape_string($_POST['edited']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log
$payment_reference = "'".mysql_real_escape_string($_POST['payment_reference'])."'";

foreach( $refunds_arr as $pay ){
	
	// decodes json string to actual json object
	$json_enc = json_decode($pay);
	
	$payment_date = ( $json_enc->payment_date!='' )?"'".$crm->formatDate(mysql_real_escape_string(trim($json_enc->payment_date)))."'":"NULL";
	$amount_paid = $json_enc->amount_paid;
	$type_of_payment = $json_enc->type_of_payment;
	$ir_id = $json_enc->ir_id;
	$orig_amount_paid = $json_enc->orig_amount_paid;
	$edited = $json_enc->edited;
	$payment_reference = "'".mysql_real_escape_string($json_enc->payment_reference)."'";
	$payment_reference2 = $json_enc->payment_reference;
	
	echo "
	payment_date: {$payment_date}<br />
	amount_paid: {$amount_paid}<br />
	type_of_payment: {$type_of_payment}<br />
	ir_id: {$ir_id}<br />
	orig_amount_paid: {$orig_amount_paid}<br />
	edited: {$edited}<br />
	payment_reference: {$payment_reference}<br />
	";
	
	
	if( $ir_id != '' ){ // UPDATE
	
		if( $edited == 1 ){
			
			// UPDATED
			mysql_query("
				UPDATE `invoice_refunds` 
				SET
					`payment_date` = {$payment_date},
					`amount_paid` = {$amount_paid},
					`type_of_payment` = {$type_of_payment},
					`payment_reference` = {$payment_reference}
				WHERE `invoice_refund_id` = {$ir_id}
			");
			
			if( $orig_amount_paid != $amount_paid ){
				
			
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
						'Refund',
						'".date('Y-m-d')."',
						'Refund Updated From <strong>\${$orig_amount_paid}</strong> to <strong>\${$amount_paid}</strong><br/>Payment Reference: ".mysql_real_escape_string($payment_reference2)."',
						{$job_id}, 
						'{$logged_user}',
						'".date('H:i')."',
						{$job_log_type},
						'{$today}'
					)
				");	
				
				
			}
			
		}
		
		
	}else{ // INSERT
		
		// ADD
		mysql_query("
			INSERT INTO
			`invoice_refunds`(
				`job_id`,
				`payment_date`,
				`amount_paid`,
				`type_of_payment`,
				`created_by`,
				`created_date`,
				`payment_reference`
			)
			VALUES(
				{$job_id},
				{$payment_date},
				{$amount_paid},
				{$type_of_payment},
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
				'Refund',
				'".date('Y-m-d')."',
				'Refund <strong>\${$amount_paid}</strong><br/>Payment Reference: ".mysql_real_escape_string($payment_reference2)."',
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