<?php

function getPrecompletedJobs($start,$limit,$sort,$order_by,$job_type,$service,$date,$phrase){

	$str = "";

	if($job_type!=""){
		$str .= " AND j.job_type = '{$job_type}' ";
	}

	if($service!=""){
		$str .= " AND j.`service` = '{$service}' ";
	}

	if($state!=""){
		$str .= " AND p.`state` = '{$state}' ";
	}

	if($date!=""){
		$str .= " AND j.`date` = '{$date}' ";
	}

	if($phrase != "")
	{
		$str .= " AND (";
			# Agency address search
			$str .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$phrase}%') OR ";
			# Property address search
			$str .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$phrase}%')";
		$str .= " )";
	}

	if( $sort!="" && $order_by!="" ){
		$str .= " ORDER BY {$sort} {$order_by} ";
	}

	if(is_numeric($start) && is_numeric($limit))
	{
		$str .= " LIMIT {$start}, {$limit}";
	}

	$sql = "
		SELECT *, j.`id` AS jid, j.`created` AS jcreated, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3,
		p.`state` AS p_state, j.`service` AS jservice, jr.`name` AS jr_name, j.`status` AS jstatus, j.`date` AS jdate
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.`status` = 'Pre Completion'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
		{$str}
	";
	return mysql_query($sql);

}

function pcjGetLastYMCompletedDate($property_id,$service){
	$sql = mysql_query("
		SELECT `id`, `date`
		FROM `jobs`
		WHERE `property_id` ={$property_id}
		AND `service` = {$service}
		AND `status` = 'Completed'
		AND `job_type` = 'Yearly Maintenance'
		AND `del_job` = 0
		ORDER BY `date` DESC
		LIMIT 0 , 1
	");
	$row = mysql_fetch_array($sql);
	echo ($row['date']!="" && $row['date']!="0000-00-00")?date("d/m/Y",strtotime($row['date'])):'';
}

// check if Expiry Dates don't match
function isAlarmExpiryDatesMatch($job_id){

	$sql = mysql_query("
		SELECT `alarm_id`
		FROM  `alarm`
		WHERE `expiry` !=  `ts_expiry`
		AND `job_id` ={$job_id}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}

}

// check if Job is $0 and YM
function isJobZeroPrice_Ym($job_id){
	$sql = mysql_query("
		SELECT `id`
		FROM  `jobs`
		WHERE  `job_price` =0.00
		AND  `job_type` = 'Yearly Maintenance'
		AND `id` ={$job_id}
		AND `del_job` = 0
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// check New Alarms Installed
function isJobHasNewAlarm($job_id){
	$sql = mysql_query("
		SELECT `alarm_id`
		FROM  `alarm`
		WHERE `new` = 1
		AND `job_id` ={$job_id}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// Property has Expired Alarms
function isPropertyAlarmExpired($job_id,$property_id){
	// instruction by sir dan to match either expiry or ts_expiry
	$sql = mysql_query("
		SELECT alrm.`alarm_id`
		FROM `alarm` AS alrm
		LEFT JOIN jobs AS j ON alrm.`job_id` = j.`id`
		LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
		WHERE (
			(
				alrm.`expiry` <= '".date("Y")."'
				AND alrm.`expiry` != ''
			) OR
			(
				alrm.`ts_expiry` <= '".date("Y")."'
				AND alrm.`ts_expiry` != ''
			)
		)
		AND alrm.`ts_discarded` = 0
		AND j.`id` ={$job_id}
		AND j.`property_id` ={$property_id}
		AND a.`country_id` = {$_SESSION['country_default']}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// COT FR and LR price must be 0
function CotLrFrPriceMustBeZero($job_id){
	$sql = mysql_query("
		SELECT `id`
		FROM  `jobs`
		WHERE (
			`job_type` = 'Change of Tenancy' OR
			`job_type` = 'Lease Renewal' OR
			`job_type` = 'Fix or Replace' OR
			`job_type` = 'Annual Visit'
		)
		AND `job_price` != 0.00
		AND `id` ={$job_id}
		AND `del_job` = 0
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// If 240v has 0 price
function is240vPriceZero($job_id){
	$sql = mysql_query("
		SELECT `id`
		FROM  `jobs`
		WHERE `job_type` = '240v Rebook'
		AND `job_price` = 0.00
		AND `id` ={$job_id}
		AND `del_job` = 0
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// display error for these agencies
function dhaAgencyIds() {
	return [3043,3036,3046,1902,3044,1906,1927,3045];
}

function ifDHAAgencies($job_id){
	$sql = mysql_query("
		SELECT `id`
		FROM `jobs` AS j
		LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`id` ={$job_id}
		AND a.`agency_id` IN(3043,3036,3046,1902,3044,1906,1927,3045)
		AND a.`country_id` = {$_SESSION['country_default']}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// If discarded alarm is not equal to new alarm
function isMissingAlarms($job_id){

	// discarded alarm
	$disc_sql = mysql_query("
		SELECT `alarm_id`
		FROM `alarm`
		WHERE `job_id` ={$job_id}
		AND `ts_discarded` =1
	");
	$dis_num = mysql_num_rows($disc_sql);

	// new alarm
	$new_sql = mysql_query("
		SELECT `alarm_id`
		FROM `alarm`
		WHERE `job_id` ={$job_id}
		AND `new` =1
		AND `ts_discarded` = 0
	");
	$new_num = mysql_num_rows($new_sql);

	if($dis_num==$new_num){
		return false;
	}else{
		return true;
	}

}

// If NO alarms
function isNoAlarms($job_id){

	// discarded alarm
	$a_sql = mysql_query("
		SELECT `alarm_id`
		FROM `alarm`
		WHERE `job_id` ={$job_id}
		AND `ts_discarded` = 0
	");

	if(mysql_num_rows($a_sql)==0){
		return true;
	}else{
		return false;
	}

}

// If job date is not today
function isJobDateNotToday($job_id){

	// discarded alarm
	$a_sql = mysql_query("
		SELECT `id`
		FROM `jobs`
		WHERE `id` ={$job_id}
		AND `date` != '".date("Y-m-d")."'
	");

	if(mysql_num_rows($a_sql)>0){
		return true;
	}else{
		return false;
	}

}

// If job date is not today
function isSSfailed($job_id){

	// discarded alarm
	$a_sql = mysql_query("
		SELECT `safety_switch_id`
		FROM `safety_switch`
		WHERE `job_id` ={$job_id}
		AND `test` = 0
	");

	if(mysql_num_rows($a_sql)>0){
		return true;
	}else{
		return false;
	}

}

?>