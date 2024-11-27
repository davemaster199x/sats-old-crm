<?php

# get job list
function getJobList2($params,$sort,$order_by)
{
	global $user;
	
	$query  = "SELECT SQL_CALC_FOUND_ROWS j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y') AS date, j.status, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.id, 
	p.property_id, a.send_emails, a.account_emails, j.client_emailed, j.service, j.`job_price`, j.`job_reason_id`, j.`urgent_job`, j.`comments`, j.`due_date`, j.`date` AS jdate,
	p.`tenant_firstname1`, p.`tenant_lastname1`, p.`tenant_firstname2`, p.`tenant_lastname2`, j.`created`, j.`start_date`, j.`key_access_required`
	
	FROM (jobs j, property p, agency a "; 
	
	#Join in Postcode Region table if required
	if(intval($params['postcode_region_id']) > 0)
	{
		$query .= ", postcode_regions r";
	}
	
	$query .= " ) LEFT JOIN job_log l ON l.job_id = j.id   
	LEFT JOIN `agency_regions` AS ar ON a.`agency_region_id` = ar.`agency_region_id`
	WHERE a.agency_id = p.agency_id 
	AND j.property_id = p.property_id "; 
	
	
	// country
	$query .= " AND a.`country_id` = {$_SESSION['country_default']} ";
	
	
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
	//$query .= $user->prepareStateString('AND', 'p.');
	
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
	
		// lease renewal
		/*
		$param_jt = ($param_jt!=="")?" OR j.job_type = 'Lease Renewal'":"";
	
		$query .= " AND (";
		
		$query .= " j.job_type = '".$params['job_type']."' {$param_jt} )";
		*/
		$query .= " AND j.job_type = '".$params['job_type']."'";  
	}
	
	
	
	if($params['param_serv'] != ""){	
		$query .= " AND j.service = ".$params['param_serv'];
	}
	
	/*
	# Search against postcode string
	if(intval($params['postcode_region_id']) > 0)
	{
		$query .= " AND (length(p.postcode) = 4 OR length(p.postcode) = 5) AND r.postcode_region_id = " . $params['postcode_region_id'] . " AND r.postcode_region_postcodes LIKE CONCAT('%', p.postcode, '%') ";
	}
	*/
	
	if($params['postcode_region_id']!=""){
		$query .= " AND p.`postcode` IN ( {$params['postcode_region_id']} ) ";
	}
	
	
	// get urgent jobs
	if($params['urgent']==1){
		$query .= " AND j.`urgent_job` = 1";
	}
	
	
	
	
	if($params['state_srch'] != ""){	
		$query .= " AND p.`state` = '{$params['state_srch']}'";
	}
	
	$query .= " GROUP BY j.id ";
	
	if($sort!=""){
		
		if($sort!='p.address_2'&&$sort!='j.job_price'&&$sort!='j.service'){
			$third_sort = ", p.`address_3` {$order_by}";
		}
		
		if($params['status']=='DHA'){
			$sort = ' ar.`agency_region_name`';
			$order_by = ' ASC';
		}
		
		if($params['status']!='On Hold'){
			$urgent_sort = 'j.`urgent_job` DESC,';
		}else{
			$urgent_sort = '';
		}
		
		$query .= "ORDER BY {$urgent_sort} {$sort} {$order_by} {$third_sort} ";
	}
	
	if(is_numeric($params['start']) && is_numeric($params['limit']))
	{
	$query .= " LIMIT {$params['start']},{$params['limit']}";
	}
	
	//echo "<div style='display:none;'>{$query}</div>";
	
	$query;

	$job_list = mysql_query($query);
	
	return $job_list;

}

?>