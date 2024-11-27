<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$credit_id = mysql_real_escape_string($_POST['credit_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log

if ($credit_id != '') {
    $invPaymentQ = mysql_query("
		SELECT ic.*,cr.reason
		FROM `invoice_credits` ic
                LEFT JOIN credit_reason cr ON cr.credit_reason_id=ic.credit_reason
		WHERE ic.`invoice_credit_id` = {$credit_id}
	");
    $invPayment = mysql_fetch_assoc($invPaymentQ);
    $invAmount = $invPayment['credit_paid'];
    $invPayType = $invPayment['reason'];
    $invPayRef = $invPayment['payment_reference'];
    if ($invPayRef === null || empty($invPayRef)) {
        $invPayRef = "No Credit Reference";
    }
    // UPDATED
    mysql_query("
		DELETE
		FROM `invoice_credits` 
		WHERE `invoice_credit_id` = {$credit_id}
	");
    $comment = "<b>$invPayType</b> of <b>$invAmount</b> Credits Deleted: <b>$invPayRef</b>";
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
			'Credit',
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