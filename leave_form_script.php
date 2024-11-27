<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

// Leave Request Form
$employee = mysql_real_escape_string($_POST['employee']);
$type_of_leave = mysql_real_escape_string($_POST['type_of_leave']);
$lday_of_work = mysql_real_escape_string($_POST['lday_of_work']);
$lday_of_work2 = $crm->formatDate($lday_of_work);
$fday_back = mysql_real_escape_string($_POST['fday_back']);
$fday_back2 = $crm->formatDate($fday_back);
$num_of_days = mysql_real_escape_string($_POST['num_of_days']);
$reason_for_leave = mysql_real_escape_string($_POST['reason_for_leave']);
$line_manager = mysql_real_escape_string($_POST['line_manager']);
$backup_leave = mysql_real_escape_string($_POST['backup_leave']);

//backup_leave tweak for email
if($backup_leave!=""){
	switch ($backup_leave) {
		case 1:
			$tol_str = "Annual leave";
			break;
		case 2:
			$tol_str = "Leave without pay";
			break;
	}
}else{
	$tol_str = "";
}

$today = date('Y-m-d');

$country_id = $_SESSION['country_default'];

$sql_str = "
	INSERT INTO
	`leave` (
		`date`,
		`employee`,
		`type_of_leave`,
		`lday_of_work`,
		`fday_back`,
		`num_of_days`,
		`reason_for_leave`,
		`line_manager`,
		`status`,
		`country_id`,
		`backup_leave`
	)
	VALUES (
		'{$today}',
		'{$employee}',
		'{$type_of_leave}',
		'{$lday_of_work2}',
		'{$fday_back2}',
		'{$num_of_days}',
		'{$reason_for_leave}',
		'{$line_manager}',		
		'Pending',
		'{$country_id}',
		'{$backup_leave}'
	)
";

mysql_query($sql_str);


$leave_id = mysql_insert_id();


// EMAIL IT
$params = array(
	'leave_id' => $leave_id,
	'output' => 'S'
);
$pdf_data = $crm->getLeavePdf($params);

// get country data
$cntry_sql = getCountryViaCountryId();
$cntry = mysql_fetch_array($cntry_sql);

// get staff data
// employee
$params2 = array( 'staff_id' => $employee );
$emp_sql = $crm->getStaffAccount($params2);
$emp = mysql_fetch_array($emp_sql);
$emp_name = "{$emp['FirstName']} {$emp['LastName']}";

// line manager
$params2 = array( 'staff_id' => $line_manager );
$emp_sql = $crm->getStaffAccount($params2);
$emp = mysql_fetch_array($emp_sql);
$lm_name = "{$emp['FirstName']} {$emp['LastName']}";
$lm_email = $emp['Email'];

$subject = "Leave request for {$emp_name}";
//$to = array( 'danielk@sats.com.au', 'vaultdweller123@gmail.com' );
//$to = array( 'vaultdweller123@gmail.com' );
//$to = array('hr@sats.com.au');
//$to = array($cntry['outgoing_email']);
$to = array(HR_EMAIL,$lm_email);



$email_body = "
<h1>Leave Request</h1>
<table style='margin:0;'>
	<tr>
		<td>Date</td><td>".date('d/m/Y',strtotime($today))."</td>
	</tr>
	<tr>
		<td>Name</td><td>{$emp_name}</td>
	</tr>
	<tr>
		<td>Type of Leave</td><td>".($crm->getTypesofLeave($type_of_leave))."</td>
	</tr>
	<tr>
		<td>Backup Leave</td><td>{$tol_str}</td>
	</tr>
	<tr>
		<td>First Day of Leave</td><td>{$lday_of_work}</td>
	</tr>
	<tr>
		<td>Last Day of Leave</td><td>{$fday_back}</td>
	</tr>
	<tr>
		<td>Number of days</td><td>{$num_of_days}</td>
	</tr>
	<tr>
		<td>Reason for Leave</td><td>{$reason_for_leave}</td>
	</tr>
	<tr>
		<td>Line Manager</td><td>{$lm_name}</td>
	</tr>
</table>
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

$pdf_filename = 'leave_'.date('dmYHis').'.pdf';

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



header("location: /leave_form.php?success=1");

?>