<?php
include('inc/init_for_ajax.php');
//if ($_SESSION["agency_id"] == "")
 //   {
  //  header("Location: http://sat.cmcc.com.au/agents/invalid_login.php");
  //  }

// init the variables
$job_url_enc = $_GET['job_id'];

$encrypt = new cast128();
$encrypt->setkey(SALT);
$job_id = $encrypt->decrypt(utf8_decode(rawurldecode($job_url_enc)));




if(!is_numeric($job_id)) exit();

# Job Details
$job_details = getJobDetails($job_id);



if($_GET['tr_id']!=""){
	
	$tr_id = mysql_real_escape_string($_GET['tr_id']);
	$en_time = mysql_real_escape_string($_GET['en_time']);

	$tr_sql = mysql_query("
		SELECT * 
		FROM  `tech_run` AS tr 
		LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
		WHERE  tr.`tech_run_id` = {$tr_id}
	");
	$tr = mysql_fetch_array($tr_sql);
	
	$job_details['date'] = date('d/m/Y',strtotime($tr['date']));
	$job_details['time_of_day'] = $en_time;
	$job_details['first_name'] = $tr['FirstName']; 
	$job_details['last_name'] = $tr['LastName']; 
	
}else{
	$job_details['first_name'] = $job_details['FirstName']; 
	$job_details['last_name'] = $job_details['LastName']; 
}

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

if($job_details['state'] == "QLD")
{
    require_once('inc/pdf_entry_notice_qld.php');
}else if($job_details['state'] == "SA"){
	require_once('inc/pdf_entry_notice_sa.php');
}else if($job_details['state'] == "NSW"){
	require_once('inc/pdf_entry_notice_nsw.php');
}else if($job_details['state'] == "ACT"){
	require_once('inc/pdf_entry_notice_act.php');
}else {
	//require_once('inc/fpdi_override.php');
    require_once('inc/pdf_entry_notice_generic.php');
}


//echo "job ID: {$job_id}<br />";
//echo "State: {$job_details['state']}<br />";
$random_str = $job_id.'-'.rand().'-'.date('dmYHis');


$pdf->Output("entryletter-{$random_str}.pdf", 'I');
?>