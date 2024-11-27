<?php

include('inc/init.php');
require('inc/fpdf/fpdf.php');

// Initiate job class
$jc = new Job_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'Merged Certificates';

// get jobs
$j_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$date,$phrase,'',0,'',0);


if(mysql_num_rows($j_sql)==0)
{
	echo '
	<style>	
	div.success {
		background-color: #DFF2BF;
		border: 1px solid #4F8A10;
		color: #4F8A10;
		margin: 5px 0;
		padding: 10px;
	}
	</style>
	<div class="success">Nothing to print - either there are no jobs in the merged certificate state - or all the jobs are set to email the documents instead.</div>';
}
else
{

	$pdf=new FPDF('P','mm','A4');
	
	while($job=mysql_fetch_array($j_sql))
	{
	
		$job_id = $job['jid'];
		
		if(!is_numeric($job_id)) exit();
		
		
		# Job Details
		$job_details = getJobDetails2($job_id);
		
		# Appliance Details
		$appliance_details = getPropertyAlarms($job_id, 1, 0, 1);
		$num_appliances = sizeof($appliance_details);

		# Alarm Details
		$alarm_details = getPropertyAlarms($job_id, 1, 0, 2);
		$num_alarms = sizeof($alarm_details);

		# Safety Switch Details
		$safety_switches = getPropertyAlarms($job_id, 0, 1, 4);
		$num_safety_switches = sizeof($safety_switches);

		# Corded Window Details
		$corded_windows = getPropertyAlarms($job_id, 1, 0, 6);
		$num_corded_windows = sizeof($corded_windows);

		$job_tech_sheet_job_types = getTechSheetAlarmTypesJob($job_id, true);
		
		# Property + Agent Details
		$query = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, a.agency_name
		FROM property p 
		LEFT JOIN agency a ON p.agency_id = a.agency_id
		WHERE p.property_id = '" . $job_details['property_id'] . "'";
		$property_details = mysqlSingleRow($query);
		
		# Run each job through the certificate template
		$print = true;
		include('inc/pdf_certificate_template.php');
	
	}
		    
	$pdf->Output();
}	        

?>
