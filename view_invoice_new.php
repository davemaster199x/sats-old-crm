<?php

include('inc/init_for_ajax.php');

# Get the query string stuff
$job_id = intval($_GET['i']);
$md5 = trim($_GET['m']);

$query = mysql_query("
	SELECT p.`agency_id` 
	FROM jobs AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	WHERE j.`id` = {$job_id}
");

$result = mysql_fetch_array($query);
$constructed_md5 = md5($result['agency_id'].$job_id);

if($constructed_md5 != $md5) exit(EXIT_MESSAGE . '3');

if(!is_numeric($job_id)) exit();

$output_type = $_GET['output_type'];

// append checkdigit to job id for new invoice number
$check_digit = getCheckDigit(trim($job_id));
$bpay_ref_code = "{$job_id}{$check_digit}";

require_once('inc/pdfInvoiceCertComb.php');

require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');
require_once('inc/pdf_invoice_template.php');

$output_type2 = ($output_type!='')?$output_type:'I'; 

$pdf->Output('invoice' . $bpay_ref_code . '.pdf', $output_type2);

?>
