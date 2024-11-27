<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$property_id = $_POST['property_id'];
$alarm_job_type_id = $_POST['alarm_job_type_id'];
$job_type = $_POST['job_type'];
$price = $_POST['price'];

$vacant_from = $_POST['vacant_from'];
$vacant_from2 = ($vacant_from!="")?$vacant_from:'';
$new_ten_start = $_POST['new_ten_start'];
$new_ten_start2 = ($new_ten_start!="")?$new_ten_start:'';
$problem = mysql_real_escape_string($_POST['problem']);
$agency_id = $_POST['agency_id'];
$workorder_notes = mysql_real_escape_string($_POST['workorder_notes']);
$comments = "";

$workorder_notes = mysql_real_escape_string($_POST['workorder_notes']);

$onhold_start_date = mysql_real_escape_string($_POST['onhold_start_date']);
$onhold_end_date = mysql_real_escape_string($_POST['onhold_end_date']);

$job_date = mysql_real_escape_string($_POST['job_date']);

$jtech_sel = mysql_real_escape_string($_POST['jtech_sel']);

$work_order = (!empty($_POST['work_order'])) ? mysql_real_escape_string($_POST['work_order']) : 'NULL' ;

$preferred_alarm_id = mysql_real_escape_string($_POST['preferred_alarm_id']);
$vacant_prop = mysql_real_escape_string($_POST['vacant_prop']);

$end_date_str = 'NULL';
$start_date_str = 'NULL';
$job_date_date_str = 'NULL';



switch($job_type){
	case 'Once-off':
		$comments = "{$job_type}";
	break;
	case 'Change of Tenancy':
		
		if( $vacant_from!="" ){
			$start_date = date('Y-m-d',strtotime(str_replace('/','-',$vacant_from)));
			$start_date_str = "'{$start_date}'";
		}else{
			$start_date_str = 'NULL';
		}

		if( $new_ten_start !="" ){
			$end_date = date('Y-m-d',strtotime(str_replace('/','-',$new_ten_start )));
			$end_date_str = "'{$end_date}'";
		}else{
			$end_date_str = 'NULL';
		}

		$no_dates_provided = 0;

		if( $vacant_from=="" && $new_ten_start =="" ){
			$no_dates_provided = 1;
			$comments_temp = 'No Dates Provided';
		}else if( $vacant_from!="" && $new_ten_start =="" ){
			$no_dates_provided = 1;
			$comments_temp = "Vacant from {$vacant_from} - {$problem}";
		}else if( $vacant_from=="" && $new_ten_start !="" ){
			$no_dates_provided = 1;
			$comments_temp = "Book before {$new_ten_start} - {$problem}";			
		}else{
			$no_dates_provided = 0;
			$comments_temp = "Vacant from {$vacant_from} - {$new_ten_start } {$problem}";
		}
		
		$comments = "COT {$comments_temp}"; 
		
		

		
	break;
	case 'Yearly Maintenance':
		$j_sql = mysql_query("
			SELECT *
			FROM `jobs`
			WHERE `property_id` = {$property_id}
			AND `del_job` = 0
		");	

	break;
	case 'Fix or Replace':
	
		if( $new_ten_start2 != '' ){
			$temp = " New Tenancy Starts ".$new_ten_start2.",";
		}else{
			$temp = ',';
		}		
		$comments = "{$job_type}{$temp} Comments: <strong>{$problem}</strong>";
	break;
	case '240v Rebook':

		$comments = "{$job_type}";
	break;
	case 'Lease Renewal':
	
		
		if( $new_ten_start!="" ){
			$end_date = date('Y-m-d',strtotime(str_replace('/','-',$new_ten_start)));
			$end_date_str = "'{$end_date}'";
			$start_date = date('Y-m-d',strtotime("{$end_date} -30 days"));
			$start_date_str = "'{$start_date}'";
			$start_date_txt = date('d/m/Y',strtotime("{$end_date} -30 days"));
		}else{
			$end_date_str = 'NULL';
			$start_date_str = 'NULL';
		}
		
		
		$no_dates_provided = 0;
		
		if( $new_ten_start=="" ){
			$no_dates_provided = 1;
			$comments_temp = 'No Dates Provided';
		}else{
			$no_dates_provided = 0;
			$comments_temp = "{$start_date_txt} - {$new_ten_start} {$problem}";
		}
		
		$comments = "LR {$comments_temp}"; 
		
	
	break;
	case 'Annual Visit':

		$comments = "{$job_type}";
	break;
	case 'IC Upgrade':

		//$comments = "{$job_type}";		

		// update preferred_alarm_id
		if( $property_id > 0 && $preferred_alarm_id > 0 ){

			// get property `qld_new_leg_alarm_num`
			$prop_sql = mysql_query("
			SELECT `qld_new_leg_alarm_num`
			FROM `property`
			WHERE `property_id` = {$property_id}
			");
			$prop_row = mysql_fetch_array($prop_sql);

			// get alarm details
			$alarm_pwr_sql = mysql_query("
			SELECT `alarm_pwr_id`, `alarm_pwr`, `alarm_make`
			FROM alarm_pwr
			WHERE `alarm_pwr_id` = {$preferred_alarm_id}
			");
			$alarm_pwr_row = mysql_fetch_array($alarm_pwr_sql);

			if( $alarm_pwr_row['alarm_pwr_id'] == 10 ){
				$alar_pwr_comb = "{$alarm_pwr_row['alarm_pwr']} ({$alarm_pwr_row['alarm_make']})";
			}else{
				$alar_pwr_comb = $alarm_pwr_row['alarm_pwr'];
			}

			$comments = "IC Upgrade created preferring <b>{$prop_row['qld_new_leg_alarm_num']}</b>, <b>{$alar_pwr_comb}</b> alarms";

			// update preferred_alarm_id
			mysql_query("
			UPDATE `property`
			SET `preferred_alarm_id` = {$preferred_alarm_id}
			WHERE `property_id` = {$property_id}
			");

		}else{

			// update preferred alarm to Emerald Planet(EP), if job type is 'C Upgrade' and is_sales = 1
			if( $job_type == 'IC Upgrade' ){
				
				mysql_query("
				UPDATE `property`
				SET `preferred_alarm_id` = 22
				WHERE `property_id` = {$property_id}
				AND `is_sales` = 1
				");

			}			

		}

	break;
}



if( $_POST['job_status'] != '' ){
	$job_status = mysql_real_escape_string($_POST['job_status']);
}else{
	$job_status = "To Be Booked"; // default
}


if( $job_status == 'On Hold' || $vacant_prop == 1 ){

	$start_date_str = ( $onhold_start_date != '' )?"'".date('Y-m-d',strtotime(str_replace('/','-',$onhold_start_date)))."'":'NULL';
	$end_date_str = ( $onhold_end_date != '' )?"'".date('Y-m-d',strtotime(str_replace('/','-',$onhold_end_date)))."'":'NULL';

}else if( $job_status == 'Completed' ){

	$job_date_date_str = ( $job_date != '' )?"'".date('Y-m-d',strtotime(str_replace('/','-',$job_date)))."'":'NULL';

	$assigned_tech_field_str = 'assigned_tech,';
	$assigned_tech_val_str = "{$jtech_sel},";

}else if($job_status=="Allocate"){ ## Per Bens request > Added by gherx March 19, 21 (set allocate_opt to 3 to force show Allocate Fancybox Response with Staff to Notify field)
	##$allocate_opt_field = 'allocate_opt,'; #disable/revert as per Ben's request
	##$allocate_opt_val = '3,'; ##disable/revert as per Ben's request
}


//echo "Job Type: ".$job_type."<br />";

// get price increase excluded agency
$piea_sql_str = "
SELECT *
FROM `price_increase_excluded_agency`
WHERE `agency_id` = {$agency_id}
AND (
	`exclude_until` >= '".date('Y-m-d')."' OR
	`exclude_until` IS NULL
)
";
$piea_sql = mysql_query($piea_sql_str);  

$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;

if( $is_price_increase_excluded == 1 ){ // orig
	$price2 = ($job_type=="Yearly Maintenance"||$job_type=="Once-off")?$price:0;
}else{ // new price, price variation

	// $price_var_params = array(
	// 	'service_type' => $alarm_job_type_id,
	// 	'agency_id' => $property_id
	// );
	// $price_var_arr = $crm->get_property_price_variation($price_var_params);
	// $price2 = ($job_type=="Yearly Maintenance"||$job_type=="Once-off")?$price_var_arr['dynamic_price_total']:0;
	$price2 = ($job_type=="Yearly Maintenance"||$job_type=="Once-off")?$price:0;

}    
//echo $price2 = ($job_type=="Yearly Maintenance"||$job_type=="Once-off")?$price:0;

/*
// if job type is 'Fix or Replace' set it as urgent
if( $job_type == 'Fix or Replace' ){
	$urg_field = " `urgent_job`, `urgent_job_reason`, ";
	$urg_val = " 1, 'URGENT REPAIR', ";

}
*/





// get Franchise Group
$agen_sql = mysql_query("
	SELECT `franchise_groups_id`
	FROM `agency`
	WHERE `agency_id` = {$agency_id}
");
$agen = mysql_fetch_array($agen_sql);

// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
if( isDHAagenciesV2($agen['franchise_groups_id'])==true || agencyHasMaintenanceProgram($agency_id)==true ){
	$dha_need_processing = 1;
}

// if workorder exist it overrides job comments
if( $workorder_notes != '' ){
	$comments = $workorder_notes;
}




$sql = "INSERT INTO 
	jobs (
		`job_type`, 
		`property_id`, 
		`status`,
		`service`,
		{$urg_field}
		`job_price`,
		`comments`,
		`start_date`, 
		`due_date`, 
		`no_dates_provided`,
		`property_vacant`,
		`dha_need_processing`,	
		{$assigned_tech_field_str}	
		`date`,
		`work_order`		
	) 
	VALUES (
		'{$job_type}', 
		'{$property_id}', 
		'{$job_status}',
		'{$alarm_job_type_id}',
		{$urg_val}
		'{$price2}',
		'{$comments}',
		{$start_date_str}, 
		{$end_date_str}, 
		'{$no_dates_provided}',
		'{$vacant_prop}',
		'{$dha_need_processing}',
		{$assigned_tech_val_str}
		{$job_date_date_str},
		'{$work_order}'
	)";
mysql_query($sql);

// job id
$job_id = mysql_insert_id();


// AUTO - UPDATE INVOICE DETAILS
$crm->updateInvoiceDetails($job_id);

			
//$service_name = $_POST['service_name'];	
		
// insert job logs
mysql_query("
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`eventtime`,
		`comments`,
		`job_id`,
		`staff_id`
	) 
	VALUES (
		'<strong>{$job_type}</strong> Job Created',
		'" . date('Y-m-d') . "',
		'" . date('H:i') . "',
		'{$comments}', 
		'{$job_id}',
		'{$_SESSION['USER_DETAILS']['StaffID']}'
	)
");

	
//$sql;

// get alarm job type
$ajt_sql = mysql_query("
	SELECT *
	FROM `alarm_job_type`
	WHERE `id` = {$alarm_job_type_id}
");
$ajt = mysql_fetch_array($ajt_sql);


// if bundle
if($ajt['bundle']==1){
	$b_ids = explode(",",trim($ajt['bundle_ids']));
	// insert bundles
	foreach($b_ids as $val){
		mysql_query("
			INSERT INTO
			`bundle_services`(
				`job_id`,
				`alarm_job_type_id`
			)
			VALUES(
				{$job_id},
				{$val}
			)
		");
		
		
		$bundle_id = mysql_insert_id();
		$bs_id = $bundle_id;
		$bs2_sql = getbundleServices($job_id,$bs_id);
		$bs2 = mysql_fetch_array($bs2_sql);
		$ajt_id = $bs2['alarm_job_type_id'];
		
		//echo "Job ID: {$job_id} - ajt ID: {$alarm_job_type_id} Bundle ID: {$bundle_id} <br />";
		
		// sync alarm
		runSync($job_id,$ajt_id,$bundle_id);

	}	
}else{
	runSync($job_id,$alarm_job_type_id);
}


if( 
	( $job_type == 'Change of Tenancy' ||  $job_type == 'Lease Renewal' ) && $crm->findExpired240vAlarm($job_id) == true ||
	( $job_type == 'Fix or Replace' && $crm->getAll240vAlarm($job_id) == true )
){
	mysql_query("
		UPDATE `jobs` 
		SET `comments` = '240v REBOOK - {$comments}'
		WHERE `id` = {$job_id}
	");
}



mysql_query("
	INSERT INTO 
	`property_propertytype` (
		`property_id`,
		`alarm_job_type_id`
	)
	VALUES (
		'".mysql_real_escape_string($property_id)."',
		'".mysql_real_escape_string($alarm_job_type_id)."'
	)
");


// add logs
//$service_name = $_POST['service_name'];
$staff_id = $_POST['staff_id'];

// if preferred_alarm_id selected
if( $job_type == 'IC Upgrade' && $preferred_alarm_id > 0 ){

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
			".$property_id.",
			".$staff_id.",
			'{$ajt['type']} Job Created',
			'{$comments}',
			'".date('Y-m-d H:i:s')."'
		)
	");

}else{ // default

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
			".$property_id.",
			".$staff_id.",
			'{$ajt['type']} Job Created',
			'{$job_type}',
			'".date('Y-m-d H:i:s')."'
		)
	");

}


// clear tenant details
$delete_tenant = $_POST['delete_tenant'];
if($delete_tenant==1){
	
	/*
	mysql_query("
		UPDATE `property`
		SET 
			`tenant_firstname1` = '',
			`tenant_lastname1` = '',
			`tenant_ph1` = '',
			`tenant_email1` = '',
			`tenant_mob1` = '',
			`tenant_firstname2` = '',
			`tenant_lastname2` = '',
			`tenant_ph2` = '',
			`tenant_email2` = '',
			`tenant_mob2` = ''
		WHERE `property_id` = {$property_id}
	");
	*/
	
	mysql_query("
		UPDATE `property_tenants`
		SET `active` = 0
		WHERE `property_id` = {$property_id}
	");

}


// EO - 'Electrician Only' check
$crm->mark_is_eo($job_id);


?>