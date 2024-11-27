<?php
include('inc/init_for_ajax.php');

// country to clear
//$country_id = 1; // AU
$country_id = 2; // NZ

$proceeed = $_REQUEST['proceeed'];
$run_script = 0;
//$run_script = 1;
$script_delay = 1;

if( $country_id==1 ){
	$country_name = 'Australia';
}else if ( $country_id==2 ){
	$country_name = 'New Zealand';
}

echo "<h1>CLEAR DB SCRIPT</h1>";
echo "Country data to clear: {$country_name}<br />";
echo "Run Delete Script?: ".(($run_script==1)?'<span style=\'color:red\'>YES</span>':'<span style=\'color:green\'>NO</span>')."<br />";
echo "Script Delay: {$script_delay} sec<br />";
echo "Proceed? <a href='clear_db_data_script.php?proceeed=1'>YES</a> | <a href='clear_db_data_script.php?proceeed=0'>NO</a> <br />";


if( $proceeed==1 ){
	
	
	echo "------- DB CLEARING STARTED ---------";
	
	echo "<br /><br />";

	// accomodation
	echo $sql = "
		DELETE
		FROM `accomodation`
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	
	// admin_documents
	// check images admin_documents/au
	echo $sql = "
		DELETE ad 
		FROM `admin_documents` AS ad 
		LEFT JOIN `admin_doc_header` AS adh ON ad.`admin_doc_header_id` = adh.`admin_doc_header_id` 
		WHERE adh.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// admin_doc_header
	echo $sql = "
		DELETE
		FROM `admin_doc_header`
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// agency_alarms
	echo $sql = "
		DELETE aa 
		FROM `agency_alarms` AS aa 
		LEFT JOIN `agency` AS a ON aa.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// agency_event_log
	echo $sql = "
		DELETE ael 
		FROM `agency_event_log` AS ael 
		LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// agency_maintenance
	echo $sql = "
		DELETE am 
		FROM `agency_maintenance` AS am 
		LEFT JOIN `agency` AS a ON am.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";



	// agency_services
	echo $sql = "
		DELETE agen_serv  
		FROM `agency_services` AS agen_serv
		LEFT JOIN `agency` AS a ON agen_serv.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// agency_tracking
	echo $sql = "
		DELETE agen_trak  
		FROM `agency_tracking` AS agen_trak
		LEFT JOIN `agency` AS a ON agen_trak.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	

	// alarm
	echo $sql = "
		DELETE alrm  
		FROM `alarm` AS alrm 
		LEFT JOIN `jobs` AS j ON alrm.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// alarm_price
	echo $sql = "
		DELETE ap 
		FROM `alarm_price` AS ap
		LEFT JOIN `agency` AS a ON ap.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// bundle_services
	echo $sql = "
		DELETE bs 
		FROM `bundle_services` AS bs 
		LEFT JOIN `jobs` AS j ON bs.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// calendar
	echo $sql = "
		DELETE
		FROM `calendar` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// call_centre_data
	echo $sql = "
		DELETE
		FROM `call_centre_data` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// contacts
	echo $sql = "
		DELETE c 
		FROM `contacts` AS c
		LEFT JOIN `agency` AS a ON c.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// contractors
	echo $sql = "
		DELETE
		FROM `contractors` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	

	// contractor_appointment
	// uploads/agency_files/
	echo $sql = "
		DELETE
		FROM `contractor_appointment` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	
	

	// corded_window: check images
	echo $sql = "
		DELETE cw
		FROM `corded_window` AS cw 
		LEFT JOIN `jobs` AS j ON cw.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// cron_log
	echo $sql = "
		DELETE
		FROM `cron_log` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// daily_figures_per_date
	echo $sql = "
		DELETE
		FROM `daily_figures_per_date` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// daily_figures
	echo $sql = "
		DELETE
		FROM `daily_figures` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";



	// escalate_agency_info
	echo $sql = "
		DELETE eai
		FROM `escalate_agency_info` AS eai
		LEFT JOIN `agency` AS a ON eai.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// expenses
	// images/expenses_receipt
	echo $sql = "
		DELETE
		FROM `expenses` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// expense_summary
	echo $sql = "
		DELETE
		FROM `expense_summary` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// figures
	echo $sql = "
		DELETE
		FROM `figures` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// franchise_groups
	echo $sql = "
		DELETE
		FROM `franchise_groups` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	

	// global_settings
	echo $sql = "
		DELETE
		FROM `global_settings` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// incident_photos
	// images/incident
	echo $sql = "
		DELETE ip
		FROM `incident_photos` AS ip
		LEFT JOIN `incident_and_injury` AS iai ON ip.`incident_and_injury_id` = iai.`incident_and_injury_id`
		WHERE iai.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// incident_and_injury
	echo $sql = "
		DELETE
		FROM `incident_and_injury` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// job_log
	echo $sql = "
		DELETE jl
		FROM `job_log` AS jl 
		LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	

	// key_routes
	echo $sql = "
		DELETE kr
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// kms
	echo $sql = "
		DELETE k
		FROM `kms` AS k
		LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
		WHERE v.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	

	// ladder_inspection_selection
	echo $sql = "
		DELETE lis 
		FROM `ladder_inspection_selection` AS lis
		LEFT JOIN `ladder_check` AS lc ON lis.`ladder_check_id` = lc.`ladder_check_id`
		LEFT JOIN `tools` AS t ON lc.`tools_id` = t.`tools_id`
		WHERE t.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// ladder_check
	echo $sql = "
		DELETE lc
		FROM `ladder_check` AS lc
		LEFT JOIN `tools` AS t ON lc.`tools_id` = t.`tools_id`
		WHERE t.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// leave
	echo $sql = "
		DELETE
		FROM `leave` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// map_routes
	echo $sql = "
		DELETE
		FROM `map_routes` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	

	// noticeboard
	echo $sql = "
		DELETE
		FROM `noticeboard` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// notifications
	echo $sql = "
		DELETE
		FROM `notifications` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// property_alarms
	echo $sql = "
		DELETE pa 
		FROM `property_alarms` AS pa
		LEFT JOIN `property` AS p ON pa.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// property_event_log
	echo $sql = "
		DELETE pel  
		FROM `property_event_log` AS pel
		LEFT JOIN `property` AS p ON pel.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// property_managers
	echo $sql = "
		DELETE pm  
		FROM `property_managers` AS pm
		LEFT JOIN `agency` AS a ON pm.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// property_propertytype
	echo $sql = "
		DELETE ppt
		FROM `property_propertytype` AS ppt
		LEFT JOIN `property` AS p ON ppt.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// property_services
	echo $sql = "
		DELETE ps
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// purchase_order_item
	echo $sql = "
		DELETE poi
		FROM `purchase_order_item` AS poi
		LEFT JOIN `purchase_order` AS po ON poi.`purchase_order_id` = po.`purchase_order_id`
		WHERE po.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// purchase_order
	echo $sql = "
		DELETE
		FROM `purchase_order` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	

	// renewals
	echo $sql = "
		DELETE
		FROM `renewals` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// resources
	// resources/au
	echo $sql = "
		DELETE
		FROM `resources` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// resources_header
	echo $sql = "
		DELETE
		FROM `resources_header` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// safety_switch
	echo $sql = "
		DELETE ss
		FROM `safety_switch` AS ss 
		LEFT JOIN `jobs` AS j ON ss.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// sales_documents
	echo $sql = "
		DELETE
		FROM `sales_documents` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// sales_snapshot
	echo $sql = "
		DELETE
		FROM `sales_snapshot` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// sales_snapshot_sales_rep
	echo $sql = "
		DELETE
		FROM `sales_snapshot_sales_rep` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	

	// selected_escalate_job_reasons
	echo $sql = "
		DELETE sej
		FROM `selected_escalate_job_reasons` AS sej 
		LEFT JOIN `jobs` AS j ON sej.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// site_accounts
	echo $sql = "
		DELETE
		FROM `site_accounts` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// sms_messages
	echo $sql = "
		DELETE
		FROM `sms_messages` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// stocks
	echo $sql = "
		DELETE
		FROM `stocks` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tech_run_suppliers
	echo $sql = "
		DELETE trs
		FROM `tech_run_suppliers` AS trs
		LEFT JOIN `suppliers` AS sup ON trs.`suppliers_id` = sup.`suppliers_id`
		WHERE sup.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// suppliers
	echo $sql = "
		DELETE
		FROM `suppliers` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// technician_documents
	echo $sql = "
		DELETE td
		FROM `technician_documents` AS td
		LEFT JOIN `tech_doc_header` AS tdh ON td.`tech_doc_header_id` = tdh.`tech_doc_header_id`
		WHERE tdh.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tech_doc_header
	echo $sql = "
		DELETE
		FROM `tech_doc_header` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	

	// tech_run_keys
	echo $sql = "
		DELETE trk
		FROM `tech_run_keys` AS trk
		LEFT JOIN `agency` AS a ON trk.`agency_id` = a.`agency_id`
		WHERE a.`country_id` = {$country_id}

	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// tech_run_rows
	echo $sql = "
		DELETE trr
		FROM `tech_run_rows` AS trr
		LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
		WHERE tr.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tech_run
	echo $sql = "
		DELETE
		FROM `tech_run` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tech_stock_items
	echo $sql = "
		DELETE tsi
		FROM `tech_stock_items` AS tsi
		LEFT JOIN `tech_stock` AS ts ON tsi.`tech_stock_id` = ts.`tech_stock_id`
		WHERE ts.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tech_stock
	echo $sql = "
		DELETE
		FROM `tech_stock` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// test_and_tag
	echo $sql = "
		DELETE tat
		FROM `test_and_tag` AS tat
		LEFT JOIN `tools` AS t ON tat.`tools_id` = t.`tools_id`
		WHERE t.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// vehicles_log
	echo $sql = "
		DELETE vl
		FROM `vehicles_log` AS vl
		LEFT JOIN `vehicles` AS v ON vl.`vehicles_id` = v.`vehicles_id`
		WHERE v.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";



	// vehicles
	echo $sql = "
		DELETE
		FROM `vehicles` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	

	// water_meter
	// images/wm_ts/au/
	echo $sql = "
		DELETE wm
		FROM `water_meter` AS wm 
		LEFT JOIN `jobs` AS j ON wm.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	





	

	// NEEDS TO BE LAST

	// vehicle_files
	// vehicle_files/35
	echo $sql = "
		DELETE vf
		FROM `vehicle_files` AS vf
		LEFT JOIN `vehicles` AS v ON vf.`vehicles_id` = v.`vehicles_id`
		WHERE v.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// tools
	echo $sql = "
		DELETE
		FROM `tools` 
		WHERE `country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// jobs
	echo $sql = "
		DELETE j
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id}
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";

	// property
	echo $sql = "
		DELETE p
		FROM `property` AS p 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";


	// agency 
	echo $sql = "
		DELETE
		FROM `agency` 
		WHERE `country_id` = {$country_id} 
	";
	if($run_script==1){
		mysql_query($sql);
		sleep($script_delay);
	}
	echo "<br /><br />";
	
	
	
	
	echo "------- DB CLEARING FINISHED ---------";
	
	

}

?>