<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$fn = "";

$row;

//define("d", date("d"));

//define("m", date("m"));

//define("y", date("Y"));

  

$agency_id = mysql_real_escape_string($_GET['agency_id']);
$p_deleted = mysql_real_escape_string($_GET['p_deleted']);

   // (1) Open the database connection

   





// send headers for download

$filename = "Properties_".$fn."_".date("d")."-".date("m")."-".date("y").".csv";

//$filename = "Properties_".$fn."_".d."-".m."-".y.".txt";



header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");



// $Query = "SELECT p.address_1, p.address_2, p.address_3, p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, count(j.id) FROM property p, agency a , jobs j WHERE (a.agency_id=$agency_id AND p.agency_id = a.agency_id AND (j.property_id = p.property_id) GROUP BY p.property_id;";

// property deleted filter
$deleted_filter = null;
if( is_numeric($p_deleted) ){
	$deleted_filter = "AND p.deleted = {$p_deleted}";
}


// edited the query and tidy up a bit
$Query = "
SELECT 
	p.address_1, 
	p.address_2, 
	p.address_3, 
	p.landlord_firstname, 
	p.landlord_lastname, 
	p.service, 
	p.`property_id`,
	p.`compass_index_num`,
	p.`pm_id_new`, 
	p.`key_number`,
	p.`deleted`,
	p.`nlm_timestamp`,
	
	aua.`fname` AS pm_fname, 
	aua.`lname` AS pm_lname,
	aua.`email` AS pm_email,

	a.franchise_groups_id

	FROM `property` AS p 
	LEFT JOIN `agency_user_accounts` AS aua ON p.`pm_id_new` = aua.`agency_user_account_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 	
	WHERE a.agency_id = {$agency_id}	
	AND a.`country_id` = {$_SESSION['country_default']}
	{$deleted_filter}
";	



   // (2) Run the query.	

   $result = mysql_query ($Query, $connection);

   if (mysql_num_rows($result) == 0) {

   	echo "<br><br>No Properties to display<br>\n";

	}

	// get services
	$as_sql = mysql_query("
		SELECT *
		FROM `agency_services` AS as2
		LEFT JOIN `alarm_job_type` AS ajt ON as2.`service_id` = ajt.`id` 
		WHERE as2.`agency_id` ={$agency_id}
	");
	$serv_str = "";
	while($as = mysql_fetch_array($as_sql)){
		$serv_str .= ",{$as['type']},last YM";
	}
	
	// Compass Housing QLD 
	## update by Gherx > if franchise_groups_id = Compass Housing
	$agency_query = mysql_query("
		SELECT *
		FROM `agency`
		WHERE `agency_id` ={$agency_id}
	");
	$add_export_header = '';
	$row1 = mysql_fetch_array($agency_query);
	if($row1['franchise_groups_id']==39){
		$add_export_header = ',Compass Index Number';
	}
	
	/*if( $agency_id == 6502 ){
		
		$add_export_header = ',Compass Index Number';
		
	}*/
	


	echo "Address,Suburb,1st Tenant Name,1st Tenant Ph,1st Tenant Mobile,2nd Tenant Name,2nd Tenant Ph,2nd Tenant Mobile,3rd Tenant Name,3rd Tenant Ph,3rd Tenant Mobile,4th Tenant Name,4th Tenant Ph,4th Tenant Mobile{$serv_str},Last Attended,Property Manager,Land Lord,Key Number,Status,NLM Date{$add_export_header}\n";


   while ( $row = mysql_fetch_array($result) ){

		$service_str = "";
		
		$as_sql = mysql_query("
			SELECT *
			FROM `agency_services` AS as2
			LEFT JOIN `alarm_job_type` AS ajt ON as2.`service_id` = ajt.`id` 
			WHERE as2.`agency_id` ={$agency_id}
		");
		$serv_str = "";
		while($as = mysql_fetch_array($as_sql)){
			
			// service
			$service = '';
			$ps_sql = mysql_query("
				SELECT *
				FROM `property_services` 
				WHERE `property_id` = {$row['property_id']}
				AND `alarm_job_type_id` = {$as['service_id']}
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

				
			$lym_sql = mysql_query("
				SELECT `date`
				FROM `jobs`
				WHERE `property_id` ={$row['property_id']}
				AND `status` = 'Completed'
				AND `job_type` = 'Yearly Maintenance'
				AND `service` = {$as['service_id']}
				ORDER BY `date` DESC
				LIMIT 0 , 1
			");	
			$lym = mysql_fetch_array($lym_sql);
			
			$lym_date = ($lym['date']!=""&&$lym['date']!="0000-00-00")?date("Y-m-d",strtotime($lym['date'])):'----';
			
			$service_str .= ",".trim($service) . ",{$lym_date}";	

			//last attended (by Gherx)
			$last_attended_query = mysql_query("
				SELECT `date`
				FROM `jobs`
				WHERE `property_id` ={$row['property_id']}
				AND ( `status` = 'Merged Certificates' OR `status` = 'Completed' )
				AND `assigned_tech` != 1
				AND `assigned_tech` != 2
				AND `assigned_tech` != 'NULL'
				AND `del_job` = 0
				ORDER BY `date` DESC
				LIMIT 0 , 1
			");	
			$last_attended_row = mysql_fetch_array($last_attended_query);
			$last_attended = $last_attended_row['date'];

		}
			
			
			
			
		$landlord = "{$row['landlord_firstname']} {$row['landlord_lastname']}";		

		$key_number = "{$row['key_number']}";


		// new tenants
		$pt_params = array( 
			'property_id' => $row['property_id'],
			'active' => 1,
			'paginate' => array(
				'offset' => 0,
				'limit' => 4
			)
		 );
		$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
		
		$tenant_name_arr = [];
		$tenant_landline_arr = [];
		$tenant_mobile_arr = [];
		
		while( $pt_row = mysql_fetch_array($pt_sql) ){
			
			$tenant_name_arr[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
			$tenant_landline_arr[] =  trim($pt_row['tenant_landline']);
			$tenant_mobile_arr[] =  trim($pt_row['tenant_mobile']);
			
		}
		
		
		// only display 4 tenants, to have consistent number of columns
		$new_tenants_str = '';
		$num_tenants = 4;
		for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ 
			$new_tenants_str .= ( $tenant_name_arr[$pt_i] != '' )?",\"{$tenant_name_arr[$pt_i]}\"":',';
			$new_tenants_str .= ( $tenant_landline_arr[$pt_i] != '' )?",\"{$tenant_landline_arr[$pt_i]}\"":',';
			$new_tenants_str .= ( $tenant_mobile_arr[$pt_i] != '' )?",\"{$tenant_mobile_arr[$pt_i]}\"":',';
		}
		
		
		// Compass Housing QLD 
		$add_export_row = '';
		/*if( $agency_id == 6502 ){
			
			$add_export_row = ",\"{$row['compass_index_num']}\"";
			
		}*/
			if($row1['franchise_groups_id']==39){
				$add_export_row = ",\"{$row['compass_index_num']}\"";
			}
		
 
		$pm_name = "{$row['pm_fname']} {$row['pm_lname']}";

		$p_deleted = ( $row['deleted'] == 1 )?'Inactive':'Active';
		$nlm_date = $crm->isDateNotEmpty($row['nlm_timestamp'])?date("d/m/Y",strtotime($row['nlm_timestamp'])):null;

		echo "\"{$row['address_1']} {$row['address_2']}\",\"{$row['address_3']}\"{$new_tenants_str}".trim($service_str).",\"{$last_attended}\",\"{$pm_name}\",\"{$landlord}\",\"{$key_number}\",\"{$p_deleted}\",\"{$nlm_date}\"{$add_export_row}\n";



   }


   



?>



