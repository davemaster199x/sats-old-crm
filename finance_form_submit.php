<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

$job_id = trim($_REQUEST['job_id']);
$to_email = trim($_REQUEST['to_email']);
$from_email = trim($_REQUEST['from_email']);
$subject = trim($_REQUEST['subject']);
$email_body = nl2br($_REQUEST['email_body']);


if( $to_email != '' ){
	

	// TO
	if( filter_var( trim($to_email), FILTER_VALIDATE_EMAIL ) ){ // validate email
		$to[] = $to_email; // needs to be associative array
	}	

	
	// Email class
	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);

	// Create the message
	$email = Swift_Message::newInstance($transport)

	// Give the message a subject
	->setSubject($subject)

	// Set the From address with an associative array
	->setFrom( [$from_email => 'Smoke Alarm Testing Services (SATS)'] )

	// Set the To addresses with an associative array
	->setTo( $to )

	// Give it a body
	->setBody('Test message with attachment')


	->addPart($email_body, 'text/html')
	;
	
	$mailer->send($email);
	
	
	// insert job logs
	$job_log_str = "
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`eventtime`,
			`comments`,
			`job_id`,
			`staff_id`,
			`created_date`
		) 
		VALUES (
			'Email Finance',
			'" . date('Y-m-d') . "',
			'" . date('H:i') . "',
			'Email sent to: <strong>{$to_email}</strong> Click <a class=\'sent_email_alink\' href=\'javascript:void(0);\'><strong>HERE</strong></a> to view email content', 
			'{$job_id}',
			'{$_SESSION['USER_DETAILS']['StaffID']}',
			'".date('Y-m-d H:i:s')."'
		)
	";
	mysql_query($job_log_str);
	$job_log_id = mysql_insert_id();
	
	
	// capture email sent
	mysql_query("
		INSERT INTO
		`email_templates_sent` (
			`job_log_id`,
			`from_email`,
			`to_email`,
			`subject`,
			`email_body`,
			`date_created`
		)
		VALUES (
			{$job_log_id},
			'".mysql_real_escape_string($from_email)."',
			'".mysql_real_escape_string($to_email)."',
			'".mysql_real_escape_string($subject)."',
			'".mysql_real_escape_string($email_body)."',
			'".date('Y-m-d H:i:s')."'
		)
	");
	
	
}

header("location:/finance_form.php?job_id={$job_id}&success=1");

?>