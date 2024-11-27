<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$sats_api_key = 'sats123';

function getGmapAPI_keys(){
	
		return array(
			'google_dev_api_key' => GOOGLE_DEV_API
		);
	
}

if($_GET['api_key']==$sats_api_key){
	
	
	
	$tr_id = mysql_real_escape_string($_GET['tr_id']);

	// data
	$tr_sql = mysql_query("
		SELECT * 
		FROM  `tech_run` 
		WHERE  `tech_run_id` = {$tr_id}
	");

	$tr = mysql_fetch_array($tr_sql);
	
	$tech_id = $tr['assigned_tech'];
	$day = date("d",strtotime($tr['date']));
	$month = date("m",strtotime($tr['date']));
	$year = date("Y",strtotime($tr['date']));
	$date = $tr['date'];
	$sub_regions = $tr['sub_regions'];

	$country_id = mysql_real_escape_string($_GET['country_id']);
	$c_sql = getCountryViaCountryId($country_id);
	$c = mysql_fetch_array($c_sql);
	$country_name = $c['country'];

	// API options
	// get API key
	if( $_GET['opt']=='get_api_key' ){
		
		$ak_arr = array(
			'sats_api_key' => $sats_api_key
		);
		
		echo json_encode($ak_arr);

	}else if( $_GET['opt']=='get_google_dev_api_key' ){ // get google dev API key
		
		$ak_arr = getGmapAPI_keys();
		
		echo json_encode($ak_arr);
		
	
	}else if($_GET['opt']=='init'){ // initialize
		
		
		//$params = array( 'checkLatLng' => 1 );
		$trr_sql = getTechRunRows($tr_id,$country_id);

		if(mysql_num_rows($trr_sql)>0){
			
			$ctr = 2;
			while($row = mysql_fetch_array($trr_sql)){
				
				// JOBS
				if( $row['row_id_type']=='job_id' ){
					
					$job_sql = getJobRowData($row['row_id'],$country_id);
					if( mysql_num_rows($job_sql)>0 ){
						$job = mysql_fetch_array($job_sql);
						if( $job['p_lat']=="" || $job['p_lng']=="" ){
							echo $address = "{$job['p_address_1']} {$job['p_address_2']} {$job['p_address_3']} {$job['p_state']} {$job['p_postcode']}, {$country_name}";
							$coordinate = getGoogleMapCoordinates($address);
							
							//echo " ---- lat: {$coordinate['lat']} lng {$coordinate['lng']}<br />";

							// update lat lng
							mysql_query("
								UPDATE `property`
								SET `lat` = {$coordinate['lat']},
									`lng` = {$coordinate['lng']}
								WHERE `property_id` = {$job['property_id']}
							");
						}
						
					}
			
				}else{
					
					// KEYS
					$key_sql = getTechRunKeys($row['row_id'],$country_id);
					if( mysql_num_rows($key_sql)>0 ){
						$key = mysql_fetch_array($key_sql);
						if( $key['lat']=="" || $key['lng']=="" ){
							echo $address = "{$key['address_1']} {$key['address_2']} {$key['address_3']} {$key['state']} {$key['postcode']}, {$country_name}";
							$coordinate = getGoogleMapCoordinates($address);
							
							//echo " ---- lat: {$coordinate['lat']} lng {$coordinate['lng']}<br />";

							// update lat lng
							mysql_query("
								UPDATE `agency`
								SET `lat` = {$coordinate['lat']},
									`lng` = {$coordinate['lng']}
								WHERE `agency_id` = {$key['agency_id']}
							");
						}
						
					}
					
				}
				
								
				
				
			}
			
			//echo "has data";
			//echo "<script>window.location='/maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}';</script>";
			
		}
		
		$ak_arr = array(
			'response' => mysql_num_rows($trr_sql)
		);
		
		echo json_encode($ak_arr);
		
		
	// get start point	
	}else if($_GET['opt']=='start'){

		

		if($tr['start']!=""){
			
			
			$start_acco_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$tr['start']}
				AND `country_id` = {$country_id}
			");
			$start_acco = mysql_fetch_array($start_acco_sql);

			if(mysql_num_rows($start_acco_sql)>0){	

				echo json_encode($start_acco);
				
			}
			
		}
		
	// get jobs	
	}else if($_GET['opt']=='get_list'){
		
		// get listing
		$trr_sql = getTechRunRows($tr_id,$country_id);
		
		while( $trr = mysql_fetch_array($trr_sql) ){
			$trr_arr[] = $trr;
		}
		
		echo json_encode($trr_arr);
		
	// get keys
	}else if($_GET['opt']=='keys'){
		
		// KEYS
		$kr_list = getKeyRouteList2($tech_id,$date,$country_id);

		$kr_count = mysql_num_rows($kr_list);

		$kr_arr = array();
		while($kr = mysql_fetch_array($kr_list)){
			
			// get agency that has no lat and lng
			$a_sql = mysql_query("
				SELECT *
				FROM `agency`
				WHERE `agency_id` = {$kr['agency_id']}
				AND `lat` IS NULL
				AND `lng` IS NULL
			");
			
			if(mysql_num_rows($a_sql)>0){

				$a = mysql_fetch_array($a_sql);
				// get geocode
				$coor = getGoogleMapCoordinates("{$a['address_1']} {$a['address_2']} {$a['address_3']} {$a['state']} {$a['postcode']}, {$country_name}");
				// update agency lat/lng
				mysql_query("
					UPDATE `agency`
					SET 
						`lat` = '{$coor['lat']}',
						`lng` = '{$coor['lng']}'
					WHERE `agency_id` = {$kr['agency_id']}
				");
				
				$k_lat = $coor['lat'];
				$k_lng = $coor['lng'];
				
			}else{
				
				$k_lat = $kr['lat'];
				$k_lng = $kr['lng'];
				
			}
			
			
			$kr_arr[$kr['sort_order']] = array(
			
				'key_routes_id' => $kr['key_routes_id'],
				'action' => $kr['action'],
				'completed' => $kr['completed'],
				'completed_date' => $kr['completed_date'],
				'number_of_keys' => $kr['number_of_keys'],
				'agency_staff' => $kr['agency_staff'],
				
				'agency_id' => $kr['agency_id'],
				'agency_name' => $kr['agency_name'],
				'address_1' => $kr['address_1'],
				'address_2' => $kr['address_2'],
				'address_3' => $kr['address_3'],
				'state' => $kr['state'],
				'postcode' => $kr['postcode'],
				'agency_hours' => $kr['agency_hours'],
				'phone' => $kr['phone'],
				'lat' => $k_lat,
				'lng' => $k_lng
				
			);
		}
		
		$kr_arr['count'] = $kr_count;
		echo json_encode($kr_arr);
		
	// get end point
	}else if($_GET['opt']=='end'){
		
		if($tr['end']!=""){
			
			$end_acco_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$tr['end']}
				AND `country_id` = {$country_id}
			");
			$end_acco = mysql_fetch_array($end_acco_sql);

			if(mysql_num_rows($end_acco_sql)>0){	

				
				echo json_encode($end_acco);
				
			}
			
		}

	// get country 
	}else if($_GET['opt']=='get_country'){
		
		$c_sql = getCountryViaCountryId($country_id);
		$c = mysql_fetch_array($c_sql);
		echo json_encode(
			array(
				'country_id' => $c['country_id'], 
				'country_name' => $c['country']
			)
		);
		
	}else if($_GET['opt']=='tech_run'){
		
		// get start and end point
		$tr_sql = mysql_query("
			SELECT *
			FROM `tech_run` AS tr
			LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
			WHERE tr.`tech_run_id` = {$tr_id}
		");
		$tr = mysql_fetch_array($tr_sql);
		echo json_encode($tr);
		
	}else if($_GET['opt']=='accomodation'){
		
		// get start and end point accomodation
		$ac_sql = mysql_query("
			SELECT *
			FROM `accomodation`
			WHERE `country_id` = {$country_id}
			ORDER BY `name`
		");
		
		if(mysql_num_rows($ac_sql)>0){	

			while( $ac = mysql_fetch_array($ac_sql) ){
				$ac_arr[] = array(
						'accomodation_id' => $ac['accomodation_id'], 
						'name' => $ac['name']
					);
				
			}	
			//echo json_encode($ac);
			echo json_encode($ac_arr);
			
		}
		
	}else if($_GET['opt']=='agency_keys'){

		$fn_agency_arr = $crm->get_fn_agencies($country_id);
		$fn_agency_main = $fn_agency_arr['fn_agency_main'];
		$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
		$fn_agency_sub_imp = implode(",",$fn_agency_sub);
		
		$agency_keys_params = array( 
			'distinct' => 1, 
			'distinct_val' => 'a.`agency_id`',
			'job_rows_only' => 1 
		);
		$agency_keys_sql = getTechRunRows($tr_id,$country_id,$agency_keys_params);
		while( $agency_keys = mysql_fetch_array($agency_keys_sql) ){

			$agency_keys_arr[] = $agency_keys;

			// First National added list
			if( $agency_keys['agency_id'] == $fn_agency_main ){ 
																
				$fn_agency_sub_sql_str = "
					SELECT `agency_id`, `agency_name`
					FROM `agency`
					WHERE `agency_id` IN({$fn_agency_sub_imp})
				";
				$fn_agency_sub_sql = mysql_query($fn_agency_sub_sql_str);
				while( $fn_agency_sub_row = mysql_fetch_array($fn_agency_sub_sql) ){ 																	
					$agency_keys_arr[] = $fn_agency_sub_row;
				}															
			}

		}		
		echo json_encode($agency_keys_arr);	
		
	
	// 	SET START AND END POINT
	}else if( $_GET['opt']=='set_start_end' ){
		
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		

		techRunUpdateStartEndPoint($tr_id,$start,$end);
		
		$data = array(
			'success' => 1
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);

		
	// ADD AGENCY KEYS		
	}else if( $_GET['opt']=='add_agency_keys' ){
		
		// data
		$keys_agency = mysql_real_escape_string($_GET['keys_agency']);

		$params = array(
			'tech_run_id' => $tr_id,
			'keys_agency' => $keys_agency,
			'tech_id' => $tech_id,
			'date' => $date,
			'country_id' => $country_id
		);

		techRunAddAgencyKeys($params);	
		
		$data = array(
			'success' => 1
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);
	
	// drag and drop sort
	}else if( $_GET['opt']=='drag_and_drop_sort' ){
		
		$tr_id = $_GET['tr_id'];
		$trw_ids = $_GET['tbl_maps'];
		
		techRunDragAndDropSort($tr_id,$trw_ids);
		
		/*
		$data = array(
			'success' => 1
		);
		
		echo $_GET['callback']."(".json_encode($data).")";
		*/
		
		
	// MOVE JOBS	
	}else if( $_GET['opt']=='move_jobs' ){
		
		$job_id = $_GET['job_id'];
		$move_to_tech_id = $_GET['move_to_tech_id'];
		$move_to_date = $_GET['move_to_date'];
		$move_to_date2 = date("Y-m-d",strtotime(str_replace("/","-",$move_to_date)));
		
		
		foreach($job_id as $val){
	
			// update job
			$sql = "
				UPDATE `jobs`
				SET 
					`status` = 'To Be Booked',
					`assigned_tech` = {$move_to_tech_id},
					`date` = '{$move_to_date2}'
				WHERE `id` = {$val}
			";
			mysql_query($sql);
			
		}
		
		
		$data = array(
			'tech_id' => $move_to_tech_id, 
			'date' => $move_to_date,
			'job_id' => $job_id
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);

	
	// GET TECHNICIAN
	}else if( $_GET['opt']=='get_technician' ){
		
		$tech_sql = mysql_query("
			SELECT *
			FROM `staff_accounts`
			WHERE `active` = 1
			AND ( 
				`FirstName` != '' AND `LastName` != ''
			)
			ORDER BY `FirstName`, `LastName`
		");
		
		while( $tech = mysql_fetch_array($tech_sql) ){
			$tech_arr[] = array(
				'id' => $tech['StaffID'], 
				'first_name' => $tech['FirstName'],
				'last_name' => $tech['LastName']
			);
		}
		
		echo json_encode($tech_arr);
	
	
	}else if( $_GET['opt']=='get_row_data' ){
		
		$row_id_type = mysql_real_escape_string($_GET['row_id_type']);
		$row_id = mysql_real_escape_string($_GET['row_id']);
		
		if( $row_id_type == 'job_id' ){
			
			$jr_sql = getJobRowData($row_id,$country_id);
			$jr = mysql_fetch_array($jr_sql);

			if( $_GET['get_service_icon'] == 1 ){
				// display icons
				$job_icons_params = array(
					'service_type' => $jr['j_service'],
					'job_type' => $jr['job_type']
				);
				$jr['serv_icon'] = $crm->display_job_icons($job_icons_params);
			}			

			echo json_encode($jr);
			
		}else if( $row_id_type == 'keys_id' ){
			
			$jr_sql = getTechRunKeys($row_id,$country_id);
			$jr = mysql_fetch_array($jr_sql);
			echo json_encode($jr);
			
		}else if( $row_id_type == 'supplier_id' ){
			
			$jr_sql = getTechRunSuppliers($row_id);
			$jr = mysql_fetch_array($jr_sql);
			echo json_encode($jr);
			
		}
		
		
		
	}else if( $_GET['opt']=='get_new_list' ){
		
		//appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$country_id);

	}else if( $_GET['opt']=='assign_pin_colors' ){
		
		$trr_id_arr = $_GET['trr_id_arr'];
		$trr_hl_color = mysql_real_escape_string($_GET['trr_hl_color']);
		
		$trr_id_arr2 = explode(",",$trr_id_arr);
		
		$str = assignTechRunPinColors($trr_id_arr2,$trr_hl_color,$tr_id);
		
		$data = array(
			'success' => 1,
			'data' => array(
				'trr_id_arr' => $trr_id_arr,
				'trr_hl_color' => $trr_hl_color
			)
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);

	}else if( $_GET['opt']=='get_tech_run_colors' ){
		
		$trrc_sql = mysql_query("
			SELECT * 
			FROM  `tech_run_row_color`
			WHERE `active` = 1
		");
		
		if(mysql_num_rows($trrc_sql)>0){	
			
			while( $trrc = mysql_fetch_array($trrc_sql) ){
				$trrc_arr[] = $trrc;
			}

			echo json_encode($trrc_arr);
			
		}
		
		

	}else if( $_GET['opt']=='clear_all_colors' ){
		
		$sql_str = "
			UPDATE `tech_run_rows`
			SET `highlight_color` = NULL
			WHERE `tech_run_id` = {$tr_id}
		";
		mysql_query($sql_str);
		
		if( mysql_affected_rows()>0 ){
			$data = array( 'success' => 1 );
			echo $_GET['callback']."(".json_encode($data).")";
		}


	}
	
}else{ // public
	
	if( $_GET['opt']=='get_google_dev_api_key' ){ // get google dev API key
		
		$ak_arr = getGmapAPI_keys();
		
		echo json_encode($ak_arr);
		
	
	}
	
}


?>