<?php

include('inc/init.php');

// init the variables
$job_id = $_GET['job_id'];

require_once('inc/pdfInvoiceCertComb.php');

require_once('inc/fpdf/fpdf.php');
require_once('inc/pdf_invoice_template.php');

$pdf->Output('invoice' . $job_id . '.pdf', 'I');

?>
