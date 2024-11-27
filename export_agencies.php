<?php

	include('inc/init.php');
	
	
	$crm = new Sats_Crm_Class;

	// header sort parameters
	$sort = ($_REQUEST['sort'])?$_REQUEST['sort']:'a.agency_name';
	$order_by = ($_REQUEST['order_by'])?$_REQUEST['order_by']:'ASC';
	
	// search filters
	$state = $_REQUEST['state'];
	$salesrep = $_REQUEST['salesrep'];
	$region = $_REQUEST['region'];
	$phrase = $_REQUEST['phrase'];	
	
	$result = get_agency_list(1,'','',$sort,$order_by,$state,$salesrep,$region,$phrase);
	
	// filename
	$filename = "Agencies_".date("d/m/Y").".csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	// get services
	$serv_type_str = "";
	$ajt_sql = $crm->getActiveServices();
	while($ajt = mysql_fetch_array($ajt_sql)){
		$serv_type_str .= ',"'.$ajt['type'].'","Price"';
	}
	
	// get services
	$alarm_pwr_str_hdr = "";
	$alarm_pwr_sql = $crm->getAlarmPower();
	while($alarm_pwr = mysql_fetch_array($alarm_pwr_sql)){
		$alarm_pwr_str_hdr .= ',"'.$alarm_pwr['alarm_pwr'].'"';
	}
	
	// body content
	//echo "Agency Name,Agency Contact,Phone,Sales Rep,State,Region,Properties,Last Contact,Smoke Alarms,Safety Switch,Corded Windows,Pool Barriers\n";
	echo "Agency Name,Legal Name,Address,Suburb,Postcode,State,Region,Phone,Accounts Email,Agency Email,Agency Contact,Contact Phone,Contact Email,Sales Rep,Properties,Last Contact{$serv_type_str}{$serv_type_price_str}{$alarm_pwr_str_hdr},Franchise Group,Country,Attach invoices to emails?,Combined Cert / Invoice PDF?,Send Entry Notice?,Work Order Required?,Auto Renew?,Key Access Allowed?,Tenant Key Email Required?,Trust Acc\n";
	while ($row = mysql_fetch_array($result)){
	
		$serv_count_str = "";
		$alarm_pwr_str = "";
	
		//Last Contact
		$crm_lcsql = mysql_query("
			SELECT *
			FROM `agency_event_log`
			WHERE `agency_id` ={$row['agency_id']}
			ORDER BY `eventdate` DESC
			LIMIT 0 , 1
		");		
		$crm_lc = mysql_fetch_array($crm_lcsql); 
		$lc = ($crm_lc['eventdate']!="")?date("d/m/Y",strtotime($crm_lc['eventdate'])):'';
		
		if($row['agency_id']!=""){
		
			// get service count
			$ajt_sql = $crm->getActiveServices();
			$i = 0;
			while($ajt = mysql_fetch_array($ajt_sql)){ 	
				$agency_service_price = $crm->getAgencyServicePrice($row['agency_id'],$ajt['id']);
				$serv_count_str .= ",\"".getServiceCount($row['agency_id'],$ajt['id'])."\",\"".( ($agency_service_price>0)?"$".number_format($agency_service_price,2):'' )."\"";
			}
			
			
			// get agency alarms price
			$alarm_pwr_sql = $crm->getAlarmPower();
			$i = 0;
			while($alarm_pwr = mysql_fetch_array($alarm_pwr_sql)){ 	
				$alarm_price = $crm->getAgencyAlarmsPrice($row['agency_id'],$alarm_pwr['alarm_pwr_id']);
				$alarm_pwr_str .= ",\"".( ($alarm_price>0)?"$".number_format($alarm_price,2):'' )."\"";
			}
			
			// trust account software
			$tas_sql = mysql_query("
				SELECT `tsa_name`
				FROM `trust_account_software`
				WHERE `trust_account_software_id` = {$row['trust_account_software']}
			");
			$tsa_row = mysql_fetch_array($tas_sql);
			
			echo "\"".trim($row['agency_name'])."\",\"".trim($row['legal_name'])."\",\"".trim($row['address_1'])." ".trim($row['address_2'])."\",\"".trim($row['address_3'])."\",\"{$row['postcode']}\",\"{$row['state']}\",\"".trim($row['postcode_region_name'])."\",\"{$row['phone']}\",\"".trim($row['account_emails'])."\",\"".trim($row['agency_emails'])."\",\"{$row['contact_first_name']} {$row['contact_last_name']}\",\"{$row['contact_phone']}\",\"{$row['contact_email']}\",\"{$row['FirstName']} {$row['LastName']}\",\"{$row['tot_properties']}\",\"{$lc}\"".$serv_count_str.$alarm_pwr_str.",\"".trim($row['name'])."\",\"".trim($row['country'])."\",".(($row['send_emails'])?'Yes':'No').",".(($row['send_combined_invoice'])?'Yes':'No').",".(($row['send_entry_notice'])?'Yes':'No').",".(($row['require_work_order'])?'Yes':'No').",".(($row['auto_renew'])?'Yes':'No').",".(($row['key_allowed'])?'Yes':'No').",".(($row['key_email_req'])?'Yes':'No').",\"{$tsa_row['tsa_name']}\"\n";
			
			//echo ''.$row['agency_name'].','.$row['a_address_1'].' '.$row['a_address_2'].','.$row['a_address_3'].','.$row['a_postcode'].','.$row['a_state'].','.$row['agency_region_name'].','.$row['phone'].','.$row['account_emails'].','.$row['agency_emails'].','.$row['contact_phone'].','.$row['contact_first_name'].' '.$row['contact_last_name'].','.$row['contact_email'].','.$row['FirstName'].' '.$row['LastName'].','.$row['tot_properties'].','.$lc.','.round((get_serv_num($row['agency_id'],2)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],2).','.round((get_serv_num($row['agency_id'],5)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],5).','.round((get_serv_num($row['agency_id'],6)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],6).','.round((get_serv_num($row['agency_id'],7)/$row['tot_properties'])*100).','.get_serv_price($row['agency_id'],7).','.$row['name'].'\n';
			
		}		
		
	}
	
	  
	
	

?>

