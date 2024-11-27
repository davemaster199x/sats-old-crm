<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$et_id = mysql_real_escape_string($_POST['et_id']);
$from_email = mysql_real_escape_string($_POST['from_email']);
$to_email = mysql_real_escape_string($_POST['to_email']);
$cc_email = mysql_real_escape_string($_POST['cc_email']);
$subject = mysql_real_escape_string($_POST['subject']);
$body = nl2br($_POST['body']);
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);

$marked_as_copy = mysql_real_escape_string($_POST['marked_as_copy']);

$job_pdf = $_POST['job_pdf'];
$file_upload = $_FILES['et_file_upload'];
$custom_upload = 0;
$stopSendEmail = 0;
$is_copy = false;
$invoice_copy_template_id = 0;




if( $job_id != '' ){
	
	// parse tags
	$jparams = array( 'job_id' => $job_id );
	
	$message_fin = $crm->parseEmailTemplateTags($jparams,$body);
	$subject_fin = $crm->parseEmailTemplateTags($jparams,$subject);
	

	// TO
	$to_email_arr = explode(";",$to_email);
	$to	= [];
	foreach(  $to_email_arr as $et_email ){
		if( filter_var( trim($et_email), FILTER_VALIDATE_EMAIL ) ){ // validate email
			$to[] = $et_email; // needs to be associative array
		}						
	}
	
	// CC
	$cc_email_arr = explode(";",$cc_email);
	$cc	= [];
	foreach(  $cc_email_arr as $et_email ){
		if( filter_var( trim($et_email), FILTER_VALIDATE_EMAIL ) ){ // validate email
			$cc[] = $et_email; // needs to be associative array
		}						
	}

	
	// Email class
	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);

	// Create the message
	$email = Swift_Message::newInstance($transport)

	// Give the message a subject
	->setSubject($subject_fin)

	// Set the From address with an associative array
	->setFrom( $from_email )

	// Set the To addresses with an associative array
	->setTo( $to )

	// set CC
	->setCc( $cc )

	// set BCC
	->setBcc( $bcc )

	// Give it a body
	->setBody('Test message with attachment')


	->addPart($message_fin, 'text/html')
	;
	
	
	// attachments
	if( count($job_pdf)>0 ){
		
		// append checkdigit to job id for new invoice number
		$check_digit = getCheckDigit(trim($job_id));
		$bpay_ref_code = "{$job_id}{$check_digit}";	
		

		require_once("inc/fpdf/fpdf.php");
		require_once('inc/fpdf_override.php');
		require_once('inc/pdfInvoiceCertComb.php');



		$is_copy = ( $marked_as_copy == 1 )?true:false;

	
		foreach( $job_pdf as $jpdf ){
			
			// Credit
			if( $jpdf == 'cred' ){
				
				if(isset($pdf)) unset($pdf);
				include('inc/pdf_credit_template.php');
				$invoice_pdf = $pdf->Output('', 'S');
				
				// CREDITS
				$credit_sql = mysql_query("
					SELECT *
					FROM `invoice_credits` AS ic 
					WHERE ic.`job_id` = {$job_id}
				");

				$credit_rows = mysql_num_rows($credit_sql);
				$bpay_ref_code_for_cred = "{$job_id}{$credit_rows}";	
				
				$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'credit' . $bpay_ref_code_for_cred . '.pdf', 'application/pdf'));
			
			}
			
			// Invoice
			if( $jpdf == 'inv' ){
				
				if(isset($pdf)) unset($pdf);
				include('inc/pdf_invoice_template.php');
				$invoice_pdf = $pdf->Output('', 'S');
				
				
				$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'invoice' . $bpay_ref_code . '.pdf', 'application/pdf'));
			
			}
			
			// Certificate
			if( $jpdf == 'cert' ){
				
				if(isset($pdf)) unset($pdf);
				include('inc/pdf_certificate_template.php');
				$cert_pdf = $pdf->Output('', 'S');
				
							
				$email->attach(Swift_Attachment::newInstance($cert_pdf, 'cert' . $bpay_ref_code . '.pdf', 'application/pdf'));	
			
			}
			
			// Combined
			if( $jpdf == 'comb' ){
				
				if(isset($pdf)) unset($pdf);
				require_once('inc/pdf_combined_template.php');
				$combined_pdf = $pdf->Output('', 'S');
					
				
				$email->attach(Swift_Attachment::newInstance($combined_pdf, 'invoice_cert_' . $bpay_ref_code . '.pdf', 'application/pdf'));
			
			}
			
		}				

	}
	
	// custom file attachment via browse upload
	if( $file_upload["name"] != '' ){	
		
		// upload size limit: 5mb, default is 50kb
		$upload_params = array(
			'image_only' => 0,
			'files' => $file_upload,
			'upload_size_limit' => 5000000
		);
		$upload_ret = $crm->nativeFileUpload($upload_params);

		if( $upload_ret['upload_success'] == 1 && $upload_ret['server_upload_path'] != '' ){
			$email->attach(Swift_Attachment::fromPath($upload_ret['server_upload_path'])); // attach file	
			$custom_upload = 1;
		}else{		
			$errors_url_params = '';
			foreach($upload_ret['error_msg'] as $up_err){
				$errors_url_params .= "&upload_errors[]={$up_err}"; 
			}				
			$stopSendEmail = 1;
		}		
		
	}
	
	
	if( $stopSendEmail == 1 ){
		
		$redirect_url = "send_email_template.php?job_id={$job_id}&error=1&upload_errors={$errors_url_params}";
		
	}else{
		
		// send email
		$result = $mailer->send($email);
		
		if( $custom_upload == 1 ){
			
			if( $upload_ret['server_upload_path'] != '' && file_exists($upload_ret['server_upload_path']) == true ){
				unlink($upload_ret['server_upload_path']); // delete file			
			}

		}
		
		if( $result ){ // success
		
			$et_params = array( 
				'echo_query' => 0,
				'email_templates_id' => $et_id
			);			
			$et_sql = $crm->getEmailTemplates($et_params);
			$et = mysql_fetch_array($et_sql);
			
			// TO: and CC: to string
			$to_imp = mysql_real_escape_string(implode(", ",$to));
			$cc_imp = mysql_real_escape_string(implode(", ",$cc));
			$cc_append_str = '';
			
			if( $cc_imp !='' ){
				$cc_append_str = " and cc to: {$cc_imp} ";
			}
			
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
					'Email Template',
					'".date('Y-m-d')."',
					'Email sent to: <strong>{$to_imp} {$cc_append_str}</strong> Click <a class=\'sent_email_alink\' href=\'javascript:void(0);\'><strong>HERE</strong></a> to view email content',
					{$job_id}, 
					'".$_SESSION['USER_DETAILS']['StaffID']."',
					'".date('H:i')."'
				)
			");
			$job_log_id = mysql_insert_id();

			
			// capture email sent
			mysql_query("
				INSERT INTO
				`email_templates_sent` (
					`job_log_id`,
					`from_email`,
					`to_email`,
					`cc_email`,
					`subject`,
					`email_body`,
					`date_created`
				)
				VALUES (
					{$job_log_id},
					'".mysql_real_escape_string($from_email)."',
					'".mysql_real_escape_string($to_imp)."',
					'".mysql_real_escape_string($cc_imp)."',
					'".mysql_real_escape_string($subject_fin)."',
					'".mysql_real_escape_string($message_fin)."',
					'".date('Y-m-d H:i:s')."'
				)
			");
			
			
		
			$redirect_url = "send_email_template.php?job_id={$job_id}&success=1";
		}else{ // fail
			$redirect_url = "send_email_template.php?job_id={$job_id}&error=1";
		}

	}
	
	header("location: {$redirect_url}");
	
}


?>