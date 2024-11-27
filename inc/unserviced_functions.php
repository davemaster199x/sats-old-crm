<?php

// get properties
function getUnservicedProperties($prop,$start,$limit){

	// paginate
	if(is_numeric($start) && is_numeric($limit)){
		$str = "LIMIT {$start}, {$limit}";
	}
	
	// format array to comma separated
	$p_arr = array();
	while($p = mysql_fetch_array($prop)){
		$p_arr[] = $p['property_id'];
	}
	$ex_prop = implode(",",$p_arr);

	return mysql_query("
		SELECT 
			DISTINCT j.`property_id`,			
			p.`address_1` AS p_address1, 
			p.`address_2` AS p_address2, 
			p.`address_3` AS p_address3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode,
			a.`agency_id`,
			a.`agency_name`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( 
			j.`property_id` = ps.`property_id` 
			AND j.`service` = ps.`alarm_job_type_id` 
		)
		WHERE p.`property_id` NOT IN({$ex_prop})
		AND ps.`service` =1
		AND p.`deleted` = 0
		AND p.`agency_deleted` = 0
		AND a.`status` = 'active'
		AND a.`country_id` = {$_SESSION['country_default']}
		ORDER BY j.`property_id` DESC
		{$str}
	");
	
}

// get excluded properties
function getExcludedProperties(){

	return mysql_query("
		SELECT DISTINCT j.`property_id`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
		AND j.`service` = ps.`alarm_job_type_id` )
		WHERE ps.`service` = 1
		AND a.`country_id` = {$_SESSION['country_default']}
		AND ( 
			j.`status` = 'Pending'
			OR j.`date` IS NULL
			OR j.`date` = '0000-00-00'
			OR j.`job_type` = 'Once-off'
			OR j.`job_type` = '240v Rebook'
			OR j.`is_eo` = 1
			OR ( j.`date` >= '". date("Y-m-d",strtotime("-1 year"))."' AND j.`job_type` = 'Yearly Maintenance' ) 			
		)	
	");

}

// get last job
function getGetLastJob($prop_id){
	$sql = mysql_query("
		SELECT MAX(j.`date`) AS jdate
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`property_id` = {$prop_id}		
		AND j.`job_type` = 'Yearly Maintenance'
		AND j.`status` = 'Completed'
		AND a.`country_id` = {$_SESSION['country_default']}
		GROUP BY j.`property_id`
	");
	$row = mysql_fetch_array($sql);
	return $row['jdate'];
}


?>