<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');
//include($_SERVER['DOCUMENT_ROOT'].'inc/precompleted_jobs_functions.php'); 

$country_id = 1;


// Initiate job class
$jc = new Job_Class();


$staff_id = -1; // for AUTO process
$job_status = 'Pre Completion';

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','','','',0,'','','','','','','',$country_id);



$job_id_arr = [];

if(mysql_num_rows($plist)>0){
	
	while($row = mysql_fetch_array($plist)){
		
		$row_color = '';
		$reason = '';
		$hide_ck = 0;
		
		/*
		if( 
			$row['job_reason_id']>0 || 
			isAlarmExpiryDatesMatch($row['jid'])==true || 
			isJobZeroPrice_Ym($row['jid'])==true ||
			isJobHasNewAlarm($row['jid'])==true ||
			isPropertyAlarmExpired($row['property_id'])==true
		){
			$row_color = 'yello_mark';
		}else{
			$row_color = '';
		}
		*/
		
		
		
		// Expiry Dates don't match
		if( isAlarmExpiryDatesMatch($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "Expiry Dates Don't Match <br />";
		}
		
		// hide for FG: Compass Housing
		if( $row['franchise_groups_id'] != 39 && $_SESSION['country_default'] == 1 ){
			
			// Job is $0 and YM
			if( isJobZeroPrice_Ym($row['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Job is $0 and YM <br />";
			}
			
		}
		
		
		// New Alarms Installed
		if( isJobHasNewAlarm($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "New Alarms Installed <br />";
		}

		// if it has repair notes
		if( $row['repair_notes'] != '' ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "Repair Notes <br />";
		}
		
		// IC upgraded property not 119 check, exluding CW
		if( $row['jservice'] != 6 && $row['prop_upgraded_to_ic_sa'] == 1 && $row['job_type'] == 'Yearly Maintenance' && $row['job_price'] != 119  ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "IC Job not $119<br />";
		}
		
		// if 240v rebook
		if($row['job_type']=='IC Upgrade'){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "Job type can't be IC Upgrade<br />";
		}
		
		// Property has Expired Alarms
		if( isPropertyAlarmExpired($row['jid'],$row['property_id'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "Expired Alarms <br />";
		}
		
		// COT FR and LR price must be 0
		if( CotLrFrPriceMustBeZero($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= getJobTypeAbbrv($row['job_type'])." must be $0 <br />";
		}
		
		// If 240v has 0 price
		if( is240vPriceZero($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Check Job Type <br />";
		}
		
		
		// if 240v rebook
		if($row['job_type']=='240v Rebook'){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "240v Rebook <br />";
		}
		
		// If discarded alarm is not equal to new alarm
		if( isMissingAlarms($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Discarded Alarms don't match Installed Alarms <br />";
		}
		
		// If NO alarms, exclude CW
		if( isNoAlarms($row['jid'])==true && $row['jservice']!=6 ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " No installed Alarms <br />";
		}
		
		// If job date is not today
		if( isJobDateNotToday($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Check Job Date <br />";
		}
		
		// If Job notes is present
		$tech_notes_pres_flag = 0;
		if( $row['tech_comments']!='' ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Check Tech notes <br />";
			$tech_notes_pres_flag = 1;
		}												
		
		// if franchise group = private
		if( $row['franchise_groups_id'] == 10 ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Payment Required Before Processing <br />";
		}
		
		
		// If Urgent
		if( $row['urgent_job']==1 ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Urgent or Out of Scope <br />";
		}
		
		//  if SS has any switched that are marked failed
		if( isSSfailed($row['jid'])==true ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= " Safety Switch Failed <br />";
		}
		
		//  if SS has any switched that are marked failed
		if( $jc->isSafetySwitchServiceTypes($row['jservice'])==true && $row['ss_quantity']=='' ){
			$hide_ck = 1;
			$row_color = 'green_mark';
			$reason .= "Safety Switch Quantity is blank<br />";
		}
		
		
		
		// MUST BE THE LAST - not completed due to = job reason
		if( ifDHAAgencies($row['jid'])==true  && $row['ts_completed']==0 ){
			$hide_ck = 1;
			$row_color = 'yello_mark';
			$reason .= " DHA Property <br />";
		}
		
		// MUST BE THE LAST - not completed due to = job reason
		$reason_icon = '';
		if( $row['job_reason_id']>0 && $row['ts_completed']==0 ){
			
			
			
			
			
			
			// if 'no keys at agency' or 'keys dont work' or 'no show' hide checkbox, show red sms icon
			if( $row['job_reason_id']==11 || $row['job_reason_id']==5 || $row['job_reason_id']==1 ){
				$hide_ck = 1;
				$reason_icon .= '<img src="images/red_sms.png" style="position: relative; top: 7px;" /> ';
			}else if( $row['job_reason_id']==2  || isDHAagencies($row['agency_id'])==true ){ // 240v rebook OR DHA agencies
				$hide_ck = 1;
			}else{ // default checkbox state for this if block
				$hide_ck = 0;
			}
			
			
			
			$row_color = 'yello_mark';
			// only show on reason: 'No Longer Managed by Agent' or 'Property Vacant'
			if( $row['job_reason_id']==17 || $row['job_reason_id']==18 ){
				$reason_icon .= '<img src="images/red_phone.png" style="position: relative; top: 7px;" /> ';
			}						
			$reason .= "{$reason_icon}{$row['jr_name']} <br />";
			
		}
		
		
		// if not completed, key access and reason is not 'no keys at agency' (sir Dan says this is the highest priority)
		if( $row['key_access_required']==1 && $row['ts_completed']==0 && $row['job_reason_id']!=11 ){
			$hide_ck = 1;
			$row_color = 'yello_mark';
			//$reason .= "Verify keys have been returned before Rebooking<br />";
		}
		
		
		if($row_color==''){
			$job_id_arr[] = $row['jid'];
		}
		
	}
	
}



//print_r($job_id_arr);

// MOVE TO MERGE
foreach($job_id_arr as $job_id){
		
	// update job to merged
	$job_sql_str = "
		UPDATE `jobs`
		SET `status` = 'Merged Certificates'
		WHERE `id` = {$job_id}
	";
	mysql_query($job_sql_str);
	
	// insert job log
	$jlog_str = "
		 INSERT INTO 
		 job_log (
		  `auto_process`, 
		  `comments`, 
		  `eventdate`, 
		  `contact_type`, 
		  `job_id`,
		  `eventtime`
		 ) 
		 VALUES (
		  1, 
		  'Job status updated from <strong>Pre Completion</strong> to <strong>Merged Certificates</strong>', 
		  '".date("Y-m-d")."', 
		  'Merged Certificates', 
		  '{$job_id}',
		  '".date("H:i")."'
		 )
	";
	mysql_query($jlog_str);
	
}



?>