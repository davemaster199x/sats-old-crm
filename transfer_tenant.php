<?php 
// include('inc/init_for_ajax.php');
// $property_id = 25;
$sqlCheckMarking = mysql_query("SELECT * FROM `property` WHERE `move_tenants_sync` = 0 AND `property_id`=".$property_id);
if(mysql_num_rows($sqlCheckMarking) == 1){


		$rsTenantsAPI = mysql_fetch_array($sqlCheckMarking);
		$tenantsAPI = [];
		$tenantsAPI[] = array(
			'tenant_firstname' 	=> $rsTenantsAPI['tenant_firstname1'],
			'tenant_lastname' 	=> $rsTenantsAPI['tenant_lastname1'],
			'tenant_ph' 		=> $rsTenantsAPI['tenant_ph1'],
			'tenant_email' 		=> $rsTenantsAPI['tenant_email1'],
			'tenant_mob' 		=> $rsTenantsAPI['tenant_mob1']
		);

		$tenantsAPI[] = array(
			'tenant_firstname' 	=> $rsTenantsAPI['tenant_firstname2'],
			'tenant_lastname' 	=> $rsTenantsAPI['tenant_lastname2'],
			'tenant_ph' 		=> $rsTenantsAPI['tenant_ph2'],
			'tenant_email' 		=> $rsTenantsAPI['tenant_email2'],
			'tenant_mob' 		=> $rsTenantsAPI['tenant_mob2']
		);

		$tenantsAPI[] = array(
			'tenant_firstname' 	=> $rsTenantsAPI['tenant_firstname3'],
			'tenant_lastname' 	=> $rsTenantsAPI['tenant_lastname3'],
			'tenant_ph' 		=> $rsTenantsAPI['tenant_ph3'],
			'tenant_email' 		=> $rsTenantsAPI['tenant_email3'],
			'tenant_mob' 		=> $rsTenantsAPI['tenant_mob3']
		);

		$tenantsAPI[] = array(
			'tenant_firstname' 	=> $rsTenantsAPI['tenant_firstname4'],
			'tenant_lastname' 	=> $rsTenantsAPI['tenant_lastname4'],
			'tenant_ph' 		=> $rsTenantsAPI['tenant_ph4'],
			'tenant_email' 		=> $rsTenantsAPI['tenant_email4'],
			'tenant_mob' 		=> $rsTenantsAPI['tenant_mob4']
		);

		// echo '<pre>'.print_r($tenant, TRUE).'</pre>';
		$ct = 0;
		foreach($tenantsAPI as $tenantAPI) {

			if(!empty($tenantAPI['tenant_firstname']) OR 
						!empty($tenantAPI['tenant_lastname']) OR 
						!empty($tenantAPI['tenant_ph']) OR 
						!empty($tenantAPI['tenant_email']) OR 
						!empty($tenantAPI['tenant_mob'])) {

				$ct++;

				// insert old tenant to new tenant
				mysql_query("INSERT INTO `property_tenants`(`property_id`,`tenant_firstname`,`tenant_lastname`,`tenant_mobile`,`tenant_landline`,`tenant_email`)
				VALUES('".$property_id."','".$tenantAPI['tenant_firstname']."','".$tenantAPI['tenant_lastname']."','".$tenantAPI['tenant_mob']."','".$tenantAPI['tenant_ph']."','".$tenantAPI['tenant_email']."')");

				// marked sync this property
				mysql_query("UPDATE `property` SET `move_tenants_sync` = 1 WHERE `property_id`=".$property_id);
			
			}
		}


		//

		$getOldBookedWith = mysql_query("SELECT `booked_with` FROM `jobs` WHERE `id`=".$job_id);
		$rsOldBookedWith = mysql_fetch_array($getOldBookedWith);

		if($rsOldBookedWith['booked_with'] != ""){
			$searchTenant = mysql_query("SELECT * FROM `property_tenants` WHERE `property_id` = ".$property_id." AND TRIM(LOWER(`tenant_firstname`))='".trim(strtolower($rsOldBookedWith['booked_with']))."'");
			if(mysql_num_rows($searchTenant) == 1){
				$rsST = mysql_fetch_array($searchTenant);
				mysql_query("UPDATE `jobs` SET `booked_with_new`=".$rsST['property_tenant_id']." WHERE `id`=".$job_id);
			}

		}


}
