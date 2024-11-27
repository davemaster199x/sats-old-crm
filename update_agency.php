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

$crm = new Sats_Crm_Class;

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
	/*$pcr_sql_str = "
		SELECT * 
		FROM  `postcode_regions`
		WHERE `postcode_region_postcodes` LIKE '%".trim($postcode)."%'
		AND `country_id` = {$_SESSION['country_default']}
		AND `deleted` = 0
	";*/
	## new table (by:gherx)
	$pcr_sql_str = "
		SELECT *, sr.sub_region_id as postcode_region_id
		FROM  `sub_regions` as sr
		LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
		WHERE pc.`postcode` LIKE '%".trim($postcode)."%'
		AND `country_id` = {$_SESSION['country_default']}
		AND `deleted` = 0";

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
$allow_upfront_billing = mysql_real_escape_string($_POST['allow_upfront_billing']);
$invoice_pm_only = mysql_real_escape_string($_POST['invoice_pm_only']);
$electrician_only = mysql_real_escape_string($_POST['electrician_only']);
$send_en_to_agency = mysql_real_escape_string($_POST['send_en_to_agency']);
$exclude_free_invoices = mysql_real_escape_string($_POST['exclude_free_invoices']);
$send_48_hr_key = mysql_real_escape_string($_POST['send_48_hr_key']);
$en_to_pm = mysql_real_escape_string($_POST['en_to_pm']);
$accounts_reports = mysql_real_escape_string($_POST['accounts_reports']);


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

$statements_agency_comments = htmlspecialchars($_POST['statements_agency_comments'], ENT_QUOTES);


$statements_agency_comments_is_changed_post = htmlspecialchars($_POST['statements_agency_comments_is_changed'], ENT_QUOTES);
$statements_agency_comments_ts_post = htmlspecialchars($_POST['statements_agency_comments_ts'], ENT_QUOTES);

if($statements_agency_comments_is_changed_post==1){
	$statements_agency_comments_ts = "'".date('Y-m-d H:i:s')."'";
}else{
	$statements_agency_comments_ts = "'".$statements_agency_comments_ts_post."'";
}



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

$allow_indiv_pm_email_cc = $_POST['allow_indiv_pm_email_cc'];

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

$agency_special_deal = mysql_real_escape_string($_POST['agency_special_deal']);
$multi_owner_discount = ( $_POST['multi_owner_discount']!='' )?"'".mysql_real_escape_string($_POST['multi_owner_discount'])."'":'NULL';


$mm_program_edited = mysql_real_escape_string($_POST['mm_program_edited']);
$agency_services_edited = mysql_real_escape_string($_POST['agency_services_edited']);
$agency_alarms_edited = mysql_real_escape_string($_POST['agency_alarms_edited']);

$trust_acc_soft = mysql_real_escape_string($_POST['trust_acc_soft']);
$tas_connected = mysql_real_escape_string($_POST['tas_connected']);
$propertyme_agency_id = ( $_POST['propertyme_agency_id']!='' )?"'".mysql_real_escape_string($_POST['propertyme_agency_id'])."'":'NULL';

$joined_sats = ($_POST['joined_sats']!='')?"'{$crm->formatDate($_POST['joined_sats'])}'":'NULL';

/** Gherx Added */
if($status=='deactivated'){
	$date_deactivated = "'".$crm->formatDate(date('Y-m-d'))."'";
	$active_prop_with_sats = ($_POST['active_prop_with_sats']!="")?"'".mysql_real_escape_string($_POST['active_prop_with_sats'])."'":'NULL';
	$deactivate_reason = ($_POST['deactivate_reason']!="")?"'".htmlspecialchars($_POST['deactivate_reason'], ENT_QUOTES)."'":'NULL';
}else{
	$date_deactivated = 'NULL';
	$active_prop_with_sats = 'NULL';
	$deactivate_reason =  'NULL';
}
/** Gherx Added END */


// get agency details
$agen_sql = mysql_query("
	SELECT *, a.`status` AS a_status
	FROM `agency` AS a
	LEFT JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`
	WHERE a.`agency_id` = {$agency_id}
");
$agen = mysql_fetch_array($agen_sql);
$orig_agency_name = $agen['agency_name'];
$orig_legal_name = $agen['legal_name'];
$orig_abn = $agen['abn'];
$orig_agency_emails = $agen['agency_emails'];
$orig_account_emails = $agen['account_emails'];
$orig_salesrep = $agen['salesrep'];
$orig_status = $agen['a_status'];
$orig_salesrep_name = "{$agen['FirstName']} {$agen['LastName']}";

if( 
	$_POST['agency_name_edited'] == 1 || 
	$_POST['legal_name_edited'] == 1 ||
	$_POST['abn_edited'] == 1 ||

	$_POST['agency_emails_edited'] == 1 || 
	$_POST['account_emails_edited'] == 1 || 
	$_POST['salesrep_edited'] == 1 
){


	$is_agency_name_updated = false;
	$is_legal_name_updated = false;
	$is_abn_updated = false;
	
	
	
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
	
	// agency name
	if( $_POST['agency_name_edited']==1 && $orig_agency_name != $_POST['agency_name'] ){

		$message .= "<p>Agency Name has been updated in the CRM:</p> 
		
		From <br /><strong>{$orig_agency_name}</strong> <br />
		To <br /><strong>{$_POST['agency_name']}</strong> <br/><br />";

		$is_agency_name_updated = true;

	}

	// legal name
	if( $_POST['legal_name_edited']==1 && $orig_legal_name != $_POST['legal_name'] ){

		$message .= "<p>Legal Name has been updated in the CRM:</p> 
		
		From <br /><strong>{$orig_legal_name}</strong> <br />
		To <br /><strong>{$_POST['legal_name']}</strong> <br/><br />";

		$is_legal_name_updated = true;

	}

	// ABN number
	if( $_POST['abn_edited']==1 && $orig_abn != $_POST['abn'] ){

		$message .= "<p>ABN number has been updated in the CRM:</p> 
		
		From <br /><strong>{$orig_abn}</strong> <br />
		To <br /><strong>{$_POST['abn']}</strong> <br/><br />";

		$is_abn_updated = true;

	}

	if( $is_agency_name_updated == true || $is_legal_name_updated == true || $is_abn_updated == true  ){
		$message .= "Please update the records in MYOB<br /><br />";
	}
	
	if( $_POST['agency_emails_edited']==1 && $orig_agency_emails != $agency_emails ){
		
		$message .= '<p>Agency Emails has been updated:</p>
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
		
		$message .= '<p>Account Emails has been updated:</p>
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
		
		$message .= "<p>Salesrep has been updated in the CRM:</p> 
		From: <strong>{$orig_salesrep_name}</strong> <br />
		To: <strong>{$new_salesrep_name}</strong> <br/><br />";
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
		mail($to, $subject, $message, $headers);
	}
	
	
}








if(  $agen['exclude_free_invoices'] != $exclude_free_invoices ){

	$marked_txt = ( $exclude_free_invoices == 1 )?'marked':'unmarked';

	mysql_query("
		INSERT INTO 
		`agency_event_log` 
		(
			`contact_type`,
			`eventdate`,
			`comments`,
			`agency_id`,
			`staff_id`,
			`date_created`,
			`hide_delete`
		) 
		VALUES (
			'Preference Update',
			'".date('Y-m-d')."',
			'Exclude free invoices <b>{$marked_txt}</b>',
			'{$agency_id}',
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('Y-m-d H:i:s')."',
			1
		);
	");
	
}

if(  $agen['send_48_hr_key'] != $send_48_hr_key ){

	$marked_txt = ( $send_48_hr_key == 1 )?'marked':'unmarked';

	mysql_query("
		INSERT INTO 
		`agency_event_log` 
		(
			`contact_type`,
			`eventdate`,
			`comments`,
			`agency_id`,
			`staff_id`,
			`date_created`,
			`hide_delete`
		) 
		VALUES (
			'Preference Update',
			'".date('Y-m-d')."',
			'Send 48 hour key email <b>{$marked_txt}</b>',
			'{$agency_id}',
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('Y-m-d H:i:s')."',
			1
		);
	");
	
}








$address = "{$street_number} {$street_name} {$suburb} {$state} {$postcode}, {$_SESSION['country_name']}";
$coordinate = getGoogleMapCoordinates($address);


// get sub region via postcode
/*$pcr_sql = mysql_query("
	SELECT * 
	FROM  `postcode_regions`
	WHERE `postcode_region_postcodes` LIKE '%{$postcode}%'
	AND `country_id` = {$_SESSION['country_default']}
	AND `deleted` = 0
");*/

$pcr_sql = mysql_query("
	SELECT *, sr.sub_region_id as postcode_region_id
	FROM  `sub_regions` as sr
	LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
	WHERE pc.`postcode` LIKE '%".trim($postcode)."%'
	AND `country_id` = {$_SESSION['country_default']}
	AND `deleted` = 0
");

$pcr = mysql_fetch_array($pcr_sql);
$pcr_id = $pcr['postcode_region_id'];


// insert agency logs
$fields_edited = $_POST['fields_edited'];
if($fields_edited!=""){
	
	$logs_arr = explode(",",substr($fields_edited,1));
	foreach($logs_arr as $log_msg){

		$log_msg_append = null;
		if( $log_msg == 'Agency Comments' ){
			$log_msg_append = ", from: <b>".mysql_real_escape_string($agen['comment'])."</b>";
		}else if( $log_msg == 'Agency Specific Notes' ){
			$log_msg_append = ", from: <b>".mysql_real_escape_string($agen['agency_specific_notes'])."</b>";
		}
		
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
				'<strong>{$log_msg}</strong> changed @ ".date('H:i')."{$log_msg_append}',
				'{$agency_id}',
				'{$_SESSION['USER_DETAILS']['StaffID']}'
			)
		");
		
	}
	
}


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
   
   {$update_tot_prop_str}
   `contact_first_name` = '{$ac_fname}',
   `contact_last_name` = '{$ac_lname}',
   `contact_phone` = '{$ac_phone}',
   `agency_emails` = '{$agency_emails}',
   
   `agency_using_id` = '{$agency_using}',
  
	account_emails = '$account_emails',
	send_emails ='$send_emails',
	send_combined_invoice = '$send_combined_invoice',
	send_entry_notice = '$send_entry_notice',
	`require_work_order` = '{$work_order_required}',
	`allow_indiv_pm_email_cc` = '{$allow_indiv_pm_email_cc}',
	`legal_name` = '{$legal_name}',
	`auto_renew` = '{$auto_renew}',
	`key_allowed` = '{$key_allowed}',
	`key_email_req` = '{$key_email_req}',
	`allow_dk` = '{$allow_dk}',
	`allow_en` = '{$allow_en}',
	`new_job_email_to_agent` = '{$new_job_email_to_agent}',	
	`display_bpay` = '{$display_bpay}',	
	`allow_upfront_billing` = '{$allow_upfront_billing}',	
	`invoice_pm_only` = '{$invoice_pm_only}',
	`electrician_only` = '{$electrician_only}',
	`send_en_to_agency` = '{$send_en_to_agency}',
	`en_to_pm` = '{$en_to_pm}',
	`accounts_reports` = '{$accounts_reports}',
	`exclude_free_invoices` = '{$exclude_free_invoices}',
	`send_48_hr_key` = '{$send_48_hr_key}',
  
   `contact_email` = '{$ac_email}',
   `accounts_name` = '{$acc_name}',
   `accounts_phone` = '{$acc_phone}',
   `postcode_region_id` = '{$pcr_id}',
   `website` = '{$website}',
   `agency_specific_notes` = '{$agency_specific_notes}',
   `team_meeting` = '{$team_meeting}',
   
    `tenant_details_contact_name` = '{$tdc_name}',
    `tenant_details_contact_phone` = '{$tdc_phone}',
	`agency_special_deal` = '{$agency_special_deal}',
	`multi_owner_discount` = {$multi_owner_discount},
	`trust_account_software` = '{$trust_acc_soft}',
	`joined_sats` = {$joined_sats},
	
	`propertyme_agency_id` = {$propertyme_agency_id},

	`deactivated_ts` = {$date_deactivated},
	`deactivated_reason` = {$deactivate_reason},
	`active_prop_with_sats` = {$active_prop_with_sats},

	`statements_agency_comments` = '{$statements_agency_comments}',
	`statements_agency_comments_ts` = {$statements_agency_comments_ts}
	
   WHERE agency_id={$agency_id}";
   
  

$result = mysql_query($Query, $connection);




$maintenance = mysql_real_escape_string($_POST['maintenance']);
$m_price = mysql_real_escape_string($_POST['m_price']);
$m_surcharge = mysql_real_escape_string($_POST['m_surcharge']);
$m_disp_surcharge = mysql_real_escape_string($_POST['m_disp_surcharge']);
$m_surcharge_msg = mysql_real_escape_string($_POST['m_surcharge_msg']);



// Property Manager
$pm_id =$_POST['pm_id'];
$pm_user_type =$_POST['pm_user_type'];
$pm_fname =$_POST['pm_fname'];
$pm_lname =$_POST['pm_lname'];
$pm_job_title =$_POST['pm_job_title'];
$pm_phone =$_POST['pm_phone'];
$pm_email =$_POST['pm_email'];

foreach($pm_id as $i => $val){
	
	if($val!=""){ // update
	
		mysql_query("
			UPDATE `agency_user_accounts`
			SET 
				`user_type` = '".mysql_real_escape_string($pm_user_type[$i])."',
				`fname` = '".mysql_real_escape_string($pm_fname[$i])."',
				`lname` = '".mysql_real_escape_string($pm_lname[$i])."',
				`job_title` = '".mysql_real_escape_string($pm_job_title[$i])."',
				`phone` = '".mysql_real_escape_string($pm_phone[$i])."',
				`email` = '".mysql_real_escape_string($pm_email[$i])."'
			WHERE `agency_user_account_id` = {$val}
		");
		
	}else{ // insert
	
		mysql_query("
			INSERT INTO
			`agency_user_accounts`(
				`fname`,
				`lname`,
				`job_title`,
				`phone`,
				`email`,
				`user_type`,
				`date_created`,
				`agency_id`
			)
			VALUES(
				'".mysql_real_escape_string($pm_fname[$i])."',
				'".mysql_real_escape_string($pm_lname[$i])."',
				'".mysql_real_escape_string($pm_job_title[$i])."',
				'".mysql_real_escape_string($pm_phone[$i])."',
				'".mysql_real_escape_string($pm_email[$i])."',
				'".mysql_real_escape_string($pm_user_type[$i])."',
				'".date('Y-m-d H:i:s')."',
				'{$agency_id}'
			)
		");
		
	}
	
}





// get current MM
$mm_sql = mysql_query("
SELECT `maintenance_id`
FROM `agency_maintenance`
WHERE `agency_id` = {$agency_id}	
");
$mm_row = mysql_fetch_object($mm_sql);


if( $mm_row->maintenance_id != $maintenance ){	
	
	// maintenance program
	// clear
	mysql_query("
		DELETE 
		FROM `agency_maintenance`
		WHERE `agency_id` = {$agency_id}	
	");	

	if( $maintenance > 0 ){ // selected any MM

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
				`updated_date`,
				`status`
			)
			VALUES (
				'{$agency_id}',
				'{$maintenance}',
				'{$m_price}',
				'{$m_surcharge}',
				'{$m_disp_surcharge}',
				'{$m_surcharge_msg}',
				'".date("Y-m-d")."',
				1
			)
		");

		// update all jobs to dha processing
		mysql_query("
		UPDATE `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		SET j.`dha_need_processing` = 1
		WHERE a.`agency_id` = {$agency_id}	
		AND j.`del_job` = 0
		AND j.`status` NOT IN('Completed','Cancelled')
		");

	}else{ // selected "none"

		// update all jobs to dha processing
		mysql_query("
		UPDATE `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		SET j.`dha_need_processing` = 0
		WHERE a.`agency_id` = {$agency_id}	
		AND j.`del_job` = 0
		AND j.`status` NOT IN('Completed','Cancelled')
		");

	}
	

	// insert logs
	mysql_query("
	INSERT INTO 
	`agency_event_log` 
	(
		`contact_type`,
		`eventdate`,
		`comments`,
		`agency_id`,
		`staff_id`,
		`date_created`,
		`hide_delete`
	) 
	VALUES (
		'Maintenance Program',
		'".date('Y-m-d')."',
		'Maintenance Program Updated',
		'{$agency_id}',
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('Y-m-d H:i:s')."',
		1
	);
	");

	
}




// PRICING
if( $agency_services_edited==1 ){
	
	// Services	
	$agency_service_approve = $_POST['agency_service_approve'];
	$service_id = $_POST['service_id'];
	$service_price = $_POST['service_price'];	
	$agency_service_changed = $_POST['agency_service_changed'];	
	$agency_service_orig_price = $_POST['agency_service_orig_price'];	
	
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
		
		if( $agency_service_changed[$manual_index] == 1 ){
			
			// insert logs
			mysql_query("
				INSERT INTO 
				`agency_event_log` 
				(
					`contact_type`,
					`eventdate`,
					`comments`,
					`agency_id`,
					`staff_id`,
					`date_created`,
					`hide_delete`
				) 
				VALUES (
					'Service Price Updated',
					'".date('Y-m-d')."',
					'Service price has been updated from <strong>\${$agency_service_orig_price[$manual_index]}</strong> to <strong>\$".number_format($service_price[$manual_index],2)."</strong>',
					'{$agency_id}',
					'".$_SESSION['USER_DETAILS']['StaffID']."',
					'".date('Y-m-d H:i:s')."',
					1
				);
			");
			
		}		
		
	}
	
}

if( $agency_alarms_edited==1 ){
	
	// alarms	
	$agency_alarm_approve = $_POST['agency_alarm_approve'];
	$alarm_id = $_POST['alarm_id'];
	$alarm_price = $_POST['alarm_price'];
	$agency_alarms_changed = $_POST['agency_alarms_changed'];	
	$agency_alarms_orig_price = $_POST['agency_alarms_orig_price'];	
	$alarm_checked = $_POST['alarm_checked'];	
	$alarm_name = $_POST['alarm_name'];	
	$alarm_orig = $_POST['alarm_orig'];
	
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
		
	/*	if( $agency_alarms_changed[$manual_index] == 1 ){
			
			// insert logs
			mysql_query("
				INSERT INTO 
				`agency_event_log` 
				(
					`contact_type`,
					`eventdate`,
					`comments`,
					`agency_id`,
					`staff_id`,
					`date_created`,
					`hide_delete`
				) 
				VALUES (
					'Alarm Price Updated',
					'".date('Y-m-d')."',
					'Alarm price has been updated from <strong>\${$agency_alarms_orig_price[$manual_index]}</strong> to <strong>\$".number_format($alarm_price[$manual_index],2)."</strong>',
					'{$agency_id}',
					'".$_SESSION['USER_DETAILS']['StaffID']."',
					'".date('Y-m-d H:i:s')."',
					1
				);
			");
			
		} */
		
	}

	//log for approve/unapprove
	$data_arr = array();
	$data_arr2 = array();
	foreach($alarm_checked as $key => $value){

		if($alarm_orig[$key]!= $value){

			if($value==1){
				$data_arr[] = "<strong>$".number_format($alarm_price[$key],2)."</strong> <strong>".$alarm_name[$key]."</strong> approved";
			}else{
				$data_arr[] = "<strong>$".$agency_alarms_orig_price[$key]."</strong> <strong>".$alarm_name[$key]."</strong> unapproved";
			}

		}else if( $agency_alarms_changed[$key] == 1 ){

			$data_arr2[] = "Alarm price has been updated from <strong>$".$agency_alarms_orig_price[$key]."</strong> to <strong>".number_format($alarm_price[$key],2)."</strong>";
		}

	}

	if(!empty($data_arr2)){
		$aw_text = implode(", ", $data_arr2);
		mysql_query("
			INSERT INTO 
			`agency_event_log` 
			(
				`contact_type`,
				`eventdate`,
				`comments`,
				`agency_id`,
				`staff_id`,
				`date_created`,
				`hide_delete`
			) 
			VALUES (
				'Alarm Price Updated',
				'".date('Y-m-d')."',
				'".$aw_text."',
				'{$agency_id}',
				'".$_SESSION['USER_DETAILS']['StaffID']."',
				'".date('Y-m-d H:i:s')."',
				1
			);
		");
	}
	
	if(!empty($data_arr)){
		$aw_text = implode(", ", $data_arr);
		mysql_query("
		INSERT INTO 
			`agency_event_log` 
			(
				`contact_type`,
				`eventdate`,
				`comments`,
				`agency_id`,
				`staff_id`,
				`date_created`,
				`hide_delete`
			) 
			VALUES (
				'Alarm approved/unapproved',
				'".date('Y-m-d')."',
				'".$aw_text."',
				'{$agency_id}',
				'".$_SESSION['USER_DETAILS']['StaffID']."',
				'".date('Y-m-d H:i:s')."',
				1
			);
		"); 
	}

	
	
}


// deactivate property and cancel jobs if status change from active to target or deactivated
if( $orig_status=='active' && ( $status=="target" || $status=="deactivated" ) ){

	$property_with_active_jobs = [];
	$property_with_active_jobs_url_params = null;

	// get only active properties
	$p_sql = mysql_query("
		SELECT `property_id`
		FROM `property`
		WHERE `agency_id` ={$agency_id}
		AND `deleted` = 0
	");

	if(mysql_num_rows($p_sql)>0){

		while($p = mysql_fetch_array($p_sql)){
			
			if( $p['property_id'] > 0 ){

				$ret = json_decode($crm->NLM_Property($p['property_id']));

				if( $ret->nlm_chk_flag == 1 ){
					$property_with_active_jobs[] = $p['property_id'];
				}

			}			

		}

		$property_with_active_jobs_url_params = http_build_query(array('property_with_active_jobs' => $property_with_active_jobs));

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
			'Agency Changed to {$status}',
			'".date("Y-m-d")."',
			'@ ".date("H:i")."',
			'{$_SESSION['USER_DETAILS']['StaffID']}'
		)
	");

}


echo "<script>window.location='view_agency_details.php?id={$agency_id}&success=1&{$property_with_active_jobs_url_params}'</script>";
?>