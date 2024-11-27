<?php

class Job_Class{

	function getJobs($start,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,$distinct,$send_emails='',$client_emailed='',$send_combined_invoice='',$agency='',$postcode_region_id='',$del_job=0,$from_date='',$to_date='',$is_urgent='',$tech_id='',$paginate='',$getCOTredhiglightsCount='',$use_plain_sort='',$country_id,$created_date,$custom_query=''){
	
		$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
		$str = "";
		$sel_str = "";
		
		if($distinct!=""){
			switch($distinct){
				case 'j.`job_type`':
					$sel_str .= " DISTINCT j.`job_type` ";
				break;
				case 'j.`service`':
					$sel_str .= " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
				break;
				case 'p.`state`':
					$sel_str .= " DISTINCT p.`state` ";
				break;
				case 'p.`agency_id`':
					$sel_str .= " DISTINCT p.`agency_id`, a.`agency_name`, a.`auto_renew` AS a_auto_renew ";
				break;
				case 'a.`state`':
					$sel_str .= " DISTINCT a.`agency_id`, a.`state` ";
				case 'sa.`assigned_tech`':
					$sel_str .= " DISTINCT sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
				break;
				case 'j.`status`':
					$sel_str .= " DISTINCT j.`status` ";
				break;
			}	
		}else{
			$sel_str .= " 
				j.`id` AS jid, 
				j.`job_type`,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,				
				j.`date` AS jdate,
				j.`job_price`,
				j.`start_date`,
				j.`due_date`,
				j.`comments`,
				j.`job_reason_id`,
				j.`job_reason_comment`,
				j.`urgent_job`,
				j.`client_emailed`,
				j.`door_knock`,
				j.`booked_with`,
				j.`sms_sent`,
				j.`assigned_tech`,
				j.`ts_completed`,
				j.`completed_timestamp`,
				j.`time_of_day`,
				j.`work_order`,
				j.`at_myob`,
				j.`no_dates_provided`,
				j.`agency_approve_en`,
				j.`ss_quantity`,
				j.`key_access_required`,
				j.`preferred_time`,
				j.`property_vacant`,
				j.`tech_comments`,
				j.`precomp_jobs_moved_to_booked`,
				j.`sms_sent_no_show`,
				j.`sms_sent_merge`,
				j.`bne_to_call_notes`,
				j.`assigned_tech`,
				j.`repair_notes`,
				j.`job_priority`,
				j.`del_job`,
				j.`is_eo`,
				
				p.`property_id`,
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3, 
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				
				p.`tenant_firstname1`,
				p.`tenant_lastname1`,
				p.`tenant_firstname2`,
				p.`tenant_lastname2`,
				p.`tenant_firstname3`,
				p.`tenant_lastname3`,
				p.`tenant_firstname4`,
				p.`tenant_lastname4`,
				
				p.`tenant_mob1`,
				p.`tenant_mob2`,
				p.`tenant_mob3`,
				p.`tenant_mob4`,
				
				p.`tenant_ph1`,
				p.`tenant_ph2`,
				p.`tenant_ph3`,
				p.`tenant_ph4`,
				
				p.`tenant_email1`,
				p.`tenant_email2`,
				p.`tenant_email3`,
				p.`tenant_email4`,
				
				p.`comments` AS p_comments,
				p.`holiday_rental`,
				
				p.`prop_upgraded_to_ic_sa`,
				p.`propertyme_prop_id`,
				p.`retest_date`,
				p.`palace_prop_id`,

				a.`agency_id`,
				a.`agency_name`,
				a.`account_emails`,
				a.`send_emails`,
				a.`allow_dk`,
				a.`phone` AS a_phone,
				a.`auto_renew` AS a_auto_renew,
				a.`franchise_groups_id`,
				a.`pme_supplier_id`,
				a.`palace_diary_id`,
				
				jr.`name` AS jr_name,
				
				sa.`FirstName`,
				sa.`LastName`,
				apd.`api`,
				apd.`api_prop_id`
			";
		}
		
		if($tech_id!=""){
			$str .= " AND j.`assigned_tech` = '{$tech_id}' ";  
		}
	
		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " AND j.job_type = '{$job_type}' ";
			}			  
		}
		
		if($job_status!=""){
			
			// amend for covid-19
            if( $job_status == 'On Hold' ){                				
				$str .= " AND j.`status` IN('On Hold','On Hold - COVID') ";  
            }else{
                $str .= " AND j.`status` = '{$job_status}' ";  
			}
			
		}
		
		if($service!=""){
			$str .= " AND j.`service` = '{$service}' ";  
		}
		
		if($state!=""){
			$str .= " AND p.`state` = '{$state}' ";  
		}
		
		if($agency!=""){
			$str .= " AND p.`agency_id` = '{$agency}' ";  
		}

		if($date!=""){
			$str .= " AND j.`date` = '{$date}' ";  
		}
		
		if($created_date!=""){
			$str .= " AND CAST( j.`created` AS DATE ) = '{$created_date}' ";  
		}
		
		//echo "{$from_date} - {$to_date}";
		
		if( $from_date!="" && $to_date!="" ){
			$str .= " AND j.`date` BETWEEN '{$from_date}' AND '{$to_date}' ";  
		}
		
		if($send_emails!==""){
			$str .= " AND a.`send_emails` = {$send_emails} ";  
		}
		
		if($client_emailed!=""){
			$str .= " AND j.`client_emailed` {$client_emailed} ";
		}
		
		if($send_combined_invoice!=""){
			$str .= " AND a.`send_combined_invoice` = {$send_combined_invoice} ";
		}
		
		if($is_urgent!=""){
			$str .= " AND j.`urgent_job` = {$is_urgent} ";
		}
		
		if($postcode_region_id!=""){
			$str .= " AND p.`postcode` IN ( {$postcode_region_id} ) ";
		}
		
		if($custom_query!=""){
			$str .= " {$custom_query} ";
		}
		
		if($getCOTredhiglightsCount==1){
			$str .= '
				AND (
				(
					j.`start_date` = "" OR
					j.`start_date` IS  NULL OR
					j.`start_date` = "0000-00-00"
				) AND
				(
					j.`due_date` = "" OR
					j.`due_date` IS  NULL OR
					j.`due_date` = "0000-00-00"
				)
			)
			';
		}
		
		if($phrase != "")
		{
			$str .= " AND (";
				# Agency address search	
				$str .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$phrase}%') OR ";
				# Property address search
				$str .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$phrase}%')";
			$str .= " )";
		}
		
		

		// sort
		if( $sort!="" && $order_by!="" ){
			
			if($job_status!='On Hold'){
				$urgent_sort = 'j.`urgent_job` DESC,';
			}else{
				$urgent_sort = '';
			}
			
			/*
			if($job_status=='DHA'){
				$sort = ' ar.`agency_region_name`';
				$order_by = ' ASC';
			}
			*/
			
			if($sort!='p.address_2'&&$sort!='j.job_price'&&$sort!='j.service'){
				$third_sort = ", p.`address_3` {$order_by}";
			}
			
			if( $job_status == 'DHA' ){
				$dha_sort = ' j.`start_date` ASC, ';
			}
			
			if( $job_type == 'cot & lr' ){
				$str .= " ORDER BY CASE WHEN j.`due_date` IS NULL THEN 1 ELSE 0 END, j.`due_date` ASC ";
			}else{
				
				if($use_plain_sort==1){
					$str .= " ORDER BY {$sort} {$order_by} ";
				}else if($sort=='j.job_price'){
					$str .= " ORDER BY {$sort} {$order_by} {$third_sort} ";
				}else{
					$str .= " ORDER BY {$urgent_sort} {$dha_sort} {$sort} {$order_by} {$third_sort} ";
				}				
				
			}
		}

		if($postcode_region_id==""){
			if(is_numeric($start) && is_numeric($limit)){
				$str .= " LIMIT {$start}, {$limit}";
			}
		}else{
			
			/*
			// need this on view all job page
			if( $paginate==1 ){
				if(is_numeric($start) && is_numeric($limit)){
					$str .= " LIMIT {$start}, {$limit}";
				}
			}
			*/
			if(is_numeric($start) && is_numeric($limit)){
				$str .= " LIMIT {$start}, {$limit}";
			}
		}
		
		
		$sql = "SELECT 
			{$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
			LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
			LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
			LEFT JOIN `api_property_data` AS apd ON p.`property_id` = apd.`crm_prop_id`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = {$del_job}
			AND a.`country_id` = {$country_id}		
			{$str}
		";
		return mysql_query($sql);
	
	}



	public function getJobs_v2($params){

		$start = $params['start'];
		$limit = $params['limit'];
		$sort = $params['sort'];
		$order_by = $params['order_by'];
		$job_type = $params['job_type'];
		$job_status = $params['job_status'];
		$service = $params['service'];
		$state = $params['state'];
		$date = $params['date'];
		$phrase = $params['phrase'];
		$distinct = $params['distinct'];
		$send_emails = $params['send_emails'];
		$client_emailed = $params['client_emailed'];
		$send_combined_invoice = $params['send_combined_invoice'];
		$agency = $params['agency'];
		$postcode_region_id = $params['postcode_region_id'];
		$del_job = ($params['del_job']!='')?$params['del_job']:0;
		$from_date = $params['from_date'];
		$to_date = $params['to_date'];
		$is_urgent = $params['is_urgent'];
		$tech_id = $params['tech_id'];		
		$getCOTredhiglightsCount = $params['getCOTredhiglightsCount'];
		$use_plain_sort = $params['use_plain_sort'];
		$country_id = ($params['country_id']!="")?$params['country_id']:$_SESSION['country_default'];
		$created_date = $params['created_date'];
		$custom_query = $params['custom_query'];

		$custom_select = $params['custom_select']; 	
						
	
		$str = "";
		$sel_str = "";

		if( $custom_select != '' ){

			$sel_str =  $custom_select;

		}else{

			if($distinct!=""){
				switch($distinct){
					case 'j.`job_type`':
						$sel_str = " DISTINCT j.`job_type` ";
					break;
					case 'j.`service`':
						$sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
					break;
					case 'p.`state`':
						$sel_str = " DISTINCT p.`state` ";
					break;
					case 'p.`agency_id`':
						$sel_str = " DISTINCT p.`agency_id`, a.`agency_name`, a.`auto_renew` AS a_auto_renew ";
					break;
					case 'a.`state`':
						$sel_str = " DISTINCT a.`agency_id`, a.`state` ";
					case 'sa.`assigned_tech`':
						$sel_str .= " DISTINCT sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
					break;
					case 'j.`status`':
						$sel_str = " DISTINCT j.`status` ";
					break;
				}	
			}else{
				$sel_str = " 
					j.`id` AS jid, 
					j.`job_type`,
					j.`status` AS jstatus,
					j.`service` AS jservice,
					j.`created` AS jcreated,				
					j.`date` AS jdate,
					j.`job_price`,
					j.`start_date`,
					j.`due_date`,
					j.`comments`,
					j.`job_reason_id`,
					j.`job_reason_comment`,
					j.`urgent_job`,
					j.`client_emailed`,
					j.`door_knock`,
					j.`booked_with`,
					j.`sms_sent`,
					j.`assigned_tech`,
					j.`ts_completed`,
					j.`completed_timestamp`,
					j.`time_of_day`,
					j.`work_order`,
					j.`at_myob`,
					j.`no_dates_provided`,
					j.`agency_approve_en`,
					j.`ss_quantity`,
					j.`key_access_required`,
					j.`preferred_time`,
					j.`property_vacant`,
					j.`tech_comments`,
					j.`precomp_jobs_moved_to_booked`,
					j.`sms_sent_no_show`,
					j.`sms_sent_merge`,
					j.`bne_to_call_notes`,
					j.`assigned_tech`,
					j.`repair_notes`,
					j.`job_priority`,
					j.`del_job`,
					
					p.`property_id`,
					p.`address_1` AS p_address_1, 
					p.`address_2` AS p_address_2, 
					p.`address_3` AS p_address_3, 
					p.`state` AS p_state,
					p.`postcode` AS p_postcode,
					
					p.`tenant_firstname1`,
					p.`tenant_lastname1`,
					p.`tenant_firstname2`,
					p.`tenant_lastname2`,
					p.`tenant_firstname3`,
					p.`tenant_lastname3`,
					p.`tenant_firstname4`,
					p.`tenant_lastname4`,
					
					p.`tenant_mob1`,
					p.`tenant_mob2`,
					p.`tenant_mob3`,
					p.`tenant_mob4`,
					
					p.`tenant_ph1`,
					p.`tenant_ph2`,
					p.`tenant_ph3`,
					p.`tenant_ph4`,
					
					p.`tenant_email1`,
					p.`tenant_email2`,
					p.`tenant_email3`,
					p.`tenant_email4`,
					
					p.`comments` AS p_comments,
					p.`holiday_rental`,
					
					p.`prop_upgraded_to_ic_sa`,
					p.`propertyme_prop_id`,
					p.`retest_date`,
					p.`palace_prop_id`,
					apd.`api`,
					apd.`api_prop_id`,
	
					a.`agency_id`,
					a.`agency_name`,
					a.`account_emails`,
					a.`send_emails`,
					a.`allow_dk`,
					a.`phone` AS a_phone,
					a.`auto_renew` AS a_auto_renew,
					a.`franchise_groups_id`,
					a.`pme_supplier_id`,
					a.`palace_diary_id`,
					
					jr.`name` AS jr_name,
					
					sa.`FirstName`,
					sa.`LastName`
				";
			}

		}
		
		
		
		if($tech_id!=""){
			$str .= " AND j.`assigned_tech` = '{$tech_id}' ";  
		}
	
		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " AND j.job_type = '{$job_type}' ";
			}			  
		}
		
		if($job_status!=""){
			
			// amend for covid-19
            if( $job_status == 'On Hold' ){                				
				$str .= " AND j.`status` IN('On Hold','On Hold - COVID') ";  
            }else{
                $str .= " AND j.`status` = '{$job_status}' ";  
			}
			
		}
		
		if($service!=""){
			$str .= " AND j.`service` = '{$service}' ";  
		}
		
		if($state!=""){
			$str .= " AND p.`state` = '{$state}' ";  
		}
		
		if($agency!=""){
			$str .= " AND p.`agency_id` = '{$agency}' ";  
		}

		if($date!=""){
			$str .= " AND j.`date` = '{$date}' ";  
		}
		
		if($created_date!=""){
			$str .= " AND CAST( j.`created` AS DATE ) = '{$created_date}' ";  
		}
		
		//echo "{$from_date} - {$to_date}";
		
		if( $from_date!="" && $to_date!="" ){
			$str .= " AND j.`date` BETWEEN '{$from_date}' AND '{$to_date}' ";  
		}
		
		if($send_emails!=""){
			$str .= " AND a.`send_emails` = {$send_emails} ";  
		}
		
		if($client_emailed!=""){
			$str .= " AND j.`client_emailed` {$client_emailed} ";
		}
		
		if($send_combined_invoice!=""){
			$str .= " AND a.`send_combined_invoice` = {$send_combined_invoice} ";
		}
		
		if($is_urgent!=""){
			$str .= " AND j.`urgent_job` = {$is_urgent} ";
		}
		
		if($postcode_region_id!=""){
			$str .= " AND p.`postcode` IN ( {$postcode_region_id} ) ";
		}
		
		if($custom_query!=""){
			$str .= " {$custom_query} ";
		}
		
		if($getCOTredhiglightsCount==1){
			$str .= '
				AND (
				(
					j.`start_date` = "" OR
					j.`start_date` IS  NULL OR
					j.`start_date` = "0000-00-00"
				) AND
				(
					j.`due_date` = "" OR
					j.`due_date` IS  NULL OR
					j.`due_date` = "0000-00-00"
				)
			)
			';
		}
		
		if($phrase != "")
		{
			$str .= " AND (";
				# Agency address search	
				$str .= " (CONCAT_WS(' ', LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode)) LIKE '%{$phrase}%') OR ";
				# Property address search
				$str .= " (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$phrase}%')";
			$str .= " )";
		}
		
		

		// sort
		if( $sort!="" && $order_by!="" ){
			
			if($job_status!='On Hold'){
				$urgent_sort = 'j.`urgent_job` DESC,';
			}else{
				$urgent_sort = '';
			}
			
			/*
			if($job_status=='DHA'){
				$sort = ' ar.`agency_region_name`';
				$order_by = ' ASC';
			}
			*/
			
			if($sort!='p.address_2'&&$sort!='j.job_price'&&$sort!='j.service'){
				$third_sort = ", p.`address_3` {$order_by}";
			}
			
			if( $job_status == 'DHA' ){
				$dha_sort = ' j.`start_date` ASC, ';
			}
			
			if( $job_type == 'cot & lr' ){
				$str .= " ORDER BY CASE WHEN j.`due_date` IS NULL THEN 1 ELSE 0 END, j.`due_date` ASC ";
			}else{
				
				if($use_plain_sort==1){
					$str .= " ORDER BY {$sort} {$order_by} ";
				}else if($sort=='j.job_price'){
					$str .= " ORDER BY {$sort} {$order_by} {$third_sort} ";
				}else{
					$str .= " ORDER BY {$urgent_sort} {$dha_sort} {$sort} {$order_by} {$third_sort} ";
				}				
				
			}
		}

		if($postcode_region_id==""){
			if(is_numeric($start) && is_numeric($limit)){
				$str .= " LIMIT {$start}, {$limit}";
			}
		}else{
			
			/*
			// need this on view all job page
			if( $paginate==1 ){
				if(is_numeric($start) && is_numeric($limit)){
					$str .= " LIMIT {$start}, {$limit}";
				}
			}
			*/
			if(is_numeric($start) && is_numeric($limit)){
				$str .= " LIMIT {$start}, {$limit}";
			}
		}
		
		
		$sql = "SELECT 
			{$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `api_property_data` AS apd ON j.`property_id` = apd.`crm_prop_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
			LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
			LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = {$del_job}
			AND a.`country_id` = {$country_id}		
			{$str}
		";

		if( $params['display_query'] == 1 ){
			echo $sql;
		}

		return mysql_query($sql);
	
	}

	
	function getLastContact($job_id){
		return mysql_query("
			SELECT `eventdate`
			FROM `job_log`
			WHERE `job_id` ={$job_id}
			ORDER BY `eventdate` DESC
			LIMIT 0 , 1
		");
	}
	
	
	function getTobeBookedSubRegionCount($country_id,$postcode,$job_type="",$job_status="",$custom_query){
		
		// disable this filter, it will conflict for custom query
		if($custom_query==""){
			$job_status = ($job_status!="")?$job_status:'To Be Booked';
		}else{
			$custom_query_str = $custom_query;
		}
		
		
		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " AND j.job_type = '{$job_type}' ";
			}			  
		}
		
		if($job_status!=""){
			$str .= " AND j.`status` = '{$job_status}' ";		  
		}
		
		$sql = mysql_query("
			SELECT count(j.`id`) AS jcount
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$country_id}	
			{$str}
			AND p.`postcode` IN ( {$postcode} )	
			{$custom_query_str}			
		");
		$row = mysql_fetch_array($sql);
		return $row['jcount'];
	}
	
	function getTobeBookedPostcodeViaRegion($region){
	
		$postcodes_imp = null;

		// get all postcode that belong to a region
		$sel_query = "pc.`postcode`";                
		$postcode_params = array(
			'sel_query' => $sel_query,
			'region_id' => $region,   			   	
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
	
	
	// same function just renamed it, so it's more understandable
	function getSubRegionPostcodes($region){		

		$postcodes_imp = null;

		// get all postcode that belong to a region
		$sel_query = "pc.`postcode`";                
		$postcode_params = array(
			'sel_query' => $sel_query,
			'region_id' => $region,    	
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
	
	function getMainRegionCount($country_id,$postcode,$job_type="",$job_status="",$params){
		
		//$job_status = ($job_status!="")?$job_status:'To Be Booked';
		
		$sel_str = " count(j.`id`) AS jcount ";
		
		$job_status = ($job_status!="")?$job_status:'';
		
		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " AND j.job_type = '{$job_type}' ";
			}			  
		}
		
		if($job_status!=""){
			if($params['is_ageing']==1){
				$str .= "
				AND	(
						j.`status` = 'To Be Booked'
						OR j.`status` = 'Pre Completion'
						OR j.`status` = 'Booked'
						OR j.`status` = 'Escalate'
				)
				";
			}else{
				$str .= " AND j.`status` = '{$job_status}' ";
			}					  
		}
		
		if($params['is_ageing']==1){
			$last_30_days = date('Y-m-d', strtotime("-30 days"));
			$str .= " AND CAST(j.`created` AS DATE) < '{$last_30_days}' ";
		}
		
		
		if( is_numeric($params['urgent_job']) ){
			$str .= "AND j.`urgent_job` = '{$params['urgent_job']}'";
		}
		
		$sql_str = "
			SELECT {$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$country_id}	
			{$str}
			AND p.`postcode` IN ( {$postcode} )		
		";
		
		$sql = mysql_query($sql_str);
		$row = mysql_fetch_array($sql);
		return $row['jcount'];
	}
	
	
	function getMainRegionCountForEscalate($country_id,$postcode,$job_type="",$job_status="",$params){
		
		//$job_status = ($job_status!="")?$job_status:'To Be Booked';
		
		$sel_str = " count(j.`id`) AS jcount ";
		
		$job_status = ($job_status!="")?$job_status:'';
		
		if($job_type!=""){
			if($job_type=='cot & lr'){
				$str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
			}else{
				$str .= " AND j.job_type = '{$job_type}' ";
			}			  
		}
		
		if($job_status!=""){
			if($params['is_ageing']==1){
				$str .= "
				AND	(
						j.`status` = 'To Be Booked'
						OR j.`status` = 'Pre Completion'
						OR j.`status` = 'Booked'
						OR j.`status` = 'Escalate'
				)
				";
			}else{
				$str .= " AND j.`status` = '{$job_status}' ";
			}					  
		}
		
		if($params['is_ageing']==1){
			$last_30_days = date('Y-m-d', strtotime("-30 days"));
			$str .= " AND CAST(j.`created` AS DATE) < '{$last_30_days}' ";
		}
		
		$sql_str = "
			SELECT {$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$country_id}	
			{$str}
			AND a.`postcode` IN ( {$postcode} )		
			GROUP BY a.`agency_id`
		";
		
		$sql_str;
		
		$sql = mysql_query($sql_str);
		return mysql_num_rows($sql);
	}
	
	
	
	function isSafetySwitchServiceTypes($service){
		
		$ss_serv_types = array(5,8,9);
		if ( in_array($service, $ss_serv_types) ){
			return true;
		 }else{
			return false;
		 }
		
	}
	

}

?>