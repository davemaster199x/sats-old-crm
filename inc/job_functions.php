<?php

function getJobList($params)
{
	global $user;
	
	$query  = "SELECT SQL_CALC_FOUND_ROWS j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y') AS date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, 
	p.property_id, a.send_emails, a.account_emails, j.client_emailed 
	
	FROM (jobs j, property p, agency a "; 
	
	#Join in Postcode Region table if required
	if(intval($params['postcode_region_id']) > 0)
	{
		$query .= ", postcode_regions r";
	}
	
	$query .= " ) LEFT JOIN job_log l ON l.job_id = j.id   
	
	WHERE a.agency_id = p.agency_id 
	AND j.property_id = p.property_id "; 
	
	// Add any generic filter
	$query .= $params['filter'];
	
	// Filter out Status
	if(!empty($params['status']))
	{
		$query .= " AND j.status='{$params['status']}' ";	
	}
	
	// Filter out Deleted
	if(is_numeric($params['deleted']))
	{
		$query .= " AND p.deleted='{$params['deleted']}' ";	
	}			
	
	// Add State based filter
	$query .= $user->prepareStateString('AND', 'p.');
	
	// Add search if needed
	$fields_to_search = array('j.id', 'j.comments', 'j.tech_comments', 'a.agency_name');
	$params['search'] = trim(strtolower($params['search']));
	$params['search'] = str_replace(" ", "%", $params['search']);
	
	// Add Agency Filter if needed
	if(is_numeric($params['agency_id']))
	{
		$query .= " AND p.agency_id = {$params['agency_id']} ";
	}
	
	if($params['search'] != "")
	{
		$query .= " AND (";
		
		# Agency address search	
		$query .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$params['search']}%') OR ";
		
		# Property address search
		$query .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['search']}%') OR ";
	
		foreach($fields_to_search as $field)
		{
			$query .= " LOWER(".$field . ") LIKE '%" . $params['search'] . "%' OR ";
		}
	
		$query .= " )";
		$query = str_replace("OR  )", " )", $query);
	}
	
	if($params['job_type'] != ""){
		$query .= " AND (";
		
		$query .= " j.job_type = '".$params['job_type']."')";
	}
	
	# Search against postcode string
	if(intval($params['postcode_region_id']) > 0)
	{
		$query .= " AND (length(p.postcode) = 4 OR length(p.postcode) = 5) AND r.postcode_region_id = " . $params['postcode_region_id'] . " AND r.postcode_region_postcodes LIKE CONCAT('%', p.postcode, '%') ";
	}
	
	$query .= " GROUP BY j.id ORDER BY j.job_type, p.address_3 ";
	
	$query .= " LIMIT {$params['start']},{$params['limit']}";

	$job_list = mysqlMultiRows($query);
	
	return $job_list;

}

function canJobBeEdited($job_id, $user_id)
{
	// names defined in config.php
	$allowed_staff = array(ADMIN_ID, ADAM, CRAIG, JANINE, ASHLEIGH, DANIEL, TED);

	# Get Job Status 
	$query = "SELECT status FROM jobs WHERE id = {$job_id} LIMIT 1";
	$r = mysqlSingleRow($query);

	if($r['status'] == "Completed" && in_array($user_id, $allowed_staff))
	{
		return 1;
	}
	else
	{
		if($r['status'] == "Completed")
		{
			return 0;
		}
		else
		{
			return 1;
		}
	}
}

/**
 * Some of the legacy pages run the MYSQL loop manually on the page so need the $query_only option
 */
function getJobDetails($job_id, $query_only = false)
{

	// improved query
	$query = "SELECT j.id, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, j.comments, j.retest_interval, j.auto_renew, j.job_type, 
	sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.time_of_day, j.assigned_tech, 
	p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, j.tech_comments, p.property_id, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, 
	a.agency_id, a.agency_name, a.address_1 AS agent_address_1, a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, 
	a.state AS agent_state, a.postcode  AS agent_postcode 
	, j.job_price, j.price_used, p.price, j.work_order , j.ts_noshow, 
	DATE_FORMAT(j.client_emailed, '%e/%m/%Y @ %r' ) AS LastSent, ts_doorknock, p.agency_deleted, a.send_combined_invoice, 
	DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.key_access_required, p.tenant_email1, p.tenant_email2, p.tenant_mob1, p.tenant_mob2,
	DATE_FORMAT(j.entry_notice_emailed, '%d/%m/%Y @ %r') AS EntryNoticeLastSent, sa.ContactNumber, 
	DATE_FORMAT(j.date, '%W') as booking_date_name,
	DATE_FORMAT(j.date, '%d') AS booking_date_day,
	DATE_FORMAT(j.date, '%m') AS booking_date_month,
	DATE_FORMAT(j.date, '%Y') AS booking_date_year,
	a.agency_emails,
	a.send_entry_notice,
	DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
	j.ss_location,
	j.ss_quantity,
	j.tmh_id,
	j.`start_date`,
	j.`due_date`,
	a.`agency_emails`,
	j.`del_job`,
	j.`booked_with`,
	j.`booked_by`,
	j.ts_db_reading, 
	p.key_number, 
	j.price_reason, 
	j.price_detail, 
	j.`urgent_job`, 
	j.`urgent_job_reason`, 
	p.`tenant_changed`, 
	j.`date` AS jdate, 
	p.`holiday_rental`, 
	p.`alarm_code`,
	a.`key_email_req`,
	j.`preferred_time`,
	j.`ps_qld_leg_num_alarm`,
	j.`allocate_opt`,
	j.`allocate_notes`,
	j.`allocated_by`,
	p.`no_en`,
	j.`property_vacant`,
	p.`address_1` AS p_street_num,
	p.`address_2` AS p_street_name,
	p.`address_3` AS p_suburb,
	p.`qld_new_leg_alarm_num`,
	
	p.`tenant_firstname3`, 
	p.`tenant_lastname3`, 
	p.`tenant_ph3`, 
	p.`tenant_mob3`, 
	p.`tenant_email3`,	
	p.`is_sales`,
	
	p.`tenant_firstname4`, 
	p.`tenant_lastname4`, 
	p.`tenant_ph4`, 
	p.`tenant_mob4`, 
	p.`tenant_email4`,
	
	j.`dha_need_processing`,
	j.`out_of_tech_hours`,
	
	j.`call_before`,
	j.`call_before_txt`,
	
	a.`account_emails`,
	
	a.`franchise_groups_id`,
	
	j.`show_as_paid`,
	j.`to_be_printed`,
	j.`repair_notes`,
	
	p.`prop_upgraded_to_ic_sa`,
	
	j.`job_priority`,
	
	`invoice_amount`,
	`invoice_payments`,
	`invoice_credits`,
	`invoice_balance`,
	
	a.`key_allowed`,
	a.`electrician_only`,
	
	j.`status` AS jstatus,
	j.`assigned_tech`,
	j.`en_date_issued`,
	j.`cancelled_date`,
	j.`deleted_date`,

	a.`allow_upfront_billing`,
	a.`send_en_to_agency`,

	p.`landlord_firstname`,
	p.`landlord_lastname`
	
	
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
	WHERE  j.`id` = {$job_id}";

	if(!$query_only)
	{
		$job_details = mysqlSingleRow($query);
		return $job_details;
	}
	else
	{
		return $query;
	}
}


function getJobDetails2($job_id, $query_only = false){

	$query = "SELECT j.id, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, j.comments, j.retest_interval, j.auto_renew, j.job_type, 
	sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.time_of_day, j.assigned_tech, 
	p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, j.tech_comments, p.property_id, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, 
	a.agency_id, a.agency_name, a.address_1 AS agent_address_1, a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, a.state AS agent_state, a.postcode  AS agent_postcode 
	, j.job_price, j.price_used, p.price, j.work_order , j.ts_noshow, 
	DATE_FORMAT(j.client_emailed, '%e/%m/%Y @ %r' ) AS LastSent, ts_doorknock, p.agency_deleted, a.send_combined_invoice, 
	DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.key_access_required, p.tenant_email1, p.tenant_email2, p.tenant_mob1, p.tenant_mob2,
	DATE_FORMAT(j.entry_notice_emailed, '%d/%m/%Y @ %r') AS EntryNoticeLastSent, sa.ContactNumber, 
	DATE_FORMAT(j.date, '%W') as booking_date_name,
	DATE_FORMAT(j.date, '%d') AS booking_date_day,
	DATE_FORMAT(j.date, '%m') AS booking_date_month,
	DATE_FORMAT(j.date, '%Y') AS booking_date_year,
	a.agency_emails,
	a.send_entry_notice,
	DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
	j.ss_location,
	j.ss_quantity,
	j.tmh_id,
	j.ts_db_reading, p.key_number, 
	j.price_reason, 
	j.price_detail, 
	j.service AS jservice, 				
	a.`country_id`,
	j.`ps_qld_leg_num_alarm`,
	a.`account_emails`,
	p.`qld_new_leg_alarm_num`,
	a.`display_bpay`,
	j.`show_as_paid`,
	j.`invoice_balance`,
	j.`invoice_payments`,
	a.`allow_upfront_billing`,
	j.`date` AS jdate,
	p.`prop_upgraded_to_ic_sa`
	
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
	WHERE  j.`id` = {$job_id}";

	if(!$query_only)
	{
		$job_details = mysqlSingleRow($query);
		return $job_details;
	}
	else
	{
		return $query;
	}
}


function removedAlarm($job_id, $incnew = 1, $discarded = 1, $alarm_job_type_id = 1)
{
	$query = "  SELECT a.*, p.alarm_pwr, t.alarm_type, adr.reason  
				FROM alarm a 
					LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
					LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
					LEFT JOIN alarm_discarded_reason adr ON adr.id = a.ts_discarded_reason
				WHERE a.job_id = '" . $job_id . "'";

	if($alarm_job_type_id == 4 || $alarm_job_type_id == 5) // Safety Switch view and mech should have same alarms
	{
		$query .= " AND a.alarm_job_type_id IN (4,5)";
	}
	else
	{
		$query .= " AND a.alarm_job_type_id = {$alarm_job_type_id}";
	}

	
	
	if($incnew == 0) $query .= " AND a.New = 0";
	if($incnew == 2) $query .= " AND a.New = 1";
	
	if($discarded == 0) $query .= " AND a.ts_discarded = 0";
	if($discarded == 2) $query .= " AND a.ts_discarded = 1";
	
	$query .= " ORDER BY a.alarm_id ASC ";

	$alarms = mysqlMultiRows($query);
	
	return $alarms;
}


function addJobLog($params)
{
	$insertQuery = "INSERT INTO job_log (contact_type,eventdate,comments,job_id, staff_id, eventtime) VALUES ('{$params->contact_type}','{$params->eventdate}','{$params->comments}','{$params->job_id}', '{$params->staff_id}', '{$params->eventime}')";

	return mysql_query($insertQuery);
}

function getTechSheetJobTypes()
{
	$sql = "SELECT id,type, html_id FROM alarm_job_type";

	return mysqlMultiRows($sql);
}

function updateTechSheetAlarmTypes($job_id, $alarm_job_types)
{
	$alarm_job_types = explode(",", $alarm_job_types);

	if(is_array($alarm_job_types) && sizeof($alarm_job_types) > 0)
	{
		// Clear Existing job types;
		$sql = "DELETE FROM property_propertytype WHERE property_id = {$job_id}";
		mysql_query($sql) or die(mysql_error());
		 
		// Add New types
		$sql = "INSERT INTO property_propertytype (property_id, alarm_job_type_id) VALUES ";

		foreach($alarm_job_types as $type)
		{
			$sql .= " (" . $job_id . ", " . $type . "),"; 
		}

		$sql = trim($sql, ","); // Get rid of any trailing ,s
		mysql_query($sql) or die(mysql_error());
	}

	return true;
}

function getTechSheetAlarmTypesJob($property_id, $fixed = false)
{
	$fixed_array = array();

	$sql = "SELECT ppt.alarm_job_type_id, ajt.type, ajt.html_id, ajt.include_file FROM property_propertytype ppt, alarm_job_type ajt
			WHERE ppt.alarm_job_type_id = ajt.id AND ppt.property_id = {$property_id} ORDER BY ajt.id ASC";

	$result = mysqlMultiRows($sql);

	if($fixed)
	{
		if(is_array($result) && sizeof($result) > 0)
		{
			foreach($result as $row)
			{
				$fixed_array[$row['alarm_job_type_id']] = $row['type'];
			}
		}
		return $fixed_array;
	}
	else
	{
		return $result;
	}
}

/**
 * Calculate job total and return price
 * @param  id $job_id job id
 * @return float         price float
 */
function calculateJobTotal($job_id)
{
	$price = 0;

	# Job Details
	$job_details = getJobDetails($job_id);

	# Alarm Details
	$alarm_details = getPropertyAlarms($job_id, 1, 0, 2);

	$price = $job_details['job_price'];

	foreach($alarm_details as $alarm)
	{
		if($alarm['new'] == 1)
		{
			$price += $alarm['alarm_price'];
		}
	}

	return $price;
}

function getJobTypes()
{
	$sql = "SELECT * FROM job_type";

	$result = mysqlMultiRows($sql);
	
	return $return;
}

// get pdf invoice service includes description
function getServiceIncludesDesc($pdf,$jt,$serv,$country_id){
	
	$country_id2 = ($country_id=="")?$_SESSION['country_default']:$country_id;
	
	# description
	if( ($jt=="Yearly Maintenance" || $jt=="Change of Tenancy" || $jt=="Once-off") && ( $serv==2 || $serv==12 ) ){
		
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Surveying the Quantity and Location of Smoke Alarms');
		$pdf->Cell(160, 5, '* Inspecting Alarms for Secure Fitting');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Replacing Batteries in all Alarms where Replaceable');
		$pdf->Cell(160, 5, '* Cleaning Alarms with a Smoke Alarm Wipe');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Testing Alarms with the Manual Test Button');
		$pdf->Cell(160, 5, '* Verifying Expiry Dates on All Alarms');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Measure and Record Alarm Decibel Readings');
		$pdf->Cell(160, 5, '* Checking Alarms for Visual Indicators');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Checking Alarms meet '.getCountryText($country_id2).' Standards');
		$pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
		$pdf->SetFont('Arial','',11);

	
	}else if( $jt=="Fix or Replace" && ( $serv==2 || $serv==12 ) ){
	
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Fix or Replace Problem Alarm');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Conduct Full Test on Alarm');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Record all Details in SATS Database');
		$pdf->SetFont('Arial','',11);
	
	}else if( $jt=="Lease Renewal" && ( $serv==2 || $serv==12 ) ){
	
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Surveying the Quantity and Location of Smoke Alarms');
		$pdf->Cell(160, 5, '* Inspecting Alarms for Secure Fitting');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Replacing Batteries in all Alarms where Replaceable');
		$pdf->Cell(160, 5, '* Cleaning Alarms with a Smoke Alarm Wipe');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Testing Alarms with the Manual Test Button');
		$pdf->Cell(160, 5, '* Verifying Expiry Dates on All Alarms');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Measure and Record Alarm Decibel Readings');
		$pdf->Cell(160, 5, '* Checking Alarms for Visual Indicators');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Checking Alarms meet '.getCountryText($country_id2).' Standards');
		$pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
		$pdf->SetFont('Arial','',11);
	
	}else if($jt=="Yearly Maintenance"&&$serv==6){
	
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		
		$pdf->Cell(108, 5, '* Verifying and Installing Clips on all Windows where Required');
		$pdf->Cell(160, 5, '* Inspecting all Window Coverings');
		$pdf->Ln();
		
		$pdf->Cell(108, 5, '* Verifying and Installing Tags on all Windows where Required');
		$pdf->Cell(160, 5, '* Surveying all Windows in the Property');
		$pdf->Ln();

		$pdf->Cell(108, 5, '* Verifying that all cord loops Meet Legislation');
		$pdf->Cell(160, 5, '* The Recording of all Details in SATS Database');
		$pdf->SetFont('Arial','',11);
	
	}else if($jt=="Fix or Replace"&&$serv==6){
	
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Repair Problem Window Covering');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Conduct Full Survey of all Windows');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Record all Details in SATS Database');
		$pdf->SetFont('Arial','',11);

	
	}else if($serv==5){
	
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Determine and Record Safety Switch Quantity');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Determine and Record Safety Switch Make');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Determine and Record Safety Switch Model');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Perform Mechanical test on all Safety Switches and Record results');
		$pdf->Ln();
	
	}

}

// get service
function getService($serv_id){
	return mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$serv_id}
		AND `active` = 1
	");
}

function getCountryText($country_id){
	
	$country_id2 = ($country_id=="")?$_SESSION['country_default']:$country_id;
	
	$c_sql = mysql_query("
		SELECT *
		FROM `countries` 
		WHERE `country_id` = {$country_id2}
	");
	$c = mysql_fetch_array($c_sql);
	
	switch($c['country_id']){
		case 1:
			$country_text = 'Australian';
		break;
		case 2:
			$country_text = 'New Zealand';
		break;
		case 3:
			$country_text = 'Canadian';
		break;
		case 4:
			$country_text = 'British';
		break;
		case 5:
			$country_text = 'American';
		break;
	}
	
	return $country_text;
}

?>