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
	$job_details['FirstName'] = $tr['FirstName']; 
	$job_details['LastName'] = $tr['LastName']; 
	
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