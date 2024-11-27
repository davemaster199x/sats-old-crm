<?php

include('inc/init_for_ajax.php');
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$agency_addresses_id = mysql_real_escape_string($_POST['agency_addresses_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);

if($agency_addresses_id == 0 || $agency_addresses_id == ''){
	$agency_sql_str = "
	SELECT 
		`address_1`, 
		`address_2`, 
		`address_3`,
		`state`,
		`postcode` 	
	FROM `agency`
	WHERE `agency_id`={$agency_id}";
	$agency_sql = mysql_query($agency_sql_str);
	if( mysql_num_rows($agency_sql) > 0 ){																					
		$agency = mysql_fetch_array($agency_sql);

		$address_1 = $agency['address_1'];
		$address_2 = $agency['address_2'];
		$address_3 = $agency['address_3'];
		$state = $agency['state'];
		$postcode = $agency['postcode'];
		$address = "{$address_1} {$address_2} {$address_3} {$state} {$postcode}, {$_SESSION['country_name']}";
		$coordinate = getGoogleMapCoordinates($address);
		$lat = $coordinate['lat'];
		$lng = $coordinate['lng'];

		$check_address_str = "SELECT `id` FROM `agency_addresses`
		WHERE `agency_id`={$agency_id} AND `address_1`='{$address_1}' AND `address_2`='{$address_2}' AND `address_3`='{$address_3}' AND `state`='{$state}' AND `postcode`='{$postcode}' AND `type`='2' AND `lat`='{$lat}' AND `lng`='{$lng}'";
		$check_address_sql = mysql_query($check_address_str);
		if( mysql_num_rows($check_address_sql) > 0 ){																					
			$agency = mysql_fetch_array($check_address_sql);
			$agency_addresses_id = $agency['id'];
		} else {
			$new_agency_adress = "INSERT INTO `agency_addresses`(`agency_id`, `address_1`, `address_2`, `address_3`, `state`, `postcode`, `type`, `lat`, `lng`)
			VALUES ('{$agency_id}','{$address_1}','{$address_2}','{$address_3}','{$state}','{$postcode}','2','{$lat}','{$lng}')";
			mysql_query($new_agency_adress);
			$agency_addresses_id = mysql_insert_id();
		}
	}

	
}

if($agency_id == 0 || $agency_id == ''){
	$delete_property_key_str = "DELETE FROM `property_keys` WHERE `property_id`={$property_id}";
	$delete_property_key_sql = mysql_query($delete_property_key_str);
	if(mysql_num_rows($delete_property_key_sql) > 0 ){
		$qry = ['status' => 'deleted'];
	}
}

if($agency_addresses_id){
	$check_property_keys_str = "SELECT `id` FROM `property_keys` WHERE `property_id`={$property_id}";
	$check_property_keys_sql = mysql_query($check_property_keys_str);
	if(mysql_num_rows($check_property_keys_sql) > 0 ){
		$sql_str = "UPDATE property_keys SET `agency_addresses_id`='{$agency_addresses_id}' WHERE property_id='{$property_id}'";
		mysql_query($sql_str);
	} else {
		$sql_str = "
		INSERT INTO 
			property_keys(
				`property_id`,
				`agency_addresses_id`
			)
			VALUES(
				{$property_id},
				{$agency_addresses_id}
			)
		";
		mysql_query($sql_str);
	}
}


echo json_encode($qry);
?>