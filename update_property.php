<?php

include('inc/init.php');
$crm = new Sats_Crm_Class;

function up_getServiceStatus($serv_status){
	switch($serv_status){
		case 0:
			$serv_stat = 'DIY';
		break;
		case 1:
			$serv_stat = 'SATS';
		break;
		case 2:
			$serv_stat = 'No Response';
		break;
		case 3:
			$serv_stat = 'Other Provider';
		break;
	}
	return $serv_stat;
}

// init the variables
$id = $_POST['id'];
$property_id = $id;
//$new_tenants = 1;
$new_tenants = NEW_TENANTS;
$today = date('Y-m-d');
$today_full = date('Y-m-d H:i:s');

//updateTechSheetAlarmTypes($id, $_POST['alarm_job_type']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";

$address_1 = addslashes($_POST['address_1']);
$address_2 = mysql_prep($_POST['address_2']);
$address_3 = addslashes($_POST['address_3']);
$state = addslashes($_POST['state']);
$postcode = addslashes($_POST['postcode']);

$testing_comments = mysql_prep($_POST['testing_comments']);
$booking_comments = mysql_prep($_POST['booking_comments']);

$tenant_firstname1 = mysql_prep($_POST['tenant_firstname1']);
$tenant_lastname1 = mysql_prep($_POST['tenant_lastname1']);
$tenant_ph1 = addslashes($_POST['tenant_ph1']);
$tenant_mob1 = addslashes($_POST['tenant_mob1']);
$tenant_email1 = addslashes($_POST['tenant_email1']);

$tenant_firstname2 = mysql_prep($_POST['tenant_firstname2']);
$tenant_lastname2 = mysql_prep($_POST['tenant_lastname2']);
$tenant_ph2 = addslashes($_POST['tenant_ph2']);
$tenant_mob2 = addslashes($_POST['tenant_mob2']);
$tenant_email2 = addslashes($_POST['tenant_email2']);

$landlord_firstname = mysql_prep($_POST['landlord_firstname']);
$landlord_lastname = mysql_prep($_POST['landlord_lastname']);
$landlord_email = addslashes($_POST['landlord_email']);
$ll_mobile = addslashes($_POST['ll_mobile']);
$ll_landline = addslashes($_POST['ll_landline']);

$inv_number = addslashes($_POST['inv_number']);

$prop_comments = addslashes($_POST['prop_comments']);

$holiday_rental = addslashes($_POST['holiday_rental']);
$no_keys = addslashes($_POST['no_keys']);

$alarm_code = mysql_real_escape_string($_POST['alarm_code']);
$compass_index_num = mysql_real_escape_string($_POST['compass_index_num']);

//$service = $_POST['service'];
$service = $_POST['radioService'];

$price = $_POST['price'];



// tenants 3
$tenant_firstname3 = mysql_prep($_POST['tenant_firstname3']);
$tenant_lastname3 = mysql_prep($_POST['tenant_lastname3']);
$tenant_ph3 = addslashes($_POST['tenant_ph3']);
$tenant_mob3 = addslashes($_POST['tenant_mob3']);
$tenant_email3 = addslashes($_POST['tenant_email3']);

// tenant 4
$tenant_firstname4 = mysql_prep($_POST['tenant_firstname4']);
$tenant_lastname4 = mysql_prep($_POST['tenant_lastname4']);
$tenant_ph4 = addslashes($_POST['tenant_ph4']);
$tenant_mob4 = addslashes($_POST['tenant_mob4']);
$tenant_email4 = addslashes($_POST['tenant_email4']);

// PMe property ID
$pme_prop_id = ( $_POST['pm_prop_id'] !='' )?"'".mysql_real_escape_string($_POST['pm_prop_id'])."'":'NULL';


$nlm_display = mysql_real_escape_string($_POST['nlm_display']);
$prop_upgraded_to_ic_sa = mysql_real_escape_string($_POST['prop_upgraded_to_ic_sa']);
$pm_id_new = mysql_real_escape_string($_POST['pm_id_new']);


$bne_to_call = mysql_real_escape_string($_POST['bne_to_call']);
$no_dk = mysql_real_escape_string($_POST['no_dk']);
$is_sales = ( $_POST['is_sales'] !='' )?mysql_real_escape_string($_POST['is_sales']):0;
$send_to_email_not_api = ( $_POST['send_to_email_not_api'] !='' )?mysql_real_escape_string($_POST['send_to_email_not_api']):0;


$key_number = mysql_real_escape_string($_POST['key_number']);
$requires_ppe = mysql_real_escape_string($_POST['requires_ppe']);
$manual_renewal = mysql_real_escape_string($_POST['manual_renewal']);
$subscription_billed = mysql_real_escape_string($_POST['subscription_billed']);
$service_garage = mysql_real_escape_string($_POST['service_garage']);
$bne_to_call = mysql_real_escape_string($_POST['bne_to_call']);
$lockbox_code = mysql_real_escape_string($_POST['lockbox_code']);
$agency_price_variation = mysql_real_escape_string($_POST['agency_price_variation']);
$subscription_date = ($_POST['subscription_date'] != "") ? "'" . date("Y-m-d", strtotime(str_replace("/", "-", mysql_real_escape_string($_POST['subscription_date'])))) . "'" : "NULL";
$subscription_source = mysql_real_escape_string($_POST['subscription_source']);
$from_other_company = mysql_real_escape_string($_POST['from_other_company']);

   

   if ($service == "serviceyes")
    {
        $service = "1";
    }
   if ($service == "serviceno")
    {
        $service = "0";
    }
	
	// tenant updated timestamp
	$tenants_changed = $_POST['tenants_changed'];
	
	$append_str = '';
	if($tenants_changed==1){
		$append_str = "		
			, `no_en` = NULL
			, `tenant_changed` = '".date("Y-m-d H:i:s")."'
		";
	}
	
	$address = "{$address_1} {$address_2} {$address_3} {$state} {$postcode}, {$_SESSION['country_name']}";
	$coordinate = getGoogleMapCoordinates($address);
	
	
	
	$no_en = ($tenants_changed==1)?1:0;
	
	
	
	
	
	
	// add property log if someone verified payment on NLM properties
	$prop_sql = mysql_query("
		SELECT 
			`nlm_display`,
			`is_sales`,
			`send_to_email_not_api`,
			`manual_renewal`,
			`bne_to_call`,
			`subscription_billed`,
			`service_garage`,
			`holiday_rental`
		FROM `property`
		WHERE `property_id` = {$id}
	");
	$current_prop = mysql_fetch_array($prop_sql);


	// manual_renewal update log
	if( $current_prop['manual_renewal'] != 1 && $manual_renewal == 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property was marked <b>Manual Renewal</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}else if( $current_prop['manual_renewal'] == 1 && $manual_renewal != 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property was unmarked <b>Manual Renewal</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}


	// bne_to_call update log

	// get active tenants
	$pt_sql = mysql_query("
	SELECT 
		`tenant_firstname`,
		`tenant_lastname`
	FROM `property_tenants` 
	WHERE `property_id` = {$property_id}
	AND `active` = 1
	");

	$active_tenants_arr = [];

	while( $pt_row = mysql_fetch_object($pt_sql) ){
		$active_tenants_arr[] = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";
	}

	if( count($active_tenants_arr) > 0 ){ // has tenants

		$active_tenants_imp = implode(", ",$active_tenants_arr);
		$active_tenants_str = " with active tenants: {$active_tenants_imp}";
		
	}else{ // no tenants

		$active_tenants_str = ' with no active tenants';

	}

	if( $current_prop['bne_to_call'] != 1 && $bne_to_call == 1 ){ // marked

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property marked \'Office to Call\'</b>{$active_tenants_str}', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}else if( $current_prop['bne_to_call'] == 1 && $bne_to_call != 1 ){ // unmarked

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property unmarked \'Office to Call\'</b>{$active_tenants_str}', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}


	// subscription_billed update log
	if( $current_prop['subscription_billed'] != 1 && $subscription_billed == 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property was marked <b>Subscription Billing</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}else if( $current_prop['subscription_billed'] == 1 && $subscription_billed != 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property was unmarked <b>Subscription Billing</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}


	// service_garage update log
	if( $current_prop['service_garage'] != 1 && $service_garage == 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property <b>marked</b> as <b>Attached garage requires alarm</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}else if( $current_prop['service_garage'] == 1 && $service_garage != 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property <b>unmarked</b> as <b>Attached garage requires alarm</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}

	// service_garage update log
	if( $current_prop['holiday_rental'] != 1 && $holiday_rental == 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property <b>marked</b> as <b>Short Term Rental</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}else if( $current_prop['holiday_rental'] == 1 && $holiday_rental != 1 ){

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property Update', 
				'Property <b>unmarked</b> as <b>Short Term Rental</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");

	}
	
	
	if( $current_prop['nlm_display']==1 && $nlm_display=='' ){

		// insert property log

		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'NLM Property', 
				'Payments verified', 
				'".date('Y-m-d H:i:s')."'
			)
		");
		
	}


	if(  $current_prop['is_sales'] != $is_sales ){

		$marked_txt = ( $is_sales == 1 )?'marked':'unmarked';
		
		// insert property log
		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Sales property status updated', 
				'This property was {$marked_txt} as a <b>sales</b> property on the <b>VPD</b> page.', 
				'".date('Y-m-d H:i:s')."'
			)
		");
		
	}


	if(  $current_prop['send_to_email_not_api'] != $send_to_email_not_api ){

		$marked_txt = ( $send_to_email_not_api == 1 )?'marked':'unmarked';
		
		// insert property log
		mysql_query("
			INSERT INTO 
			property_event_log (
				property_id, 
				staff_id, 
				event_type, 
				event_details, 
				log_date
			) 
			VALUES (
				".mysql_real_escape_string($id).", 
				".$staff_id.", 
				'Property API communication updated', 
				'This property was {$marked_txt} as <b>send invoice to email instead of API</b>', 
				'".date('Y-m-d H:i:s')."'
			)
		");
		
	}
	
	$updateQuery = "UPDATE property set 
    address_1='$address_1', 
    address_2=\"$address_2\", 
    address_3='$address_3', 
    state='$state', 
    postcode='$postcode', 
    testing_comments=\"$testing_comments\", 
    booking_comments=\"$booking_comments\", 
	`holiday_rental`='{$holiday_rental}',
	`no_keys`='{$no_keys}',
	
	`lat` = '{$coordinate['lat']}',
	`lng` = '{$coordinate['lng']}',

    landlord_firstname=\"$landlord_firstname\", 
    landlord_lastname=\"$landlord_lastname\", 
    landlord_email='$landlord_email', 
	`landlord_mob` = '{$ll_mobile}',
	`landlord_ph` = '{$ll_landline}',
	
	`nlm_display` = '{$nlm_display}',
	`is_sales` = '{$is_sales}',
	`send_to_email_not_api` = '{$send_to_email_not_api}',
	
	`compass_index_num` = '{$compass_index_num}',
	
    inv_number='$inv_number', 
    price='$price',
	key_number = '$key_number',
	`alarm_code` = '{$alarm_code}',
	`prop_upgraded_to_ic_sa` = '{$prop_upgraded_to_ic_sa}',
	`pm_id_new` = '$pm_id_new',
	`requires_ppe` = '$requires_ppe',
	`manual_renewal` = '$manual_renewal',
	`subscription_billed` = '$subscription_billed',
	`service_garage` = '$service_garage',
	
	`bne_to_call` = '{$bne_to_call}',
	`no_dk` = '{$no_dk}',	
	
	`comments` = '{$prop_comments}'
	{$append_str}
    WHERE (property_id=$id);";
   
	
	//echo "<br>An Error Occured, please copy and paste this into an email: Update Query is:<br><br>$updateQuery<br>\n";
	
	$result = mysql_query($updateQuery, $connection) or die("An Error Occured, please copy and paste this into an email: Update Query is: $updateQuery");

	// subscription section insert/update
	$orig_prop_subs_sql = mysql_query("
	SELECT 
		prop_sub.`subscription_date`,
		prop_sub.`source`,

		sub_source.`source_name`
	FROM `property_subscription` AS prop_sub
	LEFT JOIN `subscription_source` AS sub_source ON prop_sub.`source` = sub_source.`id`
	WHERE prop_sub.`property_id` = {$property_id}
	");

	// get subscription source
	$sub_source_sql = mysql_query("
	SELECT `source_name`
	FROM `subscription_source`
	WHERE `id` = {$subscription_source}
	");
	$sub_source_row = mysql_fetch_object($sub_source_sql);

	if( mysql_num_rows($orig_prop_subs_sql) > 0 ){ // exist, update

		$orig_prop_subs_row = mysql_fetch_object($orig_prop_subs_sql);		

		if( 
			$crm->formatDate($_POST['subscription_date']) != $orig_prop_subs_row->subscription_date && 
			$subscription_source != $orig_prop_subs_row->source
		){ // both

			// insert log
			mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'Property Update',
					'Subscription start date updated from <b>".$crm->formatDate($orig_prop_subs_row->subscription_date, 'd/m/Y')."</b> to <b>".$crm->formatDate($_POST['subscription_date'], 'd/m/Y')."</b> and source updated from <b>{$orig_prop_subs_row->source_name}</b> to <b>{$sub_source_row->source_name}</b>.',
					'{$today_full}',
					1
				)
			");

		}else{ // separate

			// subscription date update
			if( $crm->formatDate($_POST['subscription_date']) != $orig_prop_subs_row->subscription_date ){

				// insert log
				mysql_query("
					INSERT INTO
					`property_event_log`
					(
						`property_id`,
						`staff_id`,
						`event_type`,
						`event_details`,
						`log_date`,
						`hide_delete`
					)
					VALUES(
						{$property_id},
						{$_SESSION['USER_DETAILS']['StaffID']},
						'Property Update',
						'Subscription start date updated from <b>".$crm->formatDate($orig_prop_subs_row->subscription_date, 'd/m/Y')."</b> to <b>".$crm->formatDate($_POST['subscription_date'], 'd/m/Y')."</b>.',
						'{$today_full}',
						1
					)
				");

			}	
			
			// subscription source update
			if( $subscription_source != $orig_prop_subs_row->source ){

				// insert log
				mysql_query("
					INSERT INTO
					`property_event_log`
					(
						`property_id`,
						`staff_id`,
						`event_type`,
						`event_details`,
						`log_date`,
						`hide_delete`
					)
					VALUES(
						{$property_id},
						{$_SESSION['USER_DETAILS']['StaffID']},
						'Property Update',
						'Subscription source updated from <b>{$orig_prop_subs_row->source_name}</b> to <b>{$sub_source_row->source_name}</b>.',
						'{$today_full}',
						1
					)
				");

			}

		}	
		
		// update
		mysql_query("
		UPDATE `property_subscription`
		SET 
			`subscription_date` = {$subscription_date},
			`source` = '{$subscription_source}',
			`date_updated` = '{$today}'
		WHERE `property_id` = {$property_id}
		");

	}else{ // new, insert
		

		// subscription date update
		if( $_POST['subscription_date'] != '' && $subscription_source != '' ){ // both

			// insert log
			mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'Property Update',
					'Subscription start date set to <b>".$crm->formatDate($_POST['subscription_date'], 'd/m/Y')."</b> and source set to <b>{$sub_source_row->source_name}</b>.',
					'{$today_full}',
					1
				)
			");

		}else{ // separate

			// subscription date insert
			if( $_POST['subscription_date'] != '' ){

				// insert log
				mysql_query("
					INSERT INTO
					`property_event_log`
					(
						`property_id`,
						`staff_id`,
						`event_type`,
						`event_details`,
						`log_date`,
						`hide_delete`
					)
					VALUES(
						{$property_id},
						{$_SESSION['USER_DETAILS']['StaffID']},
						'Property Update',
						'Subscription start date set to <b>".$crm->formatDate($_POST['subscription_date'], 'd/m/Y')."</b>',
						'{$today_full}',
						1
					)
				");

			}	
			
			// subscription source insert
			if( $subscription_source != '' ){

				// insert log
				mysql_query("
					INSERT INTO
					`property_event_log`
					(
						`property_id`,
						`staff_id`,
						`event_type`,
						`event_details`,
						`log_date`,
						`hide_delete`
					)
					VALUES(
						{$property_id},
						{$_SESSION['USER_DETAILS']['StaffID']},
						'Property Update',
						'Subscription source set to <b>{$sub_source_row->source_name}</b>.',
						'{$today_full}',
						1
					)
				");

			}


		}	
		
		// insert
		mysql_query("
		INSERT INTO 
		`property_subscription`(
			`property_id`,
			`subscription_date`,
			`source`,
			`date_updated`
		)
		VALUES(
			{$property_id},
			{$subscription_date},
			'{$subscription_source}',
			'{$today}'
		)
		");

	}

	
	// check if lockbox exist
	$lb_sql = mysql_query("
	SELECT COUNT(`id`) AS pl_count
	FROM `property_lockbox`
	WHERE `property_id` = {$id}
	");
	$lb_row = mysql_fetch_object($lb_sql);

	if( $lb_row->pl_count > 0 ){ // it exist, update

		mysql_query("
		UPDATE `property_lockbox`
		SET `code` = '{$lockbox_code}'
		WHERE `property_id` = {$id}
		");

	}else{ // doesnt exist, insert

		if( $lockbox_code != '' ){

			mysql_query("
			INSERT INTO 
			`property_lockbox`(
				`code`,
				`property_id`
			)
			VALUE(
				'{$lockbox_code}',
				{$id}
			)	
			");

		}		

	}
	
	
	// add property services
	$alarm_job_type_id = $_POST['alarm_job_type_id'];
	$price = $_POST['price'];
	$is_updated = $_POST['is_updated'];
	$property_services_id = $_POST['property_services_id'];
	$agency_price = $_POST['agency_price'];
	$service_name = $_POST['service_name'];
	$building_name = mysql_real_escape_string($_POST['building_name']);
	
	
   
   foreach($is_updated as $index=>$val){
   
		$service = $_POST['service'.$index];

		// if updated
		if($val==1){
		
			if($property_services_id[$index]==""){
				
				// new property service
				mysql_query("
					INSERT INTO 
					`property_services`(
						`property_id`,
						`alarm_job_type_id`,
						`service`,
						`price`
					)
					VALUES(
						'".mysql_real_escape_string($id)."',
						'".mysql_real_escape_string($alarm_job_type_id[$index])."',
						'".mysql_real_escape_string($service)."',
						'".mysql_real_escape_string($price[$index])."'
					)
				");
				
			}else{
				
				// if changed to service SATS
				if( $service==1 ){
					$price_update_str = " ,`price` = '{$agency_price[$index]}' ";
				}

				// get status changed date and is payable   
				$this_month_start = date("Y-m-01");
				$this_month_end = date("Y-m-t");    

				$ps_sql_str = "
				SELECT `status_changed`, `is_payable` 
				FROM `property_services`
				WHERE `alarm_job_type_id` = {$alarm_job_type_id[$index]} 
				AND `property_id` = {$id}  
				";        
				$ps_sql = mysql_query($ps_sql_str); 
				$ps_sql_row = mysql_fetch_object($ps_sql);
				$status_changed = date('Y-m-d',strtotime($ps_sql_row->status_changed));

				// If property has job where status = precom | merged | completed && date = this month, then keep is_payable = 1
				$has_completed_jobs_this_month = false;
				$job_sql = mysql_query("
				SELECT COUNT(`id`) AS jcount
				FROM `jobs`
				WHERE `property_id` = {$id}
				AND `del_job` = 0
				AND `status` IN ('Pre Completion','Merged Certificates','Completed')
				AND `date` BETWEEN '{$this_month_start}' AND '{$this_month_end}' 				
				");
				$job_row = mysql_fetch_object($job_sql);

				if( $job_row->jcount > 0 ){
					$has_completed_jobs_this_month = true;
				}				
		
				// if status changed to DIY, OP(or even NR if thats possible) and is payable, clear is_payable
				$is_payable_update_str = null;
				if( 
					$has_completed_jobs_this_month == false && $ps_sql_row->is_payable == 1 && $service != 1 
				){
					$is_payable_update_str = " `is_payable` = 0, ";

					// add log
					// get service type name
					$ps_sql2 = mysql_query("
					SELECT `type`
					FROM `alarm_job_type`			      
					WHERE `id` = {$alarm_job_type_id[$index]}    				
					");
					$ps_sql2_row = mysql_fetch_object($ps_sql2);
					
					// insert log
					mysql_query("
					INSERT INTO 
					property_event_log (
						property_id, 
						staff_id, 
						event_type, 
						event_details, 
						log_date
					) 
					VALUES (
						".mysql_real_escape_string($id).", 
						".$staff_id.", 
						'Property Sales Commission', 
						'Property Service <b>{$ps_sql2_row->type}</b> unmarked <b>payable</b>', 
						'".date('Y-m-d H:i:s')."'
					)
					");

				}
				
				// existing property service
				mysql_query("
					UPDATE `property_services`
					SET `alarm_job_type_id` = '{$alarm_job_type_id[$index]}',
						`service` = '{$service}',
						{$is_payable_update_str}
						`status_changed` = '".date("Y-m-d H:i:s")."'
						{$price_update_str}
					WHERE `property_services_id` = {$property_services_id[$index]}
					AND `property_id` = '{$id}'
				");
				
			}


			// add log
			$service_status = $_POST['service_status'];
			$serv_stat = up_getServiceStatus($service_status[$index]);
			$new_serv_stat = up_getServiceStatus($service);				
			
			$update_log_str = "
				INSERT INTO 
				property_event_log (
					property_id, 
					staff_id, 
					event_type, 
					event_details, 
					log_date
				) 
				VALUES (
					".mysql_real_escape_string($id).", 
					".$staff_id.", 
					'Property Service updated', 
					'{$service_name[$index]} Changed from <b>{$serv_stat}</b> to <b>{$new_serv_stat}</b>', 
					'".date('Y-m-d H:i:s')."'
				)
			";
			mysql_query($update_log_str);
			
		
		}
		
   
   }



   	// get property variation
	$pv_sql = mysql_query("
	SELECT COUNT(`id`) AS pv_count
	FROM `property_variation`
	WHERE `property_id` = {$property_id}                    
	AND `active` = 1
	");
	$pv_row = mysql_fetch_object($pv_sql);

	// get agency price variation
	$apv_sql = mysql_query("
	SELECT 
		apv.`id`,
		apv.`amount`,
		apv.`type`,
		apv.`reason` AS apv_reason,
		apv.`scope`,

		apvr.`reason` AS apvr_reason
	FROM `agency_price_variation` AS apv
	LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
	WHERE apv.`id` = {$agency_price_variation}       
	AND apv.`active` = 1             
	"); 
	$apv_row = mysql_fetch_object($apv_sql);

	if( $pv_row->pv_count > 0 ){ // it exist, update

		/*
		mysql_query("
		UPDATE `property_variation`
		SET `agency_price_variation` = {$agency_price_variation}
		WHERE `property_id` = {$property_id}  
		AND `active` = 1                  
		");
		*/

		// update to SOFT delete, instruction by ben
		mysql_query("
		UPDATE `property_variation`
		SET 
			`active` = 0,
			`deleted_ts` = '{$today_full}'
		WHERE `property_id` = {$property_id}  
		AND `active` = 1                  
		");

		// insert new 
		mysql_query("
		INSERT INTO 
		`property_variation`(
			`property_id`,
			`agency_price_variation`,
			`date_applied`
		)
		VALUES(
			{$property_id},
			{$agency_price_variation},
			'{$today}'
		)                 
		");

		// insert log
		mysql_query("
			INSERT INTO
			`property_event_log`
			(
				`property_id`,
				`staff_id`,
				`event_type`,
				`event_details`,
				`log_date`,
				`hide_delete`
			)
			VALUES(
				{$property_id},
				{$_SESSION['USER_DETAILS']['StaffID']},
				'Property Price Variation',
				'Property price variation updated to <b>\$".number_format($apv_row->amount, 2)."</b> ".( ( $apv_row->type == 1 )?'Discount':'Surcharge' ).".',
				'{$today_full}',
				1
			)
		");

	}else{ // insert

		if( $agency_price_variation > 0 ){
			
			// insert new 
			mysql_query("
			INSERT INTO 
			`property_variation`(
				`property_id`,
				`agency_price_variation`,
				`date_applied`
			)
			VALUES(
				{$property_id},
				{$agency_price_variation},
				'{$today}'
			)                 
			");

			// insert log
			mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'Property Price Variation',
					'Property price variation set to <b>\$".number_format($apv_row->amount, 2)."</b> ".( ( $apv_row->type == 1 )?'Discount':'Surcharge' ).".',
					'{$today_full}',
					1
				)
			");

		}                        

	}
   
	// building name update/insert/delete
	if( $property_id > 0 ){

		$bn_sql = mysql_query("
		SELECT `building_name`
		FROM `other_property_details`
		WHERE `property_id` = {$property_id} 
		"); 

		$bn_sql_row = mysql_fetch_object($bn_sql);

		if( $building_name != '' ){ // update/insert					

			if( mysql_num_rows($bn_sql) > 0 ){ // already exist, update

				mysql_query("
				UPDATE `other_property_details`
				SET `building_name` = '{$building_name}'
				WHERE `property_id` = {$property_id}
				");

				// insert log
				mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$staff_id},
					'Property details update',
					'Building name updated from: <b>".mysql_real_escape_string($bn_sql_row->building_name)."</b> to <b>{$building_name}</b>',
					'".date('Y-m-d H:i:s')."',
					1
				)"
				);

			}else{ // new, insert

				mysql_query("
				INSERT INTO 
				`other_property_details` (
					`property_id`, 
					`building_name`
				) 
				VALUES (
					{$property_id}, 
					'{$building_name}'
				)
				");

				// insert log
				mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$staff_id},
					'Property details update',
					'Building name added: <b>{$building_name}</b>',
					'".date('Y-m-d H:i:s')."',
					1
				)"
				);

			}
			
		}else{ // delete

			if( mysql_num_rows($bn_sql) > 0 ){

				mysql_query("
				DELETE 
				FROM `other_property_details`
				WHERE `property_id` = {$property_id}
				");

				// insert log
				mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$staff_id},
					'Property details update',
					'Building name: <b>".mysql_real_escape_string($bn_sql_row->building_name)."</b> removed',
					'".date('Y-m-d H:i:s')."',
					1
				)"
				);

			}

		}		

	} 
	
	
	// property added from other company
	// check if data already exist
	$pfoc_sql = mysql_query("
	SELECT 
		pfoc.`pfoc_id`,
		pfoc.`company_id`,

		sac.`company_name`
	FROM `properties_from_other_company` AS pfoc
	LEFT JOIN `smoke_alarms_company` AS sac ON pfoc.`company_id` = sac.`sac_id`
	WHERE pfoc.`property_id` = {$property_id}
	AND pfoc.`active` = 1
	");
	$pfoc_row = mysql_fetch_object($pfoc_sql);

	if( $from_other_company > 0 ){

		// get company name
		$company_sql = mysql_query("
		SELECT `company_name`
		FROM `smoke_alarms_company`
		WHERE `sac_id` = {$from_other_company}
		");
		$company_row = mysql_fetch_object($company_sql);

	}

	if( mysql_num_rows($pfoc_sql) > 0 ){ // if exist, update

		if( $from_other_company > 0 ){ // if company is selected

			if( $pfoc_row->company_id != $from_other_company ){

				// insert log
				mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'Property Update',
					'Property detail <b>From Other Company</b> has been updated from <b>".mysql_real_escape_string($pfoc_row->company_name)."</b> to <b>".mysql_real_escape_string($company_row->company_name)."</b>',
					'{$today_full}',
					1
				)
				");

			}

			// deactivate current active one
			mysql_query("
			UPDATE `properties_from_other_company`
			SET `active` = 0
			WHERE `property_id` = {$property_id}
			AND `active` = 1
			AND `pfoc_id` = {$pfoc_row->pfoc_id}
			");

			// insert new
			mysql_query("
			INSERT INTO 
			`properties_from_other_company`(
				`company_id`,
				`property_id`,
				`added_date`
			)
			VALUE(
				{$from_other_company},
				{$property_id},
				'{$today_full}'
			)	
			");						

		}else{ // clear

			mysql_query("
			UPDATE `properties_from_other_company`
			SET `active` = 0
			WHERE `property_id` = {$property_id}
			AND `active` = 1
			");

			// insert log
			mysql_query("
			INSERT INTO
			`property_event_log`
			(
				`property_id`,
				`staff_id`,
				`event_type`,
				`event_details`,
				`log_date`,
				`hide_delete`
			)
			VALUES(
				{$property_id},
				{$_SESSION['USER_DETAILS']['StaffID']},
				'Property Update',
				'This property has been <b>unmarked</b> as acquired from <b>".mysql_real_escape_string($pfoc_row->company_name)."</b>',
				'{$today_full}',
				1
			)
			");

		}
		

	}else{ // doesnt exist, insert

		if( $from_other_company > 0 ){

			mysql_query("
			INSERT INTO 
			`properties_from_other_company`(
				`company_id`,
				`property_id`,
				`added_date`
			)
			VALUE(
				{$from_other_company},
				{$property_id},
				'{$today_full}'
			)	
			");

			// insert log
			mysql_query("
			INSERT INTO
			`property_event_log`
			(
				`property_id`,
				`staff_id`,
				`event_type`,
				`event_details`,
				`log_date`,
				`hide_delete`
			)
			VALUES(
				{$property_id},
				{$_SESSION['USER_DETAILS']['StaffID']},
				'Property Update',
				'This property has been <b>marked</b> as acquired from <b>".mysql_real_escape_string($company_row->company_name)."</b>',
				'{$today_full}',
				1
			)
			");

		}		

	}	


	// get job data
	$pi_job_sql = mysql_query("
	SELECT 
		j.`service` AS jservice,
		j.`created` AS jcreated,      

		p.`property_id`,

		a.`agency_id`
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE p.`property_id` = {$property_id}
	AND j.`del_job` = 0
	AND j.`status` NOT IN ('Pre Completion','Merged Certificates','Completed','Cancelled')
	ORDER BY j.`created` DESC
	LIMIT 1
	");
	$pi_job_row = mysql_fetch_object($pi_job_sql);

	// object parameters
	$agency_ex_obj = (object)[
		'agency_id' => $pi_job_row->agency_id,
		'jcreated' => $pi_job_row->jcreated
	];
	$agency_lvl_ex_obj = (object)[
		'agency_id' => $pi_job_row->agency_id,
		'jcreated' => $pi_job_row->jcreated
	];
	$prop_lvl_ex_obj = (object)[
		'property_id' => $pi_job_row->property_id,
		'jcreated' => $pi_job_row->jcreated
	];
	$serv_lvl_ex_obj = (object)[
		'agency_id' => $pi_job_row->agency_id,
		'jcreated' => $pi_job_row->jcreated,
		'service_type' => $pi_job_row->jservice
	];

	$display_price_increase_warning = 0;
	if(   
		$crm->check_if_job_created_before_agency_exclusion_expired($agency_ex_obj) == true ||
		$crm->check_if_job_created_before_agency_level_variation_expired($agency_lvl_ex_obj) == true ||
		$crm->check_if_job_created_before_property_level_variation_expired($prop_lvl_ex_obj) == true ||
		$crm->check_if_job_created_before_service_level_variation_expired($serv_lvl_ex_obj) == true  
	){
		$display_price_increase_warning = 1;
	}

	
	if (mysql_affected_rows($connection) == 1)
	{
		$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
						VALUES (".$property_id.", ".$staff_id.", 'Property details updated', 'Property details updated', '".date('Y-m-d H:i:s')."')";
		//mysql_query($insertLogQuery, $connection);	

		//header("Location: http://sat.cmcc.com.au/view_property_details.php?id=$id");
		//header("Location: http://localhost/view_property_details.php?id=$id");
		header("Location: " . URL . "view_property_details.php?id=$id&success=1&display_price_increase_warning={$display_price_increase_warning}");
	}
	else
	{
	// echo "Updates to database failed!";
	header("Location: " . URL . "view_property_details.php?id=$id&success=1&display_price_increase_warning={$display_price_increase_warning}");
	//header("Location: http://sat.cmcc.com.au/view_property_details.php?id=$id");
	//echo $updateQuery;
	}
	
//	echo $updateQuery."<br><br>\n\n";


   
?>