<?php

function bkd_getPrecompletedJobs($start,$limit,$sort,$order_by,$job_type,$service,$date,$phrase,$tech_id){

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
	
	if($tech_id!=""){
		$str .= " AND j.`assigned_tech` = '{$tech_id}' ";  
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
		SELECT *, j.`id` AS jid, j.`created` AS jcreated, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, 
		j.`service` AS jservice, jr.`name` AS jr_name, j.`status` AS jstatus, j.`date` AS jdate
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}		
		{$str}
	";
	return mysql_query($sql);

}

function bkd_pcjGetLastYMCompletedDate($property_id,$service){
	$sql = mysql_query("
		SELECT *
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
function bkd_isAlarmExpiryDatesMatch($job_id){
	
	$sql = mysql_query("
		SELECT * 
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
function bkd_isJobZeroPrice_Ym($job_id){
	$sql = mysql_query("
		SELECT * 
		FROM  `jobs` 
		WHERE  `job_price` =0.00
		AND  `job_type` = 'Yearly Maintenance'
		AND `del_job` = 0
		AND `id` ={$job_id}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// check New Alarms Installed
function bkd_isJobHasNewAlarm($job_id){
	$sql = mysql_query("
		SELECT * 
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
function bkd_isPropertyAlarmExpired($job_id,$property_id){
	$sql = mysql_query("
		SELECT * 
		FROM `alarm` AS alrm
		LEFT JOIN jobs AS j ON alrm.`job_id` = j.`id`
		LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
		WHERE alrm.`expiry` = '".date("Y")."'
		AND alrm.`ts_discarded` = 0
		AND j.`id` ={$job_id} 
		AND j.`property_id` ={$property_id} 
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

// COT FR and LR price must be 0
function bkd_CotLrFrPriceMustBeZero($job_id){
	$sql = mysql_query("
		SELECT * 
		FROM  `jobs` 
		WHERE (
			`job_type` = 'Change of Tenancy' OR
			`job_type` = 'Lease Renewal' OR
			`job_type` = 'Fix or Replace'
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
function bkd_is240vPriceZero($job_id){
	$sql = mysql_query("
		SELECT * 
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
function bkd_ifDHAAgencies($job_id){
	$sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`id` ={$job_id}
		AND a.`agency_id` IN(3043,3036,3046,1902,3044,1906,1927,3045)
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

function bkd_getPriceTotal($date){
	$sql = mysql_query("
		SELECT SUM( j.`job_price` ) AS PriceTotal
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE a.`country_id` = {$_SESSION['country_default']}
		AND j.`date` = '{$date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
	");
	$row = mysql_fetch_array($sql);
	return $row['PriceTotal'];
}

function bkd_alarmPriceTotal($date){
	$sql = mysql_query("
		SELECT SUM( al.`alarm_price` ) AS PriceTotal
		FROM  `alarm` AS al
		LEFT JOIN  `jobs` AS j ON al.`job_id` = j.`id` 
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` ={$_SESSION['country_default']}
		AND j.`date` =  '{$date}'
		AND p.`deleted` =0
		AND a.`status` =  'active'
		AND j.`del_job` = 0
		AND al.`new` = 1
		AND al.`ts_discarded` = 0
	");
	$row = mysql_fetch_array($sql);
	return $row['PriceTotal'];
}

?>