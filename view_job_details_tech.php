<style>
#uploading-screen {
	width: 100%;
	height: 100%;
	/*background: url("/images/uploading.gif") no-repeat center center #fff;*/
	background-color: rgb(119, 119, 119);
	position: fixed;
	/*opacity: 0.85;*/
	opacity: 0.7;
	display:none;
	z-index: 9999;
}
.uploading_image_gif{
	position:fixed;
    left:45%; 
    top:45%;
	z-index: 999999;
	border-radius: 10px;
	display: none;
}
.kn_msg{
	display: none;
}
.img_check {
    width: 23px;
	position: relative;
	top: 5px;
}
</style>
<img src="images/uploading_cropped.gif" class="uploading_image_gif" id="uploading_image_gif" />
<div id="uploading-screen">&nbsp;</div>
<?

# This is truly awful code, and I'm very sorry
$title = "View Job Details - Technician Sheet";

define("TECH_SHEET_INC", true);

# extra check to ensure no unautherized access
if ($_SERVER['HTTP_REFERER'] == "") {
	# if not allowed redirect to main with error banner - and exit so no more scripts can run
	header("location: main.php?restricted=1");
	exit();
}

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');


$crm = new Sats_Crm_Class;

// logged staff name
$logged_staff_name = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);
$today = date('d/m/Y');


#Get appliance  / alarm pwr
$alarm_pwr_appliances = alarmGetAlarmPower(1);
$alarm_pwr = alarmGetAlarmPower(2);

#Get appliance / alarm type
$alarm_type_appliances = alarmGetAlarmType(1);
$alarm_type = alarmGetAlarmType(2);

#Get appliance / alarm reason
$alarm_reason_appliances = alarmGetAlarmReason(1);
$alarm_reason = alarmGetAlarmReason(2);

# Safety Switch alarm type
$alarm_type_safety_switch = alarmGetAlarmType(4);

# Corded Window alarm type
$alarm_type_corded_window = alarmGetAlarmType(6);

$job_id = $_GET['id'];
$doaction = $_GET['doaction'];


function getDynamicPropertyAlarms($agency_id){
	return mysql_query("
		SELECT * 
		FROM `agency_alarms` AS aa
		LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
		WHERE aa.`agency_id` = {$agency_id}
	");		
}




// This code is updated from previous job. inc/functions.php

function syncTechSheetFields2($job_id, $property_id)
{
	// Retrieve previously completed jobs fields
	$sql = "
	SELECT *
	FROM jobs 
	WHERE property_id = {$property_id}
	AND id < {$job_id} 
	AND ts_signoffdate IS NOT NULL 
	ORDER BY id DESC LIMIT 1";

	$previous_job = mysqlSingleRow($sql);



	if(isset($previous_job['id']))
	{
		// Update fields if the job is still new
		$sql = "UPDATE jobs SET 
		survey_numlevels = '" . $previous_job['survey_numlevels'] . "', 
		survey_ceiling = '" . $previous_job['survey_ceiling'] . "', 
		survey_ladder = '" . $previous_job['survey_ladder'] . "',
		ts_safety_switch = '" . $previous_job['ts_safety_switch'] . "', 
		ss_location = '" . $previous_job['ss_location'] . "',
		ss_quantity = '" . $previous_job['ss_quantity'] . "', 
		ts_safety_switch_reason = '" . $previous_job['ts_safety_switch_reason'] . "'	
		WHERE  id = {$job_id} AND ts_signoffdate IS NULL";

		mysql_query($sql);
	}

	return true;
}

// get service
$job_sql = mysql_query("SELECT * FROM `jobs` WHERE `id` = {$job_id}");
$job = mysql_fetch_array($job_sql);

//echo "service: ".$job['service'] ."<br/ >";


// get service
$serv_sql2 = mysql_query("
	SELECT *
	FROM `alarm_job_type`
	WHERE `id` = {$job['service']}
");
$serv2 = mysql_fetch_array($serv_sql2);

//echo "bundle: ".$serv2['bundle']." <br />";

// bundle
if($serv2['bundle']==1){	
	$bs_id = $_GET['bundle_id'];
	$bs2_sql = getbundleServices($job_id,$bs_id);
	$bs2 = mysql_fetch_array($bs2_sql);
	$service = $bs2['alarm_job_type_id'];
	$bundle_id = $bs2['bundle_services_id'];
}else{
	// single service, not bundle
	$service = $job['service'];
	$bundle_id = $_GET['bundle_id'];
}

//echo "current service tab: {$service}<br />";
//echo "bundle_service_id: {$bundle_id}<br />";

$job_id = $_GET['id'];
$jserv = $service;


runSync($job_id,$jserv,$bundle_id);

$job_details = getJobDetailsTechSheet($job_id);



// service color
$serv_clr_arr = $crm->getServiceColors($service);
$serv_class_color = $serv_clr_arr['serv_class_color'];
$service_color = $serv_clr_arr['bg_color'];

// get services includes
$tech_tabs_sql = mysql_query("
	SELECT *
	FROM `alarm_job_type`
	WHERE `id` = {$service}
");


$job_tech_sheet_job_type_keys = getTechSheetAlarmTypesJob($job_details['property_id'], true);

$next_item_number = getNextItemNumber($job_id);

# Set sign off date to day
$job_details['ts_signoffdate'] = date('d/m/Y');



# Process existing alarm delete
if (is_numeric($_GET['delalarm'])) {
	$query = "DELETE FROM alarm WHERE alarm_id = " . $_GET['delalarm'] . " AND job_id = " . $job_id . " AND ts_added = 1 LIMIT 1";
	mysql_query($query) or die(mysql_error());
}














if ($_POST && $_GET['action'] == "update") {
	
	


	// save timestamp, when completed
	if($_POST['btn_comp_ts_submit']==1){
		
		// clear not completed reason
		mysql_query("
			UPDATE `jobs`
			SET 
				`job_reason_id` = NULL,
				`job_reason_comment` = NULL
			WHERE `id` = {$job_id}
		");
		
		
		
	}
	

	$error_array = array();

	# Trim all $_POST data
	$_POST = trimData($_POST);

	# Slash Data
	$_POST = addSlashesData($_POST);

	// Required fields popualted based on which tabs are available in this job
	$required_sheet_fields = array();

	/* SAFETY SWITCH TYPE VALIDATION / UPDATES */
	# Update Appliances
	if(array_key_exists(4, $job_tech_sheet_job_type_keys) || array_key_exists(5, $job_tech_sheet_job_type_keys))
	{
		if (sizeof($_POST['ss_alarm_id']) > 0) {
			# Update existing alarm details
			for ($x = 0; $x < sizeof($_POST['ss_alarm_id']); $x++) {
				# Make sure the alarm ID is set and a position has been entered
				if (intval($_POST['ss_alarm_id'][$x]) > 0) {
					
					$query = "
						UPDATE alarm SET 
						pass = '" . $_POST['ss_pass'][$x] . "',
						alarm_type_id = '" . $_POST['ss_type'][$x] . "',
						ts_comments = '" . $_POST['ss_comments'][$x] . "',
						ts_trip_rate = '" . $_POST['ss_trip_rate'][$x] . "'
						
					WHERE job_id = '$job_id' AND alarm_id = '" . $_POST['ss_alarm_id'][$x] . "' LIMIT 1";

					mysql_query($query) or die(mysql_error());
				}
			}
		}
	}
	/* /END SAFETY SWITCH TYPE VALIDATION / UPDATES */

	/* SAFETY SWITCH TYPE VALIDATION / UPDATES */
	# Update SS
	if(array_key_exists(6, $job_tech_sheet_job_type_keys))
	{
		if (sizeof($_POST['corded_window_id']) > 0) {
			# Update existing alarm details
			for ($x = 0; $x < sizeof($_POST['corded_window_id']); $x++) {
				# Make sure the alarm ID is set and a position has been entered
				if (intval($_POST['corded_window_id'][$x]) > 0) {

					$query = "
						UPDATE alarm SET 
						pass = '" . $_POST['corded_window_pass'][$x] . "',
						alarm_type_id = '" . $_POST['corded_window_type'][$x] . "',
						ts_height = '" . $_POST['corded_window_height'][$x] . "',
						ts_opening = '" . $_POST['corded_window_opening'][$x] . "',
						ts_pass_reason = '" . $_POST['corded_window_pass_reason'][$x] . "'
						
					WHERE job_id = '$job_id' AND alarm_id = '" . $_POST['corded_window_id'][$x] . "' LIMIT 1";

					mysql_query($query) or die(mysql_error());
				}
			}
		}
	}
	/* /END SAFETY SWITCH TYPE VALIDATION / UPDATES */

	/* TEST AND TAG JOB TYPE VALIDATION / UPDATES */
	# Update Appliances
	if(array_key_exists(1, $job_tech_sheet_job_type_keys))
	{

		if (sizeof($_POST['appliance_alarm_id']) > 0) {
			# Update existing alarm details
			for ($x = 0; $x < sizeof($_POST['appliance_alarm_id']); $x++) {
				# Make sure the alarm ID is set and a position has been entered
				if (intval($_POST['appliance_alarm_id'][$x]) > 0) {
					
					$query = "
						UPDATE alarm SET 
						alarm_power_id = '" . $_POST['appliance_pwr'][$x] . "',
						alarm_type_id = '" . $_POST['appliance_alarm_type'][$x] . "',
						pass = '" . $_POST['appliance_pass'][$x] . "',
						alarm_reason_id = '" . $_POST['appliance_reason'][$x] . "',
						make = '" . $_POST['appliance_make'][$x] . "',
						model = '" . $_POST['appliance_model'][$x] . "',
						expiry = '" . $_POST['appliance_exp'][$x] . "',
						alarm_price = '" . $_POST['appliance_alarm_price'][$x] . "',
						ts_comments = '" . $_POST['appliance_ts_comments'][$x] . "',
						ts_location = '" . $_POST['appliance_ts_location'][$x] . "'
						
					WHERE job_id = '$job_id' AND alarm_id = '" . $_POST['appliance_alarm_id'][$x] . "' LIMIT 1";
					mysql_query($query) or die(mysql_error());
				}
			}
		}

		// Required Fields for Test & Tag
		$required_sheet_fields[] = array('ts_signoffdate' => 'Sign off Date'); 
		$required_sheet_fields[] = array('ts_items_tested' => 'Items Tested'); 
		$required_sheet_fields[] = array('ts_techconfirm' => 'Confirmation Check box');
	}
	/* /END TEST AND TAG JOB TYPE VALIDATION / UPDATES */


	/* SMOKE & CO ALARM TYPE VALIDATION / UPDATES */
	if( $service==2 || $service==12 ){
	
		
		# Update New Alarms
		if (sizeof($_POST['alarm_alarm_id']) > 0) {
			# Update existing alarm details
			for ($x = 0; $x < sizeof($_POST['alarm_alarm_id']); $x++) {
				# Make sure the alarm ID is set and a position has been entered
				if (intval($_POST['alarm_alarm_id'][$x]) > 0) {
					$query = "
					
					UPDATE alarm SET 
						new = '" . $_POST['alarm_new'][$x] . "',
						alarm_reason_id = '" . $_POST['alarm_reason'][$x] . "',
						alarm_power_id = '" . $_POST['alarm_pwr'][$x] . "',
						alarm_type_id = '" . $_POST['alarm_type'][$x] . "',
						make = '" . $_POST['alarm_make'][$x] . "',
						model = '" . $_POST['alarm_model'][$x] . "',
						expiry = '" . $_POST['alarm_exp'][$x] . "',
						ts_position = '" . $_POST['alarm_position'][$x] . "',
						ts_db_rating = '" . $_POST['alarm_db_rating'][$x] . "',
						ts_required_compliance = '" . $_POST['alarm_compliance'][$x] . "',
						ts_alarm_sounds_other = '" . $_POST['ts_is_alarm_ic'][$x] . "'


					WHERE job_id = '$job_id' AND alarm_id = '" . $_POST['alarm_alarm_id'][$x] . "' LIMIT 1";



					mysql_query($query) or die(mysql_error());
				}
			}
		}

		# Update Existing Alarms
		if (intval($_POST['alarm_count']) > 0) 
		{

			# Update existing alarm details
			for ($x = 0; $x < $_POST['alarm_count']; $x++) {
				# Make sure the alarm ID is set and a position has been entered
				if (intval($_POST['alarm_alarm_id_' . $x]) > 0) {
					$query = "
					
					UPDATE alarm SET 
						`alarm_power_id` = '" . $_POST['ext_alarm_pw_' . $x] . "',
						ts_position = '" . $_POST['alarm_ts_position_' . $x] . "',
						ts_fixing = '" . $_POST['alarm_ts_fixing_' . $x] . "',
						ts_cleaned = '" . $_POST['alarm_ts_cleaned_' . $x] . "',
						ts_newbattery = '" . $_POST['alarm_ts_newbattery_' . $x] . "',
						ts_testbutton = '" . $_POST['alarm_ts_testbutton_' . $x] . "',
						ts_visualind = '" . $_POST['alarm_ts_visualind_' . $x] . "',
						ts_simsmoke = '" . $_POST['alarm_ts_simsmoke_' . $x] . "',
						ts_checkeddb = '" . $_POST['alarm_ts_checkeddb_' . $x] . "',
						ts_expiry = '" . $_POST['alarm_ts_expiry_' . $x] . "',
						ts_discarded = '" . $_POST['alarm_ts_discarded_' . $x] . "',
						ts_meetsas1851 = '" . $_POST['alarm_ts_meetsas1851_' . $x] . "',
						ts_db_rating = '" . $_POST['alarm_ts_db_rating_' . $x] . "',
						ts_required_compliance = '" . $_POST['required_compliance_' . $x] . "',
						ts_discarded_reason = '" . $_POST['alarm_ts_discarded_reason_' . $x] . "',
						ts_alarm_sounds_other = '" . $_POST['alarm_sounds_other_' . $x] . "',
						alarm_type_id = '" . $_POST['alarm_type_id_' . $x] . "'

						
					WHERE job_id = '$job_id' AND alarm_id = '" . $_POST['alarm_alarm_id_' . $x] . "' LIMIT 1";

					mysql_query($query) or die(mysql_error());

			
				}
			}
		}
	
	}
		

	
		
		
	
	/* END SMOKE & CO ALARM TYPE VALIDATION / UPDATES */

	/* SAFETY SWITCH - VIEW VALIDATION */
	if(array_key_exists(3, $job_tech_sheet_job_type_keys))
	{
		$required_sheet_fields[] = array('ss_location' => 'Safety Switch View - Fuse Box Location');
		$required_sheet_fields[] = array('ss_quantity' => 'Safety Switch View - Quantity'); 
	}


	if(array_key_exists(4, $job_tech_sheet_job_type_keys) || array_key_exists(5, $job_tech_sheet_job_type_keys) )
	{
		$required_sheet_fields[] = array('ss_location' => 'Safety Switch Test - Fuse Box Location');
	}

	# Determine any errors, also set #job_details field to the $_POST value where necessary
	foreach ($required_sheet_fields as $int => $array) {
		reset($array);
		$field = key($array);

		if (!isset($_POST[$field]) || $_POST[$field] == "")
			$error_array[] = $array[$field];
		$job_details[$field] = $_POST[$field];
	}

	# Set any leftover fields
	$job_details['ts_additionalnotes'] = $_POST['ts_additionalnotes'];



	# If no errors update database
	if ( $service==2 || $service==12 ) {
		
		if( $_POST['ts_ic_alarm_confirm']==1 ){
			$ic_alarm_str = " `ts_ic_alarm_confirm` = '" . mysql_real_escape_string($_POST['ts_ic_alarm_confirm']) . "' , "; 
		}

		# Main Jobs record
		$query = "UPDATE jobs SET 
				survey_numlevels = '" . mysql_real_escape_string($_POST['survey_numlevels']) . "',
				survey_numalarms = '" . mysql_real_escape_string($_POST['survey_numalarms']) . "',
				survey_ceiling =  '" . mysql_real_escape_string($_POST['survey_ceiling']) . "',
				survey_alarmspositioned = '" . mysql_real_escape_string($_POST['survey_alarmspositioned']) . "',
				survey_ladder = '" . mysql_real_escape_string($_POST['survey_ladder']) . "',
				survey_minstandard = '" . mysql_real_escape_string($_POST['survey_minstandard']) . "',
				tech_comments = '" . mysql_real_escape_string($_POST['tech_comments']) . "',
				ts_batteriesinstalled = '" . mysql_real_escape_string($_POST['ts_batteriesinstalled']) . "',
				ts_signoffdate = '" . mysql_real_escape_string($_POST['ts_signoffdate']) . "',
				ts_alarmsinstalled = '" . mysql_real_escape_string($_POST['ts_alarmsinstalled']) . "',
				ts_items_tested = '" . mysql_real_escape_string($_POST['ts_items_tested']) . "',
				{$ic_alarm_str}
				ts_techconfirm = '" . mysql_real_escape_string($_POST['ts_techconfirm']) . "' ,
				ts_safety_switch = '" . mysql_real_escape_string($_POST['ts_safety_switch']) . "',
				ss_location = '" . mysql_real_escape_string($_POST['ss_location']) . "',
				ss_quantity = '" . mysql_real_escape_string($_POST['ss_quantity']) . "',
				ts_safety_switch_reason = '" . mysql_real_escape_string($_POST['safety_switch_reason']) . "',
				ps_number_of_bedrooms = '" . mysql_real_escape_string($_POST['ps_number_of_bedrooms']) . "',
				
				`swms_heights` = '" . mysql_real_escape_string($_POST['swms_heights']) . "',
				`swms_uv_protection` = '" . mysql_real_escape_string($_POST['swms_uv_protection']) . "',
				`swms_asbestos` = '" . mysql_real_escape_string($_POST['swms_asbestos']) . "',
				`swms_powertools` = '" . mysql_real_escape_string($_POST['swms_powertools']) . "',
				`swms_animals` = '" . mysql_real_escape_string($_POST['swms_animals']) . "',
				`swms_live_circuit` = '" . mysql_real_escape_string($_POST['swms_live_circuit']) . "',
				
				`entry_gained_via` = '" . mysql_real_escape_string($_POST['entry_gained_via']) . "',
				`entry_gained_other_text` = '" . mysql_real_escape_string($_POST['entry_gained_other_text']) . "',
				
				`tech_comments` = '" . mysql_real_escape_string($_POST['tech_comments']) . "',
				`repair_notes` = '" . mysql_real_escape_string($_POST['repair_notes']) . "'
				
				WHERE id = '" . $job_id . "' LIMIT 1";

		mysql_query($query) or die(mysql_error());
		
		
		

		# Success
		$success = 1;

		# Reload Job Details
		//$job_details = getJobDetailsTechSheet($job_id);
	}

	# Remove slashes to send back to form
	//$job_details = stripSlashesData($job_details);
	$_POST = stripSlashesData($_POST);
	
	
	// update property comments
	$prop_comments = mysql_real_escape_string($_REQUEST['prop_comments']);
	$key_num = mysql_real_escape_string($_REQUEST['key_num']);
	$prop_id = mysql_real_escape_string($_REQUEST['prop_id']);
	$alarm_code = mysql_real_escape_string($_REQUEST['alarm_code']);
	
	$p_update_str = '';
	
	if( $_POST['prop_upgraded_to_ic_sa']!='' ){
		$p_update_str  .= ",`prop_upgraded_to_ic_sa` = '{$_POST['prop_upgraded_to_ic_sa']}'";
	}
	
	if( $_POST['qld_new_leg_alarm_num']!='' ){
		$p_update_str  .= ",`qld_new_leg_alarm_num` = '{$_POST['qld_new_leg_alarm_num']}'";
	}
	
	mysql_query("
		UPDATE `property`
		SET 
			`comments` = '{$prop_comments}',	
			`key_number` = '{$key_num}',
			`alarm_code` = '{$alarm_code}'
			{$p_update_str}
		WHERE `property_id` = {$prop_id}
	");
	
	
	$work_order = mysql_real_escape_string($_REQUEST['work_order']);


	// if it has leak notes from WE, combined with job notes
	$tech_comments = mysql_real_escape_string($_POST['tech_comments']);
	$leak_notes = mysql_real_escape_string($_POST['leak_notes']);

	if( $leak_notes != '' ){
		$job_notes = "{$tech_comments} --- Check leak notes!";
	}else{
		$job_notes = $tech_comments;
	}	

	// update job
	mysql_query("
		UPDATE `jobs`
		SET 
			`work_order` = '{$work_order}',
			`tech_comments` = '{$job_notes}',
			`ts_signoffdate` = '" . mysql_real_escape_string($_POST['ts_signoffdate']) . "'
		WHERE `id` = {$job_id}
	");
	
	
	
	
	
	
	
	// if completed checkbox is checked
	if($_POST['btn_comp_ts_submit']==1){

		$is_ts_completed = false;

		// get current job status
		$job_sql = mysql_query("
			SELECT `status`
			FROM `jobs`
			WHERE `id` = {$job_id}
		");
		$job_row = mysql_fetch_array($job_sql);
		$old_job_status =  $job_row['status'];	
		
		// bundle
		if($serv2['bundle']==1){

			// update bundle service completed status
			mysql_query("
				UPDATE `bundle_services`
				SET `completed` = 1
				WHERE `bundle_services_id` = {$bundle_id}
				AND `job_id` = {$job_id}
			");

			// get all bundle service for this job
			$bndl_sql = mysql_query("
				SELECT *
				FROM `bundle_services`
				WHERE `job_id` = {$job_id}
			");
			$num_bundle = mysql_num_rows($bndl_sql);

			// get completed bundle services fro this job
			$bndl_comp_sql = mysql_query("
				SELECT *
				FROM `bundle_services`
				WHERE `job_id` = {$job_id}
				AND `completed` = 1
			");
			$bndl_comp = mysql_num_rows($bndl_comp_sql);

			// if all bundle service are completed, mark as precomp
			if($bndl_comp == $num_bundle){

					mysql_query("
						UPDATE `jobs`
						SET 
							`status` = 'Pre Completion',
							`ts_completed` = 1,
							`completed_timestamp` = '".date("Y-m-d H:i:s")."',
							`precomp_jobs_moved_to_booked` = NULL
						WHERE `id` = {$job_id}
					");

					$is_ts_completed = true;

			}

		// individual services
		}else{

			mysql_query("
				UPDATE `jobs`
				SET 
					`status` = 'Pre Completion',
					`ts_completed` = 1,
					`completed_timestamp` = '".date("Y-m-d H:i:s")."',
					`precomp_jobs_moved_to_booked` = NULL
				WHERE `id` = {$job_id}
			");

			$is_ts_completed = true;

		}


		if( $is_ts_completed == true ){

			// insert job log
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
					'Techsheet Completed',
					'" . date('Y-m-d') . "',
					'<b>Techsheet Completed</b>, job changed from <b>{$old_job_status}</b> to <b>Pre Completion</b>',
					{$job_id}, 
					'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
					'" . date('H:i') . "'
				)
			");

		}
		
		
		
	}
	
	
	# Reload Job Details
	$job_details = getJobDetailsTechSheet($job_id);
	$job_details = stripSlashesData($job_details);
	
}

/* Get Appliance Details if tab enabled */
if(array_key_exists(1, $job_tech_sheet_job_type_keys))
{
	# Get Property Appliances
	$appliances = getPropertyAlarms($job_id, 1, 1, 1);
	$appliances = stripSlashesData($appliances);

	$num_existing_appliances = sizeof($appliances);

	# This is a fix to set defaults to Y for some fields - shouldn't be needed after a week or so as database default is now correct - but this is just for a few cases.
	$set_to_yes_fields = array("ts_fixing", "ts_cleaned", "ts_newbattery", "ts_testbutton", "ts_visualind", "ts_simsmoke", "ts_checkeddb", "ts_meetsas1851");
	foreach ($alarms as $aid => $alarm) {
		foreach ($set_to_yes_fields as $field)
			if ($alarm[$field] == "")
				$alarms[$aid][$field] = 1;
	}

	# Get New Property Appliances
	$new_appliances = getPropertyAlarms($job_id, 2, 1, 1);
	$new_appliances = stripSlashesData($new_appliances);
}


/* Get Alarm Details if tab enabled */
/*
if(array_key_exists(2, $job_tech_sheet_job_type_keys))
{
	# Get Property Alarms
	$alarms = getPropertyAlarms($job_id, 0, 1, 2);
	$alarms = stripSlashesData($alarms);

	$num_existing_alarms = sizeof($alarms);

	# Get New Property Alarms
	$new_alarms = getPropertyAlarms($job_id, 2, 1, 2);
	$new_alarms = stripSlashesData($new_alarms);
	$num_existing_new_alarms = sizeof($new_alarms);
}
*/

/* Get Safety Switch Details if needed */
if(array_key_exists(4, $job_tech_sheet_job_type_keys) || array_key_exists(5, $job_tech_sheet_job_type_keys))
{
	# Get Property Alarms
	$safety_switches = getPropertyAlarms($job_id, 0, 1, 4);
	$safety_switches = stripSlashesData($safety_switches);
	$num_existing_ss = sizeof($safety_switches);
}

/* Get Corded Window Details if needed */
if(array_key_exists(6, $job_tech_sheet_job_type_keys))
{
	# Get Property Alarms
	$corded_windows = getPropertyAlarms($job_id, 0, 1, 6);
	$corded_windows = stripSlashesData($corded_windows);
	$num_existing_corded_windows = sizeof($corded_windows);
}









?>


<?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
  <?php
  }  
  ?>


<style>


.vpd-tp-h .del-prop a {
    background-color: <?php echo $service_color; ?> !important;
    margin-left: 0 !important;
}
.required {
	border: inherit;
	box-shadow: 0 0 2px #404041 inset;
}

.error_border{
	border: 1px solid #b4151b;
    box-shadow: 0 0 2px #b4151b inset;
}
.jerr_hl{
	border: 1px solid red;
    box-shadow: 0 0 2px red inset;
}
</style>
  
<div id="mainContent">

<div class="sats-middle-cont">

	 <?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
  
	$tech_id = $_SESSION['USER_DETAILS']['StaffID'];
  
  $day = date("d");
  $month = date("m");
  $year = date("y");
  
  include('inc/tech_breadcrumb.php');
  
  }else{ ?>
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Tech Sheet" href="/view_job_details_tech.php?id=<?php echo $_REQUEST['id']; ?>&service=<?php echo $_REQUEST['service']; ?><?php echo ($bundle_id!='')?"&bundle_id={$bundle_id}":''; ?>"><strong>Tech Sheet</strong></a></li>
      </ul>
    </div>
  
  <?php
  }
  ?>

    
	
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   

	<?php include('inc/techsheet/javascript.php'); ?>
    


<div class='vjdt-tp-hd aviw_drop-h vpd-tp-h' style="border: 1px solid #ccc; border-bottom: 1px solid transparent;">
<div class='fl-left vjdtch-tpbtn-l del-prop' style="margin-top: -1px;">


	
	<?php
	$jr_sql = mysql_query("
		SELECT *
		FROM `job_reason`
		ORDER BY `name` ASC
	");
	?>
	<div style="float: left; margin-right: 9px; margin-top: 5px;">Job Not Completed Due To</div>
	<div style="float: left;">
			<?php
			$j_sql = mysql_query("
				SELECT `job_reason_id`, `door_knock`
				FROM `jobs`
				WHERE `id` ={$job_id}				
			");
			$j = mysql_fetch_array($j_sql);
			?>
			<select id="mark_as" style="margin-right: 9px;">
				<option value="">----</option>
				<?php
				while($jr = mysql_fetch_array($jr_sql)){ 

										
					// DK and No Time to Complete
					$hide_option = false;
					if( $jr['job_reason_id'] == 14 && $j['door_knock'] == 0 ){
						$hide_option = true;
					}

					if( $hide_option == false ){
					?>
					<option value="<?php echo $jr['job_reason_id']; ?>" <?php echo ($j['job_reason_id']==$jr['job_reason_id'])?'selected="selected"':''; ?>><?php echo $jr['name']; ?></option>
				<?php
					}
				}
				?>		
			</select>
			<div style="float:left;margin-right: 14px;" id="ma_comment_div">
				<div style="float: left; margin-right: 9px; margin-top: 5px;">Comment:</div>
				<div style="float: left;"><input type="text" id="ma_comments" style="width:400px !important;" class="addinput inputauto sig_commments" value="<?php echo $job_details['job_reason_comment']; ?>" /></div>
			</div>
			<input type="hidden" id="orig_job_reason" value="<?php echo $j['job_reason_id']; ?>" />
			<button type="button" class="submitbtnImg" id="btn_mark">Mark</button>	
	</div>
  </div>

  <!--
  <div class='fl-left' style="float: right;">				
	<button type='button' id='sync_alarm_btn' class='submitbtnImg blue-btn' style='float: left; margin-left: 13px;'>
		<img class='inner_icon' src='images/button_icons/rebook.png'>
		Sync Smoke Alarms ONLY
	</button>			
  </div>
	-->

  <div class='fl-left'>
   <? if($_SESSION['USER_DETAILS']['ClassName'] <> "TECHNICIAN"):
	?>
	<a href='view_job_details.php?id=<?=$job_id;?>' class='submitbtnImg vjdtch-tpbtn'>View Job Details</a><? endif;?>
  </div>
  </div>
	
	
	

	<?php
	if($_REQUEST['btn_comp_ts_submit']){
		
		// check for tech run
		$tr_sql = mysql_query("
			SELECT * 
			FROM  `tech_run` 
			WHERE `assigned_tech` = {$job_details['assigned_tech']}
			AND `date` = ' {$job_details['date']}'
		");

		
		if( mysql_num_rows($tr_sql)>0 ){
			$tr = mysql_fetch_array($tr_sql);
			//$vts_url = "/view_tech_schedule_day2.php?tr_id={$tr['tech_run_id']}";
			$vts_url = "/tech_day_schedule.php?tr_id={$tr['tech_run_id']}";
		}else{
			$day = date("d",strtotime($job_details['date']));
			$month = date("m",strtotime($job_details['date']));
			$year = date("Y",strtotime($job_details['date']));
			//$vts_url = "/view_tech_schedule_day.php?id={$job_details['tech_id']}&day={$day}&month={$month}&year={$year}";
			$vts_url = "/tech_day_schedule.php?tr_id={$tr['tech_run_id']}";
		}
	
		
		
	
		if($_REQUEST['btn_comp_ts_submit']==1){
	?>
		
		<div class="success">
		  Tech Sheet Updated
		</div>
		
	<?php
		}else if($_REQUEST['btn_comp_ts_submit']==2){ ?>
		
		<div class="success">
		  Tech Sheet Updated
		</div>

			
	<?php	
		}
	}
	
	
	
	if($_GET['cw_del']==1){ ?>
		<div class="success">Delete Successful!</div>
	<?php
	}
	
	
	if($_GET['sa_added']==1){ ?>
		<div class="success">Alarms Added!</div>
	<?php
	}


	if($_GET['sync_alarm']==1){ ?>
		<div class="success">Sync Successful</div>
	<?php
	}
	
	
	?>
	
	
	
	

	<form action='<?=URL;?>view_job_details_tech.php?id=<?php echo $_GET['id']; ?>&service=<?php echo $_GET['service']; ?>&bundle_id=<?php echo $_GET['bundle_id']; ?>&action=update' method='post' id='techsheetform' enctype="multipart/form-data">
		<input type="hidden" name="prop_id" value="<?php echo $job_details['property_id']; ?>" />
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table" id="vjdt-ftable">
			<tr bgcolor="<?php echo $service_color; ?>">
				<th class="techsheet_header colorwhite bold" style="width:368px">Address</th>
				<th class="techsheet_header colorwhite bold">Service</th>
				<th class="techsheet_header colorwhite bold">Job Type</th>
				<th class="techsheet_header colorwhite bold">Job #</th>
				<th class="techsheet_header colorwhite bold" colspan="2">Key #</th>
			</tr>
			<?php
			/*
			switch($job_details['service']){
				case 2:
					$serv = 'Smoke Alarms';
				break;
				case 5:
					$serv = 'Safety Switch';
				break;
				case 6:
					$serv = 'Corded Windows';
				break;
				case 7:
					$serv = 'Pool Barriers';
				break;
				default:
					$serv = '';
			}
			*/
			?>
			<tr class="tbone">
				<td><?=$job_details['address_1'] . ' ' . $job_details['address_2'].' '.$job_details['address_3'].' '.$job_details['state'].' '.$job_details['postcode'];?></td>
				<td><?=getServiceName($job_details['service']);?></td>
				<td><?=$job_details['job_type'];?></td>
				<td><?="SAT-" . $job_details['id'];?></td>
				<td><input type="text" name="key_num" id="key_num" value="<?=$job_details['key_number'];?>" /><span class="kn_msg" style="color:green; margin-left: 8px; display:none;">Updated<span></td>
			</tr>				
			<tr bgcolor='<?php echo $service_color; ?>' class="tbonecolor">
				<th class="techsheet_header colorwhite bold">Onsite Contact</th>
				<th class="techsheet_header colorwhite bold">Phone</th>
				<th class="techsheet_header colorwhite bold">Mobile</th>
				<th class="techsheet_header colorwhite bold">Agency</th>
				<th class="techsheet_header colorwhite bold">Phone</th>
			</tr>
			<?php
			/*
			$psql = mysql_query("
				SELECT *
				FROM `property`
				WHERE `property_id` = {$job_details['property_id']}
			");
			$p = mysql_fetch_array($psql);
			*/
			?>	
			
			
			<tr class="tbone">
				<td colspan="3">
				
					<table>
						<?php


						$pt_params = array( 
							'property_id' => $job_details['property_id'],
							'active' => 1
						 );
						$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
						
						while( $pt_row = mysql_fetch_array($pt_sql) ){ ?>
							<tr class="tbone">
								<td colspan="2">
									<span class="tenant_span">
										<input type="text" id="tfn1" class="prop_update" value="<?php echo $pt_row['tenant_firstname']; ?>" />					
										<input type="hidden" class="prop_field" value="tenant_firstname" />
										<span class="kn_msg">
											<img src="/images/check_icon2.png" class="img_check">
										</span>
										<input type="hidden" class="pt_id" value="<?php echo $pt_row['property_tenant_id'] ?>" />
									</span>
									
									<span class="tenant_span">
										<input type="text" id="tln1" class="prop_update" value="<?php echo $pt_row['tenant_lastname']; ?>" />
										<input type="hidden" class="prop_field" value="tenant_lastname" />
										<span class="kn_msg">
											<img src="/images/check_icon2.png" class="img_check">
										</span>
									</span>					
								</td>
								<td>
									<span class="tenant_span">
										<input type="text" id="tln1" class="prop_update" value="<?php echo $pt_row['tenant_landline'] ?>" />
										<input type="hidden" class="prop_field" value="tenant_landline" />
										<span class="kn_msg">
											<img src="/images/check_icon2.png" class="img_check">
										</span>
									</span>					
								</td>
								<td>
									<span class="tenant_span">
										<input type="text" id="tm1" class="prop_update" value="<?php echo $pt_row['tenant_mobile'] ?>" />
										<input type="hidden" class="prop_field" value="tenant_mobile" />
										<span class="kn_msg">
											<img src="/images/check_icon2.png" class="img_check">
										</span>											
									</span>
								</td>	
							</tr>
						<?php								
						}						
						?>						
					</table>
					
				</td>
				<td colspan="2">
					<table>
						<tr>
							<td>
								<?php
								if( $_SESSION['USER_DETAILS']['ClassID'] == 6 ){
									echo $job_details['agency_name'];
								}else{ ?>
									<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$job_details['agency_id']}"); ?>
									<a style="color:<?php echo $service_color; ?>" href='<?=$ci_link;?>'>
										<?=$job_details['agency_name'];?>
									</a>
								<?php
								}
								?>								
							</td>
							<td><?=$job_details['agent_phone'];?></td>
						</tr>
						<tr>
							<td>Work Order/MITM</td>
							<td><input type="text" name="work_order" value="<?php echo $job_details['work_order']; ?>" /></td>
						</tr>
						<tr>
							<td>House Alarm Code</td>
							<td style="border-right: 1px solid #cccccc;">
								<input type="text" name="alarm_code" value="<?php echo $job_details['alarm_code']; ?>" />
							</td>
						</tr>
					</table>
				</td>				
			</tr>
			
			<tr>
				<td colspan="3">
					<?php
					$jl_sql = mysql_query("
						SELECT `comments`
						FROM job_log 
						WHERE job_id = {$job_id} 
						AND deleted = 0 
						AND `contact_type` = 'Job Booked'
						ORDER BY eventdate DESC, `log_id` DESC
						LIMIT 1
					");
					$jl = mysql_fetch_array($jl_sql);
					$jl_comments = $jl['comments'];
					?>
					<input type="text" <?php echo ($jl_comments!="")?'class="jerr_hl"':''; ?> readonly="readonly" value="<?php echo strip_tags($jl_comments); ?>" style="width:100%;" />
				</td>
				<td colspan="100%">&nbsp;</td>
			</tr>
		</table>

		
		<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table" id="vjdt-ftable">
             <tr bgcolor="<?php echo $service_color; ?>">
                <th class="techsheet_header colorwhite bold">Job Notes</th>
				<th class="techsheet_header colorwhite bold">Agency Specific Notes</th>
				<?php
				$psql = mysql_query("
					SELECT `comments`
					FROM `property`
					WHERE `property_id` = {$job_details['property_id']}
				");
				$p = mysql_fetch_array($psql);
				?>
                <th class="techsheet_header colorwhite bold">Property Notes</th>                
            </tr>
			<tr>
				<td><?=stripslashes((isset($job_details['comments']) ? $job_details['comments'] : $job_details['comments']));?></td>
				<?php
				// agency specific notes
				$asn_sql = mysql_query("
					SELECT `agency_specific_notes`
					FROM `agency`
					WHERE `agency_id` = {$job_details['agency_id']}
				");
				$asn = mysql_fetch_array($asn_sql);
				?>
				<td><?php echo trim($asn['agency_specific_notes']); ?></td>
				<td><?=stripslashes((isset($p['comments']) ? $p['comments'] : $p['comments']));?></td>
			</tr>
		</table>

		

		<style>
		/*----- Tabs -----*/
		.tabs {
			width:100%;
			display:inline-block;
		}
		 
		/*----- Tab Links -----*/
		/* Clearfix */
		.tab-links:after {
			display:block;
			clear:both;
			content:'';
		}
	 
		.tab-links li {
			margin:0px 5px;
			float:left;
			list-style:none;
		}
	 
		.tab-links a {
			padding:9px 15px;
			display:inline-block;
			border-radius:3px 3px 0px 0px;
			box-shadow: 0 0 2px #404041 inset;
			color: #404041;
			font-weight: normal;
			transition:all linear 0.15s;
			
		}
 
		
		.tab-links a.j_sa:hover {
			background:#b4151b;
			box-shadow: none;
			color: #ffffff;
		}
		.tab-links a.j_ss:hover {
			background:#f15a22;
			box-shadow: none;
			color: #ffffff;
		}
		.tab-links a.j_cw:hover {
			background:#00AE4D;
			box-shadow: none;
			color: #ffffff;
		}
		.tab-links a.j_pb:hover {
			background:#00aeef;
			box-shadow: none;
			color: #ffffff;
		}
		.tab-links a.active {
			box-shadow: none;
			color: #ffffff;
		}
		.tab-links{ margin-bottom: 0px;}
		.tab-links li{ margin-left: 0px;}
		</style>
		
		
		
		<!-- Tabs Begin -->
		<?php $tech_tabs = mysql_fetch_array($tech_tabs_sql); ?>
		<?php 
			if(mysql_num_rows($tech_tabs_sql)>0){ ?>			
				<!--
				<ul id="tech-sheet-tabs">
					<li><a href="#_" id="<?php echo $tech_tabs['html_id']; ?>-tab" class="active"><?php echo htmlspecialchars($tech_tabs['type']); ?></a></li>
				</ul>
				-->
				<?php
				// if bundle
				//if($serv['bundle']==1){ 
				?>			
				<ul class="tab-links" style="padding: 0px;">
					<?php
					$serv_sql3 = getbundleServices($_GET['id']);
					while($serv3 = mysql_fetch_array($serv_sql3)){ 										
					// service color
					$serv_clr_arr = $crm->getServiceColors($serv3['id']);
					$tab_service_color = $serv_clr_arr['bg_color'];
					$serv_class_name = $serv_clr_arr['tab_class'];
					?>
							<li>
								<a class="<?php echo $serv_class_name; ?> <?php echo ($_GET['ajt_id']==$serv3['id'] || $service==$serv3['id'])?'active':''; ?>" style="background-color:<?php echo ($_GET['ajt_id']==$serv3['id'] || $service==$serv3['id'])?$tab_service_color:''; ?>;" href="/view_job_details_tech.php?id=<?php echo $job_id ?>&service=<?php echo $job['service']; ?>&bundle_id=<?php echo $serv3['bundle_services_id']; ?>"><?php echo $serv3['type']; ?></a>
							</li>
					<?php
					}
					?>					
				</ul>										
				<?php
				//}
				?>
				<div class="tech-sheet-tab-container" id="<?php echo $tech_tabs['html_id']; ?>-div" style="display:block;">
					<?php include('inc/techsheet/' . $tech_tabs['include_file']); ?>
				</div>	
			<?php
			}
			?>	
		


		
		
		</td></tr>
		</table>
		
		
		
		
		
		
	</form>
	
	<!-- end #container -->
</div>





</div>

<br class="clearfloat" />

<!-- inline lightbox -->
<style>
.camera_white{
	position: relative;
	top: 4px;
}
.cwFileSelectedHL,
.wfFileSelectedHL
{
    background-color: #eeeeee !important;
	color: white;
}
.ssFileSelectedHL{
    background-color: #eeeeee !important;
	color: white;
}
.saFileSelectedHL{
    background-color: #eeeeee !important;
	color: white;
}


.cwFileSelectedHLRed{
    background-color: #f0d0d1 !important;
	color: white;
}


.add_sa_tr_new, .add_sa_tr_exist{
	display:none;
}

</style>

<!-- CW lightbox -->
<div style="display:none">
	<div id="cw_window">
	
	<form id="form_add_cw" name="form_add_cw" enctype="multipart/form-data" method="post">
	 <table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_cw' style="border: none; margin-bottom: 10px;">
			<tbody class="add_cw_tbody">
				<tr class="greenrow">
					<td><strong>Location</strong></td>
					<td>
						<input type="text" class="addinput new_location" name="new_location[]" id="new_location" />
					</td>
					
				</tr>
				<tr class="greenrow">					
					<td><strong>Number of windows</strong></td>
					<td>
						<input type="text" class="addinput new_num_of_windows" name="new_num_of_windows[]" id="new_num_of_windows" />
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				<tr>
				<tr>					
					<td>
						<button type="button" id='btn_add_cw' class="submitbtnGreen submitbtnImg btn_add_cw">
							<img class="inner_icon" src="images/add-button.png">
							Window
						</button>
					</td>
					<td>
						<input type="hidden" name="new_cw_submitted" id="new_cw_submitted" /> 
						<button type="button" id='btn_save_cw' class="submitbtnGreen submitbtnImg btn_save_cw">
							<img class="inner_icon" src="images/save-button.png">
							Save
						</button>						
					</td>
				</tr>
			</tfoot>
		</table>
		</form>
		
	</div>
</div>


<!-- WE lightbox -->
<div style="display:none">
	<div id="we_window">
	
	<form id="form_add_we" name="form_add_we" method="post">
	 <table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_we' style="border: none; margin-bottom: 10px; color:white;">
			<tbody class="add_we_tbody">

				<tr style="background-color:<?php echo $service_color; ?>">		
					<td><strong>Location</strong></td>
					<td>
						<input type="text" class="addinput we_location" name="we_location[]" id="we_location" placeholder="Eg Main Bathroom etc" />
					</td>
				</tr>

				<tr style="background-color:<?php echo $service_color; ?>" class="we_tr">
					<td><strong>Device</strong></td>
					<td>
						<select name="we_device[]" id="we_device" class="addinput we_device">
							<option value="">---</option>
							<?php
							// get WE data
							$wed_sql = mysql_query("
							SELECT 
								`water_efficiency_device_id`,
								`name`
							FROM `water_efficiency_device` 
							WHERE `active` = 1
							");

							while( $wed_row = mysql_fetch_array($wed_sql) ){ ?>
								<option value="<?php echo $wed_row['water_efficiency_device_id'] ?>"><?php echo $wed_row['name'] ?></option>
							<?php
							}
							?>												
						</select>
					</td>					
				</tr>				
				<tr style="background-color:<?php echo $service_color; ?>; display: none;" class="we_pass_tr">					
					<td><strong class="we_pass_lbl">Pass</strong></td>
					<td>
						<ul class="we_radio">
							<input type="radio" class="we_pass we_pass_yes" name="we_pass[0]" id="we_pass we_pass0" value="1" /> <label class="we_pass_lbl_yes">Yes</label>
							<input type="radio" class="we_pass we_pass_no" name="we_pass[0]" id="we_pass we_pass0" value="0" /> <label class="we_pass_lbl_no">No</label>
						</ul>						
					</td>
				</tr>
				<tr style="background-color:<?php echo $service_color; ?>">		
					<td><strong>Note (If Needed)</strong></td>
					<td>
						<textarea name="we_notes[]" id="we_notes" class="addtextarea we_notes"></textarea>						
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">&nbsp;</td>
				<tr>
				<tr>					
					<td colspan="2">
						<input type="hidden" name="new_we_submitted" id="new_we_submitted" /> 
						<button type="button" id='btn_add_we' class="submitbtnImg btn_add_we"  style="background-color:<?php echo $service_color; ?>;">
							<img class="inner_icon" src="images/add-button.png">
							Water Flow
						</button>
						<button type="button" id='btn_remove_we' class="submitbtnImg btn_remove_we"  style="background-color:red;">
							<img class="inner_icon" src="images/cancel-button.png">
							Remove
						</button>
						<button type="button" id='btn_save_we' class="submitbtnImg btn_save_we"  style="background-color:green;">
							<img class="inner_icon" src="images/save-button.png">
							Save
						</button>						
					</td>	
				</tr>
			</tfoot>
		</table>

		
		<input type="hidden" name="property_leaks_hid" id="property_leaks_hid" />
		<input type="hidden" name="leak_notes_hid" id="leak_notes_hid" />

		</form>
		
	</div>
</div>


<!-- SS lightbox -->
<div style="display:none">
	<div id="ss_window">
	
	<form id="form_add_ss" name="form_add_cw" enctype="multipart/form-data" method="post">
		<table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_ss' style="border: none; margin-bottom: 10px;">
			<tbody class="add_ss_tbody">
				<tr class="servBgcolorNTextColor_ss">
					<td><strong>Make</strong></td>
					<td>
						<input type="text" name="ss_make[]" id="ss_make" class="addinput ss_make" />
					</td>					
				</tr>
				<tr class="servBgcolorNTextColor_ss">					
					<td><strong>Model</strong></td>
					<td>
						<input type="text" name="ss_model[]" id="ss_model" class="addinput ss_model" />
					</td>
				</tr>
				<tr class="servBgcolorNTextColor_ss">					
					<td><strong>Test</strong></td>
					<td>
						<select name="ss_test[]" id="ss_test" class="addinput ss_test">
							<option value="">---</option>
							<option value="1">Pass</option>
							<option value="0">Fail</option>
							<option value="2">No Power</option>
							<option value="3">Not Tested</option>
						</select>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>					
					<td>
						<button type="button" id='btn_add_ss_tbody' class="servBgcolorNTextColor_ss submitbtnImg btn_add_ss_tbody">
							<img class="inner_icon" src="images/add-button.png">
							Switch
						</button>
					</td>
					<td>
						<input type="hidden" name="new_ss_submitted" id="new_ss_submitted" /> 
						<button type="button" id='btn_save_ss' class="servBgcolorNTextColor_ss submitbtnImg btn_save_ss">
							<img class="inner_icon" src="images/save-button.png">
							Save
						</button>						
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
		
	</div>
</div>


<!-- Job Notes -->
<div style="display:none">
	<div id="repair_notes_lb_div">
	

	 <table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_cw' style="border: none; margin-bottom: 10px;">
			<tbody>
				<tr class="<?php echo $serv_class_color; ?>">
					<td>Repair Notes</td>
					<td>
						<textarea name="repair_notes_lb" id="repair_notes_lb" class="corderwindow-tarea techsheet addtextarea repair_notes_lb"><?php echo $job_details['repair_notes']; ?></textarea>
					</td>					
				</tr>
			</tbody>
			<tfoot>
				<tr style="border:0 none!important;">					
					<td>&nbsp;</td>
					<td>
						<button type="button" id='btn_save_repair_notes' onclick="$.fancybox.close()" class="submitbtnImg <?php echo $serv_class_color; ?> btn_save_repair_notes">Save</button>						
					</td>
				</tr>
			</tfoot>
		</table>

		
	</div>
</div>



<!-- Job Notes -->
<div style="display:none">
	<div id="job_lb_div">
	

	 <table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_cw' style="border: none; margin-bottom: 10px;">
			<tbody>
				<tr class="<?php echo $serv_class_color; ?>">
					<td>Job Notes</td>
					<td>
						<textarea name="job_notes_lb" id="job_notes_lb" class="corderwindow-tarea techsheet addtextarea job_notes_lb"><?php echo stripslashes($job_details['tech_comments']); ?></textarea>
					</td>					
				</tr>
			</tbody>
			<tfoot>
				<tr style="border:0 none!important;">					
					<td>&nbsp;</td>
					<td>
						<button type="button" id='btn_save_job_notes' onclick="$.fancybox.close()" class="submitbtnImg <?php echo $serv_class_color; ?> btn_save_job_notes">Save</button>						
					</td>
				</tr>
			</tfoot>
		</table>

		
	</div>
</div>


<!-- Property Notes -->
<div style="display:none">
	<div id="prop_lb_div">
	
	
	 <table cellpadding=2 cellspacing=0 width=100% border=0 id='tbl_add_cw' style="border: none; margin-bottom: 10px;">
			<tbody>
				<tr class="<?php echo $serv_class_color; ?>">
					<td>Property Notes</td>
					<td>
						<textarea name="prop_notes_lb" id="prop_notes_lb" class="corderwindow-tarea techsheet addtextarea prop_notes_lb"><?php echo $job_details['p_comments']; ?></textarea>
					</td>					
				</tr>
			</tbody>
			<tfoot>
				<tr style="border:0 none!important;">					
					<td>&nbsp;</td>
					<td>
						<button type="button" id='btn_save_prop_notes' onclick="$.fancybox.close()" class="submitbtnImg <?php echo $serv_class_color; ?> btn_save_prop_notes">Save</button>						
					</td>
				</tr>
			</tfoot>
		</table>
	
		
	</div>
</div>

<script>
jQuery(document).ready(function(){

	// snyc alarms
	jQuery("#sync_alarm_btn").click(function () {

		var job_id = <?php echo $job_id; ?>;
		var property_id = <?php echo $job_details['property_id']; ?>;

		if (parseInt(job_id) > 0 && parseInt(property_id) > 0) {

			if (confirm("Are you sure you want to sync alarms?")) {

				jQuery.ajax({
					type: "POST",
					url: "ajax_sync_smoke_alarms.php",
					data: {
						job_id: job_id,
						property_id: property_id
					}
				}).done(function (ret) {
					//location.reload();
					window.location = "view_job_details_tech.php?id=<?php echo $job_id; ?>&service=<?php echo $service; ?>&sync_alarm=1";
				});

			}

		}

	});	


	// remove alarm form row
	jQuery("#cw_window").on("click",".btn_remove_cw_row",function(){
		
		jQuery(this).parents("tbody.add_cw_tbody:first").remove();
		
	});
	

	// job and property note lightbox
	$("a.inlineFB").fancybox({
		//'hideOnContentClick': true
		'autoDimensions': false,
		'width': 800,
		'height': 'auto'
	});
	
	
	// save repair notes
	jQuery("#btn_save_repair_notes").click(function(){
		
		var repair_notes = jQuery("#repair_notes_lb").val();
		var error = '';
		
		if(repair_notes==''){
			error += "Job Notes is required\n";
		}
		
		if(error!=''){
			alert(error);
		}else{
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_repair_notes.php",
				data: { 
					job_id: <?php echo $job_id; ?>,
					repair_notes: repair_notes				
				}
			}).done(function( ret ){
				jQuery("#repair_notes").val(repair_notes);
			});	
		}
		
		
	});
	

	// save job notes
	jQuery("#btn_save_job_notes").click(function(){
		
		var job_notes = jQuery("#job_notes_lb").val();
		var error = '';
		
		if(job_notes==''){
			error += "Job Notes is required\n";
		}
		
		if(error!=''){
			alert(error);
		}else{
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_job_tech_comments.php",
				data: { 
					job_id: <?php echo $job_id; ?>,
					job_notes: job_notes				
				}
			}).done(function( ret ){
				jQuery("#tech_comments").val(job_notes);
			});	
		}
		
		
	});
	
	
	// save property notes
	jQuery("#btn_save_prop_notes").click(function(){
		
		var prop_notes = jQuery("#prop_notes_lb").val();
		var error = '';
		
		if(prop_notes==''){
			error += "Property Notes is required\n";
		}
		
		if(error!=''){
			alert(error);
		}else{
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_prop_comments.php",
				data: { 
					prop_id: <?php echo $job_details['property_id']; ?>,
					prop_notes: prop_notes				
				}
			}).done(function( ret ){
				jQuery("#prop_comments").val(prop_notes);
			});	
		}
		
		
	});



	/*
	jQuery(".sig_commments").blur(function(){

		var comments = jQuery(this).val();
		var append_txt = '<?php echo " - ".trim($_SESSION['USER_DETAILS']['FirstName'])." ".substr(strtoupper(trim($_SESSION['USER_DETAILS']['LastName'])),0,1).". ".date("d/m/Y"); ?>';
		
		if(comments!="" && comments.search("<?php echo " - ".trim($_SESSION['USER_DETAILS']['FirstName'])." ".substr(strtoupper(trim($_SESSION['USER_DETAILS']['LastName'])),0,1); ?>")==-1){
			jQuery(this).val(comments+append_txt);
		}
		
	});
	*/

	// update property tenants
	jQuery(".prop_update").change(function(){
		
		var obj = jQuery(this);
		var prop_id = <?php echo $job_details['property_id']; ?>;
		var prop_field = obj.parents(".tenant_span").find(".prop_field").val();
		var pt_id = obj.parents("tr:first").find(".pt_id").val();
		var prop_val = obj.val();
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_property_tenants.php",
			data: { 
				pt_id: pt_id,
				prop_id: prop_id,
				prop_field: prop_field,
				prop_val: prop_val
			}
		}).done(function( ret ){
			obj.parents(".tenant_span").find(".kn_msg").show();
		});

			
	});

	// update key number
	jQuery("#key_num").blur(function(){
		var prop_id = <?php echo $job_details['property_id']; ?>;
		var key_num = jQuery(this).val();
		jQuery.ajax({
				type: "POST",
				url: "ajax_update_key_number.php",
				data: { 
					prop_id: prop_id,
					key_num: key_num
				}
			}).done(function( ret ){
				jQuery("#key_num").parents("td:first").find(".kn_msg").show();
			});		
	});

	// trigger comment pop up
	/*
	jQuery("#mark_as").change(function(){
		if(jQuery(this).val()!=""){	
			jQuery("#ma_comment_div").show();
		}else{
			jQuery("#ma_comment_div").hide();
		}		
	});
	*/
	
	// update job reason
	jQuery("#btn_mark").click(function(){
		var job_id = <?php echo $job_id; ?>;
		var jr_id = jQuery("#mark_as").val();
		var comment = jQuery("#ma_comments").val();

		if(jr_id==""){
			alert("Please select reason");
		}else{

			var job_id_arr = [];
			job_id_arr.push(job_id);
			
			var ajax_url = "ajax_update_job_reason.php";			
			
			jQuery.ajax({
				type: "POST",
				url: ajax_url,
				data: { 
					job_id_arr: job_id_arr,
					jr_id: jr_id,
					comment: comment
				}
			}).done(function( ret ){
				var url = window.location.href+"&btn_comp_ts_submit=2";
				window.location=url;
			});	
		}		
	});

});
</script>
</body>
</html> 
