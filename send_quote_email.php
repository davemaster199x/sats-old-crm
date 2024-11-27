<?php

include('inc/init_for_ajax.php');

// init the variables
$job_id = $_GET['job_id'];
$quote_email_to = $_GET['quote_email_to'];

require_once('inc/pdfInvoiceCertComb.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');
require_once('inc/pdf_quote_template.php');

$pdf_filename = 'quote_'.date('dmYHis').'.pdf';
$pdf_data = $pdf->Output($pdf_filename, 'S');


// get country data
$cntry_sql = getCountryViaCountryId();
$cntry = mysql_fetch_array($cntry_sql);

$subject = "QLD Upgrade Quote #{$job_id}Q";


unset($to);
$to = array();

if(filter_var($quote_email_to, FILTER_VALIDATE_EMAIL)){
	$to[] = $quote_email_to;
}

//print_r($to);

//$to = array( 'danielk@sats.com.au', 'vaultdweller123@gmail.com' );
//$to = array( 'vaultdweller123@gmail.com' );
//$to = array('hr@sats.com.au');
//$to = array($cntry['outgoing_email']);


if( count($to)>0 ){
	
	$email_body = "
	<p>Dear Agent,</p>

	<p>
	Please find the attached quote for the below property to upgrade to the NEW QLD Legislation. 
	Please contact us with any enquiries you may have.<br />
	</p>

	<p>
	Property Address<br />
	{$property_details['address_1']} {$property_details['address_2']}<br />
	{$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}
	</p>

	<p>
	Kind Regards,<br />
	SATS Team
	</p>
	";

	// Get email template
	$template = getBaseEmailTemplate();	
	// replace email template content
	$find_temp = array(
		"#title", 
		"cron_email_footer.png", 
		"SATS Trading Pty Ltd",
		"#content"
	);
	$replace_temp = array(
		$subject, 
		$cntry['email_signature'], 
		$cntry['trading_name'],
		$email_body
	);

	$template2 = str_replace($find_temp, $replace_temp, $template);

	// start swift mailer
	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);


	// Create the message
	$email = Swift_Message::newInstance($transport)
	// Give the message a subject
	->setSubject($subject)
	// Set the From address with an associative array
	->setFrom(array($cntry['outgoing_email'] => COMPANY_FULL_NAME))
	// Set the To addresses with an associative array
	->setTo($to)
	// BCC
	->setBCC(CC_EMAIL)
	// Give it a body
	->setBody($template2, 'text/html')
	// attach PDF
	->attach(Swift_Attachment::newInstance($pdf_data, $pdf_filename, 'application/pdf'));
	;

	// send
	$result = $mailer->send($email);
	
	
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
			'Quote Email',
			'".date('Y-m-d')."',
			'Quote Sent to: <strong>{$quote_email_to}</strong>',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	");
	
	
	header("location: /view_job_details.php?id={$job_id}&quote_email=1");

}else{
	header("location: /view_job_details.php?id={$job_id}&quote_email=0");
}



?>