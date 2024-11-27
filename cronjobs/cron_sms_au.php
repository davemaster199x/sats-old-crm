<?php
// THIS PAGE HAS CRON, ANY UPDATES SHOULD ALSO BE DONE THERE
include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');
//include($_SERVER['DOCUMENT_ROOT'].'inc/ws_sms_class.php');

// ADJUST on CRON: START -----------
$country_id = 1; // AU
//$country_id = 2; // NZ
// SMS template: Reminder (Date)
$sms_msg_id = 5; // AU
//$sms_msg_id = 8; // NZ
// ADJUST on CRON: END --------------

$staff_id = -3; // CRON
$sms_type = 19; // SMS (Reminder)
$sent_by = $staff_id;


$cron_type_id = 12; // cron log type: Reminder SMS
$current_week = intval(date('W'));
$current_year = date('Y');

$cl_sql = mysql_query("
	SELECT * 
	FROM cron_log 
	WHERE `type_id` = '{$cron_type_id}' 
	AND `week_no` = '{$current_week}' 
	AND `year` = '{$current_year}'
	AND CAST(`started` AS DATE) = '".date('Y-m-d')."' 
	AND `country_id` = {$country_id}
");

if(mysql_num_rows($cl_sql)==0){
	
	
	$todays_day = date('D');
	$sql_date_text = '';
	if( $todays_day == 'Fri' ){ // if friday get saturday and monday
		$saturday = date("Y-m-d",strtotime("+1 day"));
		$next_monday = date("Y-m-d",strtotime("+3 day"));
		$sql_date_text = " 
		AND (
			j.`date` = '{$saturday}' 
			OR j.`date` = '{$next_monday}' 
		)
		";
	}else{ // if mon - thur, get next day
		$next_day = date("Y-m-d",strtotime("+1 day"));
		$sql_date_text = " AND j.`date` = '{$next_day}' ";
	}
	
	
	// get jobs for sms
	echo $sql_str = "
		SELECT *, j.`id` AS jid, j.`service` AS jservice, p.`address_1` AS paddress1, p.`address_2` AS paddress2, p.`address_3` AS paddress3, j.`property_id` AS jprop_id, j.status AS jstatus, j.`property_vacant`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE j.status = 'Booked' 
		{$sql_date_text}
		AND p.`deleted` = 0
		AND j.`del_job` = 0
		AND a.`status` = 'active'
		AND a.`country_id` = {$country_id}
		AND j.`door_knock` = 0
		ORDER BY p.`address_2` ASC
	";
	echo "<br /><br />";
	$sql = mysql_query($sql_str);
					
	
	if(mysql_num_rows($sql)>0){
		$i = 0;
		while($row = mysql_fetch_array($sql)){
			
			$tr_class = '';
			if( ($row['door_knock']==1 && $row['jstatus']=="Booked") || $row['key_access_required']==1 ){
				$tr_class = 'highlight_grey';
			}else{

				// get phone prefix
				$prefix = $row['phone_prefix'];
				
				
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
						
						// only sends to booked tenants
						if( $pt_row['tenant_mobile']!="" && $pt_row['tenant_firstname'] == $row['booked_with'] ){	

							// tenant mobile 
							$trim = str_replace(' ', '', trim($pt_row['tenant_mobile']));

							// reformat number
							$remove_zero = substr($trim ,1);
							$mob = $prefix.$remove_zero;

							$sms_provider = SMS_PROVIDER;
							$to = "{$mob}{$sms_provider}";
							
							// tenant name
							$tenant_name = $pt_row['tenant_firstname'];

							// get message
							echo $msg = str_replace('{name}',$tenant_name,getSingleParsedSmsMsg($row['jid'],$sms_msg_id));
							echo "<br /><br />";
							
							
							// send SMS via API
							$ws_sms = new WS_SMS($country_id,$msg,$mob);	
							$sms_res = $ws_sms->sendSMS();
							$ws_sms->captureSMSdata($sms_res,$row['jid'],$msg,$mob,$sent_by,$sms_type);
							print_r($sms_res);
							echo "<br /><br />";
							
							// insert logs
							echo $job_logs = "
								INSERT INTO 
								`job_log` (
									`contact_type`,
									`eventdate`,
									`comments`,
									`job_id`, 
									`staff_id`,
									`eventtime`,
									`auto_process`
								) 
								VALUES (
									'SMS',
									'" . date('Y-m-d') . "',
									'Reminder SMS <strong>\"".mysql_real_escape_string(trim($msg))."\"</strong>', 
									'{$row['jid']}',
									'{$staff_id}',
									'".date("H:i")."',
									1
								)
							";
							mysql_query($job_logs);			
							echo "<br /><br />";
							// count sms sent
							$sms_count++;
							
						
						}
						
					}

				}else{ // OLD TENANTS

					$num_tenants = getCurrentMaxTenants();
					for($pt_i=1;$pt_i<=$num_tenants;$pt_i++){
					
						// only sends to booked tenants
						if( $row['tenant_mob'.$pt_i]!="" && $row['tenant_firstname'.$pt_i] == $row['booked_with'] ){	

							// tenant mobile 
							$trim = str_replace(' ', '', trim($row['tenant_mob'.$pt_i]));

							// reformat number
							$remove_zero = substr($trim ,1);
							$mob = $prefix.$remove_zero;

							$sms_provider = SMS_PROVIDER;
							$to = "{$mob}{$sms_provider}";
							
							// tenant name
							$tenant_name = $row['tenant_firstname'.$pt_i];

							// get message
							echo $msg = str_replace('{name}',$tenant_name,getSingleParsedSmsMsg($row['jid'],$sms_msg_id));
							echo "<br /><br />";
							
							
							// send SMS via API
							$ws_sms = new WS_SMS($country_id,$msg,$mob);	
							$sms_res = $ws_sms->sendSMS();
							$ws_sms->captureSMSdata($sms_res,$row['jid'],$msg,$mob,$sent_by,$sms_type);
							print_r($sms_res);
							echo "<br /><br />";
							
							// insert logs
							echo $job_logs = "
								INSERT INTO 
								`job_log` (
									`contact_type`,
									`eventdate`,
									`comments`,
									`job_id`, 
									`staff_id`,
									`eventtime`,
									`auto_process`
								) 
								VALUES (
									'SMS',
									'" . date('Y-m-d') . "',
									'Reminder SMS <strong>\"".mysql_real_escape_string(trim($msg))."\"</strong>', 
									'{$row['jid']}',
									'{$staff_id}',
									'".date("H:i")."',
									1
								)
							";
							mysql_query($job_logs);			
							echo "<br /><br />";
							// count sms sent
							$sms_count++;
							
						
						}
						
					}
					
				}
				

				
			}

		$i++;
		}
	}



	// insert cron logs
	echo $cron_log_sql = "INSERT INTO cron_log (type_id, week_no, year, started, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
	echo "<br /><br />";
	mysql_query($cron_log_sql);
	
}



?>