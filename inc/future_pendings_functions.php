<?php



// get unserviced
function getFuturePendings_v2($params){

	$str = "";
	$sel_str = "";
	
	if( $params['getCount']==1 ){
		$sel_str = " COUNT(*) AS jcount ";
	}else{
		
		
		if($params['distinct']!=""){
		
			if($params['distinct']=='agency'){
				$sel_str = " DISTINCT(p.`agency_id`), a.`agency_name` ";
			}else if($params['distinct']=='state'){
				$sel_str = " DISTINCT(p.`state`) ";
			}
			
		}else{
			$sel_str = "
				CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
				j.`property_id`,
				j.`date` AS jdate,
				
				a.`agency_id`,
				a.`agency_name`,
				
				p.`address_1` AS p_address1, 
				p.`address_2` AS p_address2, 
				p.`address_3` AS p_address3, 
				p.`state` AS p_state, 
				p.`postcode` AS p_postcode
			";
		}
		
	}
	
	
	
	//echo $sel_str;
	
	if($params['phrase']!=""){
		$str .= " AND CONCAT_WS(' ', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ) LIKE '%".strtolower(trim($params['phrase']))."%' ";
	}
		
	if($params['state']!=""){
		$str .= " AND p.`state` = '{$params['state']}' ";
	}

	if( $params['agency']!="" && $params['agency']!="Any" ){
		$str .= " AND a.`agency_id` = {$params['agency']} ";
	}
	
	if($params['region_postcodes']!=""){
		$str .= " AND p.`postcode` IN ( {$params['region_postcodes']} ) ";
	}

	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	
	
	
	if( $params['from']!="" && $params['to']!="" ){

		$next_month = date("m",strtotime("{$params['from']} +1 month"));
		$last_year = date("Y",strtotime("{$params['from']} -1 year"));
		$last_day_of_month = date("t",strtotime("{$params['from']} -1 year"));	
		
	}else{
	
		// default
		$next_month = date("m",strtotime("+1 month"));
		$last_year = date("Y",strtotime("-1 year"));
		$last_day_of_month = date("t",strtotime("-1 year"));	
		
	}
	
	
					
	$j_str = "
		SELECT 
			{$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
		AND j.`service` = ps.`alarm_job_type_id` )
		WHERE j.`status` = 'Completed'
		AND j.`job_type` = 'Yearly Maintenance'
		AND ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
		AND j.`date`
		BETWEEN '{$last_year}-{$next_month}-01'
		AND '{$last_year}-{$next_month}-{$last_day_of_month}'
		{$str}
	";
	return mysql_query($j_str);	
	
}



// get unserviced
function getFuturePendings($start,$limit,$region,$agency,$phrase,$state,$is_distinct,$from="",$to=""){

	$str = "";
	$sel_str = "";
	
	//echo $is_distinct;
	
	if($is_distinct!=""){
		
		if($is_distinct=='agency'){
			$sel_str .= " DISTINCT(p.`agency_id`), a.`agency_name` ";
		}else if($is_distinct=='state'){
			$sel_str .= " DISTINCT(p.`state`) ";
		}
		
	}else{
		$sel_str .= "
			CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
			j.`property_id`, 
			a.`agency_id`,
			a.`agency_name`,
			p.`address_1` AS p_address1, 
			p.`address_2` AS p_address2, 
			p.`address_3` AS p_address3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode
		";
	}
	
	//echo $sel_str;
	
	if($phrase!=""){
		$str .= " AND CONCAT_WS(' ', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ) LIKE '%".strtolower(trim($phrase))."%' ";
	}
		
	if($state!=""){
		$str .= " AND p.`state` = '{$state}' ";
	}

	if($agency!=""&&$agency!="Any"){
		$str .= " AND a.`agency_id` = {$agency} ";
	}
	
	if($region!=""){
		$str .= " AND p.`postcode` IN ( {$region} ) ";
	}

	if(is_numeric($start) && is_numeric($limit)){
		$str .= " LIMIT {$start}, {$limit}";
	}
	
	if( $from!="" && $to!="" ){

		$next_month = date("m",strtotime("{$from} +1 month"));
		$last_year = date("Y",strtotime("{$from} -1 year"));
		$last_day_of_month = date("t",strtotime("{$from} -1 year"));	
		
	}else{
	
		// default
		$next_month = date("m",strtotime("+1 month"));
		$last_year = date("Y",strtotime("-1 year"));
		$last_day_of_month = date("t",strtotime("-1 year"));	
		
	}
	
	
					
	$j_str = "
		SELECT 
			{$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
		AND j.`service` = ps.`alarm_job_type_id` )
		WHERE j.`status` = 'Completed'
		AND j.`job_type` = 'Yearly Maintenance'
		AND ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
		AND j.`date`
		BETWEEN '{$last_year}-{$next_month}-01'
		AND '{$last_year}-{$next_month}-{$last_day_of_month}'
		{$str}
	";
	return mysql_query($j_str);	
	
}

function getFuturePendingsAgency(){

	// dates
	$next_month = date("m",strtotime("+1 month"));
	$last_year = date("Y",strtotime("-1 year"));
	$last_day_of_month = date("t");	
					
	$j_str = "
		SELECT 
			DISTINCT(a.`agency_id`),
			a.`agency_id`,
			a.`agency_name`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
		AND j.`service` = ps.`alarm_job_type_id` )
		WHERE j.`status` = 'Completed'
		AND j.`job_type` = 'Yearly Maintenance'
		AND ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND a.`country_id` = {$_SESSION['country_default']}
		AND j.`date`
		BETWEEN '{$last_year}-{$next_month}-01'
		AND '{$last_year}-{$next_month}-{$last_day_of_month}'
		ORDER BY a.`agency_name`
	";
	return mysql_query($j_str);	
	
}

function futurePendingsList($region='',$agency='',$phrase='',$state='',$getCount='',$distinct='',$from="",$to=""){
	
	// LIMIT
	if( $getCount==1 ){
		$sel_str = " SELECT COUNT(*) AS jcount ";
	}
	
	if($is_distinct!=""){
		
		if($is_distinct=='state'){
			$sel_str = " SELECT DISTINCT(p.`state`) ";
		}
		
	}

	
	// FILTER
	if($phrase!=""){
		$str .= " AND CONCAT_WS(' ', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ) LIKE '%".strtolower(trim($phrase))."%' ";
	}
		
	if($state!=""){
		$str .= " AND p.`state` = '{$state}' ";
	}

	if($agency!=""&&$agency!="Any"){
		$str .= " AND a.`agency_id` = {$agency} ";
	}
	
	if($region!=""){
		$str .= " AND p.`postcode` IN ( {$region} ) ";
	}
	
	// dates	
	if( $from!="" && $to!="" ){

		$next_month = date("m",strtotime("{$from} +1 month"));
		$last_year = date("Y",strtotime("{$from} -1 year"));
		$last_day_of_month = date("t",strtotime("{$from} -1 year"));	
		
	}else{
	
		// default
		$next_month = date("m",strtotime("+1 month"));
		$last_year = date("Y",strtotime("-1 year"));
		$last_day_of_month = date("t",strtotime("-1 year"));	
		
	}
	
	$sql = mysql_query("
		{$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
		AND j.`service` = ps.`alarm_job_type_id` )
		WHERE j.`status` = 'Completed'
		AND j.`job_type` = 'Yearly Maintenance'
		AND ps.`service` =1
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
		AND j.`date` BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$last_day_of_month}'
		{$str}	
	");
		
		
	return $sql;
		
}

?>