<?php


function alarmGetAlarmPower($alarm_job_type_id = 1)
{
    #Get alarm pwr
    $query = "SELECT * FROM alarm_pwr WHERE alarm_job_type_id = {$alarm_job_type_id}";
    $alarm_pwr = mysqlMultiRows($query);

    return $alarm_pwr;
}

function alarmGetAlarmType($alarm_job_type_id = 1)
{
    #Get alarm type
    $query = "SELECT * FROM alarm_type WHERE alarm_job_type_id = {$alarm_job_type_id}";
    $alarm_type = mysqlMultiRows($query);

    return $alarm_type;
}

function alarmGetAlarmReason($alarm_job_type_id = 1)
{
    #Get alarm reason
    $query = "SELECT * FROM alarm_reason WHERE alarm_job_type_id = {$alarm_job_type_id} ORDER BY `alarm_reason` ASC";
    $alarm_reason = mysqlMultiRows($query);

    return $alarm_reason;
}

function alarmGetDiscardedReason()
{
    #Get alarm reason
    $query = "SELECT * FROM alarm_discarded_reason";
    $alarm_reason = mysqlMultiRows($query);

    return $alarm_reason;
}

function getPropertyAlarms($job_id, $incnew = 1, $discarded = 1, $alarm_job_type_id = 1)
{
    $query = "  SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
                FROM alarm a 
                    LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
                    LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
                    LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
                WHERE a.job_id = '" . $job_id . "'";

    if($alarm_job_type_id == 4 || $alarm_job_type_id == 5) // Safety Switch view and mech should have same alarms
    {
        $query .= " AND a.alarm_job_type_id IN (4,5)";
    }
    else
    {
        $query .= " AND a.alarm_job_type_id = {$alarm_job_type_id}";
    }

    
    
    if($incnew == 0) $query .= " AND a.New = 0";
    if($incnew == 2) $query .= " AND a.New = 1";
    
    if($discarded == 0) $query .= " AND a.ts_discarded = 0";
    if($discarded == 2) $query .= " AND a.ts_discarded = 1";
    
    $query .= " ORDER BY a.alarm_id ASC ";

    $alarms = mysqlMultiRows($query);
    
    return $alarms;
}

/**
 * Used on the tech sheet page when alarms are added through Ajax
 */

function addSingleAppliance($data)
{

    $alarm_power_id = $data['alarm_power_id'];
    $alarm_type_id = $data['app_type'];
    $alarm_reason_id = $data['app_reason'];
    $pass = $data['app_pass'];
    $new = $data['app_new'];
    $make = $data['app_make'];
    $model = $data['model'];
    $expiry = $data['expiry'];
    $ts_item_number = $data['appliance_number'];
    $ts_location = $data['app_ts_location'];
    $ts_comments = $data['app_ts_comments'];
    $job_id = $data['job_id'];
    
    $query = "INSERT INTO alarm (
        job_id, pass, new, alarm_reason_id, alarm_power_id, alarm_type_id, make, model, expiry, ts_item_number, ts_location, ts_comments, ts_added
    ) VALUES (
        '$job_id', '$pass', '$new', '$alarm_reason_id', '$alarm_power_id', '$alarm_type_id', '$make', '$model', '$expiry', '$ts_item_number', '$ts_location', '$ts_comments', 1
    )";

    if(mysql_query($query))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function addSingleCordedWindow($data)
{
    $data = addSlashesData($data);

    $pass = $data['ss_pass'];
    $ts_item_number = $data['appliance_number'];
    $ts_comments = $data['ss_ts_comments'];
    $job_id = $data['job_id'];
    $alarm_job_type_id = $data['alarm_job_type_id'];
    $ts_trip_rate = $data['ss_mech_ts_trip_rate'];
    $alarm_type_id = $data['ss_type'];
    
    $query = "INSERT INTO alarm (
        job_id, pass, ts_item_number, ts_comments, alarm_job_type_id, ts_added,  ts_trip_rate, alarm_type_id
    ) VALUES (
        '$job_id', '$pass', '$ts_item_number', '$ts_comments', '$alarm_job_type_id', 1, '$ts_trip_rate', '$alarm_type_id'
    )";

    if(mysql_query($query))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function addSingleSafetySwitch($data)
{
    $data = addSlashesData($data);

    $corded_window_pass = $data['corded_window_pass'];
    $corded_window_height = $data['corded_window_height'];
    $corded_window_opening = $data['corded_window_opening'];
    $job_id = $data['job_id'];
    $alarm_job_type_id = $data['alarm_job_type_id'];
    $corded_window_type = $data['corded_window_type'];
    $alarm_type_id = $data['corded_window_type'];
    $corded_window_pass_reason = $data['corded_window_pass_reason'];
    
    

    $query = "INSERT INTO alarm (
        job_id, pass, ts_height, ts_opening, alarm_job_type_id, ts_added,  alarm_type_id, ts_pass_reason
    ) VALUES (
        '$job_id', '$corded_window_pass', '$corded_window_height', '$corded_window_opening', '$alarm_job_type_id', 1,'$alarm_type_id', '$corded_window_pass_reason')";

    if(mysql_query($query) or die(mysql_error()))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function addSingleAlarm($data)
{
    $alarm_new = addslashes($data['alarm_new']);
    $alarm_pwr = addslashes($data['alarm_pwr']);
    $alarm_type = addslashes($data['alarm_type']);
    $alarm_reason = addslashes($data['alarm_reason']);
    $alarm_position = strtoupper(addslashes($data['alarm_position']));
    $alarm_make = strtoupper(addslashes($data['alarm_make']));
    $alarm_model = strtoupper(addslashes($data['alarm_model']));
    $alarm_male = addslashes($data['expiry']);
    $alarm_exp = addslashes($data['alarm_exp']);
    $job_id = addslashes($data['job_id']);
    $ts_required_compliance = isset($data['alarm_compliance']) ? $data['alarm_compliance'] : 0;
    $ts_dbrating = addslashes($data['alarm_db_rating']);
	$agency_id = addslashes($data['agency_id']);
	$new_is_alarm_ic = addslashes($data['new_is_alarm_ic']);
	
	
	// get alarm price from agency alarm prices
	if($alarm_new){
		
		$a_sql = mysql_query("
			SELECT `price`
			FROM `agency_alarms`
			WHERE `agency_id` ={$agency_id}
			AND `alarm_pwr_id` ={$alarm_pwr}
		");
		$a = mysql_fetch_array($a_sql);
		$a_field = ", `alarm_price`";
		$a_field_value = ", '{$a['price']}'";				 
		
	}
	
	if( is_numeric($new_is_alarm_ic) ){
		// this is the correct field `ts_alarm_sounds_other` for interconnected alarm
		$is_alarm_ic_field = ", `ts_alarm_sounds_other` ";
		//$is_alarm_ic_field = ", `ts_is_alarm_ic` ";
		$is_alarm_ic_val = ", {$new_is_alarm_ic} ";
	}
    
    $query = "INSERT INTO alarm (
        job_id,  new, alarm_reason_id, alarm_power_id, alarm_type_id, make, model, expiry, ts_added, ts_position, alarm_job_type_id, ts_required_compliance, ts_db_rating {$a_field} {$is_alarm_ic_field}
    ) VALUES (
        '$job_id', '$alarm_new', '$alarm_reason', '$alarm_pwr', '$alarm_type', '$alarm_make', '$alarm_model', '$alarm_exp', 1, '$alarm_position', 2, '$ts_required_compliance', '$ts_dbrating' {$a_field_value} {$is_alarm_ic_val}
    )";


    if( $alarm_new == 1 ){ // new

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
                'New Alarm',
                '" . date('Y-m-d') . "',
                'New Alarm added at <strong>{$alarm_position}</strong> with price <strong>\${$a['price']}</strong>',
                {$job_id}, 
                " . $_SESSION['USER_DETAILS']['StaffID'] . ",
                '" . date('H:i') . "'
            )
        ");

    }
    

	
    if(mysql_query($query))
    {
        $alarm_id = mysql_insert_id();

        if($alarm_new)
        {
            //setAlarmPrice(mysql_insert_id());
        }

        return  $alarm_id;
    }
    else
    {
        return false;
    }
	
}

function deleteAlarm($alarm_id, $job_id)
{
    $alarm_id = intval($alarm_id);
    $job_id = intval($job_id);

    $sql = "DELETE FROM alarm WHERE alarm_id = {$alarm_id} AND job_id = {$job_id} LIMIT 1";

    $result = mysql_query($sql);

    if(mysql_affected_rows($result) > 0)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getNextItemNumber($job_id, $alarm_job_type_id = 1)
{
    $query = "SELECT (MAX(ts_item_number) + 1) AS new_item_number FROM alarm WHERE job_id = {$job_id} AND alarm_job_type_id = {$alarm_job_type_id}";
    $result = mysqlSingleRow($query);

    if(!is_numeric($result['new_item_number']))
    {
        return 1;
    }
    else
    {
        return $result['new_item_number'];
    }
}

function getCountAlarmsForTechSheet($job_id)
{
    $query = "SELECT COUNT(*) AS count FROM alarm WHERE alarm_job_type_id = 2 AND ts_discarded = 0 AND job_id = {$job_id}";

    $result = mysqlSingleRow($query);

    return $result['count'];

}


