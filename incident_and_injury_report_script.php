<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

// The incident
$date_of_incident = mysql_real_escape_string($_POST['date_of_incident']);
$date_of_incident2 = $crm->formatDate($date_of_incident);
$time_of_incident = mysql_real_escape_string($_POST['time_of_incident']);
$datetime_of_incident = "{$date_of_incident2} {$time_of_incident}:00";
$nature_of_incident = mysql_real_escape_string($_POST['nature_of_incident']);
$loc_of_inci = mysql_real_escape_string($_POST['loc_of_inci']);
$desc_inci = mysql_real_escape_string($_POST['desc_inci']);

// Injured Person Details
$ip_name = mysql_real_escape_string($_POST['ip_name']);
$ip_address = mysql_real_escape_string($_POST['ip_address']);
$ip_occu = mysql_real_escape_string($_POST['ip_occu']);
$ip_dob = mysql_real_escape_string($_POST['ip_dob']);
$ip_dob2 = $crm->formatDate($ip_dob);
$ip_tel_num = mysql_real_escape_string($_POST['ip_tel_num']);
$ip_employer = mysql_real_escape_string($_POST['ip_employer']);
$ip_noi = mysql_real_escape_string($_POST['ip_noi']);
$ip_loi = mysql_real_escape_string($_POST['ip_loi']);
$ip_onsite_treatment = mysql_real_escape_string($_POST['ip_onsite_treatment']);
$ip_fur_treat = mysql_real_escape_string($_POST['ip_fur_treat']);

// Witness Details
$witness_name = mysql_real_escape_string($_POST['witness_name']);
$witness_contact = mysql_real_escape_string($_POST['witness_contact']);

// Outcome
$loss_time_injury = mysql_real_escape_string($_POST['loss_time_injury']);
$reported_to = mysql_real_escape_string($_POST['reported_to']);

$confirm_chk = mysql_real_escape_string($_POST['confirm_chk']);

$country_id = $_SESSION['country_default'];



$sql_str = "
	INSERT INTO
	`incident_and_injury` (
		`datetime_of_incident`,
		`nature_of_incident`,
		`location_of_incident`,
		`describe_incident`,
		`ip_name`,
		`ip_address`,
		`ip_occupation`,
		`ip_dob`,
		`ip_tel_num`,
		`ip_employer`,
		`ip_noi`,
		`ip_loi`,
		`ip_onsite_treatment`,
		`ip_further_treatment`,
		`witness_name`,
		`witness_contact`,
		`loss_time_injury`,
		`reported_to`,
		`confirm_chk`,
		`country_id`
	)
	VALUES (
		'{$datetime_of_incident}',
		'{$nature_of_incident}',
		'{$loc_of_inci}',
		'{$desc_inci}',
		'{$ip_name}',
		'{$ip_address}',
		'{$ip_occu}',
		'{$ip_dob2}',
		'{$ip_tel_num}',
		'{$ip_employer}',
		'{$ip_noi}',
		'{$ip_loi}',
		'{$ip_onsite_treatment}',
		'{$ip_fur_treat}',
		'{$witness_name}',
		'{$witness_contact}',
		'{$loss_time_injury}',
		'{$reported_to}',
		'{$confirm_chk}',
		'{$country_id}'
	)
";

mysql_query($sql_str);

$iai_id = mysql_insert_id();

/*
echo "<pre>";
print_r($_FILES);
echo "</pre>";
*/

// Multiple upload
$files = array(); 
foreach ($_FILES['photo_of_incident'] as $k => $l) {  
	foreach ($l as $i => $v) {    
		if (!array_key_exists($i, $files)) 
		$files[$i] = array();    
		$files[$i][$k] = $v;  
	} 
}


/*
echo "<pre>";
print_r($files);
echo "</pre>";
*/

$photo_of_incident_arr = []; 
foreach ($files as $file){
	
	if( $file['name'] !='' ){
		$upload = $crm->uploadIncidentReportUpload($file);
		$photo_of_incident_arr[] = $upload['photo_of_incident'];
		mysql_query("
			INSERT INTO 
			`incident_photos` (
				`incident_and_injury_id`,
				`image_name`
			)
			VALUES(
				{$iai_id},
				'{$upload['photo_of_incident']}'
			)
		");
	}
}

/*
echo "<pre>";
print_r($photo_of_incident_arr);
echo "</pre>";
*/


// EMAIL IT
$params = array(
	'iai_id' => $iai_id,
	'output' => 'S'
);
$pdf_data = $crm->getIncidentAndReportPdf($params);

// get country data
$cntry_sql = getCountryViaCountryId();
$cntry = mysql_fetch_array($cntry_sql);

$subject = 'Incident Report';
//$to = array( 'danielk@sats.com.au', 'vaultdweller123@gmail.com' );
//$to = array('info@sats.com.au');
$to = array('hr@sats.com.au');
//$to = array($cntry['outgoing_email']);

$email_body = "
<h1>Please print 3 copies of this incident report</h1>
<ul>
	<li>Copy 1 - Insert into Employee file</li>
	<li>Copy 2 - Insert into Incident report file</li>
	<li>Copy 3 - Give to employees direct report to action</li>
</ul>
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

$pdf_filename = 'incident_and_injury_report_'.date('dmYHis').'.pdf';

// Create the message
$email = Swift_Message::newInstance($transport)
// Give the message a subject
->setSubject($subject)

// Set the From address with an associative array
->setFrom(array($cntry['outgoing_email'] => COMPANY_FULL_NAME))

// Set the To addresses with an associative array
->setTo($to)

// Give it a body
->setBody($template2, 'text/html')

// attach PDF
->attach(Swift_Attachment::newInstance($pdf_data, $pdf_filename, 'application/pdf'));
;

// send
$result = $mailer->send($email);

header('location: /incident_and_injury_report.php?success=1');


?>