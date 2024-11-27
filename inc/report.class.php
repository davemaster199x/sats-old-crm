<?php

class Report {
	
	function getSalesReportData($params)
	{
		# Assume dates validated / fixed before hitting this function.
		# Fix dates so they can search the timestamp format
		if(isValidDate($params['from']))
		{
			$params['from'] .= " 00:00:00";
			$params['to'] .= " 23:59:59";
		} 

		$report_data = array();

		# First get total properties, split by deleted / non deleted
		$query = "SELECT COUNT(p.property_id) AS NumProperties, p.agency_deleted
					FROM property p, agency a
					WHERE p.agency_id > 0 AND p.agency_id = a.agency_id AND p.deleted = 0 ";

		if($params['from'] != '')
		{
			$query .= " AND p.created >= '" . $params['from'] . "' AND p.created <= '" . $params['to'] . "' "; 
		}

		$query .= " GROUP BY agency_deleted ORDER BY agency_deleted ASC";

		$result = mysqlMultiRows($query);
		$report_data['total'] = $result[0]['NumProperties'] + $result[1]['NumProperties'];
		$report_data['active'] = $result[0]['NumProperties'];
		$report_data['agency_deleted'] = $result[1]['NumProperties'];

		# Get properties yes / no to service
		$query = "SELECT COUNT(p.property_id) AS NumProperties, p.service
					FROM property p, agency a
					WHERE p.agency_id > 0 AND p.agency_id = a.agency_id AND p.deleted = 0 AND a.`status` = 'active' ";
		
		if($params['from'] != '')
		{
			$query .= " AND p.created >= '" . $params['from'] . "' AND p.created <= '" . $params['to'] . "' "; 
		}

		$query .= "GROUP BY service ORDER BY service ASC";

		$result = mysqlMultiRows($query);

		$report_data['yes_to_service'] = 0;
		$report_data['no_to_service'] = 0;

		foreach($result as $service_type)
		{
			if($service_type['service'] == 1) 
			{
				$report_data['yes_to_service'] = $service_type['NumProperties'];
			}
			else
			{
				$report_data['no_to_service'] = $service_type['NumProperties'];
			}
		}




		# Get Properties broken down by staff member - first service
		$query = "SELECT COUNT(p.property_id) AS NumProperties, p.service , CONCAT(s.FirstName, ' ' , s.LastName) AS SalesRep
					FROM property p, 
					agency a
					LEFT JOIN staff_accounts s ON s.StaffID = a.salesrep
					WHERE p.agency_id = a.agency_id AND p.deleted = 0 AND a.`status` = 'active'
					AND p.agency_id > 0 ";

		if($params['from'] != '')
		{
			$query .= " AND p.created >= '" . $params['from'] . "' AND p.created <= '" . $params['to'] . "' "; 
		}			

		$query .= " GROUP BY p.service, SalesRep";

		$result = mysqlMultiRows($query);
		
		foreach($result as $index=>$data)
		{
			$report_data['staff'][$data['SalesRep']]['service'][$data['service']] += $data['NumProperties'];
			$report_data['staff'][$data['SalesRep']]['total'] += $data['NumProperties'];
		}			

		# Now add deleted too
		$query = "SELECT COUNT(p.property_id) AS NumProperties, p.agency_deleted , CONCAT(s.FirstName, ' ' , s.LastName) AS SalesRep
					FROM property p, 
					agency a
					LEFT JOIN staff_accounts s ON s.StaffID = a.salesrep
					WHERE p.agency_id = a.agency_id AND p.deleted = 0 AND a.`status` = 'active'
					AND p.agency_id > 0 ";

		if($params['from'] != '')
		{
			$query .= " AND p.created >= '" . $params['from'] . "' AND p.created <= '" . $params['to'] . "' "; 
		}

		$query .= " GROUP BY p.agency_deleted, SalesRep";

		$result = mysqlMultiRows($query);

		foreach($result as $index=>$data)
		{
			$report_data['staff'][$data['SalesRep']]['agency_deleted'][$data['agency_deleted']] += $data['NumProperties'];
		}	

		return $report_data;
	}

	function getReportData($params)
	{
		$report_data = array();
		
		# First get total jobs
		$query = "
			SELECT COUNT(*) AS total 
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`date` >= '{$params['from']}' 
			AND j.`date` <= '{$params['to']}'
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
			";
		if(is_int($params['staff_id'])) $query .= " AND j.staff_id = {$params['staff_id']}";
		if(is_int($params['tech_id'])) $query .= " AND j.assigned_tech = {$params['tech_id']}";
		
		$result = mysqlSingleRow($query);
		$report_data['total'] = $result['total'];
		
		
		# Get Breakdown by job type
		$query = "
			SELECT j.`job_type`, COUNT(*) AS num_jobs 
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`date` >= '{$params['from']}' 
			AND j.`date` <= '{$params['to']}'
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`status` = 'Completed'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
		";
		if(is_int($params['staff_id'])) $query .= " AND j.staff_id = {$params['staff_id']}";
		if(is_int($params['tech_id'])) $query .= " AND j.assigned_tech = {$params['tech_id']}";
		$query .= " GROUP BY j.`job_type`";
		$result = mysqlMultiRows($query);
		$report_data['job_type'] = $result;
		
		# Get Breakdown by status
		$query = "
			SELECT j.`status`, COUNT(*) AS num_jobs 
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`date` >= '{$params['from']}' 
			AND j.`date` <= '{$params['to']}'
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$_SESSION['country_default']}
		";
		if(is_int($params['staff_id'])) $query .= " AND j.staff_id = {$params['staff_id']}";
		if(is_int($params['tech_id'])) $query .= " AND j.assigned_tech = {$params['tech_id']}";
		$query .= " GROUP BY j.`status`";
		
		$result = mysqlMultiRows($query);
		$report_data['status'] = $result;
		
		# Get Tech Completed breakdown
		$query = "SELECT CONCAT_WS(' ', sa.FirstName, sa.LastName) AS tech_name, COUNT(*) AS num_jobs, j.`assigned_tech` 
					FROM jobs AS j
					LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
					LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
					LEFT JOIN staff_accounts AS sa ON j.`assigned_tech` = sa.`StaffID` 
					WHERE j.date >= '{$params['from']}' AND j.date <= '{$params['to']}'
					AND j.status = 'Completed'
					AND p.`deleted` =0
					AND a.`status` = 'active'
					AND j.`del_job` = 0
					AND a.`country_id` = {$_SESSION['country_default']}
					";
		
		if(is_int($params['staff_id'])) $query .= " AND j.staff_id = {$params['staff_id']} ";	
		if(is_int($params['tech_id'])) $query .= " AND j.assigned_tech = {$params['tech_id']}";		
					
		$query .=   " GROUP BY tech_name
					ORDER BY num_jobs DESC ";
		
		$result = mysqlMultiRows($query);
		$report_data['techs'] = $result;
		
		
		# Get Staff Booked Breakdown
		$query = "SELECT DISTINCT(ca.`staff_accounts_id`), CONCAT_WS(' ', sa.FirstName, sa.LastName) AS staff_name 
				  FROM staff_accounts AS sa
				INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
				WHERE sa.deleted =0
				AND sa.active =1			
				AND ca.`country_id` ={$_SESSION['country_default']}
				AND (
					ClassID = 2 OR 
					ClassID = 7
				)
				ORDER BY sa.`FirstName`
					";
		
		
		
		
		$result = mysqlMultiRows($query);
		$report_data['staff'] = $result;
		
		return $report_data;
	}
	
	function generateLink($params, $staff_filter = array())
	{
		$link = "<a href='?";
		
		$link .= "from=" . $params['from'] . "&to=" . $params['to']."&get_sats=1";
		
		if(is_int($staff_filter['staff_id'])) $link .= "&sid=" . $staff_filter['staff_id'];
		if(is_int($staff_filter['tech_id'])) $link .= "&tid=" . $staff_filter['tech_id'];
				
		# Close off url
		$link .= "'";
		
		# Add style
		if($params['css']) $link .=" style='" . $params['css']  ."' ";
		$link .= " />" . $params['title'] . "</a>";
		
		return $link;
	}
	
	function getAllStatuses()
	{
		$query = "
			SELECT DISTINCT(j.`status`) 
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`status` != '' 
			AND a.`country_id` = {$_SESSION['country_default']}
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			GROUP BY j.`status`
		";
		$result = mysqlMultiRows($query);
		return $result;
	}
	
	function getAllJobTypes()
	{
		$query = "
			SELECT DISTINCT(j.`job_type`) 
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`job_type` NOT LIKE '%1%' 
			AND j.`job_type` != ''
			AND a.`country_id` = {$_SESSION['country_default']}	
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0			
			GROUP BY j.`job_type`
		";
		$result = mysqlMultiRows($query);
		return $result;
	}
	
}


?>
