<?php
include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$pdf_type = mysql_real_escape_string($_POST['pdf_type']);
$country_id = $_SESSION['country_default'];
$invoice_only = 0;

if($job_id!=''){
	
	
	unset($jemail);
	$jemail = array();

	$Query = getJobDetails($job_id, true);
	$email_job_details = mysqlSingleRow($Query);

	$temp = explode("\n",trim($email_job_details['account_emails']));
	foreach($temp as $val){
		$val2 = preg_replace('/\s+/', '', $val);
		if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
			$jemail[] = $val2;
		}				
	}
	
	/*
	echo "<pre>";
	print_r($jemail);
	echo "</pre>";
	*/
	
	if( $pdf_type == 'quote' ){
		$email_job_details['send_quote'] = 1;
		$jog_log_txt = 'Quote';
	}else if( $pdf_type == 'invoice_cert' ){
		$email_job_details['mm_need_proc_inv'] = 1;
		$jog_log_txt = 'Invoice/Cert';
	}else if( $pdf_type == 'invoice' ){
		$email_job_details['mm_need_proc_inv'] = 1;
		$jog_log_txt = 'Invoice';
		$invoice_only = 1;
	}
	
	//print_r($email_job_details);
	

	
	sendInvoiceCertEmail($email_job_details, $jemail, $country_id,$invoice_only);
	
	/*
	// clear need processing
	$usql = "
		UPDATE `jobs`
		SET
			`dha_need_processing` = NULL
		WHERE `id` = {$job_id}
	";
	mysql_query($usql);
	
	
	
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
			'Agency Emailed',
			'".date('Y-m-d')."',
			'<strong>{$jog_log_txt}</strong> emailed to Agency from MM Page',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	");	
	*/

	
}
?>