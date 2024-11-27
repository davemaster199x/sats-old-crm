<?
$pageLoadStart = microtime(TRUE);

$title = "View Job Details";

# extra check to ensure no unautherized access
if ($_SERVER['HTTP_REFERER'] == "") {
    # if not allowed redirect to main with error banner - and exit so no more scripts can run
    header("location: main.php?restricted=1");
    exit();
}

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
include('inc/ourtradie_api_class.php'); 

$domain = $_SERVER['SERVER_NAME'];
if( $_SESSION['country_default']==1 ){ // AU
    // go to NZ
    $country_iso_txt = 'AU';

    if( strpos($domain,"crmdev") !== false ){ // DEV
        $site_link = 'https://crmdev.sats.com.au';
        $crm_ci_link = 'https://crmdevci.sats.com.au';
    }else{ // LIVE
        $site_link = 'https://crm.sats.com.au';
        $crm_ci_link = 'https://crmci.sats.com.au';
    }


}else if( $_SESSION['country_default']==2 ){ // NZ
    // go to AU
    $country_iso_txt = 'NZ';

    if( strpos($domain,"crmdev") !== false ){ // DEV
        $site_link = 'https://crmdev.sats.co.nz';
        $crm_ci_link = 'https://crmdevci.sats.co.nz';
    }else{ // LIVE
        $site_link = 'https://crm.sats.co.nz';
        $crm_ci_link = 'https://crmci.sats.co.nz';
    }
}

$encrypt_decrypt = new Openssl_Encrypt_Decrypt();

$crm = new Sats_Crm_Class;
$agency_api = new Agency_api;
//$propertyme_api = new Propertyme_api;
// jc, daniel, jayson and ness
$devs = array(2070, 2025, 2142, 11);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// logged staff name
$logged_staff_name = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);
$today = date('d/m/Y');

// init the variables
$job_id = $_GET['id'];
$doaction = $_GET['doaction'];

//$new_tenants = 0;
$new_tenants = NEW_TENANTS;

if ($_GET['tr_tech_id'] != "" && $_GET['tr_date'] != "") {
    $added_param = "&tr_tech_id=" . mysql_real_escape_string($_GET['tr_tech_id']) . "&tr_date=" . mysql_real_escape_string($_GET['tr_date']) . "&tr_booked_by=" . mysql_real_escape_string($_GET['tr_booked_by']);
} else {
    $added_param = "";
}

function isStaffElectrician($tech_id) {

    $sql_str = "
		SELECT COUNT(`StaffID`)
		FROM `staff_accounts`
		WHERE `StaffID` = {$tech_id}
		AND `is_electrician` = 1
	";

    $sql = mysql_query($sql_str);

    if (mysql_num_rows($sql) > 0) {
        return true;
    } else {
        return false;
    }
}

// if "FULL - No More Jobs" in STR
function isStrMappedFull($job_id) {

    $sql_str = "
		SELECT tr.`no_more_jobs`
		FROM `tech_run_rows` AS trr
		LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
		LEFT JOIN `jobs` AS j ON j.`id` = trr.`row_id`
		WHERE j.`id` = {$job_id}
		AND tr.`no_more_jobs` = 1
		AND j.`status` = 'Booked'
		AND trr.`hidden` = 0
		AND j.`del_job` = 0
		AND tr.`country_id` = {$_SESSION['country_default']}
		AND tr.`date` >= '" . date('Y-m-d') . "'
	";
    $sql = mysql_query($sql_str);

    if (mysql_num_rows($sql) > 0) {
        return true;
    } else {
        return false;
    }
}

function getTechName($tech_id) {
    $sql = mysql_query("
        SELECT FirstName, LastName
        FROM `staff_accounts`
        WHERE `StaffID` = {$tech_id}
    ");
    $row = mysql_fetch_array($sql);
    return Sats_Crm_Class::formatStaffName($row['FirstName'], $row['LastName']);
}

function is_safety_squad($agency_id){
    $sql_str= "
        SELECT 
            sac.`sac_id`,
            sac.`company_name`
        FROM `agencies_from_other_company` AS afoc
        LEFT JOIN `smoke_alarms_company` AS sac ON afoc.`company_id` = sac.`sac_id`
        LEFT JOIN `agency` AS a ON afoc.`agency_id` = a.`agency_id`
        WHERE afoc.`agency_id` = {$agency_id}
        AND afoc.`active` = 1
        AND afoc.company_id = 1
    ";
    $sql = mysql_query($sql_str);

    if (mysql_num_rows($sql) > 0) {
        return true;
    } else {
        return false;
    }

}

// get service
$s_sql = mysql_query("
	SELECT `service`
	FROM `jobs`
	WHERE `id` = {$job_id}
");
$s = mysql_fetch_array($s_sql);

$service = $s['service'];
$ajt_service_id = $s['service'];

// alarm images query
$img_sql = mysql_query("
    SELECT ai.`alarm_id`, ai.`image_filename`
    FROM `alarm` AS al
    LEFT JOIN `alarm_images` AS ai ON al.`alarm_id` = ai.`alarm_id`
    WHERE al.`job_id` = {$job_id}
    ");

// service color
switch ($service) {
    case 2:
        $serv_color = 'b4151b';
        break;
    case 5:
        $serv_color = 'f15a22';
        break;
    case 6:
        $serv_color = '00ae4d';
        break;
    case 7:
        $serv_color = '00aeef';
        break;
    case 12:
        $serv_color = 'b4151b';
        break;
    default:
        $serv_color = '9B30FF';
}
//$serv_color = ($service==6)?'00ae4d':'b4151b';
# Get appliance  / alarm pwr
$alarm_pwr_appliances = alarmGetAlarmPower(1);
$alarm_pwr = alarmGetAlarmPower(2);

# Get appliance / alarm type
$alarm_type_appliances = alarmGetAlarmType(1);
$alarm_type = alarmGetAlarmType(2);

# Get appliance / alarm reason
$alarm_reason_appliances = alarmGetAlarmReason(1);
$alarm_reason = alarmGetAlarmReason(2);

# Get Different Tech Sheet Job Types
$tech_sheet_job_types = getTechSheetJobTypes();

//$job_editable = canJobBeEdited($job_id, $_SESSION['USER_DETAILS']['StaffID']);
$job_editable = 1;

$sql = "SELECT * FROM job_type";
$jobtypes = mysqlMultiRows($sql);

//$jobtypes = getJobTypes();


function getAllExpired240vAlarm($job_id) {
    $sql = mysql_query("
		SELECT *
		FROM `alarm`
		WHERE `job_id` ={$job_id}
		AND `alarm_power_id` IN ( 2, 4, 9, 10 )
		AND `expiry` <= '" . date('Y') . "'
	");
    if (mysql_num_rows($sql) > 0) {
        return true;
    } else {
        return false;
    }
}

// delete smoke
if (is_numeric($_GET['delalarm'])) {
    deleteAlarm($_GET['delalarm'], $job_id);
}

// delete windows
if (is_numeric($_GET['delcw'])) {
    mysql_query("
		DELETE
		FROM `corded_window`
		WHERE `corded_window_id` = {$_GET['delcw']}
	");
}

// delete switch
if ( is_numeric($_GET['delss']) && $_GET['delss'] > 0 ) {

    // get safety switch
    $ss_sql = mysql_query("
    SELECT ss_reason.`reason`
    FROM `safety_switch` AS ss
    LEFT JOIN `safety_switch_reason` AS ss_reason ON ss.`ss_res_id` = ss_reason.`ss_res_id`
    WHERE ss.`job_id` = {$job_id}   
    AND ss.`safety_switch_id` = {$_GET['delss']}               
    ");
    $ss_row = mysql_fetch_object($ss_sql);

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
        'Safety Switch Update',
        '" . date('Y-m-d') . "',
        'Discarded safety switch with reason <b>{$ss_row->reason}</b> deleted.',
        {$job_id},
        '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
        '" . date('H:i') . "'
    )
    ");

    // HARD delete
    mysql_query("
		DELETE
		FROM `safety_switch`
		WHERE `safety_switch_id` = {$_GET['delss']}
	");    

}


// delete WE
if ( $_GET['we_del'] == 1 ) {

	$we_id = mysql_real_escape_string($_GET['we_id']);

	if( $we_id > 0 ){

		$query = "
		DELETE
		FROM `water_efficiency`
		WHERE `water_efficiency_id` = {$we_id}
		AND job_id = " . $job_id;

		mysql_query($query) or die(mysql_error());

	}

}


$property_id = mysql_real_escape_string($_POST['property_id']);
// update the details in the database.
$jobdate = convertDate($_POST['jobdate']);
$jobdate_orig = convertDate($_POST['jobdate_orig']);
$timeofday = mysql_real_escape_string($_POST['timeofday']);
$timeofday_orig = mysql_real_escape_string($_POST['timeofday_orig']);
$status = $_POST['status'];
$curr_status = $_POST['curr_status'];
$curr_job_type = $_POST['curr_job_type'];
$comments = mysql_real_escape_string($_POST['comments']);
$repair_notes = mysql_real_escape_string($_POST['repair_notes']);
#$techcomments = mysql_prep($_POST['techcomments']);
$autorenew = $_POST['autorenew'];
$jobtype = $_POST['jobtype'];
$techid = mysql_real_escape_string($_POST['techid']);
$techid_orig = mysql_real_escape_string($_POST['techid_orig']);
$job_price = $_POST['job_price'];
$job_price_reason = mysql_real_escape_string($_POST['price_reason']);
$job_price_detail = mysql_real_escape_string($_POST['price_detail']);
$work_order = addslashes($_POST['work_order']);
$alarm_code = addslashes($_POST['alarm_code']);
$key_access_required = $_POST['key_access_required'];
$current_key_access_required = $_POST['current_key_access_required'];
$tech_comments = mysql_real_escape_string($_POST['tech_comments']);
$key_access_details = mysql_real_escape_string($_POST['key_access_details']);
$tech_notes = mysql_real_escape_string(trim($_POST['tech_notes']));
$due_date = ($_POST['due_date'] != "") ? "'" . date("Y-m-d", strtotime(str_replace("/", "-", mysql_real_escape_string($_POST['due_date'])))) . "'" : "NULL";
$start_date = ($_POST['start_date'] != "") ? "'" . date("Y-m-d", strtotime(str_replace("/", "-", mysql_real_escape_string($_POST['start_date'])))) . "'" : "NULL";
$job_price_reason = mysql_real_escape_string($_POST['price_reason']);
$booked_with = mysql_real_escape_string($_POST['booked_with']);
$booked_with_orig = mysql_real_escape_string($_POST['booked_with_orig']);
$booked_by = mysql_real_escape_string($_POST['booked_by']);
$booked_by_orig = mysql_real_escape_string($_POST['booked_by_orig']);
$job_entry_notice = mysql_real_escape_string($_POST['job_entry_notice']);
//$escalate_job_reasons_arr = $_POST['escalate_job_reasons'];
$escalate_job_reasons = $_POST['escalate_job_reasons'];

$allocate_notes = mysql_real_escape_string($_POST['allocate_notes']);

$survey_numlevels = mysql_real_escape_string($_POST['survey_numlevels']);
$survey_ceiling = mysql_real_escape_string($_POST['survey_ceiling']);
$survey_ladder = mysql_real_escape_string($_POST['survey_ladder']);
$ss_location = mysql_real_escape_string($_POST['ss_location']);
$ss_quantity = mysql_real_escape_string($_POST['ss_quantity']);
$ss_quantity = mysql_real_escape_string($_POST['ss_quantity']);
$ts_safety_switch = mysql_real_escape_string($_POST['ts_safety_switch']);
$ss_reason = mysql_real_escape_string($_POST['ss_reason']);

$ps_number_of_bedrooms = mysql_real_escape_string($_POST['ps_number_of_bedrooms']);
$qld_new_leg_alarm_num = mysql_real_escape_string($_POST['qld_new_leg_alarm_num']);
$status_changed_flag = mysql_real_escape_string($_POST['status_changed_flag']);
$prop_vac = ( $_POST['prop_vac'] == 1 ) ? 1 : 0;
$prop_vac_orig = mysql_real_escape_string($_POST['prop_vac_orig']);

$dha_need_processing = mysql_real_escape_string($_POST['dha_need_processing']);
$out_of_tech_hours = mysql_real_escape_string($_POST['out_of_tech_hours']);
$show_as_paid = mysql_real_escape_string($_POST['show_as_paid']);

$to_be_printed = mysql_real_escape_string($_POST['to_be_printed']);

$job_priority = mysql_real_escape_string($_POST['job_priority']);
$electrician_only = mysql_real_escape_string($_POST['electrician_only']);
$preferred_alarm_id = mysql_real_escape_string($_POST['preferred_alarm_id']);


 // update tenants
 $tenant_firstname1 = mysql_real_escape_string($_POST['tenant_firstname1']);
 $tenant_lastname1 = mysql_real_escape_string($_POST['tenant_lastname1']);
 $tenant_mob1 = mysql_real_escape_string($_POST['tenant_mob1']);
 $tenant_ph1 = mysql_real_escape_string($_POST['tenant_ph1']);
 $tenant_email1 = mysql_real_escape_string($_POST['tenant_email1']);
 $tenant_firstname2 = mysql_real_escape_string($_POST['tenant_firstname2']);
 $tenant_lastname2 = mysql_real_escape_string($_POST['tenant_lastname2']);
 $tenant_mob2 = mysql_real_escape_string($_POST['tenant_mob2']);
 $tenant_ph2 = mysql_real_escape_string($_POST['tenant_ph2']);
 $tenant_email2 = mysql_real_escape_string($_POST['tenant_email2']);
 $prop_comments = mysql_real_escape_string($_POST['prop_comments']);
 $key_number = mysql_real_escape_string($_POST['key_number']);
 $tenants_changed = mysql_real_escape_string($_POST['tenants_changed']);

 $is_eo = mysql_real_escape_string($_POST['is_eo']);

 $not_compliant_notes = mysql_real_escape_string($_POST['not_compliant_notes']);
 $lockbox_code = mysql_real_escape_string($_POST['lockbox_code']);

 $preferred_time = mysql_real_escape_string($_POST['preferred_time']);
 

if ($doaction == "update") {

    if ($prop_vac == 1 && !empty($preffered_time)) {        
        // Update jobs table
        $preferred_time_sql = "UPDATE `jobs` SET preferred_time = '' where `id` = {$job_id}";
        mysql_query($preferred_time_sql);
    }

    // if status is changed
    $status_changed_flag_str = '';
    if ($status_changed_flag == 1) {
        $status_changed_flag_str = " `status_changed_timestamp` = '" . date('Y-m-d H:i:s') . "', ";
        if ($status == 'Allocate') {
            $status_changed_flag_str .= "
				`allocate_timestamp` = '" . date('Y-m-d H:i:s') . "',
				`allocated_by` = " . $_SESSION['USER_DETAILS']['StaffID'] . ",
				";
        } else {
            $status_changed_flag_str .= "
					`allocate_timestamp` = NULL,
					`allocated_by` = NULL,
					`allocate_opt` = NULL,
					`allocate_notes` = NULL,
					`allocate_response` = NULL,
				";
        }
    }

    $call_before = mysql_real_escape_string($_POST['call_before']);
    $call_before_txt = mysql_real_escape_string($_POST['call_before_txt']);

    $call_before_str = '';
    if ($call_before == 1) {
        $call_before_str = "
			, `call_before` = '{$call_before}'
			, `call_before_txt` = '{$call_before_txt}'
		";
    }


    $jdate2 = date("Y-m-d", strtotime(str_replace("/", "-", $_POST['jobdate'])));
    if (($status == "Booked" || $status == "To Be Booked" || $status == "DHA") && ( $_POST['jobdate'] != "" && $_POST['jobdate'] != "00/00/0000" && $jdate2 < date("Y-m-d") )) {
        $error .= "Date can not be older than today";
    } else if (!$job_editable) {
        echo "<div class='warn'>You do not have permission to update this job\n</div>";
    } else {

        $cancelled_str = '';

        // echo "Comments are $comments...\n";

        if ($_POST['urgent_job'] == 1) {
            $urgent_job_reason = $_POST['urgent_job_reason'];
            $urgent_job_str = " `urgent_job` = 1, `urgent_job_reason` = '" . mysql_real_escape_string($urgent_job_reason) . "',";
        } else {
            $urgent_job_str = " `urgent_job` = 0,";
        }

        $dk = ($_POST['door_knock'] == 1) ? mysql_real_escape_string($_POST['door_knock']) : 0;

        // (2) Run the query
        //echo "Job Date: ".$jobdate;
        if ($jobtype == "240v Rebook" && ( $status == "Merged Certificates" || $status == "Completed" )) {
            //$error = "Job type can't be 240v Rebook";
            $error .= "Cannot Update 240v Rebook to Completed or Merge<br />";
        } else if ($status == "Booked") {

            if ($jobdate == "" || $jobdate == "0000-00-00") {
                $error .= "Date can't be Empty <br />";
                $el[] = 'date';
            }

            if ($_POST['booked_by'] == "") {
                $error .= "Booked By can't be Empty <br />";
                $el[] = 'booked by';
            }

            if ($_POST['door_knock'] == 0) {

                if ($_POST['timeofday'] == "") {
                    $error .= "Time of Day can't be Empty <br />";
                    $el[] = 'time';
                }
                if ($_POST['booked_with'] == "") {
                    $error .= "Booked With can't be Empty <br />";
                    $el[] = 'booked with';
                }
            }


            if ($techid == '') {
                $error .= "Technician can't be Empty <br />";
                $el[] = 'tech';
            }


            //echo "electrician_only: {$electrician_only}";
            if ($electrician_only == 1 && isStaffElectrician($techid) == false) {
                $error .= "Tech Must be Electrician for this Agency<br />";
            }

            if ( ( $jobtype == '240v Rebook' || $is_eo == 1 ) && isStaffElectrician($techid) == false) {
                $error .= "Tech must Be Electrician for 240v Rebook<br />";
            }
        } else if ($status == "On Hold" && $_POST['start_date'] == "") {
            $error .= "Can't change to On Hold if start date is Empty <br />";
        } else if ($status == "DHA" && $_POST['start_date'] == "" && $_POST['due_date'] == "") {
            $error .= "Start date and Due date can't be Empty <br />";
        } else if ($status == "Cancelled") {
            $cancelled_str = ",`cancelled_date` = '" . date('Y-m-d') . "'";
        } else if ($status == "Completed") {

            if ($jobdate == "" || $jobdate == "0000-00-00") {
                $error .= "Date can't be Empty <br />";
                $el[] = 'date';
            }

            if ($techid == '') {
                $error .= "Technician can't be Empty <br />";
                $el[] = 'tech';
            }
        }




        /*
          if( $jobtype == 'Fix or Replace' && isStaffElectrician($techid) == false && getAllExpired240vAlarm($_GET['id'])==true ){
          $error .= "Tech must Be Electrician for this Job<br />";
          }


          if( $status=="Escalate" && count($escalate_job_reasons_arr)==0 ){
          $error = "Must check at least one escalate reason";
          }
         */


        /*
          // Tech Run Keys - Key Access Required Marker
          if( $key_access_required==1 ){
          $kar_sql_str = "
          ,`trk_kar` = '1'
          ,`trk_tech` = '{$techid}'
          ,`trk_date` = '{$jobdate}'
          ,`tkr_approved_by` = '{$key_access_details}'
          ";
          }
         */

        if ($error == "") {

            $no_dates_provided = ($_POST['no_dates_provided']) ? $_POST['no_dates_provided'] : 0;


            $ss_reason_append_str = '';
            $ss_reason_append_str = ( $ss_reason != '' ) ? "`ts_safety_switch_reason` = '{$ss_reason}'," : null;

            // job updates
            $orig_job_sql = mysql_query("
            SELECT
                j.`job_type`,
                j.`date` AS jdate,
                j.`work_order`,
                j.`status` AS jstatus,
                j.`property_vacant`,
                j.`booked_with`,
                j.`time_of_day`,
                j.`assigned_tech`,
                j.`job_price`,
                j.`comments` AS j_comments,

                p.`key_number`,
                p.`alarm_code`,
                p.`comments`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE j.`id` = {$job_id}
            ");
            $orig_job_row = mysql_fetch_array($orig_job_sql);

            $indv_job_log_arr = [];

            // Job Type
            $updated_field = 'Job Type';
            $orig_val = $orig_job_row['job_type']; // original value
            $update_val = $jobtype; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = $orig_val;
                $to_txt = $update_val;

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";                
            
            }

            // Job Date
            $updated_field = 'Job Date';
            $orig_val = $orig_job_row['jdate']; // original value
            $update_val = $jobdate; // update to new value

            $orig_val = ( $crm->isDateNotEmpty($orig_val) == true )?$orig_val:null; // reset 0 to empty/null

            if ( $update_val != $orig_val ) {

                $from_txt = ( $crm->isDateNotEmpty($orig_val) == true )?date('d/m/Y',strtotime($orig_val)):'NULL';
                $to_txt = ( $crm->isDateNotEmpty($update_val) == true )?date('d/m/Y',strtotime($update_val)):'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }

            // Key Number
            $updated_field = 'Key Number';
            $orig_val = $orig_job_row['key_number']; // original value
            $update_val = $key_number; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = ( $orig_val != '' )?$orig_val:'NULL';
                $to_txt =  ( $update_val != '' )?$update_val:'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }


            // Work Order
            $updated_field = 'Work Order';
            $orig_val = $orig_job_row['work_order']; // original value
            $update_val = $work_order; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = ( $orig_val != '' )?$orig_val:'NULL';
                $to_txt =  ( $update_val != '' )?$update_val:'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }


            // House Alarm Code
            $updated_field = 'House Alarm Code';
            $orig_val = $orig_job_row['alarm_code']; // original value
            $update_val = $alarm_code; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = ( $orig_val != '' )?$orig_val:'NULL';
                $to_txt =  ( $update_val != '' )?$update_val:'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }


            // property vacant
            $updated_field = 'Property Vacant';
            $orig_val = $orig_job_row['property_vacant']; // original value
            $update_val = $prop_vac; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = $orig_val;
                $to_txt = $update_val;

                $prop_vac_str = ( $update_val == 1 ) ? 'marked' : 'unmarked';

                $indv_job_log_arr[] = "Property {$prop_vac_str} <strong>Vacant</strong>";

                if ($update_val == 1) {

                    // check if the old preferred_time data
                    $preferred_time_query = "SELECT `preferred_time` FROM `jobs` where `id` = {$job_id}"; 
                    $result = mysql_query($preferred_time_query);
            
                    $row = mysql_fetch_row($result);
                    $preferred_time_empty = empty($row[0]) ? "empty" : $row[0];
            
                    // Insert job_logs table
                    $log_msg = "Property marked Vacant and Preferred time Removed, preferred time was <b>{$preferred_time_empty}</b>"; 

                    $params = array(
                        'job_id' => $job_id,
                        'log_type' => 'Job Update',
                        'log_msg' => $log_msg
                    );
    
                    $crm->insertJobLog($params);
                    
                    // Update jobs table
                    $preferred_time_sql = "UPDATE `jobs` SET preferred_time = '' where `id` = {$job_id}";
                    mysql_query($preferred_time_sql);
                }


            }


            // Time of Day
            $updated_field = 'Time of Day';
            $orig_val = $orig_job_row['time_of_day']; // original value
            $update_val = $timeofday; // update to new value

            if ( $update_val != $orig_val ) {

                $from_txt = ( $orig_val != '' )?$orig_val:'NULL';
                $to_txt =  ( $update_val != '' )?$update_val:'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }


            // Booked With
            $updated_field = 'Booked With';
            $orig_val = $orig_job_row['booked_with']; // original value
            $update_val = $booked_with; // update to new value

            if ( $update_val != $orig_val && $status != 'Booked' ) {

                $from_txt = ( $orig_val != '' )?$orig_val:'NULL';
                $to_txt =  ( $update_val != '' )?$update_val:'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }

            // Technician
            $updated_field = 'Technician';
            $orig_val = $orig_job_row['assigned_tech']; // original value
            $update_val = $techid; // update to new value

            $orig_val = ( $orig_val > 0 )?$orig_val:null; // reset 0 to empty/null

            if ( $update_val != $orig_val ) {

                $from_txt = ( $orig_val > 0 )?getTechName($orig_val):'NULL';
                $to_txt = ( $update_val > 0 )?getTechName($update_val):'NULL';

                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }

            // Property Notes/Comments
            $updated_field = 'Property Notes';
            $orig_val = $orig_job_row['comments']; // original value
            $update_val = $prop_comments; // update to new value

            if ( $update_val != $orig_val ) {
                $from_txt = (!empty($orig_val))?$orig_val:'NULL';
                $to_txt =  (!empty($update_val))?$update_val:'NULL';
                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";
            }

            // job comments update 
            $updated_field = 'Job comments';
            $orig_val = $orig_job_row['j_comments']; // original value
            $update_val = $comments; // update to new value
            
            if ( $update_val != $orig_val ){

                $from_txt = (!empty($orig_val))?$orig_val:'NULL';
                $to_txt =  (!empty($update_val))?$update_val:'NULL';
                $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

            }

            $updateQuery = "
				UPDATE jobs
				set
					price_reason='$job_price_reason',
					price_detail='$job_price_detail',
					date=".( ( $jobdate != '' )?"'{$jobdate}'":'NULL' ).",
					status='$status',
					comments='$comments',
					`tech_comments`='{$tech_comments}',
					auto_renew='$autorenew',
					job_type='$jobtype',
					time_of_day='$timeofday',

                    assigned_tech=".( ( $techid != '' )?"'{$techid}'":'NULL' ).",

					job_price='$job_price',
					work_order='$work_order',
					{$urgent_job_str}
					key_access_required = '$key_access_required',
					`door_knock` = '{$dk}',
					`due_date` = {$due_date},
					`start_date` = {$start_date},
					`key_access_details` = '{$key_access_details}',
					`tech_notes` = '{$tech_notes}',
					`booked_with` = '{$booked_with}',
					`booked_by` = '{$booked_by}',
					`no_dates_provided` = {$no_dates_provided},
					`job_entry_notice` = '{$job_entry_notice}',

					{$status_changed_flag_str}

                    `allocate_notes` = '{$allocate_notes}',
					`survey_numlevels` = '{$survey_numlevels}',
					`survey_ceiling` = '{$survey_ceiling}',
					`survey_ladder` = '{$survey_ladder}',

					`ts_safety_switch` = '{$ts_safety_switch}',

					{$ss_reason_append_str}

					`ss_location` = '{$ss_location}',
					`ss_quantity` = '{$ss_quantity}',

					`ps_number_of_bedrooms` = '{$ps_number_of_bedrooms}',

					`to_be_printed` = '{$to_be_printed}',

					`property_vacant` = '{$prop_vac}',
					`show_as_paid` = '{$show_as_paid}',

					`dha_need_processing` = '{$dha_need_processing}',

					`out_of_tech_hours` = '{$out_of_tech_hours}',
                    `call_before` = '{$call_before}',

					`job_priority` = '{$job_priority}',

                    `is_eo` = '{$is_eo}',

					`repair_notes` = '{$repair_notes}'
					{$call_before_str}
					{$kar_sql_str}
					{$cancelled_str}                   
				WHERE (id = $job_id);
			";
            $queryresult = mysql_query($updateQuery, $connection);

            if($queryresult){
                // job status
                $updated_field = 'Job status';
                $orig_val = $orig_job_row['jstatus']; // original value
                $update_val = $status; // update to new value

                if ( $update_val != $orig_val ) {

                    $from_txt = $orig_val;
                    $to_txt = $update_val;

                    $indv_job_log_arr[] = "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>";

                }
            }

             // insert job log
            if( count($indv_job_log_arr) > 0  ){

                $combined_job_log = implode(" | ",$indv_job_log_arr);

                $params = array(
                    'job_id' => $job_id,
                    'log_type' => 'Job Update',
                    'log_msg' => $combined_job_log
                );

                $crm->insertJobLog($params);

            }

           // log job type update 
           $crm->insert_job_markers($job_id,$jobtype);


            // get job details
            $job_sqll = mysql_query("
            SELECT 
                j.`property_vacant`, 

                p.`pm_id_new`, 

                a.`allow_en`,
                a.`en_to_pm`,
                a.`send_en_to_agency`,
                a.`agency_emails`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`id` = {$job_id}
            ");
            $job_roww = mysql_fetch_object($job_sqll);

            $entrynotice_toemails = [];
            $en_tent_emails_arr = [];
            $en_agency_emails_arr = [];
            $en_pm_emails_arr = [];
            if ( 
                ( 
                    ( $curr_status != 'Booked' && $status == "Booked" ) && 
                    $job_roww->allow_en == 2 
                ) && 
                $job_roww->property_vacant != 1 
            ){

                // copied EN button function below            
                // Prepare Entry Log notice
                // i dont know what this part?
                $params = new StdClass;
                $params->contact_type = 'E-mail';
                $params->eventdate = date('Y-m-d');
                //$params->job_id = $email_job_details['id'];
                $params->job_id = $job_id;
                $params->staff_id = $_SESSION['USER_DETAILS']['StaffID'];
                
                // get tenants 
                $pt_params = array(
                    'property_id' => $property_id,
                    'active' => 1
                );
                $pt_sql = $crm->getNewTenantsData($pt_params);

                // get tenant emails               
                while ($pt_row = mysql_fetch_array($pt_sql)) {

                    // tenants email
                    $tenant_email = trim($pt_row["tenant_email"]);
                    if (filter_var(trim($tenant_email), FILTER_VALIDATE_EMAIL)) {
                        $entrynotice_toemails[] = $tenant_email; 
                        $en_tent_emails_arr[] = $tenant_email;                                      
                    }
                    
                }

                $params->comments = "Entry notice emailed to Tenants Only @ " . date("H:i");

                // send to PM
                if( $job_roww->en_to_pm == 1 ) {

                    // pm id
                    $pm_id = $job_roww->pm_id_new;

                    // If property has PM with valid email
                    $pm_sql = mysql_query("
                        SELECT `email`
                        FROM `agency_user_accounts`
                        WHERE `agency_user_account_id` = {$pm_id}
                        AND `email` != ''
                        AND `email` IS NOT NULL
                    ");
                    if (mysql_num_rows($pm_sql) > 0) {

                        // email not empty, lets validate it
                        $pm = mysql_fetch_array($pm_sql);
                        $pm_email2 = trim($pm['email']);
                        $pm_email3 = preg_replace('/\s+/', '', $pm_email2);
                        if (filter_var($pm_email3, FILTER_VALIDATE_EMAIL)) {
                            $entrynotice_toemails[] = $pm_email3;
                            $en_pm_emails_arr[] = $pm_email3;
                        }
                    }

                    $params->comments = "Entry notice emailed to Tenants and Property Managers @ " . date("H:i");

                }

                // PM email only exist when en_to_pm = 1, if not send to agency which is the default action
                if (count($en_pm_emails_arr) == 0) {

                    if( $job_roww->send_en_to_agency == 1 ) {

                        // agency email
                        $temp = explode("\n", trim($job_roww->agency_emails));
                        foreach ($temp as $val) {
                            $val2 = preg_replace('/\s+/', '', $val);
                            if (filter_var($val2, FILTER_VALIDATE_EMAIL)) {
                                $entrynotice_toemails[] = $val2;
                                $en_agency_emails_arr[] = $val2;
                            }
                        }

                        $params->comments = "Entry notice emailed to Tenants and Agency @ " . date("H:i");
                    } else {

                        $params->comments = "Entry notice emailed to Tenants @ " . date("H:i");
                    }

                }                
                
                if ( count($entrynotice_toemails) > 0 ){

                    // copied from STR EN ajax_send_entry_notice_in_bulk.php 
                    // update job, this update needs to happen before the EN's are sent
                    mysql_query("
                    UPDATE `jobs`
                    SET `en_date_issued` = '".date("Y-m-d")."'
                    WHERE `id` ={$job_id}
                    ");

                    // copied EN button function below            
                    // Get Job Details
                    $Query = getJobDetails($job_id, true);
                    $email_job_details = mysqlSingleRow($Query);

                    // send EN email
                    if (sendEntryNoticeEmail($email_job_details, $entrynotice_toemails)) {
                        
                        $params->eventtime = date('h:i:s');

                        // update entry_notice_emailed
                        mysql_query("
                        UPDATE `jobs`
                        SET 
                            `entry_notice_emailed` = '" . date("Y-m-d H:i:s") . "',
                            `job_entry_notice` = 1
                        WHERE `id` = {$job_id}
                        ");

                        $en_tent_emails_imp = implode(', ', $en_tent_emails_arr);
                        $en_agency_emails_imp = implode(', ', $en_agency_emails_arr);
                        $en_pm_emails_imp = implode(', ', $en_pm_emails_arr);

                        $append_en_agency_emails = ( count($en_agency_emails_arr) > 0 ) ? " and <strong>{$en_agency_emails_imp}</strong>" : "";
                        $append_en_pm_emails = ( count($en_pm_emails_arr) > 0 ) ? " and <strong>{$en_pm_emails_imp}</strong>" : "";

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
                        'Entry Notice',
                        '" . date('Y-m-d') . "',
                        'Entry noticed emailed to: <strong>" . $en_tent_emails_imp . "</strong>" . $append_en_agency_emails . "" . $append_en_pm_emails . "',
                        {$job_id},
                        '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
                        '" . date('H:i') . "'
                        )
                        ");

                    }

                }                                

            }           


            // clear selected job escalate reason first
            mysql_query("
				DELETE
				FROM `selected_escalate_job_reasons`
				WHERE `job_id` = {$job_id}
			");


            if ($status == "Escalate") {

                mysql_query("
						INSERT INTO
						`selected_escalate_job_reasons` (
							`job_id`,
							`escalate_job_reasons_id`,
							`date_created`,
							`deleted`,
							`active`
						)
						VALUES (
							{$job_id},
							{$escalate_job_reasons},
							'" . date('Y-m-d H:i:s') . "',
							0,
							1
						)
					");


                // insert job log
                // get escalate job reason
                $ejr_sql = mysql_query("
						SELECT `reason_short`
						FROM `escalate_job_reasons`
						WHERE `escalate_job_reasons_id` = {$escalate_job_reasons}
					");
                $ejr = mysql_fetch_array($ejr_sql);

                // job log
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
							'Escalate Job',
							'" . date('Y-m-d') . "',
							'Job marked <strong>Escalate</strong> due to <strong>{$ejr['reason_short']}</strong>',
							{$job_id},
							'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
							'" . date('H:i') . "'
						)
					");
            }


            // insert logs for booked
            if ($curr_status != 'Booked' && $status == "Booked") {
                // get Updated Job Status
                $check_job_status = mysql_query("SELECT `status` FROM `jobs` WHERE `id`={$job_id}");
                $job_status_row = mysql_fetch_array($check_job_status);
                $is_booked = $job_status_row['status'];

                if($is_booked != $status){
                    $error .= "Error occurred that caused job status to not update, please try again<br />";
                }

                // get tech name
                $tech_sql = mysql_query("
                    SELECT *
                    FROM `staff_accounts`
                    WHERE `StaffID` = {$techid}
                ");
                $tech = mysql_fetch_array($tech_sql);
                $tech_name = $crm->formatStaffName($tech['FirstName'],$tech['LastName']);

                // get booked by name
                $booked_by_sql = mysql_query("
                    SELECT *
                    FROM `staff_accounts`
                    WHERE `StaffID` = {$booked_by}
                ");
                $booked_by_row = mysql_fetch_array($booked_by_sql);
                $booked_by_name = $crm->formatStaffName($booked_by_row['FirstName'],$booked_by_row['LastName']);

                // if door knocked ticked
                $job_date_dmy = date("d/m/Y",strtotime(str_replace("/","-",$_POST['jobdate'])));
                if ($_POST['door_knock'] == 1) {
                    $jl_ct = 'Door Knock Booked';
                    //$jl_comment = "By {$staff_name2} @ ".date("H:i");                   
                    $jl_comment = "Door Knock Booked for <strong>{$job_date_dmy}</strong>. Technician <strong>{$tech_name}</strong>";
                } else {
                    $jl_ct = 'Job Booked';
                    //$jl_comment = "By {$staff_name2} @ ".date("H:i");
                    $jl_comment = "
                    Booked with <strong>{$booked_with}</strong> for <strong>{$job_date_dmy}</strong> @ <strong>{$timeofday}</strong>.
                    Technician <strong>{$tech_name}</strong>
                    Booked By <strong>{$booked_by_name}</strong>
                    ";
                }

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
                        '{$jl_ct}',
                        '" . date('Y-m-d') . "',
                        '{$jl_comment}',
                        {$job_id},
                        '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
                        '" . date('H:i') . "'
                    )
                ");
            }


            # If the current key access was set to no, and it has been changed to yes, add a job contact log of the staff member who made the change.
            if ($current_key_access_required == 0 && $key_access_required == 1) {
                $insertQuery = "INSERT INTO job_log (contact_type,eventdate,comments,job_id, staff_id, eventtime) VALUES ('Other','" . date('Y-m-d') . "','Key access set to yes','$job_id', '" . $_SESSION['USER_DETAILS']['StaffID'] . "', '" . date('H:i') . "' );";
                mysql_query($insertQuery);
            }



        }

        /*
          if($job_price != $_POST['orig_price'] || $job_price_reason != $_POST['orig_reason'] || $job_price_detail != $_POST['orig_detail']){
          $insertQuery = "INSERT INTO job_log (contact_type,eventdate,comments,job_id, staff_id) VALUES ('Price Changed','" . date('Y-m-d') . "','New Price- $".$job_price.", Reason- ".$job_price_reason.", Details- ".$job_price_detail."', $job_id,'" . $_SESSION['USER_DETAILS']['StaffID'] . "');";
          mysql_query($insertQuery);
          }
         */



        $staff_name2 = $_SESSION['USER_DETAILS']['FirstName'] . " " . $_SESSION['USER_DETAILS']['LastName'];






        /*
        // add log for tech update
        if (( $booked_with_orig != '' && $booked_with != '' ) && $booked_with_orig != $booked_with) {

            $updated_field = 'Booked With';
            $from_txt = $booked_with_orig;
            $to_txt = $booked_with;

            $params = array(
                'job_id' => $job_id,
                'log_type' => $log_type_txt,
                'log_msg' => "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>"
            );
            $crm->insertJobLog($params);
        }
        */


        /*
          // job type
          $log_type_txt = 'Job Update';
          if( $jobtype != $curr_job_type ){

          $updated_field = 'Job type';
          $from_txt = $curr_job_type;
          $to_txt = $jobtype;

          $params = array(
          'job_id' => $job_id,
          'log_type' => $log_type_txt,
          'log_msg' => "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>"
          );
          $crm->insertJobLog($params);

          }



          // date
          if( $jobdate != $jobdate_orig ){

          $updated_field = 'Date';
          $from_txt = date('d/m/Y',strtotime($jobdate_orig));
          $to_txt = date('d/m/Y',strtotime($jobdate));

          $params = array(
          'job_id' => $job_id,
          'log_type' => $log_type_txt,
          'log_msg' => "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>"
          );
          $crm->insertJobLog($params);

          }






          // Booked By
          if( $booked_by != $booked_by_orig ){

          $updated_field = 'Booked By';
          $from_txt = getStaffName($booked_by_orig);
          $to_txt = getStaffName($booked_by);

          $params = array(
          'job_id' => $job_id,
          'log_type' => $log_type_txt,
          'log_msg' => "{$updated_field} updated from <strong>{$from_txt}</strong> to <strong>{$to_txt}</strong>"
          );
          $crm->insertJobLog($params);

          }
         */






        # Update Booking staff if needed - update now only if status is Booked.
        /*
          if(mysql_affected_rows() > 0 && $status == 'Booked')
          {
          $updateBooking = "UPDATE jobs set staff_id = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' WHERE (id = $job_id);";
          mysql_query($updateBooking);
          }
         */

        # Update No show if 'Status' was changed
        if ($_POST['curr_status'] != $_POST['status']) {
            /*
              $query = "UPDATE jobs SET ts_noshow = 0, ts_doorknock = 0 WHERE id = '" . $job_id . "' LIMIT 1";
              mysql_query($query) or die(mysql_error());
             */

            // clear job reason
            mysql_query("
				UPDATE `jobs`
				SET `job_reason_id` = 0
				WHERE `id` = {$job_id}
			");


            // if($_POST['status'] == 'Booked')
            // {
            //  # Log staff ID against the booking
            //  $query = "UPDATE jobs SET staff_id = '{$_SESSION['USER_DETAILS']['StaffID']}' WHERE id = '" . $job_id . "' LIMIT 1";
            //  mysql_query($query) or die(mysql_error());
            // }
        }

        // update smoke details
        $sa_serv_manipulated = $_POST['sa_serv_manipulated'];
        $alarm_id = $_POST['alarm_id'];
        $ts_position = $_POST['ts_position'];
        $alarm_power_id = $_POST['alarm_power_id'];
        $alarm_type_id = $_POST['alarm_type_id'];
        $ts_required_compliance = $_POST['ts_required_compliance'];
        $new = $_POST['newinstall'];
        $alarm_price = $_POST['alarm_price'];
        $alarm_reason_id = $_POST['alarm_reason_id'];
        $make = $_POST['make'];
        $model = $_POST['model'];
        $expiry = $_POST['expiry'];
        $ts_db_rating = $_POST['ts_db_rating'];
        // ts_alarm_sounds_other should be the correct db field
        $ts_is_alarm_ic = $_POST['ts_is_alarm_ic'];


        foreach ($alarm_id as $index => $val) {
            if ($sa_serv_manipulated[$index] == 1) {
                mysql_query("
					UPDATE `alarm`
					SET
						`ts_position` = '" . strtoupper(mysql_real_escape_string($ts_position[$index])) . "',
						`alarm_power_id` = '" . mysql_real_escape_string($alarm_power_id[$index]) . "',
						`alarm_type_id` = '" . mysql_real_escape_string($alarm_type_id[$index]) . "',
						`ts_required_compliance` = '" . mysql_real_escape_string($ts_required_compliance[$index]) . "',
						`new` = '" . mysql_real_escape_string($new[$index]) . "',
						`alarm_price` = '" . mysql_real_escape_string($alarm_price[$index]) . "',
						`alarm_reason_id` = '" . mysql_real_escape_string($alarm_reason_id[$index]) . "',
						`make` = '" . strtoupper(mysql_real_escape_string($make[$index])) . "',
						`model` = '" . strtoupper(mysql_real_escape_string($model[$index])) . "',
						`expiry` = '" . mysql_real_escape_string($expiry[$index]) . "',
						`ts_db_rating` = '" . mysql_real_escape_string($ts_db_rating[$index]) . "',
						`ts_alarm_sounds_other` = '" . mysql_real_escape_string($ts_is_alarm_ic[$index]) . "'
					WHERE `alarm_id` = '" . mysql_real_escape_string($val) . "'
					AND `job_id` = {$_GET['id']}
				");
            }
        }




        // DISCARDED
        $sa_serv_manipulated = $_POST['disc_sa_serv_manipulated'];
        $alarm_id = $_POST['disc_alarm_id'];
        $ts_position = $_POST['disc_ts_position'];
        $alarm_power_id = $_POST['disc_alarm_power_id'];
        $alarm_type_id = $_POST['disc_alarm_type_id'];
        $ts_required_compliance = $_POST['disc_ts_required_compliance'];
        $new = $_POST['disc_newinstall'];
        $alarm_price = $_POST['disc_alarm_price'];
        $alarm_reason_id = $_POST['disc_alarm_reason_id'];
        $make = $_POST['disc_make'];
        $model = $_POST['disc_model'];
        $expiry = $_POST['disc_expiry'];
        $ts_db_rating = $_POST['disc_ts_db_rating'];
        // ts_alarm_sounds_other should be the correct db field
        $ts_is_alarm_ic = $_POST['disc_ts_is_alarm_ic'];


        foreach ($alarm_id as $index => $val) {
            if ($sa_serv_manipulated[$index] == 1) {
                mysql_query("
					UPDATE `alarm`
					SET
						`ts_position` = '" . strtoupper(mysql_real_escape_string($ts_position[$index])) . "',
						`alarm_power_id` = '" . mysql_real_escape_string($alarm_power_id[$index]) . "',
						`alarm_type_id` = '" . mysql_real_escape_string($alarm_type_id[$index]) . "',
						`ts_required_compliance` = '" . mysql_real_escape_string($ts_required_compliance[$index]) . "',
						`new` = '" . mysql_real_escape_string($new[$index]) . "',
						`alarm_price` = '" . mysql_real_escape_string($alarm_price[$index]) . "',
						`ts_discarded_reason` = '" . mysql_real_escape_string($alarm_reason_id[$index]) . "',
						`make` = '" . strtoupper(mysql_real_escape_string($make[$index])) . "',
						`model` = '" . strtoupper(mysql_real_escape_string($model[$index])) . "',
						`expiry` = '" . mysql_real_escape_string($expiry[$index]) . "',
						`ts_db_rating` = '" . mysql_real_escape_string($ts_db_rating[$index]) . "',
						`ts_alarm_sounds_other` = '" . mysql_real_escape_string($ts_is_alarm_ic[$index]) . "'
					WHERE `alarm_id` = '" . mysql_real_escape_string($val) . "'
					AND `job_id` = {$_GET['id']}
				");
            }
        }



        // update safety switch
        $ss_serv_manipulated = $_POST['ss_serv_manipulated'];
        $ss_id = $_POST['ss_id'];
        $ss_make = $_POST['ss_make'];
        $ss_model = $_POST['ss_model'];
        $ss_test = $_POST['ss_test'];
        $ss_new_update = $_POST['ss_new_update'];
        $ss_reason_update = $_POST['ss_reason_update'];
        $ss_pole_update = $_POST['ss_pole_update'];

        foreach ($ss_id as $index => $val) {

            if ($ss_serv_manipulated[$index] == 1) {

                $ss_test2 = ($ss_test[$index] == "") ? 'NULL' : "'" . strtoupper(mysql_real_escape_string($ss_test[$index])) . "'";
                $ss_reason_update2 = ($ss_reason_update[$index] == "") ? 'NULL' : "'" . strtoupper(mysql_real_escape_string($ss_reason_update[$index])) . "'";
                $ss_pole_update2 = ($ss_pole_update[$index] == "") ? 'NULL' : "'" . strtoupper(mysql_real_escape_string($ss_pole_update[$index])) . "'";
                
                mysql_query("
					UPDATE `safety_switch`
					SET
						`make` = '" . strtoupper(mysql_real_escape_string($ss_make[$index])) . "',
						`model` = '" . strtoupper(mysql_real_escape_string($ss_model[$index])) . "',
						`test` = {$ss_test2},
                        `new` = '" . strtoupper(mysql_real_escape_string($ss_new_update[$index])) . "',
						`ss_res_id` = {$ss_reason_update2},
                        `ss_stock_id` = {$ss_pole_update2}
					WHERE `safety_switch_id` = '" . mysql_real_escape_string($val) . "'
					AND `job_id` = {$_GET['id']}
				");

            }

        }

        // update corded window update
        $cw_serv_manipulated = $_POST['cw_serv_manipulated'];
        $corded_window_id = $_POST['corded_window_id'];
        $location = $_POST['location'];
        $num_of_windows = $_POST['num_of_windows'];

        foreach ($corded_window_id as $index => $val) {
            if ($cw_serv_manipulated[$index] == 1) {
                mysql_query("
					UPDATE corded_window
					SET
						`location` = '" . mysql_real_escape_string($location[$index]) . "',
						`num_of_windows` = '" . mysql_real_escape_string($num_of_windows[$index]) . "'
					WHERE `corded_window_id` = '" . mysql_real_escape_string($val) . "'
					AND `job_id` = {$_GET['id']}
				");
            }
        }




        // update WE
        $we_serv_manipulated = $_POST['we_serv_manipulated'];
        $we_id_arr = $_POST['we_id'];
        $we_location = $_POST['we_location'];
        $we_note = $_POST['we_note'];
        $we_device = $_POST['we_device'];
        $we_pass = $_POST['we_pass'];

        foreach ( $we_id_arr as $index => $we_id ) {

            if ( $job_id > 0 && $we_serv_manipulated[$index] == 1 ) {

                $we_device_val = ( $we_device[$index] != '' )?mysql_real_escape_string($we_device[$index]):'NULL';
                $we_pass_val = ( $we_pass[$index] != '' )?mysql_real_escape_string($we_pass[$index]):'NULL';

                $we_insert_sql_str = "
                    UPDATE water_efficiency
                    SET
                        `device` = {$we_device_val},
                        `pass` = {$we_pass_val},
                        `location` = '".mysql_real_escape_string($we_location[$index])."',
                        `note` = '".mysql_real_escape_string($we_note[$index])."'
                    WHERE `water_efficiency_id` = ".mysql_real_escape_string($we_id)."
                    AND `job_id` = {$job_id}
                ";
                mysql_query($we_insert_sql_str);

            }

        }


        //$job_editable = canJobBeEdited($job_id, $_SESSION['USER_DETAILS']['StaffID']);
        // Update Tech Sheet Alarm Type - Now on property Page
        // updateTechSheetAlarmTypes($_POST['property_id'], $_POST['alarm_job_type']);

        $update_job_success = '';
        if ($error == "") {
            $update_job_success = "<div class='success'>Database Updated\n</div>";
        }
    }




    if ($tenants_changed == 1) {
        $str = "
		, `tenant_changed` = '" . date("Y-m-d H:i:s") . "'
		";
    }

    // preferred alarm update
    $preferred_alarm_sql_str = null;
    if( $preferred_alarm_id > 0 ){
        $preferred_alarm_sql_str = ",`preferred_alarm_id` = {$preferred_alarm_id}";

        // get current preferred alarm
        $preferred_alarm_sql = mysql_query("
        SELECT 
            p.`preferred_alarm_id`,
            al_p.`alarm_make` AS pref_alarm_make
        FROM `property` AS p
        LEFT JOIN `alarm_pwr` AS al_p ON p.`preferred_alarm_id` = al_p.`alarm_pwr_id`
        WHERE p.`property_id` = '{$property_id}'
        ");
        $preferred_alarm_row = mysql_fetch_object($preferred_alarm_sql);
        $old_preferred_alarm = $preferred_alarm_row->pref_alarm_make;

        // get new preferred alarm
        $preferred_alarm_sql = mysql_query("
        SELECT al_p.`alarm_make` AS pref_alarm_make
        FROM `alarm_pwr` AS al_p
        WHERE al_p.`alarm_pwr_id` = '{$preferred_alarm_id}'
        ");
        $preferred_alarm_row = mysql_fetch_object($preferred_alarm_sql);
        $new_preferred_alarm = $preferred_alarm_row->pref_alarm_make;

        if( $new_preferred_alarm != $old_preferred_alarm ){

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
                'Preferred alarm updated',
                '" . date('Y-m-d') . "',
                'Preferred alarm updated from <b>{$old_preferred_alarm}</b> to <b>{$new_preferred_alarm}</b>',
                {$job_id},
                '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
                '" . date('H:i') . "'
            )
            ");

        }

    }

    $sql = "
		UPDATE `property`
		SET
			`comments` = '{$prop_comments}',
			`alarm_code` = '{$alarm_code}',
			`key_number` = '{$key_number}',
			`qld_new_leg_alarm_num` = '{$qld_new_leg_alarm_num}'
			{$str}
            {$preferred_alarm_sql_str}
		WHERE `property_id` = '{$property_id}'
	";

    mysql_query($sql);


    // check if lockbox exist
    $lb_sql = mysql_query("
    SELECT COUNT(`id`) AS pl_count
    FROM `property_lockbox`
    WHERE `property_id` = {$property_id}
    ");
    $lb_row = mysql_fetch_object($lb_sql);

    if( $lb_row->pl_count > 0 ){ // it exist, update

        mysql_query("
        UPDATE `property_lockbox`
        SET `code` = '{$lockbox_code}'
        WHERE `property_id` = {$property_id}
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
                {$property_id}
            )	
            ");

        }		

    }


    if( $job_id > 0 ){

        // save QLD not compliant notes
        $ejn_sql = mysql_query("
        SELECT COUNT(`id`) AS ejn_count
        FROM `extra_job_notes`
        WHERE `job_id` = {$job_id}
        ");

        $ejn_row = mysql_fetch_object($ejn_sql);

        if( $ejn_row->ejn_count > 0 ){ // update

            mysql_query("
            UPDATE `extra_job_notes`
            SET `not_compliant_notes` = '{$not_compliant_notes}'
            WHERE `job_id` = {$job_id} 
            ");

        }else{ // insert

            if( $not_compliant_notes != '' ){ // do not insert empty notes

                mysql_query("
                INSERT INTO 
                `extra_job_notes`(
                    `job_id`,
                    `not_compliant_notes`
                )
                VALUES(
                    {$job_id},
                    '{$not_compliant_notes}'
                )       
                ");

            }            

        }

    }


} // if update
# Delete job log if necessary
if (is_numeric($_GET['deletelog'])) {
    $query = "UPDATE job_log SET deleted = 1 WHERE log_id = " . $_GET['deletelog'] . " AND job_id = '" . $job_id . "' LIMIT 1";
    mysql_query($query);

    $djl_sql = mysql_query("
		SELECT `contact_type`
		FROM `job_log`
		WHERE `log_id` = {$_GET['deletelog']}
	");
    $djl = mysql_fetch_array($djl_sql);
    $djl_ct = $djl['contact_type'];

    //echo "<br />";

    if ($djl_ct == "Unavailable") {

        //echo 'this is a unavailable job log';
        // unavailable job log
        mysql_query(
                "UPDATE `jobs`
			SET `unavailable_date` = NULL,
			   `unavailable` = 0
			WHERE `id` = {$job_id}
		");
    }
}

// get tenant number from countries
$ctn_sql = mysql_query("
	SELECT `tenant_number`, `iso`
	FROM `countries`
	WHERE `country_id` = {$_SESSION['country_default']}
");
$ctn = mysql_fetch_array($ctn_sql);




// get job details
$job_sql = jGetJobDetails($job_id);
$job = mysql_fetch_array($job_sql);

// get IC alarm services
$ic_serv = getICService();


// AUTO - UPDATE INVOICE DETAILS
$crm->updateInvoiceDetails($job_id);

$job_data_params = array(
    'custom_select' => '
		j.`id` AS jid,
        j.`job_type`,
		j.`status` AS jstatus,
		j.`service` AS jservice,
		j.`created` AS jcreated,
		j.`date` AS jdate,
		j.`comments` AS j_comments,
		j.`assigned_tech`,
		j.`preferred_time_ts`,
		j.`en_date_issued`,
		j.`unpaid`,
        j.`is_pme_invoice_upload`,
        j.`is_pme_bill_create`,
        j.`is_palace_invoice_upload`,
        j.`is_palace_bill_create`,
        j.`is_eo`,

        ejn.`not_compliant_notes`,

		p.`property_id`,
		p.`address_1` AS p_address_1,
		p.`address_2` AS p_address_2,
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		p.`comments` AS p_comments,
		p.`propertyme_prop_id`,
        p.`palace_prop_id`,
		p.`prop_upgraded_to_ic_sa`,
		p.`no_dk`,
		p.`pm_id_new`,
        p.`agency_id`,
        p.`requires_ppe`,
        p.`preferred_alarm_id`,
        p.`qld_new_leg_alarm_num`,

        al_p.`alarm_make` AS pref_alarm_make,

		a.`agency_id` AS a_id,
		a.`phone` AS a_phone,
		a.`address_1` AS a_address_1,
		a.`address_2` AS a_address_2,
		a.`address_3` AS a_address_3,
		a.`state` AS a_state,
		a.`postcode` AS a_postcode,
		a.`trust_account_software`,
		a.`tas_connected`,
		a.`en_to_pm`,
        a.`pme_supplier_id`,
        a.`palace_supplier_id`,
        a.`palace_diary_id`,
        a.`allow_upfront_billing`,
        a.`status` AS a_status, 
        aght.`priority`,
        a.`electrician_only`,
        a.`allow_en`,

		jr.`name` AS jr_name,


		aua.`agency_user_account_id`,
		aua.`fname` AS pm_fname,
		aua.`lname` AS pm_lname,
		aua.`email` AS pm_email,

		ajt.`id` AS ajt_id,
		ajt.`type` AS ajt_type,
        apd.`api`,
        apd.`api_prop_id`,

		ass_tech.`is_electrician`,
        ass_tech.`active` AS tech_active
	',
    'job_id' => $job_id,
    'display_echo' => 0
);
$job_data_sql = $crm->get_job_data($job_data_params);
$job_row = mysql_fetch_array($job_data_sql);
//print_r($job_row);
//exit();

/*
echo "Data Query: <br /><br />";
echo $job_data_sql;
echo "<br /><br />";
echo "Data: <br /><br />";
print_r($job_row);
exit();
*/

$agency_status = $job_row['a_status'];


// get lockbox data
$lockbox_sql_str = "
SELECT `code`
FROM `property_lockbox`
WHERE `property_id` = {$job_row['property_id']}
";
$lockbox_sql = mysql_query($lockbox_sql_str);
$lockbox_sql_row = mysql_fetch_object($lockbox_sql);

//get agency api token
$token_sql = mysql_query("
    SELECT aat.`connection_date`
    FROM `agency_api_tokens` AS aat
    WHERE aat.`api_id` = 1 AND aat.`agency_id` = {$job_row['agency_id']}
");
$token_data = mysql_fetch_array($token_sql);
$showUploadBtn = false;
if (
    (!is_null($token_data['connection_date']) && $token_data['connection_date'] != "") && 
    (!is_null($job_row['api_prop_id']) && $job_row['api_prop_id'] != "" && $job_row['api'] == 1) && 
    (!is_null($job_row['pme_supplier_id']) && $job_row['pme_supplier_id'] != "") &&
    ( $job_row['jstatus'] == 'Merged Certificates' || $job_row['jstatus'] == 'Completed' )
) {
    $showUploadBtn = true;
}
$showUploadBtnPalace = false;
if (
    (!is_null($job_row['api_prop_id']) && $job_row['api_prop_id'] != "" && $job_row['api'] == 4) && 
    (!is_null($job_row['palace_supplier_id']) && $job_row['palace_supplier_id'] != "") && 
    (!is_null($job_row['palace_diary_id']) && $job_row['palace_diary_id'] != "") &&
    ( $job_row['jstatus'] == 'Merged Certificates' || $job_row['jstatus'] == 'Completed' )
) {
    $showUploadBtnPalace = true;
}

if ($job_row['property_id'] != '') {
    ?>
    <style>
        h2.heading{
            <?php /* ?>color: #<?php echo $serv_color; ?>;<?php */ ?>
        }
        .jerr_hl{
            border: 1px solid red;
            box-shadow: 0 0 2px red inset;
        }
        .error_border{
            border: 1px solid #b4151b;
            box-shadow: 0 0 2px #b4151b inset;
        }
        .green_border{
            border: 1px solid green;
            box-shadow: 0 0 2px green inset;
        }
        .hide_qld_only_div, .preferred_time_elem{
            display:none;
        }
        .showIt{
            display: inline;
        }

        .sms_temp_lbl{
            width: 150px !important;
            padding-top: 7px !important;
            display:block !important;
            float: left !important;
            white-space: normal !important;
        }

        .sms_temp_div_row{
            opacity: 0.5;
            clear: both;
        }
        .jcolorItRed{
            color: red;
            font-weight: bold;
        }
        .jcolorItRedNoBold{
            color: red;
        }
        .jcolorItGreen{
            color: #00ae4d;
            font-weight: bold;
        }
        .jItalic{
            font-style: italic;
        }
        .sms_temp_txtbox{
            display:block;
            float: left;
            width: 400px;
            height:91px;
        }
        .btn_sms{
            display: none;
            position: relative;
            left: 15px;
            top: 35px;
        }
        .inner_icon{
            position: relative;
            top: 2px;
            margin-right: 3px;
        }
        .confirm_sms{
            position: relative;
            top: 1px;
        }
        .span_confirm_sms{
            margin-left: 10px;
        }
        .btn_confirm_sms_disabled{
            background-color: #dedede;
        }
        .btn_confirm_sms_disabled:hover{
            background-color: #dedede;
        }
        .green-btn {
            background-color: #00ae4d !important;
        }
        .tr_border_topnBottom{
            border-top: 1px solid #cccccc;
            border-bottom: 1px solid #cccccc;
        }
        .tr_border_notopnBottom{
            border-top: none;
            border-bottom: none;
        }
        #email_temp_lb_div .td_lbl{
            font-weight: bold;
        }
        #email_temp_lb_div {
            text-align: left;
        }
        .call_before_tbl tr.call_before_tr{
            border: none !important;
        }
        .call_before_tbl td{
            border: none !important;
        }
        .sats-breadcrumb ul li:first-child a::after {
            z-index: 1;
        }
        .sats-breadcrumb ul li a::after {
            z-index: 1;
        }
        table.vjc-log tr.border-none td {
            border-bottom: 1px solid #CCCCCC !important;
            border-top: 1px solid #CCCCCC !important;
        }
        .timestamp_style{
            color: #00D1E5;
            font-style: italic;
        }


        .pme_check_icon,
        .pme_x_icon{
            position: relative;
            top: 8px;
            right: 4px;
            width: 27px;
        }

        .pme_tenants_table tr th{
            color: black;
        }
        .pme_tenants_table tr:first-child{
            border: 1px solid #CCCCCC !important;
        }
        .allocate_div{
            border: 1px solid red;
            width: 207px;
            padding: 9px;
            border-radius: 5px;
        }
        .upfront_bill_icon{
            position: relative;
            top: 5px;
            left: 5px;
        }
        .ppe_icon {
            position: relative;
            top: 4px;
            left: 4px;
        }
        .current_alarm_heading,
        .preferred_alarm_div{
            display: inline;
            float: left;
        }
        .preferred_alarm_div{
            margin: 19px 0px 0 16px;
        }
        .not_compliant_notes{
            height: 100px;
            width: 220px;
            margin: 0;            
        }
        table#job_price_variation_table th{
            text-align: right;
        }
        table#job_price_variation_table td{
            text-align: left;
        }
    </style>
    <div id="mainContent" class="vw-jb-dtl-hld">

        <div class="sats-middle-cont">
            <div class="sats-breadcrumb">
                <ul>
                    <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
                    <li class="other first"><a title="View Jobs" href="<?= URL; ?>view_jobs.php">View Jobs</a></li>
                    <li class="other second"><a title="View Job Details" href="/view_job_details.php?id=<?php echo $_REQUEST['id']; ?><?php echo $added_param; ?>"><strong>View Job Details</strong></a></li>
                </ul>
            </div>
            <div id="time"><?php echo date("l jS F Y"); ?></div>


            <div id="tabs" class="c-tabs no-js">
                <div class="c-tabs-nav vjd_tab_div">
                    <a href="#" data-tab_index="0" data-tab_name="job_details" class="c-tabs-nav__link is-active">Job Details</a>
                    <a href="#" data-tab_index="1" data-tab_name="accounts" class="c-tabs-nav__link">Accounts</a>
                </div>
                <!--- Job Details --->
                <div class="c-tab is-active" data-tab_cont_name="job_details">
                    <div class="c-tab__content">

                        <div style="display:none;"><a href="view_job_details_demo.php?id=<?php echo $job_id; ?>" target="_blank">View</a></div>

                        <?php if (in_array($staff_id, $devs)) { ?>

                            <!--
                            <div style="text-align:left; margin: 13px 0;">
                                    <label>Version 2:</label>
                                    <a href="view_job_details_v2.php?id=<?php echo $job_id; ?>" target="_blank">
                                            <button type='submit' class='submitbtnImg'>
                                                    <img class="inner_icon" src="images/button_icons/entry-button.png">
                                            </button>
                                    </a>
                            </div>
                            -->

                            <?php
                        }
                        ?>


                        <?php
                        if ($update_job_success != '') {
                            echo $update_job_success;
                        }

                        if ($_GET['update_job_not_completed'] = 1 && isset($_GET['update_job_not_completed'])) {
                            echo "<div class='success'>Job Not Completed Successfully Updated\n</div>";
                        }


                        # Get Alarms / Appliances

                        $appliances = getPropertyAlarms($job_id, 1, 0, 1);
                        $alarms = getPropertyAlarms($job_id, 1, 0, 2);

                        $safety_switches = mysql_query("
				SELECT *
				FROM `jobs`
				WHERE `id` ={$job_id}
			");

                        //$safety_switches = getPropertyAlarms2($job_id, 0, 1, 4);
                        # Get Tech Sheet Job Types
                        $job_details = getJobDetails($job_id);
                        $job_tech_sheet_job_types = getTechSheetAlarmTypesJob($job_details['property_id'], true);
                        $need_elec = 0;
                        foreach ($job_tech_sheet_job_types as $type) {
                            if ($type == 'Safety Switch - Full Test')
                                $need_elec = 1;
                        }


                        // insert EN date issue on EN sent
                        if ($_GET['entry_notice'] == 'yes' || $_GET['doaction'] == 'emailentrynotice') {

                            $en_sql_str = "
					UPDATE `jobs`
					SET
						`en_date_issued` = '" . date("Y-m-d") . "'
					WHERE `id` ={$job_id}
				";
                            mysql_query($en_sql_str);
                        }


                        # Get Job Details
                        $Query = getJobDetails($job_id, true);
                        $email_job_details = mysqlSingleRow($Query);
                        $result = mysql_query($Query, $connection);
                        $row = mysql_fetch_array($result, MYSQL_BOTH);

                        $prop_add = "{$row[9]} {$row[10]} {$row[11]} {$row[12]} {$row[13]}";
                        $agency_name = $row['agency_name'];
                        $job_date = $row['jdate'];
                        $job_type = $row['job_type'];
                        $electrician_only = $row['electrician_only'];
                        $job_status = $row['jstatus'];
                        $property_id = $row['property_id'];


                        $invoice_amount = $row['invoice_amount'];
                        $invoice_payments = $row['invoice_payments'];
                        $invoice_credits = $row['invoice_credits'];
                        $invoice_balance = $row['invoice_balance'];

                        // put account emails into an array
                        $account_emails_exp = explode("\n", trim($row['account_emails']));
                        // put agency emails into an array
                        $agency_emails_exp = explode("\n", trim($row['agency_emails']));

                        // booked with
                        $jbw = $row['booked_with'];
                        $jstate = $row[12];
                        # Send Entry Notice to booked with



                        if ($_GET['doaction'] == 'emailcert') {
                            if (filter_var($_POST['invoice_email'], FILTER_VALIDATE_EMAIL)) {
                                //echo "invoice_email: {$_POST['invoice_email']}";
                                //print_r($email_job_details);

                                if (sendInvoiceCertEmail($email_job_details, $_POST['invoice_email'])) {
                                    echo "<div class='success'>Email has been sent successfully</div>";
                                    $email_job_details['LastSent'] = date('d/m/Y @ h:i');
                                } else {
                                    echo "<div class='error'>There was a technical problem, please try again.</div>";
                                }
                            } else {
                                echo "<div class='error'>Invalid email address entered, please correct and re-submit</div>";
                            }
                        }

                        /*
                          if($_GET['entry_notice'] == 'yes')
                          {

                          $booked_with_name = $_GET['booked_with'];
                          $booked_with_email = $_GET['booked_with_email'];

                          $entrynotice_toemails = array();


                          // Prepare Entry Log notice
                          $params = new StdClass;
                          $params->contact_type = 'Entry Notice';
                          $params->eventdate = date('Y-m-d');
                          $params->job_id = $email_job_details['id'];
                          $params->staff_id = $_SESSION['USER_DETAILS']['StaffID'];
                          $params->eventime = date("H:i");

                          // Add tenants and agency emails

                          //if(stristr($email_job_details['tenant_email1'], "@")) array_push($entrynotice_toemails, $email_job_details['tenant_email1']);
                          //if(stristr($email_job_details['tenant_email2'], "@")) array_push($entrynotice_toemails, $email_job_details['tenant_email2']);

                          if(filter_var($booked_with_email, FILTER_VALIDATE_EMAIL)){
                          array_push($entrynotice_toemails, $booked_with_email);
                          }
                          $atmp = explode("\n", $email_job_details['agency_emails']);
                          foreach($atmp as $email)
                          {
                          if(stristr($email, "@"))
                          {
                          array_push($entrynotice_toemails, $email);
                          }
                          }

                          $params->comments = "Entry noticed emailed to ".$booked_with_name." and ".$row[25];


                          //$entrynotice_toemails
                          if(sendEntryNoticeEmailBookedWith($email_job_details, $entrynotice_toemails, $booked_with_name))
                          {
                          $params->eventtime = date('h:i:s');
                          // insert logs
                          addJobLog($params);
                          //EntryNoticeLastSent
                          //$email_job_details['EntryNoticeLastSent'] = date('d/m/Y @ h:i A');

                          // update entry_notice_emailed
                          mysql_query("
                          UPDATE `jobs`
                          SET
                          `entry_notice_emailed` = '".date("Y-m-d H:i:s")."'
                          WHERE `id` ={$job_id}
                          ");

                          }

                          echo "<script>window.location='/view_job_details.php?id={$job_id}&send_entry_notice_success=1{$added_param}'</script>";
                          }
                         */




                        # Send Entry Notice
                        if ($_GET['doaction'] == 'emailentrynotice') {

                            $entrynotice_toemails = array();
                            $en_tent_emails_arr = [];
                            $en_agency_emails_arr = [];
                            $en_pm_emails_arr = [];


                            // Prepare Entry Log notice
                            $params = new StdClass;
                            $params->contact_type = 'E-mail';
                            $params->eventdate = date('Y-m-d');
                            $params->job_id = $email_job_details['id'];
                            $params->staff_id = $_SESSION['USER_DETAILS']['StaffID'];

                            // Email to tenants + agency depending on user preference
                            if (array_key_exists('email-tenants', $_POST)) {

                                $pt_params = array(
                                    'property_id' => $job_details['property_id'],
                                    'active' => 1
                                );
                                $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                                while ($pt_row = mysql_fetch_array($pt_sql)) {

                                    // tenant email
                                    if (filter_var(trim($pt_row['tenant_email']), FILTER_VALIDATE_EMAIL)) {
                                        $entrynotice_toemails[] = trim($pt_row['tenant_email']);
                                        $en_tent_emails_arr[] = trim($pt_row['tenant_email']);
                                    }
                                }

                                $params->comments = "Entry notice emailed to Tenants Only @ " . date("H:i");
                            } elseif (array_key_exists('email-tenants-agency', $_POST)) {

                                $pt_params = array(
                                    'property_id' => $job_details['property_id'],
                                    'active' => 1
                                );
                                $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                                while ($pt_row = mysql_fetch_array($pt_sql)) {

                                    // tenant email
                                    if (filter_var(trim($pt_row['tenant_email']), FILTER_VALIDATE_EMAIL)) {
                                        $entrynotice_toemails[] = trim($pt_row['tenant_email']);
                                        $en_tent_emails_arr[] = trim($pt_row['tenant_email']);
                                    }
                                }

                                // send to PM
                                if ($job_row['en_to_pm'] == 1) {

                                    // pm id
                                    $pm_id = $job_row['pm_id_new'];

                                    // If property has PM with valid email
                                    $pm_sql = mysql_query("
							SELECT `email`
							FROM `agency_user_accounts`
							WHERE `agency_user_account_id` = {$pm_id}
							AND `email` != ''
							AND `email` IS NOT NULL
						 ");
                                    if (mysql_num_rows($pm_sql) > 0) {

                                        // email not empty, lets validate it
                                        $pm = mysql_fetch_array($pm_sql);
                                        $pm_email2 = trim($pm['email']);
                                        $pm_email3 = preg_replace('/\s+/', '', $pm_email2);
                                        if (filter_var($pm_email3, FILTER_VALIDATE_EMAIL)) {
                                            $entrynotice_toemails[] = $pm_email3;
                                            $en_pm_emails_arr[] = $pm_email3;
                                        }
                                    }

                                    $params->comments = "Entry notice emailed to Tenants and Property Managers @ " . date("H:i");
                                }


                                // PM email only exist when en_to_pm = 1, if not send to agency which is the default action
                                if (count($en_pm_emails_arr) == 0) {

                                    if ($job_details['send_en_to_agency'] == 1) {

                                        // agency email
                                        $temp = explode("\n", trim($email_job_details['agency_emails']));
                                        foreach ($temp as $val) {
                                            $val2 = preg_replace('/\s+/', '', $val);
                                            if (filter_var($val2, FILTER_VALIDATE_EMAIL)) {
                                                $entrynotice_toemails[] = $val2;
                                                $en_agency_emails_arr[] = $val2;
                                            }
                                        }

                                        $params->comments = "Entry notice emailed to Tenants and Agency @ " . date("H:i");
                                    } else {

                                        $params->comments = "Entry notice emailed to Tenants @ " . date("H:i");
                                    }
                                }
                            }


                            //$entrynotice_toemails
                            if (sendEntryNoticeEmail($email_job_details, $entrynotice_toemails)) {
                                $params->eventtime = date('h:i:s');

                                // update entry_notice_emailed
                                mysql_query("
						UPDATE `jobs`
						SET
							`entry_notice_emailed` = '" . date("Y-m-d H:i:s") . "'
						WHERE `id` ={$job_id}
					");

                                $en_tent_emails_imp = implode(', ', $en_tent_emails_arr);
                                $en_agency_emails_imp = implode(', ', $en_agency_emails_arr);
                                $en_pm_emails_imp = implode(', ', $en_pm_emails_arr);

                                $append_en_agency_emails = ( count($en_agency_emails_arr) > 0 ) ? " and <strong>{$en_agency_emails_imp}</strong>" : "";
                                $append_en_pm_emails = ( count($en_pm_emails_arr) > 0 ) ? " and <strong>{$en_pm_emails_imp}</strong>" : "";

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
						'Entry Notice',
						'" . date('Y-m-d') . "',
						'Entry noticed emailed to: <strong>" . $en_tent_emails_imp . "</strong>" . $append_en_agency_emails . "" . $append_en_pm_emails . "',
						{$job_id},
						'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
						'" . date('H:i') . "'
					)
					");
                            }
                        }





                        // WATER METER
                        if ($_POST['wm_serv_manipulated'] == 1) {

                            $location = mysql_real_escape_string($_POST['location']);
                            $reading = mysql_real_escape_string($_POST['reading']);

                            /*
                              echo "Water Meter: {$_POST['wm_submit']}";
                              echo "<pre>";
                              print_r($_FILES);
                              echo "</pre>"; */

                            //echo "Location: {$location}<br />Reading: {$reading}";
                            // update
                            $db_ret = proccessWmUpload2($_FILES, $_POST, $_GET['id']);

                            if ($db_ret['meter_image'] != "") {
                                $db_str .= "`meter_image` = '" . $db_ret['meter_image'] . "',";
                            }

                            if ($db_ret['meter_reading_image'] != "") {
                                $db_str .= "`meter_reading_image` = '" . $db_ret['meter_reading_image'] . "',";
                            }

                            mysql_query("
						UPDATE `water_meter`
						SET
							`location` = '{$location}',
							{$db_str}
							`reading` = '{$reading}'
						WHERE `job_id` = {$_GET['id']}
					");
                        }




                        if ($_POST['ss_image_touched'] == 1) {

                            $sw_files = $_FILES['ss_image'];

                            /*
                              echo "<pre>";
                              print_r($sw_files);
                              echo "</pre>";
                             */


                            // dont upload if empty
                            if ($sw_files['name'] != '') {


                                // delete old image
                                $c_sql = mysql_query("
						SELECT `ss_image`
						FROM `jobs`
						WHERE `id` = {$job_id}
					");
                                $c = mysql_fetch_array($c_sql);

                                if ($c['ss_image'] != '') {
                                    $file_to_delete = 'ss_image/' . $c['ss_image'];
                                    if ($file_to_delete != "") {
                                        $crm->deleteFile($file_to_delete);
                                    }
                                }



                                // upload image
                                $uparams = array(
                                    'files' => $sw_files,
                                    'id' => $job_id,
                                    'upload_folder' => 'ss_image',
                                    'image_size' => 760
                                );
                                $upload_ret = $crm->masterDynamicUpload($uparams);


                                // store image path
                                mysql_query("
						UPDATE `jobs`
						SET `ss_image` = '{$upload_ret['image_name']}'
						WHERE `id` = {$job_id}
					");
                            }
                        }

                        function getColourTableStatus($tech_run_id, $colour_id) {

                            return mysql_query("
					SELECT *
					FROM `colour_table`
					WHERE `tech_run_id` = {$tech_run_id}
					AND `colour_id` = {$colour_id}
				");
                        }

                        function jGetSTRbookedJobs($tr_id, $color_id) {

                            $sql_str = "
                                SELECT count(trr.`tech_run_rows_id`) AS jcount
                                FROM `tech_run_rows` AS trr
                                LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
                                LEFT JOIN `tech_run_row_color` AS trrc ON trr.`highlight_color` = trrc.`tech_run_row_color_id`
                                LEFT JOIN `jobs` AS j ON j.`id` = trr.`row_id`
                                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` AND trr.`row_id_type` =  'job_id'
                                WHERE tr.`tech_run_id` = {$tr_id}
                                AND j.`status` = 'Booked'
                                AND trr.`highlight_color` = {$color_id}
                                AND trr.`hidden` = 0
                                AND j.`del_job` = 0
                                AND tr.`country_id` = {$_SESSION['country_default']}
                                AND a.`country_id` = {$_SESSION['country_default']}
                            ";
                            $sql = mysql_query($sql_str);
                            $row = mysql_fetch_array($sql);
                            return $row['jcount'];
                        }

                        function checkSmsforToday($job_id) {
                            $sql_str = "
                                SELECT *
                                FROM  `job_log`
                                WHERE  `contact_type` =  'SMS sent'
                                AND  `job_id` ={$job_id}
                                AND  `eventdate` =  '" . date('Y-m-d') . "'
                            ";
                            return mysql_query($sql_str);
                        }



                        function getStaffName($staff_id) {
                            $sql = mysql_query("
                                SELECT FirstName, LastName
                                FROM  `staff_accounts`
                                WHERE  `StaffID` =  {$staff_id}
                            ");
                            $row = mysql_fetch_array($sql);
                            return Sats_Crm_Class::formatStaffName($row['FirstName'], $row['LastName']);
                        }

                        # If job / property price not synced up yet use price from property and update it for next time
                        $price_used = $row[33];
                        //if(!$price_used) $row[32] = number_format(syncJobPrice($row[0], $row[34], $row[6]), 2);

                        $property_id = $row[20];
                        echo "\n";
                        // (4) Print out each element in $row, that is,
                        // print the values of the attributes

                        $jobsel = $row[6];
                        echo "
			<div " . (( $job_row['assigned_tech'] == 1 || $row['del_job'] == 1 ) ? 'style="background-color:#ECECEC"' : '') . ">
			<form action='" . URL . "view_job_details.php?id=" . $row[0] . "&doaction=update{$added_param}' method=post name='job_details_form' id='job_details_form' enctype='multipart/form-data'>\n";

                        echo "<input type='hidden' name='property_id' value='" . $property_id . "' />";


                        if ($row['agency_deleted']) {
                            echo "<div id='permission_error'>This Property is No Longer Managed by this Agency!</div>";
                        }


                        if ( $job_row['a_status'] == 'deactivated' ) {
                            echo "<div id='permission_error'>Agency is deactivated: You cannot create a new job while an Agency is deactivated.</div>";
                        }


                        if ($error != "") {
                            ?>
                            <div class="error"><?php echo $error; ?></div>
                            <?php
                        }


                        if ($_GET['email_temp_sent'] == 1) {
                            ?>
                            <div class="success">Email Template Sent</div>
                            <?php
                        }


                        if ($_GET['emailed_tenants'] == 1) {
                            ?>
                            <div class="success">Email Sent</div>
                            <?php
                        }

                        if ($_GET['price_changed'] == 1) {
                            ?>
                            <div class="success">Price Changed</div>
                            <?php
                        }

                        if ($_GET['sms_sent'] == 1) {
                            ?>
                            <div class="success">SMS sent</div>
                            <?php
                        }

                        if ($_GET['entry_notice'] == 'no') {
                            ?>
                            <div class="success">Job log created</div>
                            <?php
                        }

                        if ($_GET['send_entry_notice_success'] == 1) {
                            ?>
                            <div class="success">Entry Notice Sent</div>
                            <?php
                        }


                        if ($_GET['quote_email'] == 1) {
                            ?>
                            <div class="success">Quote Email Sent</div>
                            <?php
                        }

                        if ($_GET['confirm_booking_sms'] == 1) {
                            ?>
                            <div class="success">Confirm Booking SMS Sent</div>
                            <?php
                        }




                        if ($_GET['rebook_message'] == 1) {
                            ?>
                            <div class="success">240v Rebook Created</div>
                        <?php } else if ($_GET['rebook_message'] == 2) {
                            ?>
                            <div class="success">Rebook Created</div>
                            <?
                        }

                        if (isset($_GET['pme_upload_status'])) {
                            if ($_GET['pme_upload_status'] == 1) {
                                echo '<div class="success">'.$_GET['pme_msg'].'</div>';
                            }else {
                                echo '<div class="error">This job has already been uploaded an Invoice/Bill to PMe.</div>';
                            }
                        }

                        if (isset($_GET['palace_upload_status'])) {
                            if ($_GET['palace_upload_status'] == 1) {
                                echo '<div class="success">'.$_GET['palace_msg'].'</div>';
                            }else {
                                echo '<div class="error">This job has already been uploaded an Invoice/Bill to Palace.</div>';
                            }
                        }


                        // check if connected to API
                        $sel_query = "
                        agen_api_tok.`agency_api_token_id`,
                        agen_api_tok.`agency_id`,
                        agen_api_tok.`api_id`,

                        agen_api.`api_name`
                        ";
                        $api_token_params = array(
                        'sel_query' => $sel_query,
                        'active' => 1,
                        'agency_id' => $job_row['a_id'],
                        'display_echo' => 0
                        );
                        $api_sql = $crm->get_agency_api_tokens($api_token_params);

                        $connected_to_pme = false;
                        $connected_to_palace = false;
                        //$api_row = mysql_fetch_array($api_sql);
                        //print_r($api_row);

                        while ($api_row = mysql_fetch_array($api_sql)){

                            if ( $api_row['api_id'] == 1 ){ // PMe

                                $connected_to_pme = true;

                                $agency_api_params = array(
                                    'prop_id' =>  $row['api_prop_id'],
                                    'agency_id' => $job_row['a_id']
                                );

                                $pme_prop_json = $agency_api->get_property_pme($agency_api_params);
                                $pme_prop_json_dec = json_decode($pme_prop_json);

                                if( $pme_prop_json_dec->IsArchived == true ){
                                    echo "<div id='permission_error'>This Property is Deactivated in PropertyMe</div>";
                                }

                            }

                     
                            if ( $api_row['api_id'] == 3 ){ // PropertyTree

                                // get tenants contact ID
                                $agency_api_params = array(
                                    'property_id' => $job_row['property_id']
                                );

                                $curl_ret_arr = $agency_api->get_property_tree_property($agency_api_params);

                                $raw_response = $curl_ret_arr['raw_response'];
                                $json_decoded_response = $curl_ret_arr['json_decoded_response'];
                                $http_status_code = $curl_ret_arr['http_status_code'];

                                if( $http_status_code == 200 ){ // OK

                                    $api_prop_obj = $json_decoded_response[0];

                                    if( $api_prop_obj->archived == true || $api_prop_obj->deleted == true ){
                                        echo "<div id='permission_error'>This Property is Deactivated in PropertyTree</div>";
                                    }

                                }else{ // error
                                                    
                                    echo "<div class='error pt_api_error'>
                                    <h5>API Request error, please notify IT via a ticket</h5>
                                    <p>{$raw_response}</p>
                                    </div>";

                                }				

                            }
                        

                            if ( $api_row['api_id'] == 4 ){ // Palace

                                $connected_to_palace = true;

                                $agency_api_params = array(
                                    'prop_id' =>  $row['api_prop_id'],
                                    'agency_id' => $job_row['a_id']
                                );

                                $palace_prop_json = $agency_api->get_property_palace($agency_api_params);
                                $palace_prop_dec = json_decode($palace_prop_json);
                                //print_r($palace_prop_dec);


                                if( $palace_prop_dec->PropertyArchived == true ){
                                    echo "<div id='permission_error'>This Property is Deactivated in Palace</div>";
                                }


                            }

                            if ( $api_row['api_id'] == 6 ){ // Ourtradide

                                $connected_to_ourtradie = true;

                                $agency_api_params = array(
                                    'prop_id' =>  $row['api_prop_id'],
                                    'agency_id' => $job_row['a_id']
                                );

                                /*$ot_prop_json = $agency_api->get_property_ourtradie($agency_api_params);
                                $ot_prop_json_dec = json_decode($ot_prop_json);

                                if( $ot_prop_json_dec->IsArchived == true ){
                                    echo "<div id='permission_error'>This Property is Deactivated in PropertyMe</div>";
                                }
                                */


                            }

                        }

                        if( $_GET['invoice_uploaded'] == 1 || $_GET['certificate_uploaded'] ) {

                            $uploade_msg_arr = [];
                            if( $_GET['invoice_uploaded'] == 1 ){
                                $uploade_msg_arr[] = 'Invoice';
                            }

                            if( $_GET['certificate_uploaded'] == 1 ){
                                $uploade_msg_arr[] = 'Certificate';
                            }
                            
                            $uploade_msg_imp = implode(" and ",$uploade_msg_arr);

                            echo "<div class='success'>{$uploade_msg_imp} Console API Upload Successful!</div>";

                        }                        
                        ?>
                        <style>
                            .junderline_colored{
                                color: red;
                                text-decoration: underline;
                            }

                        </style>

                        <?php
                        //$age = $crm->getAge($row['jdate']);
                        //echo "property_id: {$property_id}<br />age: {$age}";


                        $staff_name = "{$_SESSION['USER_DETAILS']['FirstName']}";
                        $agency_id = $row[24];
                        $agency_address = "{$row['agent_address_1']} {$row['agent_address_2']} {$row['agent_address_3']} {$row['agent_state']} {$row['agent_postcode']}";

                        //echo "Agent Name: {$row['contact_first_name']} {$row['contact_last_name']}";

                        $arr_agency = array(1902, 1906, 1927);
                        if (in_array($agency_id, $arr_agency)) {
                            $the_rental = 'your';
                            $this_rental = 'your';
                        } else {
                            $the_rental = 'the rental';
                            $this_rental = 'this rental';
                        }
                        $agency_name = $row[25];
                        $address = "{$row[9]} {$row[10]} {$row[11]}";
                        $jsql = mysql_query("
				SELECT *, ajt.`id` AS ajt_id
				FROM `jobs` AS j
				LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
				WHERE j.`id` = {$job_id}
			");
                        $j = mysql_fetch_array($jsql);
                        $service_name = ($j['ajt_id'] == 9) ? 'SA.CW.SS' : $j['type'];
                        $alarm_job_type_id = $j['ajt_id']; //new added by Gherx
                        //echo "Service Type: {$service_name}";

                        if (( $row['jdate'] == "" || $row['jdate'] == "0000-00-00" ) && $_GET['tr_date'] != "") {
                            $day = date("l", strtotime(mysql_real_escape_string($_GET['tr_date'])));
                        } else {
                            $day = ( $j['date'] != '' && $j['date'] != "0000-00-00" && $j['date'] != "1970-01-01" ) ? date("l", strtotime($j['date'])) : 'NO DATE SELECTED';
                        }

                        $time = $j['time_of_day'];

                        // BOOKING SCRIPT
                        // private FG
                        if ($crm->getAgencyPrivateFranchiseGroups($row['franchise_groups_id']) == true) {
                            $agency_name_txt = "your agency";
                            $landlord_txt = 'your landlord';
                        } else {
                            $agency_name_txt = $agency_name;
                            $landlord_txt = 'your agency';
                        }

                        if ($j['status'] == "To Be Booked") {

                            // SA CW SS
                            if ($j['ajt_id'] == 9) {
                                $serv_text = 'Smoke Alarms/Window Blinds/Safety Switch';
                            } else if ($j['ajt_id'] == 8) { // SA SS
                                $serv_text = 'Smoke Alarms/Safety Switch';
                            } else {
                                $serv_text = 'Smoke Alarms';
                            }

                            switch ($jobsel) {
                                case 'Yearly Maintenance':
                                    $script_text = "
							<p>Hi this is {$staff_name} from Smoke Alarm Testing Services calling on behalf of <span class='junderline_colored'>{$agency_name_txt}</span> in regards to the rental property at <span class='junderline_colored'>{$row[10]}</span>.</p>
							<p>We have been instructed to service the {$serv_text} at your property. I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
							<p>Would anybody be available to allow access?</p>
						";
                                    break;
                                case '240v Rebook':
                                    $script_text = "
							<p>Hi this is {$staff_name} from Smoke Alarm Testing Services. <span class='junderline_colored'>{$agency_name_txt}</span> have instructed us to attend to your property at <span class='junderline_colored'>{$row[10]}</span> to replace the smoke alarms as they are <span class='junderline_colored'>due to expire</span>.</p>
							<p>I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
							<p>Would anybody be available to allow access?</p>
						";
                                    break;
                                case 'Fix or Replace':
                                    $script_text = "
                                    <p>Hello, this is {$staff_name} from Smoke Alarm Testing Services. We've been informed by <span class='junderline_colored'>{$agency_name_txt}</span> that there are concerns regarding your Smoke Alarms at <span class='junderline_colored'>{$row[10]}</span>, and they've requested our urgent attendance.</p>
                                    <p> I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME and TIME</span>. Is there anyone available to grant access during this time frame? </p>
                                    ";
                                    break;
                                case 'Change of Tenancy':
                                    $script_text = "
							<p>Hi this is {$staff_name} from Smoke Alarm Testing Services. <span class='junderline_colored'>{$agency_name_txt}</span> have instructed us that you are a new tenant at <span class='junderline_colored'>{$row[10]}</span> and we are to service the {$serv_text} at this property.</p>
							<p>I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
							<p>Would anybody be available to allow access?</p>
						";
                                    break;
                                case 'Lease Renewal':
                                    $script_text = "
							<p>Hi this is {$staff_name} from Smoke Alarm Testing Services. <span class='junderline_colored'>{$agency_name_txt}</span> have instructed us that you have signed a new lease and we are to attend to your property at <span class='junderline_colored'>{$row[10]}</span> to service the {$serv_text}.</p>
							<p>I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
							<p>Would anybody be available to allow access?</p>
						";
                                    break;
                                case 'Once-off':
                                    $script_text = "
							<p>Hi this is {$staff_name} from Smoke Alarm Testing Services calling on behalf of <span class='junderline_colored'>{$agency_name_txt}</span> in regards to the rental property at <span class='junderline_colored'>{$row[10]}</span>.</p>
							<p>We have been instructed to service the {$serv_text} at your property. I have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
							<p>Would anybody be available to allow access?</p>
						";
                                    break;
                            }




                            echo "<div>
						<div id='booking_script_div' class='success' style='text-align: left; color: black;'>
						<div id='bs_div'>
						{$script_text}
						</div>
						<div id='inbound_call_div' style='display:none; margin-bottom: 10px;'>
							<p>We called on behalf of <span class='junderline_colored'>{$agency_name_txt}</span></p>
							<p>We have been instructed to service the Smoke Alarms at your property. I can see we have a technician available this <span class='junderline_colored'>{$day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span></p>
							<p>Would anybody be available to allow access?</p>
						</div>
						<div id='dif_bok_div' class='error' style='display:none; margin-bottom: 10px;'>
							<p>I need to advise you that because we have attempted to book this job in multiple times and have not been able to gain access that I am obliged to notify {$agency_name_txt}</p>
						</div>
						<div id='voicemail_div' class='error' style='display:none; margin-bottom: 10px;'>
							<p>Hi this is {$staff_name} from SATS calling on behalf of {$agency_name_txt}. Please return my call on {$ctn['tenant_number']}</p>
						</div>
						<div id='not_available_div' style='display:none; margin-bottom: 10px;'>
							<p>What is the best time or day of the week that you are available so I can make a note for next time we are in the area?</p>
							<p>Thanks <span class='junderline_colored'>NAME</span> Just to confirm. I have here that your best time is <span class='junderline_colored'>TIME</span>. So we will call you again to make an appointment when we have that time available.</p>
							<p>Thanks and Have a great day</p>
						</div>
						<div id='key_access_div' style='display:none; margin-bottom: 10px;'>
							<p>We do have the option of getting the keys from {$agency_name_txt}, and we can leave a card once we have completed the service to let you know we have attended.</p>
							<p>How does that sound?</p>
						</div>


						<div id='cat_div' style='display:none; margin-bottom: 10px;'>
							<p> Hi this is {$staff_name} from SATS. We've been calling you to see if you are available on <span class='junderline_colored'>TIME AND DAY</span>. We are in the area and it would be great if we can service your smoke alarms too. </p>
						</div>
						<div id='pt_div' style='display:none; margin-bottom: 10px;'>
							<p>Hi this is {$staff_name} from SATS. I know you requested for a <span class='junderline_colored'>PREFERRED DAY/TIME/EXACT DATE</span> to be scheduled. Since we have a technician in the area between <span class='junderline_colored'>TIME AND DAY</span> and it would be great to service your smoke alarms if anyone in your household is available for us. </p>
						</div>
					<button type='button' class='submitbtnImg' id='btn_booking_script'>Hide Script</button>
					<button type='button' class='blue-btn submitbtnImg' id='btn_inbound_call'>Inbound Call</button>
					<button type='button' class='submitbtnImg' id='btn_dif_bok'>Difficult Booking</button>
					<button type='button' class='blue-btn submitbtnImg' id='btn_voicemail'>Voicemail</button>
					<button type='button' class='submitbtnImg' id='btn_not_available'>Not Available</button>
					<button style='margin-left: 5px;' type='button' class='blue-btn submitbtnImg' id='btn_key_access'>Key Access</button>
					<button type='button' class='submitbtnImg' id='btn_cat'>Called Already Today</button>
					<button type='button' class='blue-btn submitbtnImg' id='btn_pt'>Preferred Time</button>
					</div>
					";




                            echo "</div>";
                        }

                        if ($row[2] == "Booked") {
                            //echo "<div class='success' style='font-size: 20px; color: black; text-align:left;'>Thanks <span class='junderline_colored'>{$row['booked_with']}</span>. Just to confirm, we have you booked in for <span class='junderline_colored'>".date('l',strtotime($row['jdate']))." ".date('d/m/Y',strtotime($row['jdate']))."</span> at <span class='junderline_colored'>".$row[14]."</span>. ".(($jstate=='SA')?'<br />Do you require me to email you an entry notice to confirm this appointment?<br /><button type="button" class="submitbtnImg" id="btn_email_yes" style="background-color: green;">YES</button><button type="button" style="margin-left: 8px;" class="submitbtnImg" id="btn_email_no">NO</button><br /><input type="hidden" id="booked_with_name" name="booked_with_name" /><input type="hidden" id="booked_with_email" name="booked_with_email" />':'')."Have a great day</div>";

                            echo "<div class='success' style='font-size: 20px; color: black; text-align:left;'>

						<div id='default_script_text_div' style='margin-bottom: 10px;'>
							Thanks <span class='junderline_colored'>{$row['booked_with']}</span>. ";
                            if ($row['key_access_required'] == 1) {
                                if ($row['key_email_req'] == 1) {
                                    echo "Just to confirm, we have you booked in for <span class='junderline_colored'>" . date('l', strtotime($row['jdate'])) . " " . date('d/m/Y', strtotime($row['jdate'])) . "</span> and we will collect the keys from {$agency_name_txt} and our technician will leave a card to let you know the job has been done. {$agency_name_txt} requires you to confirm this booking so I am going to email you a template that you will need to reply to. Is that ok? Great, I am sending that to you now. ";
                                } else {
                                    echo "Just to confirm, we have you booked in for <span class='junderline_colored'>" . date('l', strtotime($row['jdate'])) . " " . date('d/m/Y', strtotime($row['jdate'])) . "</span> and we will collect the keys from {$agency_name_txt} and our technician will leave a card to let you know the job has been done. ";
                                }
                            } else {
                                echo "Just to confirm, we have you booked in for <span class='junderline_colored'>" . date('l', strtotime($row['jdate'])) . " " . date('d/m/Y', strtotime($row['jdate'])) . "</span> at <span class='junderline_colored'>" . $row[14] . "</span>. We will send you an SMS the day before to remind you of the appointment. ";
                            }
                            echo "Thanks and have a great day
						</div>

						<div id='cancel_vm_div' style='display:none; margin-bottom: 10px;'>
							Hi This is {$staff_name} from smoke alarm testing services, unfortunately we are unable to complete your service today as our technician <span class='junderline_colored'>REASON</span>. I'm sorry but we will have to call you again to make a new appointment. Have a nice day
						</div>

						<div id='cancel_caller_div' style='display:none; margin-bottom: 10px;'>
							Hi This is {$staff_name} from smoke alarm testing services, unfortunately we are unable to complete your service today as our technician <span class='junderline_colored'>REASON</span>. I'm sorry but we will have to call you again to make a new appointment.
						</div>

						<div id='script_rebook_div' style='display:none; margin-bottom: 10px;'>
							Hi This is {$staff_name} from smoke alarm testing services, unfortunately we are unable to complete your service today as our technician <span class='junderline_colored'>REASON</span>. Are you available <span class='junderline_colored'>{$day}</span> <span class='junderline_colored'>TIME</span> for us
						</div>

						<button type='button' class='submitbtnImg' id='btn_cancel_vm'>Cancel Voicemail</button>
						<button type='button' class='submitbtnImg blue-btn' id='btn_cancel_caller'>Cancel with Caller</button>
						<button type='button' class='submitbtnImg' id='btn_script_rebook'>Rebook</button>

				</div>";
                        }

                        if ($row['del_job'] == 1) {
                            ?>
                            <div id="permission_error">This job is deleted</div>
                            <?php
                        }

                        // other supplier
                        if ($job_row['assigned_tech'] == 1) {
                            ?>
                            <div id="permission_error">Job Performed by Previous Supplier not SATS</div>
                            <?php
                        }

                        // show only on interconnected alarms services
                        $ic_serv = getICService();
                        if (in_array($j['ajt_id'], $ic_serv)) {
                            ?>
                            <div style="color: red;font-weight: bold;">*** ALL ALARMS ARE TO BE INTERCONNECTED ***</div>
                            <?php
                        }

                        echo "<table border=0 cellspacing=0 cellpadding=5 width=100% id='job_details' class='table-vw-job tbl-fr-red fnt-small' style='border-right: 1px solid #ccc;'>";

                        if (isStrMappedFull($job_id)) {
                            ?>

                            <h2 class="heading" style="color:red;">This run is FULL please let operations know of this addition</h1>

                                <?php
                            }
                            ?>

                            <tr class='tr_rightBorder'>
                                <td colspan="100%" style="padding:0px;">


                                    <?php
                                    // get recent "phone call" job log
                                    $chk_logs_sql_str = "
                                        SELECT DATE_FORMAT(j.eventdate,'%d/%m/%Y') AS jl_date,
                                            j.contact_type,
                                            j.comments,
                                            j.log_id,
                                            s.FirstName,
                                            s.LastName,
                                            eventtime,
                                            j.`important`,
                                            j.`eventdate`
                                        FROM job_log j
                                        LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
                                        WHERE j.`job_id` = {$job_id}
                                        AND j.`deleted` = 0                                        
                                        AND j.`contact_type` = 'Phone Call'
                                        ORDER BY j.`eventdate` DESC
                                    ";
                                    
                                    $chk_logs_sql = mysql_query($chk_logs_sql_str);
                                    $chk_log = mysql_fetch_array($chk_logs_sql);

                                    // huming agency only allows 2 days interval phone call
                                    if( $_SESSION['country_default'] == 1 ){
                                        $hume_house_agency_id = 1598; // Hume Housing                                                                                   
                                        $day_interval = date("Y-m-d", strtotime("{$chk_log['eventdate']} +3 days"));
                                    }                                                                    

                                    if (
                                        (
                                            mysql_num_rows($chk_logs_sql) > 0 &&
                                            $job_row['jstatus'] == "To Be Booked" &&
                                            (
                                                ( $job_row['agency_id'] == $hume_house_agency_id && date('Y-m-d') < $day_interval )  ||
                                                ( $chk_log['eventdate'] == date('Y-m-d') ) 
                                            )
                                        ) || $job_row['allow_en'] == 2                                     
                                    ) {
                                        $hide_tenant_details = 1;
                                        echo "<h4>";
                                        if( $job_row['allow_en'] == 2 ){
                                            echo "Agency requested tenant not be called, but only entry noticed. Do not call these tenant. <br>If a tenant calls asking why we booked this, please advise them the agency has requested an entry notice be sent and keys collected. <br>If they have any issues please contact their agency.<br>";
                                        }else{ // default
                                            echo "{$chk_log['FirstName']} {$chk_log['LastName']} called on ".( date("d/m/Y", strtotime($chk_log['eventdate'])) )." @ {$chk_log['eventtime']}";
                                        }                                        
                                        echo "<button id='btn_show_tenant' class='blue-btn submitbtnImg' type='button'>show</button></h4>";
                                    }
                                    ?>

                                    <?php if ($row['holiday_rental'] == 1) { ?>
                                        <div style="color:red;">Short Term Rental</div>
                                        <?php
                                    }
                                    ?>


                                    <?php
                                    // find a property that job completed in the last 30 days
                                        $com_job_sql = mysql_query("
                                        SELECT j.`id`
                                        FROM `jobs` AS j
                                        WHERE j.`property_id` = {$property_id}
                                        AND j.`status` = 'Completed'
                                        AND j.`del_job` = 0
                                        AND j.`date` >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                                        AND j.`id` != '{$job_id}'
                                    ");

                                    if (mysql_num_rows($com_job_sql) > 0 && $job_type != 'Fix or Replace') {
                                        ?>
                                        <h1 style="text-align: left; color: #b4151b;">Job completed in the last 30 days</h1>
                                        <?php
                                    }
                                    ?>

                                    <div class="vw-jb-tnt tenant_details_div" style="<?php echo ($hide_tenant_details == 1) ? 'display:none;' : ''; ?>float: left; width:900px;">
                                        <div style="overflow: hidden;">
                                            <?php
                                            //echo "agency id: {$row[24]}";
                                            $dha_agencies = array(
                                                3043,
                                                3036,
                                                3046,
                                                1902,
                                                3044,
                                                1906,
                                                1927,
                                                3045
                                            );
                                            $td_txt = (in_array($row[24], $dha_agencies)) ? 'Member Details' : 'Tenant Details';



                                            // SMS template
                                            $serv_name = getServiceFullName($s['service']);
                                            $paddress = "{$row['p_street_num']} {$row['p_street_name']} {$row['p_suburb']}";
                                            $jdatetemp = ( $row['jdate'] != "" && $row['jdate'] != "0000-00-00" ) ? date('d/m/Y', strtotime($row['jdate'])) : '';
                                            $date_day_txt = ( $row['jdate'] != "" && $row['jdate'] != "0000-00-00" ) ? strtoupper(date('l', strtotime($row['jdate']))) : '';
                                            $date_day_txt2 = ( $row['jdate'] != "" && $row['jdate'] != "0000-00-00" ) ? strtoupper(date('l', strtotime($row['jdate']))) : '';
                                            $date_day_num = ( $row['jdate'] != "" && $row['jdate'] != "0000-00-00" ) ? date('jS', strtotime($row['jdate'])) : '';

                                            $sms_temp_arr = [];

                                            // No Answer
                                            if ($agency_id == 1200) {
                                                $no_answer_sms_temp = "Please call SATS {$ctn['tenant_number']} to make an appointment to service your smoke alarms";
                                            } else if ($jobsel == "240v Rebook") {
                                                $no_answer_sms_temp = "{$serv_name} need to be replaced on behalf of your landlord and {$agency_name_txt}. Please call SATS {$ctn['tenant_number']}";
                                            } else if ($crm->getAgencyPrivateFranchiseGroups($row['franchise_groups_id']) == true) {
                                                $no_answer_sms_temp = "SATS need to test {$serv_name} on behalf of your landlord. Please call {$ctn['tenant_number']}";
                                            } else {
                                                $no_answer_sms_temp = "SATS need to test {$serv_name} on behalf of your landlord & {$agency_name_txt}. Please call {$ctn['tenant_number']}";
                                            }
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No Answer',
                                                'sms_temp_boxtext' => $no_answer_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($no_answer_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($no_answer_sms_temp) / 160),
                                                'sms_type' => 1
                                            );

                                            // No Answer. Insert date/time
                                            $naid_sms_temp = "SATS are trying to contact you to book in {date} @ {time} to service the {$serv_name} at {$paddress} on behalf of {$landlord_txt}. Please reply YES to confirm";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No Answer',
                                                'sms_temp_desc' => 'Yes/No SMS Reply',
                                                'sms_temp_ins' => '*Insert Date/Time',
                                                'sms_temp_boxtext' => $naid_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($naid_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($naid_sms_temp) / 160),
                                                'sms_type' => 2
                                            );


                                            // No Answer (KEYS)
                                            $no_answer_keys = "SATS are trying to contact you to book in {date} @ {time} to service the {$serv_name} at {$paddress} on behalf of {$landlord_txt}. Please reply YES to confirm this appointment time or reply KEYS and we will collect keys from {$landlord_txt}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No Answer',
                                                'sms_temp_desc' => 'Keys SMS Reply',
                                                'sms_temp_ins' => '*Insert Date/Time',
                                                'sms_temp_boxtext' => $no_answer_keys,
                                                'sms_temp_boxtext_cout' => strlen($no_answer_keys),
                                                'sms_temp_num_count' => ceil(strlen($no_answer_keys) / 160),
                                                'sms_type' => 3
                                            );



                                            // No Answer. Insert date/time NZ
                                            $naid_sms_temp_nz = "SATS are trying to contact you to book in {date} @ {time} to service the {$serv_name} at {$paddress} on behalf of {$landlord_txt}. Please reply YES to confirm someone will be home for this appointment {$ctn['tenant_number']}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No Answer NZ',
                                                'sms_temp_desc' => 'Yes/No SMS Reply NZ',
                                                'sms_temp_ins' => '*Insert Date/Time',
                                                'sms_temp_boxtext' => $naid_sms_temp_nz,
                                                'sms_temp_boxtext_cout' => strlen($naid_sms_temp_nz),
                                                'sms_temp_num_count' => ceil(strlen($naid_sms_temp_nz) / 160),
                                                'sms_type' => 28
                                            );



                                            // No Answer (KEYS) NZ
                                            $no_answer_keys = "SATS are trying to contact you to book in {date} @ {time} to service the {$serv_name} at {$paddress} on behalf of {$landlord_txt}. Please reply YES to confirm someone will be home for this appointment or reply KEYS and we will collect keys from {$landlord_txt} {$ctn['tenant_number']}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No Answer NZ',
                                                'sms_temp_desc' => "Keys SMS Reply NZ",
                                                'sms_temp_ins' => '*Insert Date/Time',
                                                'sms_temp_boxtext' => $no_answer_keys,
                                                'sms_temp_boxtext_cout' => strlen($no_answer_keys),
                                                'sms_temp_num_count' => ceil(strlen($no_answer_keys) / 160),
                                                'sms_type' => 27
                                            );


                                            // No-Show
                                            $sms_type = 4;
                                            $sms_temp_params = array(
                                                'sms_type' => $sms_type,
                                                'tenant_number' => $ctn['tenant_number'],
                                                'landlord_txt' => $landlord_txt
                                            );
                                            $no_show_sms_temp = $crm->getSMStemplate($sms_temp_params);
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'No-Show',
                                                'sms_temp_desc' => 'Agency Notified',
                                                'sms_temp_boxtext' => $no_show_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($no_show_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($no_show_sms_temp) / 160),
                                                'sms_type' => $sms_type
                                            );

                                            // Cancel
                                            $cancel_sms_temp = "SATS would like to apologise as we will have to cancel our appointment for today, as our technician has been called away unexpectedly. We apologise for any inconvenience caused and we will contact you shortly to rebook a suitable time";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Cancel',
                                                'sms_temp_desc' => 'Tech Called Away',
                                                'sms_temp_boxtext' => $cancel_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($cancel_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($cancel_sms_temp) / 160),
                                                'sms_type' => 5
                                            );

                                            // Cancel - Sick Tech EN
                                            $sick_en_sms_temp = "SATS are unable to attend your property today via Entry Notice, due to the Technician being unwell, we will re-issue the Entry Notice at a later date. Sorry for any inconvenience caused";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Cancel',
                                                'sms_temp_desc' => 'Sick Tech EN',
                                                'sms_temp_boxtext' => $sick_en_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($sick_en_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($sick_en_sms_temp) / 160),
                                                'sms_type' => 6
                                            );

                                            // Cancel- Sick Tech
                                            $stc_sms_temp = "SATS are unable to attend your property for our appointment today due to the Technician being unwell. We will call you again to schedule a new appointment. Sorry for any inconvenience caused";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Cancel',
                                                'sms_temp_desc' => 'Sick Tech',
                                                'sms_temp_boxtext' => $stc_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($stc_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($stc_sms_temp) / 160),
                                                'sms_type' => 7
                                            );

                                            // Cancel - Service no longer required
                                            $snlr_sms_temp = "SATS would like to apologise as we will have to cancel our scheduled booking as {$agency_name_txt} has advised this service is no longer required. We apologise for any inconvenience caused.";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Cancel',
                                                'sms_temp_desc' => 'Service no longer required',
                                                'sms_temp_boxtext' => $snlr_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($snlr_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($snlr_sms_temp) / 160),
                                                'sms_type' => 21
                                            );

                                            // Cancel (Tenant Request)
                                            $cancel_tnt_req = "Good {Morning}. This is a courtesy sms to advise your appointment has been cancelled as per your request for today {date}. Thank you and have a lovely day. SATS {$ctn['tenant_number']}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Cancel",
                                                'sms_temp_desc' => 'Tenant Request',
                                                'sms_temp_boxtext' => $cancel_tnt_req,
                                                'sms_temp_boxtext_cout' => strlen($cancel_tnt_req),
                                                'sms_temp_num_count' => ceil(strlen($cancel_tnt_req) / 160),
                                                'sms_type' => 29
                                            );

                                            // Escalation
                                            $esc_sms_stemp = "SATS MUST carry out mandatory testing of the {$serv_name} at {$paddress}. Many attempts have been made to contact you, this has been escalated with {$landlord_txt}. Please call SATS urgently on {$ctn['tenant_number']} to arrange a time or key access";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Escalation',
                                                'sms_temp_desc' => 'Agency Notified',
                                                'sms_temp_boxtext' => $esc_sms_stemp,
                                                'sms_temp_boxtext_cout' => strlen($esc_sms_stemp),
                                                'sms_temp_num_count' => ceil(strlen($esc_sms_stemp) / 160),
                                                'sms_type' => 8
                                            );

                                            // Email Entry Notice
                                            //$email_en_sms_temp = "SATS have issued an Entry Notice via email to test the {$serv_name} at {$paddress} on {$jdatetemp}. Email may appear in Spam/Junk folders. View this Entry Notice by clicking this link <link>";
                                            $sms_type = 9;
                                            $sms_temp_params = array(
                                                'sms_type' => $sms_type,
                                                'serv_name' => $serv_name,
                                                'paddress' => $paddress,
                                                'jdatetemp' => $jdatetemp
                                            );
                                            $email_en_sms_temp = $crm->getSMStemplate($sms_temp_params);
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Email Notice',
                                                'sms_temp_desc' => 'Email EN',
                                                'sms_temp_boxtext' => $email_en_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($email_en_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($email_en_sms_temp) / 160),
                                                'sms_type' => $sms_type
                                            );

                                            // SMS Entry Notice
                                            //$sms_en_sms_temp = "SATS have issued you an Entry Notice to test the {$serv_name} at {$paddress} on {$jdatetemp} and will collect the keys from {$landlord_txt}. Click here to view <link>";
                                            $sms_type = 10;
                                            $sms_temp_params = array(
                                                'sms_type' => $sms_type,
                                                'serv_name' => $serv_name,
                                                'paddress' => $paddress,
                                                'jdatetemp' => $jdatetemp
                                            );
                                            $sms_en_sms_temp = $crm->getSMStemplate($sms_temp_params);
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Entry Notice',
                                                'sms_temp_desc' => 'SMS EN',
                                                'sms_temp_boxtext' => $sms_en_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($sms_en_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($sms_en_sms_temp) / 160),
                                                'sms_type' => $sms_type
                                            );

                                            // Tech Running Late
                                            $trl_sms_temp = "Your SATS technician has been held up and is running late. They will be there ASAP. Sorry for any inconvenience caused";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Tech Running Late',
                                                'sms_temp_desc' => 'Coming ASAP',
                                                'sms_temp_boxtext' => $trl_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($trl_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($trl_sms_temp) / 160),
                                                'sms_type' => 11
                                            );

                                            // No Keys at Agency
                                            $no_keys_sms_temp = "This is a courtesy to advise SATS were unable to complete your smoke alarm service today because {$landlord_txt} did not have keys available. We will contact you shortly to reschedule";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => 'Unable to Complete',
                                                'sms_temp_desc' => 'No Keys',
                                                'sms_temp_boxtext' => $no_keys_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($no_keys_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($no_keys_sms_temp) / 160),
                                                'sms_type' => 12
                                            );

                                            // Agency keys don't work
                                            $akdw_sms_temp = "SATS were unable to complete your smoke alarm service today because the keys provided by {$landlord_txt} didn't work. We will contact you shortly to reschedule";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Unable to Complete",
                                                'sms_temp_desc' => "Keys Don't Work",
                                                'sms_temp_boxtext' => $akdw_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($akdw_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($akdw_sms_temp) / 160),
                                                'sms_type' => 13
                                            );

                                            // Unable to Access
                                            $unable_access_temp = "Our technician attended your property today to check your smoke alarms as per our appointment however we were unable to gain access. Please call {$ctn['tenant_number']} to reschedule. We have notified {$landlord_txt} of this issue";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Unable to Complete",
                                                'sms_temp_desc' => 'Unable to Access',
                                                'sms_temp_boxtext' => $unable_access_temp,
                                                'sms_temp_boxtext_cout' => strlen($unable_access_temp),
                                                'sms_temp_num_count' => ceil(strlen($unable_access_temp) / 160),
                                                'sms_type' => 14
                                            );


                                            // Dog
                                            $utc_dog = "SATS were unable to complete your {$serv_name} service today due to a unrestrained dog on the premises. We will contact you shortly to reschedule";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Unable to Complete",
                                                'sms_temp_desc' => 'Dog',
                                                'sms_temp_boxtext' => $utc_dog,
                                                'sms_temp_boxtext_cout' => strlen($utc_dog),
                                                'sms_temp_num_count' => ceil(strlen($utc_dog) / 160),
                                                'sms_type' => 23
                                            );


                                            // No FOB or Security Tag
                                            $no_fob = "This is a courtesy to advise you that SATS were unable to complete your {$serv_name} service today because your agency was not able to supply the FOB or security key to gain entry to your property. We will contact you shortly to reschedule";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Unable to Complete",
                                                'sms_temp_desc' => 'No FOB or Security Tag',
                                                'sms_temp_boxtext' => $no_fob,
                                                'sms_temp_boxtext_cout' => strlen($no_fob),
                                                'sms_temp_num_count' => ceil(strlen($no_fob) / 160),
                                                'sms_type' => 25
                                            );


                                            // Time-slot Full
                                            $ts_full_sms_temp = "Thank you for your reply. Unfortunately this time-slot is currently full. We apologise for any inconvenience caused. We will contact you shortly to reschedule";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "SMS Reply",
                                                'sms_temp_desc' => 'Time-Slot FULL',
                                                'sms_temp_boxtext' => $ts_full_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($ts_full_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($ts_full_sms_temp) / 160),
                                                'sms_type' => 15
                                            );

                                            // Confirm Booking
                                            $conf_boooking_sms_temp = "This is to confirm your appointment made today for the {$jdatetemp} @ {$row['time_of_day']} to service the {$serv_name} at {$paddress}. Please ensure someone is home to allow access. SATS {$ctn['tenant_number']}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "SMS Reply",
                                                'sms_temp_desc' => 'Booking Confirmed',
                                                'sms_temp_boxtext' => $conf_boooking_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($conf_boooking_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($conf_boooking_sms_temp) / 160),
                                                'sms_type' => 16
                                            );

                                            // Confirm Booking Keys
                                            $book_conf_keys_sms_temp = "This is to confirm your appointment made today with SATS. We will be collecting KEYS from {$agency_name_txt} on {$jdatetemp} to service the Smoke Alarms at {$paddress}. SATS {$ctn['tenant_number']}";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "SMS Reply",
                                                'sms_temp_desc' => 'Booking Confirmed KEYS',
                                                'sms_temp_boxtext' => $book_conf_keys_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($book_conf_keys_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($book_conf_keys_sms_temp) / 160),
                                                'sms_type' => 22
                                            );

                                            // No Longer Tenant
                                            if ($crm->getAgencyPrivateFranchiseGroups($row['franchise_groups_id']) == true) {
                                                $landlord_txt_temp = 'your landlord';
                                            } else {
                                                $landlord_txt_temp = 'the agency';
                                            }
                                            $sms_rep_nlt = "Thank you for advising us. We will contact {$landlord_txt_temp} to collect the new tenant details. ";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "SMS Reply",
                                                'sms_temp_desc' => 'No Longer Tenant',
                                                'sms_temp_boxtext' => $sms_rep_nlt,
                                                'sms_temp_boxtext_cout' => strlen($sms_rep_nlt),
                                                'sms_temp_num_count' => ceil(strlen($sms_rep_nlt) / 160),
                                                'sms_type' => 20
                                            );


                                            // Preferred
                                            $preffered_txt = "SATS need to complete testing of the {$serv_name} on behalf of {$agency_name_txt}. Appointments are available between  7am-3pm weekdays with a minimum 1 hour time-frame required. Please reply to this sms and advise which day/s and time you are available and we will do our best to meet these. Thank you SATS";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Preferred",
                                                'sms_temp_desc' => 'Time/Date',
                                                'sms_temp_boxtext' => $preffered_txt,
                                                'sms_temp_boxtext_cout' => strlen($preffered_txt),
                                                'sms_temp_num_count' => ceil(strlen($preffered_txt) / 160),
                                                'sms_type' => 26
                                            );


                                            // Preferred and Offered
                                            $pref_n_off_txt = "SATS are required to service your {$serv_name} at {$paddress} on behalf of {$agency_name_txt}. We have a appointment available on date between time-time. If this is not suitable please provide day/s and times Monday to Friday between 7pm and 3pm with minimum 1 hour window and we will do our best to meet these.";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "Preferred and Offered",
                                                'sms_temp_desc' => 'Time/Date',
                                                'sms_temp_boxtext' => $pref_n_off_txt,
                                                'sms_temp_boxtext_cout' => strlen($pref_n_off_txt),
                                                'sms_temp_num_count' => ceil(strlen($pref_n_off_txt) / 160),
                                                'sms_type' => 30
                                            );



                                            // Custom SMS
                                            $custom_sms_temp = "";
                                            $sms_temp_arr[] = array(
                                                'sms_temp_name' => "SMS",
                                                'sms_temp_desc' => 'Insert own Text',
                                                'sms_temp_boxtext' => $custom_sms_temp,
                                                'sms_temp_boxtext_cout' => strlen($custom_sms_temp),
                                                'sms_temp_num_count' => ceil(strlen($custom_sms_temp) / 160),
                                                'sms_type' => 17
                                            );

                                            // get BNE to call marker
                                            $bne_sql = mysql_query("
							SELECT p.`bne_to_call`
							FROM `jobs` AS j
							LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
							WHERE j.`id` = {$job_id}
						");
                                            $bne_row = mysql_fetch_array($bne_sql);
                                            ?>
                                            <h2 class="heading chops" style="float: left; margin-left: 7px;">
                                                <?php echo $td_txt; ?>

                                                <?php
                                                if ($bne_row['bne_to_call'] == 1) {
                                                    if ($_SESSION['country_default'] == 1) { // AU
                                                        echo "<span style='margin-left: 80px;'>* This job must be booked by Brisbane Call Center *</span>";
                                                    } else if ($_SESSION['country_default'] == 2) { // NZ
                                                        echo "<span style='margin-left: 80px;'>* This job must be booked by Auckland Call Center *</span>";
                                                    }
                                                }
                                                ?>
                                            </h2>

                                            <?php
                                            // agency integrated API
                                            //if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){
                                            ?>
                                            <style>
                                                .property_api_tbl tr,
                                                .property_api_tbl td{
                                                    border: 0 !important;
                                                }
                                                .property_api_tbl .timestamp_style{
                                                    text-align: center;
                                                }
                                            </style>
                                            <div style="float:right;margin:11px;margin-right:0px; width: 100%;">

                                                <?php if( $row['is_sales']!=1 ){ ?>
                                                    
                                                <table class="property_api_tbl">
                                                        <?php
                                                        // console
                                                        // check if agency has API key stored
                                                        $cak_sql = mysql_query("
                                                        SELECT COUNT(`id`) AS cak_count
                                                        FROM `console_api_keys`
                                                        WHERE `agency_id` = {$agency_id}
                                                        ");
                                                        $cak_row = mysql_fetch_object($cak_sql);

                                                        if( $cak_row->cak_count > 0 ){ // console using webhooks

                                                        ?>

                                                            <tr>
                                                                <td>
                                                                    <table class="property_api_tbl">
                                                                        <tr>
                                                                            <td>
                                                                                <?php																																																			
                                                                                // get connected property
                                                                                $cak_sql = mysql_query("
                                                                                SELECT *
                                                                                FROM `property` AS p
                                                                                INNER JOIN `console_properties` AS cp ON ( p.`property_id` = cp.`crm_prop_id` AND cp.`active` = 1 )
                                                                                WHERE cp.`crm_prop_id` = {$property_id}	                                                                                														
                                                                                ");
                                                                                $cak_row = mysql_fetch_object($cak_sql);

                                                                                $enableApi = true;
                                                                                $controlerApi = 'console';
                                                                                $connTextApi = 'Console';
                                                                                $checkIdApi = $cak_row->console_prop_id;
                                                                                $console_prop_id = $cak_row->console_prop_id;
                                                                                $console_connected = ( mysql_num_rows($cak_sql) > 0 )?true:false;
                                                                               
                                                                                if ( $console_connected == true ) {  // property already connected 
                                                                                    
                                                                                    // crm CI link
                                                                                    $crm_ci_page = "/console/connection_details/{$property_id}";
                                                                                    $crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page);

                                                                                    $api_div_class = 'success';
                                                                                    $api_div_txt = "This Property is connected to {$connTextApi}";
                                                                                    $api_btn = "View {$connTextApi}";

                                                                                }else{ // not connected

                                                                                    // crm CI link
                                                                                    $crm_ci_page = "/console/to_connect/{$property_id}";
                                                                                    $crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page);

                                                                                    $api_div_class = 'error';
                                                                                    $api_div_txt = "This Property needs connecting to {$connTextApi}";
                                                                                    $api_btn = "Connect Now";

                                                                                } 
                                                                                ?>

                                                                                <div class="<?php echo $api_div_class; ?>">
                                                                                    <span><?php echo $api_div_txt; ?></span>
                                                                                    <a href="<?php echo $crm_ci_page_url; ?>" target="_blank">
                                                                                        <button type="button" class="submitbtnImg <?php echo ( $console_connected == true ) ? 'pme_btn_color' : 'grey-btn'; ?>" style="margin-right: 10px;">
                                                                                            <span class="inner_icon_txt">
                                                                                                <?php echo $api_btn; ?>
                                                                                            </span>
                                                                                        </button>
                                                                                    </a>
                                                                                </div>
                                                                         															
                                                                            </td>
                                                                        <tr>
                                                                    </table>
                                                                </td>
                                                            </tr>

                                                        <?php
                                                        }else{ // other API who uses agency token

                                                            // check if connected to API
                                                            $sel_query = "
                                                                agen_api_tok.`agency_api_token_id`,
                                                                agen_api_tok.`agency_id`,
                                                                agen_api_tok.`api_id`,

                                                                agen_api.`api_name`
                                                            ";
                                                            $api_token_params = array(
                                                                'sel_query' => $sel_query,
                                                                'active' => 1,
                                                                'agency_id' => $agency_id,
                                                                // 'group_by' => 'agen_api_tok.`agency_id`',
                                                                'display_query' => 0
                                                            );
                                                            $api_sql = $crm->get_agency_api_tokens($api_token_params);

                                                            while ($api_row = mysql_fetch_array($api_sql)) {
                                                                    $enableApi = false; // this is to ensure that api is completely implemented before showing it, add it new api integration on the if below.
                                                                    $controlerApi = "";
                                                                    $connTextApi = "";
                                                                    $checkIdApi = "";
                                                                    if ($api_row['api_id'] == 1) { // pme
                                                                        $enableApi = true;
                                                                        $controlerApi = 'property_me';
                                                                        $connTextApi = 'PropertyMe';
                                                                        $checkIdApi = $job_row['api_prop_id'];
                                                                    }else if ($api_row['api_id'] == 4) { // palace
                                                                        $enableApi = true;
                                                                        $controlerApi = 'palace';
                                                                        $connTextApi = 'Palace';
                                                                        $checkIdApi = $job_row['api_prop_id'];
                                                                    }else if ($api_row['api_id'] == 3) { // property tree

                                                                        $enableApi = true;
                                                                        $controlerApi = 'property_tree';
                                                                        $connTextApi = 'MRI Property Tree';
            
                                                                        // check if property is connected to Property Tree API
                                                                        $crm_connected_prop_sql_str = "
                                                                        SELECT `api_prop_id`
                                                                        FROM `api_property_data`
                                                                        WHERE `crm_prop_id` = {$property_id}
                                                                        AND `api` = 3
                                                                        ";
                                                                        $crm_connected_prop_sql = mysql_query($crm_connected_prop_sql_str);
                                                                        $crm_connected_prop_row = mysql_fetch_object($crm_connected_prop_sql);
            
                                                                        $checkIdApi = $crm_connected_prop_row->api_prop_id;
                                                                        $pt_prop_id = $crm_connected_prop_row->api_prop_id;
            
                                                                    }

                                                                    if ($enableApi) {
                                                                ?>
                                                            <tr>
                                                                <td>
                                                                    <table class="property_api_tbl">
                                                                        <tr>
                                                                            <td>
                                                                                <?php
                                                                                // crm CI link                                                                                
                                                                                if( $api_row['api_id'] == 3 ){ // property tree
                                                                                    $crm_ci_page = "/{$controlerApi}/connection_details/{$property_id}";
                                                                                }else{
                                                                                    $crm_ci_page = "/{$controlerApi}/property/{$property_id}/{$agency_id}";
                                                                                }
                                                                                $crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page);


                                                                                if ($checkIdApi != '') {  // property already connected
                                                                                    $api_div_class = 'success';
                                                                                    $api_div_txt = "This Property is connected to {$connTextApi}";
                                                                                    $api_btn = "View {$connTextApi}";
                                                                                } else { // not yet conneted
                                                                                    $api_div_class = 'error';
                                                                                    $api_div_txt = "This Property needs connecting to {$connTextApi}";
                                                                                    $api_btn = "Connect Now";
                                                                                }
                                                                                ?>
                                                                                <div class="<?php echo $api_div_class; ?>">
                                                                                    <span><?php echo $api_div_txt; ?></span>
                                                                                    <a href="<?php echo $crm_ci_page_url; ?>" target="_blank">
                                                                                        <button type="button" class="submitbtnImg <?php echo ( $checkIdApi != '' ) ? 'pme_btn_color' : 'grey-btn'; ?>" style="margin-right: 10px;">
                                                                                            <span class="inner_icon_txt">
                                                                                                <?php echo $api_btn; ?>
                                                                                            </span>
                                                                                        </button>
                                                                                    </a>
                                                                                </div>
                                                                            </td>
                                                                        <tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                                <?php
                                                                }
                                                            }

                                                        }                                                        
                                                        ?>
                                                </table>
                                                <?php } ?>
                                            </div>
                                            <?php
                                            //}
                                            ?>
                                        </div>
                                        <?php
                                        // get staff
                                        $staff_sql = mysql_query("
							SELECT *
							FROM `staff_accounts`
							WHERE `StaffID` = {$staff_id}
						");
                                        $staff = mysql_fetch_array($staff_sql);
                                        ?>


                                        <?php
                                        $cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
                                        $cntry = mysql_fetch_array($cntry_sql);



                                        if ($job_row['api_prop_id'] != '' && $agency_id != '' && $job_row['api'] == 1) {


                                            // get tenants contact ID
                                            $pme_tenants_arr = [];
                                            $agency_api_params = array(
                                                'prop_id' => $job_row['api_prop_id'],
                                                'agency_id' => $agency_id
                                            );

                                            $tenant_json = $agency_api->get_tenants($agency_api_params);
                                            $tenant_json_enc = json_decode($tenant_json);
                                            
                                            if ($tenant_json_enc->ResponseStatus->ErrorCode == '' && !empty($tenant_json_enc) ) { // no error
                                                $tenant_contact_id = $tenant_json_enc[0]->ContactId;

                                                if( $tenant_contact_id != '' ){

                                                    // get pme tenants
                                                    $agency_api_params = array(
                                                        'contact_id' => $tenant_contact_id,
                                                        'agency_id' => $agency_id
                                                    );
                                                    $contact_json = $agency_api->get_contact($agency_api_params);
                                                    $contact_json_enc = json_decode($contact_json);
                                                    
                                                    foreach ($contact_json_enc->ContactPersons as $pme_tenant) {
                                                        $pme_tenants_arr[] = array(
                                                            'fname' => trim($pme_tenant->FirstName),
                                                            'lname' => trim($pme_tenant->LastName),
                                                            'mobile' => $crm->remove_space(trim($pme_tenant->CellPhone)),
                                                            'landline' => $crm->remove_space(trim($pme_tenant->HomePhone)),
                                                            'email' => trim($pme_tenant->Email),
                                                            'UpdatedOn' => trim($contact_json_enc->Contact->UpdatedOn),
                                                            'company_name' => trim($pme_tenant->CompanyName)
                                                        );
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }

                                        if ($job_row['api_prop_id'] != '' && $agency_id != '' && $job_row['api'] == 4) {
                                        

                                            // get tenants contact ID
                                            $agency_api_params = array(
                                                'prop_id' => $job_row['api_prop_id'],
                                                'agency_id' => $agency_id
                                            );
                                            $tenant_json_dec = $agency_api->get_palace_tenants_v2($agency_api_params);

                                            $palace_tenants_arr = [];
                                            foreach ($tenant_json_dec as $tenant_json_data) {

                                                $palace_tenant_obj_row = $tenant_json_data->TenancyTenants[0];

                                                $palace_tenants_arr[] = array(
                                                    'fname' => trim($palace_tenant_obj_row->TenantFirstName),
                                                    'lname' => trim($palace_tenant_obj_row->TenantLastName),
                                                    'mobile' => $crm->remove_space(trim($palace_tenant_obj_row->TenantPhoneMobile)),
                                                    'landline' => $crm->remove_space(trim($palace_tenant_obj_row->TenantPhoneHome)),
                                                    'email' => trim($palace_tenant_obj_row->TenantEmail)
                                                );

                                            }


                                        }

                                        if ($job_row['api_prop_id'] != '' && $agency_id != '' && $job_row['api'] == 6) {

                                            $unixtime 	= time();
                                            $now 		= date("Y-m-d H:i:s",$unixtime);

                                            $api_id = 6;

                                            $agency_api_name_str = "
                                                SELECT 
                                                    `agency_name`
                                                FROM `agency`
                                                WHERE `agency_id` = {$agency_id}
                                            ";
                                            $agency_api_name_sql =  mysql_query($agency_api_name_str) or die(mysql_error());
                                            $a_api_name_row = mysql_fetch_array($agency_api_name_sql);
                                            $agency_name = $a_api_name_row['agency_name'];

                                            $agency_api_tokens_str = "
                                                SELECT 
                                                    `access_token`,
                                                    `expiry`,
                                                    `refresh_token`
                                                FROM `agency_api_tokens`
                                                WHERE `agency_id` = {$agency_id}
                                                AND `api_id` = {$api_id}
                                            ";
                                            $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
                                            $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);

                                            $expiry          = $a_api_tok_row['expiry'];
                                            $expired         = strtotime($now) - strtotime($expiry);

                                            $refresh_token   = $a_api_tok_row['refresh_token'];
                                            $tmp_refresh_token   = $a_api_tok_row['refresh_token'];
                                            $tmp_arr_refresh_token = explode("+/-]",$tmp_refresh_token);
                                            $refresh_token = $tmp_arr_refresh_token[0];

                                            $raccess_token   = $a_api_tok_row['access_token'];
                                            $refresh_token  = $refresh_token;
                                            $expiry         = date('Y-m-d H:i:s',strtotime('+3600 seconds'));
                                            $created        = $now;

                                            if($expired > 0){

                                                $options = array(
                                                    'grant_type'      => 'refresh_token',
                                                    'refresh_token'   =>  $refresh_token,
                                                    'client_id'		  => 'br6ucKvcPRqDNA1V2s7x',
                                                    'client_secret'	  => 'd5YOJHb6EYRw5oypl73CJFWGLob5KB9A',
                                                    'redirect_uri'	  => ''
                                                    );
                                        
                                                $api = new OurtradieApi($options, $_REQUEST);
                                                $token = $refresh_token;
            
                                                $response = $api->refreshToken($token);
                                                
                                                if(!empty($response)){
                                                    $raccess_token   = $response->access_token;
                                                    $refresh_token  = $response->refresh_token;
                                                    $expiry         = date('Y-m-d H:i:s',strtotime('+3600 seconds'));
                                                    $created        = $now;
                                                    $separator = "+/-]";
            
                                                    $update_token_str = "
                                                        UPDATE `agency_api_tokens`
                                                        SET 
                                                            `access_token` = '{$raccess_token}',
                                                            `expiry` = '{$expiry}',
                                                            `refresh_token` = '$refresh_token"."+/-]"."$tmp_arr_refresh_token[1]'
                                                        WHERE `agency_id` = {$agency_id}
                                                        AND `api_id` = {$api_id}
                                                    ";
                                                    mysql_query($update_token_str) or die(mysql_error());
            
                                                    $access_token    = $raccess_token;
                                                }
                                            }
                                            else{
                                                $access_token    = $a_api_tok_row['access_token'];
                                            }

                                            $api_id = 6;

                                            $token = array('access_token' => $access_token);

                                            $api = new OurtradieApi();

                                            $ot_agency_id = $tmp_arr_refresh_token[1];

                                            //GetAllResidentialProperties
                                            $params = array(
                                                'Skip' 	 		=> 'No',
                                                'Count'     => 'No',
                                                'AgencyID'  => $ot_agency_id
                                            );
                                            $property = $api->query('GetAllResidentialProperties', $params, '', $token, true);

                                            $data_property = array();
                                            $data_property = json_decode($property, true);

                                            $data['property_list'] = array_filter($data_property, function ($v) {
                                            return $v !== 'OK';
                                            });

                                            foreach($data['property_list'] as $ot_prop){
                                                foreach($ot_prop as $row1){
                                                    
                                                    //$tmp_data = $row;
                                                    if($row1['ID'] == $job_row['api_prop_id']){
                                                        $tenants_data = $row1['Tenant_Contacts'];
                                                    }
                                                }
                                            }

                                            
                                            $ourtradie_tenants_arr = [];
                                            foreach ($tenants_data as $tenants_row) {

                                                /*
                                                echo "<br /><br />";
                                                echo "==== Tenant First Name: <br />";
                                                echo $tenants_row['FirstName'];
                                                */
                                                
                                                $ourtradie_tenants_arr[] = array(
                                                    'fname' => trim($tenants_row['FirstName']),
                                                    'lname' => trim($tenants_row['LastName']),
                                                    'mobile' => $crm->remove_space(trim($tenants_row['Mobile'])),
                                                    'email' => trim($tenants_row['Email'])
                                                );
                                            }

                                        }

                                        // property tree tenants
                                        if ( $pt_prop_id != ''  && $agency_id != '') {

                                            /* commented, bec this needs to load above, it is need on the deleted banner
                                            // get tenants contact ID
                                            $agency_api_params = array(
                                            'property_id' => $property_id
                                            );

                                            $api_prop_json = $agency_api->get_property_tree_property($agency_api_params);
                                            $api_prop_obj = $api_prop_json[0];
                                            */                                        

                                            
                                           if( $http_status_code == 200 ){ // OK

                                                $api_prop_obj = $json_decoded_response[0];

                                                if( $api_prop_obj->tenancy != '' ){
            
                                                    // get tenants contact ID
                                                    $agency_api_params = array(
                                                        'tenancy_id' => $api_prop_obj->tenancy,
                                                        'agency_id' => $agency_id
                                                    );
                                                    $api_tenant_json = $agency_api->get_property_tree_tenancy($agency_api_params);
                                                    $contact_arr = $api_tenant_json->contacts;
                    
                                                    $pt_tenants_arr = [];
                                                    foreach ($contact_arr as $contact_obj) {
                                                                                                                                                                         
                                                        $ct_has_tenant = false;											
                                                        foreach( $contact_obj->contact_types as $contact_types ){

                                                            if( $contact_types == 'Tenant' ){
                                                                $ct_has_tenant = true;
                                                            }

                                                        }

                                                        if( $ct_has_tenant ==  true ){

                                                            $pt_tenants_arr[] = array(
                                                                'fname' => trim($contact_obj->first_name),
                                                                'lname' => trim($contact_obj->last_name),
                                                                'mobile' => $crm->remove_space(trim($contact_obj->mobile_phone_number)),
                                                                'landline' => $crm->remove_space(trim($contact_obj->phone_number)),
                                                                'email' => trim($contact_obj->email_address)
                                                            );

                                                        }
                    
                                                    }        
                
                                                }	
                                            
                                           }  
                                                                                                                            								                             
           
                                       }


                                        // console API tenants
                                        if ( $console_prop_id > 0 ) {                                    

                                            // get console tenants
                                            $console_tenant_sql_str = "
                                            SELECT *
                                            FROM `console_property_tenants` AS cpt
                                            INNER JOIN `console_properties` AS cp ON ( cpt.`console_prop_id` = cp.`console_prop_id` AND cp.`active` = 1 )
                                            WHERE cp.`crm_prop_id` = {$property_id}                                            
                                            AND cpt.`active` = 1
                                            AND cpt.`is_landlord` = 0
                                            ";
                                            $console_tenant_sql = mysql_query($console_tenant_sql_str);
            
                                            $console_tenants_arr = [];                     
                                            while ( $console_tenant_row = mysql_fetch_object($console_tenant_sql) ) {	

                                               
                                                
                                                // get console tenants phones
                                                $console_tent_phone_sql = mysql_query("
                                                SELECT *
                                                FROM `console_property_tenant_phones` AS cpt_phones
                                                INNER JOIN `console_property_tenants` AS cpt ON cpt_phones.`contact_id` = cpt.`contact_id`
                                                WHERE cpt.`contact_id` = {$console_tenant_row->contact_id}                            
                                                AND cpt_phones.`active`
                                                ");
            
                                                $console_tent_phone_arr = [];
                                                while ( $console_tent_phone_row = mysql_fetch_object($console_tent_phone_sql) ){ 
                                                    
                                                    $console_tent_phone_arr[] = array(
                                                        'type' => trim($console_tent_phone_row->type),
                                                        'number' => trim($console_tent_phone_row->number),
                                                        'primary' => trim($console_tent_phone_row->is_primary)
                                                    );

                                                }

                                                // get console tenants emails
                                                $console_tent_email_sql = mysql_query("
                                                SELECT *
                                                FROM `console_property_tenant_emails` AS cpt_emails
                                                INNER JOIN `console_property_tenants` AS cpt ON cpt_emails.`contact_id` = cpt.`contact_id`
                                                WHERE cpt.`contact_id` = {$console_tenant_row->contact_id}                            
                                                AND cpt_emails.`active`
                                                ");
            
                                                $console_tent_email_arr = [];
                                                while ( $console_tent_email_row = mysql_fetch_object($console_tent_email_sql) ){ 
                                                    
                                                    $console_tent_email_arr[] = array(
                                                        'type' => trim($console_tent_email_row->type),
                                                        'email' => trim($console_tent_email_row->email),
                                                        'primary' => trim($console_tent_email_row->is_primary)
                                                    );

                                                }

                                                $console_tenants_arr[] = array(
                                                    'fname' => trim($console_tenant_row->first_name),
                                                    'lname' => trim($console_tenant_row->last_name),
                                                    'phone' => $console_tent_phone_arr,
                                                    'email' => $console_tent_email_arr
                                                );
            
                                            }   
                                            
                                            /*
                                            echo "<pre>";
                                            print_r($console_tenants_arr);
                                            echo "</pre>";
                                            */
        
                                        }

                                        // add aditional OR api connection here ex. $job_row['NEW IMPLEMENT API']
                                        if ($job_row['api_prop_id'] != '' || $pt_prop_id != '') {
                                            // get crm tenants
                                            $params = array('property_id' => $property_id, 'active' => 1);
                                            $sqlGetTenants = $crm->getNewTenantsData($params);
                                            $crm_tenants_arr = [];
                                            while ($crm_tenant = mysql_fetch_array($sqlGetTenants)) {
                                                $crm_tenants_arr[] = array(
                                                    'fname' => trim($crm_tenant['tenant_firstname']),
                                                    'lname' => trim($crm_tenant['tenant_lastname']),
                                                    'mobile' => $crm->remove_space(trim($crm_tenant['tenant_mobile'])),
                                                    'landline' => $crm->remove_space(trim($crm_tenant['tenant_landline'])),
                                                    'email' => trim($crm_tenant['tenant_email'])
                                                );
                                            }
                                        }
                                        ?>

                                        <?php
                                        include 'tenant_details_new.php';
                                        ?>

                                        <input type="hidden" name="tenants_changed" id="tenants_changed" value="0" />

                                        <div class="source_of_company">
                                            <?php
                                                $result = $crm->get_property_source($property_id);

                                                echo isset($result['company_name']) && !empty($result['company_name']) ? "<button type='button' class='blue-btn submitbtnImg'> {$result['company_name']}</button>" : "";
                                            ?>
                                        </div>

                                        <div style="float:right;margin:11px;margin-right:0px;">

                                            <button type="button" id="add_new_tenant_btn" class="blue-btn submitbtnImg">
                                                <img class="inner_icon" src="images/button_icons/add-button.png">
                                                <span class="inner_icon_txt">Tenant</span>
                                            </button>
                                            <button type="button" id="view_all_tick_column" class="blue-btn submitbtnImg">
                                                <!-- <img class="inner_icon" src="images/button_icons/show-button.png">  -->
                                                &#10006; <span class="inner_icon_txt">Hide</span>
                                            </button>

                                        </div>


                                        <?php
                                        if ($job_row['api_prop_id'] != '' && $job_row['api'] == 1 && $agency_id != '' && count($pme_tenants_arr) > 0 && false) { // set to false to NOT display
                                            ?>

                                            <div style="clear:both;"></div>


                                            <div id="all_pme_table">
                                                <h2 class="heading" style="margin-left: 7px;">
                                                    PropertyMe Tenants
                                                    <button type="button" id="view_all_pme_tnt_btn" class="blue-btn submitbtnImg">
                                                        <img class="inner_icon" src="images/button_icons/show-button.png">
                                                        <span class="inner_icon_txt">Show</span>
                                                    </button>
                                                </h2>
                                                <table id="pme_tnt_tbl" class="table-center tbl-fr-red view-property-table-inner jtenant_table">
                                                    <tr>
                                                        <td class="j_tbl_heading">First Name</td>
                                                        <td class="j_tbl_heading">Last Name</td>
                                                        <td class="j_tbl_heading">Mobile</td>
                                                        <td class="j_tbl_heading">Landline</td>
                                                        <td class="j_tbl_heading">Email</td>
                                                        <td class="j_tbl_heading">Action</td>
                                                    </tr>
                                                    <?php
                                                    foreach ($pme_tenants_arr as $pme_tnt_row) {

                                                        $pme_tenants_full_name = "{$pme_tnt_row['fname']} {$pme_tnt_row['lname']}";

                                                        $row_hl = "PMe_tenant_new_bg";

                                                        $tenant_already_exist = 0;
                                                        $tenant_has_update = 0;
                                                        $new_tenant = 0;
                                                        foreach ($crm_tenants_arr as $crm_tenant) {

                                                            $crm_tenant_full_name = "{$crm_tenant['fname']} {$crm_tenant['lname']}";

                                                            // same all 5 fields
                                                            if (
                                                                    $crm_tenant_full_name == $pme_tenants_full_name &&
                                                                    $crm_tenant['mobile'] == $pme_tnt_row['mobile'] &&
                                                                    $crm_tenant['landline'] == $pme_tnt_row['landline'] &&
                                                                    $crm_tenant['email'] == $pme_tnt_row['email']
                                                            ) {

                                                                $tenant_already_exist = 1;
                                                            } else {

                                                                if ($crm_tenant_full_name == $pme_tenants_full_name) {
                                                                    $tenant_has_update = 1;
                                                                } else {
                                                                    $new_tenant = 1;
                                                                }
                                                            }
                                                        }

                                                        /*
                                                          echo "tenant_already_exist: {$tenant_already_exist}<br />";
                                                          echo "tenant_has_update: {$tenant_has_update}<br />";
                                                          echo "new_tenant: {$new_tenant}<br /><br />";
                                                         */

                                                        // highlight color
                                                        if ($tenant_already_exist == 1) {
                                                            $row_hl = "PMe_tenant_exist_bg hideIt";
                                                        } else if ($tenant_has_update == 1) {
                                                            $row_hl = "crm_tenant_need_update_bg";
                                                        } else if ($new_tenant == 1) {
                                                            $row_hl = "PMe_tenant_new_bg";
                                                        }
                                                        ?>

                                                        <tr class="<?php echo $row_hl; ?>">
                                                            <td>
                                                                <?php echo $pme_tnt_row['fname']; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo $pme_tnt_row['lname']; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo $pme_tnt_row['mobile']; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo $pme_tnt_row['landline']; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo $pme_tnt_row['email']; ?>
                                                            </td>
                                                            <td>

                                                                <input type="hidden" class="pme_tenant_fname" value="<?php echo $pme_tnt_row['fname']; ?>" />
                                                                <input type="hidden" class="pme_tenant_lname" value="<?php echo $pme_tnt_row['lname']; ?>" />
                                                                <input type="hidden" class="pme_tenant_mobile" value="<?php echo $pme_tnt_row['mobile']; ?>" />
                                                                <input type="hidden" class="pme_tenant_landline" value="<?php echo $pme_tnt_row['landline']; ?>" />
                                                                <input type="hidden" class="pme_tenant_email" value="<?php echo $pme_tnt_row['email']; ?>" />

                                                                <button type="button" class="blue-btn submitbtnImg add_new_pme_tenant_btn">
                                                                    <img class="inner_icon" src="images/button_icons/add-button.png">
                                                                    <span class="inner_icon_txt">Add</span>
                                                                </button>

                                                            </td>
                                                        </tr>

                                                        <?php
                                                    }
                                                    ?>
                                                </table>
                                            </div>

                                            <?php
                                        }
                                        ?>



                                    </div>



                                    <?php
                                    /*
                                      $reg_sql = mysql_query("
                                      SELECT *
                                      FROM `postcode_regions`
                                      WHERE `postcode_region_postcodes` LIKE '%{$row[13]}%'
                                      AND `country_id` = {$_SESSION['country_default']}
                                      AND `deleted` = 0
                                      ");
                                      $reg = mysql_fetch_array($reg_sql);

                                      $tr_sql = mysql_query("
                                      SELECT *
                                      FROM  `tech_run`
                                      WHERE `sub_regions` LIKE '%{$reg['postcode_region_id']}%'
                                      AND `date` > '".date('Y-m-d')."'
                                      ORDER BY `date`
                                      LIMIT 1
                                      ");
                                      $tr = mysql_fetch_array($tr_sql);

                                      $reg_arr2 = explode(",",$tr['sub_regions']);

                                      //print_r($reg_arr2);

                                      if( in_array($reg['postcode_region_id'], $reg_arr2) ){

                                      //$tr = mysql_fetch_array($tr_sql2);
                                      $tr_id = $tr['tech_run_id'];


                                      }
                                     */





                                    //if( mysql_num_rows($tr_sql)>0 ){

                                    if ($row[2] != 'Merged Certificates' && $row[2] != 'Completed') {
                                        ?>


                                        <div style="float:left; margin-left: 40px;" id="available_days_main_div">

                                            <div style="margin-bottom:17px; font-weight: bold;"><h2 class="heading" id="available_days_header_txt">Available Days:</h2></div>
                                            <?php
                                            // job date
                                            if ($row['jdate'] != "" && $row['jdate'] != "0000-00-00") {
                                                $str_date = $row['jdate'];
                                            } else if (( $row['jdate'] == "" || $row['jdate'] == "0000-00-00" ) && $_GET['tr_date'] != "") {
                                                // tech run date
                                                $str_date = mysql_real_escape_string($_GET['tr_date']);
                                            } else {
                                                // current date
                                                $str_date = date('Y-m-d');
                                            }

                                            // fetch all future STR
                                            $other_str_txt = "
                                            SELECT
                                                trr.`hidden`,
                                                trr.`highlight_color`,

                                                tr.`tech_run_id`,
                                                tr.`show_hidden`,
                                                tr.`date` AS tr_date,
                                                tr.`ready_to_book`,

                                                j.`job_type`,
                                                j.`unavailable`,
                                                j.`unavailable_date`,
                                                j.`start_date`,
                                                j.`status` AS j_status,
                                                j.`is_eo`,

                                                sa.`is_electrician`,
                                                sa.`FirstName`,
                                                sa.`LastName`
                                            FROM `tech_run_rows` AS trr
                                            LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
                                            LEFT JOIN `tech_run_row_color` AS trrc ON trr.`highlight_color` = trrc.`tech_run_row_color_id`
                                            LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
                                            LEFT JOIN `jobs` AS j ON j.`id` = trr.`row_id`
                                            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                                            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                                            AND trr.`row_id_type` =  'job_id'
                                            WHERE j.`id` = {$job_id}
                                            AND trr.`hidden` = 0
                                            AND j.`del_job` = 0
                                            AND tr.`country_id` = {$_SESSION['country_default']}
                                            AND a.`country_id` = {$_SESSION['country_default']}
                                            AND tr.`date` >= '" . date('Y-m-d') . "'
                                            ORDER BY tr.`date` ASC
									        ";
                                            //echo "<div style='display:none;'>{$other_str_txt}</div>";
                                            $other_str_sql = mysql_query($other_str_txt);

                                            if (mysql_num_rows($other_str_sql) > 0) {
                                                ?>

                                                <table style="border-collapse: initial;">

                                                    <?php
                                                    $ctr = 0;
                                                    while ($other_str = mysql_fetch_array($other_str_sql)) {

                                                        $hiddenText = "";
                                                        $showRow = 1;
                                                        $isUnavailable = 0;
                                                        $isHidden = 0;
                                                        $isPriority = 0;
                                                        $show_hidden = $other_str['show_hidden'];
                                                        $date = $other_str['tr_date'];
                                                        $isElectrician = ( $other_str['is_electrician'] == 1 ) ? true : false;

                                                        // only show 240v rebook to electrician
                                                        if ( ( $other_str['job_type'] == '240v Rebook' || $other_str['is_eo'] == 1 ) && $isElectrician == false) {
                                                            $hiddenText .= '240v<br />';
                                                            $showRow = 0;
                                                        } else {
                                                            $showRow = 1;
                                                        }

                                                        if ($other_str['hidden'] == 1) {
                                                            $hiddenText .= 'User<br />';
                                                        }

                                                        if ($other_str['unavailable'] == 1 && $other_str['unavailable_date'] == $date) {
                                                            $isUnavailable = 1;
                                                            $hiddenText .= 'Unavailable<br />';
                                                        }

                                                        $startDate = date('Y-m-d', strtotime($other_str['start_date']));

                                                        if ($other_str['job_type'] == 'Lease Renewal' && ( $other_str['start_date'] != "" && $date < $startDate )) {
                                                            $hiddenText .= 'LR<br />';
                                                        }

                                                        if ($other_str['job_type'] == 'Change of Tenancy' && ( $other_str['start_date'] != "" && $date < $startDate )) {
                                                            $hiddenText .= 'COT<br />';
                                                        }

                                                        if ($other_str['j_status'] == 'DHA' && ( $other_str['start_date'] != "" && $date < $startDate )) {
                                                            $hiddenText .= 'DHA<br />';
                                                        }

                                                        if ($other_str['j_status'] == 'On Hold' && ( $other_str['start_date'] != "" && $date < $startDate )) {
                                                            $hiddenText .= 'On Hold<br />';
                                                        }

                                                        /*
                                                          if( $row2['j_status'] == 'Allocate' && ( $row2['start_date']!="" && $date < $startDate ) ){
                                                          $hiddenText .= 'Allocate<br />';
                                                          }
                                                         */


                                                        if ($show_hidden == 0 && $hiddenText != "") {
                                                            $showRow = 0;
                                                        } else {
                                                            $showRow = 1;
                                                        }

                                                        if ($showRow == 1) {

                                                             // only show tech run if marked "ready to book"
                                                             if( $other_str['ready_to_book'] == 1 ){
                                                            ?>
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" class="tr_id" value="<?php echo $other_str['tech_run_id']; ?>" />
                                                                    <div style="display:none;"><?php echo "Unavailable: {$other_str['unavailable']} - {$other_str['unavailable_date']} Hidden Text: {$hiddenText}"; ?></div>
                                                                </td>
                                                                <td style="font-size: 13px; border: none;">
                                                                    <a href="/set_tech_run.php?tr_id=<?php echo $other_str['tech_run_id']; ?>">
                                                                        <?php echo date('l d/m', strtotime($other_str['tr_date'])); ?>
                                                                    </a>
                                                                    <?php echo $crm->formatStaffName($other_str['FirstName'], $other_str['LastName']); ?>
                                                                    <span>
                                                                        <?php
                                                                        $colour_tbl_sql = getColourTableStatus($other_str['tech_run_id'], $other_str['highlight_color']);
                                                                        $colour_tbl = mysql_fetch_array($colour_tbl_sql);

                                                                            if ($colour_tbl['time'] != '') {

                                                                                $status_dif_txt = '';
                                                                                $ct_booking_status = $colour_tbl['booking_status'];

                                                                                if ($ct_booking_status != '') {

                                                                                    if ($ct_booking_status == 'FULL') {
                                                                                        $status_dif_txt = "<span style='color:red;'>(FULL)</span>";
                                                                                    } else {
                                                                                        $status_dif_txt = "({$ct_booking_status})";
                                                                                    }
                                                                                }

                                                                                if ($colour_tbl['no_keys'] == 1) {
                                                                                    $no_keys_txt = " <span style='color:red;'>NO KEYS</span>";
                                                                                } else {
                                                                                    $no_keys_txt = "";
                                                                                }

                                                                                echo "({$colour_tbl['time']}{$no_keys_txt}) {$status_dif_txt}";
                                                                            } else {
                                                                                echo "(No Time Set)";
                                                                            }

                                                                        ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            $ctr++;
                                                            }
                                                        }
                                                    }
                                                    ?>

                                                </table>

                                                <?php if ($ctr == 0) { ?>
                                                    <div>Sorry there are no other available days scheduled in your area at the moment</div>
                                                    <?php
                                                }
                                                ?>

                                            <?php } else {
                                                ?>

                                                <div>Sorry there are no other available days scheduled in your area at the moment</div>

                                                <?php
                                            }
                                            ?>




                                        </div>

                                        <?php
                                    }

                                    //}
                                    ?>


                                </td>

                            </tr>

                            <?php
                            if($job_row['priority'] == 1){
                                $ap = "(HT)";
                            }
                            else if($job_row['priority'] == 2){
                                $ap = "(VIP)";
                            }
                            else if($job_row['priority'] == 3){
                                $ap = "(HWC)";
                            }
                            else{
                                $ap = "";
                            }
                            // agency
                            echo "<tr class='tr_border_topnBottom tr_rightBorder'>";
                            echo "<td valign=top>
				<h2 class='heading' style='float: left;'>Agency</h2>";


                            echo "</td>";
                           $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row[24]}");
                            echo "<td><a class='".( ( $job_row['priority'] > 0 )?'j_bold':null )."' href='$ci_link'>$row[25] ".( $ap )."</a> {$row[29]}";

                                if( $job_row['allow_upfront_billing'] == 1 ){
                                    echo '<img src="/images/upfront_bill.png" class="upfront_bill_icon" title="Upfront Billing Agency" />';
                                }

                            echo "</td>";
                            echo "<td valign=top><h2 class='heading'>Agency Comments</h2></td>";
                            // get agency comment
                            $jcom_sql = mysql_query("
				SELECT `comment`
				FROM `agency`
				WHERE `agency_id` = {$row[24]}
			");
                            $jcom = mysql_fetch_array($jcom_sql);
                            $red_glow = ($jcom['comment'] != '') ? 'border: 1px solid #b4151b;box-shadow: 0 0 2px #b4151b inset;' : '';
                            echo "<td colspan='2' class='td_rightBorder'><input type='text' readonly='readonly' style='width:260px;{$red_glow}' value='{$jcom['comment']}' /></td>";
                            echo "</tr>";



                            // property address
                            echo "<tr class='tr_border_topnBottom tr_rightBorder'>";
                            echo "<td valign=top><h2 class='heading'>Property Address</h2></td>";
                            echo "<td>";
                            if($crm->check_links() == 0){
                                echo "<a href='" . URL . "view_property_details.php?id={$property_id}'>{$prop_add}</a>";
                                if( $job_row['requires_ppe'] == 1 ){
                                    echo "<img src='/images/ppe_icon.png' class='ppe_icon' />";
                                }
                            } else {
                                echo "<a href='" . $crm->crm_ci_redirect(rawurlencode('/properties/details/?id=' . $property_id . '&tab=1')) . "'>" . $prop_add . "</a>";
                            }
                            
                            echo "</td>";
                            /* old table
                            $sub_reg_sql = mysql_query("
                                SELECT *
                                FROM `postcode_regions`
                                WHERE `postcode_region_postcodes` LIKE '%{$row[13]}%'
                                AND `country_id` = {$_SESSION['country_default']}
                                AND `deleted` = 0
                            ");
                            */

                            ##new table (by:gherx)
                            $sub_reg_sql = mysql_query("
                                SELECT sr.subregion_name as postcode_region_name
                                FROM `sub_regions` as sr
                                LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
                                WHERE pc.`postcode` = {$row[13]}
                                AND pc.`deleted` = 0
                            ");

                            $sub_reg = mysql_fetch_array($sub_reg_sql);
                            
                            echo "<td><h2 class='heading'>" . getDynamicRegionViaCountry($_SESSION['country_default']) . "</h2></td>";
                            echo "<td colspan='2'>{$sub_reg['postcode_region_name']}</td>";


                            echo "</tr>";


                            $jsql = mysql_query("
				SELECT *
				FROM `jobs` AS j
				LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
				WHERE j.`id` = {$job_id}
			");
                            $j = mysql_fetch_array($jsql);
                            // job type
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";



                            echo "<td>Job Type <span class='colorItRed'>{$service_name}</span></td>";
                            echo "<td><select name='jobtype' id='jobtype' class='vw-jb-sel'>";
                            echo "<option value='None Selected' $nojob>No Job Type Selected</option>";
                            foreach ($jobtypes as $types) {
                                $selected = ($jobsel == $types[0] ? "selected='selected'" : "");
                                echo "<option value='" . $types[0] . "' " . $selected . " >" . $types[0] . "</option>";
                            }
                            echo "</select>
			<input type='hidden' name='curr_job_type' value='{$jobsel}' />
			</td>";

                            echo "<td>Job Created</td>";

                            if ($row[2] == 'Completed') {

                                // Age
                                $date1 = date_create(date('Y-m-d', strtotime($j['created'])));
                                $date2 = date_create($row['jdate']);
                                $diff = date_diff($date1, $date2);
                                $age = $diff->format("%r%a");
                                $age2 = (((int) $age) != 0) ? $age : 0;
                            } else {

                                // Age
                                $date1 = date_create(date('Y-m-d', strtotime($j['created'])));
                                $date2 = date_create(date('Y-m-d'));
                                $diff = date_diff($date1, $date2);
                                $age = $diff->format("%r%a");
                                $age2 = (((int) $age) != 0) ? $age : 0;
                            }

                            $day_text = ($age2 > 1) ? 'days' : 'day';
                            $age_text = ($age2 > 0) ? "{$age2} {$day_text} old" : '';


                            echo "<td colspan='2'>
			<input type='text' readonly='readonly' style='width:71px' value='" . ( ( $j['created'] != "" && $j['created'] != "0000-00-00 00:00:00" ) ? date("d/m/Y", strtotime($j['created'])) : '' ) . "' />
			{$age_text}
			</td>";

                            echo "</tr>";


                            switch ($row[2]) {
                                case "To Be Booked": $tb = "selected";
                                    break;
                                case "Send Letters": $sl = "selected";
                                    break;
                                case "Booked": $bk = "selected";
                                    break;
                                case "Cancelled": $cl = "selected";
                                    break;
                                case "Completed": $cp = "selected";
                                    break;
                                case "Pre Completion": $pc = "selected";
                                    break;
                                case "Merged Certificates": $mc = "selected";
                                    break;
                                case "Pending": $pe = "selected";
                                    break;
                                case "Action Required": $ar = "selected";
                                    break;
                                case "On Hold": $pa = "selected";
                                    break;
                                case "DHA": $dha = "selected";
                                    break;
                                case "To Be Invoiced": $tbi = "selected";
                                    break;
                                case "On Hold - COVID": $oh_cv19 = "selected";
                                    break;
                                case "Escalate": $escalate = "selected";
                                    break;
                                case "Allocate": $allocate = "selected";
                                    break;
                                case "": $nb = "selected";
                                    break;
                            }



                            $ha_span = "";

                            if ($row['prop_upgraded_to_ic_sa'] == 1) {
                                $ha_span = "<strong class='colorItRed'>PROPERTY UPGRADED</strong>";
                            } else if ( $crm->check_prop_first_visit($property_id) == true ) {
                                $ha_span = "<span class='colorItRed'>FIRST VISIT</span>";
                            }
                            // job status
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";
                            echo "<td>Job Status {$ha_span}</td>";
                            echo "<td><select id='job_status' name='status' class='vw-jb-sel'>";
                            echo "<option value=''>No Job Status Selected</option>";
                            echo "<option value='Send Letters' $sl>Send Letters</option>";
                            echo "<option value='On Hold' $pa>On Hold</option>";
                            echo "<option value='To Be Booked' $tb>To Be Booked</option>";
                            echo "<option value='Booked' $bk>Booked</option>";
                            echo "<option value='Pre Completion' $pc>Pre Completion</option>";
                            echo "<option value='Merged Certificates' $mc>Merged Certificates</option>";
                            echo "<option value='Completed' $cp>Completed</option>";
                            echo "<option value='Pending' $pe>Pending</option>";
                            echo "<option value='Cancelled' $cl>Cancelled</option>";
                            echo "<option value='Action Required' $ar>Action Required</option>";
                            echo "<option value='DHA' $dha>DHA</option>";
                            echo "<option value='To Be Invoiced' $tbi>To Be Invoiced</option>";
                            if ($_SESSION['country_default'] == 2) {
                                echo "<option value='On Hold - COVID' $oh_cv19>On Hold - COVID</option>";
                            }
                            echo "<option style='color:red;' value='Escalate' $escalate>Escalate **</option>";
                            echo "<option style='color:red;' value='Allocate' $allocate>Allocate **</option>";
                            echo "</select>";
                            echo "<input type='hidden' name='curr_status' id='curr_status' value='" . $row[2] . "' />";
                            echo "<input type='hidden' name='status_changed_flag' id='status_changed_flag' />";

                            echo "<br />";
                            ?>
                            <div id="escalate_job_div" style="display:<?php echo ($row[2] == "Escalate") ? 'block' : 'none'; ?>; margin-top: 12px;">
                                <select name="escalate_job_reasons" class="vw-jb-sel redBorder">
                                    <option value="">--- Select ---</option>
                                    <?php
                                    // get escalate job reasons
                                    $esc_jobs_str = "
				SELECT *
				FROM `escalate_job_reasons`
				WHERE `deleted` = 0
				AND `active` = 1
				ORDER BY `sort_num` ASC
			";
                                    $esc_jobs_sql = mysql_query($esc_jobs_str);

                                    while ($esc_job = mysql_fetch_array($esc_jobs_sql)) {

                                        $hide_escalate = 0;

                                        /*
                                        // hide escalate NLM for OS call centre
                                        if ($_SESSION['USER_DETAILS']['ClassID'] == 8 && $esc_job['escalate_job_reasons_id'] == 6) {
                                            $hide_escalate = 1;
                                        }
                                        */

                                        if ($hide_escalate != 1) {

                                            // check selected escalate job reasons
                                            $sel_esc_str = "
						SELECT *
						FROM `selected_escalate_job_reasons`
						WHERE `deleted` = 0
						AND `active` = 1
						AND `job_id` = {$job_id}
						AND `escalate_job_reasons_id` = {$esc_job['escalate_job_reasons_id']}
					";
                                            $sel_esc_job_sql = mysql_query($sel_esc_str);
                                            $ejr_selected = ( mysql_num_rows($sel_esc_job_sql) > 0 ) ? true : false;
                                            ?>
                                            <option value="<?php echo $esc_job['escalate_job_reasons_id']; ?>" <?php echo ( $ejr_selected == true ) ? 'selected="selected"' : ''; ?> style="color:<?php echo ( $ejr_selected == true ) ? 'red' : 'black'; ?>;"><?php echo $esc_job['reason_short']; ?></option>

                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <img id="escalate_green_check"  class="green_check" style="display:none; position:relative; bottom: 16px; left: 14px;" src="/images/check_icon2.png" />
                            </div>






                            <div id="allocate_job_div" style="display:<?php echo ($row[2] == "Allocate") ? 'block' : 'none'; ?>; margin-top: 12px;">



                                <div class="allocate_div">
                                    <input type="radio" class="allocate_opt" data-alloc-type="allocate_opt" name="allocate_opt" value="1" <?php echo ($row['allocate_opt'] == 1) ? 'checked="checked"' : ''; ?> /> <span class="allocate_label">2 Hours</span>
                                    <input type="radio" class="allocate_opt" data-alloc-type="allocate_opt" name="allocate_opt" value="2" <?php echo ($row['allocate_opt'] == 2) ? 'checked="checked"' : ''; ?> /> <span class="allocate_label">4 Hours</span>
                                    <input type="radio" class="allocate_opt" data-alloc-type="allocate_opt" name="allocate_opt" value="3" <?php echo ($row['allocate_opt'] == 3) ? 'checked="checked"' : ''; ?> /> <span class="allocate_label">Today</span>
                                </div>


                                <div style="margin-top:9px">
                                    <span class="allocate_label">Notes</span>
                                    <img id="escalate_green_check" class="green_check" style="display:none; width: 20px; margin-left: 6px;" src="/images/check_icon2.png" /><br />
                                    <textarea data-alloc-type="allocate_notes" style="width: 222px; height: 28px; margin: 7px 0 0 0; padding: 5px 0 0 5px; border: 1px solid red;" class="addtextarea allocate_notes" name="allocate_notes"><?php echo $status == "To Be Booked" ? "" : $row['allocate_notes']; ?></textarea>
                                </div>


                            </div>


                            <?php
                            $invoice_number = getCheckDigit(trim($_REQUEST['id']));
                            echo "</td>";


                            echo "<td>Job Number</td>";
                            echo "<td colspan='2'><input type='text' readonly='readonly' value='{$_REQUEST['id']}' class='addinput vw-jb-inpt' /></td>";
                            echo "</tr>";
                            
                            if ($_SESSION['country_default']==1) {
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";
                            echo "<td></td>";
                            echo "<td></td>";
                            echo "<td>Invoice Number</td>";
                            echo "<td colspan='2'><input type='text' readonly='readonly' value='{$_REQUEST['id']}{$invoice_number}' class='addinput vw-jb-inpt' /></td>";
                            echo "</tr>";
                            // date
                            }
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";
                            echo "<td>Date</td>";
                            echo "<td>";

                            $error_border = 0;
                            if ($row['jdate'] != "" && $row['jdate'] != "0000-00-00") {
                                $date_fin = date("d/m/Y", strtotime($row['jdate']));
                            } else if (( $row['jdate'] == "" || $row['jdate'] == "0000-00-00" ) && $_GET['tr_date'] != "") {
                                $date_fin = date("d/m/Y", strtotime(mysql_real_escape_string($_GET['tr_date'])));
                                $error_border = 1;
                            } else {
                                $date_fin = ($row['jdate'] != "" && $row['jdate'] != "0000-00-00") ? date("d/m/Y", strtotime($row['jdate'])) : '';
                            }

                            echo "<input TYPE=\"text\" id=\"jobdate\" NAME=\"jobdate\" value=\"" . $date_fin . "\" class='addinput vw-jb-inpt jobdate datepicker " . ((in_array("date", $el)) ? 'jerr_hl' : '') . " " . (($error_border == 1) ? 'error_border' : '') . "'> ";
                            echo "<input type=\"hidden\" NAME=\"jobdate_orig\" value=\"" . $date_fin . "\" /> ";

                            echo "</td>";



                            // work order
                            $wo_txt = (in_array($row[24], $dha_agencies)) ? 'MITM' : 'Work Order';

                            // get files uploaded same day job was created
                            $property_file_sql_str = "
                            SELECT `property_files_id`,`property_id`,`path`,`filename`
                            FROM `property_files`
                            WHERE `property_id` = {$property_id}
                            AND DATE(`date_created`) = '".date('Y-m-d',strtotime($job_row['jcreated']))."'
                            ORDER BY `date_created` DESC
                            LIMIT 1
                            ";
                            $property_file_sql = mysql_query($property_file_sql_str);

                            $prop_link_str = null;
                            if( mysql_num_rows($property_file_sql) > 0 ){

                                $property_file_row = mysql_fetch_object($property_file_sql);
                                $prop_link = "{$crm_ci_link}{$property_file_row->path}{$property_file_row->filename}";
                                $prop_link_str = "<a target='_blank' href='{$prop_link}' style='margin-left: 2px;'>(Click Here)</a>";

                            }                            

                            echo "<td>{$wo_txt} {$prop_link_str}</td>";
                            echo "<td colspan='2'><input name='work_order' value='{$row['work_order']}' class='addinput vw-jb-inpt'></td>";



                            echo "</tr>";

                            $dk_sql = mysql_query("
				SELECT `door_knock`
				FROM `jobs`
				WHERE `id` = '{$_GET['id']}'
			");
                            $dk = mysql_fetch_array($dk_sql);

                            // time of the day
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";
                            echo "<td>
			<div style='float: left;margin-right: 9px;'>Time of Day</div>
			<div>";

                            // get agency allow door knock option
                            // get agency hours
                            $adk_sql = mysql_query("
				SELECT `allow_dk`
				FROM `agency`
				WHERE `agency_id` = {$row[24]}
			");
                            $adk = mysql_fetch_array($adk_sql);
                            if ($adk['allow_dk'] == 1 && $job_row['no_dk'] == 0) {
                                echo "<input type='checkbox' name='door_knock' id='door_knock' value='1' class='addinput vw-jb-inpt' style='width: auto !important; height: auto; margin-right: 5px; margin-left: 8px;' " . (($dk['door_knock'] == 1) ? "checked='checked'" : '') . " />
				<div>Door Knock/ Lock Box</div>";
                            } else {
                                echo "<span style='color:red; margin-left: 30px;'>NO DKs ALLOWED</span>";
                            }

                            echo "</div>
			</td>";

                            echo "<td>";
                            echo "<input type='text' name='timeofday' id='timeofday' value='$row[14]' class='addinput vw-jb-inpt " . ((in_array("time", $el)) ? 'jerr_hl' : '') . "'>";
                            echo "<input type='hidden' name='timeofday_orig' id='timeofday_orig' value='$row[14]' />";
                            echo "</td>";



                            // house alarm code
                            echo "<td>House Alarm Code</td>";
                            echo "<td colspan='2'><input name='alarm_code' value='{$row['alarm_code']}' class='addinput vw-jb-inpt'></td>";

                            echo "</tr>";
                            ?>

                            <!-- Call before -->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>
                                    Call Before
                                    <input type='radio' name='call_before' id='call_before_yes' value='1' <?php echo ($row['call_before'] == 1) ? 'checked' : ''; ?>>Yes
                                    <input type='radio' name='call_before' id='call_before_no' value='0' <?php echo ($row['call_before'] == 0) ? 'checked' : ''; ?>>No
                                </td>
                                <td>
                                    <input type='text' maxlength="6" name='call_before_txt' id='call_before_txt' class='tenantinput addinput jred_border_higlight call_before_txt' style='margin-left: 0; width: 220px; <?php echo ($row['call_before'] != '') ? 'display:block' : 'display:none'; ?>;' value="<?php echo $row['call_before_txt']; ?>" />
                                </td>
                                <td>
                                    <!--Show as PAID-->
                                </td>
                                <td colspan='2'>
                                        <!--<input type="checkbox" name="show_as_paid" value="1" <?php echo ($row['show_as_paid'] == 1) ? 'checked="checked"' : ''; ?> />-->
                                </td>
                            </tr>

                            <!-- Preferred Time -->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>
                                    <span class="preferred_time_elem <?php echo ($row['preferred_time'] != '') ? 'showIt' : ''; ?>">Preferred Time</span>
                                </td>
                                <td>
                                    <input name='preferred_time' maxlength="20" id='preferred_time' class='addinput vw-jb-inpt green_border preferred_time_elem <?php echo ($row['preferred_time'] != '') ? 'showIt' : ''; ?>' style='margin-bottom: 11px; margin-right: 10px;' value='<?php echo $row['preferred_time']; ?>' />
                                    <div style="clear:both;"></div>
                                    <div class="preferred_time_elem" style="margin-bottom: 8px; <?php echo ($row['out_of_tech_hours'] == 1) ? 'display:block;' : '' ?>">
                                        Outside of Tech Hours (7-3) <input name="out_of_tech_hours" id="out_of_tech_hours" class="out_of_tech_hours" value="1" type="checkbox" <?php echo ($row['out_of_tech_hours'] == 1) ? 'checked="checked"' : ''; ?> / >
                                    </div>
                                    <div class="preferred_time_elem timestamp_style" style="margin-bottom: 8px; <?php echo ($row['out_of_tech_hours'] == 1) ? 'display:block;' : '' ?>">
                                        <?php echo ( $crm->isDateNotEmpty($job_row['preferred_time_ts']) ) ? date("d/m/Y H:i", strtotime($job_row['preferred_time_ts'])) : null; ?>
                                    </div>
                                    <button type="button" id="btn_preferred_time" class="blue-btn submitbtnImg" <?php echo ($row['preferred_time'] != '') ? 'style="display:none;"' : ''; ?>>
                                        <img class="inner_icon" src="images/button_icons/like-button.png">
                                        <span class="inner_icon_span">Preferred Time</span>
                                    </button>
                                    <button type="button" id="btn_update_pref_time" class="blue-btn submitbtnImg preferred_time_elem <?php echo ($row['preferred_time'] != '') ? 'showIt' : ''; ?>">
                                        <img class="inner_icon" src="images/button_icons/save-button.png" />
                                        Update
                                    </button>
                                </td>
                                <td>Property Vacant</td>
                                <td colspan='2'>
                                    <input type="checkbox" name="prop_vac" id="prop_vac" class="prop_vac" value="1" <?php echo ($row['property_vacant'] == 1) ? 'checked="checked"' : ''; ?> />
                                    <input type="hidden" name="prop_vac_orig" id="prop_vac_orig" class="prop_vac_orig" value="<?php echo $row['property_vacant']; ?>" />
                                </td>
                            </tr>

                            <!-- Run Sheet Notes -->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>
                                    Run Sheet Notes
                                    <?php
                                    // get job entry notice marker
                                    $jen_sql = mysql_query("
						SELECT `job_entry_notice`
						FROM `jobs`
						WHERE `id` ={$job_id}
					");
                                    $jen = mysql_fetch_array($jen_sql);
                                    ?>
                                    <input type="checkbox" name="job_entry_notice" id="job_entry_notice" class="job_entry_notice" <?php echo ($jen['job_entry_notice'] == 1) ? 'checked="checked"' : ''; ?> value="1" />
                                    <label id="job_entry_notice_lbl">Entry Notice</label>
                                    <input type="checkbox" name="job_priority" id="job_priority" class="job_priority" <?php echo ( $row['job_priority'] == 1 ) ? 'checked="checked"' : ''; ?> value="1" />
                                    <label id="job_priority_lbl">Priority</label>
                                </td>
                                <?php
                                $tn_sql = mysql_query("
						SELECT `tech_notes`
						FROM `jobs`
						WHERE `id` = '{$_GET['id']}'
					");
                                $tn = mysql_fetch_array($tn_sql);
                                ?>
                                <td><input type='text' id="tech_notes" maxlength='15' value="<?php echo $tn['tech_notes']; ?>" name='tech_notes' class='tenantinput addinput' style='margin-left: 0; width: 220px;' /><img id="tech_notes_check" style="display:none;" src="/images/check_icon2.png" /></td>



                                <?php
                                // 	Job Not Completed Due to
                                $j_sql = mysql_query("
				SELECT j.`job_reason_id`, jr.`name`
				FROM `jobs` AS j
				LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
				WHERE j.`id` ={$job_id}
			");
                                $j = mysql_fetch_array($j_sql);

                                echo "<td>Job Not Completed Due to:</td>";

                                /*
                                  echo "<td colspan='2'>";

                                  $job_reason_red_border = ($j['job_reason_id']!=0)?"style='margin-left: 0; width: 220px; border: 1px solid #b4151b;box-shadow: 0 0 2px #b4151b inset;'":"";
                                  echo "<input type='text' readonly='readonly' {$job_reason_red_border} class='addinput vw-jb-inpt' value='{$j['name']}' />";

                                  echo "</td>";
                                 */
                                ?>

                                <!-- BY GHERX -->
                                <?php
                                /*
                                14 - No Time to Complete
                                25 - Staff Sick
                                26 - Tenant Cancelled
                                30 - Stock
                                31 - Car Issues
                                */
                                $jr_sql = mysql_query("
                                SELECT *
                                FROM `job_reason`
                                WHERE `job_reason_id` IN(14, 25, 26, 30, 31, 34)
                                ORDER BY `name`
				                ");

                                //sel current job by id
                                $gherx_job_sql = mysql_query("
                                    SELECT *
                                    FROM `jobs`
                                    WHERE `id` ={$_GET['id']}
                                ");

                                $gherx_j = mysql_fetch_array($gherx_job_sql);
                                ?>
                                <td colspan="2">
                                    <select id="mark_as" style="margin-right: 9px;">
                                        <option value="">----</option>
                                        <?php
                                        while ($jr = mysql_fetch_array($jr_sql)) {                               
                                        ?>
                                            <option value="<?php echo $jr['job_reason_id']; ?>" <?php echo ($j['job_reason_id'] == $jr['job_reason_id']) ? 'selected="selected"' : ''; ?>><?php echo $jr['name']; ?></option>
                                        <?php                                    
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" id="orig_job_reason" value="<?php echo $j['job_reason_id']; ?>" />
                                    <button type="button" class="submitbtnImg" id="btn_mark">Mark</button>
                                </td>

                                <!-- BY GHERX END -->

                            </tr>



                            <?php
                            // Key Acces
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";

                            $kaa_sql = mysql_query("
				SELECT `key_allowed`
				FROM `agency`
				WHERE `agency_id` = {$row[24]}
			");
                            $kaa = mysql_fetch_array($kaa_sql);


                            $nk_sql = mysql_query("
				SELECT `no_keys`
				FROM `property`
				WHERE `property_id` = {$property_id}
			");
                            $nk = mysql_fetch_array($nk_sql);

                            //echo "no keys: {$nk['no_keys']}";


                            if ($kaa['key_allowed'] == 1 && $nk['no_keys'] != 1) {

                                echo "<td>
						Key Access
						<input type='radio' id='key_access_required_yes' name='key_access_required' value='1' " . ($row['key_access_required'] ? " checked " : "") . ">Yes&nbsp;&nbsp;<input type='radio' name='key_access_required' id='key_access_required_no' value='0' " . (!$row['key_access_required'] ? " checked " : "") . ">No
						<input type='hidden' name='current_key_access_required' value='" . $row['key_access_required'] . "' />";

                                if ($row['key_access_required'] == 1) {
                                    echo "<span style='margin-left: 15px;' id='authorised_by_span'>Authorised By</span>";
                                }

                                echo "</td>";

                                // key email required
                                $ker_sql = mysql_query("
							SELECT `key_email_req`
							FROM `agency`
							WHERE `agency_id` = {$row[24]}
						");
                                $ker = mysql_fetch_array($ker_sql);

                                // key access details
                                $kad_sql = mysql_query("
							SELECT `key_access_details`
							FROM `jobs`
							WHERE `id` = {$_GET['id']}
						");
                                $kad = mysql_fetch_array($kad_sql);



                                echo "<td>
						<input type='text' value='{$kad['key_access_details']}' name='key_access_details' id='key_access_details' class='tenantinput addinput' style='margin-left: 0; width: 220px; " . (($row['key_access_required'] == 1) ? 'border: 1px solid #b4151b;box-shadow: 0 0 2px #b4151b inset;' : 'display:none;') . "' />";

                                if ($ker['key_email_req'] == 1) {
                                    $mailto_subject = "ACTION REQUIRED $row[9] $row[10] $row[11]";


                                    $tenants_names_arr = [];

                                    $pt_params = array(
                                        'property_id' => $job_details['property_id'],
                                        'active' => 1
                                    );
                                    $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                                    while ($pt_row = mysql_fetch_array($pt_sql)) {

                                        if ($pt_row['tenant_email'] != '' && $pt_row['tenant_firstname'] != "") {
                                            $tenants_names_arr[] = $pt_row['tenant_firstname'];
                                        }
                                    }


                                    $tenant_str_imp = implode(", ", $tenants_names_arr); // separate tenant names with a comma
                                    $last_comma_pos = strrpos($tenant_str_imp, ","); // find the last comma(,) position
                                    $tenants_str = substr_replace($tenant_str_imp, ' &', $last_comma_pos, 1); // replace comma with ampersand(&)



                                    $cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
                                    $cntry = mysql_fetch_array($cntry_sql);

// IMPORTANT: this needs to have no space on the left
                                    $mailto_email_to = "Hi {$tenants_str}\n\n";

                                    $mailto_email_body = "We confirm our conversation granting us permission to collect keys from {$agency_name} in order to service the smoke alarms at {$row[9]} {$row[10]} {$row[11]}\n
Would you kindly press 'REPLY ALL' to this email with the word 'APPROVED'.\n
{$agency_name} has also received a copy of this email\n\n";

                                    $mailto_email_from = "Thanks\n{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}\nSATS - Smoke Alarm Testing Services \n{$cntry['tenant_number']} {$cntry['web']}
";

                                    $mailto_body = $mailto_email_to . $mailto_email_body . $mailto_email_from;

                                    // agency emails
                                    $ae_sql = mysql_query("
								SELECT `agency_emails`
								FROM `agency`
								WHERE `agency_id` = {$row[24]}
							");
                                    $ae = mysql_fetch_array($ae_sql);
                                    $temp = explode("\n", trim($ae['agency_emails']));
                                    $mailto_cc = "";
                                    foreach ($temp as $val) {
                                        $val2 = preg_replace('/\s+/', '', $val);
                                        if (filter_var($val2, FILTER_VALIDATE_EMAIL)) {
                                            $mailto_cc .= ", {$val2}";
                                        }
                                    }

                                    $mailto_cc2 = substr($mailto_cc, 2);


                                    $mailto_to_arr = [];
                                    $mailto_to_str = '';

                                    $pt_params = array(
                                        'property_id' => $job_details['property_id'],
                                        'active' => 1
                                    );
                                    $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                                    while ($pt_row = mysql_fetch_array($pt_sql)) {

                                        if ($pt_row['tenant_email'] != "" && filter_var(trim($pt_row['tenant_email']), FILTER_VALIDATE_EMAIL)) {
                                            $mailto_to_arr[] = $pt_row['tenant_email'];
                                        }
                                    }
                                    $mailto_to_str = implode(',', $mailto_to_arr);

                                    // Hide both if EN is ticked
                                    if ($jen['job_entry_notice'] != 1) {


                                        echo "<a class='mailto_div' id='mailto_tenants_disabled' " . (($row['key_access_required'] != 1) ? "style='display:none;'" : "") . " href='send_email_template.php?job_id={$job_id}'>
									<img src='/images/email_button_green.png' class='mailto_icon' style='height: 29px; cursor: pointer;' />
								</a>

								<div style='display:none;'>
									<div id='popup_email_template'>
										<textarea id='maito_textarea' class='addtextarea' style='float: none; width: 600px; height:248px;margin: 17px;padding:10px;'>{$mailto_body}</textarea>
										<div><button type='button' id='btn_mailto_send' class='blue-btn submitbtnImg '>Send Email</button></div>
									</div>
								</div>
								";
                                        if ($row['key_access_required'] == 1) {
                                            echo "<span id='must_email_tenant_div' style='bottom: 10px; color: red; position: relative;'>MUST EMAIL TENANT</span>";
                                        }
                                        echo "<img id='mailto_sent_icon' src='/images/check_icon2.png' style='display:none; margin-left: 10px;' />";
                                    }
                                }


                                echo "</td>";
                            } else if ($nk['no_keys'] == 1) {
                                echo "<td>Key Access</td><td><p style='color:red;'>NO KEYS</p></td>";
                            } else {
                                echo "<td>Key Access</td><td><p style='color:red;'>NO KEY ACCESS ALLOWED</p></td>";
                            }



                            //echo "<td>dB Reading</td>";
                            //echo "<td>".($row['ts_db_reading'] ? '<span style=\'color: red;\'>Yes</span>': '<span style=\'color: green;\'>No</span>')."</td>";
                            // get agency hours
                            $ah_sql = mysql_query("
				SELECT `agency_hours`
				FROM `agency`
				WHERE `agency_id` = {$row[24]}
			");
                            $ah = mysql_fetch_array($ah_sql);

                            echo "<td>Agency Hours</td>";
                            echo "<td colspan='2'><input name='agency_hours' class='addinput vw-jb-inpt' value='{$ah['agency_hours']}' readonly='readonly' /></td>";
                            ?>




                            <?php
                            echo "</tr>\n";
                            ?>


                            <?php
                            // PMe
                            if( $job_row['api_prop_id'] != '' && $agency_id > 0  && $job_row['api'] == 1){

                                $pme_key_number = null;

                                // get pme property pm
                                $pme_get_pm_params = array(
                                    'prop_id' =>  $job_row['api_prop_id'],
                                    'agency_id' => $agency_id
                                );

                                $pme_get_pm_params_json = $agency_api->get_property_pme($pme_get_pm_params);
                                $pme_get_pm_params_dec = json_decode($pme_get_pm_params_json);
                                //print_r($pme_get_pm_params_dec);

                                if( $pme_get_pm_params_dec->KeyNumber != '' ){
                                    $pme_key_number =  "{$pme_get_pm_params_dec->KeyNumber} (PMe)";
                                }							

                            }						 
                            ?>
                            <!--- Key Number --->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>
                                    <span style="bottom: 7px; position: relative;">Key Number: <?php echo $pme_key_number; ?></span>
                                    <?php
                                    // check if key access already rub today
                                    $ka_cron_sql = mysql_query("
						SELECT *
						FROM  `cron_log`
						WHERE  `type_id` =3
						AND CAST(  `started` AS DATE ) =  '" . date('Y-m-d') . "'
						AND `country_id` ={$_SESSION['country_default']}
					");

                                    if (mysql_num_rows($ka_cron_sql) > 0) {

                                        $mailto_subject2 = 'Key Access Required Addition';

                                        //echo $agency_emails = $row['agency_emails'];

                                        $paddress = "{$row[9]} {$row[10]} {$row[11]}";

                                        $agency_emails = str_replace("\n", ", ", $row['agency_emails']);
                                        ;

                                        //echo "Agency emails: {$agency_emails}";

                                        $mailto_body2 = "
							Good%20afternoon,
							%0D%0A
							%0D%0A
							I%20have%20just%20spoken%20to%20the%20tenant%20{$row['tenant_firstname1']}%20and%20they%20have%20ok'd%20key%20access%20for%20tomorrow%20for%20{$paddress}.
							%0D%0A
							%0D%0A
							As%20our%20automatic%20key%20email%20has%20already%20been%20sent%20I%20wanted%20to%20inform%20you%20of%20the%20addition.
							%0D%0A
							%0D%0A
							Thanks
							%0D%0A
							{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}
							%0D%0A
							SATS%20â€“%20Smoke%20Alarm%20Testing%20Services
						";

                                        echo '
							<a class="mailto_div"  href="mailto:' . $agency_emails . '?Subject=' . rawurlencode($mailto_subject2) . '&body=' . $mailto_body2 . '" style="position: relative; left: 9px; top: 15px; ' . (($row['key_access_required'] != 1) ? " display:none;" : "") . '">
								<img style="height: 29px; cursor: pointer; margin-left: 150px; position: relative; bottom: 13px;" class="email_icon" src="/images/email_button_green.png">
							</a>
						';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <input type='text' maxlength='10' value="<?php echo $row['key_number']; ?>" name='key_number' class='tenantinput addinput' style='margin-left: 0; width: 220px;' />
                                </td>



                                <?php
                                // Job Price
                                echo "<td>Job Price</td>";
                                echo "<td colspan='2'>";
                                echo "
			<input name='job_price' value='$row[32]' class='addinput hdn-jp-prc txtfld_job_price' style='display:none;'>
			<input type='hidden' class='addinput hdn-jp-prc' name='orig_price' id='orig_price' value='$row[32]'>";
                                //echo "<span class='lbl_job_price'>$row[32]</span>
                                //<a href='#' id='pricechange' class='submitbtnImg btn-chng-price' style='background-color: #".$serv_color.";color:#ffffff'>Change Price</a>";
                                // job price, the fuck is row[32]?
                                $job_price = $row[32];

                                // check if agency is excluded                                
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
                                
                                $pc_excluded = false;

                                if( mysql_num_rows($piea_sql) > 0 ){ // original price edit

                                    echo "<a href='javascript:void(0);' id='edit_price_link'>$<span class='lbl_job_price' id='lbl_job_price'>{$job_price}</span></a>";
                                    $pc_excluded = true;

                                }else{ // price increase

                                    echo "<a href='#job_price_variation_fb' class='fancybox' id='job_price_variation_fb_link'>$<span class='lbl_job_price' id='lbl_job_price'>{$job_price}</span></a>";
                                    $pc_excluded = false;                                    

                                }                                



                                // get new alarm
                                $alarm_tot_price = 0;
                                $a_sql = mysql_query("
                                    SELECT *
                                    FROM `alarm`
                                    WHERE `job_id`  = {$job_id}
                                    AND `new` = 1
                                    AND `ts_discarded` = 0
                                ");
                                while ($a = mysql_fetch_array($a_sql)) {
                                    $alarm_tot_price += $a['alarm_price'];
                                }                     
                                
                                //safety_switch prices
                                $ss_price_sql = mysql_query("
                                    SELECT 
                                        ss.`new`,
                                        ss_stock.`pole`,
                                        ss_stock.`sell_price`,
                                        ss_reason.`reason`
                                    FROM `safety_switch` AS ss
                                    LEFT JOIN `safety_switch_stock` AS ss_stock ON ss.`ss_stock_id` = ss_stock.`ss_stock_id`
                                    LEFT JOIN `safety_switch_reason` AS ss_reason ON ss.`ss_res_id` = ss_reason.`ss_res_id`
                                    WHERE ss.`job_id` = {$job_id}
                                    AND ss.`new` = 1
                                    AND ss.`discarded` = 0
                                ");

                                $ss_new_price = 0;
                                while ($ss_price_row = mysql_fetch_array($ss_price_sql)) {
                                    $ss_new_price += $ss_price_row['sell_price'];
                                }

                                $p_n_a_total = ($job_price + $alarm_tot_price + $ss_new_price);
                                $final_job_price_total = $p_n_a_total;

                                

                                ?>
                            <span>+</span>
                            <span>$<?= number_format($alarm_tot_price, 2); ?> (alarms)</span>
                            <span>+ $<?= number_format($ss_new_price, 2) ?> (safety_switch)</span>

                            <?php
                            // get job variation
                            $jv_sql = mysql_query("
                            SELECT 
                                `id`,
                                `amount`,
                                `type`,
                                `reason`
                            FROM `job_variation`
                            WHERE `job_id` = {$job_id}                    
                            AND `active` = 1
                            ");
                            $jv_row = mysql_fetch_object($jv_sql);

                            if( mysql_num_rows($jv_sql) > 0 ){

                                if( $jv_row->type == 1 ){ // discount
                                    $final_job_price_total = $p_n_a_total-$jv_row->amount;
                                    $math_operation = '-';
                                }else{ // surcharge
                                    $final_job_price_total = $p_n_a_total+$jv_row->amount;
                                    $math_operation = '+';
                                }
                                                                                    
                            ?>
                                <span><?php echo $math_operation; ?></span>
                                <span>$<?php echo number_format($jv_row->amount, 2); ?> (<?php echo ( $jv_row->type == 1 )?'discount':'surcharge'; ?>)</span>
                            <?php
                            }
                            ?>
                            <span> = <b>$<?php echo number_format($final_job_price_total, 2); ?></b></span>

                            <?php
                            if( $pc_excluded == false ){

                                /*
                                $price_var_params = array(
                                'service_type' => $service,
                                'property_id' => $property_id
                                );
                                $price_var_arr = $crm->get_property_price_variation($price_var_params);   
                                */
                                
                                
                                $price_var_params = array(
                                'service_type' => $service,
                                'property_id' => $property_id,
                                'job_id' => $job_id
                                );
                                $price_var_arr = $crm->job_price_breakdown($price_var_params);   
                                
                                echo "<br />";
                                echo "Price breakdown: {$price_var_arr['price_breakdown_text']}";

                            }
                            ?>


                            <div id="change_price_div" class="change_price_div" style="display:none; margin-bottom: 17px;">
                                <table style="margin: 10px 0;" id="vpdlatest">
                                    <tbody>
                                        <tr style="border: medium none !important;">
                                            <td>Price:</td>
                                            <td>
                                                <span class="fllefdl">$</span><input type="text" id="job_price" style="display: inline-block;float: none;" class="tenantinput addinput price_field" value="<?php echo $row[32]; ?>" />
                                            </td>
                                        </tr>
                                        <tr style="border: none;">
                                            <td>Reason:</td>
                                            <td>
                                                <select id="price_reason" name="price_reason" class="addinput price_reason">
                                                    <option value=""></option>
                                                    <option value="FOC" <?php echo ($row['price_reason'] == 'FOC') ? 'selected="selected"' : ''; ?>>FOC</option>
                                                    <option value="Price match" <?php echo ($row['price_reason'] == 'Price match') ? 'selected="selected"' : ''; ?>>Price match</option>
                                                    <option value="Multiple properties" <?php echo ($row['price_reason'] == 'Multiple properties') ? 'selected="selected"' : ''; ?>>Multiple properties</option>
                                                    <option value="Agents Property" <?php echo ($row['price_reason'] == 'Agents Property') ? 'selected="selected"' : ''; ?>>Agents Property</option>
                                                    <option value="Other" <?php echo ($row['price_reason'] == 'Other') ? 'selected="selected"' : ''; ?>>Other</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr style="border: none !important;">
                                            <td>Details:</td>
                                            <td>
                                                <input type="text" id="price_detail" name="price_detail" class="proptenantinput tenantinput addinput price_detail" value="<?php echo $row['price_detail']; ?>">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php
                                // can edit price?
                                $staff_perm_sql_str = "
                                SELECT COUNT(`id`) AS sp_count
                                FROM `staff_permissions`
                                WHERE `staff_id` = {$_SESSION['USER_DETAILS']['StaffID']}
                                AND `has_permission_on` = 3
                                ";
                                $staff_perm_sql = mysql_query($staff_perm_sql_str);
                                $staff_perm_row = mysql_fetch_object($staff_perm_sql);
                                $can_edit_price = ( $staff_perm_row->sp_count > 0 )?true:false;

                                // staff has permission to perform actions
                                if( 
                                    ( $job_row['jstatus'] != 'Merged Certificates' && $job_row['jstatus'] != 'Completed' ) ||
                                    ( ( $job_row['jstatus'] == 'Merged Certificates' || $job_row['jstatus'] == 'Completed' ) && $can_edit_price == true )
                                ){ ?>

                                    <button type="button" class="blue-btn submitbtnImg colorwhite btn_update_price">
                                        <img class="inner_icon" src="images/button_icons/save-button.png" />
                                        Update Price
                                    </button>

                                    <button type="button" class="blue-btn submitbtnImg colorwhite btn_update_all_price">
                                        Update Job/Service Price
                                    </button>

                                <?php
                                }
                                ?>                                
                            </div>

                            <?php
                            echo "</td>";
                            ?>




                            </tr>



                            <?php
                            // Decide which one is checked
                            if ($row[5] == '1') {
                                $renewtruechecked = "selected";
                            } else {
                                $renewfalsechecked = "selected";
                            }

                            $nojob = "";
                            $cot = "";
                            $ym = "";
                            $yt = "";

                            // switch($jobsel) {
                            // case "": $nojob="selected"; break;
                            // case "Change of Tenancy": $cot="selected"; break;
                            // case "Yearly Maintenance": $ym="selected"; break;
                            // // case "Yearly Test": $yt="selected"; break;
                            // case "Once-off": $of="selected"; break;
                            // case "Fix or Replace": $fr="selected"; break;
                            // case "240v Rebook": $rb="selected"; break;
                            // }
                            ?>

                            <?php
                            $available_tenants_arr = [];

                            $pt_params = array(
                                'property_id' => $job_details['property_id'],
                                'active' => 1
                            );
                            $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                            while ($pt_row = mysql_fetch_array($pt_sql)) {

                                if ($pt_row['tenant_firstname'] != '') {
                                    $available_tenants_arr[] = $pt_row['tenant_firstname'];
                                }
                            }
                            ?>
                            <!--- Booked With --->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>Booked With</td>
                                <td  class="vjd-tech">
                                    <select id="booked_with" name="booked_with" <?php echo (in_array("booked with", $el)) ? 'class="jerr_hl"' : ''; ?> >
                                        <option value="">-- Select --</option>
                                        <?php foreach ($available_tenants_arr as $av_ten) { ?>
                                            <option value="<?php echo $av_ten; ?>" <?php echo ( $row['booked_with'] == $av_ten ) ? 'selected="selected"' : ''; ?>><?php echo $av_ten; ?></option>
                                            <?php
                                        }
                                        ?>
                                        <option value="Agent" <?php echo ( $row['booked_with'] == 'Agent' ) ? 'selected="selected"' : ''; ?>>Agent</option>
                                    </select>
                                    <input type="hidden" name="booked_with_orig" value="<?php echo $row['booked_with']; ?>" />
                                </td>


                                <?php
                                echo "<td>Urgent/ Outside of scope</td>";
                                echo "<td colspan='2'>
				<input type='checkbox' name='urgent_job' id='urgent_job' class='urgent_job' value='1'";
                                echo ($row['urgent_job'] == 1) ? " checked='checked'" : '';
                                echo "/>
				<input type='text' style='width: 195px;' name='urgent_job_reason' id='urgent_job_reason' class='add_input urgent_job_reason' value='" . $row['urgent_job_reason'] . "' />
			  </td>";
                                ?>



                            </tr>

                            <?php
                            // Technician
                            echo "<tr class='tr_border_notopnBottom tr_rightBorder'>";
                            echo "<td>Technician</td>";
                            echo "<td class='vjd-tech'>";


                            // grab the current user
                            $logedInUser = $_SESSION['USER_DETAILS']['StaffID'];

                            if( $job_row['assigned_tech'] > 0 && ( is_numeric($job_row['tech_active']) && $job_row['tech_active'] == 0 ) ){ // tech is inactive
                                $only_active_users_filter = null;
                            }else{ // default
                                $only_active_users_filter = ( $job_row['jstatus'] != 'Completed' ) ? 'AND sa.`Deleted` = 0 AND sa.`active` = 1' : null;
                            }
                            

                            //GRAB ALL TECHS
                            //$result2 = mysql_query ("SELECT id, first_name, last_name, active, electrician FROM techs WHERE id > 1  ORDER BY first_name ASC", $connection);
                            $sa_sql = mysql_query("
				SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
				FROM `staff_accounts` AS sa
				LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
				WHERE ca.`country_id` ={$_SESSION['country_default']}
				AND sa.`ClassID` = 6
				{$only_active_users_filter}
				ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
			");

                            if ($job_row['assigned_tech'] != '') {
                                $sel_tech = $job_row['assigned_tech'];
                            } else if ($job_row['assigned_tech'] == '' && $_GET['tr_tech_id'] != '') {
                                $sel_tech = mysql_real_escape_string($_GET['tr_tech_id']);
                            }
                            ?>
                            <select id="techid" name="techid" class="<?php echo (in_array("tech", $el)) ? 'jerr_hl' : null; ?>">
                                <option value="">--- Select ---</option>
                                <?php while ($sa_row = mysql_fetch_array($sa_sql)) { ?>
                                    <option
                                        value="<?php echo $sa_row['StaffID']; ?>"
                                        <?php echo ( $sa_row['StaffID'] == $sel_tech ) ? 'selected="selected"' : null; ?>
                                        <?php echo ( $sa_row['StaffID'] == 1 || $sa_row['StaffID'] == 2 ) ? 'style="color:red;"' : null; ?>
                                        data-is_electrian="<?php echo $sa_row['is_electrician']; ?>"
                                        >
                                            <?php
                                            echo $crm->formatStaffName($sa_row['FirstName'], $sa_row['LastName']) .
                                            ( ( $sa_row['is_electrician'] == 1 ) ? ' [E]' : null ) .
                                            ( ( $sa_row['sa_active'] == 0 ) ? ' (Inactive)' : null );
                                            ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>


                            <?php                            
                            echo "<input type='hidden' name='need_elec' id='need_elec' value='" . $need_elec . "' />";
                            echo "<input type='hidden' name='techid_orig' id='techid_orig' value='{$job_row['assigned_tech']}' />";
                            echo "<input type='hidden' name='electrician_only' id='electrician_only' value='{$electrician_only}' />";

                            if ($need_elec) {
                                echo "<br />*REQUIRES ELECTRICIAN*";
                            }
                            echo "</td>\n";
                            ?>




                            <td>Distance to agency</td>
                            <td colspan='2'>
                                <span id="distance_to_agency_span"></span>
                                <button class="blue-btn submitbtnImg" id="btn_check_distance_to_agency" type="button">
                                    <img class="inner_icon" src="images/button_icons/distance.png"/>
                                    Check Distance to Agency
                                </button>

                                <?php //if( $job_details['state']=='QLD' ){ ?>
                                <a href="/finance_form.php?job_id=<?php echo $job_id; ?>">
                                    <button class="blue-btn submitbtnImg" id="btn_create_finance" type="button">
                                        <img class="inner_icon" src="images/button_icons/dollar_icon.png"/>
                                        Create Finance
                                    </button>
                                </a>
                                <?php 
                                if ($_SESSION['country_default']==1) { 
                                
                                    if( IS_PRODUCTION ==  1 ){ // LIVE
                                        $paybyweb_link = 'https://paybyweb.nab.com.au/SecureBillPayment/start?org_id=3le&bill_name=smokealarm';                        
                                    }else{ // DEV                    
                                        $paybyweb_link = 'https://demo.paybyweb.nab.com.au/SecureBillPayment/start?org_id=3le&bill_name=smokealarm';                        
                                    }
                                    
                                    ?>
                                    <a href="<?php echo $paybyweb_link; ?>" target="_blank">
                                        <button class="blue-btn submitbtnImg" id="btn_create_finance" type="button">
                                            <img class="inner_icon" src="images/button_icons/dollar_icon.png"/>
                                            Payment Portal
                                        </button>
                                    </a>

                                <?php
                                }
                                ?>
                            </td>



                            <?
                            echo "</tr>";

                            //$sel_booked_by = ($row['booked_by']!="")?$row['booked_by']:$_SESSION['USER_DETAILS']['StaffID'];
                            if ($_GET['tr_booked_by'] != "" && $row['booked_by'] == "") {
                                $sel_booked_by = $_GET['tr_booked_by'];
                                $error_border = 1;
                            } else {
                                $sel_booked_by = $row['booked_by'];
                            }
                            //$sel_booked_by = $row['booked_by'];
                            ?>
                            <!--- Booked By --->
                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>Booked By</td>
                                <td class="vjd-tech">
                                    <select name="booked_by" id="booked_by" <?php echo (in_array("booked by", $el)) ? 'class="jerr_hl"' : ''; ?> <?php echo ($error_border == 1) ? 'style="border: 1px solid #b4151b;"' : ''; ?>>
                                        <option value="">-- Select --</option>
                                        <?php
                                        if ($row[2] != "Completed") {
                                            $bb_where = "
								AND sa.deleted =0
								AND sa.active =1
							";
                                        }
                                        $bb_sql = mysql_query("
							SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
							FROM staff_accounts AS sa
							INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
							WHERE ca.`country_id` ={$_SESSION['country_default']}
							{$bb_where}
							ORDER BY sa.`FirstName`
						");
                                        while ($bb = mysql_fetch_array($bb_sql)) {
                                            ?>
                                            <option value="<?php echo $bb['staff_accounts_id']; ?>" <?php echo ($bb['staff_accounts_id'] == $sel_booked_by) ? 'selected="selected"' : ''; ?>><?php echo "{$bb['FirstName']} {$bb['LastName']}" ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" name="booked_by_orig" value="<?php echo $sel_booked_by; ?>" />
                                </td>


                                <td>&nbsp;</td>
                                <td colspan='2'>
                                    <button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg ">
                                        <img class="inner_icon" src="images/button_icons/240v-rebook.png"/>
                                        Create 240v Rebook
                                    </button>
                                    <button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg ">
                                        <img class="inner_icon" src="images/button_icons/rebook.png"/>
                                        Create Rebook
                                    </button><br />
                                    <button type="button" id="btn_move_to_booked" class="submitbtnImg" style="margin-top: 10px;">
                                        <img class="inner_icon" src="images/button_icons/back-to-tech.png"/>
                                        Send Back to Tech
                                    </button>
                                    
                                    <!--
                                    <a target="_blank" id="custom_email_link" href="send_email_template.php?job_id=<?php echo $job_id; ?>">
                                        <button type="button" id="btn_custom_email" class="submitbtnImg green-btn" style="margin-top: 10px;">
                                            <img class="inner_icon" src="images/button_icons/email.png"/>
                                            Send Email Templates
                                        </button>
                                    </a>
                                    -->

                                    <?php                                   
                                    $crm_ci_page = "/email/send";
                                    $crm_ci_page_params = "job_id:{$job_row['jid']}";
                                    $crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page, $crm_ci_page_params);
                                    ?>
                                    <a target="_blank" id="custom_email_link" href="<?php echo $crm_ci_page_url; ?>">
                                        <button type="button" id="btn_custom_email" class="submitbtnImg green-btn" style="margin-top: 10px;">
                                            <img class="inner_icon" src="images/button_icons/email.png"/>
                                            Send Email Templates
                                        </button>
                                    </a>

                                    <?php
                                    //if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){
                                    // $crm_ci_page = "/sms/send/&page_params=job_id:{$job_row['jid']}-data:1";
                                    $crm_ci_page = "/sms/send";
                                    $crm_ci_page_params = "job_id:{$job_row['jid']}";
                                    $crm_ci_page_url = $crm->crm_ci_redirect($crm_ci_page, $crm_ci_page_params);
                                    ?>

                                    <a href="<?php echo $crm_ci_page_url; ?>" target="_bank">
                                        <button type="button" id="btn_custom_email" class="submitbtnImg green-btn" style="margin-top: 10px;">
                                            <img class="inner_icon" src="images/button_icons/email.png"/>
                                            Send SMS Templates
                                        </button>
                                    </a>

                                    <?php
                                    //}
                                    ?>
                                </td>

                            </tr>

                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <?php
                                // get key access details
                                $ndp_sql = mysql_query("
					SELECT `no_dates_provided`
					FROM `jobs`
					WHERE `id` = {$_GET['id']}
				");
                                $ndp = mysql_fetch_array($ndp_sql);
                                ?>
                                <td>Start Date/End Date <input type="checkbox" name="no_dates_provided" value="1" <?php echo ($ndp['no_dates_provided'] == 1) ? 'checked="checked"' : ''; ?> />Dates not Provided</td>
                                <td>
                                    <?php $sd2 = ($row['start_date'] != "" && $row['start_date'] != "1970-01-01" && $row['start_date'] != "0000-00-00") ? date('d/m/Y', strtotime($row['start_date'])) : ''; ?>
                                    <input type="text" class="addinput vw-jb-inpt <?php echo ( $jobsel == 'Lease Renewal' ) ? '' : 'datepicker'; ?> start_date" name="start_date" style="width: 100px; margin-right: 12px;" value="<?php echo $sd2; ?>" <?php echo ( $jobsel == 'Lease Renewal' ) ? 'readonly="readonly"' : ''; ?> />
                                    <?php
                                    /*
                                      $dd_sql = mysql_query("
                                      SELECT `due_date`
                                      FROM `jobs`
                                      WHERE `id` = {$job_id}
                                      ");
                                      $dd = mysql_fetch_array($dd_sql);
                                     */
                                    $dd2 = ($row['due_date'] != "" && $row['due_date'] != "1970-01-01" && $row['due_date'] != "0000-00-00") ? date('d/m/Y', strtotime($row['due_date'])) : '';
                                    ?>
                                    <input type="text" class="addinput vw-jb-inpt datepicker due_date" name="due_date" value="<?php echo $dd2; ?>" style="width: 100px;" />

                                </td>

                                <td>Need Processing (MM or DHA)</td>
                                <td colspan='2'><input type="checkbox" name="dha_need_processing" id="dha_need_processing" class="dha_need_processing" value="1" <?php echo ($row['dha_need_processing'] == 1) ? 'checked="checked"' : ''; ?> /></td>

                            </tr>

                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>To be Printed</td>
                                <td><input type="checkbox" name="to_be_printed" id="to_be_printed" class="to_be_printed" value="1" <?php echo ($row['to_be_printed'] == 1) ? 'checked="checked"' : ''; ?> /></td>
                                <td>Cancelled Date</td>
                                <td colspan='2'><input type="text" readonly="readonly" style="width:71px;" value="<?php echo $crm->isDateNotEmpty($row['cancelled_date']) ? date('d/m/Y', strtotime($row['cancelled_date'])) : null; ?>"  /></td>
                            </tr>

                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>Deleted Date</td>
                                <td><input type="text" readonly="readonly" style="width:71px;" value="<?php echo $crm->isDateNotEmpty($row['deleted_date']) ? date('d/m/Y', strtotime($row['deleted_date'])) : null; ?>"  /></td>
                                <?php
                                if( $job_row['p_state'] == 'QLD' ){ ?>

                                    <td>Approved Alarm</td>
                                    <td colspan='2'>
                                        <select name="preferred_alarm_id">
                                            <option value="">---</option>
                                            <option value="10" <?php echo ( $job_row['preferred_alarm_id'] == 10 )?'selected':null; ?>>Brooks</option>
                                            <option value="14" <?php echo ( $job_row['preferred_alarm_id'] == 14 )?'selected':null; ?>>Cavius</option>
                                            <option value="22" <?php echo ( $job_row['preferred_alarm_id'] == 22 )?'selected':null; ?>>Emerald</option>
                                        </select>
                                    </td>

                                <?php
                                }else{ ?>                                    
                                    <td colspan='3'></td>
                                <?php
                                }
                                ?>                                
                            </tr>



                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>Electrician Only <br><label style="white-space: nowrap; color: red;" class="tr_border_notopnBottom tr_rightBorder"><?php echo ($job_row['electrician_only'] == 1) ?'(Agency Marked as Electrician Only)':null; ?></label> </td>
                                <td>
                                    <input type="checkbox" name="is_eo" id="is_eo" class="is_eo" value="1" <?php echo ($job_row['is_eo'] == 1) ?'checked':null; ?> />
                                </td>
                                <td colspan='3'></td>
                            </tr>





                            <?php
                            // echo "<tr>";
                            // echo "<td valign=top>Tenant 1 Name:</td><td  valign=top>$row[16] $row[17]</td>";
                            // echo "<td valign=top>Tenant 1 Ph:</td><td  valign=top>$row[18]</td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            // echo "<td valign=top>Tenant 1 Email:</td><td  valign=top><a href='mailto:" . $row['tenant_email1'] . "'>" . $row['tenant_email1'] . "</a></td>";
                            // echo "<td valign=top>Tenant 1 Mob:</td><td  valign=top>" . $row['tenant_mob1'] . "</td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            // echo "<td valign=top>Tenant 2 Name:</td><td  valign=top>$row[21] $row[22]</td>";
                            // echo "<td valign=top>Tenant 2 Ph:</td><td  valign=top>$row[23]</td>";
                            // echo "</tr>";
                            // echo "<tr>";
                            // echo "<td valign=top>Tenant 2 Email:</td><td  valign=top><a href='mailto:" . $row['tenant_email2'] . "'>" . $row['tenant_email2'] . "</a></td>";
                            // echo "<td valign=top>Tenant 2 Mob:</td><td  valign=top>" . $row['tenant_mob2'] . "</td>";
                            // echo "</tr>";
                            ?>


                            <tr class='tr_border_notopnBottom tr_rightBorder'>
                                <td>Compliance Notes</td>
                                <td>
                                    <textarea name='not_compliant_notes' class='addtextarea not_compliant_notes' spellcheck="true" maxlength="200"><?php echo strip_tags(trim($job_row['not_compliant_notes'])); ?></textarea>
                                </td>
                                <td>Lockbox Code</td>
                                <td colspan='2'>
                                    <input type='text' name='lockbox_code' class='tenantinput addinput' style='margin-left: 0; width: 220px;' value="<?php echo $lockbox_sql_row->code; ?>" />
                                </td>
                            </tr>


                            <tr style="background-color:#<?php echo $serv_color; ?>">
                                <td class="colorwhite bold">Job Notes FOR Technician</td>
                                <td class="colorwhite bold">Property Notes</td>
                                <td class="colorwhite bold">Repair Notes</td>
                                <td class="colorwhite bold">Agency Specific Notes</td>
                                <td class="colorwhite bold" colspan="1">Job Notes FROM Technician</td>
                            </tr>

                            <?php
                            // get property comments
                            $psql = mysql_query("
                                        SELECT `comments`
                                        FROM `property`
                                        WHERE `property_id` = {$property_id}
                                    ");
                            $p = mysql_fetch_array($psql);
                            // echo "<pre>";
                            // var_dump($row[3]);
                            // var_dump($row['comments']);
                            echo "<tr style='border-right: 1px solid #cccccc;'>";
                            echo "<td><textarea rows=5 name='comments' class='addtextarea vw-jb-tar' style='width: 220px;'>" . strip_tags(trim($row[3])) . "</textarea></td>";
                            echo "<td><textarea rows=5 name='prop_comments' class='addtextarea vw-jb-tar' style='width: 220px;'>" . trim($p['comments']) . "</textarea></td>";
                            echo "<td><textarea rows=5 name='repair_notes' class='addtextarea vw-jb-tar' style='width: 220px;'>" . trim($row['repair_notes']) . "</textarea></td>";

                            // agency specific notes
                            $asn_sql =  mysql_query("
                                            SELECT `agency_specific_notes`
                                            FROM `agency`
                                            WHERE `agency_id` = {$agency_id}
                                        ");
                            $asn = mysql_fetch_array($asn_sql);

                            echo "<td><textarea rows=5 name='agency_specific_notes' class='addtextarea vw-jb-tar' style='width: 220px;'>" . trim($asn['agency_specific_notes']) . "</textarea></td>";
                            echo "<td><textarea rows=5 name='tech_comments' class='addtextarea vw-jb-tar' style='width: 220px;'>" . stripslashes(trim($row['tech_comments'])) . "</textarea></td>";
                            echo "</tr>\n";

                            # Tech Sheet Job Types // Now on property Page
                            // echo "<tr>";
                            // echo "<td valign=top>Tech Sheet Job Type</td>";
                            // echo "<td colspan='3'>";
                            // foreach($tech_sheet_job_types as $i=>$type)
                            // {
                            //     if($i > 0 && $i % 3 == 0)
                            //     {
                            //         $class = 'alarm_job_type clear';
                            //     }
                            //     else
                            //     {
                            //        $class = 'alarm_job_type';
                            //     }
                            //     echo "<label class='" . $class . "'>";
                            //     echo "<input type='checkbox' name='alarm_job_type[]' ";
                            //     # Pre-check if already seleceted
                            //     if(array_key_exists($type['id'], $job_tech_sheet_job_types))
                            //     {
                            //         echo " checked ";
                            //     }
                            //     echo " id='" . $type['html_id'] . "' ";
                            //     echo " value='" . $type['id'] . "'>";
                            //     echo $type['type'];
                            //     echo "</label>";
                            // }
                            // echo "</td>";
                            // echo "</tr>";


                            if (($mc || $cp) == "selected") {
                                echo "<tr style='border-right: 1px solid #cccccc;'>";
                                echo "<td>Invoices and Certificates</td>";

                                // combined pdf
                                $encode_encrypt_job_id = rawurlencode($encrypt_decrypt->encrypt($job_id));
                                $pdf_combine_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_combined/?job_id={$encode_encrypt_job_id}"));
                                //--old--// echo "<td valign=top ><a href='" . URL . "view_combined.php?job_id=$job_id' target='_blank'>View Combined</a></td>";
                                if( $job_row['assigned_tech']!=1 && $job_row['assigned_tech']!=2 ){
                                    echo "<td valign=top ><a href='$pdf_combine_ci_link_view' target='_blank'>View Combined</a></td>";
                                }
                                //echo "<td valign=top colspan='2'><a href='" . URL . "view_combined_new.php?i={$job_id}&m=".md5($agency_id.$job_id)."' target='_blank'>View Combined</a>";
                                // certificate pdf
                                $pdf_certificate_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_certificate/?job_id={$encode_encrypt_job_id}"));
                                //--old--// echo "<td valign=top ><a href='" . URL . "view_certificate.php?job_id=$job_id' target='_blank'>View Certificate</a></td>";
                                if( $job_row['assigned_tech']!=1 && $job_row['assigned_tech']!=2 ){
                                    echo "<td valign=top ><a href='$pdf_certificate_ci_link_view' target='_blank'>View Certificate</a></td>";
                                }
                                //echo "<td valign=top colspan='2'><a href='" . URL . "view_certificate_new.php?i={$job_id}&m=".md5($agency_id.$job_id)."' target='_blank'>View Certificate</a>";
                                // invoice pdf
                                $pdf_invoice_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_job_id}"));
                                //--old--// echo "<td valign=top colspan='2'><a href='" . URL . "view_invoice.php?job_id=$job_id' target='_blank'>View Invoice</a>";
                                echo "<td valign=top><a href='$pdf_invoice_ci_link_view' target='_blank'>View Invoice</a></td>";
                                //echo "<td valign=top colspan='2'><a href='" . URL . "view_invoice_new.php?i={$job_id}&m=".md5($agency_id.$job_id)."' target='_blank'>View Invoice</a>";
                                //if( $job_details['state']=='QLD' && $job_details['qld_new_leg_alarm_num'] > 0 && $job_row['prop_upgraded_to_ic_sa'] != 1 ){
                                //if( $job_details['qld_new_leg_alarm_num'] > 0 ){
                                

                                //new certificate with alarm images
                                ##get safety sqad preferences
                                /*$safety_squad_preference_sql = mysql_query("
                                    SELECT *
                                    FROM agency_preference_selected
                                    WHERE agency_id = {$agency_id}
                                    AND agency_pref_id = 23
                                ");
                                $safety_squad_preference_row = mysql_fetch_array($safety_squad_preference_sql);

                                if( ($safety_squad_preference_row['sel_pref_val']==1 AND mysql_num_rows($safety_squad_preference_sql) > 0) || is_safety_squad($job_row['a_id']) ){
                                    $pdf_certificate_with_images_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_certificate_with_photos/?job_id={$encode_encrypt_job_id}"));
                                    echo "<td valign=top><a href='$pdf_certificate_with_images_ci_link_view' target='_blank'>View Certificate with Images</a></td>";
                                */
                                //new certificate with alarm images end
                                
                                echo "<td valign=top>"; 
                                if( $job_row['p_state'] == 'QLD' ){

                                    $has_brooks_quote = false;
                                    $has_cavius_quote = false;

                                    //quote pdf
                                    // check if 240v RF brooks available on agency alarms
                                    $get_240v_rf_brooks_sql_str = "
                                    SELECT COUNT(`agency_alarm_id`) AS agen_al_count
                                    FROM `agency_alarms`
                                    WHERE `agency_id` = {$agency_id}
                                    AND `alarm_pwr_id` = 10                                
                                    ";
                                    $get_240v_rf_brooks_sql = mysql_query($get_240v_rf_brooks_sql_str);
                                    $get_240v_rf_brooks_row = mysql_fetch_array($get_240v_rf_brooks_sql);

                                    if( $get_240v_rf_brooks_row['agen_al_count'] > 0 ){

                                        $pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_job_id}&qt=brooks"));
                                        echo "
                                            <span style='float: left; margin-right: 15px;'>
                                                <a href='$pdf_quote_ci_link_view' target='_blank'>View Brooks Quote</a>
                                            </span>
                                        ";
                                        $has_brooks_quote = true;

                                    } 


                                    // check if 240v RF cavius available on agency alarms
                                   /* $get_240v_rf_cavius_sql_str = "
                                    SELECT COUNT(`agency_alarm_id`) AS agen_al_count
                                    FROM `agency_alarms`
                                    WHERE `agency_id` = {$agency_id}
                                    AND `alarm_pwr_id` = 14                                
                                    ";
                                    $get_240v_rf_cavius_sql = mysql_query($get_240v_rf_cavius_sql_str);
                                    $get_240v_rf_cavius_row = mysql_fetch_array($get_240v_rf_cavius_sql);

                                    if( $get_240v_rf_cavius_row['agen_al_count'] > 0 ){

                                        $pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_job_id}&qt=cavius"));
                                        echo "
                                            <span style='float: left; margin-right: 15px;'>
                                                <a href='$pdf_quote_ci_link_view' target='_blank'>View Cavius Quote</a>
                                            </span>
                                        ";
                                        $has_cavius_quote = true;

                                    } */ ##disable cavius as per req

                                }  


                                if( $row['state'] == 'QLD' ){

                                    // check if 240v RF emerald available on agency alarms
                                    $get_240vrf_emerald_sql_str = "
                                    SELECT COUNT(`agency_alarm_id`) AS agen_al_count
                                    FROM `agency_alarms`
                                    WHERE `agency_id` = {$agency_id}
                                    AND `alarm_pwr_id` = 22                                
                                    ";
                                    $get_240vrf_emerald_sql = mysql_query($get_240vrf_emerald_sql_str);
                                    $get_240vrf_emerald_row = mysql_fetch_array($get_240vrf_emerald_sql);
    
                                    if( $get_240vrf_emerald_row['agen_al_count'] > 0 ){
    
                                        // emerald pdf
                                        $pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_job_id}&qt=emerald"));                                                         
                                        echo "
                                        <span style='float: left; margin-right: 15px;'>
                                            <a href='$pdf_quote_ci_link_view' target='_blank'>View {$crm->get_quotes_new_name(22)} Quote</a>
                                        </span>
                                        ";
                                        $has_emerald_quote = true;
    
                                    } 

                                }
                                
                                // combined quotes
                                if( $has_brooks_quote == true && $has_cavius_quote == true && $has_emerald_quote == true ){

                                    $pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_job_id}&qt=combined"));
                                    echo "
                                        <span style='float: left; margin-right: 15px;'>
                                            <a href='$pdf_quote_ci_link_view' target='_blank'>View Combined Quote</a>
                                        </span>
                                    ";
                                    $has_cavius_quote = true;

                                }

                                //}else{
                                //echo "<span style='float: right; margin-right: 10%;'>No Quote Available</span>";
                                //}
                                //}
                                echo "</td>
				</tr>";
                            }


                            echo "<tr style='border-right: 1px solid #cccccc;'>";

                            // Can edit completed jobs?
							$staff_perm_sql_str = "
							SELECT COUNT(`id`) AS sp_count
							FROM `staff_permissions`
							WHERE `staff_id` = {$_SESSION['USER_DETAILS']['StaffID']}
							AND `has_permission_on` = 1
							";
							$staff_perm_sql = mysql_query($staff_perm_sql_str);
							$staff_perm_row = mysql_fetch_object($staff_perm_sql);
							$can_edit_completed = ( $staff_perm_row->sp_count > 0 )?true:false;

                            echo "<td align=left colspan='100%'>";

                            if ($job_row['jstatus'] == 'Completed' && $can_edit_completed == false ) {

                                echo "<div class='info'>Completed Jobs can't be Edited</div>";

                            } else {

                                echo "
                                <input type='hidden' value='Update Job Details' class='submitbtnImg' style='background-color:#" . $serv_color . ";color:#ffffff;'>
                                <input type='hidden' name='holiday_rental' id='holiday_rental' class='holiday_rental' value='" . $row['holiday_rental'] . "' />
                                <button type='button' id='btn_update_job_details' name='btn_update_job_details' class='submitbtnImg' style='background-color:#" . $serv_color . ";color:#ffffff; float: left;'>
                                <img class='inner_icon' src='images/button_icons/save-button.png' />
                                Update Job Details
                                </button>";

                                //CI VJD link
                                $vjd_ci_link = $crm->crm_ci_redirect(rawurlencode("/jobs/details/{$job_id}"));
                                echo "<a href='{$vjd_ci_link}' style='float:right'>
                                    <button type='button' class='submitbtnImg blue-btn' style='float: left; margin-left: 13px;'>
                                        <img class='inner_icon' src='images/button_icons/email.png'>
                                        CI VJD
                                    </button>
                                </a>
                                ";
                                //CI VJD link end

                                // delete/restore job
                                if ($job['del_job'] == 1) {
                                    echo "<button class='blue-btn submitbtnImg ' id='btn_restore_job' type='button' style='float: right; margin-left: 13px;'>Restore Job</button>";
                                } else {
                                    echo "
                                    <button type='button' id='btn_del_job_temp' class='submitbtnImg' style='float: right; margin-left: 13px;'>
                                        <img class='inner_icon' src='images/button_icons/cancel-button.png' />
                                        Delete Job
                                    </button>
                                    ";
                                }

                                // move job
                                echo "
                                <button type='button' id='btn_move_job' class='submitbtnImg blue-btn' style='float: right; margin-left: 13px;'>
                                <img class='inner_icon' src='images/button_icons/move.png'/>
                                Move Job
                                </button>
                                <div id='move_job_div' style='display: none;'>
                                <div style='float: left; margin: 7px 0 0 10px;'>Property ID:</div>
                                <input type='text' id='move_job_property_id' class='addinput' style='width: 88px;' />
                                <input type='hidden' id='old_prop_id' value='{$property_id}' />
                                <div id='search_prop_display' style='float: left; margin: 6px 0 0 10px;'></div>
                                <button type='button' id='btn_move' class='blue-btn submitbtnImg' style='float: left; margin-left: 13px; display:none;'>MOVE</button>
                                </div>
                                ";

                                // techsheet
                                if ($job['bundle'] == 1) {
                                    $bndl_sql = getbundleServices($job_id, '', 1);
                                    $bndl = mysql_fetch_array($bndl_sql);
                                    $url = "/view_job_details_tech.php?id={$job_id}&service={$service}&bundle_id={$bndl['bundle_services_id']}";
                                } else {
                                    $url = "/view_job_details_tech.php?id={$job_id}&service={$service}";
                                }
                                
                                
                                // techsheet CI
                                $ts_link_ci = $crm->crm_ci_redirect(rawurlencode("/jobs/tech_sheet/?job_id={$job_id}"));

                                echo "<a href='{$ts_link_ci}'>
                                    <button type='button' class='submitbtnImg blue-btn' style='float: left; margin-left: 13px;'>
                                        <img class='inner_icon' src='images/button_icons/notes-button.png'>
                                        View Tech Sheet
                                    </button>
                                </a>
                                ";

                                echo "
                                <button type='button' id='sync_alarm_btn' class='submitbtnImg blue-btn' style='float: left; margin-left: 13px;'>
                                    <img class='inner_icon' src='images/button_icons/rebook.png'>
                                    Sync Smoke Alarms ONLY
                                </button>
                                ";



                                echo "
                                <button type='button' id='recreate_bundle_services_btn' class='submitbtnImg blue-btn' style='float: left; margin-left: 13px;'>
                                    <img class='inner_icon' src='images/button_icons/rebook.png'>
                                    Recreate Bundle Services
                                </button>
                                ";
                                
                                
                                if ($row[2] == 'Booked') {

                                    echo "
                                    <span >
                                        <button type='button' class='submitbtnImg blue-btn " . ( ( $booked_with_mobile == '' ) ? 'jGreyFadedBtn' : '' ) . "' style='margin-left: 20px;' id='sms_to_conf_book'>
                                            <img class='inner_icon' src='images/button_icons/sms_icon.png'>
                                            SMS to Confirm booking
                                        </button>
                                    </span>
                                    ";

                                }


                            }


                            // upload to API
                            if ($showUploadBtn) {
                                echo "
                                    <button type='button' id='upload_invoice_bill_to_pme_btn' class='submitbtnImg' style='float: left; margin-left: 13px; background-color:#9B30FF;'>
                                        Upload Documents to PMe
                                    </button>
                                ";
                            }
                            if ($showUploadBtnPalace) {

                                echo "
                                    <button type='button' id='upload_invoice_bill_to_palace_btn' class='submitbtnImg' style='float: left; margin-left: 13px; background-color:#9B30FF;'>
                                        Upload Invoice/Bill to Palace
                                    </button>
                                ";

                                if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){

                                    echo "
                                        <button type='button' id='upload_invoice_bill_to_palace_btn_payload_only' class='submitbtnImg' style='float: left; margin-left: 13px; background-color:#9B30FF;'>
                                            Upload Invoice/Bill to Palace -  Payload Only
                                        </button>
                                    ";

                                }                                

                            }

                            // check if connected
                            $cons_prop_sql = mysql_query("
                            SELECT COUNT(`console_prop_id`) AS cp_count
                            FROM `console_properties` 
                            WHERE `crm_prop_id` = {$job_details['property_id']}
                            AND `active` = 1
                            ");
                            $cons_prop_row = mysql_fetch_object($cons_prop_sql);
                            $cp_count = $cons_prop_row->cp_count;

                            // check if invoice/cert already uploaded
                            $ajd_sql = mysql_query("
                            SELECT COUNT(`id`) AS ajd_count
                            FROM `api_job_data` 
                            WHERE `crm_job_id` = {$job_id}     
                            AND ( `api_inv_uploaded` = 1 OR `api_cert_uploaded` = 1 )  
                            ");
                            $ajd_row = mysql_fetch_object($ajd_sql);
                            $ajd_count = $ajd_row->ajd_count;

                            // only show button on connected properties and not-yet uploaded invoice/cert
                            if( 
                                $cp_count > 0 && $ajd_count == 0 && 
                                ( $job_row['jstatus'] == 'Merged Certificates' || $job_row['jstatus'] == 'Completed' )
                             ){

                                echo "
                                <button type='button' id='upload_invoice_bill_to_console_btn' class='submitbtnImg' style='float: left; margin-left: 13px; background-color:#9B30FF;'>
                                    Upload Invoice/Bill to Console
                                </button>
                                ";

                            }                            

                            echo "</td>";
                            echo "</tr>";
                            ?>

                            <style>
                                .jl_div{
                                    float:left;
                                    margin-right: 10px;
                                }
                            </style>
                            <table border=0 cellspacing=1 cellpadding=5 width='100%' class="jcl njcl">
                                <tr valign=middle>
                                    <td colspan="7" style="padding: 0px;">
                                        <table  border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">
                                            <tr class="tgt-ag-bl" style="background-color:#eeeeee;">
                                                <td>

                                                    <div class="jl_div">
                                                        <label for="eventdate">Date</label><br />
                                                        <input type="text" id='joblog-date' style="width: 80px;" name="eventdate" value="<?php echo date('d/m/Y'); ?>" class="addinput datepicker"  />
                                                    </div>



                                <!--<td>
                                        <select id="hour" name="hour">
                                                    <?php
                                                    //for($i; $i <= 12; $i++){
                                                    //	echo '<option value="'.$i.'">'.$i.'</option>';
                                                    //}
                                                    ?>
                                        </select>
                                        :
                                        <select id="minute" name="minute">
                                                    <?php
                                                    //for($t; $t <= 59; $t++){
                                                    //	echo '<option value="'.$t.'">'.$t.'</option>';
                                                    //}
                                                    ?>
                                        </select>
                                        <select id="day" name="day"><option value="am">am</option><option value="pm">pm</option></select>
                                </td>-->

                                                    <div class="jl_div">
                                                        <label for="contact_type">Contact Type</label><br />
                                                        <select name="contact_type" id='joblog-contact_type'>
                                                            <option value="Phone Call">Phone Call</option>
                                                            <option value="E-mail">E-mail</option>
                                                            <option value="SMS Sent">SMS Sent</option>
                                                            <option value="Work Order">Work Order</option>
                                                            <option value="Unavailable">Unavailable</option>
                                                            <option style="color:red;" value="Problematic">Problematic</option>
                                                            <option value="SMS Received">SMS Received</option>
                                                            <option value="Duplicate Property">Duplicate Property</option>
                                                            <option value="Payment Taken">Payment Taken</option>
                                                            <option value="Airtable">Airtable</option>
                                                            <option value="Teams">Teams</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>


                                                    <div class="jl_div" style="width: 50%;">
                                                        <label for="comments">Comment</label><br />
                                                        <input id='joblog-comments' name="joblog-comments" class="addtextarea" />
                                                        <!-- <textarea rows="2" id='joblog-comments' name="joblog-comments" class="addtextarea vw-jb-tar" style="width: 700px; height: 13px;"></textarea> -->
                                                    </div>

                                                    <div class="jl_div">
                                                        <label for="comments">Unavailable</label><br />
                                                        <input type="checkbox" id="unavailable" name="unavailable" style="margin-top: 8px; float: none;" />
                                                        <input type="text" name="unavailable_date" id='unavailable_date' class="addinput datepicker" style="width: 80px; float: none;" />
                                                    </div>

                                                    <div class="jl_div">
                                                        <label for="comments">Important</label><br />
                                                        <input type="checkbox" id="important" name="important" style="margin-top: 8px;" />
                                                    </div>

                                                    <div class="jl_div">
                                                        <button id='add-log' class="submitbtnImg" style="background-color:#<?php echo $serv_color; ?>;color:#ffffff">
                                                            <img class="inner_icon" src="images/button_icons/add-button.png"/>
                                                            Add Event
                                                        </button>
                                                    </div>

                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <?php
                                $Query = "SELECT DATE_FORMAT(j.eventdate,'%d/%m/%Y') AS jl_date,
					j.contact_type,
					j.comments,
					j.log_id,
					s.FirstName,
					s.LastName,
					eventtime,
					j.`important`,
					j.`log_agency_id`,
					j.`auto_process`,
					j.`staff_id`
			FROM job_log j
			LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
			WHERE (j.job_id = $job_id) AND j.deleted = 0
			AND j.`log_type` = 1
			ORDER BY j.eventdate DESC, j.`log_id` DESC";

                                $result = mysql_query($Query, $connection);

                                if (mysql_num_rows($result) != 0) {

                                    $odd = 0;

                                    echo "<tr bgcolor=#{$serv_color}>\n";
                                    echo "<td class='colorwhite bold'>Date</td>\n";
                                    echo "<td class='colorwhite bold'>Time</td>\n";
                                    echo "<td class='colorwhite bold'>Type</td>\n";
                                    echo "<td class='colorwhite bold'>Who</td>\n";
                                    echo "<td colspan=2 class='colorwhite bold'>Comments</td>\n";
                                    echo "<td class='colorwhite bold'>Delete</td>\n";

                                    echo "</tr>\n";


                                    // (3) While there are still rows in the result set,
                                    // fetch the current row into the array $row
                                    while ($row = mysql_fetch_array($result)) {

                                        $odd++;
                                        if (is_odd($odd)) {
                                            echo "<tr " . (($row['important'] == 1) ? 'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"' : 'bgcolor="#FFFFFF"') . ">";
                                        } else {
                                            echo "<tr " . (($row['important'] == 1) ? 'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"' : 'bgcolor="#eeeeee"') . ">";
                                        }

                                        echo "<td width=50>";
                                        echo $row['jl_date'];
                                        echo "</td>\n";

                                        echo "<td>{$row['eventtime']}</td>";

                                        //echo "<td width=50>";
                                        //echo $row[6];
                                        //echo "</td>\n";

                                        echo "<td class='td_log_type'>";
                                        echo $row['contact_type'];
                                        echo "</td>\n";

                                        echo "<td>";
                                        if ($row['log_agency_id'] != "") {
                                            $agency2_sql = mysql_query("
							SELECT *
							FROM `agency`
							WHERE `agency_id` = {$row['log_agency_id']}
						");
                                            $agency2 = mysql_fetch_array($agency2_sql);
                                            $who = $agency2['agency_name'];
                                        } else {
                                            if ($row['auto_process'] == 1) {
                                                $who = 'Auto Processed';
                                            } else if ($row['staff_id'] != 0) {
                                                $who = $crm->formatStaffName($row['FirstName'], $row['LastName']);
                                            } else {
                                                $who = 'Agency';
                                            }
                                        }
                                        echo $who;
                                        //echo $row['FirstName'] . " " . $row['LastName'];
                                        echo "</td>\n";

                                        echo "<td colspan=2>";

                                        $jl_com = '';
                                        if ($row['comments'] == 'Invoice/Cert Email Sent') {
                                            $jl_com = 'Invoice/Cert Email Sent (Not by Agency, by SATS)';
                                        } else {
                                            $jl_com = $row['comments'];
                                        }

                                        echo $jl_com;

                                        echo "</td>\n";

                                        echo "<td>";

                                        if (
                                                $row['contact_type'] == "Phone Call" ||
                                                $row['contact_type'] == "E-mail" ||
                                                $row['contact_type'] == "SMS Sent" ||
                                                $row['contact_type'] == "Work Order" ||
                                                $row['contact_type'] == "Unavailable" ||
                                                $row['contact_type'] == "Problematic" ||
                                                $row['contact_type'] == "SMS Received" ||
                                                $row['contact_type'] == "Duplicate Property" ||
                                                $row['contact_type'] == "Other"
                                        ) {
                                            if( $row['staff_id'] == $_SESSION['USER_DETAILS']['StaffID'] ){ //show only to user who created intentional event
                                                echo "<a href='view_job_details.php?id=" . $job_id . "&deletelog=" . $row['log_id'] . "#log{$added_param}' class='job_log_del' onclick='javascript: return confirm(\"Are you sure you want to delete this job log?\")'>Delete</a>";
                                            }
                                        }

                                        echo "<input type='hidden' class='job_log_id' value='{$row['log_id']}' />";
                                        echo "</td>\n";


                                        echo "</tr>\n";
                                    }
                                }
                                ?>

                            </table>





                            <?php
                            //if( strpos($_SERVER['SERVER_NAME'],"crmdev") !== false ){
                            // pagination
                            $pagi_offset = ($_REQUEST['offset'] != "") ? $_REQUEST['offset'] : 0;
                            $pagi_limit = 50;

                            $this_page = $_SERVER['PHP_SELF'];
                            $pagi_params = "&{$_SERVER['QUERY_STRING']}";

                            $next_link = "{$this_page}?offset=" . ($pagi_offset + $pagi_limit) . $pagi_params;
                            $prev_link = "{$this_page}?offset=" . ($pagi_offset - $pagi_limit) . $pagi_params;

                            // NEW LOGS TABLE
                            // paginate
                            $params = array(
                                'custom_select' => '
                                l.`log_id`,
                                l.`created_date`,
                                l.`title`,
                                l.`details`,
                                l.`auto_process`,

                                ltit.`title_name`,

                                aua.`fname`,
                                aua.`lname`,

                                sa.`StaffID`,
                                sa.`FirstName`,
                                sa.`LastName`
                            ',
                            'job_id' => $job_id,
                            'display_in_vjd' => 1,
                            'deleted' => 0,
                            'paginate' => array(
                                'offset' => $pagi_offset,
                                'limit' => $pagi_limit
                            ),
                            'sort_list' => array(
                                array(
                                    'order_by' => 'l.`created_date`',
                                    'sort' => 'DESC'
                                )
                            ),
                            'echo_query' => 0
                            );
                            $result = $crm->getNewLogs($params);

                            // all row
                            $params = array(
                                'custom_select' => 'l.`log_id`',
                                'job_id' => $job_id,
                                'display_in_vjd' => 1,
                                'deleted' => 0
                            );
                            $ptotal = mysql_num_rows($crm->getNewLogs($params));
                            ?>

                            <h2 class="heading">New logs</h2>
                            <table style="border:1px solid #cccccc !important;" border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">
                                <tr bgcolor="#<?php echo $serv_color ?>">
                                    <td class='colorwhite bold'>Date</td>
                                    <td class='colorwhite bold'>Time</td>
                                    <td class='colorwhite bold'>Title</td>
                                    <td class='colorwhite bold'>Who</td>
                                    <td class='colorwhite bold' style="width: 53%;">Details</td>
                                </tr>
                                <?php
                                // (3) While there are still rows in the result set,
                                // fetch the current row into the array $row
                                while ($row = mysql_fetch_array($result)) {
                                    ?>
                                    <tr class="border-none">
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($row['created_date'])); ?>
                                        </td>
                                        <td>
                                            <?php echo date('H:i', strtotime($row['created_date'])); ?>
                                        </td>
                                        <td>
                                            <?php echo $row['title_name']; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if( $row['auto_process'] == 1 ){
                                                echo "Auto Processed";
                                            }else{
                                                if ($row['StaffID'] != '') { // sats staff
                                                    echo "{$row['FirstName']} {$row['LastName']}";
                                                } else { // agency portal users
                                                    echo "{$row['fname']} {$row['lname']}";
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $params = array(
                                                'log_details' => $row['details'],
                                                'log_id' => $row['log_id']
                                            );
                                            echo $crm->parseDynamicLink($params);
                                            ?>
                                            <input type="hidden" class="log_id" value="<?php echo $row['log_id']; ?>" />
                                        </td>
                                    </tr>
                                <?php }
                                ?>
                            </table>

                            <?php
                            //}
                            ?>


                            <?php
                            // Initiate pagination class
                            $jp = new jPagination();

                            $per_page = $pagi_limit;
                            $page = ($_GET['page'] != "") ? $_GET['page'] : 1;
                            $pagi_offset = ($_GET['offset'] != "") ? $_GET['offset'] : 0;

                            echo $jp->display($page, $ptotal, $per_page, $pagi_offset, $pagi_params);
                            ?>


                            <?php
                            $prop_det_sql = mysql_query("
				SELECT *, j.`service` AS jservice
				FROM `jobs` AS j
				LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
				WHERE j.`id` = {$job_id}
			");
                            $prop_det = mysql_fetch_array($prop_det_sql);

                            // get switch property survey
                            $ssp_sql = mysql_query("
				SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_location`, `ss_quantity`, `ps_number_of_bedrooms`, ss_image
				FROM `jobs`
				WHERE `id` = {$_GET['id']}
			");
                            $ssp = mysql_fetch_array($ssp_sql);
                            ?>
                            <h2 class='heading'>Property Details</h2></td>
                            <table id="cw_table" class="ss_serv_updatable" width="100%" cellspacing="0" cellpadding="4" border="0">
                                <tbody>
                                    <tr bgcolor="#000000">
                                        <td class="colorwhite">Levels</td>
                                        <td class="colorwhite">Ceiling Type</td>
                                        <td class="colorwhite">Ladder Required</td>
                                        <td class="colorwhite">Bedrooms</td>
                                        <td class="colorwhite <?php echo($prop_det['state'] == 'QLD') ? '' : 'hide_qld_only_div'; ?>">Alarms required to meet NEW legislation (<strong style="color:red;">QLD ONLY</strong>)</td>
                                        <td class="colorwhite">Switchboard Viewed</td>
                                        <td class="colorwhite">Switchboard Location</td>
                                        <td class="colorwhite">SS Qty</td>
                                    </tr>
                                    <tr>

                                        <td><input type="number" name="survey_numlevels" style="width: 35px;" value="<?php echo $prop_det['survey_numlevels']; ?>" /></td>
                                        <td>
                                            <input name="survey_ceiling" type="radio" value="CON" <?php echo ($prop_det['survey_ceiling'] == 'CON') ? 'checked="checked"' : ''; ?> /> CON
                                            <input name="survey_ceiling" type="radio" value="GYP" <?php echo ($prop_det['survey_ceiling'] == 'GYP') ? 'checked="checked"' : ''; ?> /> GYP
                                        </td>
                                        <td>
                                            <input type="radio" name="survey_ladder" <?php echo ($prop_det['survey_ladder'] == '4FT') ? 'checked="checked"' : ''; ?> value="4FT" /> 3FT
                                            <input type="radio" name="survey_ladder" <?php echo ($prop_det['survey_ladder'] == '6FT') ? 'checked="checked"' : ''; ?> value="6FT" /> 6FT
                                            <input type="radio" name="survey_ladder" <?php echo ($prop_det['survey_ladder'] == '8FT') ? 'checked="checked"' : ''; ?> value="8FT" /> 8FT
                                        </td>


                                        <td><input type="number" name="ps_number_of_bedrooms" style="width: 35px;" value="<?php echo ($prop_det['ps_number_of_bedrooms'] == 0) ? '' : $prop_det['ps_number_of_bedrooms']; ?>" /></td>

                                        <td <?php echo($prop_det['state'] == 'QLD') ? '' : 'class="hide_qld_only_div"'; ?>><input type="number" name="qld_new_leg_alarm_num" style="width: 35px;" value="<?php echo ($prop_det['qld_new_leg_alarm_num'] == 0) ? '' : $prop_det['qld_new_leg_alarm_num']; ?>" /></td>

                                        <td>
                                            <?php //echo ($ssp['ts_safety_switch']==2)?'Yes':'No'; ?>
                                            <input type="radio" onclick="" name="ts_safety_switch" class="safety_switch_toggle" id="safety_switch_yes" <?php echo ($ssp['ts_safety_switch'] == '2') ? 'checked' : ''; ?> value="2">
                                            <label for="safety_switch_yes">Yes</label> &nbsp;&nbsp;
                                            <input type="radio" onclick="" name="ts_safety_switch" class="safety_switch_toggle radiobut-red"  id="safety_switch_no"  <?= ($ssp['ts_safety_switch'] == '1') ? 'checked' : ''; ?> value="1">
                                            <label for="safety_switch_no">No</label>
                                        </td>
                                        <td><input type="text" name="ss_location" value="<?php echo $prop_det['ss_location']; ?>" /></td>
                                        <td><input type="number" name="ss_quantity" style="width: 35px;" value="<?php echo $prop_det['ss_quantity']; ?>" /></td>




                                    </tr>
                                </tbody>
                            </table>


                            <!-- /Job contact log -->
                            <?php
                            // get jobs if bundle
                            $j_ajt_sql = mysql_query("
					SELECT *
					FROM `jobs` AS j
					LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
					WHERE j.`id` = {$_GET['id']}
				");
                            $j_ajt = mysql_fetch_array($j_ajt_sql);
                            $services = array();
                            if ($j_ajt['bundle'] == 1) {

                                $bndl_sql = mysql_query("
						SELECT *
						FROM `bundle_services`
						WHERE `job_id` ={$_GET['id']}
					");
                                while ($bndl = mysql_fetch_array($bndl_sql)) {
                                    $services[] = $bndl['alarm_job_type_id'];
                                }
                            } else {

                                $jsql = mysql_query("
						SELECT *
						FROM `jobs`
						WHERE `id` ={$_GET['id']}
					");
                                $j3 = mysql_fetch_array($jsql);
                                $services[] = $j3['service'];
                            }


                            echo "</td>";
                            echo "</tr>";

                            //print_r($services);



                            foreach ($services as $serv_val) {


                                switch ($serv_val) {
                                    case 2:
                                        $serv_color2 = 'b4151b';
                                        break;
                                    case 5:
                                        $serv_color2 = 'f15a22';
                                        break;
                                    case 6:
                                        $serv_color2 = '00ae4d';
                                        break;
                                    case 7:
                                        $serv_color2 = '00aeef';
                                        break;
                                    case 12:
                                        $serv_color2 = 'b4151b';
                                        break;
                                    default:
                                        $serv_color2 = 'b4151b';
                                }


                                # Draw the Alarms


                                if ($serv_val == 2 || $serv_val == 12) {

                                    // show only on staging
                                    $current_url = $_SERVER['HTTP_HOST'];
                                    if (strpos($current_url, 'crmdev') !== false) { // dev
                                        $show_it_on_dev = 1;
                                        $url = "https://crmdevci.sats.com.au/";
                                    } else {  // live
                                        if (strpos($current_url, 'nz') !== false) { // dev
                                            $show_it_on_dev = 1;
                                            $url = "https://crmci.sats.co.nz/";
                                        } 
                                        else{
                                            $show_it_on_dev = 1;
                                            $url = "https://crmci.sats.com.au/";
                                        }
                                    }

                                    // current alarms
                                    if (count($alarms) > 0) {

                                        echo "<h2 class='heading current_alarm_heading'>Current Alarms</h2>";
                                        
                                        
                                        if( $_SESSION['country_default'] == 1 ){ // AU

                                            if(  $jstate == 'QLD' ){  // QLD
                                                if( $job_row['preferred_alarm_id'] > 0 && $job_row['qld_new_leg_alarm_num'] > 0 ){
                                                    echo "<div class='preferred_alarm_div'>Property should use {$job_row['pref_alarm_make']} alarms</div>";
                                                }                                            
                                            }else{ // non-QLD
                                                echo "<div class='preferred_alarm_div'>Property should use ".$crm->display_free_emerald_or_paid_brooks($job_row['agency_id'])." alarms</div>";
                                            }

                                        }else{ // NZ
                                            // ask ben if they want to add here for NZ
                                            echo "<div class='preferred_alarm_div'>Property should use ".$crm->display_orca_or_cavi_alarms($job_row['agency_id'])." alarms</div>";
                                        }   
                                                                                                                        
                                        
                                       
                                        echo "<table cellpadding=4 cellspacing=0 width=100% border=0 id='alarm_table' class='sa_serv_updatable'>
									  <tr bgcolor='{$serv_color2}'>
										<td class='colorwhite bold' style='border-left: 1px solid #" . $serv_color2 . ";'>Position</td>
										<td class='colorwhite bold'>Power</td>
										<td class='colorwhite bold'>Type</td>
										<td class='colorwhite bold'>RFC</td>
										<td class='colorwhite bold'>New?</td>
										<td class='colorwhite bold'>Price</td>
										<td class='colorwhite bold'>Reason</td>";

                                        // only if IC alarm
                                        if (in_array($s['service'], $ic_serv)) {
                                            echo "<td class='colorwhite bold'>Interconnected</td>";
                                        }

                                        echo "<td class='colorwhite bold'>Make</td>
										<td class='colorwhite bold'>Model</td>
										<td class='colorwhite bold'>Expiry</td>
										<td class='colorwhite bold'>TS</td>
										<td class='colorwhite bold'>dB</td>
										<td class='colorwhite bold' " . ( ($show_it_on_dev == 1) ? '' : "style='display:none;'" ) . ">Images</td>
										<td class='colorwhite bold' style='border-right: 1px solid #b4151b;'>Remove</td>
									</tr>";

                                        # Alarm grid





                                        foreach ($alarms as $index => $data) {
                                            echo "<tr bgcolor=#F0F0F0 style='border-bottom: none;'>";
                                            echo "<td style='border-left: 1px solid #ccc;'>";

                                            # Hidden Alarm ID
                                            echo "<input type='hidden' name='alarm_id[]' value='" . $data['alarm_id'] . "'>";
                                            echo "<input type='hidden' name='sa_serv_manipulated[]' class='sa_serv_manipulated' value='0'>";

                                            echo "<input type='text' name='ts_position[]' value='" . $data['ts_position'] . "' size=8 class='xsmall addinput chops'>";

                                            echo "</select>";

                                            echo "</td>";


                                            echo "<td>";



                                            echo "<select name='alarm_power_id[]' size=1 class='vjd-sel' style='width: 100px !important;'>";
                                            echo "  <option selected value=''>&nbsp;</option>";

                                            foreach ($alarm_pwr as $a_i => $a_data) {
                                                echo "<option value='" . $a_data['alarm_pwr_id'] . "' " . ($data['alarm_power_id'] == $a_data['alarm_pwr_id'] ? 'selected' : '') . ">" . $a_data['alarm_pwr'] . "</option>\n";
                                            }

                                            echo "</select>";

                                            echo "</td>";


                                            echo "<td><select type=text name='alarm_type_id[]' size=1 class='vjd-sel'>\n";
                                            echo "  <option selected value=''>&nbsp;</option>\n";

                                            foreach ($alarm_type as $a_i => $a_data) {
                                                echo "<option value='" . $a_data['alarm_type_id'] . "' " . ($data['alarm_type_id'] == $a_data['alarm_type_id'] ? 'selected' : '') . ">" . $a_data['alarm_type'] . "</option>\n";
                                            }

                                            echo "</select>";

                                            echo "</td>";




                                            echo "<td>\n";

                                            echo "<select type=text name='ts_required_compliance[]' size=1 class='vjd-sel' style='width: 60px !important;'>\n";
                                            echo "<option value='0' " . ($data['ts_required_compliance'] == 0 ? 'selected' : '') . ">No</option>";
                                            echo "<option value='1' " . ($data['ts_required_compliance'] == 1 ? 'selected' : '') . ">Yes</option>";
                                            echo "</select>\n";

                                            echo "</td>\n";


                                            echo "<td>\n";

                                            echo "<select type=text name='newinstall[]' size=1 class='vjd-sel' style='width: 60px !important;'>\n";
                                            echo "<option value='0' " . ($data['new'] == 0 ? 'selected' : '') . ">No</option>";
                                            echo "<option value='1' " . ($data['new'] == 1 ? 'selected' : '') . ">Yes</option>";
                                            echo "</select>\n";

                                            echo "</td>\n";

                                            echo "<td><input type='number' name='alarm_price[]' style='width: 60px !important;' value='" . $data['alarm_price'] . "' size=8 class='xsmall addinput'></td>\n";                                           

                                            echo "<td>\n";
                                            echo "<select type=text name='alarm_reason_id[]' size=1 class='vjd-sel' style='width: 190px !important;'>\n";
                                            echo "<option selected value=''>&nbsp;</option>\n";

                                            foreach ($alarm_reason as $a_i => $a_data) {
                                                echo "<option value='" . $a_data['alarm_reason_id'] . "' " . ($data['alarm_reason_id'] == $a_data['alarm_reason_id'] ? 'selected' : '') . ">" . $a_data['alarm_reason'] . "</option>\n";
                                            }

                                            echo "</select>\n";
                                            echo "</td>";



                                            // only if IC alarm
                                            if (in_array($s['service'], $ic_serv)) {

                                                echo "<td>";

                                                echo "<select type=text name='ts_is_alarm_ic[]' size=1 class='vjd-sel' style='width: 60px !important;'>";
                                                echo "<option value=''>--- Select ---</option>";
                                                echo "<option value='0' " . ( ( is_numeric($data['ts_alarm_sounds_other']) && $data['ts_alarm_sounds_other'] == 0 ) ? 'selected' : '') . ">No</option>";
                                                echo "<option value='1' " . ($data['ts_alarm_sounds_other'] == 1 ? 'selected' : '') . ">Yes</option>";
                                                echo "</select>";

                                                echo "</td>";
                                            }


                                            echo "<td><input type=text name='make[]' value='" . strtoupper($data['make']) . "' size=8 class='xsmall addinput exlarge'></td>\n";
                                            echo "<td><input type=text name='model[]' value='" . strtoupper($data['model']) . "' size=8 class='xsmall addinput exlarge'></td>\n";
                                            echo "<td><input type='number' name='expiry[]' style='width: 55px;' value='" . $data['expiry'] . "' size=8 class='xxsmall addinput' /></td>\n";
                                            echo "<td>{$data['ts_expiry']}</td>";
                                            echo "<td><input type='number' class='xxsmall addinput vjd-inpt-rtn' name='ts_db_rating[]' style='width: 40px !important;' value='" . $data['ts_db_rating'] . "' maxlength='3' /></td>";

                                            echo "<td " . ( ($show_it_on_dev == 1) ? '' : "style='display:none;'" ) . "><a href='javascript:void(0);' class='alarm_images_toggle'><img src='/images/camera_red.png' style='margin-right: 6px;' /></a></td>";

                                            if ($job_editable) {
                                                echo "<td style='border-right: 1px solid #ccc;'><a href='?id=" . $job_id . "&delalarm=" . $data['alarm_id'] . "' class='remove_link'>Remove</a></td>\n";
                                            } else {
                                                echo "<td>N/A</td>\n";
                                            }

                                            echo "</tr>";
                                            ?>
                                            <?php
                                            //     $al_image_sql = mysql_query("
                                            //     SELECT alarm_id
                                            //     FROM `alarm`
                                            //     WHERE `job_id` = {$job_id}
                                            // ");
                                            // $row_img_sql = mysql_fetch_array($al_image_sql);
                                            // echo "Alarm IDS: <br />";
                                            // print_r($row_img_sql);
                                            // while($row_img_sql = mysql_fetch_object($al_image_sql)){
                                            //     echo $row_img_sql->alarm_id;
                                            // }
                                            ?>
                                            <tr class="alarm_images_tr" bgcolor=#F0F0F0 style='border-bottom: none; display:none;'>
                                                <td>Pic of Expiry Date:</td>
                                                <td>
                                                    <?php
                                                    $al_id = $data['alarm_id'];

                                                    $al_expiry_sql = mysql_query("
                                                        SELECT expiry_image_filename
                                                        FROM `alarm_images`
                                                        WHERE `alarm_id` = {$al_id}
                                                    ");
                                                    
                                                    while($row_expiry_sql = mysql_fetch_object($al_expiry_sql)){
                                                        $img_expiry_filename = $row_expiry_sql->expiry_image_filename;
                                                    }
                                                    ?>

                                                    <!--<a href="/images/sample_sa_expiry_date.jpg" class="fancybox">-->
                                                    <?php if($img_expiry_filename!=""){ ?>

                                                    <a href="<?php echo $url; ?>images/alarm_images/<?php echo $img_expiry_filename ?>" class="fancybox">
                                                        <img src="/images/camera_red.png" style="margin-right: 6px;" />
                                                    </a>
                                                    <span style="position: relative; bottom: 5px; left: 4px; margin-right: 9px; color:red;">Image Stored</span>
                                                    <!--<input type="file" capture="camera" accept="image/*" name="pic_of_exp" id="pic_of_exp" style="margin-top: 2px;  position: relative; bottom: 5px; width: 189px;" />-->
                                                    <input type="hidden" name="sa_image_touched" id="sa_image_touched" value="" />
                                                    
                                                    <?php }else{
                                                        echo "<span style='color:red'>No Image</span>";
                                                    } ?>

                                                </td>
                                                <td>Pic of Alarm Location:</td>
                                                <td>
                                                    <?php
                                                    $al_id = $data['alarm_id'];

                                                    $al_location_sql = mysql_query("
                                                        SELECT location_image_filename
                                                        FROM `alarm_images`
                                                        WHERE `alarm_id` = {$al_id}
                                                    ");
                                                    
                                                    while($row_location_sql = mysql_fetch_object($al_location_sql)){
                                                        $img_location_filename = $row_location_sql->location_image_filename;
                                                    }
                                                    ?>

                                                    <?php if($img_location_filename!=""){ ?>

                                                    <!--<a href="/images/sample_sa_in_situ.png" class="fancybox"> -->
                                                    <a href="<?php echo $url; ?>images/alarm_images/<?php echo $img_location_filename ?>" class="fancybox">
                                                        <img src="/images/camera_red.png" style="margin-right: 6px;" />
                                                    </a>
                                                    <span style="position: relative; bottom: 5px; left: 4px; margin-right: 9px; color:red;">Image Stored</span>
                                                    <!--<input type="file" capture="camera" accept="image/*" name="pic_of_exp" id="pic_of_exp" style="margin-top: 2px;  position: relative; bottom: 5px; width: 189px;" />-->
                                                    <input type="hidden" name="sa_image_touched" id="sa_image_touched" value="" />

                                                    <?php }else{
                                                        echo "<span style='color:red'>No Image</span>";
                                                    } ?>

                                                </td>
                                                <td colspan="100%">&nbsp;</td>
                                            </tr>
                                            <?php
                                        }

                                        echo "</table>";
                                        echo "<td>";
                                        echo "</tr>";
                                    }


                                    // discarded alarms
                                    $disc_alarms = getPropertyAlarms($job_id, 1, 2, 2);
                                    if (count($disc_alarms) > 0) {

                                        echo "<tr>";
                                        echo "<td colspan=4><h2 class='heading'>Removed Alarms</h2></td>";
                                        echo "</tr>";
                                        echo "<tr>";
                                        echo "<td colspan=7><a name='alarmtop'></a>";
                                        echo "<table cellpadding=4 cellspacing=0 width=100% border=0 id='alarm_table' class='sa_serv_updatable'>
									  <tr bgcolor='{$serv_color2}'>
										<td class='colorwhite bold' style='border-left: 1px solid #" . $serv_color2 . ";'>Position</td>
										<td class='colorwhite bold'>Power</td>
										<td class='colorwhite bold'>Type</td>
										<td class='colorwhite bold'>RFC</td>
										<td class='colorwhite bold'>New?</td>
										<td class='colorwhite bold'>Price</td>
										<td class='colorwhite bold'>Reason</td>";

                                        // only if IC alarm
                                        if (in_array($s['service'], $ic_serv)) {
                                            echo "<td class='colorwhite bold'>Interconnected</td>";
                                        }

                                        echo "<td class='colorwhite bold'>Make</td>
										<td class='colorwhite bold'>Model</td>
										<td class='colorwhite bold'>Expiry</td>
										<td class='colorwhite bold'>TS</td>
										<td class='colorwhite bold'>dB</td>
										<td class='colorwhite bold' style='border-right: 1px solid #b4151b;'>Remove</td>
									</tr>";

                                        # Alarm grid

                                        foreach ($disc_alarms as $index => $data) {
                                            echo "<tr bgcolor=#FFCCCB style='border-bottom: none;'>";
                                            echo "<td style='border-left: 1px solid #ccc;'>";

                                            # Hidden Alarm ID
                                            echo "<input type='hidden' name='disc_alarm_id[]' value='" . $data['alarm_id'] . "'>";
                                            echo "<input type='hidden' name='disc_sa_serv_manipulated[]' class='sa_serv_manipulated' value='0'>";

                                            echo "<input type='text' name='disc_ts_position[]' value='" . $data['ts_position'] . "' size=8 class='xsmall addinput'>";

                                            echo "</select>";

                                            echo "</td>";


                                            echo "<td>";



                                            echo "<select name='disc_alarm_power_id[]' size=1 class='vjd-sel' style='width: 100px !important;'>";
                                            echo "  <option selected value=''>&nbsp;</option>";

                                            foreach ($alarm_pwr as $a_i => $a_data) {
                                                echo "<option value='" . $a_data['alarm_pwr_id'] . "' " . ($data['alarm_power_id'] == $a_data['alarm_pwr_id'] ? 'selected' : '') . ">" . $a_data['alarm_pwr'] . "</option>\n";
                                            }

                                            echo "</select>";

                                            echo "</td>";


                                            echo "<td><select type=text name='disc_alarm_type_id[]' size=1 class='vjd-sel'>\n";
                                            echo "  <option selected value=''>&nbsp;</option>\n";

                                            foreach ($alarm_type as $a_i => $a_data) {
                                                echo "<option value='" . $a_data['alarm_type_id'] . "' " . ($data['alarm_type_id'] == $a_data['alarm_type_id'] ? 'selected' : '') . ">" . $a_data['alarm_type'] . "</option>\n";
                                            }

                                            echo "</select>";

                                            echo "</td>";




                                            echo "<td>\n";

                                            echo "<select type=text name='disc_ts_required_compliance[]' size=1 class='vjd-sel' style='width: 60px !important;'>\n";
                                            echo "<option value='0' " . ($data['ts_required_compliance'] == 0 ? 'selected' : '') . ">No</option>";
                                            echo "<option value='1' " . ($data['ts_required_compliance'] == 1 ? 'selected' : '') . ">Yes</option>";
                                            echo "</select>\n";

                                            echo "</td>\n";


                                            echo "<td>\n";

                                            echo "<select type=text name='disc_newinstall[]' size=1 class='vjd-sel' style='width: 60px !important;'>\n";
                                            echo "<option value='0' " . ($data['new'] == 0 ? 'selected' : '') . ">No</option>";
                                            echo "<option value='1' " . ($data['new'] == 1 ? 'selected' : '') . ">Yes</option>";
                                            echo "</select>\n";

                                            echo "</td>\n";

                                            echo "<td><input type='number' name='disc_alarm_price[]' style='width: 60px !important;' value='" . $data['alarm_price'] . "' size=8 class='xsmall addinput'></td>\n";

                                            echo "<td>\n";
                                            echo "<select type=text name='disc_alarm_reason_id[]' size=1 class='vjd-sel' style='width: 190px !important;'>\n";
                                            echo "<option selected value=''>&nbsp;</option>\n";

                                            $alarm_reason_sql = mysql_query("
											SELECT *
											FROM `alarm_discarded_reason`
											WHERE `active` = 1
											ORDER BY `reason` ASC
										");
                                            while ($a_data = mysql_fetch_array($alarm_reason_sql)) {
                                                echo "<option value='" . $a_data['id'] . "' " . ($data['ts_discarded_reason'] == $a_data['id'] ? 'selected' : '') . ">" . $a_data['reason'] . "</option>\n";
                                            }

                                            echo "</select>\n";
                                            echo "</td>";


                                            // only if IC alarm
                                            if (in_array($s['service'], $ic_serv)) {

                                                echo "<td>";

                                                echo "<select type=text name='disc_ts_is_alarm_ic[]' size=1 class='vjd-sel' style='width: 60px !important;'>";
                                                echo "<option value='0' " . ($data['ts_alarm_sounds_other'] == 0 ? 'selected' : '') . ">No</option>";
                                                echo "<option value='1' " . ($data['ts_alarm_sounds_other'] == 1 ? 'selected' : '') . ">Yes</option>";
                                                echo "</select>";

                                                echo "</td>";
                                            }


                                            echo "<td><input type=text name='disc_make[]' value='" . strtoupper($data['make']) . "' size=8 class='xsmall addinput exlarge'></td>\n";
                                            echo "<td><input type=text name='disc_model[]' value='" . strtoupper($data['model']) . "' size=8 class='xsmall addinput exlarge'></td>\n";
                                            echo "<td><input type='number' name='disc_expiry[]' style='width: 55px;' value='" . $data['expiry'] . "' size=8 class='xxsmall addinput' /></td>\n";
                                            echo "<td>{$data['ts_expiry']}</td>";
                                            echo "<td><input type='number' class='xxsmall addinput vjd-inpt-rtn' name='disc_ts_db_rating[]' style='width: 40px !important;' value='" . $data['ts_db_rating'] . "' maxlength='3' /></td>";

                                            if ($job_editable) {
                                                echo "<td style='border-right: 1px solid #ccc;'><a href='?id=" . $job_id . "&delalarm=" . $data['alarm_id'] . "' class='remove_link'>Remove</a></td>\n";
                                            } else {
                                                echo "<td>N/A</td>\n";
                                            }


                                            echo "</tr>";
                                        }

                                        echo "</table>";
                                        echo "<td>";
                                        echo "</tr>";
                                    }
                                }

                                // only if corded windows
                                if ($serv_val == 6) {

                                    // get corded windows
                                    $cw_sql = mysql_query("
							SELECT *
							FROM `corded_window`
							WHERE `job_id` ={$_GET['id']}
						");

                                    if (mysql_num_rows($cw_sql) > 0) {
                                        ?>

                                        <tr>
                                            <td colspan=4><h2 class='heading'>Window Details</h2></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table cellpadding=4 cellspacing=0 width=100% border=0 id='cw_table' class='cw_serv_updatable'>

                                                    <tr bgcolor="#00ae4d">
                                                        <td class="colorwhite">Location</td>
                                                        <td class="colorwhite">Number of windows</td>
                                                        <td class="colorwhite" style="border-right: 1px solid #ccc;">Delete</td>
                                                    </tr>
                                                    <?php
                                                    $x = 0;
                                                    while ($cw = mysql_fetch_array($cw_sql)) {
                                                        ?>
                                                        <tr class="<?php echo $row_clr = ($x % 2 == 0 ? "grey" : "off"); ?>">
                                                            <td>
                                                                <input type="hidden" name="cw_serv_manipulated[]" class="cw_serv_manipulated" value="0" />
                                                                <input type="hidden" name="corded_window_id[]" value="<?php echo $cw['corded_window_id']; ?>" />
                                                                <input type="text" class="addinput cw_data" name="location[]" value="<?php echo $cw['location']; ?>" />
                                                            </td>
                                                            <td><input type="number" style="width: 30px;" class="addinput cw_data" name="num_of_windows[]" value="<?php echo $cw['num_of_windows']; ?>" /></td>
                                                            <td style="border-right: 1px solid #ccc;">
                                                                <a href="?id=<?= $job_id; ?>&delcw=<?= $cw['corded_window_id']; ?>&amp;tab=corded-window-compliance-tab" onclick="return confirm('Are you sure you want to delete?');" class="green">
                                                                    Delete
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $x++;
                                                    }
                                                    ?>

                                                </table>
                                                <input type="hidden" id="cw_touched_flag" name="cw_touched_flag" value="" />
                                            </td>
                                        </tr>


                                        <?
                                    }
                                }



                                // only safety switch
                                if ($serv_val == 5) {
                                    ?>

                                    <tr>
                                        <td colspan=4><h2 class='heading'>Switch Details</h2></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table cellpadding=4 cellspacing=0 width=100% border=0 id='cw_table' class="ss_serv_updatable">

                                                <tr bgcolor="#<?php echo $serv_color2; ?>">
                                                    <td class="colorwhite">New?</td>
                                                    <td class="colorwhite">Reason</td>
                                                    <td class="colorwhite">Pole</td>
                                                    <td class="colorwhite">Make</td>
                                                    <td class="colorwhite">Model</td>
                                                    <td class="colorwhite">Test</td>
                                                    <td class="colorwhite">Discarded</td>
                                                    <td class="colorwhite" style="border-right: 1px solid #ccc;">Discard</td>
                                                </tr>
                                                <?php
                                                // get safety switch
                                                $ss_sql = mysql_query("
                                                SELECT 
                                                    ss.`safety_switch_id`,
                                                    ss.`make`,
                                                    ss.`model`,
                                                    ss.`test`,
                                                    ss.`new`,
                                                    ss.`ss_res_id`,
                                                    ss.`ss_stock_id`,
                                                    ss.`discarded`
                                                FROM `safety_switch` AS ss
                                                LEFT JOIN `safety_switch_stock` AS ss_stock ON ss.`ss_stock_id` = ss_stock.`ss_stock_id`
                                                LEFT JOIN `safety_switch_reason` AS ss_reason ON ss.`ss_res_id` = ss_reason.`ss_res_id`
                                                WHERE ss.`job_id` = {$job_id}                   
                                                ORDER BY ss.`make`
                                                ");

                                                if (mysql_num_rows($ss_sql) > 0) {

                                                    $x = 0;
                                                    while ($ss = mysql_fetch_object($ss_sql)) {
                                                        ?>
                                                        <tr class="<?php echo ($x % 2 == 0 ? "grey" : "off"); ?>">
                                                        <td>
                                                            <select name="ss_new_update[]" class="form-control">
                                                                <option value="">---</option>
                                                                <option value="1" <?php echo ( $ss->new == 1 )?'selected':null; ?>>Yes</option>
                                                                <option value="0" <?php echo ( $ss->new == 0 && is_numeric($ss->new) )?'selected':null; ?>>Existing</option>                                                     
                                                            </select>	
                                                        </td>  
                                                        <td>
                                                            <select name="ss_reason_update[]" class="form-control">
                                                                <option value="">---</option>
                                                                <?php
                                                                // get safety switch reason
                                                                $ss_reason_sql = mysql_query("
                                                                SELECT 
                                                                    `ss_res_id`,    
                                                                    `reason`                        
                                                                FROM `safety_switch_reason`
                                                                ");
                                                                while( $ss_reason_row = mysql_fetch_object($ss_reason_sql) ){ ?>
                                                                    <option value='<?php echo $ss_reason_row->ss_res_id; ?>' <?php echo ( $ss_reason_row->ss_res_id == $ss->ss_res_id )?'selected':null; ?>><?php echo $ss_reason_row->reason; ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>	
                                                        </td>
                                                        <td>
                                                            <select name="ss_pole_update[]" class="form-control">
                                                                <option value="">---</option>
                                                                <?php
                                                                // get safety switch stocks
                                                                $ss_stock_sql = mysql_query("
                                                                SELECT 
                                                                    `ss_stock_id`,
                                                                    `pole`,
                                                                    `make`,
                                                                    `model`                            
                                                                FROM `safety_switch_stock`
                                                                WHERE `active` = 1
                                                                ");
                                                                while( $ss_stock_row = mysql_fetch_object($ss_stock_sql) ){ ?>
                                                                    <option 
                                                                        value="<?php echo $ss_stock_row->ss_stock_id; ?>"
                                                                        data-ss_stock_make="<?php echo $ss_stock_row->make; ?>"
                                                                        data-ss_stock_model="<?php echo $ss_stock_row->model; ?>"
                                                                        <?php echo ( $ss_stock_row->ss_stock_id == $ss->ss_stock_id )?'selected':null; ?>
                                                                    >
                                                                        <?php echo $ss_stock_row->pole; ?> Pole
                                                                    </option>
                                                                <?php
                                                                }
                                                                ?>                                                   
                                                            </select>	
                                                        </td>   
                                                            <td class="colorwhite">
                                                                <input type="hidden" name="ss_id[]" value="<?= $ss->safety_switch_id; ?>" />
                                                                <input type="hidden" name="ss_serv_manipulated[]" class="ss_serv_manipulated" value="0" />
                                                                <input type="text" class="xsmall addinput exlarge" name="ss_make[]"  value="<?php echo $ss->make; ?>" />
                                                            </td>
                                                            <td class="colorwhite">
                                                                <input type="text" class="xsmall addinput exlarge" name="ss_model[]"  value="<?php echo $ss->model; ?>" />
                                                            </td>
                                                            <td class="colorwhite">
                                                                <select name="ss_test[]" style="width:110px;">
                                                                    <option value="">---</option>
                                                                    <option value="1" <?php echo ($ss->test == 1) ? 'selected="selected"' : ''; ?>>Pass</option>
                                                                    <option value="0" <?php echo ($ss->test == 0 && is_numeric($ss->test)) ? 'selected="selected"' : ''; ?>>Fail</option>
                                                                    <option value="2" <?php echo ($ss->test == 2) ? 'selected="selected"' : ''; ?>>No Power</option>
                                                                    <option value="3" <?php echo ($ss->test == 3) ? 'selected="selected"' : ''; ?>>Not Tested</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <?php echo ( $ss->discarded == 1 )?'Yes':null; ?>
                                                            </td>
                                                            <td class="colorwhite" style="border-right: 1px solid #ccc;">                                                               
                                                                <?php                                                               
                                                                if( $ss->discarded == 1 ){ // discarded ?>
                                                                    
                                                                    <a href="?id=<?= $job_id; ?>&delss=<?= $ss->safety_switch_id; ?>" onclick="return confirm('Are you sure you want to delete?');">
                                                                        Delete
                                                                    </a>

                                                                <?php
                                                                }else{ ?>
                                                                    
                                                                    <a class="fancybox confirm_discard_ss_link" data-discard_ss_id="<?= $ss->safety_switch_id; ?>" href="#confirm_discard_ss_fb">Discard</a>

                                                                <?php
                                                                }
                                                                ?>                                                                
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $x++;
                                                    }

                                                } else {
                                                    echo '<tr><td colspan="100%">Empty</td></tr>';
                                                }
                                                ?>

                                            </table>
                                        </td>
                                    </tr>

                                    <?php
                                    // get switch property survey
                                    $ssp_sql = mysql_query("
							SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_location`, `ss_quantity`, `ps_number_of_bedrooms`, ss_image
							FROM `jobs`
							WHERE `id` = {$_GET['id']}
						");
                                    $ssp = mysql_fetch_array($ssp_sql);
                                    ?>
                                    <tr>
                                        <td colspan=4><h2 class='heading'>Switch Property Survey</h2></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table cellpadding=4 cellspacing=0 width=100% border=0 id='cw_table'>

                                                <tr bgcolor="#<?php echo $serv_color2; ?>">

                                                    <td class="colorwhite">Reason</td>
                                                    <td class="colorwhite">Fusebox Location</td>
                                                    <td class="colorwhite">Safety Switch Quantity</td>
                                                    <td class="colorwhite">Switch Board Image</td>

                                                </tr>

                                                <tr>

                                                    <td>
                                                        <?php
                                                        if ($ssp['ts_safety_switch'] == 1) { // if NO
                                                            switch ($ssp['ts_safety_switch_reason']) {
                                                                case 0:
                                                                    $ssp_reason = 'Circuit Breaker Only';
                                                                    break;
                                                                case 1:
                                                                    $ssp_reason = 'Unable to Locate';
                                                                    break;
                                                                case 2:
                                                                    $ssp_reason = 'Unable to Access';
                                                                    break;
                                                            }
                                                            ?>

                                                            <select name="ss_reason" id="ss_reason" class="xsmall addinput exlarge ss_reason" style="width: 140px !important;">
                                                                <option value="">----</option>
                                                                <option value="0" <?php echo ($ssp['ts_safety_switch_reason'] == 0 ? "selected" : ""); ?>>Circuit Breaker Only</option>
                                                                <option value="1" <?php echo ($ssp['ts_safety_switch_reason'] == 1 ? "selected" : ""); ?>>Unable to Locate</option>
                                                                <option value="2" <?php echo ($ssp['ts_safety_switch_reason'] == 2 ? "selected" : ""); ?>>Unable to Access</option>
                                                            </select>

                                                            <?php
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        //echo $ssp['ss_location'];
                                                        ?>
                                                        <input type="text" class="xsmall addinput exlarge" name="ss_location" value="<?php echo $ssp['ss_location']; ?>" />
                                                    </td>
                                                    <td>
                                                        <?php
                                                        //echo $ssp['ss_quantity'];
                                                        ?>
                                                        <input type="number" class="xsmall addinput exlarge" name="ss_quantity" value="<?php echo $ssp['ss_quantity']; ?>" />
                                                    </td>
                                                    <td>
                                                        <?php if ($ssp['ss_image'] != '') {
                                                        
                                                        // dynamic switch of ss image
                                                        if ( file_exists("{$_SERVER['DOCUMENT_ROOT']}/images/ss_image/{$ssp['ss_image']}") ) {   
                                                            // old techsheet 
                                                            $ss_image_upload_folder = '/images/ss_image';
                                                        }else{ // tecsheet CI 
                                                            $ci_domain = $crm->getDynamicCiDomain();
                                                            $ss_image_upload_folder = "{$ci_domain}/uploads/switchboard_image";
                                                        }

                                                        ?>
                                                            <a href="<?php echo $ss_image_upload_folder ?>/<?php echo $ssp['ss_image']; ?>" class="fancybox">
                                                                <img src="/images/camera_orange.png" style="margin-right: 6px;" />
                                                            </a>
                                                            <span style="position: relative; bottom: 5px; left: 4px; margin-right: 9px; color:#f15a22;">Image Stored</span>
                                                        <?php } else {
                                                            ?>
                                                            <img src="/images/camera_grey.png" style="margin-right: 6px;" />
                                                            <?php
                                                        }
                                                        ?>
                                                        <!--<input type="file" capture="camera" accept="image/*" name="ss_image" id="ss_image" style="margin-top: 2px;  position: relative; bottom: 5px" />-->
                                                        <input type="hidden" name="ss_image_touched" id="ss_image_touched" value="" />
                                                    </td>
                                                </tr>


                                            </table>
                                        </td>
                                    </tr>


                                    <?
                                }



                                // only Water Meter
                                if ($serv_val == 7) {
                                    ?>

                                    <tr>
                                        <td colspan=4><h2 class='heading'>Water Meter Details</h2></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table cellpadding=4 cellspacing=0 width=100% border=0 id='cw_table' class="wm_serv_updatable">

                                                <tr bgcolor="#<?php echo $serv_color2; ?>">
                                                    <td class="colorwhite" style="width: 20%;">Location</td>
                                                    <td class="colorwhite">Reading</td>
                                                    <td class="colorwhite">Meter Image</td>
                                                    <td class="colorwhite" style="border-right: 1px solid #ccc;">Meter Reading Image</td>
                                                </tr>
                                                <?php
                                                $wm_sql = getWaterMeter($_GET['id']);
                                                $wm = mysql_fetch_array($wm_sql);

                                                if (mysql_num_rows($wm_sql) > 0) {
                                                    ?>
                                                    <tr class="<?php echo ($x % 2 == 0 ? "grey" : "off"); ?>">
                                                        <td>
                                                            <input type="hidden" name="water_meter_id" value="<?= $wm['water_meter_id']; ?>" />
                                                            <input type="hidden" name="wm_serv_manipulated" class="wm_serv_manipulated" value="0" />
                                                            <input type="text" class="xsmall addinput exlarge" name="location" style="width: 90% !important;"  value="<?php echo $wm['location']; ?>" />
                                                        </td>
                                                        <td><input type="number" class="xsmall addinput exlarge" name="reading" style="width:80px;" value="<?php echo $wm['reading']; ?>" />
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $img = explode("/", $wm['meter_image']);
                                                            ?>
                                                            
                                                            <?php if($wm['meter_image']!=""){ ?>

                                                            <a href="<?php echo $wm['meter_image']; ?>" class="fancybox" style="margin-right: 5px; position: relative; top: 5px;">
                                                                <img src="/images/camera_blue.png" />
                                                            </a>
                                                            <span style="position: relative; bottom: 2px; left: 4px; margin-right: 9px; color: #00aeef;">Image Stored</span>
                                                            <input type="file" capture="camera" accept="image/*" name="meter_image" id="meter_image" style="margin-top: 2px;" />
                                                                
                                                            <?php }else{
                                                                echo "<span style='color:red'>No Image</span>";
                                                            } ?>
                                                            
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $img = explode("/", $wm['meter_reading_image']);
                                                            ?>

                                                            <?php if($wm['meter_reading_image']!=""){ ?>

                                                            <a href="<?php echo $wm['meter_reading_image']; ?>" class="fancybox" style="margin-right: 5px; position: relative; top: 5px;">
                                                                <img src="/images/camera_blue.png" />
                                                            </a>
                                                            <span style="position: relative; bottom: 2px; left: 4px; margin-right: 9px; color: #00aeef;">Image Stored</span>
                                                            <input type="file" capture="camera" accept="image/*" name="meter_reading_image" id="meter_reading_image" style="margin-top: 2px;" />
                                                                
                                                            <?php }else{
                                                                echo "<span style='color:red'>No Image</span>";
                                                            } ?>

                                                        </td>
                                                    </tr>
                                                    <?php
                                                } else {
                                                    echo '<tr><td colspan="100%">Empty</td></tr>';
                                                }
                                                ?>

                                            </table>
                                        </td>
                                    </tr>



                                    <?
                                }



                                // only if WE
                                if ($serv_val == 15) {

                                    // get WE data
                                    $we_sql = mysql_query("
                                    SELECT
                                        we.`water_efficiency_id`,
                                        we.`device`,
                                        we.`pass`,
                                        we.`location`,
                                        we.`note`,

                                        wed.`name` AS wed_name
                                    FROM `water_efficiency` AS we
                                    LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
                                    WHERE we.`job_id` = {$job_id}
                                    AND we.`active` = 1
                                    ");

                                    if (mysql_num_rows($we_sql) > 0) {
                                        ?>

                                        <tr>
                                            <td colspan=4><h2 class='heading'>Water Efficiency Details</h2></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table cellpadding=4 cellspacing=0 width=100% border=0 id='cw_table' class='we_serv_updatable'>

                                                    <tr bgcolor="#24B8EF">
                                                        <td class="colorwhite">Device</td>
                                                        <td class="colorwhite">Toilet Type / Is water flow less than 9L per minute?</td>
                                                        <td class="colorwhite">Location</td>
                                                        <td class="colorwhite">Note</td>
                                                        <td class="colorwhite" style="border-right: 1px solid #ccc;">Delete</td>
                                                    </tr>
                                                    <?php
                                                    $x = 0;
                                                    while ($we_row = mysql_fetch_array($we_sql)) {
                                                        ?>
                                                        <tr class="<?php echo $row_clr = ($x % 2 == 0 ? "grey" : "off"); ?>">
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
                                                                        <option value="<?php echo $wed_row['water_efficiency_device_id'] ?>" <?php echo ( $wed_row['water_efficiency_device_id'] == $we_row['device'] )?'selected':null; ?>><?php echo $wed_row['name'] ?></option>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                if( $we_row['device'] == 2 ){ // toilet
                                                                    $pass_yes = 'Dual';
                                                                    $pass_no = 'Single';
                                                                }else{
                                                                    $pass_yes = 'Yes';
                                                                    $pass_no = 'No';
                                                                }
                                                                ?>
                                                                <input type="radio" class="we_pass we_pass_yes" name="we_pass[<?php echo $x; ?>]" value="1" <?php echo ( $we_row['pass'] == 1 )?'checked':null; ?> /> <label class="we_pass_lbl_yes"><?php echo $pass_yes; ?></label>
				                                                <input type="radio" class="we_pass we_pass_no" name="we_pass[<?php echo $x; ?>]" value="0" <?php echo ( $we_row['pass'] == 0 && is_numeric($we_row['pass']) )?'checked':null; ?> /> <label class="we_pass_lbl_no"><?php echo $pass_no; ?></label>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="addinput we_location" name="we_location[]" id="we_location" value="<?php echo $we_row['location']; ?>" />
                                                            </td>
                                                            <td>
                                                                <input type="text" class="addinput we_note" name="we_note[]" id="we_note" value="<?php echo $we_row['note']; ?>" />
                                                            </td>
                                                            <td style="border-right: 1px solid #ccc;">

                                                                <input type="hidden" name="we_serv_manipulated[]" class="we_serv_manipulated" value="0" />
                                                                <input type="hidden" name="we_id[]" value="<?php echo $we_row['water_efficiency_id']; ?>" />

                                                                <a href="?id=<?= $job_id; ?>&we_del=1&we_id=<?= $we_row['water_efficiency_id']; ?>&amp;tab=water-efficiency-compliance-tab" onclick="return confirm('Are you sure you want to delete?');">
                                                                    Delete
                                                                </a>

                                                            </td>
                                                        </tr>
                                                        <?php
                                                        $x++;
                                                    }
                                                    ?>

                                                </table>
                                                <input type="hidden" id="cw_touched_flag" name="cw_touched_flag" value="" />
                                            </td>
                                        </tr>


                                        <?
                                    }
                                }


                            }



                            # Draw the Appliances
                            echo "<tr>";
                            echo "<td colspan='6' style='border-bottom: 1px dotted #cecece;'></td>";
                            echo "</tr>";


                            echo "<tr>";
                            echo "<td colspan=4><a name='alarmtop'></a>";



                            // only show on smoke
                            if ($service == 2 || $service == 12) {


                                # Draw the Safety Switches
                                echo "<h2 class='heading'>Property Details</h2>";
                                echo "<table cellpadding=4 cellspacing=0 width=100% border=0 id='alarm_table' class='vw-alm-det-inr'>";

                                if (mysql_num_rows($safety_switches) == 0) {
                                    echo "<tr>";
                                    echo "<td colspan='6'>No appliances on file</td>";

                                    echo "</tr>";
                                } else {
                                    echo "
							<tr bgcolor='#{$serv_color}'>
								<td class='colorwhite bold' style='border-left: 1px solid #{$serv_color};'>Ladder Required</td>
								<td class='colorwhite bold'>Ceiling Type</td>
								<td class='colorwhite bold'>Fusebox Viewed</td>
								<td class='colorwhite bold'>Fuse Box Location</td>
								<td class='colorwhite bold'>Quantity</td>
								<td class='colorwhite bold' style='border-right: 1px solid #{$serv_color};'>Reason</td>
							</tr>";


                                    while ($data = mysql_fetch_array($safety_switches)) {

                                        // safety switch present
                                        switch ($data['ts_safety_switch']) {
                                            case 2:
                                                $ss_present = 'Yes';
                                                $ss_location = $data['ss_location'];
                                                $ss_quantity = $data['ss_quantity'];
                                                break;
                                            case 1:
                                                $ss_present = 'No';
                                                // safety switch reason
                                                switch ($data['ts_safety_switch_reason']) {
                                                    case 0:
                                                        $ss_reason = 'Circuit Breaker Only';
                                                        break;
                                                    case 1:
                                                        $ss_reason = 'Unable to Locate';
                                                        break;
                                                    case 2:
                                                        $ss_reason = 'Unable to Access';
                                                        break;
                                                }
                                                break;
                                            default:
                                                $ss_present = '';
                                                $ss_reason = '';
                                                $ss_location = '';
                                                $ss_quantity = '';
                                        }

                                        echo "<tr style='border: 1px solid #ccc !important;'>";
                                        echo "<td>";
                                        echo ( $data['survey_ladder'] == '4FT' ) ? '3FT' : $data['survey_ladder'];
                                        echo "</td>";
                                        echo "<td>";
                                        echo $data['survey_ceiling'];
                                        echo "</td>";
                                        echo "<td>";
                                        echo $ss_present;
                                        echo "</td>";
                                        echo "<td>";
                                        echo $ss_location;
                                        echo "</td>";
                                        echo "<td>";
                                        echo $ss_quantity;
                                        echo "</td>";
                                        echo "<td>";
                                        echo $ss_reason;
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }


                                echo "\n";

                                // if completed is selected, copy the last booking date into the test date for the property.
                                if ($mc == "selected") {
                                    //
                                    //$insertQuery2 = "UPDATE property set test_date='$jobdate', retest_date=(DATE_ADD('$jobdate', INTERVAL 1 YEAR)) WHERE (property_id=$property_id);";
                                    $insertQuery2 = "UPDATE property set test_date='$jobdate' WHERE (property_id=$property_id);";
                                    $queryresult = mysql_query($insertQuery2, $connection);
                                } elseif (($cp == "selected" && ($ym == "selected" | $ym == "selected"))) {
                                    $insertQuery2 = "UPDATE property set test_date='$jobdate', retest_date=(DATE_ADD('$jobdate', INTERVAL 1 YEAR)) WHERE (property_id=$property_id);";
                                    $queryresult = mysql_query($insertQuery2, $connection);
                                }

                                //if (!$queryresult) echo "Error occured: please contact your IT consultant\n".mysql_error();
                                ?>
                                </tr>
                                </table>


                            <?php } ?>


                            </td></tr></table>

                            <!-- email invoice / cert -->
                            </form>
                            <?php
                            /*
                              if (($mc || $cp) == "selected"): ?>

                              <h2 class="heading"><a name="emailcert"></a>Email Certificate + Invoice</h2>

                              <form action='view_job_details.php?id=<?=$job_id;?>&doaction=emailcert#emailcert<?php echo $added_param; ?>' method='post' id='email_cert' name='email_cert'>
                              <table border=0 cellspacing=0 cellpadding=5 width='100%' class="table-left tbl-fr-red fnt-small tnt-cert">
                              <?php
                              // get agency comment
                              $ae_sql = mysql_query("
                              SELECT a.`account_emails`, a.`send_combined_invoice`
                              FROM `jobs` AS j
                              LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                              LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
                              WHERE j.`id` = {$_GET['id']}
                              ");
                              $ae = mysql_fetch_array($ae_sql);
                              ?>
                              <tr style="background-color: #e0f2c0;">
                              <td style="width: 65%;">
                              <span style="float: left; margin-top: 7px; margin-right: 5px;">Email Address</span>
                              <span>
                              <?php if($ae['send_combined_invoice']==1){ ?>
                              <input type="text" name="invoice_email" class="addinput float-left"  style="width: 200px;">
                              <button type="Submit" class="submitbtnImg float-left" style="margin-right: 10px;">
                              <img class="inner_icon" src="images/button_icons/email.png"/>
                              Email Invoice + Certificate
                              </button>
                              <?php } ?>
                              </span>
                              <?php
                              $email_inv_ls_sql = mysql_query("
                              SELECT *
                              FROM `job_log`
                              WHERE `job_id` = {$_GET['id']}
                              AND `contact_type` = 'Email Invoice'
                              ORDER BY `eventdate` DESC, `eventtime` DESC
                              LIMIT 0,1
                              ");
                              $email_inv_ls = mysql_fetch_array($email_inv_ls_sql);
                              ?>
                              <!--<span style="display: block; margin-top: 7px;">Last Sent: <?php echo (empty($email_inv_ls['eventdate']) ? "<span class='red'>Never</span>" : "<span class='green'>" . date('d/m/Y',strtotime($email_inv_ls['eventdate'])) . " @ ".$email_inv_ls['eventtime']. "</span>"); ?></span>-->
                              </td>
                              <td style="text-align: right;">Account Emails <input type="text" readonly value="<?php echo str_replace("\n",", ",$ae['account_emails']); ?>"  style="width: 200px;" /></td>
                              </tr>
                              </table>
                              </form>
                              <?php endif;
                             */
                            ?>



                            <?php if ((($mc || $cp) == "selected") && $jstate == 'QLD'): ?>

                                <h2 class="heading"><a name="emailcert"></a>Email Quote</h2>
                                <table border=0 cellspacing=0 cellpadding=5 width='100%' class="table-left tbl-fr-red fnt-small tnt-cert">
                                    <?php
                                    // get agency comment
                                    $quo_sql = mysql_query("
						SELECT a.`agency_emails`
						FROM `jobs` AS j
						LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
						LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
						WHERE j.`id` = {$_GET['id']}
					");
                                    $quo = mysql_fetch_array($quo_sql);
                                    ?>
                                    <tr style="background-color: #e0f2c0;">
                                        <td style="width: 65%;">
                                            <span style="float: left; margin-top: 7px; margin-right: 5px;">Email Address</span>
                                            <span>
                                                <input type="text" id="quote_email_to" class="addinput  float-left"  style="width: 200px;">
                                                <button type="button" id="btn_email_quote" class="submitbtnImg  float-left" style="margin-right: 8px;">
                                                    <img class="inner_icon" src="images/button_icons/email.png"/>
                                                    Email Quote To Agency
                                                </button>
                                            </span>
                                            <?php
                                            $quo_jl_sql = mysql_query("
					SELECT *
					FROM `job_log`
					WHERE `job_id` = {$_GET['id']}
					AND `contact_type` = 'Quote Email'
					ORDER BY `eventdate` DESC, `eventtime` DESC
					LIMIT 0,1
				");
                                            $quo_jl = mysql_fetch_array($quo_jl_sql);
                                            ?>
                                            <!--<span style="display: block; margin-top: 7px;">Last Sent: <?php echo (empty($quo_jl['eventdate']) ? "<span class='red'>Never</span>" : "<span class='green'>" . date('d/m/Y', strtotime($quo_jl['eventdate'])) . " @ " . $quo_jl['eventtime'] . "</span>"); ?></span>-->
                                        </td>
                                        <td style="text-align: right;">Agency Emails <input type="text" readonly value="<?php echo str_replace("\n", ", ", $quo['agency_emails']); ?>"  style="width: 200px;" /></td>
                                    </tr>
                                </table>

                            <?php endif; ?>

                            <div class="style3">
                                <h2 class="heading"><a name="emailcert"></a>Tenant Entry Notice</h2>
                                <?php
                                $sen_sql = mysql_query("
						SELECT a.`send_entry_notice`, a.`allow_en`
						FROM `jobs` AS j
						LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
						LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
						WHERE j.`id` ={$_GET['id']}
					");
                                $sen = mysql_fetch_array($sen_sql);
                                ?>
                                <table border=0 cellspacing=0 cellpadding=5 width='100%' class="fnt-small tnt-cert">
                                    <tr>
                                        <td style="text-align: left;">
                                            <?php
                                            if ($sen['send_entry_notice'] != 1) {

                                                echo '<span style="color:red;">Agency does not allow Emailed Entry Notice.</span> Please use the button to Display, Print and Post Entry Notice';
                                            }
                                            ?>


                                            <?php if ($row['no_en'] == 1) { ?>
                                                <p style="color:red;">This property is marked NO Entry Notice</p>
                                            <?php } else {
                                                ?>

                                                <?php
                                                if ( $sen['allow_en'] == 1 ) {

                                                    $pt_params = array(
                                                        'property_id' => $job_details['property_id'],
                                                        'active' => 1
                                                    );
                                                    $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                                                    while ($pt_row = mysql_fetch_array($pt_sql)) {

                                                        // tenant email
                                                        if (filter_var(trim($pt_row['tenant_email']), FILTER_VALIDATE_EMAIL)) {
                                                            $entrynotice_toemails[] = $pt_row['tenant_email'];
                                                            $en_tent_emails_arr[] = $pt_row['tenant_email'];
                                                        }
                                                    }

                                                    $has_tenant_email = ( count($en_tent_emails_arr) > 0 ) ? true : false;
                                                    ?>
                                                    <form action='view_job_details.php?id=<?= $job_id; ?>&doaction=emailentrynotice#emailcert<?php echo $added_param; ?>' method='post' id='email_entry_notice' name='email_entry_notice' style="float: left;">

                                                        EN Date Issued:<input type="text" class="datepicker en_date_issued_dp" value="<?php echo $crm->isDateNotEmpty($job_row['en_date_issued']) ? date("d/m/Y", strtotime($job_row['en_date_issued'])) : ''; ?>" />

                                                        <?php
                                                        if( $sen['send_entry_notice'] == 1 ){ ?>

                                                            <button type="<?php echo ( $has_tenant_email == true ) ? 'submit' : 'button'; ?>" id="email-tenants" value="tenants" class="submitbtnImg email-entry-notice <?php echo ( $has_tenant_email == true ) ? '' : 'jGreyFadedBtn'; ?>" name="email-tenants">
                                                                <img class="inner_icon" src="images/button_icons/email.png"/>
                                                                Email EN to Tenant ONLY
                                                            </button>

                                                            <button type="<?php echo ( $has_tenant_email == true ) ? 'submit' : 'button'; ?>" id="email-tenants-agency" value="tenants-agency" class="submitbtnImg email-entry-notice <?php echo ( $has_tenant_email == true ) ? '' : 'jGreyFadedBtn'; ?>" name="email-tenants-agency" style="margin-right: 4px;">
                                                                <img class="inner_icon" src="images/button_icons/email.png"/>
                                                                Email EN to Tenant+Agency
                                                            </button>

                                                        <?php
                                                        }
                                                        ?>                                                        


                                                    </form>
                                                    <?php
                                                }
                                                ?>


                                                <?php
                                                // View EN PDF
                                                $preview_en_link = 'javascript:void(0);';
                                                $preview_en_link_target = null;
                                                $preview_en_btn = 'jGreyFadedBtn';

                                                if ($crm->isDateNotEmpty($job_row['en_date_issued'])) { // if date is not empty

                                                    //$preview_en_link = "/view_entry_notice_new.php?i={$job_id}&m=".md5($agency_id.$job_id);
                                                    $encode_encrypt_job_id = rawurlencode($encrypt_decrypt->encrypt($job_id));
                                                    $preview_en_link = $crm->crm_ci_redirect(rawurlencode("/pdf/entry_notice/?job_id={$encode_encrypt_job_id}"));
                                                    $preview_en_link_target = 'target="_blank"';
                                                    $preview_en_btn = null;

                                                }
                                                ?>

                                                <!--
                                                <a href="<?php echo $preview_en_link; ?>" <?php echo $preview_en_link_target; ?>>
                                                    <button class="submitbtnImg <?php echo $preview_en_btn; ?>">
                                                        <img class="inner_icon" src="images/button_icons/pdf_white.png"/>
                                                        Display EN PDF
                                                    </button>
                                                </a>
                                                -->

                                                <a href="<?php echo $preview_en_link; ?>" <?php echo $preview_en_link_target; ?>>
                                                    <button class="submitbtnImg <?php echo $preview_en_btn; ?>">
                                                        <img class="inner_icon" src="images/button_icons/pdf_white.png"/>
                                                        Display EN PDF
                                                    </button>
                                                </a>

                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php
                                        $ene_sql = mysql_query("
								SELECT `entry_notice_emailed`
								FROM `jobs`
								WHERE `id` = {$_GET['id']}
							");
                                        $ene = mysql_fetch_array($ene_sql);
                                        ?>
                                        <td style="border-right: 1px solid #ccc; text-align: left; font-weight: normal;" colspan="100%">Last Emailed: <?php echo ($ene['entry_notice_emailed'] == null) ? "<span class='red'>Never</span>" : "<span class='green'>" . date('d/m/Y H:i', strtotime($ene['entry_notice_emailed'])) . " - See job log for additional info</span>"; ?></td>
                                        <!--<td>Last Emailed: <?php echo (empty($email_job_details['EntryNoticeLastSent']) ? "<span class='red'>Never</span>" : "<span class='green'>" . $email_job_details['EntryNoticeLastSent'] . " - See job log for additional info</span>"); ?></td>-->
                                    </tr>
                                </table>

                            </div>

                    </div>


                </div>
            </div>

            <!--- ACCOUNTS -->
            <?php
            // can edit accounts?
            $staff_perm_sql_str = "
            SELECT COUNT(`id`) AS sp_count
            FROM `staff_permissions`
            WHERE `staff_id` = {$_SESSION['USER_DETAILS']['StaffID']}
            AND `has_permission_on` = 4
            ";
            $staff_perm_sql = mysql_query($staff_perm_sql_str);
            $staff_perm_row = mysql_fetch_object($staff_perm_sql);
            $can_edit_account = ( $staff_perm_row->sp_count > 0 )?true:false;
            ?>
            <div class="c-tab accounts_tab_div"  data-tab_cont_name="accounts">
                <div class="c-tab__content">


                    <div class="invoice_details_div jfloatleft" style="margin-right: 50px;">
                        <h2 class="heading">Invoice Details</h2>
                        <table class="table-vw-job tbl-fr-red fnt-small invoice_details_tbl" style="border:none;">
                            <tr>
                                <td class="col1">Address</td>
                                <td><?php echo $prop_add; ?></td>
                            </tr>
                            <tr>
                                <td class="col1">Agency Name</td>
                                <td><?php echo $agency_name; ?></td>
                            </tr>
                            <tr>
                                <td class="col1">Job Type</td>
                                <td><?php echo $job_type; ?></td>
                            </tr>
                            <tr>
                                <td class="col1">Invoice Date</td>
                                <td><?php echo ( $crm->isDateNotEmpty($job_date) ) ? date('d/m/Y', strtotime($job_date)) : ''; ?></td>
                            </tr>
                            <tr>
                                <td class="col1">Due Date</td>
                                <td><?php echo ( $crm->isDateNotEmpty($job_date) ) ? date('d/m/Y', strtotime($job_date . "+30 days")) : ''; ?></td>
                            </tr>
                            <tr>
                                <td class="col1">Invoice Amount</td>
                                <td>
                                    <strong>$<?php echo number_format($invoice_amount, 2); ?></strong>
                                </td>
                            </tr>
                            <tr style="border: 2px solid #cccccc !important;">
                                <td class="col1"><strong>Balance</strong></td>
                                <td>
                                    <strong class="<?php echo ( $invoice_balance > 0 ) ? 'colorItRed' : ''; ?>">$<span class="invoice-balance"><?php echo number_format($invoice_balance, 2) ?></span></strong>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- PAYMENTS -->
                    <?php
                    // get payments
                    $params = array(
                        'job_id' => $job_id,
                        'sort_list' => array(
                            array(
                                'order_by' => 'ip.`created_date`',
                                'sort' => 'ASC'
                            )
                        ),
                        'echo_query' => 0
                    );
                    $payments_sql = $crm->getInvoicePaymentsData($params);
                    ?>
                    <div class="payment_details_main_div jfloatleft" style="margin-right: 50px;">
                        <h2 class="heading">Payments</h2>
                        <table class="table-vw-job tbl-fr-red fnt-small payment_details_tbl" style="border:none;">

                            <thead>
                                <tr>
                                    <td>Date</td>
                                    <td>Amount</td>
                                    <td>Type</td>
                                    <td>Payment Reference</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </thead>

                            <?php
                            // IMMPORTANT!!! any changes made, update the js for when adding new row
                            while ($payment = mysql_fetch_array($payments_sql)) {
                                ?>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker payment_date pd_fields" value="<?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?>" />
                                            <input type="hidden" class="ip_id" value="<?php echo $payment['invoice_payment_id']; ?>" />
                                            <input type="hidden" class="edited" value="0" />
                                        </td>
                                        <td>
                                            <input style="width: 60px;" type="text" id="amount_paid" class="addinput vw-jb-inpt amount_paid pd_fields" value="<?php echo $payment['amount_paid']; ?>" />
                                            <input type="hidden" class="addinput vw-jb-inpt orig_amount_paid" value="<?php echo $payment['amount_paid']; ?>" />
                                        </td>
                                        <td>
                                            <?php
                                            $pt_sql = $crm->getPaymentTypes();
                                            ?>
                                            <select style="width: 105px;" class="type_of_payment pd_fields" />
                                <option value="">--- Select ---</option>
                                <?php while ($pt = mysql_fetch_array($pt_sql)) { ?>
                                    <option value="<?php echo $pt['payment_type_id']; ?>" <?php echo ( $pt['payment_type_id'] == $payment['type_of_payment'] ) ? 'selected="selected"' : ''; ?>><?php echo $pt['pt_name'] ?></option>
                                    <?php
                                }
                                ?>
                                </select>
                                </td>
                                <td><input type="text" class="addinput pd_fields payment_reference vw-jb-inpt"  value="<?php echo $payment['payment_reference'] ?>" ></td>
                                <td>
                                    <?php
                                    // only global and full access can delete invoice payments
                                    if( ( $_SESSION['USER_DETAILS']['ClassID'] == 2 || $_SESSION['USER_DETAILS']['ClassID'] == 9 || $_SESSION['USER_DETAILS']['ClassID'] == 10 ) && $can_edit_account == true ){ ?>
                                        <button type="button" class="submitbtnImg jfloatright btn_delete_pd">
                                            <img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </td>
                                </tr>
                                </tbody>
                                <?php
                            }
                            ?>


                        </table>

                        <?php                      
                        // staff has permission to perform actions
                        if( $can_edit_account == true ){                            
                        ?>
                            <button type="button" id="save_payment_details_btn" class="submitbtnImg jfloatright" style="margin: 10px 0;">
                                <img class="inner_icon" src="images/button_icons/save-button.png">
                                <span class="inner_icon_txt">SAVE</span>
                            </button>

                            <?php //if( $invoice_balance > 0 ){ ?>
                            <button type="button" id="add_payment_btn" class="green-btn submitbtnImg jfloatright" style="margin: 10px 10px 10px 0;">
                                <img class="inner_icon" src="images/button_icons/add-button.png">
                                <span class="inner_icon_txt">PAYMENT</span>
                            </button>
                        <?php
                            //}
                        }
                        ?>


                    </div>






                    <!-- REFUND -->
                    <?php
                    // get payments
                    $params = array(
                        'job_id' => $job_id,
                        'sort_list' => array(
                            array(
                                'order_by' => 'ir.`created_date`',
                                'sort' => 'ASC'
                            )
                        ),
                        'echo_query' => 0
                    );
                    $refund_sql = $crm->getInvoiceRefundsData($params);
                    ?>
                    <div class="refund_details_main_div jfloatleft" style="margin-right: 50px;">
                        <h2 class="heading">Refunds</h2>
                        <table class="table-vw-job tbl-fr-red fnt-small refund_details_tbl" style="border:none;">

                            <thead>
                                <tr>
                                    <td>Date</td>
                                    <td>Amount</td>
                                    <td>Type</td>
                                    <td>Payment Reference</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </thead>

                            <?php
                            // IMMPORTANT!!! any changes made, update the js for when adding new row
                            while ($refund = mysql_fetch_array($refund_sql)) {
                                ?>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker payment_date pd_fields" value="<?php echo date('d/m/Y', strtotime($refund['payment_date'])); ?>" />
                                            <input type="hidden" class="ir_id" value="<?php echo $refund['invoice_refund_id']; ?>" />
                                            <input type="hidden" class="edited" value="0" />
                                        </td>
                                        <td>
                                            <input style="width: 60px;" type="text" id="amount_paid" class="addinput vw-jb-inpt amount_paid pd_fields" value="<?php echo $refund['amount_paid']; ?>" />
                                            <input type="hidden" class="addinput vw-jb-inpt orig_amount_paid" value="<?php echo $refund['amount_paid']; ?>" />
                                        </td>
                                        <td>
                                            <?php
                                            $pt_sql = $crm->getPaymentTypes();
                                            ?>
                                            <select style="width: 105px;" class="type_of_payment pd_fields" />
                                <option value="">--- Select ---</option>
                                <?php
                                while ($pt = mysql_fetch_array($pt_sql)) {
                                    // only EFT and cheque
                                    if (
                                            $pt['payment_type_id'] == 3 ||
                                            $pt['payment_type_id'] == 5 ||
                                            $pt['payment_type_id'] == 6
                                    ) {
                                        ?>
                                        <option value="<?php echo $pt['payment_type_id']; ?>" <?php echo ( $pt['payment_type_id'] == $refund['type_of_payment'] ) ? 'selected="selected"' : ''; ?>><?php echo $pt['pt_name'] ?></option>
                                        <?php
                                    }
                                }
                                ?>
                                </select>
                                </td>
                                <td><input type="text" class="addinput pd_fields payment_reference vw-jb-inpt"  value="<?php echo $refund['payment_reference'] ?>" ></td>
                                <td>
                                    <?php
                                    // only global and full access can delete invoice payments
                                    if( ( $_SESSION['USER_DETAILS']['ClassID'] == 2 || $_SESSION['USER_DETAILS']['ClassID'] == 9 || $_SESSION['USER_DETAILS']['ClassID'] == 10 ) && $can_edit_account == true ){
                                    ?>
                                        <button type="button" class="submitbtnImg jfloatright btn_delete_ir">
                                            <img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />
                                        </button>
                                    <?php 
                                    } 
                                    ?>
                                </td>
                                </tr>
                                </tbody>
                                <?php
                            }
                            ?>


                        </table>


                        <?php
                        // staff has permission to perform actions
                        if( $can_edit_account == true ){ ?>

                            <button type="button" id="save_refund_details_btn" class="submitbtnImg jfloatright" style="margin: 10px 0;">
                                <img class="inner_icon" src="images/button_icons/save-button.png">
                                <span class="inner_icon_txt">SAVE</span>
                            </button>


                            <button type="button" id="add_refund_btn" class="green-btn submitbtnImg jfloatright" style="margin: 10px 10px 10px 0;">
                                <img class="inner_icon" src="images/button_icons/add-button.png">
                                <span class="inner_icon_txt">REFUND</span>
                            </button>

                        <?php
                        }
                        ?>                        


                    </div>










                    <div id="credits_div" class="jfloatleft">


                        <?php
                        // get credits
                        $params = array(
                            'job_id' => $job_id,
                            'join_table' => array(
                                'created_by_who',
                                'approved_by'
                            ),
                            'sort_list' => array(
                                array(
                                    'order_by' => 'ic.`created_date`',
                                    'sort' => 'ASC'
                                )
                            ),
                            'echo_query' => 0
                        );
                        $credit_sql = $crm->getInvoiceCreditsData($params);
                        ?>

                        <h2 class="heading">Credits</h2>

                        <div id="credits_div_id">


                            <table class="table-vw-job tbl-fr-red fnt-small credits_tbl" style="border:none;">

                                <thead>
                                    <tr>
                                        <td>Date</td>
                                        <td>Amount</td>
                                        <td>Reason for Credit</td>
                                        <td>Approved by</td>
                                        <td>Payment Reference</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </thead>

                                <?php
                                // IMMPORTANT!!! any changes made, update the js for when adding new row
                                while ($credit = mysql_fetch_array($credit_sql)) {
                                    ?>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker credit_date" value="<?php echo ( $crm->isDateNotEmpty($credit['credit_date']) == true ) ? date('d/m/Y', strtotime($credit['credit_date'])) : date('d/m/Y'); ?>" />
                                                <input type="hidden" class="addinput vw-jb-inpt credit_id" value="<?php echo $credit['invoice_credit_id']; ?>" />
                                            </td>
                                            <td>
                                                <input style="width: 60px;" type="text" class="addinput vw-jb-inpt credit_paid" value="<?php echo $credit['credit_paid']; ?>" />
                                                <input type="hidden" class="addinput vw-jb-inpt orig_credit_paid" value="<?php echo $credit['credit_paid']; ?>" />
                                            </td>
                                            <td>
                                                <select class="credit_reason">
                                                    <option value="">--- Select ---</option>
                                                    <?php
                                                    $credit_reason_sql = $crm->getCreditReason();
                                                    while ($cr_row = mysql_fetch_array($credit_reason_sql)) {
                                                        ?>
                                                        <option value="<?php echo $cr_row['credit_reason_id'] ?>" <?php echo ( $credit['credit_reason'] == $cr_row['credit_reason_id'] ) ? 'selected="selected"' : ''; ?>><?php echo $cr_row['reason'] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <select style="width: 140px;" class="approved_by">
                                                    <option value="">--- Select ---</option>
                                                    <?php
                                                    // for global and full access
                                                    $staff_sql = mysql_query("
										SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
										FROM staff_accounts AS sa
										INNER JOIN `country_access` AS ca ON (
											sa.`StaffID` = ca.`staff_accounts_id`
											AND ca.`country_id` ={$_SESSION['country_default']}
										)
										WHERE sa.deleted =0
										AND sa.active =1
										ORDER BY sa.`FirstName`
										");
                                                    while ($staff = mysql_fetch_array($staff_sql)) {
                                                        ?>
                                                        <option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ( $staff['staff_accounts_id'] == $credit['approved_by'] ) ? 'selected="selected"' : ''; ?>><?php echo $staff['FirstName'] . ' ' . $staff['LastName']; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td><input type="text" class="addinput pd_fields payment_reference vw-jb-inpt"  value="<?php echo $credit['payment_reference'] ?>" ></td>
                                            <td>
                                                <?php
                                                // only global and full access can delete invoice payments
                                                if( ( $_SESSION['USER_DETAILS']['ClassID'] == 2 || $_SESSION['USER_DETAILS']['ClassID'] == 9 || $_SESSION['USER_DETAILS']['ClassID'] == 10 ) && $can_edit_account == true ){
                                                ?>
                                                    <button type="button" class="submitbtnImg jfloatright btn_delete_credit">
                                                        <img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />
                                                    </button>
                                                <?php 
                                                } 
                                                ?>                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                    <?php
                                }
                                ?>

                            </table>


                            <?php
                            // staff has permission to perform actions
                            if( $can_edit_account == true ){ ?>

                                <button type="button" id="save_credit_details_btn" class="submitbtnImg jfloatright" style="margin: 10px 0;">
                                    <img class="inner_icon" src="images/button_icons/save-button.png">
                                    <span class="inner_icon_txt">SAVE</span>
                                </button>


                                <button type="button" id="add_credit_btn" class="green-btn submitbtnImg jfloatright" style="margin: 10px 10px 10px 0;">
                                    <img class="inner_icon" src="images/button_icons/add-button.png">
                                    <span class="inner_icon_txt">CREDITS</span>
                                </button>

                            <?php
                            }
                            ?>                            

                            <?php
                               ## $pdf_link_str = URL.'view_credit_new.php?i='.$job_id.'&m='.md5($agency_id.$job_id); ##replaced below
                                $encode_encrypt_job_id2 = rawurlencode($encrypt_decrypt->encrypt($job_id));
                                $pdf_link_str = $crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_job_id2}"));
                            ?>
                            <button type="button" id="" class="submitbtnImg jfloatright" style="margin: 10px 10px;">
                                <a href="<?=$pdf_link_str?>" target="_blank">
                                    <img class="inner_icon" src="images/button_icons/pdf_white.png">
                                </a>
                            </button>




                            <input type="hidden" class="credit_id" value="<?php echo $credit['invoice_credit_id']; ?>" />

                        </div>




                    </div>

                    <div style="clear:both;"></div>


                    <!-- Unpaid -->
                    <div style="text-align: left;">
                        <input type="checkbox" id="unpaid_chk" <?php echo ( $job_row['unpaid'] == 1 ) ? 'checked="checked"' : null; ?> /> Unpaid ( if Ticked, this job will show on Debtors report and Agency portal as Unpaid until invoice Balance = $0 )
                    </div>

                    <!--- ACCOUNT NOTES --->
                    <h2 class="heading" style="margin-top: 40px;">Accounts Notes</h2>
                    <table border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">
                        <tr class="tgt-ag-bl" style="background-color:#eeeeee;">
                            <td>

                                <div class="jl_div">
                                    <label for="eventdate">Date</label><br />
                                    <input type="text" style="width: 80px;" value="<?php echo date('d/m/Y'); ?>" class="addinput datepicker al_date"  />
                                </div>



                                <div class="jl_div" style="width: 79%;">
                                    <label for="comments">Comment</label><br />
                                    <input class="addinput al_comment" style="width: 100%; padding: 0;" />
                                </div>


                                <div class="jl_div">
                                    <label for="add_event"></label><br />
                                    <button id='btn_add_accounts_log' class="submitbtnImg" style="background-color:#<?php echo $serv_color; ?>;color:#ffffff">
                                        <img class="inner_icon" src="images/button_icons/add-button.png"/>
                                        Add Event
                                    </button>
                                </div>

                            </td>
                        </tr>
                    </table>
                    <table border="0" cellpadding="5" cellspacing="1" class="table-left">
                        <tr bgcolor=#<?php echo $serv_color; ?>>
                            <td class='colorwhite bold'>Date</td>
                            <td class='colorwhite bold'>Time</td>
                            <td class='colorwhite bold'>Type</td>
                            <td class='colorwhite bold'>Who</td>
                            <td class='colorwhite bold'>Comments</td>
                        </tr>
                        <?php
                        // get accounts job log
                        $acc_job_log_sql = mysql_query("
					SELECT *
					FROM `job_log` AS jl
					LEFT JOIN `staff_accounts` AS sa ON jl.`staff_id` = sa.`StaffID`
					WHERE jl.`log_type` = 2
					AND jl.`job_id` = {$job_id}
					ORDER BY `created_date` DESC
				");
                        while ($jl = mysql_fetch_array($acc_job_log_sql)) {
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($jl['created_date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($jl['created_date'])); ?></td>
                                <td><?php echo $jl['contact_type']; ?></td>
                                <td><?php echo $crm->formatStaffName($jl['FirstName'], $jl['LastName']); ?></td>
                                <td><?php echo $jl['comments']; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
					</table>


				<?php
			    //if( strpos($_SERVER['SERVER_NAME'],"crmdev") !== false ){

				// NEW ACCOUNT LOGS
				// pagination
				$pagi_offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
				$pagi_limit = 50;

				$this_page = $_SERVER['PHP_SELF'];
				$pagi_params = "&{$_SERVER['QUERY_STRING']}";

				$next_link = "{$this_page}?offset=".($pagi_offset+$pagi_limit).$pagi_params;
				$prev_link = "{$this_page}?offset=".($pagi_offset-$pagi_limit).$pagi_params;

				// NEW LOGS TABLE
				// paginate
				$params = array(
					'custom_select' => '
						l.`log_id`,
						l.`created_date`,
						l.`title`,
						l.`details`,

						ltit.`title_name`,

						aua.`fname`,
						aua.`lname`,

						sa.`StaffID`,
						sa.`FirstName`,
						sa.`LastName`
					',
					'job_id' => $job_id,
					'display_in_accounts' => 1,
					'deleted' => 0,
					'paginate' => array(
						'offset' => $pagi_offset,
						'limit' => $pagi_limit
					),
					'sort_list' => array(
						array(
							'order_by' => 'l.`created_date`',
							'sort' => 'DESC'
						)
					),
					'echo_query' => 0
				);
				$result = $crm->getNewLogs($params);
				// all row
				$params = array(
					'custom_select' => '
						l.`log_id`
					',
					'job_id' => $job_id,
					'display_in_accounts' => 1,
					'deleted' => 0
				);
				$ptotal = mysql_num_rows($crm->getNewLogs($params));
				?>

				<h2 class="heading">New Account Notes</h2>
				<table style="border:1px solid #cccccc !important;" border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">
					<tr bgcolor="#<?php echo $serv_color ?>">
						<td class='colorwhite bold'>Date</td>
						<td class='colorwhite bold'>Time</td>
						<td class='colorwhite bold'>Title</td>
						<td class='colorwhite bold'>Who</td>
						<td class='colorwhite bold' style="width: 53%;">Details</td>
					</tr>
					<?php
					// (3) While there are still rows in the result set,
					// fetch the current row into the array $row
					while ($row = mysql_fetch_array($result)){ ?>
						<tr class="border-none">
							<td>
								<?php echo date('d/m/Y',strtotime($row['created_date'])); ?>
							</td>
							<td>
								<?php echo date('H:i',strtotime($row['created_date'])); ?>
							</td>
							<td>
								<?php echo $row['title_name']; ?>
							</td>
							<td>
								<?php
								if( $row['StaffID'] != '' ){ // sats staff
									echo $crm->formatStaffName($row['FirstName'],$row['LastName']);
								}else{ // agency portal users
									echo "{$row['fname']} {$row['lname']}";
								}
								?>
							</td>
							<td>
								<?php
								$params = array(
									'log_details' => $row['details'],
									'log_id' => $row['log_id']
								);
								echo $crm->parseDynamicLink($params);
								?>
							</td>
						</tr>
					<?php
					} ?>
				</table>

			<?php
			//}
			?>


			<?php

			// Initiate pagination class
			$jp = new jPagination();

			$per_page = $pagi_limit;
			$page = ($_GET['page']!="")?$_GET['page']:1;
			$pagi_offset = ($_GET['offset']!="")?$_GET['offset']:0;

			echo $jp->display($page,$ptotal,$per_page,$pagi_offset,$pagi_params);

			?>


                </div>
            </div>



        </div>




        <script src="js/responsive_tabs.js"></script>
        <script>
                                                    var myTabs = tabs({
                                                        el: '#tabs',
                                                        tabNavigationLinks: '.c-tabs-nav__link',
                                                        tabContentContainers: '.c-tab'
                                                    });

                                                    myTabs.init();
        </script>






    </div>

    </div>

    <br class="clearfloat" />


    <a id="sent_email_lb_link" href="#email_temp_lb_div" style='display:none;'>here</a>
    <div style="display:none;">
        <div id="email_temp_lb_div">
            <table class="table">
                <tr>
                    <td class="td_lbl">From:</td><td class="prev_et_from"></td>
                </tr>
                <tr>
                    <td class="td_lbl">To:</td><td class="prev_et_to"></td>
                </tr>
                <tr>
                    <td class="td_lbl">CC:</td><td class="prev_et_cc"></td>
                </tr>
                <tr>
                    <td class="td_lbl">Subject:</td><td class="prev_et_subj"></td>
                </tr>
                <tr>
                    <td class="td_lbl prev_et_body_lbl">Body:</td><td class="prev_et_body"></td>
                </tr>
            </table>
        </div>
    </div>


<?php } else {
    ?>

    <div id="permission_error" class="property_deleted_error">This job property is deleted</div>

    <?
}
?>

<div style="display:none;">
    <div id="job_price_variation_fb">
        <form id="add_job_variation_form">
        <table id="job_price_variation_table">
            <tr id="make_ym_tr">
                <th>Make YM</th>
                <td>
                        <input type="checkbox" id="make_ym">
                        <?php
                        $price_var_params = array(
                            'service_type' => $service,
                            'property_id' => $property_id
                        );
                        $price_var_arr = $crm->get_property_price_variation($price_var_params);
                        ?>
                        <input type="hidden" id="ppv_price" value="<?php echo $price_var_arr['dynamic_price_total']; ?>" />
                </td>
            </tr>
            <tr>
                <th id="amount_th">Variation Amount</th>
                <td><input type="text" name="job_variation_amount" id="job_variation_amount" value="<?php echo ( $jv_row->amount > 0 )?number_format($jv_row->amount, 2):null; ?>" required /></td>
            </tr>
            <tr id="apv_type_tr">
                <th>Type</th>
                <td>
                    <select id="apv_type" style="width:100%;" required>
                        <option value="">---</option>
                        <option value="1" <?php echo ( $jv_row->type == 1 )?'selected':null; ?>>Discount</option>
                        <option value="2" <?php echo ( $jv_row->type == 2 )?'selected':null; ?>>Surcharge</option>
                    </select>
                </td>
            </tr>
            <tr id="apv_reason_tr">
                <th>Reason</th>
                <td>                   
                    <select id="apv_reason" name="apv_reason"  class="form-control apv_reason" required>
                        <option value="">---</option>
                        <?php
                        $adr_sql = mysql_query("
                        SELECT *
                        FROM `agency_price_variation_reason`
                        WHERE `active` = 1										
                        ORDER BY `reason` ASC
                        ");
                        while( $adr_row = mysql_fetch_object($adr_sql) ){ ?>                           
                            <option data-is_discount="<?php echo $adr_row->is_discount; ?>" value="<?php echo $adr_row->id; ?>" <?php echo ( $adr_row->id == $jv_row->reason )?'selected':null; ?>><?php echo $adr_row->reason; ?></option>
                        <?php
                        }
                        ?>                        
                    </select>
                </td>
            </tr>
            <?php
            // get display on
            $dv_type = 2; // job
            $dv_sql = mysql_query("
            SELECT dv.`display_on`
            FROM `display_variation` AS dv
            LEFT JOIN `job_variation` AS jv ON ( dv.`variation_id` = jv.`id` && dv.`type` = $dv_type )   
            WHERE jv.`job_id` = {$job_id}    
            AND jv.`active` = 1
            ");
            $dv_row = mysql_fetch_object($dv_sql);
            ?>
            <tr id="display_on_tr">
                <th>Display On</th>
                <td>                   
                    <select id="display_on" name="display_on"  class="form-control display_on">
                        <option value="">Do Not Display</option>
                        <?php
                        $display_on_sql = mysql_query("
                        SELECT *
                        FROM `display_on`
                        WHERE `id` IN(3,6,7)
                        AND `active` = 1									
                        ORDER BY `location` ASC
                        ");
                        while( $display_on_row = mysql_fetch_object($display_on_sql) ){ ?>                           
                            <option value="<?php echo $display_on_row->id; ?>" <?php echo ( $display_on_row->id == $dv_row->display_on )?'selected':null; ?>><?php echo $display_on_row->location; ?></option>
                        <?php
                        }
                        ?>                        
                    </select>
                </td>
            </tr>
            <?php
            // can edit price?
            $staff_perm_sql_str = "
            SELECT COUNT(`id`) AS sp_count
            FROM `staff_permissions`
            WHERE `staff_id` = {$_SESSION['USER_DETAILS']['StaffID']}
            AND `has_permission_on` = 3
            ";
            $staff_perm_sql = mysql_query($staff_perm_sql_str);
            $staff_perm_row = mysql_fetch_object($staff_perm_sql);
            $can_edit_price = ( $staff_perm_row->sp_count > 0 )?true:false;

            // staff has permission to perform actions
            if( 
                ( $job_row['jstatus'] != 'Merged Certificates' && $job_row['jstatus'] != 'Completed' ) ||
                ( ( $job_row['jstatus'] == 'Merged Certificates' || $job_row['jstatus'] == 'Completed' ) && $can_edit_price == true )
            ){ ?>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="hidden" id="jv_id" value="<?php echo $jv_row->id; ?>" />
                    <button type='submit' id="update_job_price_variation" class='submitbtnImg'>Update</button>
                    <?php
                    if( mysql_num_rows($jv_sql) > 0 ){ ?>
                        <button type='button' id="delete_job_price_variation" class='submitbtnImg'>Delete</button>
                    <?php
                    }
                    ?>                    
                </td>
            </tr>
            <?php
            }
            ?> 
        </table>    
        </form>    
    </div>    
</div>

<div style="display:none;">

	<div id="confirm_discard_ss_fb">

        <p>This will discard this safety switch. Please select reason for discarding.</p>

        <select class='form-control' id='ss_discard_reason'>
            <option value=''>---</option>
            <?php
            // get safety switch reason
            $ss_reason_sql = mysql_query("
            SELECT 
                `ss_res_id`,    
                `reason`                        
            FROM `safety_switch_reason`
            ");
            while( $ss_reason_row =  mysql_fetch_object($ss_reason_sql) ){ ?>
                <option value='<?php echo $ss_reason_row->ss_res_id; ?>'><?php echo $ss_reason_row->reason; ?></option>
            <?php
            }
            ?>             
        </select>
        <input type="hidden" id="discard_ss_id" />

        
        <div style="margin-top: 5px;">
            <button type="button" id="confirm_discard_yes_btn" class="submitbtnImg blue-btn" style="margin-right: 5px;">Yes</button>
            <button type="button" id="confirm_discard_no_btn" class="submitbtnImg">Cancel</button>            
        </div>

    </div>

</div>

<style>
    .jred_border_higlight{
        border: 1px solid #b4151b;box-shadow: 0 0 2px #b4151b inset;
    }
    .grey-btn{
        background-color: #dedede;
    }
    .fadeIt{
        opacity: 0.5;
    }
    #inner_new_tenants_tbl tr:first-child,
    .property_api_tbl tr:first-child {
        border: 0px !important;
    }
    .accounts_tab_div .table-vw-job tr{
        border: 0px !important;
    }
    .accounts_tab_div .table-vw-job td.col1{
        width: 100px;
    }
    .c-tab__content{
        height: auto;
    }
    .pme_btn_color{
        background-color: #14cdeb !important;
        border-color: #14cdeb !important;

    }
    .property_deleted_error {
        position: absolute;
        top: 62px;
        width: 100%;
    }
    #inner_new_tenants_tbl .tr_rightBorder td:last-child {
        border-right: unset !important;
    }
    #apv_discount_reason,
    #apv_surcharge_reason{
        width: 100%;
    }
    .source_of_company {float:left;margin: 15px 0 0 10px;}
</style>

<script>

    function getBookedWith() {

        var booked_with = jQuery("#booked_with").val();
        var allowShowPdf = false;
        var booked_with_name;

        jQuery(".tenant_fname_field").each(function () {



            if (booked_with == jQuery(this).val()) {
                allowShowPdf = true;
                ten_email = jQuery(this).parents(".vw-pro-dtl-tn-hld").find(".tenant_email_field").val();
                ten_lname = jQuery(this).parents(".vw-pro-dtl-tn-hld").find(".tenant_lname_field").val();
                booked_with_name = booked_with + " " + ten_lname;
                jQuery("#booked_with_email").val(ten_email);
                jQuery("#booked_with_name").val(booked_with_name);
                //jQuery("#link_entry_notice_yes").attr("href",'/view_job_details.php?id=<?php echo $_GET['id']; ?>&entry_notice=yes&booked_with='+booked_with_name+'&booked_with_email='+ten_email);
                //jQuery("#link_booked_with_show_pdf").attr("href",'/view_entry_notice_booked_with.php?job_id=<?php echo $_GET['id']; ?>&booked_with='+booked_with_name);
            }

        });

        if (booked_with == 'Agent') {

            allowShowPdf = true;

            jQuery(".tenant_fname_field").each(function () {

                if (jQuery(this).val() != "") {
                    ten_email = jQuery(this).parents(".vw-pro-dtl-tn-hld").find(".tenant_email_field").val();
                    ten_lname = jQuery(this).parents(".vw-pro-dtl-tn-hld").find(".tenant_lname_field").val();
                    booked_with_name = jQuery(this).val() + " " + ten_lname;
                    jQuery("#booked_with_email").val(ten_email);
                    jQuery("#booked_with_name").val(booked_with_name);
                    //jQuery("#link_entry_notice_yes").attr("href",'/view_job_details.php?id=<?php echo $_GET['id']; ?>&entry_notice=yes&booked_with='+booked_with_name+'&booked_with_email='+ten_email);
                    //jQuery("#link_booked_with_show_pdf").attr("href",'/view_entry_notice_booked_with.php?job_id=<?php echo $_GET['id']; ?>&booked_with='+booked_with_name);
                }

            });

        }



    }



    function formatJsDate(day, month, year) {

        var jdate;

        if (day < 10) {
            day = '0' + day;
        }
        if (month < 10) {
            month = '0' + month;
        }
        return jdate = day + '/' + month + '/' + year;

    }


    function getTechNotesFromTickBox() {

        var jen_check = jQuery("#job_entry_notice").prop("checked");
        var jp_check = jQuery("#job_priority").prop("checked");
        var tech_notes_txt = '';

        jen_check_txt = (jen_check == true) ? 'EN - KEYS ' : '';
        jp_check_txt = (jp_check == true) ? 'DO NOT CANCEL ' : '';

        return tech_notes_txt = jen_check_txt + jp_check_txt;

    }

    function unpaid_check_action() {
            var job_id = <?php echo $job_id; ?>;
            var unpaid = ($('#unpaid_chk').prop("checked") == true) ? 1 : 0;

            if (parseInt(job_id) > 0) {

                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_toggle_unpaid_marker.php",
                    data: {
                        job_id: job_id,
                        unpaid: unpaid
                    }
                }).done(function (ret) {
                    jQuery("#load-screen").hide();
                    //window.location="view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });

            }
        }


    function display_dynamic_variation_reason(apv_type){

        if( apv_type == 1 ){ // discount

            jQuery("#apv_reason option[data-is_discount=1]").show(); // discount
            jQuery("#apv_reason option[data-is_discount=0]").hide(); // surcharge   
            
            
            <?php 
            if( mysql_num_rows($dv_sql) == 0 ){ ?>
                jQuery("#display_on option[value='7']").prop("selected",true); // Invoice & Agency Portal
            <?php
            }
            ?>            
            
        }else{ // surcharge

            jQuery("#apv_reason option[data-is_discount=1]").hide(); // discount
            jQuery("#apv_reason option[data-is_discount=0]").show(); // surcharge 

            <?php 
            if( mysql_num_rows($dv_sql) == 0 ){ ?>
                jQuery("#display_on option[value='']").prop("selected",true); // Do Not Display
            <?php
            }
            ?>                        

        }

    }

    jQuery(window).load(function () {
        /*
         *   Set unpaid balance checkbox to "uncheck" if balance is zero, otherwise, "checked"
         *
         */
        var invoice_balance = parseFloat($('span.invoice-balance').text());
        var chkUnpaid = $('#unpaid_chk:checked').length;
        if (invoice_balance === 0) {
            if (chkUnpaid === 1) {
                $('#unpaid_chk').removeAttr("checked");
                unpaid_check_action();
            }
        }
//        else {
//            if (chkUnpaid === 0) {
//                $('#unpaid_chk').attr("checked","checked");
//                unpaid_check_action();
//            }
//        }

        // Set session user_id in booked_by
        var staff_id = <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>;
        if ($("#job_status").val() == "To Be Booked") {
            $("#booked_by").val(staff_id);
        }

    });



    jQuery(document).ready(function () {

        // display dynamic variation reason, on load
        var apv_type = jQuery("#apv_type").val();
        display_dynamic_variation_reason(apv_type);

        jQuery("#apv_type").change(function(){

            var apv_type = jQuery(this).val();

            display_dynamic_variation_reason(apv_type);

        });

        // show all PMe tenants
        jQuery("#view_all_pme_tnt_btn").click(function () {

            var obj = jQuery(this);
            var btn_txt = obj.find(".inner_icon_txt").html();
            var orig_btn_txt = 'Show';

            if (btn_txt == orig_btn_txt) {
                jQuery("#pme_tnt_tbl tr.PMe_tenant_exist_bg").show();
                obj.find(".inner_icon_txt").html("Hide");
            } else {
                jQuery("#pme_tnt_tbl tr.PMe_tenant_exist_bg").hide();
                obj.find(".inner_icon_txt").html(orig_btn_txt);
            }

        });

        // show all PMe tenants
        jQuery("#view_all_tick_column").click(function () {

            var obj = jQuery(this);
            var btn_txt = obj.find(".inner_icon_txt").html();
            var orig_btn_txt = 'Hide';
            if (btn_txt == orig_btn_txt) {
                $('#inner_new_tenants_tbl tr td:nth-child(6)').nextAll().hide();
                obj.html('&#10004; <span class="inner_icon_txt">Show</span>');
                // obj.find(".inner_icon_txt").html("Show");
            } else {
                $('#inner_new_tenants_tbl tr td:nth-child(6)').nextAll().show();
                obj.html('&#10006; <span class="inner_icon_txt">'+orig_btn_txt+'</span>');
                // obj.find(".inner_icon_txt").html(orig_btn_txt);
            }

        });


        // add new Pme tenants
        jQuery('.add_new_pme_tenant_btn').click(function () {

            var new_t_fname = jQuery(this).parents("td:first").find(".pme_tenant_fname").val();
            var new_t_lname = jQuery(this).parents("td:first").find(".pme_tenant_lname").val();
            var new_t_mobile = jQuery(this).parents("td:first").find(".pme_tenant_mobile").val();
            var new_t_landline = jQuery(this).parents("td:first").find(".pme_tenant_landline").val();
            var new_t_email = jQuery(this).parents("td:first").find(".pme_tenant_email").val();
            var pme_api_txt = jQuery(this).parents("td:first").find(".pme_api_txt").val();

            var errorMsg = "";

            if (confirm("Are you sure you want to add "+pme_api_txt+" tenant?")) {

                if (new_t_fname == "") {
                    errorMsg += "Please Enter First Name \n";
                }

                if (errorMsg != "") {
                    alert(errorMsg);
                    return false;
                }

                jQuery.ajax({
                    url: 'ajax_function_tenants.php?f=newTenant',
                    type: 'POST',
                    data: {
                        'property_id': <?php echo $property_id ?>,
                        'tenant_firstname': new_t_fname,
                        'tenant_lastname': new_t_lname,
                        'tenant_mobile': new_t_mobile,
                        'tenant_landline': new_t_landline,
                        'tenant_email': new_t_email,
                        'active': 1
                    }
                }).done(function (ret) {
                    //window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    location.reload();
                });

            }


        });


        // unpaid marker
        jQuery(".en_date_issued_dp").change(function () {

            var job_id = <?php echo $job_id; ?>;
            var en_date_issued = jQuery(this).val();

            if (parseInt(job_id) > 0) {

                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_update_en_date_issued.php",
                    data: {
                        job_id: job_id,
                        en_date_issued: en_date_issued
                    }
                }).done(function (ret) {
                    jQuery("#load-screen").hide();
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });

            }

        });

        jQuery("#make_ym").change(function(){

            var make_ym_dom = jQuery(this);
            var job_variation_amount = jQuery("#job_variation_amount");
            
            if( make_ym_dom.prop("checked") == true ){ // ticked, make type and reason not required

                jQuery("#amount_th").text('Job Price');

                var ppv_price = jQuery("#ppv_price").val();
                job_variation_amount.val(ppv_price);
                job_variation_amount.prop("readonly",true);

                jQuery("#apv_type").prop("required",false);                
                jQuery("#apv_reason").prop("required",false);
                jQuery("#display_on").prop("required",false);

                // hide row
                jQuery("#apv_type_tr").hide();
                jQuery("#apv_reason_tr").hide();
                jQuery("#display_on_tr").hide();

            }else{ // not ticked, put back type and reason as required

                jQuery("#amount_th").text('Variation Amount');

                job_variation_amount.val("");
                job_variation_amount.prop("readonly",false);

                jQuery("#apv_type").prop("required",true);
                jQuery("#apv_reason").prop("required",true);
                jQuery("#display_on").prop("required",true);

                // show row
                jQuery("#apv_type_tr").show();
                jQuery("#apv_reason_tr").show();
                jQuery("#display_on_tr").show();
                
            }            

        });

        // update job price variation
        jQuery("#add_job_variation_form").submit(function (e) {

            e.preventDefault();

            var job_id = <?php echo $job_id; ?>;
            
            var make_ym = ( jQuery("#make_ym").prop("checked") == true )?1:0;
            var job_var_amount = jQuery("#job_variation_amount").val();
            var job_var_type = jQuery("#apv_type").val();
            var job_var_type_text = jQuery("#apv_type option:selected").text();
           
            var apv_reason = jQuery("#apv_reason").val();
            var apv_reason_text = jQuery("#apv_reason option:selected").text();

            var display_on = jQuery("#display_on").val();            

            if (parseInt(job_id) > 0) {                

                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_update_job_variation.php",
                    data: {
                        job_id: job_id,
                        job_var_amount: job_var_amount,
                        job_var_type: job_var_type,
                        job_var_type_text: job_var_type_text,
                        job_var_reason: apv_reason,
                        job_var_reason_text: apv_reason_text,
                        make_ym: make_ym,
                        display_on: display_on
                    }
                }).done(function (ret) {
                    jQuery("#load-screen").hide();
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });

            }

            return false;

        });


        // unpaid marker
        jQuery("#unpaid_chk").click(function () {
            unpaid_check_action();


        });





        // snyc alarms
        jQuery("#sync_alarm_btn").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var property_id = <?php echo $property_id; ?>;

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
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            }

        });



        // recreate bundle services
        jQuery("#recreate_bundle_services_btn").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var ajt_id = <?php echo $job_row['jservice'] ?>;

            if (parseInt(job_id) > 0 && parseInt(ajt_id) > 0) {

                if (confirm("You are about to reacreate bundle services, are you you want to proceed?")) {

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_recreate_bundle_services.php",
                        data: {
                            job_id: job_id,
                            ajt_id: ajt_id
                        }
                    }).done(function (ret) {
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            }

        });


        // upload invoice/bill to pme
        jQuery("#upload_invoice_bill_to_pme_btn").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var is_uploaded_to_api = '<?php echo $job_row['is_pme_invoice_upload']; ?>';
            var confirm_message = '';

            if (parseInt(job_id) > 0) {

                if( is_uploaded_to_api == '1' ){
                    confirm_message = 'This has already been uploaded to PropertyMe, do you want to upload again?';
                }else{
                    confirm_message = 'You are about to upload invoice and create bill on PropertyMe, do you want to proceed?';
                }

                if (confirm(confirm_message)) {

                    var site_link = jQuery(location).attr('href');
                    var base_url = window.location.origin;

                    var session = "<?=$_SESSION['country_default']?>";
                    var page_url = "";
                    if (session == 2) {
                        page_url = "https://crmci.sats.co.nz";
                    }else {
                        if (base_url == "https://crmdev.sats.com.au") {
                            page_url = "https://crmdevci.sats.com.au";
                        }else {
                            page_url = "https://crmci.sats.com.au";
                        }
                    }
                    window.location = page_url+"/property_me/send_all_certificates_and_invoices_via_vjd/?job_id="+job_id+"&url="+site_link;

                }

            }

        });

        // upload invoice/bill to console
        jQuery("#upload_invoice_bill_to_console_btn").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var is_uploaded_to_api = '<?php echo $job_row['is_console_invoice_upload']; ?>';
            var confirm_message = '';

            if (parseInt(job_id) > 0) {

                if( is_uploaded_to_api == '1' ){
                    confirm_message = 'This has already been uploaded to Console, do you want to upload again?';
                }else{
                    confirm_message = 'You are about to upload invoice and create bill on Console, do you want to proceed?';
                }

                if (confirm(confirm_message)) {

                    var url = '<?php echo $crm->crm_ci_redirect(rawurlencode("/console/upload_invoice_and_certificate/?job_id={$job_id}")); ?>';                    
                    window.location = url;
                    
                }

            }

        });


        // upload invoice/bill to pme
        jQuery("#upload_invoice_bill_to_palace_btn").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var is_uploaded_to_api = '<?php echo $job_row['is_palace_invoice_upload']; ?>';
            var confirm_message = '';

            if (parseInt(job_id) > 0) {
                
                if( is_uploaded_to_api == '1' ){
                    confirm_message = 'This has already been uploaded to Palace, do you want to upload again?';
                }else{
                    confirm_message = 'You are about to upload invoice and create bill on Palace, do you want to proceed?';
                }

                if (confirm(confirm_message)) {

                    var site_link = jQuery(location).attr('href');
                    var base_url = window.location.origin;

                    var session = "<?=$_SESSION['country_default']?>";
                    if (session == 2) {
                        var page_url = "https://crmci.sats.co.nz";
                    }else {
                        if (base_url == "https://crmdev.sats.com.au") {
                            var page_url = "https://crmdevci.sats.com.au";
                        }else {
                            var page_url = "https://crmci.sats.com.au";
                        }
                    }
                    window.location = page_url+"/palace/send_all_certificates_and_invoices_via_vjd/?job_id="+job_id+"&url="+site_link;

                }

            }

        });


        // upload invoice/bill to pme
        jQuery("#upload_invoice_bill_to_palace_btn_payload_only").click(function () {

            var job_id = <?php echo $job_id; ?>;
            var is_uploaded_to_api = '<?php echo $job_row['is_palace_invoice_upload']; ?>';
            var confirm_message = '';

            if (parseInt(job_id) > 0) {
                
                if( is_uploaded_to_api == '1' ){
                    confirm_message = 'This has already been uploaded to Palace, do you want to upload again?';
                }else{
                    confirm_message = 'You are about to upload invoice and create bill on Palace, do you want to proceed?';
                }

                if (confirm(confirm_message)) {

                    var site_link = jQuery(location).attr('href');
                    var base_url = window.location.origin;

                    var session = "<?=$_SESSION['country_default']?>";
                    if (session == 2) {
                        var page_url = "https://crmci.sats.co.nz";
                    }else {
                        if (base_url == "https://crmdev.sats.com.au") {
                            var page_url = "https://crmdevci.sats.com.au";
                        }else {
                            var page_url = "https://crmci.sats.com.au";
                        }
                    }
                    window.location = page_url+"/palace/send_all_certificates_and_invoices_via_vjd_payload_only/?job_id="+job_id+"&url="+site_link;

                }

            }

        });

        // add accounts log form
        jQuery("#btn_add_accounts_log").click(function () {

            var this_row = jQuery(this).parents("tr:first");
            var al_date = this_row.find(".al_date").val();
            var al_comment = this_row.find(".al_comment").val();
            var error = '';

            if (al_date == '') {
                error += "Accounts Note Date is required\n";
            }

            if (al_comment == '') {
                error += "Accounts Note Comment is required\n";
            }

            if (error != '') {

                alert(error);

            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "ajax_add_job_account_logs.php",
                    data: {
                        job_id: <?php echo $job_id; ?>,
                        al_date: al_date,
                        al_comment: al_comment
                    }
                }).done(function (ret) {
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });

            }



        });



        // credit delete script
        jQuery(document).on("click", ".btn_delete_credit", function () {

            var this_row = jQuery(this).parents("tr:first");
            var credit_id = this_row.find(".credit_id").val();

            if (credit_id != null) {

                if (confirm("Are you sure you want to continue delete?")) {

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_delete_credit_details.php",
                        data: {
                            credit_id: credit_id,
                            job_id: <?php echo $job_id; ?>
                        }
                    }).done(function (ret) {
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            }



        });



        // invoice delete script
        jQuery(document).on("click", ".btn_delete_pd", function () {

            var this_row = jQuery(this).parents("tr:first");
            var ip_id = this_row.find(".ip_id").val();

            if (ip_id != null) {

                if (confirm("Are you sure you want to continue delete?")) {

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_delete_invoice_payment.php",
                        data: {
                            ip_id: ip_id,
                            job_id: <?php echo $job_id; ?>
                        }
                    }).done(function (ret) {
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            } else {
                this_row.remove();
            }



        });



        // invoice refund script
        jQuery(document).on("click", ".btn_delete_ir", function () {

            var this_row = jQuery(this).parents("tr:first");
            var ir_id = this_row.find(".ir_id").val();

            if (ir_id != null) {

                if (confirm("Are you sure you want to continue delete?")) {

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_delete_invoice_refund.php",
                        data: {
                            ir_id: ir_id,
                            job_id: <?php echo $job_id; ?>
                        }
                    }).done(function (ret) {
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            } else {
                this_row.remove();
            }



        });


        // edited marker
        jQuery(".pd_fields").change(function () {

            jQuery(this).parents("tr:first").find(".edited").val(1);

        });


        // save payment details
        jQuery("#save_payment_details_btn").click(function () {

            var pd_row_count = jQuery(".payment_details_main_div .amount_paid").length;
            var i = 1;
            var pd_empty = 0;
            var ap_empty = 0;
            var top_empty = 0;
            var error = '';

            var payments_arr = [];

            // validation, rushing change to json if possible
            jQuery(".payment_details_main_div .amount_paid").each(function () {

                var error_flag = 0;

                var this_row = jQuery(this).parents("tr:first");
                var ip_id = this_row.find(".ip_id").val();
                var payment_date = this_row.find(".payment_date").val();
                var amount_paid = jQuery(this).val();
                var orig_amount_paid = this_row.find(".orig_amount_paid").val();
                var type_of_payment = this_row.find(".type_of_payment").val();
                var edited = this_row.find(".edited").val();
                var payment_reference = this_row.find(".payment_reference").val();

                // clear red highlight
                this_row.find(".payment_date").removeClass('redBorder');
                this_row.find(".amount_paid").removeClass('redBorder');
                this_row.find(".type_of_payment").removeClass('redBorder');


                if (payment_date == '') {
                    pd_empty = 1;
                    error_flag = 1;
                    this_row.find(".payment_date").addClass('redBorder');
                }

                if (amount_paid == '') {
                    ap_empty = 1;
                    error_flag = 1;
                    this_row.find(".amount_paid").addClass('redBorder');
                }

                if (type_of_payment == '') {
                    top_empty = 1;
                    error_flag = 1;
                    this_row.find(".type_of_payment").addClass('redBorder');
                }

                if (error_flag == 0) {

                    json_data = {
                        'payment_date': payment_date,
                        'amount_paid': amount_paid,
                        'type_of_payment': type_of_payment,
                        'ip_id': ip_id,
                        'orig_amount_paid': orig_amount_paid,
                        'edited': edited,
                        'payment_reference': payment_reference
                    }
                    var json_str = JSON.stringify(json_data);

                    payments_arr.push(json_str);

                }


            });



            //alert(payments_arr);



            if (pd_empty == 1) {
                error += "Payment Date is required\n";
            }

            if (ap_empty == 1) {
                error += "Amount Paid is required\n";
            }

            if (top_empty == 1) {
                error += "Type of payment is required\n";
            }



            if (error != '') { // error
                alert(error);
            } else {



                jQuery.ajax({
                    type: "POST",
                    url: "ajax_submit_invoice_payment.php",
                    data: {
                        job_id: <?php echo $job_id; ?>,
                        payments_arr: payments_arr
                    }
                }).done(function (ret) {
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });


            }





        });





        // save refund details
        jQuery("#save_refund_details_btn").click(function () {

            var pd_row_count = jQuery(".refund_details_main_div .amount_paid").length;
            var i = 1;
            var pd_empty = 0;
            var ap_empty = 0;
            var top_empty = 0;
            var error = '';

            var refunds_arr = [];

            // validation, rushing change to json if possible
            jQuery(".refund_details_main_div .amount_paid").each(function () {

                var error_flag = 0;

                var this_row = jQuery(this).parents("tr:first");
                var ir_id = this_row.find(".ir_id").val();
                var payment_date = this_row.find(".payment_date").val();
                var amount_paid = jQuery(this).val();
                var orig_amount_paid = this_row.find(".orig_amount_paid").val();
                var type_of_payment = this_row.find(".type_of_payment").val();
                var edited = this_row.find(".edited").val();
                var payment_reference = this_row.find(".payment_reference").val();

                // clear red highlight
                this_row.find(".payment_date").removeClass('redBorder');
                this_row.find(".amount_paid").removeClass('redBorder');
                this_row.find(".type_of_payment").removeClass('redBorder');


                if (payment_date == '') {
                    pd_empty = 1;
                    error_flag = 1;
                    this_row.find(".payment_date").addClass('redBorder');
                }

                if (amount_paid == '') {
                    ap_empty = 1;
                    error_flag = 1;
                    this_row.find(".amount_paid").addClass('redBorder');
                }

                if (type_of_payment == '') {
                    top_empty = 1;
                    error_flag = 1;
                    this_row.find(".type_of_payment").addClass('redBorder');
                }

                if (error_flag == 0) {

                    json_data = {
                        'payment_date': payment_date,
                        'amount_paid': amount_paid,
                        'type_of_payment': type_of_payment,
                        'ir_id': ir_id,
                        'orig_amount_paid': orig_amount_paid,
                        'edited': edited,
                        'payment_reference': payment_reference
                    }
                    var json_str = JSON.stringify(json_data);

                    refunds_arr.push(json_str);

                }


            });



            //alert(refunds_arr);



            if (pd_empty == 1) {
                error += "Payment Date is required\n";
            }

            if (ap_empty == 1) {
                error += "Amount Paid is required\n";
            }

            if (top_empty == 1) {
                error += "Type of payment is required\n";
            }



            if (error != '') { // error
                alert(error);
            } else {



                jQuery.ajax({
                    type: "POST",
                    url: "ajax_submit_invoice_refund.php",
                    data: {
                        job_id: <?php echo $job_id; ?>,
                        refunds_arr: refunds_arr
                    }
                }).done(function (ret) {
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });


            }





        });



        // save credit details
        jQuery("#save_credit_details_btn").click(function () {



            var cd_empty = 0;
            var cp_empty = 0;
            var cr_empty = 0;
            var ab_empty = 0;
            var error = '';

            var credits_arr = [];


            // validation, rushing change to json if possible
            jQuery("#credits_div .credit_paid").each(function () {

                var error_flag = 0;

                var this_row = jQuery(this).parents("tr:first");
                var credit_id = this_row.find(".credit_id").val();
                var credit_date = this_row.find(".credit_date").val();
                var credit_paid = jQuery(this).val();
                var orig_amount_paid = this_row.find(".orig_amount_paid").val();
                var credit_reason = this_row.find(".credit_reason").val();
                var approved_by = this_row.find(".approved_by").val();
                var edited = this_row.find(".edited").val();
                var payment_reference = this_row.find(".payment_reference").val();

                // clear red highlight
                this_row.find(".credit_date").removeClass('redBorder');
                this_row.find(".credit_paid").removeClass('redBorder');
                this_row.find(".credit_reason").removeClass('redBorder');
                this_row.find(".approved_by").removeClass('redBorder');


                if (credit_date == '') {
                    cd_empty = 1;
                    error_flag = 1;
                    this_row.find(".credit_date").addClass('redBorder');
                }

                if (credit_paid == '') {
                    cp_empty = 1;
                    error_flag = 1;
                    this_row.find(".credit_paid").addClass('redBorder');
                }

                if (credit_reason == '') {
                    cr_empty = 1;
                    error_flag = 1;
                    this_row.find(".credit_reason").addClass('redBorder');
                }

                if (approved_by == '') {
                    ab_empty = 1;
                    error_flag = 1;
                    this_row.find(".approved_by").addClass('redBorder');
                }

                if (error_flag == 0) {

                    json_data = {
                        'credit_date': credit_date,
                        'credit_paid': credit_paid,
                        'credit_reason': credit_reason,
                        'approved_by': approved_by,
                        'credit_id': credit_id,
                        'orig_amount_paid': orig_amount_paid,
                        'edited': edited,
                        'payment_reference': payment_reference
                    }
                    var json_str = JSON.stringify(json_data);

                    credits_arr.push(json_str);

                }


            });


            //alert(credits_arr);



            if (cd_empty == 1) {
                error += "Credit Date is required\n";
            }

            if (cp_empty == 1) {
                error += "Credit Paid is required\n";
            }

            if (cr_empty == 1) {
                error += "Credit Reason is required\n";
            }

            if (ab_empty == 1) {
                error += "Approved By is required\n";
            }



            if (error != '') { // error
                alert(error);
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "ajax_save_credit_details.php",
                    data: {
                        job_id: <?php echo $job_id; ?>,
                        credits_arr: credits_arr
                    }
                }).done(function (ret) {
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });


            }



        });


        // credit toggle script
        jQuery("#credit_btn").click(function () {

            var btn_txt_elem = jQuery(this).find(".inner_icon_txt");
            var btn_inner_icon = jQuery(this).find(".inner_icon");
            var btn_txt_val = btn_txt_elem.html();
            var orig_btn_txt = 'CREDIT';
            var div_toggle_name = 'credits_div_id';

            if (btn_txt_val == orig_btn_txt) {
                jQuery(this).css("margin-top", '10px');
                btn_txt_elem.html("CANCEL");
                jQuery("#" + div_toggle_name).show();
                btn_inner_icon.attr("src", "images/button_icons/cancel-button.png");
            } else {
                jQuery(this).css("margin-top", '35px');
                btn_txt_elem.html(orig_btn_txt);
                jQuery("#" + div_toggle_name).hide();
                btn_inner_icon.attr("src", "images/button_icons/add-button.png");
            }

        });


        // paymeny add row
        jQuery("#add_payment_btn").click(function () {

            /*
             var pd_row = jQuery(".payment_details_tbl tbody:last").clone();
             jQuery(".payment_details_tbl").append(pd_row);
             */


            var pd_row = '' +
                    '<tbody>' +
                    '<tr>' +
                    '<td>' +
                    '<input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker payment_date" value="<?php echo date('d/m/Y') ?>" />' +
                    '</td>' +
                    '<td>' +
                    '<input style="width: 60px;" type="text" id="amount_paid" class="addinput vw-jb-inpt amount_paid" />' +
                    '</td>' +
                    '<td>' +
<?php
$pt_sql = $crm->getPaymentTypes();
?>
            '<select style="width: 105px;" class="type_of_payment">' +
                    '<option value="">--- Select ---</option>' +
<?php while ($pt = mysql_fetch_array($pt_sql)) { ?>
                '<option value="<?php echo $pt['payment_type_id']; ?>"><?php echo $pt['pt_name'] ?></option>' +
    <?php
}
?>
            '</select>' +
                    '</td>' +
                    '<td>' +
                    '<input type="text" class="addinput vw-jb-inpt payment_reference" name="payment_reference" >' +
                    '</td>' +
                    '<td>' +
                    '<button type="button" class="submitbtnImg jfloatright btn_delete_pd">' +
                    '<img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />' +
                    '</button>' +
                    '</td>' +
                    '</tr>' +
                    '</tbody>';
            jQuery(".payment_details_tbl").append(pd_row);

            // datepicker
            jQuery(".datepicker").datepicker({dateFormat: "dd/mm/yy"});


        });



        // refund add row
        jQuery("#add_refund_btn").click(function () {

            /*
             var pd_row = jQuery(".payment_details_tbl tbody:last").clone();
             jQuery(".payment_details_tbl").append(pd_row);
             */


            var pd_row = '' +
                    '<tbody>' +
                    '<tr>' +
                    '<td>' +
                    '<input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker payment_date" value="<?php echo date('d/m/Y') ?>" />' +
                    '</td>' +
                    '<td>' +
                    '<input style="width: 60px;" type="text" id="amount_paid" class="addinput vw-jb-inpt amount_paid" />' +
                    '</td>' +
                    '<td>' +
<?php
$pt_sql = $crm->getPaymentTypes();
?>
            '<select style="width: 105px;" class="type_of_payment">' +
                    '<option value="">--- Select ---</option>' +
<?php
while ($pt = mysql_fetch_array($pt_sql)) {
    // only EFT and cheque
    if (
            $pt['payment_type_id'] == 3 ||
            $pt['payment_type_id'] == 5 ||
            $pt['payment_type_id'] == 6
    ) {
        ?>
                    '<option value="<?php echo $pt['payment_type_id']; ?>"><?php echo $pt['pt_name'] ?></option>' +
        <?php
    }
}
?>
            '</select>' +
                    '</td>' +
                    '<td>' +
                    '<input type="text" class="addinput payment_reference vw-jb-inpt" name="payment_reference" >' +
                    '</td>' +
                    '<td>' +
                    '<button type="button" class="submitbtnImg jfloatright btn_delete_ir">' +
                    '<img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />' +
                    '</button>' +
                    '</td>' +
                    '</tr>' +
                    '</tbody>';
            jQuery(".refund_details_tbl").append(pd_row);

            // datepicker
            jQuery(".datepicker").datepicker({dateFormat: "dd/mm/yy"});


        });



        // credit add row
        jQuery("#add_credit_btn").click(function () {

            var pd_row = '' +
                    '<tbody>' +
                    '<tr>' +
                    '<td>' +
                    '<input style="width: 80px;" type="text" class="addinput vw-jb-inpt datepicker credit_date" value="<?php echo date('d/m/Y'); ?>" />' +
                    '</td>' +
                    '<td>' +
                    '<input style="width: 60px;" type="text" class="addinput vw-jb-inpt credit_paid" />' +
                    '</td>' +
                    '<td>' +
                    '<select class="credit_reason">' +
                    '<option value="">--- Select ---</option>' +
<?php
$credit_reason_sql = $crm->getCreditReason();
while ($cr_row = mysql_fetch_array($credit_reason_sql)) {
    ?>
                '<option value="<?php echo $cr_row['credit_reason_id'] ?>" <?php echo ( $credit['credit_reason'] == $cr_row['credit_reason_id'] ) ? 'selected="selected"' : ''; ?>><?php echo $cr_row['reason'] ?></option>' +
    <?php
}
?>
            '</select>' +
                    '</td>' +
                    '<td>' +
                    '<select style="width: 140px;" class="approved_by">' +
                    '<option value="">--- Select ---</option>' +
<?php
// for global and full access
$staff_sql = mysql_query("
                                                                                SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
                                                                                FROM staff_accounts AS sa
                                                                                INNER JOIN `country_access` AS ca ON (
                                                                                        sa.`StaffID` = ca.`staff_accounts_id`
                                                                                        AND ca.`country_id` ={$_SESSION['country_default']}
                                                                                )
                                                                                WHERE sa.deleted =0
                                                                                AND sa.active =1
                                                                                ORDER BY sa.`FirstName`
                                                                                ");
while ($staff = mysql_fetch_array($staff_sql)) {
    ?>
                '<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ( $staff['staff_accounts_id'] == $credit['approved_by'] ) ? 'selected="selected"' : ''; ?>><?php echo $staff['FirstName'] . ' ' . $staff['LastName']; ?></option>' +
    <?php
}
?>
            '</select>' +
                    '</td>' +
                    '<td>' +
                    '<input type="text" class="addinput payment_reference vw-jb-inpt" name="payment_reference" >' +
                    '</td>' +
                    '<td>' +
                    '<button type="button" class="submitbtnImg jfloatright btn_delete_pd">' +
                    '<img class="inner_icon" src="images/button_icons/cancel-button.png" style="margin:0;" />' +
                    '</button>' +
                    '</td>' +
                    '</tr>' +
                    '</tbody>';
            jQuery(".credits_tbl").append(pd_row);

            // datepicker
            jQuery(".datepicker").datepicker({dateFormat: "dd/mm/yy"});


        });



        // selects the previous tab on load
        var curr_tab = parseInt($.cookie('vjd_tab_index'));
        var num_tab = jQuery(".vjd_tab_div a.c-tabs-nav__link").length;

        console.log("curr_tab: "+curr_tab);
        console.log("num_tab: "+num_tab);

        if ( curr_tab > 0 && curr_tab < num_tab ) {
            myTabs.goToTab(curr_tab);
        } else {
            myTabs.goToTab(0);
        }


        // keep tab script
        jQuery(".c-tabs-nav__link").click(function () {

            var tab_index = jQuery(this).attr('data-tab_index');
            console.log("tab_index" + tab_index);

            //console.log(tab_index);
            if( tab_index != '' ){
                $.cookie('vjd_tab_index', tab_index);
            }


        });



        // EN and priority insert text script
        jQuery("#job_entry_notice").change(function () {

            var checked = jQuery(this).prop("checked");
            var jp_check = jQuery("#job_priority").prop("checked");
            var tech_notes_txt = '';

            tech_notes_txt = getTechNotesFromTickBox();

            if (checked == true) {
                //tech_notes_txt = ( jp_check == true )?'EN - KEYS, DO NOT CANCEL':'EN - KEYS';
                jQuery("#job_entry_notice_lbl").addClass("colorItRedBold");
                //jQuery("#tech_notes").val(tech_notes_txt);
            } else {
                jQuery("#job_entry_notice_lbl").removeClass("colorItRedBold");
                //jQuery("#tech_notes").val("");
            }
            jQuery("#tech_notes").val(tech_notes_txt);

        });

        jQuery("#job_priority").change(function () {

            var checked = jQuery(this).prop("checked");
            var jen_check = jQuery("#job_entry_notice").prop("checked");
            var tech_notes_txt = '';

            tech_notes_txt = getTechNotesFromTickBox();

            if (checked == true) {
                //tech_notes_txt = ( jen_check == true )?'EN - KEYS, DO NOT CANCEL':'DO NOT CANCEL';
                jQuery("#job_priority_lbl").addClass("colorItRedBold");
                //jQuery("#tech_notes").val(tech_notes_txt);
            } else {
                jQuery("#job_priority_lbl").removeClass("colorItRedBold");
                //jQuery("#tech_notes").val("");
            }
            jQuery("#tech_notes").val(tech_notes_txt);

        });



<?php
// only if booked with mobile not empty
if ($booked_with_mobile != '') {
    ?>

            // confirm sms script
            jQuery("#sms_to_conf_book").click(function () {

                var booked_with_node = jQuery("#booked_with");
                var booked_with = booked_with_node.val();

                // get booked with tenant via finding the same name on tenants panel
                var booked_with_tenant_name_node = jQuery(".tenant_fname_field[value='" + booked_with + "']");
                // get booked with tenant value
                var booked_with_tenant_name = booked_with_tenant_name_node.val();
                // get booked with tenant mobile by finding mobile number on the same row
                var booked_with_tenant_mob = booked_with_tenant_name_node.parents("tr:first").find(".tenant_mobile_field").val();

                console.log("booked_with: "+booked_with);
                console.log("booked_with_tenant_name: "+booked_with_tenant_name);
                console.log("booked_with_tenant_mob: "+booked_with_tenant_mob);

                var sms_type = 16 // Booking Confirmed


                if (booked_with != '') {

                    if (confirm("Are you sure you want to proceed?")) {

                        //jQuery(".tenant_fname_field[value='" + booked_with + "']").parents(".jtenant_div").find(".sms_type[value='" + sms_type + "']").parents(".sms_temp_div_row").find(".btn_sms").click();

                        jQuery.ajax({
                            type: "POST",
                            url: "ajax_send_confirmed_booking_sms.php",
                            data: {
                                job_id: <?php echo $job_id; ?>,
                                booked_with_tenant_name: booked_with_tenant_name,
                                booked_with_tenant_mob: booked_with_tenant_mob
                            }
                        }).done(function (ret) {
                            window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>&confirm_booking_sms=1";
                        });


                    }

                } else {
                    alert("Select Tenants in booked with dropdown to be sent SMS with");
                }


            });

    <?php
}
?>



        // Delete ALL Tenant Data script
        jQuery("#btn_delete_tenant").click(function () {

            if (confirm('Are you sure you want to DELETE ALL Data?')) {

                jQuery.ajax({
                    type: "POST",
                    url: "ajax_clear_property_tenant_data.php",
                    data: {
                        property_id: <?php echo $property_id; ?>
                    }
                }).done(function (ret) {
                    window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                });

            }

        });


        // SMS template character count script live update
        jQuery(".sms_temp_txtbox").keyup(function () {

            var txtbox_txt = jQuery(this).val();
            var txtbox_txt_count = txtbox_txt.length;
            console.log(txtbox_txt_count);
            jQuery(this).parents(".sms_temp_div_row").find(".sms_temp_char_count").html(txtbox_txt_count);
            var sms_count = Math.ceil(txtbox_txt_count / 160);
            jQuery(this).parents(".sms_temp_div_row").find(".sms_num_count_val").html(sms_count);

        });



        // SMS template fade Out script
        jQuery(".sms_temp_rad").click(function () {

            var chk_state = jQuery(this).prop("checked");

            // default
            jQuery(".sms_temp_div_row").css("opacity", 0.5);
            jQuery(".sms_temp_div_row").find(".sms_temp_insert_date").removeClass("jcolorItRedNoBold");
            jQuery(".sms_temp_div_row").find(".sms_temp_char_count").removeClass("jcolorItRed");
            jQuery(".sms_temp_div_row").find(".sms_num_count").removeClass("jcolorItGreen");
            jQuery(".sms_temp_div_row").find(".btn_sms").hide();

            jQuery(this).parents(".sms_temp_div_row").css("opacity", 1);
            jQuery(this).parents(".sms_temp_div_row").find(".sms_temp_insert_date").addClass("jcolorItRedNoBold");
            jQuery(this).parents(".sms_temp_div_row").find(".sms_temp_char_count").addClass("jcolorItRed");
            jQuery(this).parents(".sms_temp_div_row").find(".sms_num_count").addClass("jcolorItGreen");
            jQuery(this).parents(".sms_temp_div_row").find(".btn_sms").show();

        });



        // SA alarm images toggle script
        jQuery(".alarm_images_toggle").click(function () {
            jQuery(this).parents("tr:first").next().toggle();
        });



        jQuery("#call_before_yes").click(function () {

            jQuery("#call_before_txt").show();

        });

        jQuery("#call_before_no").click(function () {

            jQuery("#call_before_txt").hide();

        });


        // mark corded window as touched
        jQuery(".cw_image").change(function () {
            jQuery(this).parents("tr:first").find(".cw_image_touched").val(1);
            jQuery("#cw_touched_flag").val(1);
        });


        // mark safety switch as touched
        jQuery("#ss_image").change(function () {
            jQuery("#ss_image_touched").val(1);
        });



        // allocate ajax
        jQuery(".allocate_opt").click(function () {

            var obj = jQuery(this);
            var allocate_opt_val = obj.val();

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_allocation_data.php",
                data: {
                    job_id: <?php echo $job_id; ?>,
                    allocate_opt_type: 'allocate_opt',
                    allocate_opt_val: allocate_opt_val
                }
            }).done(function (ret) {
                //window.location="view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>"
                obj.parents("div#allocate_job_div").find(".green_check").show();
            });

        });

        jQuery(".allocate_notes").change(function () {

            var obj = jQuery(this);
            var allocate_opt_val = obj.val();

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_allocation_data.php",
                data: {
                    job_id: <?php echo $job_id; ?>,
                    allocate_opt_type: 'allocate_notes',
                    allocate_opt_val: allocate_opt_val
                }
            }).done(function (ret) {
                //window.location="view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>"
                obj.parents("div#allocate_job_div").find(".green_check").show();
            });

        });




        // send quote email
        jQuery("#btn_email_quote").click(function () {

            var quote_email_to = jQuery("#quote_email_to").val();
            var url = '';

            if (quote_email_to == '') {
                alert('Please Enter Quote Email');
            } else {
                if (confirm("Are you sure you want to proceed?")) {
                    url = 'send_quote_email.php?job_id=<?php echo $job_id; ?>&quote_email_to=' + quote_email_to;
                    console.log(url);
                    window.location = url;
                }
            }



        });




        // preferred time script
        jQuery("#btn_preferred_time").click(function () {

            var icon = jQuery(this).find(".inner_icon");
            var icon_span = jQuery(this).find(".inner_icon_span");
            var btn_txt = icon_span.html();
            var btn_orig_txt = 'Preferred Time'
            var orig_btn_src = 'images/button_icons/like-button.png';
            var cancel_btn = 'images/button_icons/cancel-button.png';

            if (btn_txt == btn_orig_txt) {

                icon_span.html("Cancel");
                jQuery(this).removeClass("blue-btn");
                icon.attr("src", cancel_btn);
                jQuery(".preferred_time_elem").show();

            } else {

                icon_span.html(btn_orig_txt);
                jQuery(this).addClass("blue-btn");
                icon.attr("src", orig_btn_src);
                jQuery(".preferred_time_elem").hide();


            }


        });

        // Update preferred time
        jQuery("#btn_update_pref_time").click(function () {

            var preferred_time = jQuery("#preferred_time").val();
            var out_of_tech_hours = (jQuery("#out_of_tech_hours").prop("checked") == true) ? 1 : 0;

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_job_preferred_time.php",
                data: {
                    job_id: <?php echo $job_id; ?>,
                    preferred_time: preferred_time,
                    out_of_tech_hours: out_of_tech_hours
                }
            }).done(function (ret) {
                window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>"
            });

        });



        jQuery("#btn_mailto_send").click(function () {

            var maito_textarea = jQuery("#maito_textarea").val();


            var tenants_arr = [];

            jQuery(".tenant_email").each(function () {

                var tenant_email = jQuery(this).val();

                if (tenant_email != '') {
                    tenants_arr.push(tenant_email);
                }


            });

            jQuery.ajax({
                type: "POST",
                url: "ajax_email_tenant.php",
                data: {
                    job_id: <?php echo $job_id; ?>,
                    maito_textarea: maito_textarea,
                    tenants_arr: tenants_arr
                }
            }).done(function (ret) {
                jQuery("#mailto_sent_icon").show();
                parent.$.fancybox.close();
                window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>&emailed_tenants=1"
            });




        });


        // pop up mailto
        jQuery("#mailto_tenants").fancybox({
            'autoSize': false
        });


        //jQuery("#mailto_tenants").fancybox();



        jQuery("#show_agency_pass").click(function () {

            btn_txt = jQuery(this).html();
            if (btn_txt == 'Show Password') { // show
                jQuery(this).html("Hide Password");
                jQuery(".agency_pass_div").show();
            } else { // hide
                jQuery(this).html("Show Password");
                jQuery(".agency_pass_div").hide();
            }

        });



        // move job script
        jQuery("#btn_move_job").click(function () {
            jQuery("#move_job_div").toggle();
        });

        jQuery("#move_job_property_id").keyup(function () {

            var property_id = jQuery(this).val();
            var txt = '';

            jQuery.ajax({
                type: "POST",
                url: "ajax_get_property.php",
                data: {
                    property_id: property_id
                }
            }).done(function (ret) {
                if (ret != '') {
                    txt = ret;
                    jQuery("#btn_move").show();
                } else {
                    txt = 'Property not found';
                    jQuery("#btn_move").hide();
                }
                jQuery("#search_prop_display").html(txt);
            });

        });

        jQuery("#btn_move").click(function () {

            var property_id = jQuery("#move_job_property_id").val();
            var old_prop_id = jQuery("#old_prop_id").val();

            jQuery.ajax({
                type: "POST",
                url: "ajax_move_job_to_property.php",
                data: {
                    job_id: <?php echo $job_id; ?>,
                    property_id: property_id,
                    old_prop_id: old_prop_id
                }
            }).done(function (ret) {
                //jQuery("#search_prop_display").html(ret);
                window.location = "/view_property_details.php?id=" + old_prop_id + "&job_moved=1";
            });

        });

        /*
        // escalate script
        jQuery("#job_status").change(function () {

            jQuery("#status_changed_flag").val(1);
            jQuery("#escalate_green_check").hide();
            var hr = jQuery("#holiday_rental").val();

            // escalate
            if (jQuery(this).val() == "Escalate") {
                jQuery(".jobdate").val("");
                jQuery("#timeofday").val("");
                jQuery("#booked_with option:eq(0)").prop("selected", true);
                jQuery("#techid option:eq(0)").prop("selected", true);
                jQuery("#booked_by option:eq(0)").prop("selected", true);
            }

            if (jQuery(this).val() == "Escalate" && hr == 0) {
                jQuery("#escalate_job_div").show();
            } else {
                jQuery("#escalate_job_div").hide();
            }

            // allocate
            if (jQuery(this).val() == "Allocate") {
                jQuery("#allocate_job_div").show();
            } else {

                // clear allocate data
                jQuery(".allocate_opt").prop("checked", false);
                jQuery(".allocate_notes").val('');

                jQuery("#allocate_job_div").hide();
            }




        });
        */

        /*
         // escalate text color script
         jQuery(".escalate_job_reasons").change(function(){

         var error = '';

         if( jQuery(".jobdate").val()!='' && jQuery("#job_status").val()=='Escalate' ){
         //jQuery(this).attr("checked",false);
         //jQuery(this).parents("div.escalate_job_reasons_div:first").find(".escalate_job_reasons_label").css('color','black');
         error += "Job Date Cannot be set on Escalate Jobs\n";
         }

         if( jQuery("#techid").val()!=1 && jQuery("#job_status").val()=='Escalate' ){
         //jQuery(this).parents("div.escalate_job_reasons_div:first").find(".escalate_job_reasons_label").css('color','black');
         //jQuery(this).attr("checked",false);
         error += "Technician Cannot be set on Escalate Jobs\n";
         }

         if( jQuery("#booked_by").val()!='' && jQuery("#job_status").val()=='Escalate' ){
         //jQuery(this).parents("div.escalate_job_reasons_div:first").find(".escalate_job_reasons_label").css('color','black');
         //jQuery(this).attr("checked",false);
         error += "Booked By Cannot be set on Escalate Jobs\n";
         }

         if( error!='' ){
         alert(error);
         }else{

         if( jQuery(this).prop("checked")==true ){
         jQuery(this).parents("div.escalate_job_reasons_div:first").find(".escalate_job_reasons_label").css("color","red");
         }else{
         jQuery(this).parents("div.escalate_job_reasons_div:first").find(".escalate_job_reasons_label").css("color","black");
         }


         var ejr_id_arr = [];
         jQuery(".escalate_job_reasons:checked").each(function(){
         ejr_id_arr.push(jQuery(this).val());
         });

         //console.log(ejr_id_arr.length);

         if(ejr_id_arr.length==0){
         jQuery("#job_status option[value='To Be Booked']").prop("selected",true);


         jQuery("#escalate_job_div").hide();

         }

         jQuery.ajax({
         type: "POST",
         url: "ajax_update_escalate_job_reason.php",
         data: {
         job_id: <?php echo $_GET['id']; ?>,
         ejr_id_arr: ejr_id_arr
         }
         }).done(function( ret ){
         jQuery("#escalate_green_check").show();
         //window.location="/view_job_details.php?id=<?php echo $_GET['id']; ?>";
         //location.reload();
         //window.location='/main.php';
         });

         }




         });
         */


        // run sheet notes ajax update
        jQuery("#tech_notes").blur(function () {

            var tech_notes = jQuery(this).val();

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_job_tech_notes.php",
                data: {
                    job_id: <?php echo $_GET['id']; ?>,
                    tech_notes: tech_notes
                }
            }).done(function (ret) {
                jQuery("#tech_notes_check").show();
                //window.location="/view_job_details.php?id=<?php echo $_GET['id']; ?><?php echo $added_param; ?>";
                //location.reload();
                //window.location='/main.php';
            });
        });


        // sms text template
        jQuery(".sms_icon").click(function () {

            jQuery(this).parents("div.jtenant_div").find(".sms_div").toggle();

        });


        // STR notes script
        jQuery(".btn_show_str_notes").click(function () {

            var obj = jQuery(this);
            var tr_id = obj.parents("tr:first").find(".tr_id").val();
            var btn_text = obj.html();

            if (btn_text == "Notes") {

                obj.html("Hide");

                // invoke ajax
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_get_tech_run_notes.php",
                    data: {
                        tr_id: tr_id,
                        job_id: <?php echo $job_id; ?>
                    }
                }).done(function (ret) {

                    obj.parents("tr:first").find(".tech_run_notes_span").html(ret);
                    //window.location="/view_job_details.php?id=<?php echo $_GET['id']; ?>&entry_notice=no";

                });

            } else {

                obj.html("Notes");
                obj.parents("tr:first").find(".tech_run_notes_span").html("");

            }




        });

<?php if ($jobsel == 'Lease Renewal') { ?>

            // Lease Renewal End Date script
            jQuery(".due_date").change(function () {

                var due_date = jQuery(this).val();
                var due_date_arr = due_date.split("/");
                var day = due_date_arr[0];
                var month = due_date_arr[1];
                var year = due_date_arr[2];
                //console.log("Day: "+day+" Month:"+month+" Year: "+year);

                // minus 30 days
                var d = new Date(year, (month - 1), day);
                console.log(d.toLocaleString());
                d.setDate(d.getDate() - 30);

                var dd = d.getDate();
                var mm = d.getMonth() + 1; //January is 0!
                var yyyy = d.getFullYear();

                var start_date_text = formatJsDate(dd, mm, yyyy);
                console.log();

                jQuery(".start_date").val(start_date_text);

            });


    <?php
}
?>





        // ajax get distance to agency
        jQuery("#btn_check_distance_to_agency").click(function () {

            setTimeout(function () {

                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_get_distance_to_agency.php",
                    data: {
                        property_id: "<?php echo $property_id; ?>",
                        agency_id: "<?php echo $agency_id; ?>"
                    }
                }).done(function (ret) {

                    jQuery("#load-screen").hide();
                    jQuery("#distance_to_agency_span").html(ret);

                });

            }, 1000);

        });



        // show hide tenant details script
        jQuery("#btn_show_tenant").toggle(function () {

            jQuery(".tenant_details_div").show();
            jQuery(this).html("Hide");

        }, function () {

            jQuery(".tenant_details_div").hide();
            jQuery(this).html("Show");

        });


        // ajax update tenant details
        jQuery("#btn_edit_tenant_details").click(function () {

            //var btn_txt = jQuery(this).html();

            var obj = jQuery(this);
            var btn_txt = jQuery(this).find(".inner_icon_lbl").html();

            var job_id = obj.parents("div.tenant_details_div").find("input.job_id").val();
            var property_id = obj.parents("div.tenant_details_div").find(".property_id").val();

            if (btn_txt == "Update") {


                var tenants_arr = [];

                obj.parents("div.tenant_details_div").find(".pt_id").each(function () {

                    var obj2 = jQuery(this)
                    var row = obj2.parents("div.jtenant_div");

                    var pt_id = obj2.val();
                    var tenant_firstname = row.find(".tenant_fname_field").val();
                    var tenant_lastname = row.find(".tenant_lname_field").val();
                    var tenant_mobile = row.find(".tenant_mobile_field").val();
                    var tenant_landline = row.find(".tenant_phone_field").val();
                    var tenant_email = row.find(".tenant_email_field").val();

                    if (pt_id != '') {


                        var json_data = {
                            'pt_id': pt_id,
                            'tenant_firstname': tenant_firstname,
                            'tenant_lastname': tenant_lastname,
                            'tenant_mobile': tenant_mobile,
                            'tenant_landline': tenant_landline,
                            'tenant_email': tenant_email
                        }
                        var json_str = JSON.stringify(json_data);

                        tenants_arr.push(json_str);

                    }

                });

                jQuery.ajax({
                    type: "POST",
                    url: "ajax_update_tenant_details.php",
                    data: {
                        job_id: job_id,
                        property_id: property_id,
                        tenants_arr: tenants_arr
                    }
                }).done(function (res) {

                    console.log(res);
                    window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?><?php echo $added_param; ?>";

                });




            }


        });


        // edit tenant details script
        jQuery("#btn_edit_tenant_details").toggle(function () {

            //jQuery(this).html("Update");
            jQuery(this).find(".inner_icon").attr("src", "images/button_icons/save-button.png");
            jQuery(this).find(".inner_icon_lbl").html('Update');

            jQuery(".tenant_details_div input[type='text']").addClass('error_border');
            jQuery(".tenant_details_div input[type='text']").removeClass('green_border');
            jQuery(".tenant_details_div input[type='text']").removeAttr("readonly");
            jQuery(".tenant_details_div input[type='text']").click(function (e) {
                e.preventDefault();
            });

        }, function () {

            /*
             jQuery(this).html("Edit");
             jQuery(".tenant_details_div input[type='text']").removeClass('error_border');
             jQuery(".tenant_details_div input[type='text']").addClass('green_border');
             jQuery(".tenant_details_div input[type='text']").attr("readonly","readonly");
             jQuery(".tenant_details_div input[type='text']").unbind('click');
             */


        });





        // invoke fancybox
        jQuery('.fancybox').fancybox();

        // append booked with script
        var booked_with = "";
        var ten_email = "";
        var ten_lname = "";
        var booked_with_name = "";

        getBookedWith();

        //console.log(ten_email);

        /*
         jQuery("#btn_email_yes").click(function(){
         var booked_with_name = jQuery("#booked_with_name").val();
         var booked_with_email = jQuery("#booked_with_email").val();
         if(booked_with_email==""){
         alert("Email is Required");
         }else{
         if(confirm("Are you sure you want to email an Entry notice to the tenant and Agency?")){
         var url = "/view_job_details.php?id=<?php echo $_GET['id']; ?>&entry_notice=yes&booked_with="+booked_with_name+"&booked_with_email="+booked_with_email+"<?php echo $added_param; ?>";
         //console.log(url);
         window.location=url;
         }
         }

         });
         */


        // entry notice - No
        jQuery("#btn_email_no").click(function () {

            // invoke ajax
            jQuery.ajax({
                type: "POST",
                url: "ajax_jobd_entry_notice.php",
                data: {
                    job_id: <?php echo $_GET['id']; ?>,
                    booked_with: "<?php echo $jbw; ?>"
                }
            }).done(function (ret) {

                console.log(ret);

                if (parseInt(ret) == 1) {
                    //obj.parents("div.vw-pro-dtl-tn-hld").find(".sms_success_msg").slideDown();
                    window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?>&entry_notice=no<?php echo $added_param; ?>";
                }

            });

        });

        // restore job
        jQuery("#btn_restore_job").click(function () {
            if (confirm("Are you sure you want to restore job?")) {
                //console.log('yes');
                window.location = "/restore_jobs.php?job_id=<?php echo $job_id; ?>";
            }
        });

        // delete job script
        jQuery("#btn_del_job_temp").click(function () {

            // invoice payment check
            jQuery.ajax({
                type: "POST",
                url: "ajax_invoice_payment_check.php",
                data: {
                    job_id: <?php echo $_GET['id']; ?>
                }
            }).done(function (ret) {

                var inv_pay_count = parseInt(ret);

                if( inv_pay_count > 0 ){
                    alert("This job cannot be deleted as it has an attached payment.")
                }else{

                    if ( confirm("Are you sure you want to delete job?") ) {

                        //console.log('yes');
                        //jQuery("#btn_vj_delete_job").click();
                        window.location = "delete_job.php?id=<?php echo $job_id; ?>&property_id=<?php echo $property_id; ?>&service=<?php echo $ajt_service_id; ?>&doaction=delete";

                    }

                }


            });



        });


        // difficult booking script
        jQuery("#btn_dif_bok").click(function () {

            if (jQuery(this).html() == 'Difficult Booking') {
                jQuery(this).html("Cancel");
                jQuery("#dif_bok_div").show();
            } else {
                jQuery(this).html("Difficult Booking");
                jQuery("#dif_bok_div").hide();
            }

        });

        // difficult booking script
        jQuery("#btn_voicemail").click(function () {

            if (jQuery(this).html() == 'Voicemail') {
                jQuery(this).html("Cancel");
                jQuery("#voicemail_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Voicemail");
                jQuery("#voicemail_div").hide();
                jQuery("#bs_div").show();
            }

        });

        // difficult booking script
        jQuery("#btn_key_access").click(function () {

            if (jQuery(this).html() == 'Key Access') {
                jQuery(this).html("Cancel");
                jQuery("#key_access_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Key Access");
                jQuery("#key_access_div").hide();
                jQuery("#bs_div").show();
            }


        });


        // Key access text script
        jQuery("#btn_not_available").click(function () {

            if (jQuery(this).html() == 'Not Available') {
                jQuery(this).html("Cancel");
                jQuery("#not_available_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Not Available");
                jQuery("#not_available_div").hide();
                jQuery("#bs_div").show();
            }


        });



        // Inbound Call
        jQuery("#btn_inbound_call").click(function () {

            if (jQuery(this).html() == 'Inbound Call') {
                jQuery(this).html("Cancel");
                jQuery("#inbound_call_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Inbound Call");
                jQuery("#inbound_call_div").hide();
                jQuery("#bs_div").show();
            }


        });


        // Called Already Today
        jQuery("#btn_cat").click(function () {

            if (jQuery(this).html() == 'Called Already Today') {
                jQuery(this).html("Cancel");
                jQuery("#cat_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Called Already Today");
                jQuery("#cat_div").hide();
                jQuery("#bs_div").show();
            }


        });

        // Called Already Today
        jQuery("#btn_pt").click(function () {

            if (jQuery(this).html() == 'Preferred Time') {
                jQuery(this).html("Cancel");
                jQuery("#pt_div").show();
                jQuery("#bs_div").hide();
            } else {
                jQuery(this).html("Preferred Time");
                jQuery("#pt_div").hide();
                jQuery("#bs_div").show();
            }


        });


        // booking script
        jQuery("#btn_booking_script").click(function () {

            jQuery("#booking_script_div").toggle();

        });


        // booked jobs script buttons
        // cancel voicemail
        jQuery("#btn_cancel_vm").click(function () {

            if (jQuery(this).html() == 'Cancel Voicemail') {
                jQuery(this).html("Cancel");
                jQuery("#cancel_vm_div").show();
                jQuery("#default_script_text_div").hide();
            } else {
                jQuery(this).html("Cancel Voicemail");
                jQuery("#cancel_vm_div").hide();
                jQuery("#default_script_text_div").show();
            }


        });

        // cancel voicemail with caller
        jQuery("#btn_cancel_caller").click(function () {

            if (jQuery(this).html() == 'Cancel with Caller') {
                jQuery(this).html("Cancel");
                jQuery("#cancel_caller_div").show();
                jQuery("#default_script_text_div").hide();
            } else {
                jQuery(this).html("Cancel with Caller");
                jQuery("#cancel_caller_div").hide();
                jQuery("#default_script_text_div").show();
            }


        });


        // rebook
        jQuery("#btn_script_rebook").click(function () {

            if (jQuery(this).html() == 'Rebook') {
                jQuery(this).html("Cancel");
                jQuery("#script_rebook_div").show();
                jQuery("#default_script_text_div").hide();
            } else {
                jQuery(this).html("Rebook");
                jQuery("#script_rebook_div").hide();
                jQuery("#default_script_text_div").show();
            }


        });





        // send sms script
        jQuery(".btn_sms").click(function () {

            var obj = jQuery(this);
            var tenant_mobile = obj.parents("div.sms_temp_div_row").find(".tenant_mobile").val();
            var sms_message = obj.parents("div.sms_temp_div_row").find(".sms_temp_txtbox").val();
            var sms_type = obj.parents("div.sms_temp_div_row").find(".sms_type").val();
            var sms_sent_to_tenant = obj.parents("div.sms_temp_div_row").find(".sms_sent_to_tenant").val();

            // invoke ajax
            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "ajax_send_sms.php",
                data: {
                    property_id: <?php echo $property_id; ?>,
                    job_id: <?php echo $_GET['id']; ?>,
                    tenant_mobile: tenant_mobile,
                    sms_message: sms_message,
                    sms_type: sms_type,
                    sms_sent_to_tenant: sms_sent_to_tenant
                }
            }).done(function (ret) {

                jQuery("#load-screen").hide();
                window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?>&sms_sent=1<?php echo $added_param; ?>";

            });


        });





        jQuery("#btn_update_job_details").click(function () {

            var kar_y = jQuery("#key_access_required_yes");

            var kad = jQuery("#key_access_details").val();
            var ejr = jQuery(".escalate_job_reasons:checked").length;
            var hr = jQuery("#holiday_rental").val();
            var job_status = jQuery("#job_status").val();
            var orig_status = jQuery("#curr_status").val();
            var tech_is_elec = jQuery("#techid option:selected").attr("data-is_electrian");
            var is_eo = jQuery("#is_eo").prop("checked");
            var jobtype = jQuery("#jobtype").val();
            var tech_id = jQuery("#techid").val();
            var allow_en = '<?php echo $job_row['allow_en']; ?>';
            var prop_vac = jQuery("#prop_vac").prop("checked");

            var proceed_check_1 = false;
            var proceed_check_2 = false;
            var proceed_check_3 = false;

            var property_id = '<?php echo $job_row['property_id']; ?>';

            var error = "";

            if (kar_y.prop("checked") == true && kad == "") {
                error += "Key Accesss Details Required\n";
            }

            /*
             //console.log(ejr);
             if(  job_status=="Escalate" && ejr==0 && hr==0 ){
             error += "Must check at least one escalate reason\n";
             }
             */

            if (hr == 1 && job_status == "Escalate") {
                error += "Short Term Rental Property cannot be marked as Escalate\n";
            }

            var js_arr = ['Escalate', 'To Be Booked', 'On Hold', 'On Hold - COVID', 'Send Letters', 'Pending', 'Action Required'];
            if (jQuery(".jobdate").val() != '' && jQuery.inArray(job_status, js_arr) >= 0) {
                error += "Job Date Cannot be set on " + job_status + " Jobs\n";
            }

            if (jQuery("#techid").val() != '' && jQuery.inArray(job_status, js_arr) >= 0) {
                error += "Technician Cannot be set on " + job_status + " Jobs\n";
            }

            if (jQuery("#booked_by").val() != '' && jQuery.inArray(job_status, js_arr) >= 0) {
                error += "Booked By Cannot be set on " + job_status + " Jobs\n";
            }

            var dk = jQuery("#door_knock");
            if (kar_y.prop("checked") == true && dk.prop("checked") == true) {
                error += "Door Knock is not Allowed if Key Access is Yes";
            }
           

            // if job type is FR and status is booked and electrician only checkbox is not ticked
            if ( ( jQuery("#jobtype").val() == 'Fix or Replace' && job_status == 'Booked' ) && is_eo == false ) {
                <?php if ($crm->getAll240vAlarm($_GET['id']) == true) { ?>
                    error += "This job has 240v alarms that require servicing, please mark it 'Electrician Only'.\n";
                <?php
                }
                ?>
            }


            if( jobtype == '240v Rebook' ){
                error += "Cannot update job type to 240v Rebook\n";
            }


            if (error != "") {
                alert(error);
            } else {

                // check if property is NLM                
                jQuery.ajax({
                    type: "POST",
                    url: "ajax_check_if_property_is_nlm.php",
                    data: {
                        property_id: property_id
                    }
                }).done(function (ret) {

                    var is_nlm = parseInt(ret);

                    if( is_nlm == 1 && job_status != "Cancelled" ){

                        if( confirm("This property is marked NLM, are you sure you want to proceed?") ){

                            proceed_check_1 = true;

                        }

                    }else{

                        proceed_check_1 = true;

                    }

                    // if proceed update with property NLM true
                    if( proceed_check_1 == true ){ 

                        if ( is_eo == true && ( tech_id > 0 && tech_is_elec != 1 ) ) {
                        
                            if( confirm("This job is marked as for Electrician Only(EO). Make sure tech selected is electrician. Assign this tech anyways?") ){
                                
                                proceed_check_2 = true;
                                
                            }

                        }else{

                            proceed_check_2 = true;

                        }

                        // proceed with job marked as EO
                        if( proceed_check_2 == true ){ 

                            if( 
                                ( 
                                    ( orig_status != 'Booked' && job_status == 'Booked' ) && 
                                    allow_en == 2 
                                ) && 
                                prop_vac == false 
                            ){

                                if( confirm("If you proceed, an EN will be issued") ){

                                    proceed_check_3 = true;

                                }

                            }else{ // default
                                
                                proceed_check_3 = true;

                            }

                            // proceed with EN will be issued
                            if( proceed_check_3 == true ){ 

                                jQuery("#job_details_form").submit();

                            }

                        }


                    }

                });                                

                
            }

        });

        jQuery("#key_access_required_yes").click(function () {

            jQuery("#authorised_by_span").show();
            jQuery("#key_access_details").show();
            jQuery("#must_email_tenant_div").show();
            //jQuery("#mailto_tenants").show();
            jQuery(".mailto_div").show();
            jQuery("#key_access_details").addClass("jred_border_higlight");

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_key_access.php",
                data: {
                    job_id: <?php echo $_GET['id']; ?>,
                    key_access: 1
                }
            }).done(function (ret) {
                //window.location="/view_job_details.php?id=<?php echo $_GET['id']; ?>&price_changed=1";
            });
        });

        jQuery("#key_access_required_no").click(function () {

            jQuery("#authorised_by_span").hide();
            jQuery("#key_access_details").hide();
            jQuery("#must_email_tenant_div").hide();
            //jQuery("#mailto_tenants").hide();
            jQuery(".mailto_div").hide();
            jQuery("#key_access_details").removeClass("jred_border_higlight");

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_key_access.php",
                data: {
                    job_id: <?php echo $_GET['id']; ?>,
                    key_access: 0
                }
            }).done(function (ret) {
                //window.location="/view_job_details.php?id=<?php echo $_GET['id']; ?>&price_changed=1";
            });
        });

        // manipulated service
        // alarms
        jQuery(".sa_serv_updatable input, .sa_serv_updatable select").change(function () {
            jQuery(this).parents("tr:first").find(".sa_serv_manipulated").val(1);
        });
        jQuery(".sa_serv_updatable input, .sa_serv_updatable select").change(function () {
            jQuery(this).parents("tr:first").find(".dic_sa_serv_manipulated").val(1);
        });
        // safety switch
        jQuery(".ss_serv_updatable input, .ss_serv_updatable select").change(function () {
            jQuery(this).parents("tr:first").find(".ss_serv_manipulated").val(1);
        });
        // corded window
        jQuery(".cw_serv_updatable input, .cw_serv_updatable select").change(function () {
            jQuery(this).parents("tr:first").find(".cw_serv_manipulated").val(1);
        });

        // Water Efficiency
        jQuery(".we_serv_updatable input, .we_serv_updatable select").change(function () {
            jQuery(this).parents("tr:first").find(".we_serv_manipulated").val(1);
        });

        // water meter
        jQuery(".wm_serv_updatable input").change(function () {
            jQuery(this).parents("tr:first").find(".wm_serv_manipulated").val(1);
        });

        
        // price toggle
        jQuery("#edit_price_link").toggle(function () {
            jQuery("#change_price_div").show();
            jQuery(this).html("Cancel");
        }, function () {
            var orig_price = jQuery("#orig_price").val();
            jQuery("#change_price_div").hide();
            jQuery(this).html("$ " + orig_price);
        });
        

        // update job price
        jQuery(".btn_update_price").click(function () {

            var job_id = <?php echo $_GET['id']; ?>;
            var job_price = jQuery("#job_price").val();
            var price_reason = jQuery("#price_reason").val();
            var price_detail = jQuery("#price_detail").val();
            var staff_id = <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>;

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_job_price.php",
                data: {
                    job_id: job_id,
                    job_price: job_price,
                    price_reason: price_reason,
                    price_detail: price_detail,
                    staff_id: staff_id
                }
            }).done(function (ret) {
                window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?>&price_changed=1<?php echo $added_param; ?>";
            });

        });


        // tenants updated mark script
        jQuery(".tenant_fields").change(function () {
            jQuery("#tenants_changed").val(1);
        });



        // change job price script
        jQuery(".btn-chng-price").toggle(function () {
            jQuery(".txtfld_job_price").show();
            jQuery(".lbl_job_price").hide();
            jQuery("#price_row").show();
            jQuery(this).attr("id", "pricehide");
            jQuery(this).html("Cancel");
        }, function () {
            jQuery(".txtfld_job_price").hide();
            jQuery(".lbl_job_price").show();
            jQuery(this).attr("id", "pricechange");
            jQuery(this).html("Change Price");
            jQuery("#price_row").hide();
        });

        // job price validation script
        jQuery("#btn-update-job-price").click(function () {

            var rsn = jQuery(".tf_job_reason").val();
            var det = jQuery(".tf_job_details").val();
            var error = "";

            if (rsn == "") {
                error += "Reason is Required\n";
            }

            if (det == "") {
                error += "Details is Required\n";
            }

            if (error != "") {
                alert(error);
            } else {
                jQuery("#job_details_form").submit();
            }

        });

    });
</script>
<script type="text/javascript">

    $(document).ready(function () {

        // fancy box
        jQuery("#sent_email_lb_link").fancybox();



        // get email template sent data
        jQuery(".sent_email_alink").click(function () {

            var obj = jQuery(this);
            var job_log_id = obj.parents("tr:first").find(".job_log_id").val();
            var log_id = obj.parents("tr:first").find(".log_id").val();

            // parse body tags
            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "ajax_get_email_template_sent_data.php",
                data: {
                    job_log_id: job_log_id,
                    log_id: log_id
                },
                dataType: 'json'
            }).done(function (ret) {

                jQuery("#load-screen").hide();

                jQuery("#email_temp_lb_div .prev_et_from").html(ret.from_email);
                jQuery("#email_temp_lb_div .prev_et_to").html(ret.to_email);
                jQuery("#email_temp_lb_div .prev_et_cc").html(ret.cc_email);
                jQuery("#email_temp_lb_div .prev_et_subj").html(ret.subject);
                jQuery("#email_temp_lb_div .prev_et_body").html(ret.email_body);

                jQuery('#sent_email_lb_link').click();

            });

        });



        // update jobs = booked
        jQuery("#btn_move_to_booked").click(function () {

            var curr_status = jQuery("#curr_status").val();

            if (confirm("Are you sure you want to move jobs to Booked?") == true) {

                jQuery.ajax({
                    type: "POST",
                    url: "ajax_move_to_booked.php",
                    data: {
                        job_id: <?php echo $job_id; ?>,
                        curr_status: curr_status
                    }
                }).done(function (ret) {
                    //window.location="/precompleted_jobs.php";
                    location.reload();
                });

            }

        });


        var agency_status = '<?php echo $agency_status; ?>';
        jQuery("#btn_create_240v_rebook").click(function () {
            if(agency_status == 'deactivated'){
                alert('Error: Unable to do this while an Agency is Deactivated.');
            } else if(agency_status =='target'){
                alert('Error: Unable to do this while an Agency is Target.');
            } else {
                if (confirm("Are you sure you want to continue?") == true) {
                    var job_id = new Array();
                    job_id.push(<?php echo $job_id; ?>);

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_rebook_script.php",
                        data: {
                            job_id: job_id,
                            is_240v: 1
                        }
                    }).done(function (ret) {
                        window.location.href = "/view_job_details.php?id=<?php echo $job_id; ?>&rebook_message=1<?php echo $added_param; ?>";
                    });
                }
            }

        });

        jQuery("#btn_create_rebook").click(function () {
            if(agency_status == 'deactivated'){
                alert('Error: Unable to do this while an Agency is Deactivated.');
            } else if(agency_status =='target'){
                alert('Error: Unable to do this while an Agency is Target.');
            } else {
                if (confirm("Are you sure you want to continue?") == true) {
                    var job_id = new Array();
                    job_id.push(<?php echo $job_id; ?>);
                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_rebook_script.php",
                        data: {
                            job_id: job_id,
                            is_240v: 0
                        }
                    }).done(function (ret) {
                        window.location.href = "/view_job_details.php?id=<?php echo $job_id; ?>&rebook_message=2<?php echo $added_param; ?>";
                    });
                }
            }
        });

        // change status trap
        /*
         jQuery("#job_status").change(function(){
         if(jQuery("#jobtype").val()=="240v Rebook"){
         cur_stat = jQuery("#curr_status").val();
         if(cur_stat=="Pre Completion"&&jQuery(this).val()=="Merged Certificates"){
         alert("Job type can't be 240v Rebook");
         jQuery("#job_status option:eq(3)").prop("selected",true);
         }
         }
         });
         */

        
        // Change status and verifivation
        jQuery("#job_status").change(function(){

            var job_id = <?php echo $_GET['id']; ?>;
            var ajax_url = "ajax_check_open_jobs.php";
            var job_status = $("select#job_status option:selected").val();

            if(job_status == "Booked"){
                jQuery.ajax({
                type: "POST",
                url: ajax_url,
                data: {
                    job_id: job_id
                }
                }).done(function (ret) {
                    if(ret == 1){
                        alert("There are other uncompleted jobs for this property");
                    }
                    //console.log(ret);
                });
            }
        });
        

        // Ajax Add Contact Log
        $("button#add-log").click(function () {

            var contact_type = $("#joblog-contact_type").val();
            var date = $("#joblog-date").val();
            var comments = $("#joblog-comments").val();
            //var day = $('#hour').val() + ':' + $('#minute').val() + ' ' + $('#day').val();
            var jl_time = jQuery("#job_log_time").val();
            //console.log(comments);
            var important = (jQuery("#important").prop("checked") == true) ? 1 : 0;
            var unavailable = (jQuery("#unavailable").prop("checked") == true) ? 1 : 0;
            var unavailable_date = jQuery("#unavailable_date").val();

            if (date == null || comments == null || date == "" || comments == "")
            {
                alert("Please complete all fields");
            } else
            {
                $.ajax({
                    type: "POST",
                    data: {
                        JobContactLog: 1,
                        contact_type: contact_type,
                        date: date,
                        comments: comments,
                        job_id: <?php echo $job_id; ?>,
                        time: jl_time,
                        important: important,
                        unavailable: unavailable,
                        unavailable_date: unavailable_date
                    },
                    url: "ajax/ajax.php",
                    dataType: 'json',
                    success: function (response) {

                        if (response.success == true)
                        {
                            window.location.href = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                        } else
                        {
                            alert("There was a technical error, please try again");
                        }

                    }
                });
            }

            return false;
        });

        // Delete Alarm Link
        $("a.remove_link").click(function () {
            return confirm("Are you sure you want to delete this alarm");

        });


        // Print Entry Notice Button
        $("button#print-entry-notice").click(function () {
            // Open PDF in new window / tab
            var win = window.open("<?php echo "/view_entry_notice_new.php?i={$job_id}&m=".md5($agency_id.$job_id); ?>", '_blank');
            win.focus();
            return false;
        });


        // Print Entry Notice Button - With Letterhead
        $("button#print-entry-notice-letterhead").click(function () {
            // Open PDF in new window / tab
            var win = window.open("<?php echo "/view_entry_notice_new.php?letterhead=1&i={$job_id}&m=".md5($agency_id.$job_id); ?>", '_blank');
            win.focus();
            return false;
        });

        $("button.email-entry-notice").click(function () {
            var agency_enabled = <?php echo $email_job_details['send_entry_notice']; ?>;

            if (agency_enabled === 0 && this.id === 'email-tenants-agency')
            {
                alert("Entry notice to Agency is disabled - please enable it in the Agents profile before continuing");
                return false;
            } else
            {
                return confirm("Are you sure you want to email the entry notice?");
            }
        });

        // Capture the main form submit
        // First ensure an electrician has been selected for a Safety Switch Full Test
        // Then ensure at least one job type is chosen. NOW ON PROPERTY PAGE
        $("form#job_details_form").submit(function () {


            var need_elec = $("input#need_elec").val();
            var selected_elec = $("select#techid option:selected").text();
            var job_status = $("select#job_status option:selected").val();

            if (need_elec == 1 && selected_elec.indexOf("[ELEC]") < 0 && job_status == "Booked")
            {
                alert("An Electrician must be assigned for a Safety Switch Full Test! Please change the assigned Tech");
                return false;
            } else
            {
                return true;
            }


        });


        // update job reason
        jQuery("#btn_mark").click(function () {
            var job_id = <?php echo $_GET['id']; ?>;
            var jr_id = jQuery("#mark_as").val();
            //var comment = jQuery("#ma_comments").val();
            var comment = "";            
            
            if (jr_id == "") {
                alert("Please select reason");
            } else {

                var job_id_arr = [];
                job_id_arr.push(job_id);

                if( jr_id == 25 ){  // Staff Sick
                    comment = 'Marked tech sick on <b><?php echo $today; ?></b> by <b><?php echo $logged_staff_name; ?></b>';
                }

                var ajax_url = "ajax_update_job_reason.php";

                jQuery.ajax({
                    type: "POST",
                    url: ajax_url,
                    data: {
                        job_id_arr: job_id_arr,
                        jr_id: jr_id,
                        comment: comment
                    }
                }).done(function (ret) {

                    //var url = window.location.href+"?doaction=update&update_job_not_completed=1";
                    window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?><?php echo $added_param; ?>&update_job_not_completed=1";
                });
            }
        });


        // add red box when selecting booked
        jQuery("#job_status").change(function(){

            var node = jQuery(this);
            var job_status = node.val();

            jQuery("#status_changed_flag").val(1);
            jQuery("#escalate_green_check").hide();


            // add red border, required
            jQuery("#jobdate").removeClass("jerr_hl");
            jQuery("#timeofday").removeClass("jerr_hl");
            jQuery("#booked_with").removeClass("jerr_hl");
            jQuery("#techid").removeClass("jerr_hl");
            jQuery("#booked_by").removeClass("jerr_hl");

            // clear allocate data
            jQuery(".allocate_opt").prop("checked", false);
            jQuery(".allocate_notes").val('');
            jQuery("#allocate_job_div").hide();

            if(
                job_status == 'Booked' ||
                job_status == 'Pre Completion' ||
                job_status == 'Merged Certificates' ||
                job_status == 'Completed'
            ){

                // add red border, required
                jQuery("#jobdate").addClass("jerr_hl");
                jQuery("#timeofday").addClass("jerr_hl");
                jQuery("#booked_with").addClass("jerr_hl");
                jQuery("#techid").addClass("jerr_hl");
                jQuery("#booked_by").addClass("jerr_hl");

            }else if( job_status == 'Escalate' ){ // escalate

                var hr = jQuery("#holiday_rental").val();

                jQuery("#jobdate").val("");
                jQuery("#timeofday").val("");
                jQuery("#booked_with").val("");
                jQuery("#techid").val("");
                jQuery("#booked_by").val("");


                if ( hr == 0) {
                    jQuery("#escalate_job_div").show();
                } else {
                    jQuery("#escalate_job_div").hide();
                }


            }else if( job_status == 'Allocate' ){ // allocate

                jQuery("#allocate_job_div").show();

            }else if( job_status == 'On Hold - COVID' ){ // +7 days when On Hold Covid
                var current_date = new Date(); 
                //current_date.setDate(current_date.getDate() + 7);  //orig
                <?php if( $_SESSION['country_default']==1 ){
                ?>
                current_date.setDate(current_date.getDate() + 7);
                <?php }else{
                ?>
                current_date.setDate(current_date.getDate() + 10);
                <?php
                } ?>
                $('.start_date').datepicker('setDate', current_date);
            }

        });


    // pass/fail label togglee
	jQuery(".we_device").change(function(){

        var node = jQuery(this);
        var parent = node.parents("tr:first");
        var we_device = node.val();

        if( we_device == 2 ){ // toilet

            parent.find(".we_pass_lbl_yes").html("Dual");
            parent.find(".we_pass_lbl_no").html("Single");

        }else if( we_device == 1 || we_device == 3 ){

            parent.find(".we_pass_lbl_yes").html("Yes");
            parent.find(".we_pass_lbl_no").html("No");

        }else{

            parent.find(".we_pass_lbl_yes").html("Pass");
            parent.find(".we_pass_lbl_no").html("Fail");

        }


    });

    // add new Pme tenants
	jQuery('.add_new_ourtradie_tenant_btn').click(function(){

        var new_t_fname = jQuery(this).parents("td:first").find(".ourtradie_tenant_fname").val();
        var new_t_lname = jQuery(this).parents("td:first").find(".ourtradie_tenant_lname").val();
        var new_t_mobile = jQuery(this).parents("td:first").find(".ourtradie_tenant_mobile").val();
        var new_t_landline = jQuery(this).parents("td:first").find(".ourtradie_tenant_landline").val();
        var new_t_email = jQuery(this).parents("td:first").find(".ourtradie_tenant_email").val();
        var pme_api_txt = jQuery(this).parents("td:first").find(".ourtradie_api_txt").val();

        var errorMsg = "";

        if( confirm("Are you sure you want to add "+pme_api_txt+" tenant?") ){

            if(new_t_fname==""){
                errorMsg +="Please Enter First Name \n";
            }

            if(errorMsg!=""){
                alert(errorMsg);
                return false;
            }

            jQuery.ajax({
                url: 'ajax_function_tenants.php?f=newTenant',
                type: 'POST',
                data: {
                    'property_id': <?php echo $property_id ?>,
                    'tenant_firstname' : new_t_fname,
                    'tenant_lastname' : new_t_lname,
                    'tenant_mobile' : new_t_mobile,
                    'tenant_landline' : new_t_landline,
                    'tenant_email' : new_t_email,
                    'active': 1
                }
            }).done(function( ret ){
                //window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                location.reload();
            });

        }


        });

        // update ALL job price (Job price and Property Services price) > by:Gherx
        jQuery(".btn_update_all_price").click(function () {

            var job_id = <?php echo $_GET['id']; ?>;
            var job_price = jQuery("#job_price").val();
            var price_reason = jQuery("#price_reason").val();
            var price_detail = jQuery("#price_detail").val();
            var alarm_job_type_id = <?php echo $alarm_job_type_id; ?>;
            var staff_id = <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>;
            var property_id = $("input[name='property_id']").val();
            var orig_price = $("#orig_price").val();

            jQuery.ajax({
                type: "POST",
                url: "ajax_update_all_job_price.php",
                data: {
                    job_id: job_id,
                    job_price: job_price,
                    orig_price: orig_price,
                    price_reason: price_reason,
                    price_detail: price_detail,
                    staff_id: staff_id,
                    alarm_job_type_id: alarm_job_type_id,
                    property_id: property_id
                }
            }).done(function (ret) {
                window.location = "/view_job_details.php?id=<?php echo $_GET['id']; ?>&price_changed=1<?php echo $added_param; ?>";
            });

        });


        jQuery("#job_price_variation_fb_link").click(function(){

            var job_type = jQuery("#jobtype").val();

            if( job_type == 'Yearly Maintenance' ){
                jQuery("#make_ym_tr").hide();
            }else{
                jQuery("#make_ym_tr").show();
            }

        });


        // delete job variation
        jQuery("#delete_job_price_variation").click(function () {
            
            var jv_id = jQuery("#jv_id").val();  
            var job_id = <?php echo $job_id; ?>;              

            if( parseInt(jv_id) > 0 && parseInt(job_id) > 0) {

                if (confirm("Are you sure you want to delete this job variation?")) {

                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_delete_job_variation.php",
                        data: {
                            jv_id: jv_id,
                            job_id: job_id
                        }
                    }).done(function (ret) {
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                    });

                }

            }

         });


        // pass safety switch ID to fancybox hidden field
        jQuery(".confirm_discard_ss_link").click(function(){

            var confirm_discard_ss_link_dom = jQuery(this);
            var ss_id = confirm_discard_ss_link_dom.attr('data-discard_ss_id');
            jQuery("#discard_ss_id").val(ss_id);

        });


        // discard safety switch
        jQuery("#confirm_discard_yes_btn").click(function(){

            var ss_id = jQuery("#discard_ss_id").val();  
            var ss_discard_reason = jQuery("#ss_discard_reason").val();
            var job_id = <?php echo $job_id; ?>;       
            var error = '';
            
            if( ss_discard_reason == '' ){
                error += 'Discard reason is required\n';
            }

            if( error != '' ){ // error
                alert(error);
            }else{
                
                if( parseInt(ss_id) > 0 ){

                    jQuery("#load-screen").show();
                    jQuery.ajax({
                        type: "POST",
                        url: "ajax_discard_safety_switch.php",
                        data: {
                            ss_id: ss_id,
                            job_id: job_id,
                            ss_discard_reason: ss_discard_reason
                        }
                    }).done(function (ret) {

                        jQuery("#load-screen").hide();
                        window.location = "view_job_details.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
                        
                    });

                }                

            }            

        });


    });


</script>

</body>
</html>

<?php
$pageLoadEnd = microtime(TRUE);
$pageLoadDuration = floor(($pageLoadEnd - $pageLoadStart) * 1000);

$page = 'VJD';
$created = date('Y-m-d H:i:s');

$sql = "
INSERT INTO logged_page_durations
    (`page`, `duration`, `created`)
VALUES
    ('{$page}', {$pageLoadDuration}, '{$created}')
";

mysql_query($sql);
?>