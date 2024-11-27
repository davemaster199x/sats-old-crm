<?php

include('inc/init_for_ajax.php');

// init the variables
$job_id = $_GET['job_id'];
$output_type = $_GET['output_type'];

// append checkdigit to job id for new invoice number
$check_digit = getCheckDigit(trim($job_id));
$bpay_ref_code = "{$job_id}{$check_digit}";	

require_once('inc/pdfInvoiceCertComb.php');

require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');
require_once('inc/pdf_quote_template.php');

$pdf_filename = 'quote_'.date('dmYHis').'.pdf';

$output_type2 = ($output_type!='')?$output_type:'I'; 

$pdf->Output($pdf_filename, $output_type2);

?>
