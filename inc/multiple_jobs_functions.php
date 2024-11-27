<?php

function getMultipleJobs($start,$limit){

	// paginate
	if(is_numeric($start) && is_numeric($limit)){
		$str = " LIMIT {$start}, {$limit} ";
	}
	
	return mysql_query("
		SELECT COUNT( j.`id` ) AS jcount , j.`id`, j.`job_type`, j.`status` , j.`property_id` , p.`address_1` , p.`address_2` , p.`address_3`, p.`state`, a.`agency_name`, p.`deleted`, a.`agency_id`, j.`service`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` != 'Completed'
		AND j.`status` != 'Cancelled'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` ={$_SESSION['country_default']}
		GROUP BY j.`property_id`
		HAVING jcount >=2
		{$str}
	");
}

function getOtherMultipleJobs($property_id,$jid){
	$sql = "
		SELECT j.`id`, j.`job_type`, j.`status`, j.`property_id` , p.`address_1` , p.`address_2` , p.`address_3`, p.`state`, a.`agency_name`, p.`deleted`, a.`agency_id`, j.`service`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` != 'Completed'
		AND j.`status` != 'Cancelled'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND p.`property_id` = {$property_id}
		AND j.`id` != {$jid}
		AND a.`country_id` ={$_SESSION['country_default']}
	";
	return mysql_query($sql);
}

?>