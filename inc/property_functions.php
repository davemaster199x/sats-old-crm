<?php

function getPropertyList($agency_id, $search, $limit = PER_PAGE, $start = 0, $deleted = 0)
{
	global $user;
	
	$selectQuery = "SELECT SQL_CALC_FOUND_ROWS p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, 
	p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2, p.service, DATE_FORMAT(MAX(j.date), '%d/%m/%Y') AS last_visit,
	a.agency_id, a.agency_name, a.address_3 AS agency_address FROM (property p, agency a) LEFT JOIN jobs j ON j.property_id = p.property_id
	WHERE (p.agency_id = a.agency_id AND p.deleted= {$deleted} " . $user -> prepareStateString('AND', 'p.') . ") ";
	
	# Add AgencyID
	if (!in_array($agency_id, array("", "Any"))) {
		$selectQuery .= " AND p.agency_id = ".$agency_id;
	}

	# Add Search suburb
	if ($search != "") {
		
		# Convert to lowercase
		$search = strtolower($search);
		
		# Concat fields to search
		# address CONCAT_WS(' ', p.address_1, p.address_2, p.address_3, p.state, p.postcode)
		# tenant name CONCAT_WS(' ', p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2)
		# landlord CONCAT_WS(' ', p.landlord_ph, p.landlord_firstname, p.landlord_lastname)
		
		$other_fields_to_search = array("booking_comments", "property_id");
		
		$keywords = explode(" ", trim($search));
		
		$search = str_replace(" ", "%", $search);
		$selectQuery .= " AND ( ";
		
		# Address search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search}%') OR ";
		
		# Tenant search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.tenant_firstname1), LOWER(p.tenant_lastname1), LOWER(p.tenant_firstname2), LOWER(p.tenant_lastname2), LOWER(p.tenant_ph1), LOWER(p.tenant_ph2), LOWER(p.tenant_mob1), LOWER(p.tenant_mob2), LOWER(p.tenant_email1), LOWER(p.tenant_email2) ) LIKE '%{$search}%') OR ";
		
		# Landlord search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.landlord_email), LOWER(p.landlord_firstname), LOWER(p.landlord_lastname) ) LIKE '%{$search}%') OR ";
		

		foreach($other_fields_to_search as $field)
		{
			$selectQuery .= " LOWER(p.{$field}) LIKE '%{$search}%' OR ";
		}

		
		
		$selectQuery .= ") ";
		# fix up query
		$selectQuery = str_replace("OR )", " )", $selectQuery);
	
	}	
	
	$selectQuery .= " GROUP BY p.property_id  ORDER BY a.agency_name ASC";
	
	# Add Limit if Necessary
	if(is_numeric($start) && is_numeric($limit))
	{
		$selectQuery .= " LIMIT {$start}, {$limit}";
	}
	
	$property_list = mysqlMultiRows($selectQuery);
	
	return $property_list;
}



// get property list
function getPropertyList2($agency_id, $search,$start = 0, $limit = PER_PAGE, $deleted = 0,$sort='a.agency_name',$order_by='ASC',$filterregion="",$from="",$to="")
{
	global $user;
	
	$selectQuery = "SELECT SQL_CALC_FOUND_ROWS p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, 
	p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2, p.service, DATE_FORMAT(MAX(j.date), '%d/%m/%Y') AS last_visit,
	a.agency_id, a.agency_name, a.address_3 AS agency_address, p.`agency_deleted`, p.`deleted_date` FROM (property p, agency a) LEFT JOIN jobs j ON j.property_id = p.property_id
	WHERE (p.agency_id = a.agency_id AND p.deleted= {$deleted} ) ";
	
	// country
	$selectQuery .= " AND a.`country_id` = {$_SESSION['country_default']} ";
	
	# Add AgencyID
	if (!in_array($agency_id, array("", "Any"))) {
		$selectQuery .= " AND p.agency_id = ".$agency_id;
	}

	# Add Search suburb
	if ($search != "") {
		
		# Convert to lowercase
		$search = mysql_real_escape_string(strtolower($search));
		
		# Concat fields to search
		# address CONCAT_WS(' ', p.address_1, p.address_2, p.address_3, p.state, p.postcode)
		# tenant name CONCAT_WS(' ', p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2)
		# landlord CONCAT_WS(' ', p.landlord_ph, p.landlord_firstname, p.landlord_lastname)
		
		$other_fields_to_search = array("booking_comments", "property_id");
		
		$keywords = explode(" ", trim($search));
		
		$search = str_replace(" ", "%", $search);
		$selectQuery .= " AND ( ";
		
		# Address search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search}%') OR ";
		
		# Tenant search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.tenant_firstname1), LOWER(p.tenant_lastname1), LOWER(p.tenant_firstname2), LOWER(p.tenant_lastname2), LOWER(p.tenant_ph1), LOWER(p.tenant_ph2), LOWER(p.tenant_mob1), LOWER(p.tenant_mob2), LOWER(p.tenant_email1), LOWER(p.tenant_email2), LOWER(p.landlord_firstname), LOWER(p.landlord_lastname) ) LIKE '%{$search}%') OR ";
		
		# Landlord search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.landlord_email), LOWER(p.landlord_firstname), LOWER(p.landlord_lastname) ) LIKE '%{$search}%') OR ";
		

		foreach($other_fields_to_search as $field)
		{
			$selectQuery .= " LOWER(p.{$field}) LIKE '%{$search}%' OR ";
		}

		
		
		$selectQuery .= ") ";
		# fix up query
		$selectQuery = str_replace("OR )", " )", $selectQuery);
	
	}
	
	
	// date from - to
	if( $from!='' && $to!='' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$selectQuery .= " AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}' ";
	}
	
	if($filterregion!=""){
		$selectQuery .= " AND p.`postcode` IN ( {$filterregion} ) ";
	}
	
	//echo $sort." - ".$order_by;
	$selectQuery .= " GROUP BY p.property_id  ORDER BY {$sort} {$order_by}";
	
	# Add Limit if Necessary
	if(is_numeric($start) && is_numeric($limit))
	{
		$selectQuery .= " LIMIT {$start}, {$limit}";
	}
	
	//echo $selectQuery;
	
	$property_list = mysql_query($selectQuery);
	
	return $property_list;
}


// get property list
function getPropertyListWithActiveService($agency_id, $search,$start = 0, $limit = PER_PAGE, $deleted = 0,$sort='a.agency_name',$order_by='ASC',$filterregion="",$from="",$to="")
{
	global $user;
	
	$selectQuery = "SELECT DISTINCT(ps.`property_id`), p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, 
	p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2, p.service, 
	a.agency_id, a.agency_name, a.address_3 AS agency_address, p.`agency_deleted`, p.`deleted_date` 

	FROM `property_services` AS ps
	LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE p.deleted= {$deleted}
	AND ps.`service` = 1
	
	";
	
	// country
	$selectQuery .= " AND a.`country_id` = {$_SESSION['country_default']} ";
	
	# Add AgencyID
	if (!in_array($agency_id, array("", "Any"))) {
		$selectQuery .= " AND p.agency_id = ".$agency_id;
	}

	# Add Search suburb
	if ($search != "") {
		
		# Convert to lowercase
		$search = mysql_real_escape_string(strtolower($search));
		
		# Concat fields to search
		# address CONCAT_WS(' ', p.address_1, p.address_2, p.address_3, p.state, p.postcode)
		# tenant name CONCAT_WS(' ', p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph1, p.tenant_ph2)
		# landlord CONCAT_WS(' ', p.landlord_ph, p.landlord_firstname, p.landlord_lastname)
		
		$other_fields_to_search = array("booking_comments", "property_id");
		
		$keywords = explode(" ", trim($search));
		
		$search = str_replace(" ", "%", $search);
		$selectQuery .= " AND ( ";
		
		# Address search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$search}%') OR ";
		
		# Tenant search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.tenant_firstname1), LOWER(p.tenant_lastname1), LOWER(p.tenant_firstname2), LOWER(p.tenant_lastname2), LOWER(p.tenant_ph1), LOWER(p.tenant_ph2), LOWER(p.tenant_mob1), LOWER(p.tenant_mob2), LOWER(p.tenant_email1), LOWER(p.tenant_email2), LOWER(p.landlord_firstname), LOWER(p.landlord_lastname) ) LIKE '%{$search}%') OR ";
		
		# Landlord search
		$selectQuery .= " ( CONCAT_WS(' ', LOWER(p.landlord_email), LOWER(p.landlord_firstname), LOWER(p.landlord_lastname) ) LIKE '%{$search}%') OR ";
		

		foreach($other_fields_to_search as $field)
		{
			$selectQuery .= " LOWER(p.{$field}) LIKE '%{$search}%' OR ";
		}

		
		
		$selectQuery .= ") ";
		# fix up query
		$selectQuery = str_replace("OR )", " )", $selectQuery);
	
	}
	
	
	// date from - to
	if( $from!='' && $to!='' ){
		$from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
		$to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
		$selectQuery .= " AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}' ";
	}
	
	if($filterregion!=""){
		$selectQuery .= " AND p.`postcode` IN ( {$filterregion} ) ";
	}
	
	//echo $sort." - ".$order_by;
	$selectQuery .= " GROUP BY p.property_id  ORDER BY {$sort} {$order_by}";
	
	# Add Limit if Necessary
	if(is_numeric($start) && is_numeric($limit))
	{
		$selectQuery .= " LIMIT {$start}, {$limit}";
	}
	
	$selectQuery;
	
	$property_list = mysql_query($selectQuery);
	
	return $property_list;
}

function getPropertyServiceStatus($property_id,$ajt){
	$ps_sql = mysql_query("
		SELECT *
		FROM `property_services` 
		WHERE `property_id` = {$property_id}
		AND `alarm_job_type_id` = {$ajt}
	");
	if(mysql_num_rows($ps_sql)>0){
		$s = mysql_fetch_array($ps_sql);
		$service = $s['service'];
		switch($service) {
			case 0:
				$service = 'DIY';
				break;
			case 1:
				$service = 'SATS';
				break;
			case 2:
				$service = 'No Response';
				break;
			case 3:
				$service = 'Other Provider';
				break;
		}		
	}else{
		$service = "N/A";
	}
	return $service;
}

function noActiveJobPropperties($offset,$limit){
	# Add Limit if Necessary
	if(is_numeric($offset) && is_numeric($limit))
	{
		$str .= " LIMIT {$offset}, {$limit}";
	}
	
	$str = "
		SELECT p.`property_id`, p.`address_1`, p.`address_2`, p.`address_3`, a.`agency_id`, a.`agency_name`, p.`created`
		FROM `jobs` AS j1
		INNER JOIN (
			SELECT MAX(  j.`date` ) as latestJob, j.`property_id` 
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN  `property_services` AS ps ON ( j.`service` = ps.`alarm_job_type_id` AND j.`property_id` = ps.`property_id` ) 
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
			AND j.`job_type` =  'Yearly Maintenance'
			AND j.`status` =  'Completed'
			AND ps.`service` =1
			GROUP BY j.`property_id`
		) AS j2 ON ( j1.`property_id` = j2.`property_id` AND j1.`date` = j2.latestJob )
		LEFT JOIN `property` AS p ON j1.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j1.`del_job` = 0
		AND j1.`job_type` =  'Yearly Maintenance'
		AND j1.`status` =  'Completed'
		AND a.`country_id` = {$_SESSION['country_default']}
		AND NOT YEAR( j2.`latestJob` ) BETWEEN  '".date('Y',strtotime('-1 year'))."' AND '".date('Y')."'
		AND p.`property_id` NOT IN (
			SELECT DISTINCT(p2.`property_id`)
			FROM  `jobs` AS j2
			LEFT JOIN  `property` AS p2 ON j2.`property_id` = p2.`property_id` 
			LEFT JOIN  `agency` AS a2 ON p2.`agency_id` = a2.`agency_id` 
			WHERE p2.`deleted` =0
			AND a2.`status` =  'active'
			AND j2.`del_job` =0
			AND j2.`status` !=  'Cancelled'
			AND j2.`status` !=  'Completed'
			AND a2.`country_id` ={$_SESSION['country_default']}
		)
		{$str}
	";
	return mysql_query($str);
}

?>
