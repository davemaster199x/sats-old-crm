<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$country_id = 2;
$today = date('Y-m-d');

echo $sql_str = "
	SELECT *, j.`id` AS jid 
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
	WHERE j.`job_reason_id` = 1
	AND j.`status` = 'Pre Completion'
	AND (
		CAST( j.`sms_sent_no_show` AS Date ) != '{$today}' OR
		j.`sms_sent_no_show` IS NULL
	)
	AND j.`del_job` = 0
	AND p.deleted =0
	AND a.`status` = 'active'
	AND a.`country_id` = {$country_id}
";
$sql = mysql_query($sql_str);
echo "<br /><br />";

if( mysql_num_rows($sql)>0 ){
	
	while( $row = mysql_fetch_array($sql) ){
	
		$job_id = $row['jid'];
		
		
		// get phone prefix
		$prefix = $row['phone_prefix'];
		$sms_provider = SMS_PROVIDER;
		//$num_tenants = getCurrentMaxTenants();
		$tent_full_mob_num = '';
		$tenant_mob_arr = '';
		$ten_mob = '';
		
		
		// new tenants switch
		//$new_tenants = 0;
		$new_tenants = NEW_TENANTS;

		if( $new_tenants == 1 ){ // NEW TENANTS

			$pt_params = array( 
				'property_id' => $row['property_id'],
				'active' => 1
			 );
			$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
			
			while( $pt_row = mysql_fetch_array($pt_sql) ){
				
				// send only to booked with tenants
				if( $pt_row["tenant_mobile"]!="" && $pt_row['tenant_firstname'] == $row['booked_with'] ){
					
					$sms_sent_to_tenant = "{$pt_row['tenant_firstname']}";
					
					// tenant mobile 
					$ten_mob = trim($pt_row["tenant_mobile"]);
					if($ten_mob!=''){
						$trimmed_mob = str_replace(' ', '', $ten_mob);
						// reformat number
						$remove_zero = substr($trimmed_mob,1);
						$mob = $prefix.$remove_zero;

						$tenant_mob_arr = "{$mob}{$sms_provider}";
						$tent_full_mob_num = $mob;
					}
					
				}
				
				
			}		

		}else{ // OLD TENANTS

			$num_tenant = getCurrentMaxTenants();
			for( $i=1; $i<=$num_tenants; $i++ ){
			
				// send only to booked with tenants
				if( $row["tenant_mob{$i}"][$val]!="" && $row['tenant_firstname'.$i]==$row['booked_with'] ){
					
					$sms_sent_to_tenant = "{$row['tenant_firstname'.$i]}";
					
					// tenant mobile 
					$ten_mob = trim($row["tenant_mob{$i}"]);
					if($ten_mob!=''){
						$trimmed_mob = str_replace(' ', '', $ten_mob);
						// reformat number
						$remove_zero = substr($trimmed_mob,1);
						$mob = $prefix.$remove_zero;

						$tenant_mob_arr = "{$mob}{$sms_provider}";
						$tent_full_mob_num = $mob;
					}
					
				}
				
			}
			
		}
		
		
		// private FG
		if( $crm->getAgencyPrivateFranchiseGroups($row['franchise_groups_id']) == true ){ 
			$landlord_txt = 'your landlord';
		}else{
			$landlord_txt = 'your agency';
		}
		
		// No-Show			
		$sms_type = 4;
		$sent_by = -3; // CRON
		$sms_temp_params = array(
			'sms_type' => $sms_type,
			'tenant_number' => $row['tenant_number'],
			'landlord_txt' => $landlord_txt
		);
		echo $no_show_sms_temp = $crm->getSMStemplate($sms_temp_params);
		echo "<br /><br />";
		
		
		// SEND SMS

		if( $tent_full_mob_num != '' ){
			
			echo $tent_full_mob_num;
			echo "<br />";
			
			
			// send SMS via API
			$ws_sms = new WS_SMS($country_id,$no_show_sms_temp,$tent_full_mob_num);	
			$sms_res = $ws_sms->sendSMS();
			$ws_sms->captureSMSdata($sms_res,$job_id,$no_show_sms_temp,$tent_full_mob_num,$sent_by,$sms_type);
			
	
			
			// mark job
			echo "<br /><br />";
			echo $update_job_sql_str = "
				UPDATE jobs
					SET 
						`sms_sent_no_show` = '".date("Y-m-d H:i:s")."'
				WHERE `id` = {$job_id}
			";
			mysql_query($update_job_sql_str);
			

			
			// insert logs
			echo "<br /><br />";
			$sms_message3 = mysql_real_escape_string($no_show_sms_temp);
			
			echo $jl_sql_str = "
				INSERT INTO 
				`job_log` (
					`contact_type`,
					`eventdate`,
					`comments`,
					`job_id`, 
					`eventtime`,
					`auto_process`
				) 
				VALUES (
					'SMS sent',
					'" . date('Y-m-d') . "',
					'SMS to {$sms_sent_to_tenant} <strong>\"{$sms_message3}\"</strong>',
					'{$job_id}',
					'".date("H:i")."',
					1
				)
			";
			mysql_query($jl_sql_str);
			
			
		}
		
		
		
	}


	
}

?>


