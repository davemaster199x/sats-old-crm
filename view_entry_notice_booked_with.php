<?php
include('inc/init.php');

// init the variables
$job_id = $_GET['job_id'];

if(!is_numeric($job_id)) exit();

# Job Details
$job_details = getJobDetails($job_id);

// booked with
$booked_with_name = $_GET['booked_with'];

# Letterhead?
if($_GET['letterhead'])
{
    define('EXTERNAL_PDF', true);
}

# Force type 'post' on QLD doc if viewing
define('TYPE_POST', 1);

require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdi-1.4.4/fpdi.php');

// If QLD Show the Form9, otherwise the generic SATS letter

require_once('inc/pdf_entry_notice_booked_with.php');

//echo "job ID: {$job_id}<br />";
//echo "State: {$job_details['state']}<br />";
$random_str = $job_id.'-'.rand().'-'.date('dmYHis');

$pdf->Output("entryletter-{$random_str}.pdf", 'I');
?>
