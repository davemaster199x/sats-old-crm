<?php

	include('inc/init.php');

	$crm = new Sats_Crm_Class();
	
	function get_services($property_id,$alarm_job_type_id){
	
		$ps_sql = mysql_query("
			SELECT *
			FROM `property_services` 
			WHERE `property_id` = {$property_id}
			AND `alarm_job_type_id` = {$alarm_job_type_id}
		");
		
		if(mysql_num_rows($ps_sql)>0){
			$s = mysql_fetch_array($ps_sql);
			$service = $s['service'];
			switch ($service) {
				case 0:
					$service = 'DIY';
					break;
				case 1:
					$service = 'SATS';
					break;
				case 2:
					$service = 'No Response';
					break;
				case 3:
					$service = 'Other Provider';
					break;
			}		
		}else{
			$service = "N/A";
		}
	
		return $service;
		
	}

	$agency = $_REQUEST['agency'];
	$phrase = trim($_GET['phrase']);

	if($_POST['postcode_region_id']){
		$filterregion = implode(",",$_POST['postcode_region_id']);
		//print_r($region2);
	}else if($_GET['postcode_region_id']){
		$filterregion = $_GET['postcode_region_id'];
		//echo $filterregion;
	}
	
	// header sort parameters
	$sort = $_REQUEST['sort'];
	$order_by = $_REQUEST['order_by'];

	$sort = ($sort)?$sort:'p.`deleted_date`';
	$order_by = ($order_by)?$order_by:'DESC';
	
	$from = $_REQUEST['from'];
	$to = $_REQUEST['to'];


	$jparams = array(
		'custom_select' => '
			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`agency_deleted`,
			p.`deleted_date`,
	
			a.`agency_id`,
			a.`agency_name`
		',
		'country_id' => $country_id,
		'agency_id' => $agency,
		'phrase' => $phrase,
		'deleted_date_from' => $from,
		'deleted_date_to' => $to,
		'p_deleted' => 1,
		'custom_sort' => 'p.`address_2` ASC, p.`address_1` ASC',
		'echo_query' => 0
	);
	$propertylist = $crm->getPropertyOnly($jparams);
	
	//$propertylist = getPropertyList2($agency, $search, '', '', 1, $sort, $order_by, $from, $to);
	
	
	// filename
	$filename = "Delete_Properties_".date("d_m_Y").".csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	// body content
	echo "Address,Suburb,State,Agency,Smoke Alarms,Safety Switch,Corded Windows,Pool Barriers,Deleted By,Deleted Date\n";
	while ( $row = mysql_fetch_array($propertylist) ){	
		if($row['property_id']!=""){
			$delete_by = ($row['agency_deleted']==1)?'Agency':'SATS';
			$delete_date = ($row['deleted_date']!="0000-00-00 00:00:00")?date("d/m/Y",strtotime($row['deleted_date'])):'----';
			echo "{$row['p_address_1']} {$row['p_address_2']},{$row['p_address_3']},{$row['p_state']},{$row['agency_name']},".get_services($row['property_id'],2).",".get_services($row['property_id'],5).",".get_services($row['property_id'],6).",".get_services($row['property_id'],7).",{$delete_by},{$delete_date}\n";
		}		
		
	}		
	

?>

