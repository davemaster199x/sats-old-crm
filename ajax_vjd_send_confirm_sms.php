<?php

$confirm_sms = mysql_real_escape_string($_POST['confirm_sms']);
$conf_sms_serv_name = $_POST['conf_sms_serv_name'];
$conf_sms_paddress = $_POST['conf_sms_paddress'];


if($_REQUEST['confirm_sms']==1){
	
	
	// Confirm SMS
	// get booked with tenant number
		$p_sql = mysql_query("
			SELECT *		
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
			WHERE p.`property_id` ={$property_id}
			AND a.`country_id` = {$_SESSION['country_default']}
		");
		$p = mysql_fetch_array($p_sql);
		// get phone prefix
		$prefix = $p['phone_prefix'];	
		$num_tenants = getCurrentMaxTenants();
		$to_sms = '';
		for( $i=1; $i<=$num_tenants; $i++ ){
			
			// loop through tenants, send sms only on tenants that are booked with
			if( $p['tenant_mob'.$i]!="" && $p['tenant_firstname'.$i]==$booked_with ){					

				// tenant mobile 
				$trim = str_replace(' ', '', trim($p['tenant_mob'.$i]));

				// reformat number
				$remove_zero = substr($trim ,1);
				$mob = $prefix.$remove_zero;
				
				$to_sms = "{$mob}{$sms_provider}";

				

			
			}
			
		}	
		
		
		// SEND SMS		
		$sms_type_id = 16;
		$sms_temp_params = array(
			'sms_type' => $sms_type_id,
			'date' => $_POST['jobdate'],
			'time' => $timeofday,
			'serv_name' => $conf_sms_serv_name,
			'paddress' => $conf_sms_paddress,
			'ctry_ten_num' => $ctn['tenant_number']
		);
		$conf_boooking_sms_temp = $crm->getSMStemplate($sms_temp_params);
		// send SMS via API
		$ws_sms = new WS_SMS($_SESSION['country_default'],$conf_boooking_sms_temp,$mob);	
		$sms_res = $ws_sms->sendSMS();
		$ws_sms->captureSMSdata($sms_res,$job_id,$conf_boooking_sms_temp,$mob,$_SESSION['USER_DETAILS']['StaffID'],$sms_type_id);
	
		
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
				'SMS sent',
				'" . date('Y-m-d') . "',
				'SMS Sent, Details- {$conf_boooking_sms_temp}', 
				'{$job_id}',
				'{$_SESSION['USER_DETAILS']['StaffID']}',
				'".date("H:i")."'
			)
		");
	
}


?>