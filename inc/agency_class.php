<?php

class Agency_Class{

		
	public function get_regions(){
		return mysql_query("
			SELECT *
			FROM agency_regions 
			WHERE deleted = 0 
		");
	}


	public function get_alarms(){
		return mysql_query("
			SELECT *
			FROM `alarm_pwr`
			WHERE `active` = 1
		");
	}
	
	public function get_services(){
		return mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
		");
	}
	
	function add_agency_maintenance($agency_id,$maintenance_id,$price,$surcharge,$display_surcharge,$surcharge_msg){
		$sql = "
			INSERT INTO
			`agency_maintenance` (
				`agency_id`,
				`maintenance_id`,
				`price`,
				`surcharge`,
				`display_surcharge`,
				`surcharge_msg`,
				`status`
			)
			VALUES (
				'".mysql_real_escape_string($agency_id)."',
				'".mysql_real_escape_string($maintenance_id)."',
				'".mysql_real_escape_string($price)."',
				'".mysql_real_escape_string($surcharge)."',
				'".mysql_real_escape_string($display_surcharge)."',
				'".mysql_real_escape_string($surcharge_msg)."',
				1
			)
		";
		mysql_query($sql);
	}
	
	public function add_agency($agency_name,$franchise_groups_id,$address_1,$address_2,$address_3,$phone,$state,$postcode,$postcode_region_id,$country,$lat,$lng,$tot_properties,$agency_hours,$comment,$login_id,$password,$contact_first_name,$contact_last_name,$contact_phone,$contact_email,$agency_emails,$account_emails,$send_emails,$send_combined_invoice,$send_entry_notice,$require_work_order,$allow_indiv_pm,$salesrep,$agen_stat,$agency_using_id,$legal_name,$auto_renew,$key_allowed,$key_email_req,$phone_call_req,$abn,$acc_name,$acc_phone,$allow_dk='',$website,$allow_en,$agency_specific_notes,$new_job_email_to_agent,$display_bpay,$allow_upfront_billing,$agency_special_deal){		
		
		// get sub region via postcode
		$pcr_sql = mysql_query("
			SELECT * 
			FROM  `postcode_regions`
			WHERE `postcode_region_postcodes` LIKE '%{$postcode}%'
			AND `country_id` = {$_SESSION['country_default']}
			AND `deleted` = 0
		");

		$pcr = mysql_fetch_array($pcr_sql);
		$pcr_id = $pcr['postcode_region_id'];
		
		mysql_query("
			INSERT INTO
			`agency` (
				`agency_name`,
				`franchise_groups_id`,
				`address_1`,
				`address_2`,
				`address_3`,
				`phone`,
				`state`,
				`postcode`,
				
				`lat`,
				`lng`,
				
				`postcode_region_id`,
				`tot_properties`,
				`agency_hours`,
				`comment`,
				`status`,
				`contact_first_name`,
				`contact_last_name`,
				`contact_phone`,
				`contact_email`,
				`agency_emails`,
				`account_emails`,
				`send_emails`,
				`send_combined_invoice`,
				`send_entry_notice`,
				`require_work_order`,
				`allow_indiv_pm`,
				`salesrep`,
				`pass_timestamp`,
				`tot_prop_timestamp`,
				`agency_using_id`,
				`legal_name`,
				`country_id`,
				`auto_renew`,
				`key_allowed`,
				`key_email_req`,
				`abn`,
				`accounts_name`,
				`accounts_phone`,
				`allow_dk`,
				`website`,
				`allow_en`,
				`agency_specific_notes`,
				`new_job_email_to_agent`,
				`display_bpay`,
				`allow_upfront_billing`,
				`invoice_pm_only`,
				`electrician_only`,
				`agency_special_deal`
			)
			VALUES (
				'".mysql_real_escape_string($agency_name)."',
				'".mysql_real_escape_string($franchise_groups_id)."',
				'".mysql_real_escape_string($address_1)."',
				'".mysql_real_escape_string($address_2)."',
				'".mysql_real_escape_string($address_3)."',
				'".mysql_real_escape_string($phone)."',
				'".mysql_real_escape_string($state)."',
				'".mysql_real_escape_string($postcode)."',
				
				'{$lat}',
				'{$lng}',
				
				'".mysql_real_escape_string($pcr_id)."',
				'".mysql_real_escape_string($tot_properties)."',
				'".mysql_real_escape_string($agency_hours)."',
				'".mysql_real_escape_string($comment)."',
				'".mysql_real_escape_string($agen_stat)."',
				'".mysql_real_escape_string($contact_first_name)."',
				'".mysql_real_escape_string($contact_last_name)."',
				'".mysql_real_escape_string($contact_phone)."',
				'".mysql_real_escape_string($contact_email)."',
				'".mysql_real_escape_string($agency_emails)."',
				'".mysql_real_escape_string($account_emails)."',
				1,
				1,
				1,
				0,
				'".mysql_real_escape_string($allow_indiv_pm)."',
				'".mysql_real_escape_string($salesrep)."',
				'".date('Y-m-d H:i:s')."',
				'".date('Y-m-d H:i:s')."',
				'".mysql_real_escape_string($agency_using_id)."',
				'".mysql_real_escape_string($legal_name)."',
				{$_SESSION['country_default']},
				1,
				1,
				0,
				'".mysql_real_escape_string($abn)."',
				'".mysql_real_escape_string($acc_name)."',
				'".mysql_real_escape_string($acc_phone)."',
				1,
				'".mysql_real_escape_string($website)."',
				'".mysql_real_escape_string($allow_en)."',
				'".mysql_real_escape_string($agency_specific_notes)."',
				'".mysql_real_escape_string($new_job_email_to_agent)."',
				0,
				'".mysql_real_escape_string($allow_upfront_billing)."',
				0,
				0,
				'".mysql_real_escape_string($agency_special_deal)."'
			)
		");
		
		$agency_id = mysql_insert_id();
		
		// add agency logs
		mysql_query("
			INSERT INTO 
			`agency_event_log`(
				`contact_type`,
				`eventdate`,
				`comments`,
				`agency_id`,
				`staff_id`
			) 
			VALUES(
			   'New Agency',
			   '" . date('Y-m-d') . "',
			   'Agency added as {$agen_stat} agency',
			   '{$agency_id}',
			   '{$_SESSION['USER_DETAILS']['StaffID']}'
			 )
		 ");
		
		
		
		return $agency_id;
	}
	
	public function get_agency($agency_id){
		return mysql_query("
			SELECT *
			FROM `agency`
			WHERE `agency_id` = {$agency_id}
		");
	}
	
	public function add_agency_alarms($agency_id,$alarm_pwr_id,$price){
		mysql_query("
			INSERT INTO
			`agency_alarms` (
				`agency_id`,
				`alarm_pwr_id`,
				`price`
			)
			VALUES (
				'".mysql_real_escape_string($agency_id)."',
				'".mysql_real_escape_string($alarm_pwr_id)."',
				'".mysql_real_escape_string($price)."'
			)
		");
	}
	
	public function add_agency_services($agency_id,$service_id,$price){
		mysql_query("
			INSERT INTO
			`agency_services` (
				`agency_id`,
				`service_id`,
				`price`
			)
			VALUES (
				'".mysql_real_escape_string($agency_id)."',
				'".mysql_real_escape_string($service_id)."',
				'".mysql_real_escape_string($price)."'
			)
		");
	}
	
	public function get_sales_representative(){
		return mysql_query("
			SELECT *
			FROM staff_accounts AS sa
			LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
			WHERE sa.deleted =0
			AND sa.active =1
			AND (
			sa.`ClassID` =1
			OR sa.`ClassID` =2
			OR sa.`ClassID` =5
			)
			AND `country_id` ={$_SESSION['country_default']}
		");
	}
	
	public function get_agency_alarms($agency_id){
		return mysql_query("
			SELECT *
			FROM `agency_alarms` AS a_a
			LEFT JOIN `alarm_pwr` AS pwr ON a_a.`alarm_pwr_id` = pwr.`alarm_pwr_id`
			WHERE `agency_id` = {$agency_id}
		");
	}
	
	public function get_agency_services($agency_id){
		return mysql_query("
			SELECT *
			FROM `agency_services` AS a_s
			LEFT JOIN `alarm_job_type` AS ajt ON a_s.`service_id` = ajt.`id`
			WHERE `agency_id` = {$agency_id}
		");
	}
	
	public function update_agency_alarms($agency_id,$agency_alarm_id,$price){
		mysql_query("
			UPDATE `agency_alarms`
			SET `price` = '".mysql_real_escape_string($price)."'
			WHERE `agency_alarm_id` = {$agency_alarm_id}
			AND `agency_id` = {$agency_id}
		");
	}
	
	public function update_agency_services($agency_id,$agency_services_id,$price){
		mysql_query("
			UPDATE `agency_services`
			SET `price` = '".mysql_real_escape_string($price)."'
			WHERE `agency_services_id` = {$agency_services_id}
			AND `agency_id` = {$agency_id}
		");
	}
	
	public function get_approved_agency_alarms($agency_id,$alarm_pwr_id){
		return mysql_query("
			SELECT * 
			FROM `agency_alarms`
			WHERE `agency_id` = {$agency_id}
			AND `alarm_pwr_id` = {$alarm_pwr_id}
		");
	}
	
	public function delete_agency_alarm($agency_id,$agency_alarm_id){
		mysql_query("
			DELETE 
			FROM `agency_alarms`
			WHERE `agency_id` = {$agency_id}
			AND `agency_alarm_id` = {$agency_alarm_id}
		");
	}
	
	public function get_approved_agency_services($agency_id,$service_id){
		return mysql_query("
			SELECT * 
			FROM `agency_services`
			WHERE `agency_id` = {$agency_id}
			AND `service_id` = {$service_id}
		");
	}
	
	public function delete_agency_service($agency_id,$agency_services_id){
		mysql_query("
			DELETE 
			FROM `agency_services`
			WHERE `agency_id` = {$agency_id}
			AND `agency_services_id` = {$agency_services_id}
		");
	}
	
	public function add_property_managers($agency_id,$name){
		mysql_query("
			INSERT INTO 
			`property_managers`(
				`name`,
				`agency_id`
			)
			VALUES(
				'".mysql_real_escape_string($name)."',
				{$agency_id}
			)
		");
	}
	
	public function send_mail($agency_name,$address_1,$address_2,$address_3,$phone,$state,$postcode,$region,$tot_properties,$contact_first_name,$contact_last_name,$contact_phone,$contact_email,$agency_emails,$account_emails,$send_emails,$send_combined_invoice,$send_entry_notice,$require_work_order,$allow_indiv_pm,$auto_renew,$key_allowed,$key_email_req,$salesrep,$phone_call_req,$legal_name,$abn,$acc_name,$acc_phone,$allow_dk,$allow_en,$agency_specific_notes,$new_job_email_to_agent,$display_bpay,$agency_special_deal){
	
		// get country
		$country_id = $_SESSION['country_default'];
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
	
		// recipients
		$to  = ACCOUNTS_EMAIL;
		//$to  = 'danielk@sats.com.au';
		//$to  = 'vaultdweller123@gmail.com';

		// subject
		$subject = 'New Agency Added';
		
		// get region name
		$sql = mysql_query("
			SELECT `postcode_region_name`
			FROM `postcode_regions`
			WHERE `postcode_region_id` = {$region}
		");
		$reg = mysql_fetch_array($sql);

		// message
		$message = '
		<html>
		<head>
		</head>
		<body>
		
		  <h3>Agency Details:</h3>
		  
		  <table>
			<tr>
			  <td style="text-align:right">Agency Name:</td><td>'.$agency_name.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Legal Name:</td><td>'.$legal_name.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">'.(($_SESSION['country_default']==1)?'ABN Number':'GST Number').':</td><td>'.$abn.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Street Number:</td><td>'.$address_1.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Street Name:</td><td>'.$address_2.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Suburb:</td><td>'.$address_3.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Phone:</td><td>'.$phone.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">State:</td><td>'.$state.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Postcode:</td><td>'.$postcode.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Region:</td><td>'.$reg['postcode_region_name'].'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Total Properties:</td><td>'.$tot_properties.'</td>
			</tr>
		  </table>
		  
		  <h3>Agency Contact</h3>
		  <table>
			<tr>
			  <td style="text-align:right">First Name:</td><td>'.$contact_first_name.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Last Name:</td><td>'.$contact_last_name.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Phone:</td><td>'.$contact_phone.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Email:</td><td>'.$contact_email.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Accounts Name:</td><td>'.$acc_name.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Accounts Phone:</td><td>'.$acc_phone.'</td>
			</tr>			
		  </table>
		  
		  <h3>Agency Emails</h3>
		  <table>
			<tr>
			  <td style="text-align:right">Agency Emails:</td><td>'.$agency_emails.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Accounts Emails:</td><td>'.$account_emails.'</td>
			</tr>			
		  </table>';
		  
		 /* 
		$message .= '<h3>Preferences</h3>
		  <table>
			<tr>
			  <td style="text-align:right">Send Account Emails to Agency?:</td><td>'.(($send_emails==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Combined Invoice / Cert PDF?:</td><td>'.(($send_combined_invoice==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Send Entry Notice Email?:</td><td>'.(($send_entry_notice==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Work Order Required For All Jobs?:</td><td>'.(($require_work_order==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Allow Individual Property Managers?:</td><td>'.(($allow_indiv_pm==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Auto Renew?:</td><td>'.(($auto_renew==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Key Access Allowed?:</td><td>'.(($key_allowed==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Tenant Key Email Required?:</td><td>'.(($key_email_req==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Allow Doorknocks?:</td><td>'.(($allow_dk==1)?"Yes":"No").'</td>
			</tr>';
			
			switch($allow_en){
				case -1:
					$allow_en_text = 'No Response';
				break;
				case 0:
					$allow_en_text = 'No';
				break;
				case 1:
					$allow_en_text = 'Yes';
				break;				
			}
			
			$message .= '
			<tr>
			  <td style="text-align:right">Allow Entry Notice?:</td><td>'.$allow_en_text.'</td>
			</tr>
			<tr>
			  <td style="text-align:right">New Job Email to Agent?:</td><td>'.(($new_job_email_to_agent==1)?"Yes":"No").'</td>
			</tr>
			<tr>
			  <td style="text-align:right">Display BPAY?:</td><td>'.(($display_bpay==1)?"Yes":"No").'</td>
			</tr>
		  </table>
		  
		  
		  ';
		  */
		  
		  $sql = mysql_query("
			SELECT `FirstName`, `LastName` 
			FROM `staff_accounts` 
			WHERE `StaffID` = {$salesrep}
		  ");
		  $sr = mysql_fetch_array($sql);
		  
		   $message .= '
		  <h3>Sales Rep</h3>
		  <table>
			<tr>
			  <td style="text-align:right">Name:</td><td>'.$sr['FirstName'].' '.$sr['LastName'].'</td>
			</tr>		
		  </table>
		  
		  <p>Agency added by '.$_SESSION['USER_DETAILS']['FirstName'].' '.$_SESSION['USER_DETAILS']['LastName'].'</p>
		  
		</body>
		</html>
		';
		
		//return $message;
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		$headers .= 'To: SATS <'.$to.'>' . "\r\n";
		$headers .= 'From: CRM <'.$cntry['outgoing_email'].'>' . "\r\n";
		$headers .= 'Cc: '.SALES_EMAIL. "\r\n";
		$headers .= 'Bcc: '.INFO_EMAIL. "\r\n";
		mail($to, $subject, $message, $headers);
		
	}

}

?>