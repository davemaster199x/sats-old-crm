<style>
#load-screen {
	width: 100%;
	height: 100%;
	background: url("/images/loading.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	z-index: 9999999999;
}

</style>
<div id="load-screen"></div>
<?php

include ('inc/init.php');
include ('inc/agency_class.php');

$agency_id = $_REQUEST['agency_id'];

if(!is_numeric($agency_id))
{
	header("Location: " . URL . "view_agencies.php");
}


//define variables name and initialize accordingly from submit form

$arr = array("letter1", "letter2", "letter3", "noletter");

foreach ($arr as $a) {

	if (isset($_POST[$a])) {

		${$a} = 1;
		//assign to 1 if checked

	} else {

		${$a} = 0;
		//assign to 0 if not checked

		//echo "<br>field {$a} is not set!!";

	}

	//echo "<br> ${$a} <br>";

}

$agency_name = mysql_real_escape_string($_POST['agency_name']);

$street_number = htmlspecialchars($_POST['street_number'], ENT_QUOTES);

$street_name = htmlspecialchars($_POST['street_name'], ENT_QUOTES);

$suburb = htmlspecialchars($_POST['suburb'], ENT_QUOTES);

$phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);

$state = htmlspecialchars($_POST['state'], ENT_QUOTES);

$region_id = $_POST['region_id'];
$country = $_POST['country'];

$postcode = htmlspecialchars($_POST['postcode'], ENT_QUOTES);

if($postcode!=''){
		
	// get sub region via postcode
	$pcr_sql_str = "
		SELECT * 
		FROM  `postcode_regions`
		WHERE `postcode_region_postcodes` LIKE '%".trim($postcode)."%'
		AND `country_id` = {$_SESSION['country_default']}
		AND `deleted` = 0
	";
	$pcr_sql = mysql_query($pcr_sql_str);
	$pcr = mysql_fetch_array($pcr_sql);
	$pcr_id = $pcr['postcode_region_id'];

	mysql_query("
		UPDATE `agency`
		SET `postcode_region_id` = {$pcr_id}
		WHERE `agency_id` = {$agency_id}
	");
		
}


$totprop = htmlspecialchars($_POST['tot_properties'], ENT_QUOTES);

$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);

$agency_specific_notes = mysql_real_escape_string($_POST['agency_specific_notes']);
$team_meeting = mysql_real_escape_string($_POST['team_meeting']);


$display_bpay = mysql_real_escape_string($_POST['display_bpay']);


$user = mysql_real_escape_string($_POST['user']);
$pass = mysql_real_escape_string($_POST['pass']);

$encrypt = new cast128();
$encrypt->setkey(SALT);
$pass2 = addslashes(utf8_encode($encrypt->encrypt($pass)));


$ac_fname = htmlspecialchars($_POST['ac_fname'], ENT_QUOTES);
$ac_lname = htmlspecialchars($_POST['ac_lname'], ENT_QUOTES);
$ac_phone = htmlspecialchars($_POST['ac_phone'], ENT_QUOTES);
$ac_email = htmlspecialchars($_POST['ac_email'], ENT_QUOTES);



$custom_alarm_pricing = htmlspecialchars($_POST['custom_alarm_pricing'], ENT_QUOTES);

$send_emails = intval($_POST['send_emails']);

$send_combined_invoice = htmlspecialchars($_POST['send_combined_invoice'], ENT_QUOTES);

$send_entry_notice = htmlspecialchars($_POST['send_entry_notice'], ENT_QUOTES);

$salesrep = $_POST['salesrep'];

$status = $_POST['status'];


# Process emails
$account_emails = "";
$_POST['account_emails'] = trim($_POST['account_emails']);

if (stristr($_POST['account_emails'], "\n")) {
	$_POST['account_emails'] = explode("\n", $_POST['account_emails']);
	foreach ($_POST['account_emails'] as $email) {
		$email = trim($email);
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
			$account_emails .= $email . "\n";
	}
} else {
	if (filter_var($_POST['account_emails'], FILTER_VALIDATE_EMAIL))
		$account_emails = $_POST['account_emails'];
}

$account_emails = trim($account_emails);

$agency_emails = "";
$_POST['agency_emails'] = trim($_POST['agency_emails']);

if (stristr($_POST['agency_emails'], "\n")) {
	$_POST['agency_emails'] = explode("\n", $_POST['agency_emails']);
	foreach ($_POST['agency_emails'] as $email) {
		$email = trim($email);
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
			$agency_emails .= $email . "\n";
	}
} else {
	if (filter_var($_POST['agency_emails'], FILTER_VALIDATE_EMAIL))
		$agency_emails = $_POST['agency_emails'];
}

$agency_emails = trim($agency_emails);

if ($totprop == ""){
	$totprop = 0;
}
	



$pass_change =$_POST['pass_change'];
$tot_prop_change = $_POST['tot_prop_change'];

$allow_indiv_pm = $_POST['allow_indiv_pm'];

$now = date("Y-m-d H:i:s");
$update_password_str = ($pass_change=="1")?"`pass_timestamp`='".$now."',":"";
$update_tot_prop_str = ($tot_prop_change=="1")?"`tot_prop_timestamp`='".$now."',":"";

$agency_id;

$work_order_required =$_POST['work_order_required'];
$franchise_group = mysql_real_escape_string($_POST['franchise_group']);
$abn = mysql_real_escape_string($_POST['abn']);
$agency_using = mysql_real_escape_string($_POST['agency_using']);
$legal_name = mysql_real_escape_string($_POST['legal_name']);
$auto_renew = mysql_real_escape_string($_POST['auto_renew']);

$key_allowed = mysql_real_escape_string($_POST['key_allowed']);
$key_email_req = mysql_real_escape_string($_POST['key_email_req']);
$phone_call_req = mysql_real_escape_string($_POST['phone_call_req']);
$allow_dk = mysql_real_escape_string($_POST['allow_dk']);

$agency_hours = mysql_real_escape_string($_POST['agency_hours']);

$acc_name = mysql_real_escape_string($_POST['acc_name']);
$acc_phone = mysql_real_escape_string($_POST['acc_phone']);
$website = mysql_real_escape_string($_POST['website']);
$allow_en = mysql_real_escape_string($_POST['allow_en']);
$new_job_email_to_agent = mysql_real_escape_string($_POST['new_job_email_to_agent']);


$tdc_name = mysql_real_escape_string($_POST['tdc_name']);
$tdc_phone = mysql_real_escape_string($_POST['tdc_phone']);


// get agency details
$agen_sql = mysql_query("
	SELECT *, a.`status` AS a_status
	FROM `agency` AS a
	LEFT JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`
	WHERE a.`agency_id` = {$agency_id}
");
$agen = mysql_fetch_array($agen_sql);
$orig_agency_name = $agen['agency_name'];
$orig_agency_emails = $agen['agency_emails'];
$orig_account_emails = $agen['account_emails'];
$orig_salesrep = $agen['salesrep'];
$orig_status = $agen['a_status'];
$orig_salesrep_name = "{$agen['FirstName']} {$agen['LastName']}";

if( $_POST['agency_name_edited']==1 || $_POST['agency_emails_edited']==1 || $_POST['account_emails_edited']==1 || $_POST['salesrep_edited']==1 ){
	
	
	
	// multiple recipients
	$to = ACCOUNTS_EMAIL;
	//$to  = 'accounts@sats.com.au';
	//$to  = 'vaultdweller123@gmail.com';
	//$to = 'danielk@sats.com.au';

	// subject
	$subject = 'MYOB Update Required';

	// message
	$message = '
	<html>
	<head>
	  <title>'.$subject.'</title>
	</head>
	<body>';
	
	$message .= "<p>{$orig_agency_name} had updated their details</p>";
	
	if( $_POST['agency_name_edited']==1 && $orig_agency_name != $_POST['agency_name'] ){
		$message .= "Agency Name has been updated in the CRM:<br /><br /> 
		
		From <br /><strong>{$orig_agency_name}</strong> <br />
		To <br /><strong>{$_POST['agency_name']}</strong> <br/><br />
		
		Please update the records in MYOB<br /><br />";
	}
	
	if( $_POST['agency_emails_edited']==1 && $orig_agency_emails != $agency_emails ){
		
		$message .= 'Agency Emails has been updated <br /><br />
		From: <br />';
		$orig_agency_emails_split = explode("\n",trim($orig_agency_emails));
		$message .= '<ul>';
		foreach( $orig_agency_emails_split as $email ){
			$message .= '<li>'.$email.'</li>';
		}
		$message .= '</ul>';
		$message .= '<br />To: <br />';
		$agency_emails_split = explode("\n",trim($agency_emails));
		$message .= '<ul>';
		foreach( $agency_emails_split as $email ){
			$message .= '<li>'.$email.'</li>';
		}
		$message .= '</ul>';
	}
	
	if( $_POST['account_emails_edited']==1 && $orig_account_emails != $account_emails ){
		
		$message .= 'Account Emails has been updated <br /><br />
		From: <br />';
		$orig_account_emails_split = explode("\n",trim($orig_account_emails));
		$message .= '<ul>';
		foreach( $orig_account_emails_split as $email ){
			$message .= '<li>'.$email.'</li>';
		}
		$message .= '</ul>';
		$message .= '<br />To: <br />';
		$account_emails_split = explode("\n",trim($account_emails));
		$message .= '<ul>';
		foreach( $account_emails_split as $email ){
			$message .= '<li>'.$email.'</li>';
		}
		$message .= '</ul>';
	}
	
	
	// salesrep
	if( $_POST['salesrep_edited']==1 && $salesrep != $orig_salesrep ){
		
		// get salesrep name
		$sr_sql = mysql_query("
			SELECT `FirstName`, `LastName`
			FROM `staff_accounts`
			WHERE `StaffID` = {$salesrep}
		");
		$sr = mysql_fetch_array($sr_sql);
		$new_salesrep_name = "{$sr['FirstName']} {$sr['LastName']}";
		
		$message .= "<br /><br />Salesrep has been updated in the CRM:<br /><br /> 
		From <strong>{$orig_salesrep_name}</strong> <br />
		To <strong>{$new_salesrep_name}</strong> <br/><br />";
	}
	 
	 $message .= '
	</body>
	</html>
	';

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	// Additional headers
	// get country
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);

	$headers .= 'To: SATS <'.$to.'>' . "\r\n";
	$headers .= 'From: SATS - Smoke Alarm Testing Services <'.$cntry['outgoing_email'].'>' . "\r\n";

	//echo $message;

	// Mail it
	if( $status=='active' || $orig_status=='active' ){
		//mail($to, $subject, $message, $headers);
	}
	
	
}















if( $status=="target" || $orig_status=='deactivated' ){
	$target_str = "`agency_using_id` = '{$agency_using}',";
}else if($status=="active"){
	
	//echo "status: {$status} auto renew: {$auto_renew}";
	
	$active_str = " 
		account_emails = '$account_emails',
		send_emails ='$send_emails',
		send_combined_invoice = '$send_combined_invoice',
		send_entry_notice = '$send_entry_notice',
		`require_work_order` = '{$work_order_required}',
		`allow_indiv_pm` = '{$allow_indiv_pm}',
		`legal_name` = '{$legal_name}',
		`auto_renew` = '{$auto_renew}',
		`key_allowed` = '{$key_allowed}',
		`key_email_req` = '{$key_email_req}',
		`allow_dk` = '{$allow_dk}',
		`allow_en` = '{$allow_en}',
		`new_job_email_to_agent` = '{$new_job_email_to_agent}',	
		`display_bpay` = '{$display_bpay}',		
   ";
}

$address = "{$street_number} {$street_name} {$suburb} {$state} {$postcode}, {$_SESSION['country_name']}";
$coordinate = getGoogleMapCoordinates($address);


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


# Finally update agent details
$Query = "UPDATE agency SET 
   agency_name='$agency_name', 
   franchise_groups_id='$franchise_group',
   `abn` = '{$abn}',
   address_1='$street_number', 
   address_2='$street_name', 
   address_3='$suburb', 
   phone='$phone', 
   state='$state',
   postcode='$postcode', 
   tot_properties=$totprop,
   `comment` = '{$comment}',
   `agency_hours` = '{$agency_hours}',
   custom_alarm_pricing = '$custom_alarm_pricing',   
   postcode_region_id = '$region_id',
   status = '$status',
   salesrep = '$salesrep',
   
   `lat` = '{$coordinate['lat']}',
   `lng` = '{$coordinate['lng']}',
   
   `login_id` = '{$user}',
   `password` = '{$pass2}',
   {$update_password_str}
   {$update_tot_prop_str}
   `contact_first_name` = '{$ac_fname}',
   `contact_last_name` = '{$ac_lname}',
   `contact_phone` = '{$ac_phone}',
   `agency_emails` = '{$agency_emails}',
   {$target_str}
   {$active_str}
   `contact_email` = '{$ac_email}',
   `accounts_name` = '{$acc_name}',
   `accounts_phone` = '{$acc_phone}',
   `postcode_region_id` = '{$pcr_id}',
   `website` = '{$website}',
   `agency_specific_notes` = '{$agency_specific_notes}',
   `team_meeting` = '{$team_meeting}',
   
    `tenant_details_contact_name` = '{$tdc_name}',
    `tenant_details_contact_phone` = '{$tdc_phone}'
   WHERE agency_id={$agency_id}";
   
  

$result = mysql_query($Query, $connection);

// insert agency logs
$fields_edited = $_POST['fields_edited'];
if($fields_edited!=""){
	
	$logs_arr = explode(",",substr($fields_edited,1));
	foreach($logs_arr as $log_msg){
		
		mysql_query("
			INSERT INTO 
			`agency_event_log` (
				contact_type,
				eventdate,
				comments,
				agency_id,
				`staff_id`
			) 
			VALUES (
				'Agency Update',
				'".date('Y-m-d')."',
				'{$log_msg} changed by {$_SESSION['USER_DETAILS']['FirstName']} ".(strtoupper(substr($_SESSION['USER_DETAILS']['LastName'],0,1)).'.')." @ ".date('H:i')."',
				'{$agency_id}',
				'{$_SESSION['USER_DETAILS']['StaffID']}'
			)
		");
		
	}
	
}


$maintenance = mysql_real_escape_string($_POST['maintenance']);
$m_price = mysql_real_escape_string($_POST['m_price']);
$m_surcharge = mysql_real_escape_string($_POST['m_surcharge']);
$m_disp_surcharge = mysql_real_escape_string($_POST['m_disp_surcharge']);
$m_surcharge_msg = mysql_real_escape_string($_POST['m_surcharge_msg']);

if($status=="active"){
	
	
	// maintenance program
	// clear
	mysql_query("
		DELETE 
		FROM `agency_maintenance`
		WHERE `agency_id` = {$agency_id}	
	");
	// insert
	mysql_query("
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
			'{$agency_id}',
			'{$maintenance}',
			'{$m_price}',
			'{$m_surcharge}',
			'{$m_disp_surcharge}',
			'{$m_surcharge_msg}',
			1
		)
	");
	
	
}




//echo $Query;




// deactivate property and cancel jobs if status change from active to target or deactivated
if( $orig_status=='active' && ( $status=="target" || $status=="deactivated" ) ){

	// get agency properties
	$p_sql = mysql_query("
		SELECT `property_id`
		FROM `property`
		WHERE `agency_id` ={$agency_id}
	");
	if(mysql_num_rows($p_sql)>0){
		while($p = mysql_fetch_array($p_sql)){
			
			// update property to deleted
			mysql_query("
				UPDATE `property` 
				SET 
					`deleted` =1,
					`deleted_date` = '".date("Y-m-d H:i:s")."' 
				WHERE `property_id` ={$p['property_id']}
				AND `agency_id` ={$agency_id}
			");
			
			// jobs
			mysql_query("
				UPDATE `jobs`
				SET 
					`status` = 'Cancelled',
					`comments` = 'This Agency was marked No Longer Active by {$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']} on ".date("d/m/Y")." and all jobs cancelled'
				WHERE `property_id` = {$p['property_id']}
				AND `status` != 'Completed'
			");
			// property logs
			mysql_query("
				INSERT INTO 
				`property_event_log`(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`
				)
				VALUES(
					'{$p['property_id']}',
					'{$_SESSION['USER_DETAILS']['StaffID']}',
					'Agency Changed to Target',
					'@ ".date("H:i")."',
					'".date("Y-m-d H:i:s")."'
				)
			");
		}
	}

	// agency logs
	mysql_query("
		INSERT INTO
		`crm`(
			`agency_id`,	
			`contact_type`,
			`eventdate`,
			`comments`,
			`staff_id`
		)
		VALUES(
			'{$agency_id}',
			'Agency Changed to Target',
			'".date("Y-m-d")."',
			'@ ".date("H:i")."',
			'{$_SESSION['USER_DETAILS']['StaffID']}'
		)
	");

}else if( $status=="active" ){
	
	
	// Property Manager
	$pm_id =$_POST['pm_id'];
	$pm_name =$_POST['pm_name'];
	$pm_email =$_POST['pm_email'];
	
	foreach($pm_id as $i => $val){
		
		if($val!=""){
			mysql_query("
				UPDATE `property_managers`
				SET 
					`name` = '".mysql_real_escape_string($pm_name[$i])."',
					`pm_email` = '".mysql_real_escape_string($pm_email [$i])."'
				WHERE `property_managers_id` = {$val}
			");
		}else{
			mysql_query("
				INSERT INTO
				`property_managers`(
					`name`,
					`pm_email`,
					`agency_id`
				)
				VALUES(
					'".mysql_real_escape_string($pm_name[$i])."',
					'".mysql_real_escape_string($pm_email[$i])."',
					'{$agency_id}'
				)
			");
		}
		
	}
	
	
	
	
	// PRICING
	// Services	
	$agency_service_approve = $_POST['agency_service_approve'];
	$service_id = $_POST['service_id'];
	$service_price = $_POST['service_price'];	
	
	mysql_query("
		DELETE 
		FROM `agency_services`
		WHERE `agency_id` = {$agency_id}
	");	
	
	foreach( $agency_service_approve as $manual_index ){
		
		$sql = "
			INSERT INTO
			`agency_services` (
				`agency_id`,
				`service_id`,
				`price`
			)
			VALUES (
				'".mysql_real_escape_string($agency_id)."',
				'".mysql_real_escape_string($service_id[$manual_index])."',
				'".mysql_real_escape_string($service_price[$manual_index])."'
			)
		";
		
		mysql_query($sql);
		
	}
	
	// alarms	
	$agency_alarm_approve = $_POST['agency_alarm_approve'];
	$alarm_id = $_POST['alarm_id'];
	$alarm_price = $_POST['alarm_price'];	
	
	
	mysql_query("
		DELETE 
		FROM `agency_alarms`
		WHERE `agency_id` = {$agency_id}
	");	
	
	
	foreach( $agency_alarm_approve as $manual_index ){
		
		$sql = "
			INSERT INTO
			`agency_alarms` (
				`agency_id`,
				`alarm_pwr_id`,
				`price`
			)
			VALUES (
				'".mysql_real_escape_string($agency_id)."',
				'".mysql_real_escape_string($alarm_id[$manual_index])."',
				'".mysql_real_escape_string($alarm_price[$manual_index])."'
			)
		";
		mysql_query($sql);
		
	}
	
	
	

}


echo "<script>window.location='view_agency_details.php?id={$agency_id}&success=1'</script>";
?>