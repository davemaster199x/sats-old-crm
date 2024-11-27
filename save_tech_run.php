<?php

include('inc/init.php');

// Initiate job class
$jc = new Job_Class();

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = jFormatDateToBeDbReady($_POST['date']);
$start_point = mysql_real_escape_string($_POST['start_point']);
$end_point = mysql_real_escape_string($_POST['end_point']);
$sub_regions = mysql_real_escape_string($_POST['selected_sub_regions']);
$country_id = $_SESSION['country_default'];
$notes = mysql_real_escape_string($_POST['notes']);
$agency_id = $_POST['agency'];
$agency_ids = implode(",",$agency_id);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$calendar_id = mysql_real_escape_string($_POST['calendar_id']);
$calendar_name = mysql_real_escape_string($_POST['calendar_name']);


// ADD
if($tr_id==""){
	
	// add map route
	$str = "
		INSERT INTO
		`tech_run` (
			`assigned_tech`,
			`date`,
			`sub_regions`,
			`start`,
			`end`,
			`sorted`,
			`country_id`,
			`notes`,
			`agency_filter`
		)
		VALUES(
			{$tech_id},
			'{$date}',
			'{$sub_regions}',
			{$start_point},
			{$end_point},
			1,
			{$country_id},
			'{$notes}',
			'{$agency_ids}'
		)
	";
	
	mysql_query($str);
	$tr_id = mysql_insert_id();
	
	$j_sql = getJobsByRegionSort($tech_id,$date,$sub_regions,$country_id,$agency_ids);
	$last_index = 2;

	$i = 2;
	// insert tech run rows
	while( $j = mysql_fetch_array($j_sql) ){

		$str3 = "
			INSERT INTO
			`tech_run_rows` (
				`tech_run_id`,
				`row_id_type`,
				`row_id`,
				`sort_order_num`,
				`created_date`,
				`status`
			)
			VALUES (
				{$tr_id},
				'job_id',
				{$j['jid']},
				{$i},
				'".date('Y-m-d H:i:s')."',
				1
			)
		";
		mysql_query($str3);
		$i++;
	}

	
	$accomodation = mysql_real_escape_string($_POST['accomodation']);
	$accomodation_id = mysql_real_escape_string($_POST['accomodation_id']);
	$booking_staff = mysql_real_escape_string($_POST['booking_staff']);
	
	$accomodation_str = ($accomodation!="")?"'{$accomodation}'":'NULL';
	$accomodation_id_str = ($accomodation==1 || $accomodation==2)?"'{$accomodation_id}'":'NULL';
	
	
	if( $calendar_id!='' ){ // UPDATE
		mysql_query("
			UPDATE `calendar`
			SET 
				`region` = '{$calendar_name}',
				`accomodation` = {$accomodation_str},
				`accomodation_id` = {$accomodation_id_str},
				`booking_staff` = '{$booking_staff}'
			WHERE `calendar_id` = {$calendar_id}
		");
	}else{ // INSERT
		mysql_query("
			INSERT INTO 
			`calendar` (
				`staff_id`,
				`date_start`,
				`date_finish`,
				`region`,
				 `country_id`, 
				 `date_start_time`, 
				 `date_finish_time`,
				 `accomodation`,
				 `accomodation_id`,
				 `booking_staff`
			 )
			 VALUES(
				'{$tech_id}',
				'{$date}',
				'{$date}',
				'{$calendar_name}',
				'{$country_id}',
				'09:00',
				'17:00',
				{$accomodation_str},
				{$accomodation_id_str},
				'{$booking_staff}'
			 )
		");
	}
	

	
}else{ // UPDATE

	
	// add map route
	$str = "
		UPDATE `tech_run` 
		SET
			`assigned_tech` = {$tech_id},
			`date` = '{$date}',
			`sub_regions` = '{$sub_regions}',
			`agency_filter` = '{$agency_ids}'
		WHERE `tech_run_id` = {$tr_id}
	";
	mysql_query($str);

	// clear tech run row 
	clearTechRunRows($tr_id,$tech_id);
	
	$j_sql = getSTRnewlyAddedListing($tr_id,$tech_id,$date,$sub_regions,$country_id,'','',$agency_ids);

	
	// insert tech run rows
	while( $j = mysql_fetch_array($j_sql) ){

		$str3 = "
			INSERT INTO
			`tech_run_rows` (
				`tech_run_id`,
				`row_id_type`,
				`row_id`,
				`sort_order_num`,
				`dnd_sorted`,
				`created_date`,
				`status`
			)
			VALUES (
				{$tr_id},
				'job_id',
				{$j['jid']},
				999999,
				0,
				'".date('Y-m-d H:i:s')."',
				1
			)
		";
		mysql_query($str3);
		$i++;
	}
	
	// update calendar 
	$accomodation = mysql_real_escape_string($_POST['accomodation']);
	$accomodation_id = mysql_real_escape_string($_POST['accomodation_id']);
	$booking_staff = mysql_real_escape_string($_POST['booking_staff']);
	
	$accomodation_str = ($accomodation!="")?"'{$accomodation}'":'NULL';
	$accomodation_id_str = ($accomodation==1 || $accomodation==2)?"'{$accomodation_id}'":'NULL';
	
	
	mysql_query("
		UPDATE `calendar`
		SET 
			`region` = '{$calendar_name}',
			`accomodation` = {$accomodation_str},
			`accomodation_id` = {$accomodation_id_str},
			`booking_staff` = '{$booking_staff}'
		WHERE `calendar_id` = {$calendar_id}
	");
	
	

	// update notes	
	// get current note
	$tr_notes_sql = mysql_query("
		SELECT `notes`
		FROM `tech_run`
		WHERE `tech_run_id` = {$tr_id}
	");
	$orig_notes = mysql_fetch_array($tr_notes_sql);
	
	// only update if notes is edited
	if( $orig_notes['notes'] != $_POST['notes'] ){
		
		$notes = mysql_real_escape_string($_POST['notes']);
		
		mysql_query("
			UPDATE `tech_run`
			SET 
				`notes` = '{$notes}',
				`notes_updated_ts` = '".date('Y-m-d H:i:s')."',
				`notes_updated_by` = {$staff_id}
			WHERE `tech_run_id` = {$tr_id}
		");
		
	}
	
	

	// update start-end point
	techRunUpdateStartEndPoint($tr_id,$start_point,$end_point);
	
	
}




header("location:/set_tech_run.php?tr_id={$tr_id}&success=1");


?>