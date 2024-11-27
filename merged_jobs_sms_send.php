<?php
include('inc/init.php');
//include('inc/ws_sms_class.php');

// Initiate job class
$jc = new Job_Class();
$mj_sql = $jc->getJobs('','','','','','Merged Certificates','','','','','');

$sms_provider = SMS_PROVIDER;
$staff_id =  $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];
$sent_by = $staff_id;
$sms_type = 18; // SMS (Thank You)

//$sms_count = 0;
while( $row = mysql_fetch_array($mj_sql) ){
	
	if( date("Y-m-d",strtotime($row['sms_sent_merge'])) != date("Y-m-d") ){
		
		// get phone prefix
		$p_sql = mysql_query("
			SELECT *
			FROM `property` AS p
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
			WHERE p.`property_id` ={$row['property_id']}
			AND a.`country_id` = {$_SESSION['country_default']}
		");
		$p = mysql_fetch_array($p_sql);

		// get phone prefix
		$prefix = $p['phone_prefix'];	
			
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
				
				// loop through tenants, send sms only on tenants that are booked with
				if( $pt_row['tenant_mobile'] != "" && $pt_row['tenant_firstname'] == $row['booked_with'] ){	

					// tenant name 
					$ten_name = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";

					// tenant mobile 
					$trim = str_replace(' ', '', trim($pt_row['tenant_mobile']));

					// reformat number
					$remove_zero = substr($trim ,1);
					$mob = $prefix.$remove_zero;
					
					$to = "{$mob}{$sms_provider}";

					// get message
					$msg = 'Smoke Alarm Testing Services would like to thank you for allowing us to service your property today. We strive to provide the best service we can and welcome your feedback about our service.';
					
					
					// send SMS via API
					$ws_sms = new WS_SMS($country_id,$msg,$mob);	
					$sms_res = $ws_sms->sendSMS();
					$ws_sms->captureSMSdata($sms_res,$row['jid'],$msg,$mob,$sent_by,$sms_type);
					
					// insert logs
					mysql_query("
						INSERT INTO 
						`job_log` (
							`contact_type`,
							`eventdate`,
							`comments`,
							`job_id`, 
							`staff_id`,
							`eventtime`
						) 
						VALUES (
							'TY SMS Sent',
							'" . date('Y-m-d') . "',
							'SMS to {$ten_name} <strong>\"{$msg}\"</strong>', 
							'{$row['jid']}',
							'{$staff_id}',
							'".date("H:i")."'
						)
					");
					
					// update sms sent
					mysql_query("
						UPDATE `jobs`
						SET `sms_sent_merge` = '".date("Y-m-d H:i:s")."'
						WHERE `id` = {$row['jid']}
					");


				}
				
			}

		}else{ // OLD TENANTS

			
			$num_tenants = getCurrentMaxTenants();
			for( $i=1; $i<=$num_tenants; $i++ ){
			
				// loop through tenants, send sms only on tenants that are booked with
				if( $row['tenant_mob'.$i]!="" && $row['tenant_firstname'.$i]==$row['booked_with'] ){	

					// tenant name 
					$ten_name = "{$p['tenant_firstname'.$i]} {$p['tenant_lastname'.$i]}";

					// tenant mobile 
					$trim = str_replace(' ', '', trim($row['tenant_mob'.$i]));

					// reformat number
					$remove_zero = substr($trim ,1);
					$mob = $prefix.$remove_zero;
					
					$to = "{$mob}{$sms_provider}";

					// get message
					$msg = 'Smoke Alarm Testing Services would like to thank you for allowing us to service your property today. We strive to provide the best service we can and welcome your feedback about our service.';
					

					//echo $row['tenant_firstname'.$i]." ".$to." ".$msg ."<br />";
					

					// send SMS via API
					$ws_sms = new WS_SMS($country_id,$msg,$mob);	
					$sms_res = $ws_sms->sendSMS();
					$ws_sms->captureSMSdata($sms_res,$row['jid'],$msg,$mob,$sent_by,$sms_type);
					
					// insert logs
					mysql_query("
						INSERT INTO 
						`job_log` (
							`contact_type`,
							`eventdate`,
							`comments`,
							`job_id`, 
							`staff_id`,
							`eventtime`
						) 
						VALUES (
							'TY SMS Sent',
							'" . date('Y-m-d') . "',
							'SMS to {$ten_name} <strong>\"{$msg}\"</strong>', 
							'{$row['jid']}',
							'{$staff_id}',
							'".date("H:i")."'
						)
					");
					
					// update sms sent
					mysql_query("
						UPDATE `jobs`
						SET `sms_sent_merge` = '".date("Y-m-d H:i:s")."'
						WHERE `id` = {$row['jid']}
					");
				
				}
			}
		}		
		
	}

}

header("Location: merged_jobs.php?sms_sent=1");

?>