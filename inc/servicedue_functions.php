<?php

function getAgencies($start,$limit,$state,$salesrep,$region,$phrase){
	
	
	// state
	if($state!=""){
		$str .= " AND LOWER(a.state) LIKE '%{$state}%' ";
	}
	
	// sales rep
	if($salesrep!=""){
		$str .= " AND a.`salesrep` = {$salesrep}";
	}
	
	// region
	if($region!=""){
		$str .= "AND (LOWER(ar.agency_region_name) LIKE '%{$region}%') ";
	}
	
	// phrase
	if($phrase!=""){
		$str .= " AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(sa.FirstName), LOWER(sa.LastName), LOWER(a.state), LOWER(ar.agency_region_name), LOWER(a.`account_emails`), LOWER(a.`agency_emails`), LOWER(a.`contact_email`) ) LIKE '%{$phrase}%') ";
	}
	
	$str .= " 
		GROUP BY a.`agency_id`
		ORDER BY jcount DESC
	";
	
	// paginate
	if(is_numeric($start) && is_numeric($limit)){
		$str .= " LIMIT {$start}, {$limit}";
	}
	
	// get agency
	$sql = "
		SELECT 
			count(j.`id`) AS jcount, 
			a.`agency_id`,
			a.`agency_name`,
			sa.`FirstName`,
			sa.`LastName`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
		LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
		LEFT JOIN `agency_regions` AS ar ON ar.`agency_region_id` = a.`agency_region_id`
		WHERE j.`status` = 'Pending'
		AND a.`status` = 'active'
		AND p.`deleted` = 0
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}		
		{$str}
	";

	return mysql_query($sql);
}

function getServiceDue($agency_id){

	// get total
	$sql = mysql_query("
		SELECT COUNT(j.`id`) as jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON p.`property_id` = j.`property_id`
		LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
		WHERE p.`agency_id` = {$agency_id}
		AND j.`status` = 'Pending'
		AND a.`status` = 'active'
		AND p.`deleted` = 0
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}	
	");	
	$row = mysql_fetch_array($sql);
	return $row['jcount'];
	
}


?>