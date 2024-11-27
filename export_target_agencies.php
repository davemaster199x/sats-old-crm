<?php

	include('inc/init.php');
	
	
	
	
	function get_target_agency_list($get_all,$offset,$limit,$sort,$order_by,$state,$salesrep,$region,$phrase){

		// state
		if($state!=""){
			$str .= "AND LOWER(a.state) LIKE '%{$state}%' ";
		}
		
		// sales rep
		if($salesrep!=""){
			$str .= "AND (CONCAT_WS(' ',LOWER(s.FirstName), LOWER(s.LastName)) LIKE '%{$salesrep}%') ";
		}
		
		// region
		if($region!=""){
			$str .= "AND a.`postcode` IN ({$region}) ";
		}
		
		// phrase
		if($phrase!=""){
			$str .= "AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(s.FirstName), LOWER(s.LastName), LOWER(a.state), LOWER(ar.agency_region_name) ) LIKE '%{$phrase}%') ";
		}
		
		// sort/order by
		if($sort!=""&&$order_by!=""){
			$str .= " ORDER BY {$sort} {$order_by} ";
		}
				
		// pagination limit
		if($get_all==1){
			$str .= "";
		}else{
			$str .= " LIMIT {$offset}, {$limit}";
		}
				
		// copy pasted from original old query, edited and improved the code - jc
		$sql = "
			SELECT *,
				a.address_1 AS a_address_1,
				a.address_2 AS a_address_2,
				a.address_3 AS a_address_3,
				a.state AS a_state,
				a.postcode AS a_postcode,
				au.`name` AS au_name
			FROM `agency` AS a
			LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
			LEFT JOIN `franchise_groups` AS fg ON a.`franchise_groups_id` = fg.`franchise_groups_id`
			LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
			LEFT JOIN `agency_using` AS au ON a.`agency_using_id` = au.`agency_using_id`
			LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
			WHERE a.`country_id` = {$_SESSION['country_default']}
			AND (
				a.status = 'target' 
				OR a.status IS NULL
			  )
			{$str}
	   ";
	   
	   return mysql_query ($sql);
	
	}
	
	// get services numbers
	function get_serv_num($agency_id,$alarm_job_type_id,$service=""){
	
		$str = "";
	
		if($service !== ""){
			$str .= " AND ps.`service` = {$service} ";
		}
	
		$sql = "
			SELECT COUNT( * ) AS num_serv
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`agency_id` ={$agency_id}
			AND p.`deleted` =0
			AND ps.`alarm_job_type_id` ={$alarm_job_type_id}
			{$str}
		";
	
		$serv_sql = mysql_query($sql);
		
		if(mysql_num_rows($serv_sql)>0){		
			$serv = mysql_fetch_array($serv_sql);		
			return $serv['num_serv'];		
		}else{
			return 0;	
		}	
	
	}
	
	// get services price
	function get_serv_price($agency_id,$service){
		$sql = mysql_query("
			SELECT `price`
			FROM `agency_services`
			WHERE `agency_id` ={$agency_id}
			AND `service_id` ={$service}
		");
		$row = mysql_fetch_array($sql);
		return (mysql_num_rows($sql)>0)?$row['price']:0.00;
	}
	
	// header sort parameters
	$sort = ($_REQUEST['sort'])?$_REQUEST['sort']:'a.agency_name';
	$order_by = ($_REQUEST['order_by'])?$_REQUEST['order_by']:'ASC';
	
	// search filters
	$state = $_REQUEST['state'];
	$salesrep = $_REQUEST['salesrep'];
	$region = $_REQUEST['region'];
	$phrase = $_REQUEST['phrase'];	
	
	$result = get_target_agency_list(1,'','',$sort,$order_by,$state,$salesrep,$region,$phrase);
	
	// filename
	$filename = "Target_Agencies_".date("d/m/Y").".csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	// get service headers
	$serv_type_str = "";
	$ajt_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` = 1
	");
	while($ajt = mysql_fetch_array($ajt_sql)){
		$serv_type_str .= ',"'.$ajt['type'].'"';
	}
	
	// body content
	//echo "Agency Name,Agency Contact,Phone,Sales Rep,State,Region,Properties,Last Contact,Smoke Alarms,Safety Switch,Corded Windows,Pool Barriers\n";
	echo "Agency Name,Address,Suburb,Postcode,State,Region,Phone,Accounts Email,Agency Email,Agency Contact,Contact Phone,Contact Email,Sales Rep,Properties,Last Contact{$serv_type_str},Franchise Group,Country,Agency Using\n";
	while ($row = mysql_fetch_array($result)){
	
		$serv_count_str = "";
	
		//Last Contact
		$crm_sql = mysql_query("
			SELECT *
			FROM `agency_event_log`
			WHERE `agency_id` ={$row['agency_id']}
			ORDER BY `eventdate` DESC
			LIMIT 0 , 1
		");		
		$crm = mysql_fetch_array($crm_sql); 
		$lc = ($crm['eventdate']!="")?date("d/m/Y",strtotime($crm['eventdate'])):'';
		
		if($row['agency_id']!=""){
		
			// get service count
			$ajt_sql = mysql_query("
				SELECT *
				FROM `alarm_job_type`
				WHERE `active` = 1
			");
			$i = 0;
			while($ajt = mysql_fetch_array($ajt_sql)){ 	
				$serv_count_str .= ','.getServiceCount($row['agency_id'],$ajt['id']);
			}
			
			echo "\"".trim($row['agency_name'])."\",\"".trim($row['a_address_1'])." ".trim($row['a_address_2'])."\",\"".trim($row['a_address_3'])."\",{$row['a_postcode']},{$row['a_state']},\"".trim($row['postcode_region_name'])."\",{$row['phone']},\"".trim($row['account_emails'])."\",\"".trim($row['agency_emails'])."\",{$row['contact_first_name']} {$row['contact_last_name']},{$row['contact_phone']},{$row['contact_email']},{$row['FirstName']} {$row['LastName']},{$row['tot_properties']},{$lc}".$serv_count_str.",\"".trim($row['name'])."\",\"".trim($row['country'])."\",\"".trim($row['au_name'])."\"\n";
			
			//echo ''.$row['agency_name'].','.$row['a_address_1'].' '.$row['a_address_2'].','.$row['a_address_3'].','.$row['a_postcode'].','.$row['a_state'].','.$row['agency_region_name'].','.$row['phone'].','.$row['account_emails'].','.$row['agency_emails'].','.$row['contact_phone'].','.$row['contact_first_name'].' '.$row['contact_last_name'].','.$row['contact_email'].','.$row['FirstName'].' '.$row['LastName'].','.$row['tot_properties'].','.$lc.','.round((get_serv_num($row['agency_id'],2)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],2).','.round((get_serv_num($row['agency_id'],5)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],5).','.round((get_serv_num($row['agency_id'],6)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],6).','.round((get_serv_num($row['agency_id'],7)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],7).','.$row['name'].'\n';
			
		}		
		
	}
	
	  
	
	

?>
