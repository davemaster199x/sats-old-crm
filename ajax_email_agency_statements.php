<?php

include('inc/init_for_ajax.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_statements.php');

$crm = new Sats_Crm_Class();

$agency_id_arr = $_POST['agency_id_arr'];
$country_id = $_SESSION['country_default'];

// get country
$cntry_sql = getCountryViaCountryId($country_id);
$cntry = mysql_fetch_array($cntry_sql);

foreach( $agency_id_arr as $json ){
	
	// decodes json string to actual json object
	$json_enc = json_decode($json);
	
	$agency_id = $json_enc->agency_id;
	$accounts_email = $json_enc->accounts_email;
	
	echo "
	agency_id: {$agency_id}<br />
	accounts_email: {$accounts_email}<br /><br />
	";
	
	$to_email = explode(",",trim($accounts_email));
	
	
	$email_body = 'attached is statements pdf';
	$subj = 'Statement as of '.date('d/m/Y');


	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);	

	// Create the message
	$email = Swift_Message::newInstance($transport)

	  // Give the message a subject
	  ->setSubject($subj)

	  // Set the From address with an associative array
	  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))

	  // Set the To addresses with an associative array
	  ->setTo($to_email)

	  // Give it a body
	  ->setBody($email_body)

	 ;


	$pdf_filename = 'statements_'.date('dmYHis').'.pdf';
	$params = array(
		'agency_id' => $agency_id,
		'country_id' => $country_id,
		'ret' => 1,
		'output' => 'S',
		'file_name' => $pdf_filename
	);
	$state_attach = $crm->getStatementsPdf($params);

	// Attach Quote
	$email->attach(Swift_Attachment::newInstance($state_attach, $pdf_filename, 'application/pdf'));
	 
	$result = $mailer->send($email);
	
	if( $result == true ){
		
		echo "email sent";
		
		mysql_query("
			UPDATE `agency`
			SET `send_statement_email_ts` = '".date('Y-m-d H:i:s')."'
			WHERE `agency_id` = {$agency_id}
		");
		
	}else{
		echo "email fail";
	}
	
}

?>