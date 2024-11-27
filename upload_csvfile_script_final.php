<?

$title = "Import Properties Final";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

?>

<div id="mainContent">
  
  <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/import_properties.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	


    <div class="jalign_left">	
	
		<div style="margin: 40px 0 0;text-align: left;">
	
		<?php
		
		$agency = $_POST['agency'];
		$upload_type = $_POST['upload_type'];
		$csv_array = $_SESSION['imported_property'];
		$prop_insert = 0;

		/*
		echo "Agency: {$agency}<br />";
		echo "Upload type: {$upload_type}<br /><br />";
		*/

		/*					
		// print
		echo "<pre>";
		print_r($csv_array);
		echo "</pre>";
		*/

		// loop through property array
		foreach($csv_array as $index=>$csv){


				
				$street_num = str_replace("*","/",mysql_real_escape_string($csv['street_num']));
				
				// get lat and lng for mapping
				$address_str = "{$csv['street_num']} {$csv['street_name']} {$csv['suburb']} {$csv['state']} {$csv['postcode']}";
				$coordinate = getGoogleMapCoordinates($address_str);
				
				
				//echo "<br />";
				$prop_sql = "
					INSERT INTO 
						`property`(
							`agency_id`,
							
							`address_1`,
							`address_2`,
							`address_3`,
							`state`,
							`postcode`,
							
							`landlord_firstname`,
							`landlord_lastname`,
							`landlord_email`,
							
							`key_number`,
							`comments`,
							
							`lat`,
							`lng`
						)
						VALUES(
							'".mysql_real_escape_string($agency)."',
							
							'".$street_num."',
							'".mysql_real_escape_string($csv['street_name'])."',
							'".mysql_real_escape_string($csv['suburb'])."',
							'".mysql_real_escape_string($csv['state'])."',
							'".mysql_real_escape_string($csv['postcode'])."',
							
							'".mysql_real_escape_string($csv['landlord_fname'])."',
							'".mysql_real_escape_string($csv['landlord_lname'])."',
							'".mysql_real_escape_string($csv['landlord_email'])."',
							
							'".mysql_real_escape_string($csv['key_number'])."',
							'".mysql_real_escape_string($csv['comments'])."',
							
							'{$coordinate['lat']}',
							'{$coordinate['lng']}'
						)
				";
				
				//echo "<br />";
				mysql_query($prop_sql);
				
				$prop_insert++;
				
				
				// get property id							
				$prop_id = mysql_insert_id();
				$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
				
				
				// insert new tenants, limit to 4
				$num_tenants = 4;
				for( $pt_i=0; $pt_i<$num_tenants; $pt_i++ ){ 
				
					$fname = $csv['tenant_fname'.$pt_i];
					$lname = $csv['tenant_lname'.$pt_i];
					$mobile = $csv['tenant_mobile'.$pt_i];
					$landline = $csv['tenant_phone'.$pt_i];
					$email = $csv['tenant_email'.$pt_i];
					$not_empty_tenant = false;
				
				
					if( $fname != '' || $lname != '' || $mobile != '' || $landline != '' || $email != '' ){
						$not_empty_tenant = true;
					}
				
					// do not insert empty tenants
					if( $prop_id > 0 && $not_empty_tenant == true ){
						mysql_query("
							INSERT INTO
							`property_tenants` (
								`property_id`,
								`tenant_firstname`,
								`tenant_lastname`,
								`tenant_mobile`,
								`tenant_landline`,
								`tenant_email`
							)
							VALUES (
								{$prop_id},
								'".mysql_real_escape_string($fname)."',
								'".mysql_real_escape_string($lname)."',
								'".mysql_real_escape_string($mobile)."',
								'".mysql_real_escape_string($landline)."',
								'".mysql_real_escape_string($email)."'
							)
						");
					}
					
				}
				
				
				// insert property log
				mysql_query("
					INSERT INTO 
					`property_event_log` (
						`property_id`, 
						`staff_id`, 
						`event_type`, 
						`event_details`, 
						`log_date`
					) 
					VALUES (
						".$prop_id.", 
						".$staff_id.", 
						'Property Uploaded', 
						'Property Uploaded', 
						'".date('Y-m-d H:i:s')."'
					)
				");
				
				
				$success = 1;
				
				// get agency sevices
				$as_sql = mysql_query("
					SELECT *
					FROM `agency_services`
					WHERE `agency_id` = ".mysql_real_escape_string($agency)."
				");
				
				// loop through agency services
				while($as = mysql_fetch_array($as_sql)){	
					
					// CREATE JOB temp 	
					// insert property services
					
					switch($upload_type){
						case 'nr':
							$jserv_status = 2;
						break;
						case 'sats':
							// alarm job type = smoke
							if($as['service_id']==2){
								$jserv_status = 1;
							}else{
								$jserv_status = 2;
							}
						break;
						case 'mixed':
							$jserv_status = $_POST["prop{$index}_serv{$as['service_id']}"];
						break;
					}
					
					$ps_sql = "
						INSERT INTO 
						`property_services` (
							`property_id`,
							`alarm_job_type_id`,
							`service`,
							`price`,
							`status_changed`
						)
						VALUES (
							'".mysql_real_escape_string($prop_id)."',
							'".mysql_real_escape_string($as['service_id'])."',
							'".$jserv_status."',
							'".mysql_real_escape_string($as['price'])."',
							'".date('Y-m-d H:i:s')."'
						)
					";
					
					//echo "<br />";
					mysql_query($ps_sql);
					
					// if SATS to service
					if($jserv_status==1){
						
						// techsheet
						mysql_query("
							INSERT INTO 
							`property_propertytype` (
								`property_id`,
								`alarm_job_type_id`
							)
							VALUES (
								'".mysql_real_escape_string($prop_id)."',
								'".mysql_real_escape_string($as['service_id'])."'
							)
						");
						
						
						// get Franchise Group
						$agen_sql = mysql_query("
							SELECT `franchise_groups_id`
							FROM `agency`
							WHERE `agency_id` = ".mysql_real_escape_string($agency)."
						");
						$agen = mysql_fetch_array($agen_sql);
						
						$fg_id = $agen['franchise_groups_id'];
						// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
						if( isDHAagenciesV2($fg_id)==true || agencyHasMaintenanceProgram($agency)==true   ){
							$dha_need_processing = 1;
						}
						
						
					
						// jobs
						mysql_query("
							INSERT INTO 
							jobs (
								`job_type`, 
								`property_id`, 
								`status`,
								`service`,
								`job_price`,
								`dha_need_processing`
							) 
							VALUES (
								'Yearly Maintenance', 
								'{$prop_id}', 
								'Send Letters',
								'".mysql_real_escape_string($as['service_id'])."',
								'".mysql_real_escape_string($as['price'])."',
								'{$dha_need_processing}'
							)
						");
						
						// job id
						$job_id = mysql_insert_id();
						
						// AUTO - UPDATE INVOICE DETAILS
						$crm->updateInvoiceDetails($job_id);
							
						
						// if bundle, insert bundle
						// get alarm job type
						$ajt_sql = mysql_query("
							SELECT *
							FROM `alarm_job_type`
							WHERE `id` = {$as['service_id']}
						");
						$ajt = mysql_fetch_array($ajt_sql);


						// if bundle
						if($ajt['bundle']==1){
							$b_ids = explode(",",trim($ajt['bundle_ids']));
							// insert bundles
							foreach($b_ids as $val2){
								mysql_query("
									INSERT INTO
									`bundle_services`(
										`job_id`,
										`alarm_job_type_id`
									)
									VALUES(
										{$job_id},
										{$val2}
									)
								");
							}	
						}

					}
					
					
					
					// popupate alarms for smoke alarms services
					if($as['service_id']==2){
					
						// get agency alarms
						$aa_sql = mysql_query("
							SELECT *
							FROM `agency_alarms`
							WHERE `agency_id` = '".mysql_real_escape_string($agency)."'
						");
						
						// insert property alarms
						if(mysql_num_rows($aa_sql)>0){					
							while($aa = mysql_fetch_array($aa_sql)){
						
								mysql_query("
									INSERT INTO
									`property_alarms` (
										`property_id`,
										`alarm_pwr_id`,
										`price`
									)
									VALUES (
										'{$prop_id}',
										'{$aa['alarm_pwr_id']}',
										'{$aa['price']}'
									)
								");
								
							}						
						}					
						
					}
					
					
				}
				
							
			
		}
		
		if($prop_insert==count($csv_array)){
			echo '<div class="success">Property Import Successful</div>';
		}

		unset($_SESSION['imported_property']); 
		
		
		
		?>
		
		
		<a href="/import_properties.php">
			<button type="button" class="submitbtnImg" style="margin-top: 24px;">BACK to Import Properties Page</button>
		</a>
		
		</div>
					
	</div>	
   

  </div>
  
</div>

<br class="clearfloat" />
</body>
</html>