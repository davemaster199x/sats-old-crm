<?php

class Last_Contact_Class{

	function getJobs($start,$limit,$sort='',$order_by='',$state=''){
		
		$filter_str = '';
		$order_str = '';
		$limit_str = '';
		
		if( $state!='' ){
			$filter_str = " AND p.`state` = '{$state}' ";
		}
		
		if( $sort!="" && $order_by!="" ){
			$order_str = " ORDER BY {$sort} {$order_by} ";
		}

		if(is_numeric($start) && is_numeric($limit)){
			$limit_str = " LIMIT {$start}, {$limit} ";
		}
		
		$sql = "
			SELECT
				MAX( jl.`eventdate` ) AS last_contact, 
				
				j.`id` AS jid,
				j.`created` AS jcreated, 
				j.`date` AS jdate, 
				j.`job_type`, 
				j.`service` AS jservice, 
				j.`job_price`,
				j.`comments`,

				p.`property_id`,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				
				a.`agency_name`
			FROM  `job_log` AS jl
			LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`status` =  'To Be Booked'
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}	
			AND j.`status` != 'On Hold'
			{$filter_str}
			GROUP BY jl.`job_id` 
			HAVING last_contact <=  '".date('Y-m-d',strtotime('-14 days'))."'
			{$order_str}
			{$limit_str}
		";
	
			
		
		return mysql_query($sql);
	
	}

}

?>