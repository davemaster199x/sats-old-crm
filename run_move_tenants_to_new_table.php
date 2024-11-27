<?php

include('inc/init_for_ajax.php');

$run_query = 0;

if( $run_query == 1 ){
	
	$num_tenants = getCurrentMaxTenants();

	echo $clear_bwn_str = "
		UPDATE `jobs` 
		SET `booked_with_new` = NULL
		WHERE `booked_with_new` != ''
	";
	echo "<br /><br /><br />";
	mysql_query($clear_bwn_str);

	echo $clear_p_sync_str = "
		UPDATE `property` 
		SET `move_tenants_sync` = 0
		WHERE `move_tenants_sync` = 1
	";
	echo "<br /><br /><br />";
	mysql_query($clear_p_sync_str);


	// old tenants max number
	echo "Number of Tenants(old): {$num_tenants}";

	echo "<br /><br /><br />";

	// main query, get property that is not synced
	echo $prop_sql_str = "
		SELECT 
			p.`property_id`,
			
			p.`tenant_firstname1`, 
			p.`tenant_lastname1`, 
			p.`tenant_mob1`,
			p.`tenant_ph1`, 		
			p.`tenant_email1`,
			
			p.`tenant_firstname2`, 
			p.`tenant_lastname2`, 
			p.`tenant_mob2`,
			p.`tenant_ph2`, 		
			p.`tenant_email2`,
			
			p.`tenant_firstname3`, 
			p.`tenant_lastname3`, 
			p.`tenant_mob3`,
			p.`tenant_ph3`, 		
			p.`tenant_email3`,
			
			p.`tenant_firstname4`, 
			p.`tenant_lastname4`, 
			p.`tenant_mob4`,
			p.`tenant_ph4`, 		
			p.`tenant_email4`
		FROM `property` AS p
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE p.`deleted` = 0
		AND p.move_tenants_sync = 0
		AND a.`status` = 'active'	
	";
	echo "<br /><br /><br />";


	// loop though properties
	$prop_sql = mysql_query($prop_sql_str);
	while( $prop = mysql_fetch_array($prop_sql) ){
		
		$property_id = $prop['property_id'];
		$isTenantMoved = false;
		
		echo "
		---------------------------------------<br />
		Property ID: {$property_id}<br /><br />";
		
		// loop through all tenants
		for( $i=1; $i<=$num_tenants; $i++ ){
			
			
			$tenant_firstname = $prop['tenant_firstname'.$i];
			$tenant_lastname = $prop['tenant_lastname'.$i];
			$tenant_mob = $prop['tenant_mob'.$i];
			$tenant_ph = $prop['tenant_ph'.$i];
			$tenant_email = $prop['tenant_email'.$i];
			
			if( 
				$tenant_firstname != '' || 
				$tenant_lastname != '' ||
				$tenant_mob != '' ||
				$tenant_ph != '' ||
				$tenant_email != ''
			){
				
				// insert to new table
				echo $new_tnt_sql_str = "
					INSERT INTO
					`property_tenants`(
						`property_id`,
						`tenant_firstname`,
						`tenant_lastname`,
						`tenant_mobile`,
						`tenant_landline`,
						`tenant_email`
					)
					VALUES(
						{$property_id},
						'{$tenant_firstname}',
						'{$tenant_lastname}',
						'{$tenant_mobile}',
						'{$tenant_landline}',
						'{$tenant_email}'	
					)
				";
				echo "<br />";
				
				mysql_query($new_tnt_sql_str);
				$isTenantMoved =  true;

				
			}	
			
			
		}
		
		echo "<br />";

		if( $isTenantMoved == true ){			
			
			// mark it as done copying old tenants to new tenants
			echo $prop_sql_str = "
				UPDATE `property`
				SET `move_tenants_sync` = 1
				WHERE `property_id` = {$property_id}
			";
			mysql_query($prop_sql_str);
			
			
		}else{
			echo 'No tenants found<br />';
		}		
		
		
		echo "<br /><br />";
		
		
		
		// find jobs with booked with, store to new field
		echo $job_str = "
			SELECT * 
			FROM `jobs` 
			WHERE `booked_with` != ''
			AND `property_id` = {$property_id}
		";
		echo "<br /><br />";
		$jobs_sql = mysql_query($job_str);
		if( mysql_num_rows($jobs_sql) > 0 ){
			while( $job = mysql_fetch_array($jobs_sql) ){
				$job_id = $job['id'];
				echo $tnt_str = "
					SELECT * 
					FROM `property_tenants` 
					WHERE `property_id` = ".$property_id." 
					AND TRIM(LOWER(`tenant_firstname`)) = '".trim(strtolower($job['booked_with']))."'
				";
				echo "<br /><br />";
				$tnt_sql = mysql_query($tnt_str);
				if( mysql_num_rows($tnt_sql) > 0 ){
					$tnt = mysql_fetch_array($tnt_sql);
					echo $update_job_str = "
						UPDATE `jobs`
						SET `booked_with_new` = {$tnt['property_tenant_id']}
						WHERE `id` = {$job_id}
						AND `property_id` = {$property_id}
					";
					echo "<br /><br />";
					mysql_query($update_job_str);
				}
				
			}
		}
		
		
		
		
		echo "<br /><br /><br />";
		
		
	}
	
}else{
	echo "Run Query is turned off";
}



?>