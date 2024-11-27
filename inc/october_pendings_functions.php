<?php

// get unserviced
function getOctPendings($start,$limit){

	if(is_numeric($start) && is_numeric($limit)){
		$str = "LIMIT {$start}, {$limit}";
	}
	
	// dates
	$this_month = date("m");
	$last_year = date("Y",strtotime("-1 year"));
	$last_day_of_month = date("t");	
					
	$j_str = "
		SELECT 
			*,
			j.`property_id`, 
			a.`agency_id`,
			a.`agency_name`,
			p.`address_1` AS p_address1, 
			p.`address_2` AS p_address1, 
			p.`address_3` AS p_address3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode
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
		AND j.`date`
		BETWEEN '{$last_year}-{$this_month}-01'
		AND '{$last_year}-{$this_month}-{$last_day_of_month}'
		{$str}
	";
	return mysql_query($j_str);	
	
}

?>