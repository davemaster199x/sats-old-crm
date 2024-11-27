<?php

// note:
// DHA agencies filter: AND a.`agency_id` NOT IN(3043,3036,3046,1902,3044,1906,1927,3045)
function getCompletedCount($from,$to,$serv_type,$job_type,$country_id,$return_data=0){
	
	if($return_data==1){
		$sel_str = "CAST( j.`created` AS DATE ) AS jcreated, j.`date` ";
	}else{
		$sel_str = "count( j.`id` ) AS jtot ";
	}
	
	$jt_str = ($job_type!="")?" AND j.`job_type` = '{$job_type}' ":'';
	$serv_type_str = ($serv_type!="")?"AND j.`service` = {$serv_type}":"";

	$sql = "
		SELECT {$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Completed'
		AND p.`deleted` = 0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		{$serv_type_str}
		{$jt_str}		
		AND (j.`date`
		BETWEEN '{$from}'
		AND '{$to}')
	 ";
	 
	$ci_sql = mysql_query($sql);
	
	
	if($return_data==1){
		return $ci_sql;
	}else{
		$ci = mysql_fetch_array($ci_sql);
		return $ci['jtot'];
	}
	
}

function monthsToComplete($from,$to,$serv_type,$job_type,$month,$country_id){
	
	$jt_str = ($job_type!="")?" AND j.`job_type` = '{$job_type}' ":'';
	
	$cr_from = date("Y-m-1", strtotime("{$from} -{$month} month"));
	$cr_to = date("Y-m-t", strtotime("{$to} -{$month} month"));
	//$cr_to = date("Y-m-t",strtotime($cr_to_temp));
	
	$serv_type_str = ($serv_type!="")?"AND j.`service` = {$serv_type}":"";
	
	if($month==0){
		$cr_from = date("Y-m-1", strtotime("{$from} -{$month} month"));
		$cr_str = "
			AND CAST(j.`created` AS DATE) >= '{$cr_from}'				
		";
	}else if($month==4){
		$cr_to = date("Y-m-t", strtotime("{$from} -{$month} month"));
		$cr_str = "
			AND CAST(j.`created` AS DATE) <= '{$cr_to}'				
		";
	}else{
		$cr_to = date("Y-m-t", strtotime("{$from} -{$month} month"));
		$cr_str = "
			AND (
				CAST(j.`created` AS DATE)
				BETWEEN '{$cr_from}'
				AND '{$cr_to}'
			)
		";
	}
	
	$sql = "
		SELECT count( j.`id` ) AS jtot
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Completed'
		AND a.`country_id` = {$country_id}
		AND p.`deleted` = 0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		{$serv_type_str}
		{$jt_str}
		AND (
			j.`date` 
			BETWEEN '{$from}'
			AND '{$to}'
		)
		{$cr_str}
	 ";
	$ci_sql = mysql_query($sql);
	 $ci = mysql_fetch_array($ci_sql);
	 return $ci['jtot'];
	 
}

function daysToComplete($from,$to,$serv_type='',$job_type='',$min='',$max='',$country_id){
	
	$jt_str = ($job_type!="")?" AND j.`job_type` = '{$job_type}' ":'';
	
	$cr_from = date("Y-m-1", strtotime("{$from} -{$days} month"));
	$cr_to = date("Y-m-t", strtotime("{$to} -{$days} month"));
	//$cr_to = date("Y-m-t",strtotime($cr_to_temp));
	
	$serv_type_str = ($serv_type!="")?"AND j.`service` = {$serv_type}":"";
	
	if($min==$max){
		$cr_str = " AND CAST( j.`created` AS DATE ) <= DATE_SUB( j.`date` , INTERVAL {$min} DAY ) ";
	}else{
		$cr_str = " AND ( CAST( j.`created` AS DATE ) BETWEEN DATE_SUB( j.`date` , INTERVAL {$max} DAY ) AND DATE_SUB( j.`date` , INTERVAL {$min} DAY ) ) ";
	}
	
	// do not include DHA agencies
	$sql = "
		SELECT count( j.`id` ) AS jtot
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Completed'
		AND a.`country_id` = {$country_id}
		AND p.`deleted` = 0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		{$serv_type_str}
		{$jt_str}
		AND (
			j.`date` 
			BETWEEN '{$from}'
			AND '{$to}'
		)
		{$cr_str}
	 ";
	 
	$ci_sql = mysql_query($sql);
	$ci = mysql_fetch_array($ci_sql);
	return $ci['jtot'];
	 
	 
}

?>
