<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_expense.php');

$crm = new Sats_Crm_Class;

// Leave Request Form
$employee = mysql_real_escape_string($_POST['employee']);
$total_amount = mysql_real_escape_string($_POST['total_amount']);
$expense_arr = $_POST['expense_id'];
$country_id = $_SESSION['country_default'];
$line_manager = mysql_real_escape_string($_POST['line_manager']);

$sql_str = "
	INSERT INTO
	`expense_summary` (
		`date`,
		`employee`,
		`total_amount`,
		`line_manager`,
		`country_id`
	)
	VALUES (
		'".date('Y-m-d')."',
		'{$employee}',
		'{$total_amount}',
		'{$line_manager}',
		'{$country_id}'
	)
";

mysql_query($sql_str);
$exp_sum_id = mysql_insert_id();

foreach( $expense_arr as $expense_id ){
	mysql_query("
		UPDATE `expenses`
		SET `expense_summary_id` = {$exp_sum_id}
		WHERE `expense_id` = {$expense_id}
	");
}



// EMAIL IT
$jparams = array(
	'exp_sum_id' => $exp_sum_id,
	'country_id' => $country_id,
	'output' => 'S'
);
$pdf_data = $crm->getExpenseSummaryPdf($jparams);

// get country data
$cntry_sql = getCountryViaCountryId();
$cntry = mysql_fetch_array($cntry_sql);

// employee
$params2 = array( 'staff_id' => $employee );
$emp_sql = $crm->getStaffAccount($params2);
$emp = mysql_fetch_array($emp_sql);
$emp_name = "{$emp['FirstName']} {$emp['LastName']}";

$lm_params = array( 'staff_id' => $line_manager );
$lm_sql = $crm->getStaffAccount($lm_params);
$lm = mysql_fetch_array($lm_sql);
$lm_name = "{$lm['FirstName']} {$lm['LastName']}";
$lm_email = $lm['Email'];


$subject = "Expense Summary for {$emp_name}";
$to = array(ACCOUNTS_EMAIL,$lm_email);
//$to = array('pokemaniacs123@yahoo.com');



$email_body = "
<p>
	<table style='border:none; margin: 0;'>
		<tr><td>Date: </td><td>".date('d/m/Y')."</td></tr>
		<tr><td>Staff: </td><td>{$emp_name}</td></tr>
		<tr><td>Amount: </td><td>$".number_format($total_amount,2)."</td></tr>
		<tr><td>Line Manager: </td><td>{$lm_name} <strong style='color:red;'>APPROVAL REQUIRED</strong></td></tr>
	</table>
</p>
<p>Please find attached Expense Claim Form</p>
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

$pdf_filename = 'expense_summary_'.date('dmYHis').'.pdf';

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


header("location: /expense.php?exp_state_succ=1");

?>