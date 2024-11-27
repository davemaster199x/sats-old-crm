<?php

include('inc/init.php');

$crm = new Sats_Crm_Class();

$phrase = trim($_GET['phrase']);
$agency = $_GET['agency'];

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

//$propertylist = getPropertyList2($agency, $search);

$jparams = array(
	'custom_select' => '
		p.`property_id`,
		p.`address_1` AS p_address_1,
		p.`address_2` AS p_address_2,
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,

		a.`agency_id`,
		a.`agency_name`
	',
	'country_id' => $country_id,
	'agency_id' => $agency,
	'phrase' => $phrase,
	'region_postcodes' => $filterregion,
	'p_deleted' => 0,
	'custom_sort' => 'p.`address_2` ASC, p.`address_1` ASC',
	'echo_query' => 0
);
$propertylist = $crm->getPropertyOnly($jparams);


if(sizeof($propertylist) == 0)
{
	echo "<br><br>No Properties to display<br>\n";
}
else
{
	
	// send headers for download
	$filename = "All_Properties_".$fn."_".date(d)."-".date(m)."-".date(y).".csv";
	
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	$hdr = "Address,Suburb,1st Tenant Name,1st Tenant Ph,2nd Tenant Name,2nd Tenant Ph,3rd Tenant Name,3rd Tenant Ph,4th Tenant Name,4th Tenant Ph";

	// get dynamic services
	$dserv_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `active` = 1
	");
	while($dserv = mysql_fetch_array($dserv_sql)){
		$hdr .= ",{$dserv['type']}";
	}
	$hdr .= "\n";
	
	echo $hdr;
	   
	while( $property = mysql_fetch_array($propertylist) )
	{
		
		echo trim($property['p_address_1']) . " " .  trim($property['p_address_2']) . ",";
		echo trim($property['p_address_3']) . ",";
		
		// new tenants
		$pt_params = array( 
			'property_id' => $property['property_id'],
			'active' => 1,
			'paginate' => array(
				'offset' => 0,
				'limit' => 4
			)
		 );
		$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
		
		$new_tent_name_arr = [];
		$new_tent_landline_arr = [];
		while( $pt_row = mysql_fetch_array($pt_sql) ){
			
			$new_tent_name_arr[] = trim($pt_row['tenant_firstname']) . " " .  trim($pt_row['tenant_lastname']);
			$new_tent_landline_arr[] =  trim($pt_row['tenant_landline']);
			
		}
		
		// only display 4 tenants, to have consistent number of columns
		$num_tenants = 4;
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ 
			echo '"'.$new_tent_name_arr[$pt_i].'",';
			echo '"'.$new_tent_landline_arr[$pt_i].'",';
		}
		
		
		
		
		
		
		$dserv_sql = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
		");
		
		while($dserv = mysql_fetch_array($dserv_sql)){
			
			// smoke alarms
			$service = '';
			$ps_sql = mysql_query("
				SELECT *
				FROM `property_services` 
				WHERE `property_id` = {$property['property_id']}
				AND `alarm_job_type_id` = {$dserv['id']}
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
			
			echo trim($service) . ",";
			
		}
		
		
		
		
		echo "\n";
		
		#echo "$row[0] $row[1],$row[2],$row[3] $row[4],$row[5],$row[6] $row[7],$row[8]\n";
	}
}
?>

