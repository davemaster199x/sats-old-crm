<?php

class Tech_Run_Class{

	function getJobsViaRegion($tech_run_id,$params){
		
		
		// get tech run data
		$tr_sql = $this->getTechRun($tech_run_id);
		$tr = mysql_fetch_array($tr_sql);
		$tech_id = $tr['assigned_tech'];
		$date = $tr['date'];
		
		
		if( $params['is_assigned'] ==1 ){
			
			$is_assigned_str = "			
				AND j.`assigned_tech` = {$tech_id} 
				AND j.`date` = '{$date}'
			";
			
		}else{
			
			$is_assigned_str = "			
				
				AND (
					j.`assigned_tech` = {$tech_id} 
					OR j.`assigned_tech` IS NULL
				)
				
				AND(
					j.`date` = '{$date}'
					OR j.`date` IS NULL
					OR j.`date` = '0000-00-00'
					OR j.`date` = ''
				)
				
			";
			
		}
		
		
		if($params['filter_postcodes']==1){
			// get postcode 
			$params2 = array(
				'country_id' => $params['country_id']
			);
			$postcodes_fin = $this->getPostCodesViaSubRegions($tech_run_id,$params2);
			$postcode_filter_str = " AND p.`postcode` IN ( {$postcodes_fin} ) ";
		}
		
		// exclude current tech run list
		if($params['filter_ex_tr_list']==1){
			$ex_trr_list = "
				AND j.`id` NOT IN(
					SELECT trr.`row_id`
					FROM  `tech_run_rows` AS trr 
					WHERE  trr.`row_id_type` =  'job_id'
					AND trr.`status` = 1
					AND trr.`tech_run_id` = {$tech_run_id}
				)
			";
		}
		
		// get main filters
		$main_filters = $this->getTechRunFilters($tech_run_id);
		

		$sql_str = "
			SELECT *, j.`id` AS jid
			FROM jobs AS j
			LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
			LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
			LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$params['country_id']}
			

			{$postcode_filter_str}
			
			
			{$is_assigned_str}
			
			
			AND (
				j.`status` = 'To Be Booked' 
				OR j.`status` = 'Booked' 
				OR j.`status` = 'DHA'
			)
			
			

			{$main_filters}
			
			
			{$ex_trr_list}

			

		";
		
		return mysql_query($sql_str);
		
	}
	
	
	function getTechRunFilters($tech_run_id){
		
		// get tech run data
		$tr_sql = $this->getTechRun($tech_run_id);
		$tr = mysql_fetch_array($tr_sql);
		$tech_id = $tr['assigned_tech'];
		$date = $tr['date'];
		
		// if electrician?
		$tsql = mysql_query("
			SELECT * 
			FROM  `staff_accounts` 
			WHERE `StaffID` = {$tech_id}
			AND `is_electrician` = 1	
		");
		$isElectrician = ( mysql_num_rows($tsql)>0 )?1:0;

		//if NOT electrician
		if($isElectrician!=1){
			$is_elect_str = "
				AND NOT `job_type` = '240v Rebook'
			";
		}
		
		return $str = "
			AND (
				(
					j.`unavailable` !=1
					AND j.`unavailable_date` !=  '{$date}'
				)
				OR (
					j.`unavailable` IS NULL 
					OR j.`unavailable_date` IS NULL
				)
			)
				
			AND NOT (
				j.`job_type` = 'Lease Renewal' AND j.`start_date` < '{$date}'
			)
			AND NOT (
				j.`job_type` = 'Change of Tenancy' AND j.`start_date` < '{$date}'
			)
			AND NOT (
				j.`status` = 'DHA' AND j.`start_date` < '{$date}'
			)
			
			
			{$is_elect_str}
		";
		
	}
	
	
	function getTechRunRows($tech_run_id,$params){
		
			// get tech run data
			$tr_sql = $this->getTechRun($tech_run_id);
			$tr = mysql_fetch_array($tr_sql);
			$tech_id = $tr['assigned_tech'];
			$date = $tr['date'];
			
			
			
			// select
			if( $params['distinct']==1 ){
		
				// check property address lat/lng
				switch( $params['distinct_val'] ){
					case 'a.`agency_id`':
						$sel_str .= '
							SELECT DISTINCT (a.`agency_id`), a.`agency_name`
						';
					break;
						
				}
				
			}else{
				$sel_str .= "SELECT *, j.`id` AS jid, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`postcode` AS p_postcode";
			}
			
			
			
	
			if( $params['is_vts']==1 || $tr['run_complete']==1 ){
				$filter_str = "
					AND(
						j.`status` = 'Booked'
						OR j.`status` = 'Pre Completion'
						OR j.`status` = 'Merged Certificates'
						OR j.`status` = 'Completed'
					)	
					AND (
						j.`assigned_tech` = {$tech_id} 
						AND j.`date` = '{$date}'
					)
				";
				
			}else{
				$filter_str = "
					AND (
						j.`status` = 'To Be Booked'	
						OR j.`status` = 'Booked' 
						OR j.`status` = 'DHA'
						OR j.`status` = 'Escalate'
					)
				";
			}
			
			
			
			
			$main_filters = $this->getTechRunFilters($tech_run_id);
			
			
			if( $params['postcode_regions']!="" ){
				$passed_filters .= " AND p.`postcode` IN ( {$params['postcode_regions']} ) ";
			}
			
			
			if( $params['exclude_key']==1 ){
				
				$keys_filters .= " 
					AND trr.`row_id_type` != 'keys_id'
				";
				
				
			}else{
				
				$keys_filters .= " 
					OR (
						trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tech_run_id}
					) 
				";
				
			}
			

			//$params = "";
			$sql_str = "
				{$sel_str}
				FROM `tech_run_rows` AS trr
				LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`
				LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
				LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
				LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
				LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
				WHERE tr.`tech_run_id` = {$tech_run_id}
				AND tr.`country_id` = {$params['country_id']}
				AND p.`deleted` =0
				AND a.`status` = 'active'
				AND j.`del_job` = 0
				AND a.`country_id` = {$params['country_id']}
				
				{$filter_str}	
				
				{$main_filters}
				
				{$passed_filters}
				
				{$keys_filters}
				
				ORDER BY trr.`sort_order_num`
			";
			
			return mysql_query($sql_str);
			
	}
	
	
	function getPostCodesViaSubRegions($tech_run_id,$params){
		
		if( $params['sub_region_array']!="" ){
			
			$sr_ids = implode(",",$params['sub_region_array']);	
			$filter_str = " AND pr.`postcode_region_id` IN( {$sr_ids} ) ";
			
		}
		
		$sub_r_sql = mysql_query("
			SELECT * 
			FROM  `tech_run_sub_regions` AS trsb
			LEFT JOIN  `postcode_regions` AS pr ON trsb.`sub_region_id` = pr.`postcode_region_id` 
			WHERE trsb.`tech_run_id` ={$tech_run_id}
			AND pr.`country_id` = {$params['country_id']}
			{$filter_str}
		");
		while( $row = mysql_fetch_array($sub_r_sql) ){
			$postcodes .= ",".$row['postcode_region_postcodes'];
		}
		
		return $postcodes_fin = str_replace(',,',',',substr($postcodes,1));
		
	}
	
	function getPostcodeRegionPostCodes($params){
		
		$sr_id_cs = implode(",",$params['sub_region_array']);	
	
		$postcodes_imp = null;

		// get all postcode via sub regions
		$sel_query = "pc.`postcode`";                
		$postcode_params = array(
			'sel_query' => $sel_query,			
			'sub_region_id_imp' => $sr_id_cs,                                                                
			'deleted' => 0,
			'display_query' => 0
		);
		$postcode_sql = Sats_Crm_Class::get_postcodes($postcode_params);	

		$postcodes_arr = [];
		while ( $postcode_row = mysql_fetch_array($postcode_sql)) {
			$postcodes_arr[] = $postcode_row['postcode'];
		}

		if( count($postcodes_arr) > 0 ){
			$postcodes_imp = implode(",", $postcodes_arr);
		}

		return $postcodes_imp;
		
	}
	
	function getSubRegions($tech_run_id,$params){
		
		return mysql_query("
			SELECT * 
			FROM `tech_run_sub_regions` AS trsb
			LEFT JOIN  `postcode_regions` AS pr ON trsb.`sub_region_id` = pr.`postcode_region_id` 
			WHERE trsb.`tech_run_id` ={$tech_run_id}
			AND pr.`country_id` = {$params['country_id']}
		");
		
	}
	
	function getTechRun($tech_run_id){
		return mysql_query("
			SELECT *
			FROM `tech_run`
			WHERE `tech_run_id` = {$tech_run_id}
		");
	}
	
	function getLastSortOrderNumber($tech_run_id){
		$sql = mysql_query("
			SELECT *
			FROM  `tech_run_rows`
			WHERE `tech_run_id` ={$tech_run_id}
			ORDER BY `sort_order_num` DESC 
			LIMIT 1
		");
		$row = mysql_fetch_array($sql);
		return $row['sort_order_num'];
	}
	
	
	function clearTechRunRows($tech_run_id,$params){
		
		// get tech run data
		$tr_sql = $this->getTechRun($tech_run_id);
		$tr = mysql_fetch_array($tr_sql);
		$tech_id = $tr['assigned_tech'];
		$date = $tr['date'];
		
		// get postcode 
		$params2 = array(
			'country_id' => $params['country_id'],
			'sub_region_array' => $params['sub_region_array']
		);
		$postcodes_fin = $this->getPostcodeRegionPostCodes($params2);		
		
		mysql_query("
			DELETE trr
			FROM `tech_run_rows` AS trr
			LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
			WHERE trr.`tech_run_id` = {$tech_run_id}
			AND trr.`row_id_type` =  'job_id'
			AND j.`status` NOT IN('Booked','Pre Completion','Merged Certificates','Completed')
			AND j.`assigned_tech` != {$tech_id}
			
		");
		
	}
	
	function clearTechRunRegions($tech_run_id){
		mysql_query("
			DELETE 
			FROM `tech_run_sub_regions`
			WHERE `tech_run_id` = {$tech_run_id}
		");
	}

}

?>