<?php
include('inc/init_for_ajax.php');

// loop through all properties
echo $p_sql_str = "
	SELECT 
		`property_id`,
	
		`tenant_firstname1`,
		`tenant_lastname1`,
		`tenant_ph1`,
		`tenant_mob1`,
		`tenant_email1`,
		
		`tenant_firstname2`,
		`tenant_lastname2`,
		`tenant_ph2`,
		`tenant_mob2`,
		`tenant_email2`,
		
		`tenant_firstname3`,
		`tenant_lastname3`,
		`tenant_ph3`,
		`tenant_mob3`,
		`tenant_email3`,
		
		`tenant_firstname4`,
		`tenant_lastname4`,
		`tenant_ph4`,
		`tenant_mob4`,
		`tenant_email4`
		
	FROM `property`
	WHERE `new_tenants_sync` = 0
	AND `deleted` = 0
";
echo "<br /><br />";

$p_sql =  mysql_query($p_sql_str);
$num_prop = mysql_num_rows($p_sql);
echo "Number of properties to be processed: {$num_prop}";

echo "<br /><br /><br />";
while( $p = mysql_fetch_array($p_sql) ){
	
	// copy tenants to new property table
	$num_tenants = getCurrentMaxTenants();
	for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
	
		$property_id = $p['property_id'];
		$fname = $p['tenant_firstname'.$pt_i];
		$lname = $p['tenant_lastname'.$pt_i];
		$mobile = $p['tenant_mob'.$pt_i];
		$landline = $p['tenant_ph'.$pt_i];
		$email = $p['tenant_email'.$pt_i];
		$not_empty_tenant = false;
		
		if( $fname != '' || $lname != '' || $mobile != '' || $landline != '' || $email != '' ){
			$not_empty_tenant = true;
		}
	
		// only copy non empty tenants
		if( $property_id > 0 && $not_empty_tenant == true ){
			
			echo $pt_sql_str = "
				INSERT INTO
				`property_tenants` (
					`property_id`,
					`tenant_firstname`,
					`tenant_lastname`,
					`tenant_mobile`,
					`tenant_landline`,
					`tenant_email`
				)
				VALUE (
					{$property_id},
					'{$fname}',
					'{$lname}',
					'{$mobile}',
					'{$landline}',
					'{$email}'
				)
			";
			echo "<br /><br />";
			mysql_query($pt_sql_str);
			
			
			echo $p_update_str = "
			UPDATE `property`
			SET `new_tenants_sync` = 1
			WHERE `property_id` = {$property_id}
			";
			echo "<br /><br />";
			mysql_query($p_update_str);
			
			
		}
		
	}
	
}

?>