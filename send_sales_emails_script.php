<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$et_id = mysql_real_escape_string($_POST['et_id']);
$from_email = mysql_real_escape_string($_POST['from_email']);
$to_email = mysql_real_escape_string($_POST['to_email']);
$cc_email = mysql_real_escape_string($_POST['cc_email']);
$subject = mysql_real_escape_string($_POST['subject']);
$body = nl2br($_POST['body']);
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_name = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);

$file_upload = $_FILES['et_file_upload'];
$custom_upload = 0;
$stopSendEmail = 0;


if( $agency_id != '' ){
	
	// parse tags
	$jparams = array( 'agency_id' => $agency_id );
	
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
		
		$redirect_url = "send_sales_emails.php?agency_id={$agency_id}&error=1&upload_errors={$errors_url_params}";
		
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
			
			// insert logs
			mysql_query("
				INSERT INTO 
				`agency_event_log` 
				(
					`contact_type`,
					`eventdate`,
					`comments`,
					`agency_id`,
					`staff_id`,
					`date_created`,
					`hide_delete`
				) 
				VALUES (
					'Sales Emails',
					'".date('Y-m-d')."',
					'{$et['template_name']} sent to: {$to_imp} and cc to: {$cc_imp}',
					'{$agency_id}',
					'".$_SESSION['USER_DETAILS']['StaffID']."',
					'".date('Y-m-d H:i:s')."',
					1
				);
			");
		
			$redirect_url = "send_sales_emails.php?agency_id={$agency_id}&success=1";
		}else{ // fail
			$redirect_url = "send_sales_emails.php?agency_id={$agency_id}&error=1";
		}

	}
	
	header("location: {$redirect_url}");
	
}


?>