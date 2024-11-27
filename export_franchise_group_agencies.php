<?php

	include('inc/init.php');
	
	$fg_id = $_REQUEST['fg_id'];
	
	function get_agency($get_all,$offset,$limit,$sort,$order_by,$state,$salesrep,$region,$phrase,$franchise_groups_id){

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
			$str .= "AND (LOWER(ar.agency_region_name) LIKE '%{$region}%') ";
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
				
		$sql = "
			SELECT *,
					a.address_1 AS a_address_1,
					a.address_2 AS a_address_2,
					a.address_3 AS a_address_3,
					a.state AS a_state,
					a.postcode AS a_postcode
			FROM
			  agency a
			LEFT JOIN  agency_regions ar USING (agency_region_id)
			LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
			WHERE a.`status` = 'active'
			AND `franchise_groups_id` = {$franchise_groups_id}
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
	
	// header sort parameters
	$sort = ($_REQUEST['sort'])?$_REQUEST['sort']:'a.agency_name';
	$order_by = ($_REQUEST['order_by'])?$_REQUEST['order_by']:'ASC';
	
	// search filters
	$state = $_REQUEST['state'];
	$salesrep = $_REQUEST['salesrep'];
	$region = $_REQUEST['region'];
	$phrase = $_REQUEST['phrase'];	
	
	$result = get_agency(1,'','',$sort,$order_by,$state,$salesrep,$region,$phrase,$fg_id);
	
	// filename
	$filename = "Franchise_groups_agencies_".date("d/m/Y").".csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	// body content
	//echo "Agency Name,Agency Contact,Phone,Sales Rep,State,Region,Properties,Last Contact,Smoke Alarms,Safety Switch,Corded Windows,Pool Barriers\n";
	$ajt_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` = 1
	");
	while($ajt = mysql_fetch_array($ajt_sql)){
		$serv_header .= ',"'.$ajt['type'].'"';
	}
	echo "Agency Name,State,Properties{$serv_header}\n";
	$prop_tot = 0;
	$sa_tot = 0;
	$cw_tot = 0;
	$sw_tot = 0;
	$pb_tot = 0;
	while ($row = mysql_fetch_array($result)){
		
		$serv_count_str = "";
	
		//Last Contact
		$crm_sql = mysql_query("
			SELECT *
			FROM `crm`
			WHERE `agency_id` ={$row['agency_id']}
			ORDER BY `eventdate` DESC
			LIMIT 0 , 1
		");		
		$crm = mysql_fetch_array($crm_sql); 
		$lc = ($crm['eventdate']!="")?date("d/m/Y",strtotime($crm['eventdate'])):'';
		
		$ajt_sql = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
		");
		while($ajt = mysql_fetch_array($ajt_sql)){
			$serv_count_str .= ",".get_serv_num($row['agency_id'],$ajt['id'],1);
			$serv_count = get_serv_num($row['agency_id'],$ajt['id'],1);
			$serv_tot[$ajt['id']] += $serv_count;
		}
		
		
		if($row['agency_id']!=""){
			echo "{$row['agency_name']},{$row['a_state']},{$row['tot_properties']}{$serv_count_str}\n";
		}		
		
		 $prop_tot += $row['tot_properties'];
		 
		
		
	}
	
	foreach($serv_tot as $index=>$val){
		$serv_count_tot_str .= ",{$val}";
	}
	echo "Total:,,{$prop_tot}{$serv_count_tot_str}\n";
	
	  
	
	

?>

