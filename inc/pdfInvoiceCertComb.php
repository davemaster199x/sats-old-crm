<?php



if(!is_numeric($job_id)) exit();

$crm->updateInvoiceDetails($job_id); ## initiate updateInvoiceDetails first

# Job Details
$job_details = getJobDetails2($job_id);

# Appliance Details
$appliance_details = getPropertyAlarms($job_id, 1, 0);
$num_appliances = sizeof($appliance_details);

# Alarm Details
$alarm_details = getPropertyAlarms($job_id, 1, 0, 2);
$num_alarms = sizeof($alarm_details);

# Safety Switch Details
$safety_switches = getPropertyAlarms($job_id, 0, 1, 4);
$num_safety_switches = sizeof($safety_switches);

# Corded Window Details
/*
$corded_windows = getPropertyAlarms($job_id, 1, 0, 6);
$num_corded_windows = sizeof($corded_windows);
*/
$cw_sql = mysql_query("
	SELECT *
	FROM `corded_window`
	WHERE `job_id` ={$job_id}
");

$job_tech_sheet_job_types = getTechSheetAlarmTypesJob($job_id, true);

# Property + Agent Details
$property_details = getPropertyAgentDetails($job_details['property_id']);

/*
# Sync price if not already
if(!$job_details['price_used'])
{
    $job_details['job_price'] = $property_details['price'];
    syncJobPrice($job_id, $property_details['price']);
    
}
*/

?>