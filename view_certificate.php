<?php

include('inc/init.php');

// init the variables
$job_id = $_GET['job_id'];
$output_type = $_GET['output_type'];

// append checkdigit to job id for new invoice number
$check_digit = getCheckDigit(trim($job_id));
$bpay_ref_code = "{$job_id}{$check_digit}";	

require_once('inc/pdfInvoiceCertComb.php');

require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');
require_once('inc/pdf_certificate_template.php');

$output_type2 = ($output_type!='')?$output_type:'I'; 
    
$pdf->Output('cert' . $bpay_ref_code . '.pdf', $output_type2);

?>
