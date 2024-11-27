<?php

include('inc/init.php');
//if ($_SESSION["agency_id"] == "")
 //   {
  //  header("Location: http://sat.cmcc.com.au/agents/invalid_login.php");
  //  }


$useday = $_GET['day'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];
$techid = $_GET['tech_id'];
$jobdate = $useyear."-".$usemonth."-".$useday;

// query rewritten, improved
$selectQuery = "SELECT j.`id`
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
WHERE sa.`StaffID` = $techid  
AND j.`date` = '$jobdate' 
AND p.`deleted` = 0
ORDER BY j.sort_order ASC;";

$jobs = mysqlMultiRows($selectQuery);

# Letterhead?
if($_GET['letterhead'])
{
    define('EXTERNAL_PDF', true);
}

# Force type 'post' on QLD doc if viewing
define('TYPE_POST', 1);

foreach($jobs as $job)
{
    $job_id = $job['id'];

    if(!is_numeric($job_id)) exit();

    # Job Details
    $job_details = getJobDetails($job_id);

    require_once('inc/fpdf/fpdf.php');
    require_once('inc/fpdi-1.4.4/fpdi.php');

    // If QLD Show the Form9, otherwise the generic SATS letter

    if($job_details['state'] == "QLD")
    {
        include('inc/pdf_entry_notice_qld.php');
    }else if($job_details['state'] == "SA"){
        include('inc/pdf_entry_notice_sa.php');
    }else if($job_details['state'] == "NSW"){
        include('inc/pdf_entry_notice_nsw.php');
    }else if($job_details['state'] == "ACT"){
        include('inc/pdf_entry_notice_act.php');
    }else {
        include('inc/pdf_entry_notice_generic.php');
    }
}

$pdf->Output('batch_entry_letters_entryletter-' . $jobdate . '.pdf', 'I');

?>
