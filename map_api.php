<?php

$sats_api_key = 'sats123';

if($_GET['api_key']==$sats_api_key){
	
	include('inc/init_for_ajax.php');

	// data
	$tech_id = mysql_real_escape_string($_GET['tech_id']);
	$day = mysql_real_escape_string($_GET['day']);
	$month = mysql_real_escape_string($_GET['month']);
	$year = mysql_real_escape_string($_GET['year']);
	$date = "{$year}-{$month}-{$day}";
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
		
	// initialize
	}else if($_GET['opt']=='init'){
		
		// check for property that has no lat lng coordinate
		$sql = "
			SELECT j.`id`, j.`property_id`, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`assigned_tech` ={$tech_id}
			AND j.date = '".$date."'
			AND p.deleted =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND p.`lat` IS NULL
			AND p.`lng` IS NULL
			AND a.`country_id` = {$country_id}
		";
		$result = mysql_query($sql);

		if(mysql_num_rows($result)>0){
			
			$ctr = 2;
			while($row = mysql_fetch_array($result)){
				
				
				echo $address = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}, {$country_name}";
				$coordinate = getGoogleMapCoordinates($address);
				
				echo " ---- lat: {$coordinate['lat']} lng {$coordinate['lng']}<br />";

				// update lat lng
				mysql_query("
					UPDATE `property`
					SET `lat` = {$coordinate['lat']},
						`lng` = {$coordinate['lng']}
					WHERE `property_id` = {$row['property_id']}
				");
				
				
			}
			
			//echo "has data";
			//echo "<script>window.location='/maps.php?tech_id={$tech_id}&day={$day}&month={$month}&year={$year}';</script>";
			
		}
		
		// run sort on firt load
		$mp_sql = mysql_query("
			SELECT *
			FROM `map_routes`
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'
			AND `sorted` = 1
		");

		if(mysql_num_rows($mp_sql)==0){
			
			
			manualSortJobBySortOrder('ASC',$tech_id,$date,$country_id);
			
			
			// add map route
			$sql = "
				INSERT INTO
				`map_routes` (
					`tech_id`,
					`date`,
					`sorted`
				)
				VALUES(
					{$tech_id},
					'{$date}',
					1
				)
			";
			mysql_query($sql);
			
		}else{
			
			// updates the map listing to it's newest state
			updateMapListing($tech_id,$date,$country_id);
			
		}
		
		$ak_arr = array(
			'init_success' => 1
		);
		
		echo json_encode($ak_arr);
		
		
	// get start point	
	}else if($_GET['opt']=='start'){

		// get start and end point
		$mp_sql = mysql_query("
			SELECT *
			FROM `map_routes`
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'
		");
		$mp = mysql_fetch_array($mp_sql); 

		if($mp['start']!=""){
			
			
			$start_acco_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$mp['start']}
				AND `country_id` = {$country_id}
			");
			$start_acco = mysql_fetch_array($start_acco_sql);

			if(mysql_num_rows($start_acco_sql)>0){	

				echo json_encode($start_acco);
				
			}
			
		}
		
	// get jobs	
	}else if($_GET['opt']=='jobs'){
		
		// JOBS
		$jr_list = getJobRouteList2($tech_id,$date,$country_id);

		$jr_count = mysql_num_rows($jr_list);

		$comp_count = 0;
		$jr_arr = array();
		while($jr = mysql_fetch_array($jr_list)){
			
			if($jr['ts_completed']==1){
				$comp_count++;
			}
			
			$jr_arr[$jr['sort_order']] = array(
			
				'jid' => $jr['jid'],
				'job_type' => $jr['job_type'],
				'j_status' => $jr['j_status'],
				'tech_notes' => $jr['tech_notes'],
				'time_of_day' => $jr['time_of_day'],
				'completed_timestamp' => $jr['completed_timestamp'],
				'job_reason_id' => $jr['job_reason_id'],
				'ts_completed' => $jr['ts_completed'],
				'j_service' => $jr['j_service'],
				'created' => $jr['created'],
				'urgent_job' => $jr['urgent_job'],
				
				'property_id' => $jr['property_id'],
				'p_address_1' => $jr['p_address_1'],
				'p_address_2' => $jr['p_address_2'],
				'p_address_3' => $jr['p_address_3'],
				'p_state' => $jr['p_state'],
				'p_postcode' => $jr['p_postcode'],
				'key_number' => $jr['key_number'],
				'p_lat' => $jr['p_lat'],
				'p_lng' => $jr['p_lng'],
				
				'agency_id' => $jr['agency_id'],
				'agency_name' => $jr['agency_name'],
				'a_address_1' => $jr['a_address_1'],
				'a_address_2' => $jr['a_address_2'],
				'a_address_3' => $jr['a_address_3'],
				'a_state' => $jr['a_state'],
				'a_postcode' => $jr['a_postcode'],
				'a_phone' => $jr['a_phone']
				
			);
		}
		
		$jr_arr['count'] = $jr_count;
		
		echo json_encode($jr_arr);
		
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
		
		// get start and end point
		$mp_sql = mysql_query("
			SELECT *
			FROM `map_routes`
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'
		");
		$mp = mysql_fetch_array($mp_sql);
		
		if($mp['end']!=""){
			
			$end_acco_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$mp['end']}
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
		
	}else if($_GET['opt']=='map_routes'){
		
		// get start and end point
		$mp_sql = mysql_query("
			SELECT *
			FROM `map_routes`
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'
		");
		$mp = mysql_fetch_array($mp_sql);
		echo json_encode(
			array(
				'start' => $mp['start'], 
				'end' => $mp['end']
			)
		);
		
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
		
		$ak_sql = mysql_query("
			SELECT DISTINCT (
				a.`agency_id`
			), a.`agency_id` , a.`agency_name` 
			FROM jobs AS j
			LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
			LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
			WHERE j.`assigned_tech` = {$tech_id}
			AND j.date = '{$date}'
			AND p.deleted = 0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND j.`sort_date` = '{$date}'
			AND a.`country_id` = {$country_id}
		");
		
		if(mysql_num_rows($ak_sql)>0){	

			while( $ak = mysql_fetch_array($ak_sql) ){
				$ak_arr[] = array(
						'agency_id' => $ak['agency_id'], 
						'agency_name' => $ak['agency_name']
					);
				
			}	
			//echo json_encode($ac);
			echo json_encode($ak_arr);
			
		}
	
	// 	SET START AND END POINT
	}else if( $_GET['opt']=='set_start_end' ){
		
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		
		
		// start point
		if($start!=""){
			
			// start
			// get accomodation address
			$a_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$start}
				AND `lat` IS NULL
				AND `lng` IS NULL
			");

			if(mysql_num_rows($a_sql)>0){
				$a = mysql_fetch_array($a_sql);
				// get geocode
				$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
				// update agency lat/lng
				mysql_query("
					UPDATE `accomodation`
					SET 
						`lat` = '{$coor['lat']}',
						`lng` = '{$coor['lng']}'
					WHERE `accomodation_id` = {$start}
				");
			}
			
		}

		// end point
		if($end!=""){
			
			// end
			// get accomodation address
			$a_sql = mysql_query("
				SELECT *
				FROM `accomodation`
				WHERE `accomodation_id` = {$end}
				AND `lat` IS NULL
				AND `lng` IS NULL
			");

			if(mysql_num_rows($a_sql)>0){
				$a = mysql_fetch_array($a_sql);
				// get geocode
				$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
				// update agency lat/lng
				mysql_query("
					UPDATE `accomodation`
					SET 
						`lat` = '{$coor['lat']}',
						`lng` = '{$coor['lng']}'
					WHERE `accomodation_id` = {$end}
				");
			}

			
		}


		// check if route already set
		$mp_sql = mysql_query("
			SELECT *
			FROM `map_routes`
			WHERE `tech_id` = {$tech_id}
			AND `date` = '{$date}'
		");

		// map route data already created, just update it start and end point then
		if( $start!="" || $end!="" ){
			
			if(mysql_num_rows($mp_sql)>0){
			
				// update start and end point
				$sql = "UPDATE `map_routes`
					SET `start` = '{$start}',
						`end` = '{$end}'
					WHERE `tech_id` = {$tech_id}
					AND `date` = '{$date}'";
				mysql_query($sql);
				
			}else{
				
				// add map route
				$sql = "
					INSERT INTO
					`map_routes` (
						`tech_id`,
						`date`,
						`start`,
						`end`
					)
					VALUES(
						{$tech_id},
						'{$date}',
						'{$start}',
						'{$end}'
					)
				";
				mysql_query($sql);
				
			}
			
			$data = array(
				'tech_id' => $tech_id, 
				'date' => $date,
				'start' => $start,
				'end' => $end
			);
			
			//header('Content-type: application/json');
			echo $_GET['callback']."(".json_encode($data).")";
			//echo json_encode($data);
			
		}
		
	// ADD AGENCY KEYS		
	}else if( $_GET['opt']=='set_agency_keys' ){
		
		$agency_id = $_GET['agency_id'];
		
		// get agency address
		$a_sql = mysql_query("
			SELECT *
			FROM `agency`
			WHERE `agency_id` = {$agency_id}
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
				WHERE `agency_id` = {$agency_id}
			");
		}

		$job_count = getJobsTotalRoutes($tech_id,$date,$country_id);
		$key_count = getTotalKeyRoutes($tech_id,$date,$country_id);

		$last_index = ($job_count+$key_count)+2;
		
		$keys_array = array(
				'Pick Up',
				'Drop Off'
			);
			
		foreach($keys_array as $val){
			
			mysql_query("
				INSERT INTO
				`key_routes`(
					`tech_id`,
					`date`,
					`action`,
					`agency_id`,
					`sort_order`
				)
				VALUES(
					{$tech_id},
					'{$date}',
					'{$val}',
					'{$agency_id}',
					{$last_index}
				)
			");	

			$last_index++;
			
		}
		
		$data = array(
			'tech_id' => $tech_id, 
			'date' => $date,
			'agency_id' => $agency_id
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);
	
	// drag and drop sort
	}else if( $_GET['opt']=='drag_and_drop_sort' ){
		
		$job_id = $_GET['tbl_maps'];
		$i = 2;
		
		foreach($job_id as $val){
			
			$temp = explode(":",$val);
			$map_type = $temp[0];
			$id = $temp[1];
			
			if($val!=""){
				
				// jobs
				if($map_type=="jobs_id"){
					
					if($id!=""){
						
						$sql = "
							UPDATE `jobs`
							SET `sort_order` = {$i}
							WHERE `id` = {$id}
							AND `assigned_tech` = {$tech_id}
						";
						mysql_query($sql);
						
					}

				}else{
						
					// key routes
					if($id!=""){
						
						$sql = "
							UPDATE `key_routes`
							SET `sort_order` = {$i}
							WHERE `key_routes_id` = {$id}
							AND `tech_id` = {$tech_id}
						";
						mysql_query($sql);
						
					}
							
				}
				
				$i++;
				
			}

		}
		
		$data = array(
			'tech_id' => $tech_id, 
			'date' => $date,
			'job_id' => $job_id
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);
		
		
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
	
	// REMOVE JOBS
	}else if( $_GET['opt']=='remove_jobs' ){
		
		$del_map_item = $_GET['del_map_item'];
		$map_id = $_GET['map_id'];
		
		$j = 2;
		foreach($map_id as $val){
			
			$temp = explode(":",$val);
			$map_type = $temp[0];
			$id = $temp[1];
			
			
			
			// deleted
			if(in_array($val, $del_map_item)){
				
				//$in_flag = 1;
				
				// jobs
				if($map_type=="job_id"){
					$sql = "
						UPDATE `jobs`
						SET 
							`status` = 'To Be Booked',
							`date` = NULL,
							`time_of_day` = NULL,
							`assigned_tech` = NULL,
							`ts_completed` = 0,
							`job_reason_id` = 0,
							`door_knock` = 0,
							`completed_timestamp` = NULL,
							`tech_notes` = NULL,
							`sort_order` = NULL
						WHERE `id` = {$id}
					";
					//echo "<br />";
					mysql_query($sql);
				}else{ // keys
					$sql = "
						UPDATE `key_routes`
						SET `deleted` = 1
						WHERE `key_routes_id` = {$id}
					";
					//echo "<br />";
					mysql_query($sql);
				}
				
			}else{
				
				//$in_flag = 2;
				
				if($map_type=="job_id"){
				
					$sql = "
						UPDATE `jobs`
						SET `sort_order` = {$j}
						WHERE `id` = {$id}
					";
					//echo "<br />";
					mysql_query($sql);
					
				}else{
					
					$sql = "
						UPDATE `key_routes`
						SET `sort_order` = {$j}
						WHERE `key_routes_id` = {$id}
					";
					//echo "<br />";
					mysql_query($sql);
				}
				
				$j++;
				
			}
			
			
			
		}
		
		//$delete_mp = 1;
		
		$data = array(
			'del_map_item' => $del_map_item, 
			'map_id' => $map_id
		);
		
		//header('Content-type: application/json');
		echo $_GET['callback']."(".json_encode($data).")";
		//echo json_encode($data);
		
	}
	
}


?>