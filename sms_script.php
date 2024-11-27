<?php
include('inc/init.php');
//include('inc/ws_sms_class.php');

// post data
$job_chk = $_POST['job_chk'];
$job_id = $_POST['job_id'];
$prop_id = $_POST['prop_id'];
$sms_msg_id = $_POST['sms_msg_id'];
$staff_id =  $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];
$sent_by = $staff_id;
$sms_type = 19; // SMS (Reminder)

$sms_count = 0;
foreach($job_chk as $val){

	// get phone prefix
	$p_sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE j.`id` ={$job_id[$val]}
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$p = mysql_fetch_array($p_sql);

	// get phone prefix
	$prefix = $p['phone_prefix'];
	
	
	
	// new tenants switch
	//$new_tenants = 0;
	$new_tenants = NEW_TENANTS;

	if( $new_tenants == 1 ){ // NEW TENANTS
		
		$tenant_mobile_arr = $_POST['tenant_mobile'.$val];
		$tenant_firstname_arr = $_POST['tenant_firstname'.$val];
		
		
		//print_r($tenant_firstname_arr);
		//print_r($tenant_mobile_arr);
		

		foreach( $tenant_mobile_arr as $index => $tenant_mobile ){
			
			$tenant_firstname = $tenant_firstname_arr[$index];		

				
			//echo "tenant_firstname: {$tenant_firstname} - booked with: {$p['booked_with']}<br />";
			
			// only sends to booked tenants
			if( $tenant_mobile !="" && $tenant_firstname == $p['booked_with'] ){	
			

				// tenant mobile 
				$trim = str_replace(' ', '', trim($tenant_mobile));

				// reformat number
				$remove_zero = substr($trim ,1);
				$mob = $prefix.$remove_zero;

				$sms_provider = SMS_PROVIDER;
				$to = "{$mob}{$sms_provider}";
				
				// tenant name
				$tenant_name = $tenant_firstname;

				// get message
				$msg = str_replace('{name}',$tenant_name,getSingleParsedSmsMsg($job_id[$val],$sms_msg_id));
				
				
				// send SMS via API
				$ws_sms = new WS_SMS($country_id,$msg,$mob);	
				$sms_res = $ws_sms->sendSMS();
				$ws_sms->captureSMSdata($sms_res,$job_id[$val],$msg,$mob,$sent_by,$sms_type);
				
				
				
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
						'Booking Reminder SMS',
						'" . date('Y-m-d') . "',
						'SMS to {$tenant_name} <strong>\"{$msg}\"</strong>', 
						'{$job_id[$val]}',
						'{$staff_id}',
						'".date("H:i")."'
					)
				");			
				
				// count sms sent
				$sms_count++;
				
			
			}
			
			
			
		}

	}else{ // OLD TENANTS

		$num_tenants = getCurrentMaxTenants();
		for($i=1;$i<=$num_tenants;$i++){
		
			// only sends to booked tenants
			if( $_POST["tenant_mob{$i}"][$val]!="" && $p['tenant_firstname'.$i]==$p['booked_with'] ){	

				// tenant mobile 
				$trim = str_replace(' ', '', trim($_POST["tenant_mob{$i}"][$val]));

				// reformat number
				$remove_zero = substr($trim ,1);
				$mob = $prefix.$remove_zero;

				$sms_provider = SMS_PROVIDER;
				$to = "{$mob}{$sms_provider}";
				
				// tenant name
				$tenant_name = $p['tenant_firstname'.$i];

				// get message
				$msg = str_replace('{name}',$tenant_name,getSingleParsedSmsMsg($job_id[$val],$sms_msg_id));
				
				
				// send SMS via API
				$ws_sms = new WS_SMS($country_id,$msg,$mob);	
				$sms_res = $ws_sms->sendSMS();
				$ws_sms->captureSMSdata($sms_res,$job_id[$val],$msg,$mob,$sent_by,$sms_type);
				
				
				
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
						'Booking Reminder SMS',
						'" . date('Y-m-d') . "',
						'SMS to {$tenant_name} <strong>\"{$msg}\"</strong>', 
						'{$job_id[$val]}',
						'{$staff_id}',
						'".date("H:i")."'
					)
				");		
				
				// count sms sent
				$sms_count++;
				
			
			}
			
		}
		
	}
	
	

	

}

header("Location: sms.php?success=1&sms_count={$sms_count}");

?>