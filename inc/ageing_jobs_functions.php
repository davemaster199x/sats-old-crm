<?php

function getAgeingJobs($start,$limit,$region,$job_type,$state,$agency,$distinct,$order_by,$sort,$custom_filter){
	
	// distinct
	if( $distinct!="" ){
		
		switch($distinct){
			case 'a.`agency_id`':
				$sel_str = "SELECT DISTINCT a.`agency_id`, a.`agency_name`";
			break;
			default:
			$sel_str = "SELECT DISTINCT {$distinct}";		
		}
		
	}else{
		$sel_str = "SELECT *, j.`id` AS jid, j.`created` AS jcreated, j.`date` AS jdate, j.`service` AS jservice, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`postcode` AS p_postcode";
	}
	

	$str = "";
	
	if($job_type!=""){
		$str .= " AND j.job_type = '{$job_type}'";  
	}
	
	if($state!=""){
		$str .= " AND p.state = '{$state}'";  
	}
	
	if($agency!=""){
		$str .= " AND a.`agency_id` = '{$agency}'";  
	}
	
	if($region!=""){
		$str .= " AND p.`postcode` IN ( {$region} ) ";
	}
	
	if( $order_by!='' &&  $sort!='' ){
		$str .= " ORDER BY {$order_by} {$sort} ";
	}

	if(is_numeric($start) && is_numeric($limit)){
		$str .= " LIMIT {$start}, {$limit}";
	}
	
	$sql = "
		{$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE (
			j.`status` = 'To Be Booked'
			OR j.`status` = 'Pre Completion'
			OR j.`status` = 'Booked'
			OR j.`status` = 'Escalate'
		)
		{$custom_filter}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}		
		{$str}
	";
	return mysql_query($sql);

}

?>