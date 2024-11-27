<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$ip_id = mysql_real_escape_string($_POST['ip_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log

if ($ip_id != '') {

    $invPaymentQ = mysql_query("
		SELECT ip.*,pt.pt_name
		FROM `invoice_payments` ip
                LEFT JOIN payment_types pt ON pt.payment_type_id=ip.type_of_payment
		WHERE ip.`invoice_payment_id` = {$ip_id}
	");
    $invPayment = mysql_fetch_assoc($invPaymentQ);
    $invAmount = $invPayment['amount_paid'];
    $invPayType = $invPayment['pt_name'];
    $invPayRef = $invPayment['payment_reference'];
    if($invPayRef===null ||empty($invPayRef)) {
        $invPayRef = "No Payment Reference";
    }

    // UPDATED
    mysql_query("
		DELETE
		FROM `invoice_payments` 
		WHERE `invoice_payment_id` = {$ip_id}
	");
$comment = "<b>$invPayType</b> of <b>$invAmount</b> Payment Deleted: <b>$invPayRef</b>";
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
			'Payment',
			'" . date('Y-m-d') . "',
			'$comment',
			{$job_id}, 
			'{$logged_user}',
			'" . date('H:i') . "',
			{$job_log_type},
			'{$today}'
		)
	");

    // AUTO - UPDATE INVOICE DETAILS
    $crm->updateInvoiceDetails($job_id);
}
?>