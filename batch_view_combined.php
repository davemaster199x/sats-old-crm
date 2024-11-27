<?php

include('inc/init.php');
require('inc/fpdf/fpdf.php');



$query = "
SELECT j.id, a.agency_name FROM jobs j, property p, agency a
WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Merged Certificates')  " . $user->prepareStateString('AND', 'p.') . " 
AND a.send_combined_invoice = 1
ORDER BY a.agency_name ASC";

$jobs = mysqlMultiRows($query);


if(sizeof($jobs) == 0)
{
	echo "Nothing to print - either there are no jobs in the merged certificate state - or all the jobs are set to email the documents instead.";
}
else
{
	$pdf=new FPDF('P','mm','A4');

	foreach($jobs as $job)
	{
	
		$job_id = $job['id'];
		
		if(!is_numeric($job_id)) exit();
		
		# Job Details
		$job_details = getJobDetails2($job_id);
		
		# Alarm Details
		$alarm_details = getPropertyAlarms($job_id, 1, 0, 2);
		$num_alarms = sizeof($alarm_details);
		
		# Property + Agent Details
		$property_details = getPropertyAgentDetails($job_details['property_id']);
		
		# Safety Switch Details
		$safety_switches = getPropertyAlarms($job_id, 0, 1, 4);
		$num_safety_switches = sizeof($safety_switches);

		# Corded Window Details
		$corded_windows = getPropertyAlarms($job_id, 1, 0, 6);
		$num_corded_windows = sizeof($corded_windows);

		$job_tech_sheet_job_types = getTechSheetAlarmTypesJob($job_id, true);
		
		/*
		# Sync price if not already
		if(!$job_details['price_used'])
		{
			$job_details['job_price'] = syncJobPrice($job_id, $property_details['price'], $job_details['job_type']);
			
		}
		*/
		
		# Run each job through the invoice template
		# TODO - better way than including file over and over  
		$print = true;
		include('inc/pdf_combined_template.php');
	
	}
		    
	$pdf->Output();
}	        

?>
