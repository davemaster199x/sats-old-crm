<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$ir_id = mysql_real_escape_string($_POST['ir_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today = date('Y-m-d H:i:s');
$job_log_type = 2; // job accounts log

if ($ir_id != '') {
    $invPaymentQ = mysql_query("
		SELECT ir.*,pt.pt_name
		FROM `invoice_refunds` ir
                LEFT JOIN payment_types pt ON pt.payment_type_id=ir.type_of_payment
		WHERE ir.`invoice_refund_id` = {$ir_id}
	");
    $invPayment = mysql_fetch_assoc($invPaymentQ);
    $invAmount = $invPayment['amount_paid'];
    $invPayType = $invPayment['pt_name'];
    $invPayRef = $invPayment['payment_reference'];
    if ($invPayRef === null || empty($invPayRef)) {
        $invPayRef = "No Credit Reference";
    }
    $comment = "<b>$invPayType</b> of <b>$invAmount</b> Refund Deleted: <b>$invPayRef</b>";
    // UPDATED
    mysql_query("
		DELETE
		FROM `invoice_refunds` 
		WHERE `invoice_refund_id` = {$ir_id}
	");

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
			'Refund',
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