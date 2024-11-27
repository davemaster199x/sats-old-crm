<?php

# Sats functions

function getJobQuery($b){

	global $connection;

	$numtechs = array();
	$numjobs = array();  

	$Query1 = "SELECT DISTINCT sa.StaffID, sa.FirstName, sa.LastName 
	FROM jobs AS j	   
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.StaffID
	where j.date = '{$b}' 
	AND p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$_SESSION['country_default']}";

	$result = mysql_query($Query1, $connection);

	while ($row1 = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		
		$tid = $row1['StaffID'];

		$Query2 = "		
		SELECT COUNT(j.id)
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE j.`assigned_tech` ={$tid}
		AND j.date =  '{$b}'
		AND p.deleted =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND ( j.`status`='Booked' || j.`status`='To Be Booked' )
		AND a.`country_id` ={$_SESSION['country_default']}";


		$result2 = mysql_query ($Query2, $connection);
		$row2 = mysql_fetch_array($result2, MYSQL_NUM);					//MYSQL_ASSOC

		$numjobs[] = empty($row2[0]) ? "" : $row2[0]."<br>";
		if(!empty($row2[0])) $numtechs[$row1['StaffID']] = $row1['FirstName']." ".strtoupper(substr($row1['LastName'],0,1).".");			

	}

	return array($numtechs, $numjobs);
}   

    
function dateafter($a, $b)
{
   //$format = array('D jS \ M', 'Y-m-d', 'd-m-Y');		//date('Y-m-d');
   //$format = 'D jS \ M';
   $format = array('l jS F Y', 'Y-m-d', 'd-m-Y','l<\b\r>d/m/Y');
   $hours =  $a * 24;
   return date($format[$b], ($hours * 3600)+time());
}

function chkAlarmtype_isLI($arr) {
   foreach($arr as $pwr){
   		if(substr($pwr, -2) == "LI") return 1;
   }
   return 0;
}

function is_odd($number) {

   return $number & 1; // 0 = even, 1 = odd
}

# added by adam to tidy up code on homepage
function getHomeTotals()
{
	$arr = array();

	$query  = "select count(*) from property  where (service ='1' and deleted <> 1 AND agency_id > 0);";
   	$result = mysqlSingleRow($query);
    $arr[0] = $result['count(*)'];
   
    $query  = "select count(*) from property where (service ='0' and deleted <> 1 AND agency_id > 0);";
	$result = mysqlSingleRow($query);
    $arr[1] = $result['count(*)'];
	
    //$query = "select count(*) from agency;";
	$query = "select count(*) from agency where (status ='active') AND `country_id` = {$_SESSION['country_default']};";
	$result = mysqlSingleRow($query);
    $arr[2] = $result['count(*)'];

    $query  = "select count(*) from property where (service ='2' and deleted <> 1 AND agency_id > 0);";
	$result = mysqlSingleRow($query);
    $arr[3] = $result['count(*)'];

    $query  = "select count(*) from property where (service ='3' and deleted <> 1 AND agency_id > 0);";
	$result = mysqlSingleRow($query);
    $arr[4] = $result['count(*)'];

	
	return $arr;
}

function mysql_prep($value)
{
	if(get_magic_quotes_gpc()){
		//get rid of slash if data contain (',")
		$value = stripslashes($value);
	}
	
	$value = addslashes($value);
	
	return $value;
} 

function mysqlMultiRows( $query )
{
	$result = mysql_query($query) or die(mysql_error());

	if ( mysql_num_rows($result) > 0 )
	{
			$row_array = array();
			while ( $row = mysql_fetch_array($result) )
			{
					array_push( $row_array, $row );
			}
			return $row_array;
	}
	else
	{
		return NULL;
	}
}

function mysqlSingleRow( $query )
{
	$result = mysql_query($query) or die(mysql_error());
	if ( mysql_num_rows($result) > 0 )
	{
			$row = mysql_fetch_array($result);
			return $row;
	}
	else
	{
			return 0;
	}

}

# Trim data
function trimData($item)
{
	if(is_array($item))
	{
		foreach($item as $key=>$value)
		{
			$item[$key] = trimData($item[$key]);
		} 
	}
	else
	{
		$item = trim($item);
	}
	
	return $item;
}

# Escape data before database
function addSlashesData($item)
{
	if(is_array($item))
	{
		foreach($item as $key=>$value)
		{
			$item[$key] = addSlashesData($item[$key]);
		} 
	}
	else
	{
		$item = addslashes($item);
	}
	
	return $item;
}

# Strip slashes from data
function stripSlashesData($item)
{
	if($item == NULL) return $item;
	
	if(is_array($item))
	{
		foreach($item as $key=>$value) $item[$key] = stripSlashesData($item[$key]);
	}
	else
	{
		$item = stripslashes($item);
	}

	return $item;
}

# Check if email valid
function validEmail($email)
{
	if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) 
	{
		return 0;
	}
	else
	{
		return 1;	
	}
}

# is state box checked
function stateIsChecked($checked_array, $value)
{
	$return = 0;

	foreach($checked_array as $index=>$data)
	{
		if($data['StateID'] == $value)
		{
			$return = 1;
			break;
		}
	}
	
	return $return;
}

function monthafter() {
	$date = getdate();
	$nextMonth = $date['mon']+1;
	$monthEnd = $nextMonth + 1;
	$year = $date['year'];
	
	//concatenate from date, to date string
	$dateStr = date('Y-m-d', mktime(0,0,0, $nextMonth, 1, $year));
	$dateStr .= "_" . date('Y-m-d', mktime(0,0,0, $monthEnd, 0, $year));
	return $dateStr;
}

# populate alarm into new job - only if hasn't been synced yet
function populateAlarms($job_id)
{
	# get the previous job for this property
	$query = "SELECT `alarms_synced`, `property_id`, `service` FROM jobs WHERE id = " . $job_id;
	$result = mysqlSingleRow($query);

	if(!$result['alarms_synced'])
	{
		$query = "SELECT MAX(id) AS prev_id FROM jobs WHERE property_id = '" . $result['property_id'] . "' AND id != '" . $job_id . "' AND `service`=2";
		$result = mysqlSingleRow($query);

		if(is_numeric($result['prev_id']))
		{
			# get alarms
			$query = "SELECT * FROM alarm WHERE job_id = '" . $result['prev_id'] . "' AND ts_discarded = 0";
			$result = mysqlMultiRows($query);

			if(is_array($result))
			{
				foreach($result as $index=>$data)
				{
					$alarm_power_id = $data['alarm_power_id'];
					$alarm_type_id = $data['alarm_type_id'];
					$make = $data['make'];
					$model = $data['model'];
					$expiry = $data['expiry'];
					$ts_position = $data['ts_position'];
					$ts_item_number = $data['ts_item_number'];
					$alarm_job_type_id = $data['alarm_job_type_id'];
					
					$query = "INSERT INTO alarm (job_id, alarm_power_id, alarm_type_id, make, model, expiry, ts_position, alarm_job_type_id) VALUES ('$job_id', '$alarm_power_id', '$alarm_type_id', '$make', '$model', '$expiry', '$ts_position', '$alarm_job_type_id')";
					
					mysql_query($query) or die(mysql_error());
						
				}
			}

			# Flag alarms as sycned
			$query = "UPDATE jobs SET alarms_synced = 1 WHERE id = " . $job_id . " LIMIT 1";
			mysql_query($query);
		}
	}
}

/**
 * Update any tech sheet fields that are NULL (i.e not set) when the tech sheet hasn't been updated (ts_signoff date NULL) from the previously completed tech sheet
 * @param  [int] $job_id The Job ID
 * @return true on success
 */
function syncTechSheetFields($job_id, $property_id)
{
	// Retrieve previously completed jobs fields
	$sql = "
	SELECT id, survey_numlevels, survey_ceiling, survey_ladder, ss_location  
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
		survey_ladder = '" . $previous_job['survey_ladder'] . "' 
		WHERE  id = {$job_id} AND ts_signoffdate IS NULL";

		mysql_query($sql);
	}

	return true;
}

# Check dd/mm/yyyy and yyyy-dd-mm format
function isValidDate($date)
{
	if(stristr($date, "/"))
	{
		$tmp = explode("/", $date);
		
		if(checkdate($tmp[1], $tmp[0], $tmp[2]))
		{
			return true;
		}
	}
	
	if(stristr($date, "-"))
	{
		$tmp = explode("-", $date);
		
		if(checkdate($tmp[1], $tmp[2], $tmp[0]))
		{
			return true;
		}
	}
	
	return false;

}

# Convert dd/mm/yyyy to yyyy-mm-dd for database
function convertDate($date)
{
	if(stristr($date, "/"))
	{
		$tmp = explode("/", $date);
		$date = $tmp[2] . "-" . $tmp[1] . "-" . $tmp[0];
	}
	return $date;
	
}

function convertDateAus($date)
{
	if(stristr($date, "-"))
	{
		$tmp = explode("-", $date);
		$date = $tmp[2] . "/" . $tmp[1] . "/" . $tmp[0];
	}
	return $date;
	
}

function syncJobPrice($job_id, $price, $job_type)
{
	/*
	# Specific Rules
	$zero_dollar_jobs = array("Change of Tenancy", "Fix or Replace", "240v Rebook");
	
	if(in_array($job_type, $zero_dollar_jobs)) $price = 0;
	
	$query = "UPDATE jobs SET job_price = '$price', price_used = 1 WHERE id = '$job_id' LIMIT 1";
	mysql_query($query) or die(mysql_error());
	return $price;
	*/
}

function setAlarmPrice($alarm_id)
{
	# Determine Price - either from Agency set price, or fallback to default price
	
	$query = "
	SELECT apr.alarm_price 
	FROM alarm_price apr, alarm a, jobs j, property p
	WHERE apr.alarm_pwr_id = a.alarm_power_id
	AND a.job_id = j.id
	AND j.property_id = p.property_id
	AND p.agency_id = apr.agency_id
	AND alarm_id = {$alarm_id} LIMIT 1";
	
	$result = mysqlSingleRow($query);
	
	if(is_numeric($result['alarm_price']))
	{
		$newprice = $result['alarm_price'];
	}
	else
	{
		$query = "
		SELECT apr.alarm_price 
		FROM alarm_price apr, alarm a
		WHERE apr.alarm_pwr_id = a.alarm_power_id
		AND apr.agency_id = 0
		AND alarm_id = {$alarm_id} LIMIT 1";
		
		$result = mysqlSingleRow($query);
		$newprice = $result['alarm_price'];
	}

	$query = "UPDATE alarm SET alarm_price = '$newprice' WHERE alarm_id = '$alarm_id' LIMIT 1";
	mysql_query($query);
	
	return 1;
}

function getJobDetailsTechSheet($job_id)
{
	$query = "
		SELECT 
		j.*, sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode,
		p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, p.property_id, p.tenant_firstname2, 
		p.tenant_lastname2, p.tenant_ph2, a.agency_id, a.agency_name, a.address_1 AS agent_address_1, 
		a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, a.state AS agent_state, 
		a.postcode  AS agent_postcode, p.price, sa.FirstName AS tech_first_name, sa.LastName AS tech_last_name,
		DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date, p.tenant_mob1, p.tenant_mob2,
		j.ss_location, j.ss_quantity, j.tmh_id, p.key_number, p.`alarm_code`, p.`qld_new_leg_alarm_num`, p.`comments` AS p_comments,
		p.tenant_firstname3, p.tenant_lastname3, p.tenant_ph3, p.tenant_mob3,
		p.tenant_firstname4, p.tenant_lastname4, p.tenant_ph4, p.tenant_mob4, p.`prop_upgraded_to_ic_sa`

		FROM 
		(jobs j, property p)

		LEFT JOIN agency a ON p.agency_id = a.agency_id
		LEFT JOIN staff_accounts AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.property_id = p.property_id AND j.id = $job_id";

	return mysqlSingleRow($query);
}

function getPropertyAgentDetails($property_id)
{
	$query = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.landlord_lastname, p.landlord_firstname, a.agency_name,
	a.address_1 AS a_address_1, a.address_2 AS a_address_2, a.address_3 AS a_address_3, a.state AS a_state, a.postcode  AS a_postcode, p.price, a.agency_id, p.`compass_index_num`
	FROM property p 
	LEFT JOIN agency a ON p.agency_id = a.agency_id
	WHERE p.property_id = '" . $property_id . "'";	
	
	$result = mysqlSingleRow($query);
	
	return $result;
}

function getTechList()
{
	$query = "SELECT * FROM staff_accounts ORDER BY FirstName ASC";
	$result = mysqlMultiRows($query);
	return $result;
}

# Upload property file
function uploadfile($files_arr, $property_id)
{
	#ensure property id set
	if(intval($property_id) == 0) return false;	
	
	#security measure, don't allow ..
	if(stristr($files_arr['fileupload']['name'], "..")) return false; 
	
	
	# if subdir doesn't exist then create it first
	if(!is_dir(UPLOAD_PATH_BASE . $property_id))
	{
		@mkdir(UPLOAD_PATH_BASE . $property_id, 0777);
	}
	
	$filename = preg_replace('/#+/', 'num', $files_arr['fileupload']['name']);
	$filename2 = preg_replace('/\s+/', '_', $filename);
	
	if(move_uploaded_file($files_arr['fileupload']['tmp_name'], UPLOAD_PATH_BASE . $property_id . "/" .rand().date('YmdHis').$filename2))
	{
		return true;
	}
	else {
		return false;
	}
}

# Get Property Files - will eventually move these into a class / similar
function getPropertyFiles($property_id)
{
	# if subdir doesn't exist then return null
	if(!is_dir(UPLOAD_PATH_BASE . $property_id))
	{
		return null;
	}
	else 
	{
		if ($handle = opendir(UPLOAD_PATH_BASE . $property_id)) 
		{
			$files = array();
			
		    while (false !== ($entry = readdir($handle))) 
		    {
		    	if($entry != "." && $entry != "..")
				{	
		        	$files[] = $entry;
		    	}
		    }
		
		    closedir($handle);
		
			return $files;
		}
		else
		{
			return null;
		}
	}
}

# Delete property file
function deletefile($file, $property_id)
{
	if(intval($property_id) == 0) return false;
	if(strlen($file) == 0) return false;
	
	#non allowed chars
	$notallowed = array("/", "\\", "..");
	$file = str_replace($notallowed, "", $file);
	
	if(file_exists(UPLOAD_PATH_BASE . $property_id . "/" . $file))
	{
		@unlink(UPLOAD_PATH_BASE . $property_id . "/" . $file);
		return true;	
	}
	else
	{
		return false;
	}

}


function getFoundRows()
{
	$query = "SELECT FOUND_ROWS() as rows";
	$result = mysqlSingleRow($query);
	return $result['rows'];
}

function generatePassword($length=9, $strength=4) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
 
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}	

/* 
This function checks the alarm dates against the property job date - if any of the alarm dates equal the job date year, 
then it must be a 240 Rebook and not a 'yearly maintenance'
*/

function determinePendingJobType($property_id)
{
	// Get Job # From property

	$query = "SELECT j.id, j.alarms_synced, DATE_FORMAT(j.created, '%Y') AS job_year FROM property p, agency a, jobs j
    WHERE (
    		p.agency_id = a.agency_id
    	AND
    		p.deleted = 0
    	AND
    		p.agency_deleted = 0	
    	AND
    		p.service = 1    		
    	AND	
    		j.`status` = 'Pending'
		AND
			j.`job_type` = 'Yearly Maintenance'
		AND
			p.`property_id` = j.`property_id`
		AND j.`property_id` = {$property_id}	
    )";

	$result = mysqlMultiRows($query);

	if(is_numeric($result[0]['id']))
	{
		foreach($result as $job)
		{
			// Sync alarms if need be
			if($job['alarms_synced'] == 0)
			{
				populateAlarms($job['id']);
			}

			// Get alarms and loop through to check expiry date
			$alarms = getJobAlarms($job['id']);

			if(is_array($alarms))
			{
				foreach($alarms as $alarm)
				{
					if(trim($alarm['expiry']) == $job['job_year'])
					{

						// If 240li, Brooks and PFS then do not make it a 240rebook
						if(
							(trim(strtolower($alarm['make'])) == "brooks" && stristr(trim(strtolower($alarm['model'])), "PFS")
							||
							$alarm['alarm_power_id'] != 2)
							)
						{	
							return "Yearly Maintenance";
						}
						else
						{
							return "240v Rebook";
						}
					}
				}
			}
		}
	}

	// Fallback to default
	return "Yearly Maintenance";
}

function getJobAlarms($job_id)
{
	# Get Alarms
     
     $query = "SELECT * FROM alarm WHERE job_id = $job_id AND ts_discarded = 0 ORDER BY alarm_id ASC";

     $alarms = mysqlMultiRows($query);

     return $alarms;
}

function getServiceName($ajt_id){
	$ajt_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$ajt_id}
	");
	$ajt = mysql_fetch_array($ajt_sql);
	
	if($ajt['id']==8){
		$serv = 'Smoke Alarms/Safety Switches';
	}else if($ajt['id']==9){
		$serv = 'Smoke Alarms/Safety Switches/Window Coverings';
	}else{
		$serv = $ajt['type'];
	}
	
	return $serv;
}

function getParsedSmsMsg($job_id,$sms_msg_id){

	$prev = "";

	// get jobs data
	$jsql = mysql_query("
		SELECT *,
			j.`service` AS jserv, 
			j.`date` AS jdate, 
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE `id` = {$job_id}
	");
	$j = mysql_fetch_array($jsql);

	// get sms
	$sms_sql = mysql_query("
		SELECT *
		FROM `sms_messages`
		WHERE `sms_messages_id` ={$sms_msg_id}
	");
	$sms = mysql_fetch_array($sms_sql);
	
	
	
	
	
	// new tenants switch
	//$new_tenants = 0;
	$new_tenants = NEW_TENANTS;

	if( $new_tenants == 1 ){ // NEW TENANTS

		$pt_params = array( 
			'property_id' => $j['property_id'],
			'active' => 1
		 );
		$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
		
		while( $pt_row = mysql_fetch_array($pt_sql) ){
			
			if( $pt_row['tenant_mobile']!="" && $pt_row['tenant_firstname'] == $j['booked_with'] ){	

				$find = array('{name}', '{address}', '{date}', '{time}','{service}','{tenant_number}');
				
				$name = "{$pt_row['tenant_firstname']}";
				$address = "{$j['p_address_1']} {$j['p_address_2']}";
				$date = date("d/m/Y",strtotime($j['jdate']));
				$time = $j['time_of_day'];
				$service = getServiceName($j['jserv']);
				$country_id = $j['country_id'];
				
				// get country
				$cntry_sql = getCountryViaCountryId($country_id);
				$cntry = mysql_fetch_array($cntry_sql);
				
				$tenant_number = $cntry['tenant_number'];
				
				$replace = array($name, $address, $date, $time, $service, $tenant_number);
				$sms_str = str_replace($find, $replace, $sms['message']);
				
				$prev .= $sms_str." \n";
				
				if($i==1){
					$prev .= "\n \n \n";
				}
			
			}
			
		}

	}else{ // OLD TENANTS

		$num_tenants = getCurrentMaxTenants();
		for($i=1;$i<=$num_tenants;$i++){
		
			if( $j['tenant_mob'.$i]!="" && $j['tenant_firstname'.$i]==$j['booked_with'] ){	

				$find = array('{name}', '{address}', '{date}', '{time}','{service}','{tenant_number}');
				
				$name = "{$j['tenant_firstname'.$i]}";
				$address = "{$j['p_address_1']} {$j['p_address_2']}";
				$date = date("d/m/Y",strtotime($j['jdate']));
				$time = $j['time_of_day'];
				$service = getServiceName($j['jserv']);
				$country_id = $j['country_id'];
				
				// get country
				$cntry_sql = getCountryViaCountryId($country_id);
				$cntry = mysql_fetch_array($cntry_sql);
				
				$tenant_number = $cntry['tenant_number'];
				
				$replace = array($name, $address, $date, $time, $service, $tenant_number);
				$sms_str = str_replace($find, $replace, $sms['message']);
				
				$prev .= $sms_str." \n";
				
				if($i==1){
					$prev .= "\n \n \n";
				}
			
			}
				
		}
		
	}
	
	
	return $prev;
	
	
}

function getSingleParsedSmsMsg($job_id,$sms_msg_id){

	$prev = "";

	// get jobs data
	$jsql = mysql_query("
		SELECT *, 
			j.`service` AS jserv, 
			j.`date` AS jdate, 
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE `id` = {$job_id}
	");
	$j = mysql_fetch_array($jsql);

	// get sms
	$sms_sql = mysql_query("
		SELECT *
		FROM `sms_messages`
		WHERE `sms_messages_id` ={$sms_msg_id}
	");
	$sms = mysql_fetch_array($sms_sql);

	$find = array('{address}', '{date}', '{time}','{service}','{tenant_number}');
	
	$address = "{$j['p_address_1']} {$j['p_address_2']}";
	$date = date("d/m/Y",strtotime($j['jdate']));
	$time = $j['time_of_day'];
	$service = getServiceName($j['jserv']);
	$country_id = $j['country_id'];
	
	// get country
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);
	
	$tenant_number = $cntry['tenant_number'];
	
	$replace = array($address, $date, $time, $service, $tenant_number);
	$sms_str = str_replace($find, $replace, $sms['message']);
	
	$prev .= $sms_str." \n";
	
	return $prev;
	
	
}


function getPrevSmokeAlarm($prop_id){
	return mysql_query("
		SELECT DISTINCT j.`id`
		FROM `alarm` AS a
		LEFT JOIN `jobs` AS j ON j.`id` = a.`job_id`
		WHERE j.`property_id` ={$prop_id}
		AND j.status IN('Completed','Merged Certificates')
		AND j.`id` != ''
		AND j.`del_job` = 0
		AND a.`ts_discarded` = 0
		AND j.`assigned_tech` != 1
		AND j.`assigned_tech` != 2
		ORDER BY j.`date` DESC, j.`id` DESC
		LIMIT 0,1
	");
}


function SnycSmokeAlarmData($job_id,$prev_job_sql2){

	$prev_job2 = mysql_fetch_array($prev_job_sql2);
	
	$pj_sql = mysql_query("
		SELECT *
		FROM `jobs`
		WHERE `id` = {$prev_job2['id']}
	");
	$prev_job = mysql_fetch_array($pj_sql);

	// update safety alarm details
	mysql_query("
		UPDATE `jobs` 
		SET 
			`survey_numlevels` = '{$prev_job['survey_numlevels']}', 
			`survey_ceiling` = '{$prev_job['survey_ceiling']}', 
			`survey_ladder` = '{$prev_job['survey_ladder']}',
			`ts_safety_switch` = '{$prev_job['ts_safety_switch']}', 
			`ss_location` = '{$prev_job['ss_location']}',
			`ss_quantity` = '{$prev_job['ss_quantity']}', 
			`ts_safety_switch_reason` = '{$prev_job['ts_safety_switch_reason']}',
			`ss_image` = '{$prev_job['ss_image']}'
		WHERE `id` = {$job_id} 
	");
	
	// get previous job and insert previous alarm to this job
	$ss_sql2 = mysql_query("
		INSERT INTO 
		`alarm` (
			`job_id`,
			`alarm_power_id`,
			`alarm_type_id`,			
			`make`,
			`model`,
			`ts_position`,			
			`alarm_job_type_id`,
			`expiry`,
			`ts_required_compliance`
		)
		SELECT 
			{$job_id}, 					
			`alarm_power_id`,
			`alarm_type_id`,			
			UPPER( `make` ),
			UPPER( `model` ),
			UPPER( `ts_position` ),			
			`alarm_job_type_id`,
			`expiry`,
			`ts_required_compliance`
		FROM `alarm`
		WHERE `job_id` = {$prev_job['id']}
		AND `ts_discarded` = 0
	");

}



function getPrevCordedWindow($prop_id){
	// get safety switch that is job status completed
	return mysql_query("
		SELECT DISTINCT j.`id`
		FROM `corded_window` AS cw
		LEFT JOIN `jobs` AS j ON j.`id` = cw.`job_id`
		WHERE j.`property_id` ={$prop_id}
		AND j.status IN('Completed','Merged Certificates')
		AND j.`id` != ''
		AND j.`del_job` = 0
		AND j.`assigned_tech` != 1
		AND j.`assigned_tech` != 2
		ORDER BY j.`date` DESC, j.`id` DESC
		LIMIT 0,1
	");
}


function SnycCordedWindowData($job_id,$prev_job_sql2){

	$prev_job2 = mysql_fetch_array($prev_job_sql2);
	
	$pj_sql = mysql_query("
		SELECT *
		FROM `jobs`
		WHERE `id` = {$prev_job2['id']}
	");
	$prev_job = mysql_fetch_array($pj_sql);
	
	
	// get previous job and insert previous corded window to this job
	$ss_sql2 = mysql_query("
		INSERT INTO 
		`corded_window` (
			`job_id`,
			`covering`,
			`ftllt1_6m`,
			`tag_present`,
			`clip_rfc`,
			`clip_present`,
			`loop_lt220m`,
			`seventy_n`,
			`cw_image`,
			`location`,
			`num_of_windows`
		)
		SELECT 
			'{$job_id}', 
			`covering`,
			`ftllt1_6m`,
			`tag_present`,
			`clip_rfc`,
			`clip_present`,
			`loop_lt220m`,
			`seventy_n`,
			`cw_image`, 
			`location`,
			`num_of_windows`
		FROM `corded_window`
		WHERE `job_id` = {$prev_job['id']}
	");


}


// get previous completed WE job
function getPrevWaterEfficiency($prop_id){
	
	return mysql_query("
		SELECT j.`id`
		FROM `water_efficiency` AS we
		LEFT JOIN `jobs` AS j ON j.`id` = we.`job_id`
		WHERE j.`property_id` ={$prop_id}
		AND j.status IN('Completed','Merged Certificates')
		AND j.`id` > 0
		AND j.`del_job` = 0
		AND j.`assigned_tech` != 1
		AND j.`assigned_tech` != 2
		ORDER BY j.`date` DESC, j.`id` DESC
		LIMIT 0,1
	");
}

// sync WE from prev completed WE job
function SnycWaterEfficiency($job_id,$prev_job_sql2){

	$today_full_ts = date('Y-m-d H:i:s');

	$prev_job2 = mysql_fetch_array($prev_job_sql2);
	
	$pj_sql = mysql_query("
		SELECT *
		FROM `jobs`
		WHERE `id` = {$prev_job2['id']}
	");
	$prev_job = mysql_fetch_array($pj_sql);	
	
	// get previous job and insert previous WE to this job
	mysql_query("
		INSERT INTO 
		`water_efficiency` (
			`job_id`,
			`device`,
			`location`,
			`note`,
			`created_date`
		)
		SELECT 
			'{$job_id}', 
			`device`,
			`location`,
			`note`,
			'{$today_full_ts}'
		FROM `water_efficiency`
		WHERE `job_id` = {$prev_job['id']}
	");


}

function getPrevWaterMeter($prop_id){
	// get safety switch that is job status completed
	return mysql_query("
		SELECT DISTINCT j.`id`
		FROM `water_meter` AS wm
		LEFT JOIN `jobs` AS j ON j.`id` = wm.`job_id`
		WHERE j.`property_id` = {$prop_id}
		AND j.status IN('Completed','Merged Certificates')
		AND j.`id` != ''
		AND j.`del_job` = 0
		ORDER BY j.`date` DESC, j.`id` DESC
		LIMIT 0,1
	");
}

function SnycWaterMeter($job_id,$prev_job_sql2){

	$prev_job2 = mysql_fetch_array($prev_job_sql2);
	
	$pj_sql = mysql_query("
		SELECT *
		FROM `jobs`
		WHERE `id` = {$prev_job2['id']}
	");
	$prev_job = mysql_fetch_array($pj_sql);

	
	// get previous job and insert previous corded window to this job
	$ss_sql2 = mysql_query("
		INSERT INTO 
		`water_meter` (
			`job_id`,
			`location`,
			`meter_image`,
			`created_date`,
			`active`
		)
		SELECT 
			'{$job_id}', 
			`location`,
			`meter_image`,
			'".date('Y-m-d H:i:s')."',
			'1'
		FROM `water_meter`
		WHERE `job_id` = {$prev_job['id']}
	");

}


function getPrevSafetySwitch($prop_id){
	// get safety switch that is job status completed
	return mysql_query("
		SELECT DISTINCT j.`id`
		FROM `safety_switch` AS ss
		LEFT JOIN `jobs` AS j ON j.`id` = ss.`job_id`
		WHERE j.`property_id` ={$prop_id}
		AND j.status IN('Completed','Merged Certificates')
		AND j.`id` != ''
		AND j.`del_job` = 0
		AND j.`assigned_tech` != 1
		AND j.`assigned_tech` != 2
		AND ss.`discarded` = 0
		ORDER BY j.`date` DESC, j.`id` DESC
		LIMIT 0,1
	");
}


function SnycSafetySwitchData($job_id,$prev_job_sql2){

	//get the property id
	$p_sql = mysql_query("
		SELECT `property_id`
		FROM `jobs` 
		WHERE `id` = {$job_id}
	");
	$p = mysql_fetch_array($p_sql);
	
	// check if no SS data yet
	$ss_sql = mysql_query("
		SELECT *
		FROM `safety_switch` AS ss
		LEFT JOIN `jobs` AS j ON ss.`job_id` = j.`id`
		WHERE j.`property_id` ={$p['property_id']}
		AND j.status = 'Completed'
		AND ss.`discarded` = 0
	");
	
	// has already SS data, get previous SS data
	if(mysql_num_rows($ss_sql)>0){
	
		// get previous SS data
		$prev_job2 = mysql_fetch_array($prev_job_sql2);
		
		$pj_sql = mysql_query("
			SELECT *
			FROM `jobs`
			WHERE `id` = {$prev_job2['id']}
		");
		$prev_job = mysql_fetch_array($pj_sql);
		
		// update safety switch job details
		mysql_query("
			UPDATE `jobs`
			SET 
				`ss_location` = '{$prev_job['ss_location']}',
				`ss_quantity` = '{$prev_job['ss_quantity']}'
			WHERE `id` = {$job_id}
		");
	
	// no SS data yet, get it from alarm
	}else{
			
		// get previous SA data
		$sa_job_sql2 = getPrevSmokeAlarm($p['property_id']);
		$prev_job2 = mysql_fetch_array($sa_job_sql2);
		
		$pj_sql = mysql_query("
			SELECT *
			FROM `jobs`
			WHERE `id` = {$prev_job2['id']}
		");
		$prev_job = mysql_fetch_array($pj_sql);
		
		// update safety switch job details
		mysql_query("
			UPDATE `jobs`
			SET 
				`ss_location` = '{$prev_job['ss_location']}',
				`ss_quantity` = '{$prev_job['ss_quantity']}'
			WHERE `id` = {$job_id}
		");
	
	}
	
	

	
	
	// get previous job and insert previous safety switch to this job
	$ss_sql2 = mysql_query("
		INSERT INTO 
		`safety_switch` (
			`job_id`, 
			`make`, 
			`model`,
			`ss_stock_id`
		)
		SELECT {$job_id}, `make`, `model`, `ss_stock_id`
		FROM `safety_switch`
		WHERE `job_id` = {$prev_job['id']}
		AND `discarded` = 0
	");

}


function markAsSyncBundle($bundle_id){

	// marked as synced
	mysql_query("
		UPDATE `bundle_services`
		SET `sync` = 1
		WHERE `bundle_services_id` = {$bundle_id}
	");

}

function markAsSync($job_id,$jserv){

	switch($jserv){
			case 2:
				$sync_field = '`alarms_synced`';
			break;
			// SA IC
			case 12:
				$sync_field = '`alarms_synced`';
			break;
			case 5:
				$sync_field = '`ss_sync`';
			break;
			case 6:
				$sync_field = '`cw_sync`';
			break;
			case 7:
				$sync_field = '`wm_sync`';
			break;
			case 15: // WE
				$sync_field = '`we_sync`';
			break;
		}

	// mark as sync
	mysql_query("
		UPDATE `jobs`
		SET 
			{$sync_field} = 1
		WHERE `id` = {$job_id}
	");

}


function runSync($job_id,$jserv,$bundle_id_param){


	// get job details
	$jsql5 = jGetJobDetails($job_id);
	$j5 = mysql_fetch_array($jsql5);

	//if bundle
	if($j5['bundle']==1){

		// get bundle id
		$bun_ids = explode(",",trim($j5['bundle_ids']));
		
		$bundle_id = ($bundle_id_param!="")?$bundle_id_param:$bun_ids[0];

		// check if jobs are already synced
		$js_sql = mysql_query("
			SELECT *
			FROM `bundle_services`
			WHERE `bundle_services_id` = {$bundle_id}
		");
		$js = mysql_fetch_array($js_sql);

		// if not yet snyc, do sync
		if($js['sync']==0){

			// get previous safety switch that is job status completed
			switch($jserv){
				case 2:
					$prev_job_sql = getPrevSmokeAlarm($j5['property_id']);
				break;
				// SA IC
				case 12:
					$prev_job_sql = getPrevSmokeAlarm($j5['property_id']);
				break;
				case 5:
					$prev_job_sql = getPrevSafetySwitch($j5['property_id']);
				break;
				case 6:
					$prev_job_sql = getPrevCordedWindow($j5['property_id']);
				break;
				case 7:
					$prev_job_sql = getPrevWaterMeter($j5['property_id']);
				break;
				case 15: // WE
					$prev_job_sql = getPrevWaterEfficiency($j5['property_id']);
				break;
			}
			
					
			
			if(mysql_num_rows($prev_job_sql)>0){
			
				switch($jserv){
					case 2:
						SnycSmokeAlarmData($job_id,$prev_job_sql);
					break;
					// SA IC
					case 12:
						SnycSmokeAlarmData($job_id,$prev_job_sql);
					break;
					case 5:
						SnycSafetySwitchData($job_id,$prev_job_sql);
					break;
					case 6:
						SnycCordedWindowData($job_id,$prev_job_sql);
					break;
					case 7:
						SnycWaterMeter($job_id,$prev_job_sql);
					break;
					case 15: // WE
						SnycWaterEfficiency($job_id,$prev_job_sql);
					break;
				}
				
				
										
			}	
			
			markAsSyncBundle($bundle_id);		

			
		}
		

	}else{

		switch($jserv){
			case 2:
				$is_sync = $j5['alarms_synced'];
			break;
			// SA IC
			case 12:
				$is_sync = $j5['alarms_synced'];
			break;
			case 5:
				$is_sync = $j5['ss_sync'];
			break;
			case 6:
				$is_sync = $j5['cw_sync'];
			break;
			case 7:
				$is_sync = $j5['wm_sync'];
			break;
			case 15: // WE
				$is_sync = $j5['we_sync'];
			break;
		}
		if($is_sync==0){
		
			// get previous safety switch that is job status completed
			switch($jserv){
				case 2:
					$prev_job_sql = getPrevSmokeAlarm($j5['property_id']);
				break;
				// SA IC
				case 12:
					$prev_job_sql = getPrevSmokeAlarm($j5['property_id']);
				break;
				case 5:
					$prev_job_sql = getPrevSafetySwitch($j5['property_id']);
					if(mysql_num_rows($prev_job_sql)==0){
						$prev_job_sql = getPrevSmokeAlarm($j5['property_id']);						
					}
				break;
				case 6:
					$prev_job_sql = getPrevCordedWindow($j5['property_id']);
				break;
				case 7:
					$prev_job_sql = getPrevWaterMeter($j5['property_id']);
				break;
				case 15: // WE
					$prev_job_sql = getPrevWaterEfficiency($j5['property_id']);
				break;
			}
			
			if(mysql_num_rows($prev_job_sql)>0){
			
				switch($jserv){
					case 2:
						SnycSmokeAlarmData($job_id,$prev_job_sql);
					break;
					// SA IC
					case 12:
						SnycSmokeAlarmData($job_id,$prev_job_sql);
					break;
					case 5:
						SnycSafetySwitchData($job_id,$prev_job_sql);
					break;
					case 6:
						SnycCordedWindowData($job_id,$prev_job_sql);
					break;
					case 7:
						SnycWaterMeter($job_id,$prev_job_sql);
					break;
					case 15: // WE
						SnycWaterEfficiency($job_id,$prev_job_sql);
					break;
				}
										
			}
			
			markAsSync($job_id,$jserv);

		
		}

	}

}



function sync_alarms($params){

	// get previous smoke alarms that is job status completed
	$prev_job_sql = getPrevSmokeAlarm($params['property_id']);
	
	if( mysql_num_rows($prev_job_sql) > 0 && $params['job_id'] > 0 ){

		// sync alarms
		SnycSmokeAlarmData($params['job_id'],$prev_job_sql);
		// mark job as sync
		mysql_query("
			UPDATE `jobs`
			SET `alarms_synced` = 1
			WHERE `id` = {$params['job_id']}
		");		

	}

	

}


function find_already_snyced_job_without_service_data($params){

	$ret = 0;

	// detect if 'Levels in Property' already has value and sync marker already set
	if( $params['bundle_services_id'] !='' ){ // BUNDLE

		// bundle service has synced
		$bs_sql = mysql_query("
			SELECT `bundle_services_id`
			FROM `bundle_services`
			WHERE `bundle_services_id` = {$params['bundle_services_id']}
			AND `sync` = 1
		");

		// property level has value
		$job_sql = mysql_query("
			SELECT `id`
			FROM `jobs`
			WHERE `id` = {$params['job_id']}
			AND `survey_numlevels` != ''
		");

		if( mysql_num_rows($bs_sql) > 0 && mysql_num_rows($job_sql) > 0 ){
			$ret = 1;
		}else{
			$ret = 0;
		}

	}else{ // SINGLE SERVICE

		// property level has value and sync marker already set
		$job_sql = mysql_query("
			SELECT `id`
			FROM `jobs`
			WHERE `id` = {$params['job_id']}
			AND `survey_numlevels` != ''
			AND `{$params['snyc_marker']}` = 1
		");

		$ret = 1;

	}
	

	// get job service data
	if( $params['service_id'] == 2 ||  $params['service_id'] == 12 ){ // SA or SA(IC)

		$serv_sql = mysql_query("
			SELECT `alarm_id`
			FROM `alarm`
			WHERE `job_id` = {$params['job_id']}
			AND `ts_discarded` = 0
		");

	}else if( $params['service_id'] == 5 ){ // SS

		$serv_sql = mysql_query("
			SELECT `safety_switch_id`
			FROM `safety_switch`
			WHERE `job_id` = {$params['job_id']}
		");

	}else if( $params['service_id'] == 6 ){ // CW

		$serv_sql = mysql_query("
			SELECT `corded_window_id`
			FROM `corded_window`
			WHERE `job_id` = {$params['job_id']}
		");

	}else if( $params['service_id'] == 7 ){ // WM

		$serv_sql = mysql_query("
			SELECT `water_meter_id`
			FROM `water_meter`
			WHERE `job_id` = {$params['job_id']}
		");

	}

	// has levels of property, sync set and alarm empty
	if( $ret == 1 && mysql_num_rows($serv_sql) == 0 ){
		return true;
	}else{
		return false;
	}

}



function jGetJobDetails($job_id){
	
	// get job details
	return mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		WHERE j.`id` = {$job_id}
	");

}

// get bundled services
function getbundleServices($job_id,$bs_id,$is_limit){
	$str = "";
	if($bs_id!=""){
		$str .= "AND `bundle_services_id` = {$bs_id}";
	}
	if($is_limit!=""){
		$limit_str = "LIMIT 1";
	}
	return mysql_query("
		SELECT *
		FROM `bundle_services` AS bs
		LEFT JOIN `alarm_job_type` AS ajt ON ajt.`id` = bs.`alarm_job_type_id`
		WHERE `job_id` = {$job_id}
		{$str}
		ORDER BY ajt.`id`
		{$limit_str}
	");
}

// new
function getStaffCountries($staff_id,$order_by){
	
	if($order_by!=""){
		$str = " ORDER BY {$order_by} ASC ";
	}else{
		$str = "";
	}
	
	return mysql_query("
		SELECT *
		FROM `country_access` AS ca
		LEFT JOIN `countries` AS c ON ca.`country_id` = c.`country_id`
		WHERE ca.`staff_accounts_id` = {$staff_id}
		{$str}
	");
	
}

function countrySelectedDefault($staff_id,$country_id){
	$sql = mysql_query("
		SELECT *
		FROM `country_access` 
		WHERE `staff_accounts_id` = {$staff_id}
		AND `country_id` = {$country_id}
		AND `default` = 1
	");
	if( mysql_num_rows($sql)>0 ){
		return true;
	}else{
		return false;
	}
}

function getCountrySelectedDefault($staff_id){
	$sql = mysql_query("
		SELECT *
		FROM `country_access` AS ca
		LEFT JOIN `countries` AS c ON ca.`country_id` = c.`country_id`
		WHERE ca.`staff_accounts_id` = {$staff_id}
		AND ca.`default` = 1
	");
	return $sql;
}

function clearCountryDefault($staff_id){
	mysql_query("
		UPDATE `country_access`
		SET `default` = NULL
		WHERE `staff_accounts_id` = {$staff_id}
	");
}

function setCountryDefault($staff_id,$country_id){
	mysql_query("
		UPDATE `country_access`
		SET `default` = 1
		WHERE `staff_accounts_id` = {$staff_id}
		AND `country_id` = {$country_id}
	");
}

function ifCountryHasState($country_id){
	$sql = mysql_query("
		SELECT *
		FROM `countries`
		WHERE `country_id` = {$country_id}
	");
	$row = mysql_fetch_array($sql);
	if($row['states']==1){
		return true;
	}else{
		return false;
	}
}

function getCountries(){
	return mysql_query("
		SELECT *
		FROM `countries`
	");	
}

function getGoogleMapCoordinates($address){
	
	$coordinates = array();
	
	// init curl object        
	$ch = curl_init();
	
	$API_key = GOOGLE_DEV_API;

	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".rawurlencode($address)."&key={$API_key}";

	// define options
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);

	// apply those options
	curl_setopt_array($ch, $optArray);

	// execute request and get response
	$result = curl_exec($ch);


	$result_json = json_decode($result);
	
	$lat = $result_json->results[0]->geometry->location->lat;
	$lng = $result_json->results[0]->geometry->location->lng;
	
	//$coordinates = "{ lat: {$lat}, lng: {$lng} }";
	//$coordinates = $result_json->results[0]->geometry->location;
	$coordinates['lat'] = $lat;
	$coordinates['lng'] = $lng;
	
	curl_close($ch);
	
	return $coordinates;
	
}

function getJobsTotalRoutes($tech_id,$date,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	$j_sql = mysql_query("
		SELECT count( j.`id` ) AS jcount
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.date = '{$date}'
		AND p.deleted =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND j.`sort_date` = '{$date}'
		AND a.`country_id` = {$country_id}
	");
	$j = mysql_fetch_array($j_sql);
	
	return $j['jcount'];
	
}


function getJobsTotalRoutes2($tech_id,$date,$sub_regions,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	$region_str = getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id);
	
	$j_sql = mysql_query("
		SELECT count( j.`id` ) AS jcount
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		{$region_str}
	");
	$j = mysql_fetch_array($j_sql);
	
	return $j['jcount'];
	
}

function getTotalKeyRoutes($tech_id,$date,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	$k_sql = mysql_query("
		SELECT count( * ) AS jcount
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`tech_id` = {$tech_id}
		AND kr.`date` = '{$date}'
		AND kr.`deleted` IS NULL
		AND a.`country_id` = {$country_id}
	");
	$k = mysql_fetch_array($k_sql);
	
	return $k['jcount'];
	
}

function manualSortJobBySortOrder($order_by,$tech_id,$date,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	// update sort order
	$sql = "
		SELECT *
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.date = '".$date."'
		AND p.deleted =0
		AND a.`country_id` = {$country_id}
		ORDER BY p.`address_3` {$order_by}
	";
	$result = mysql_query($sql);
	if(mysql_num_rows($result)>0){
		
		$ctr = 2;
		while($row = mysql_fetch_array($result)){
			
			// update sort
			mysql_query("
				UPDATE `jobs`
				SET 
					`sort_order` = {$ctr},
					`sort_date` = '{$date}'
				WHERE `id` = {$row['id']}
			");
			
			$ctr++;

			
		}
	}
}

function manualSortByKeys($order_by,$tech_id,$date){
	
	// KEYS
	$tot_map_routes = getJobsTotalRoutes($tech_id,$date)+2;
	$kr_sql_str = "
		SELECT *
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`tech_id` = {$tech_id}
		AND kr.`date` = '{$date}'
		AND kr.`deleted` IS NULL
		ORDER BY a.`address_3` {$order_by}
	";
	$kr_sql = mysql_query($kr_sql_str);
	if(mysql_num_rows($kr_sql)>0){
		
		while($kr = mysql_fetch_array($kr_sql)){
			//echo "<br />";
			$sql2 = "
				UPDATE `key_routes`
				SET `sort_order` = {$tot_map_routes}
				WHERE `key_routes_id` = {$kr['key_routes_id']}
			";
			mysql_query($sql2);
			$tot_map_routes++;
		}
		
	}
		
}


function getStaffByCountry(){
	return mysql_query("
			SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
			FROM staff_accounts AS sa
			INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
			WHERE sa.deleted =0
			AND sa.active =1			
			AND ca.`country_id` ={$_SESSION['country_default']}
			ORDER BY sa.`FirstName`
		");
}

function getAgencyRegionsByCountry(){
	return mysql_query("
		SELECT *
		FROM agency_regions
		WHERE `country_id` ={$_SESSION['country_default']}
		ORDER BY `agency_region_name` 
	");
}

function getAgencySalesRep($status){
	return mysql_query("
		SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`
		FROM `agency` AS a
		LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
		WHERE a.`status` = '{$status}'
		AND a.`country_id` ={$_SESSION['country_default']}
		AND a.`salesrep` !=0
		ORDER BY sa.`FirstName`
	");
}

function getCountryState(){
	$sql = "
		SELECT *
		FROM `states_def`
		WHERE `country_id` ={$_SESSION['country_default']}
	";
	return mysql_query($sql);
}

function getStateViaCountry($country_id){
	$sql = "
		SELECT *
		FROM `states_def`
		WHERE `country_id` ={$country_id}
	";
	return mysql_query($sql);
}

function getAgencyUsingByCountry(){
	return mysql_query("
		SELECT *
		FROM `agency_using`
		WHERE `country_id` ={$_SESSION['country_default']}
		ORDER BY `name` ASC
	");
}

function getServiceCount($agency_id,$ajt){
	$ps_sql = mysql_query("
		SELECT count(ps.`property_services_id`) as jcount
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE p.`agency_id` ={$agency_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` =1
	");
	$ps = mysql_fetch_array($ps_sql);
	return $ps['jcount'];
}


function get_agency_list($get_all,$offset,$limit,$sort,$order_by,$state,$salesrep,$region,$phrase){
	
	// country
	$str = " AND a.`country_id` = {$_SESSION['country_default']} ";

	// state
	if($state!=""){
		$str .= "AND LOWER(a.state) LIKE '%{$state}%' ";
	}
	
	// sales rep
	if($salesrep!=""){
		$str .= " AND a.`salesrep` = {$salesrep} ";
	}
	
	// region
	if($region!=""){
		//$str .= " AND pr.`postcode_region_id` = {$region} ";
		$str .= " AND a.`postcode` IN ( {$region} ) ";
	}

	
	// phrase
	if($phrase!=""){
		$str .= "AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(s.FirstName), LOWER(s.LastName), LOWER(a.state), LOWER(ar.agency_region_name), LOWER(a.`account_emails`), LOWER(a.`agency_emails`), LOWER(a.`contact_email`) ) LIKE '%{$phrase}%') ";
	}
	
	if( $sort!='' && $order_by!='' ){
		$str .= " ORDER BY {$sort} {$order_by} ";
	}
	
	// pagination limit
	if($get_all==1){
		$str .= "";
	}else{
		$str .= "LIMIT {$offset}, {$limit}";
	}
			
	$sql = "
		SELECT *
		FROM
		  agency a
		LEFT JOIN  agency_regions ar USING (agency_region_id)
		LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
		LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
		WHERE (
			a.status = 'active' 
		  )
		{$str}
   ";
   
   return mysql_query ($sql);

}

function getTechniciansByCountry($order_by,$sort,$order_by2,$sort2){
	return mysql_query("
		SELECT sa.FirstName, sa.LastName, sa.sa_position, sa.ContactNumber, sa.Email, sa.active, sa.is_electrician, sa.`dha_card`, sa.`StaffID` 
		FROM `staff_accounts` AS sa
		LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
		WHERE ca.`country_id` ={$_SESSION['country_default']}
		AND sa.`ClassID` = 6
		AND sa.`Deleted` = 0
		AND sa.`active` = 1
		ORDER BY {$order_by} {$sort}, {$order_by2} {$sort2}
	");
}

function getCountryViaCountryId($country_id){
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	return mysql_query("
		SELECT *
		FROM `countries`
		WHERE `country_id` = {$country_id}
	");
}

function getResourcesHeaders(){
	return mysql_query("
		SELECT *
		FROM `resources_header`
		WHERE `country_id` = {$_SESSION['country_default']}
	");
}

function checkKeySameSortOrder($tech_id,$date,$i,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	return mysql_query("
		SELECT *
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`tech_id` = {$tech_id}
		AND kr.`date` = '{$date}'
		AND kr.`sort_order` = {$i}
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$country_id}
	");
	
}

function updateMapListing($tech_id,$date,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	$str = "
		SELECT j.`id` 
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE j.`assigned_tech` ={$tech_id}
		AND j.date = '{$date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		ORDER BY j.`sort_order`
	";

	$sql = mysql_query($str);

	$i = 2;
	while($row = mysql_fetch_array($sql)){
		
		
		$key_sql = checkKeySameSortOrder($tech_id,$date,$i,$country_id);
		
		// there is key that has that sort number, so avoid overwriting
		while(mysql_num_rows($key_sql)>0){
			++$i; //increment current index to avoid overwriting keys existing sort order
			$key_sql = checkKeySameSortOrder($tech_id,$date,$i,$country_id);
		}
		
		// update sort order
		$str2 = "
			UPDATE `jobs`
			SET 
				`sort_order` = {$i},
				`sort_date` = '{$date}'
			WHERE `id` = {$row['id']}
		";
		mysql_query($str2);
		$i++;
		
	}

	$kr_sql_str = "
		SELECT *
			FROM `key_routes` AS kr
			LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
			WHERE kr.`tech_id` = {$tech_id}
			AND kr.`date` = '{$date}'
			AND kr.`sort_order` >= {$i}
			AND ( 
				kr.`deleted` = 0 
				OR kr.`deleted` IS NULL 
			)
			AND a.`country_id` = {$country_id}
			ORDER BY kr.`sort_order` ASC
	";
	$kr_sql = mysql_query($kr_sql_str);
	if(mysql_num_rows($kr_sql)>0){
		
		while($kr = mysql_fetch_array($kr_sql)){
			//echo "<br />";
			$sql2 = "
				UPDATE `key_routes`
				SET `sort_order` = {$i}
				WHERE `key_routes_id` = {$kr['key_routes_id']}
			";
			mysql_query($sql2);
			$i++;
		}
		
	}
	
}

// new map update listing with sub regions
function updateMapListing2($tech_id,$date,$sub_regions,$country_id=''){
	
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	$region_str = getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id);
	
	$str = "
		SELECT j.`id` 
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		{$region_str}
		ORDER BY j.`sort_order`
	";

	$sql = mysql_query($str);

	$i = 2;
	while($row = mysql_fetch_array($sql)){
		
		
		$key_sql = checkKeySameSortOrder($tech_id,$date,$i,$country_id);
		
		// there is key that has that sort number, so avoid overwriting
		while(mysql_num_rows($key_sql)>0){
			++$i; //increment current index to avoid overwriting keys existing sort order
			$key_sql = checkKeySameSortOrder($tech_id,$date,$i,$country_id);
		}
		
		// update sort order
		$str2 = "
			UPDATE `jobs`
			SET 
				`sort_order` = {$i},
				`sort_date` = '{$date}'
			WHERE `id` = {$row['id']}
		";
		mysql_query($str2);
		$i++;
		
	}

	$kr_sql_str = "
		SELECT *
			FROM `key_routes` AS kr
			LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
			WHERE kr.`tech_id` = {$tech_id}
			AND kr.`date` = '{$date}'
			AND kr.`sort_order` >= {$i}
			AND ( 
				kr.`deleted` = 0 
				OR kr.`deleted` IS NULL 
			)
			AND a.`country_id` = {$country_id}
			ORDER BY kr.`sort_order` ASC
	";
	$kr_sql = mysql_query($kr_sql_str);
	if(mysql_num_rows($kr_sql)>0){
		
		while($kr = mysql_fetch_array($kr_sql)){
			//echo "<br />";
			$sql2 = "
				UPDATE `key_routes`
				SET `sort_order` = {$i}
				WHERE `key_routes_id` = {$kr['key_routes_id']}
			";
			mysql_query($sql2);
			$i++;
		}
		
	}
	
}

function getJobTypeAbbrv($jt){
	
	// job type
	switch($jt){
		case 'Once-off':
			$jt = 'Once-off';
		break;
		case 'Change of Tenancy':
			$jt = 'COT';
		break;
		case 'Yearly Maintenance':
			$jt = 'YM';
		break;
		case 'Fix or Replace':
			$jt = 'FR';
		break;
		case '240v Rebook':
			$jt = '240v';
		break;
		case 'Lease Renewal':
			$jt = 'LR';
		break;
	}
	return $jt;
	
}

function getServiceIcons($service,$color=''){
	
	switch($color){
		case 1:
			$color_str = 'white';
		break;
		case 2:
			$color_str = 'grey';
		break;
		default:
			$color_str = 'colored';
	}
	
	switch($service){
		case 2:
			$serv_icon = 'smoke_'.$color_str.'.png';
		break;
		case 5:
			$serv_icon = 'safety_'.$color_str.'.png';
		break;
		case 6:
			$serv_icon = 'corded_'.$color_str.'.png';
		break;
		case 7:
			$serv_icon = 'water_'.$color_str.'.png';
		break;
		case 8:
			$serv_icon = 'sa_ss_'.$color_str.'.png';
		break;
		case 9:
			$serv_icon = 'sa_cw_ss_'.$color_str.'.png';
		break;
		case 11:
			$serv_icon = 'sa_wm_'.$color_str.'.png';
		break;
		case 12:
			$serv_icon = 'sa_'.$color_str.'_IC.png';
		break;
		case 13:
			$serv_icon = 'sa_ss_'.$color_str.'_IC.png';
		break;
		case 14:
			$serv_icon = 'sa_cw_ss_'.$color_str.'_IC.png';
		break;
		case 15: // Water Efficiency
			$serv_icon = 'we_' . $color_str . '.png';
		break;
		case 16: // Smoke Alarms & Water Efficiency
			$serv_icon = 'sawe_' . $color_str . '.png';
		break;
		case 17: // Bundle SA.SS.WE
			$serv_icon = 'sasswe_' . $color_str . '.png';
		break;
		case 18: // Bundle SA.SS.CW.WE
			$serv_icon = 'sasscwwe_' . $color_str . '.png';
		break;
		case 19: // Bundle SA.CW
			$serv_icon = 'sacw_' . $color_str . '.png';
		break;
		case 20: // Bundle SA.CW(IC)
			$serv_icon = 'sacw_' . $color_str . '_IC.png';
		break;
	}
	
	return $serv_icon;
	
}

/*
function getServiceIcons_v2($service,$color='',$show_ic_icon=0){
	
	$append_ic = ($show_ic_icon == 1)?'_IC':'';
	
	switch($color){
		case 1:
			$color_str = 'white';
		break;
		case 2:
			$color_str = 'grey';
		break;
		default:
			$color_str = 'colored';
	}
	
	
	if( $service == 2 || $service == 12 ){ // Smoke Alarm
		$serv_icon = 'smoke_'.$color_str.$append_ic.'.png';
	}else if( $service == 5 ){ // Safety Switch
		$serv_icon = 'safety_'.$color_str.$append_ic.'.png';
	}else if( $service == 6 ){ // Corded Window
		$serv_icon = 'corded_'.$color_str.$append_ic.'.png';
	}else if( $service == 7 ){ // Water meter
		$serv_icon = 'water_'.$color_str.$append_ic.'.png';
	}else if( $service == 11 ){ // Smoke Alarm and Water Meter Bundle
		$serv_icon = 'sa_wm_'.$color_str.$append_ic.'.png';
	}else if( $service == 8 || $service == 13 ){ // Smoke Alarm and Safety Switch Bundle
		$serv_icon = 'sa_ss_'.$color_str.$append_ic.'.png';
	}else if( $service == 9 || $service == 14 ){ // Smoke Alarm, Corded Window and Safety Switch Bundle
		$serv_icon = 'sa_cw_ss_'.$color_str.$append_ic.'.png';
	}else if( $service == 15 ){ // Water Efficiency
		$serv_icon = 'we_'.$color_str.$append_ic.'.png';
	}else if( $service == 16 ){ // Smoke Alarms & Water Efficiency
		$serv_icon = 'sawe_'.$color_str.$append_ic.'.png';
	}else if( $service == 17 ){ // Bundle SA.SS.WE
		$serv_icon = 'sasswe_'.$color_str.$append_ic.'.png';
	}else if( $service == 18 ){ // Bundle SA.SS.CW.WE
		$serv_icon = 'sasscwwe_'.$color_str.$append_ic.'.png';
	}
	
	return $serv_icon;
	
}
*/


function jFormatDateToBeDbReady($date){
	return date('Y-m-d',strtotime(str_replace("/","-",mysql_real_escape_string($date))));
}

function getJobRouteList2($tech_id,$date,$country_id=''){
	
	$tech_id = ($tech_id!="")?mysql_real_escape_string($tech_id):'';
	$date = ($date!="")?mysql_real_escape_string($date):'';
	$country_id = ($country_id!="")?mysql_real_escape_string($country_id):$_SESSION['country_default'];
	
	$selectQuery = "SELECT 
			j.`id` AS jid, j.`sort_order`, j.`job_type`, j.time_of_day, j.`tech_notes`, j.`status` AS j_status, j.`completed_timestamp`, j.`job_reason_id`, j.`ts_completed`, j.`service` AS j_service, j.`urgent_job`, j.`created`, j.`comments` AS j_comments,
			p.`property_id`, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`postcode` AS p_postcode, p.`key_number`, p.`lat` AS p_lat, p.`lng` AS p_lng,
			a.`agency_id`, a.`agency_name`, a.`address_1` AS a_address_1, a.`address_2` AS a_address_2, a.`address_3` AS a_address_3, a.`state` AS a_state, a.`postcode` AS a_postcode, a.`phone` AS a_phone
		FROM jobs AS j
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.date = '".$date."'
		AND p.deleted =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND j.`assigned_tech` ={$tech_id}
	";
	
   $result = mysql_query ($selectQuery);
   
   return $result;
	
}


function getKeyRouteList2($tech_id,$date,$country_id=''){
	
	$tech_id = ($tech_id!="")?mysql_real_escape_string($tech_id):'';
	$date = ($date!="")?mysql_real_escape_string($date):'';
	$country_id = ($country_id!="")?mysql_real_escape_string($country_id):$_SESSION['country_default'];
	
	$sql = "
		SELECT 
			kr.`key_routes_id`, kr.`action`, kr.`number_of_keys`, kr.`agency_staff`, kr.`completed`, kr.`completed_date`, kr.`sort_order`,
			a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`
		FROM `key_routes` AS kr
		LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
		WHERE kr.`date` = '{$date}'
		AND ( 
			kr.`deleted` = 0 
			OR kr.`deleted` IS NULL 
		)
		AND a.`country_id` = {$country_id}
		AND kr.`tech_id` ={$tech_id}
	";
	return mysql_query($sql);
	
}

function getBookingRegions($postcode){
	return mysql_query("
		SELECT `postcode_region_name`
		FROM  `postcode_regions` 
		WHERE `country_id` ={$_SESSION['country_default']}
		AND `deleted` = 0
		AND `postcode_region_postcodes` LIKE '%{$postcode}%'
	");
}

function getPostcodeRegion(){
	return mysql_query("
		SELECT *
		FROM  `postcode_regions` 
		WHERE `country_id` ={$_SESSION['country_default']}
		AND `deleted` = 0
		ORDER BY `postcode_region_name`
	");
}

function CountryISOName($country_id){
	switch($country_id){
		case 1:
			$c_iso = 'au';
		break;
		case 2:
			$c_iso = 'nz';
		break;
	}
	return $c_iso;
}

function jsanitize($input){
	return filter_var(trim($input), FILTER_SANITIZE_STRING);
}

function send_ical_to_mail($subject='', $to_name='', $to_email='', $event_name='', $description='', $date_start='', $date_end='' ){
	
// data
// santize input
$summary     = jsanitize($event_name);
$date = date("Ymd\THis");
$datestart   = date("Ymd\THis",strtotime(str_replace("/","-",jsanitize($date_start))));
$dateend     = date("Ymd\THis",strtotime(str_replace("/","-",jsanitize($date_end))));
$filename    = 'iCalendar'.date('YmdHis');

$eol = PHP_EOL;
$unique_id = md5(time());

$headers .= "MIME-version: 1.0".$eol;
$headers .= "Content-class: urn:content-classes:calendarmessage".$eol;
$headers .= "Content-type: text/calendar;name={$filename}.ics;method=REQUEST; charset=UTF-8".$eol;

// attachment
$message = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:{$unique_id}
DTSTAMP:{$date}
SUMMARY:{$summary}
DESCRIPTION:{$description}
DTSTART:{$datestart}
DTEND:{$dateend}
END:VEVENT
END:VCALENDAR
";

/*
echo $message;
echo "<br /><br />";
echo $to_email;
*/

// mail it
mail( $to_email, $subject, $message, $headers );
	
}

function getWaterMeter($job_id){
	return mysql_query("
		SELECT *
		FROM `water_meter`
		WHERE `job_id` = {$job_id}
	");
}

// WATER METER camera capture upload
function proccessWmUpload($files,$post){
	
	// upload
	if($files){

		$location = $post['location'];
		$reading = $post['reading'];
		$country_folder = "/".strtolower($_SESSION['country_iso']);
		$rand1 = "img".rand()."_";
		$rand2 = "img".rand()."_";
		
		$folder = "images/wm_ts{$country_folder}";
		//$folder = "temp_upload";
		
		$meter_image_changed = $post['meter_image_changed'];
		$meter_reading_image_changed = $post['meter_reading_image_changed'];
			
		// file error
		if( $files["meter_image"]["error"] > 0 && $meter_image_changed==1 ){
			$error1 .= "Meter Image Error: " . $files["meter_image"]["error"] . "<br>";
		}
		if( $files["meter_reading_image"]["error"] > 0 && $meter_reading_image_changed==1 ){
			$error2 .= "Meter Image Error: " . $files["meter_reading_image"]["error"] . "<br>";
		}
		
		// file limit
		if( $files["meter_image"]["size"] > 50000000 && $meter_image_changed==1 ){
			$error1 .= "Meter Image uploaded file is too large, file size limit is 50mb<br />";
		}
		if( $files["meter_reading_image"]["size"] > 50000000 && $meter_reading_image_changed==1 ){
			$error2 .= "Meter Reading uploaded file is too large, file size limit is 50mb<br />";
		}
		
		// if exist
		if( file_exists("{$folder}/{$rand1}" . $files["meter_image"]["name"]) && $meter_image_changed==1 ){
			$error1 .= $files["meter_image"]["name"] . " already exists. <br />";
		}
		if( file_exists("{$folder}/{$rand2}" . $files["meter_reading_image"]["name"]) && $meter_reading_image_changed==1 ){
			$error2 .= $files["meter_reading_image"]["name"] . " already exists. <br />";
		}
			
		
		
		
			
		// if folder does not exist, make one
		if(!is_dir($folder)){
			mkdir($folder);
		}
		
		if($error1==""){
			// upload file
			$meter_image = "{$folder}/{$rand1}" . mysql_real_escape_string($files["meter_image"]["name"]);
			if(move_uploaded_file($files["meter_image"]["tmp_name"],$meter_image)){	
				//$db_insert .= "`meter_image` = '".mysql_real_escape_string($meter_image)."',";
				$db_ret['meter_image'] = $meter_image;
			}
		}
		
		if($error2==""){
			// upload file
			$meter_reading_image = "{$folder}/{$rand2}" . mysql_real_escape_string($files["meter_reading_image"]["name"]);
			if(move_uploaded_file($files["meter_reading_image"]["tmp_name"],$meter_reading_image)){	
				//$db_insert .= "`meter_reading_image` = '".mysql_real_escape_string($meter_reading_image)."',";	
				$db_ret['meter_reading_image'] = $meter_reading_image;
			}
		}
		
		$db_ret['error'] = $error1.$error2;
			
		return $db_ret;
		
	}
	
}

// WATER METER camera capture upload
function proccessWmUpload2($files,$post,$job_id){
	
	// upload
	if($files){

		$location = $post['location'];
		$reading = $post['reading'];
		$country_folder = "/".strtolower($_SESSION['country_iso']);
		$rand1 = "img_{$job_id}_".rand();
		$rand2 = "img_{$job_id}_".rand();
		
		$folder = "images/wm_ts{$country_folder}";
		//$folder = "temp_upload";
		
		$meter_image_changed = $post['meter_image_changed'];
		$meter_reading_image_changed = $post['meter_reading_image_changed'];
			
			
		// if folder does not exist, make one
		if(!is_dir($folder)){
			mkdir($folder);
		}
		
		// IMAGE 1
		$handle = new upload($_FILES['meter_image']);
		if ($handle->uploaded) {
		  $handle->file_new_name_body   = $rand1;
		  $handle->image_resize         = true;
		  $handle->image_x              = 760;
		  $handle->image_ratio_y        = true;
		  $handle->process($_SERVER['DOCUMENT_ROOT'].$folder);
		  if ($handle->processed) {
			//echo 'image resized';
			$db_ret['meter_image'] = "{$folder}/{$rand1}.jpg";
			$handle->clean();
		  } else {
			$error1 = 'error : ' . $handle->error;
		  }
		}
		
		// IMAGE 2
		$handle = new upload($_FILES['meter_reading_image']);
		if ($handle->uploaded) {
		  $handle->file_new_name_body   = $rand2;
		  $handle->image_resize         = true;
		  $handle->image_x              = 760;
		  $handle->image_ratio_y        = true;
		  $handle->process($_SERVER['DOCUMENT_ROOT'].$folder);
		  if ($handle->processed) {
			//echo 'image resized';
			$db_ret['meter_reading_image'] = "{$folder}/{$rand2}.jpg";
			$handle->clean();
		  } else {
			$error2 = 'error : ' . $handle->error;
		  }
		}
		
		$db_ret['error'] = $error1.$error2;
			
		return $db_ret;
		
	}
	
}

// generate QR code
function generate_qr_code($invoice_number,$property_id,$amount_due,$gst_amount,$due_date,$country_id){
	
	$country_id2 = ($country_id=="")?$_SESSION['country_default']:$country_id;
	
	$absolute_path = $_SERVER['DOCUMENT_ROOT'].'phpqrcode/temp/';
	$file_name = "invoice_{$invoice_number}_qr_code.png";
	
	$fin_path = $absolute_path.$file_name;
	
	// get country
	$cntry_sql = getCountryViaCountryId($country_id2);
	$cntry = mysql_fetch_array($cntry_sql);
	
	$bsb = str_replace(' ','',$cntry['bsb']);
	$bank_acc_num = str_replace('-','',str_replace(' ','',$cntry['ac_number']));
	$abn = str_replace(' ','',$cntry['abn']);
	$due_date = date("dmY",strtotime(str_replace('/','-',$due_date)));
	
	$data = "getpaidfaster.com.au/p 1={$bsb} 2={$bank_acc_num} 3={$amount_due} 4={$due_date} 5={$abn} 6= 7={$gst_amount} 8={$invoice_number} 9={$property_id}";
	
	// pack them on an array for return
	$qr_code['data'] = $data;
	$qr_code['path'] = $fin_path;

	return $qr_code;
    //$qr_code::png($data, $fin_path);
	
}

function appendPhonePrefix($tenant_mobile,$property_id){
	// get phone prefix
	$p_sql = mysql_query("
		SELECT *
		FROM `property` AS p
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE p.`property_id` ={$property_id}
	");
	$p = mysql_fetch_array($p_sql);

	// get phone prefix
	$prefix = $p['phone_prefix'];

	// tenant mobile 
	$trim = str_replace(' ', '', trim($tenant_mobile));

	// reformat number
	$remove_zero = substr($trim ,1);
	$mob = $prefix.$remove_zero;
	
	$sms_provider = SMS_PROVIDER;

	return $to = "{$mob}{$sms_provider}";
}

function send_letters_send_tenant_email($job_id,$staff_id,$country_id){
	
	unset($to_arr);
	unset($tenant_arr);
	$to_arr = array();
	$tenant_arr = array();
	
	
	// get country
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);
	
	
	// get phone prefix
	$p_sql = mysql_query("
		SELECT 
			p.`property_id`, 
			p.`tenant_firstname1`,
			p.`tenant_lastname1`,
			p.`tenant_email1`, 
			p.`tenant_firstname2`,
			p.`tenant_lastname2`,
			p.`tenant_email2`, 
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode, 
			
			a.`agency_name`,
			a.`agency_emails`,
			a.`new_job_email_to_agent`,
			
			ajt.`type`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		WHERE j.`id` ={$job_id}
	");
	$p = mysql_fetch_array($p_sql);
	
	$property_id = $p['property_id'];
	$agency_name = $p['agency_name'];
	$prop_address = "{$p['p_address_1']} {$p['p_address_2']} {$p['p_address_3']} {$p['p_state']} {$p['p_postcode']}";
	$new_job_email_to_agent = $p['new_job_email_to_agent'];
	
	echo "new_job_email_to_agent: {$new_job_email_to_agent}";

	
	
	
	// new tenants switch
	//$new_tenants = 0;
	$new_tenants = NEW_TENANTS;

	if( $new_tenants == 1 ){ // new

		$pt_params = array( 
			'property_id' => $property_id,
			'active' => 1
		 );
		$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
		
		while( $pt_row = mysql_fetch_array($pt_sql) ){
			
			// tenant emails
			if($pt_row['tenant_email']!=""){
				$to_arr[] = $pt_row['tenant_email'];
			}
			
			// tenant name
			if($pt_row['tenant_firstname']!=""){
				$tenant_arr[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
			}
			
			
		}
		
		$to = implode(",",$to_arr);
		
		if( count($tenant_arr) > 1 ){

			$tenant_str_imp = implode(", ",$tenant_arr); // separate tenant names with a comma
			$last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
			$tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)
		
		}else{
			$tenant_str = $tenant_arr[0];
		}

	}else{ // OLD TENANTS

		// tenant emails
		if($p['tenant_email1']!=""){
			$to_arr[] = $p['tenant_email1'];
		}

		if($p['tenant_email2']!=""){
			$to_arr[] = $p['tenant_email2'];
		}
		
		$to = implode(",",$to_arr);
		
		// tenant name
		if($p['tenant_firstname1']!=""){
			$tenant_arr[] = "{$p['tenant_firstname1']} {$p['tenant_lastname1']}";
		}

		if($p['tenant_firstname2']!=""){
			$tenant_arr[] = "{$p['tenant_firstname2']} {$p['tenant_lastname2']}";
		}

		$tenant_str = implode(" & ",$tenant_arr);
		
	}
	

	$body = '
	<p>'.date("F d, Y").'</p>
	
	<p>
	'.$tenant_str.'<br />
	'.$p['p_address_1'].' '.$p['p_address_2'].'<br />
	'.$p['p_address_3'].' '.$p['p_state'].' '.$p['p_postcode'].'<br />
	</p>

	<p>
	Dear '.$tenant_str.'
	</p>
	
	<p>
	Recently your Landlord and '.$agency_name.' engaged the services of '.$cntry['trading_name'].' (SATS) to service the 
	'.$p['type'].' at the property you occupy.
	</p>
	
	<p>
	The test will take approximately 5-30 minutes and is a mandatory requirement to ensure the safety of the people in 
	your home and is at no cost to you. Please be advised that your power may be disrupted during this time. 
	Any electrical devices that you feel may be affected should be disconnected from the power socket prior to our 
	technician attending.
	</p>
	
	<p>
	A representative from Smoke Alarm Testing Services will be in touch shortly to book in a schedule. You can be present during the testing or alternatively we will collect keys from '.$agency_name.' to access the property.
	</p>
	
	<p>
	You have the choice of:
	</p>
	
	<p>
	Being at home during the testing or arrange to have someone to allow access.
	</p>
	
	<p>
	Or
	</p>
	
	<p>
	Allow us to obtain keys from '.$agency_name.' to access the property.
	</p>
	
	<p>
	If you have any questions in regards to this matter please contact your property manager or our office on '.$cntry['tenant_number'].'.
	</p>

	<p>
	Yours Faithfully<br />
	<img src="https://sats.com.au/images/'.$cntry['email_signature'].'" /><br />
	Smoke Alarm Testing Services<br />
	</p>
	';

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= "To: {$to}" . "\r\n";
	$headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";

	$subj = 'Smoke Alarm Testing';
	
	
	// send email
	if(mail($to,$subj,$body,$headers)){
		
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
				'Welcome Email',
				'" . date('Y-m-d') . "',
				'Welcome Email Sent', 
				'{$job_id}',
				'".$staff_id."',
				'".date("H:i")."'
			)
		");
		
		// insert property logs
		mysql_query("
		INSERT INTO 
		property_event_log(
			`property_id`, 
			`staff_id`, 
			`event_type`, 
			`event_details`, 
			`log_date`
		)
		VALUES(
			'{$property_id}', 
			'".staff_id."', 
			'Welcome Email', 
			'Welcome Email Sent', 
			'".date('Y-m-d H:i:s')."'
		)
		");
		
		// if agency option 'new job email to agent' = yes
		if( $new_job_email_to_agent == 1 ){
			
			// send to agency
			unset($jemail);
			$jemail = array();
			$temp = explode("\n",trim($p['agency_emails']));
			foreach($temp as $val){
				
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$jemail[] = $val2;
				}
				
			}
			
			// send email
			$to2 = implode(",",$jemail);
			
			// subject
			$subject2 = "Tenant Notification {$p['p_address_1']} {$p['p_address_2']} {$p['p_address_3']}";

			$template = file_get_contents(EMAIL_TEMPLATE);

			#Set template title
			$template = str_replace("#title", "Letter Sent", $template);
			// replace trading name
			$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
			// replace image
			$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
			
			$html_content = "<p>Dear {$agency_name},</p>
			<p>The Tenants at {$prop_address} have now been notified that SATS will be contacting them to book an appointment to service their property.</p>
			<p>Any questions please feel free to contact us on {$cntry['agent_number']}</p>
			<br/>
			";

			# Populate Template
			$template = str_replace("#content", $html_content, $template);

			// To send HTML mail, the Content-type header must be set
			$headers2  = 'MIME-Version: 1.0' . "\r\n";
			$headers2 .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers2 .= "To: {$to2}" . "\r\n";
			$headers2 .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";
			mail($to2, $subject2, $template, $headers2);
			
		}		
		
		// update
		mysql_query("
			UPDATE property
			SET `tenant_ltr_sent` = '".date("Y-m-d")."'
			WHERE `property_id` = {$property_id}
		");	
		
		mysql_query("
			UPDATE `jobs`
			SET `status` = 'To Be Booked'
			WHERE `id` = {$job_id}
		");
		
		//echo 1;
	}else{
		//echo 0;
	}
	
	
}



function send_letters_send_tenant_sms($job_id,$staff_id,$country_id){
	
	unset($to_arr);
	unset($tenant_arr);
	$to_arr = array();
	$tenant_arr = array();
	
	// get country
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);

	
	// get phone prefix
	$p_sql = mysql_query("
		SELECT 
			*,
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE j.`id` ={$job_id}
	");
	$p = mysql_fetch_array($p_sql);
	
	$property_id = $p['property_id'];
	$agency_name = $p['agency_name'];
	$prop_address = "{$p['p_address_1']} {$p['p_address_2']} {$p['p_address_3']} {$p['p_state']} {$p['p_postcode']}";
	$new_job_email_to_agent = $p['new_job_email_to_agent'];

	
	// get phone prefix
	$prefix = $p['phone_prefix'];
	$sms_provider = SMS_PROVIDER;
	$num_tenants = getCurrentMaxTenants();
	$sent_by = $_SESSION['USER_DETAILS']['StaffID'];
	$tent_full_mob_num = [];
	$tenant_mob_arr = [];
	$ten_name = [];
	$ten_mob = '';
	
	
	
	// new tenants switch
	//$new_tenants = 0;
	$new_tenants = NEW_TENANTS;
	
	if( $new_tenants == 1 ){ // new
	
		$pt_params = array( 
			'property_id' => $property_id,
			'active' => 1
		 );
		$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
		
		while( $pt_row = mysql_fetch_array($pt_sql) ){
			
			// tenant mobile 
			$ten_mob = trim($pt_row["tenant_mobile"]);
			if($ten_mob!=''){
				$trimmed_mob = str_replace(' ', '', $ten_mob);
				// reformat number
				$remove_zero = substr($trimmed_mob,1);
				$mob = $prefix.$remove_zero;

				$tenant_mob_arr[] = "{$mob}{$sms_provider}";
				$tent_full_mob_num[] = $mob;
				
				
				// tenant name 
				$ten_name[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
			}
			
		}
	
	}else{ // OLD TENANTS
	
		for( $i=1; $i<=$num_tenants; $i++ ){
			
			// tenant mobile 
			$ten_mob = trim($p["tenant_mob{$i}"]);
			if($ten_mob!=''){
				$trimmed_mob = str_replace(' ', '', $ten_mob);
				// reformat number
				$remove_zero = substr($trimmed_mob,1);
				$mob = $prefix.$remove_zero;

				$tenant_mob_arr[] = "{$mob}{$sms_provider}";
				$tent_full_mob_num[] = $mob;
				
				
				// tenant name 
				$ten_name[] = "{$p['tenant_firstname'.$i]} {$p['tenant_lastname'.$i]}";
			}
			
		}
		
	}
	

	$body = "SATS have been asked to test the smoke alarms at the property you occupy. Our staff will contact you shortly to make an appointment. Any questions {$cntry['tenant_number']}";


	$headers .= "To: {$to}" . "\r\n";
	$headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";

	$subj = 'Smoke Alarm Testing';
	
	//echo $body;
	
	//mail($to,$subj,$body,$headers);
	

		
	// SEND SMS
	foreach( $tent_full_mob_num as $tent_mob ){
		
		// send SMS via API
		$sms_type = 24; // send letters
		$ws_sms = new WS_SMS($country_id,$body,$tent_mob);	
		$sms_res = $ws_sms->sendSMS();
		$ws_sms->captureSMSdata($sms_res,$job_id,$body,$tent_mob,$sent_by,$sms_type);
		//sleep(1);

		
	}
	
	// insert logs
	
	$tenant_names = implode(", ",$ten_name);
	
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
			'Welcome SMS sent',
			'" . date('Y-m-d') . "',
			'SMS to {$tenant_names} <strong>\"".mysql_real_escape_string($body)."\"</strong>', 
			'{$job_id}',
			'".$staff_id."',
			'".date("H:i")."'
		)
	");
	
	
	// insert property logs
	mysql_query("
	INSERT INTO 
	property_event_log(
		`property_id`, 
		`staff_id`, 
		`event_type`, 
		`event_details`, 
		`log_date`
	)
	VALUES(
		'{$property_id}', 
		'".$staff_id."', 
		'Welcome SMS', 
		'Welcome SMS Sent', 
		'".date('Y-m-d H:i:s')."'
	)
	");
	
	
	// if agency option 'new job email to agent' = yes
	if( $new_job_email_to_agent == 1 ){
		
		// send to agency
		unset($jemail);
		$jemail = array();
		$temp = explode("\n",trim($p['agency_emails']));
		foreach($temp as $val){
			
			$val2 = preg_replace('/\s+/', '', $val);
			if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
				$jemail[] = $val2;
			}
			
		}
		
		// send email
		$to2 = implode(",",$jemail);
		
		// subject
		$subject2 = "Tenant Notification {$p['p_address_1']} {$p['p_address_2']} {$p['p_address_3']}";

		$template = file_get_contents(EMAIL_TEMPLATE);

		#Set template title
		$template = str_replace("#title", "Letter Sent", $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		// replace image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		
		$html_content = "<p>Dear {$agency_name},</p>
		<p>The Tenants at {$prop_address} have now been notified that SATS will be contacting them to book an appointment to service their property.</p>
		<p>Any questions please feel free to contact us on {$cntry['agent_number']}</p>
		<br/>
		";

		# Populate Template
		$template = str_replace("#content", $html_content, $template);

		// To send HTML mail, the Content-type header must be set
		$headers2  = 'MIME-Version: 1.0' . "\r\n";
		$headers2 .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers2 .= "To: {$to2}" . "\r\n";
		$headers2 .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";
		mail($to2, $subject2, $template, $headers2);

	}

	
	// update
	mysql_query("
		UPDATE property
		SET `tenant_ltr_sent` = '".date("Y-m-d")."'
		WHERE `property_id` = {$property_id}
	");	
	
	mysql_query("
		UPDATE `jobs`
		SET `status` = 'To Be Booked'
		WHERE `id` = {$job_id}
	");
		
	
	
}

function send_letters_no_tenant_email($job_id,$staff_id,$country_id){
	
	// get country
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);
	
	// get property
	$j_sql = mysql_query("
		SELECT *, 
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode,
			
			a.`new_job_email_to_agent`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Send Letters'
		AND p.`deleted` =0
		AND j.`id` = {$job_id}
		AND a.`country_id` = {$country_id}
	");
	$j = mysql_fetch_array($j_sql);
	$property_id = $j['property_id'];
	$property_vacant = $j['property_vacant'];
	$new_job_email_to_agent = $j['new_job_email_to_agent'];
	
	
	// if agency option 'new job email to agent' = yes
	if( $new_job_email_to_agent == 1 ){
		
		// send email to agency
		unset($jemail);
		$jemail = array();
		$temp = explode("\n",trim($j['agency_emails']));
		foreach($temp as $val){
			
			$val2 = preg_replace('/\s+/', '', $val);
			if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
				$jemail[] = $val2;
			}
			
		}
		
		/*
		echo $to = implode(",",$jemail);
		echo "<br />";
		*/
		
		// send email
		$to = implode(",",$jemail);
		
		$agency_name = $j['agency_name'];
		$prop_address = "{$j['p_address_1']} {$j['p_address_2']} {$j['p_address_3']} {$j['p_state']} {$j['p_postcode']}";
		
		// subject
		$subject = "Ready for Booking {$j['p_aaddress_1']} {$j['p_aaddress_2']} {$j['p_aaddress_3']}";

		$template = file_get_contents(EMAIL_TEMPLATE);

		#Set template title
		$template = str_replace("#title", "Letter Sent", $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		// replace image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		
		$html_content = "<p>Dear {$agency_name},</p>
	   <p>{$prop_address} is now in our system ready for booking.</p>
	   <p>Any questions please feel free to contact us on {$cntry['agent_number']}</p>
	   <br/>
	   ";

		# Populate Template
		$template = str_replace("#content", $html_content, $template);

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		//echo $template;

		//echo $template;
		
		// Mail it
		// Additional headers
		$headers .= "To: {$to}" . "\r\n";
		$headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";
		mail($to, $subject, $template, $headers);
		
		// insert property log
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
			 '".$staff_id."',
			 'No Tenant Letter Sent',
			 'No Tenant Details Available on ".date('d-m-Y')."',
			 '".date('Y-m-d H:i:s')."'
			)
		");
		
	}
	
	

	
	
	// update
	mysql_query("
		UPDATE property
		SET `tenant_ltr_sent` = '".date("Y-m-d")."'
		WHERE `property_id` = {$property_id}
	");
	
	// if property vacant
	if( $property_vacant==1 ){
		
		// move to To Be Booked
		mysql_query("
			UPDATE jobs
			SET `status` = 'To Be Booked'
			WHERE `status` = 'Send Letters'
			AND `id` = {$job_id}
		");
		
	}else{
		
		// move to escalate
		mysql_query("
			UPDATE jobs
			SET `status` = 'Escalate'
			WHERE `status` = 'Send Letters'
			AND `id` = {$job_id}
		");
		
		// esclate job reason, verify tenant detail ID
		$verify_tenant_details_id = 1;
		
		// clear any 'Verify Tenant Details' escalate job reason first, to avoid duplicate entry	
		mysql_query("
			DELETE 
			FROM `selected_escalate_job_reasons`
			WHERE `job_id` = {$job_id}
			AND `escalate_job_reasons_id` = {$verify_tenant_details_id}
		");
		
		// insert escalate job reason - Verify Tenant Details
		mysql_query("
			INSERT INTO
			`selected_escalate_job_reasons` (
				`job_id`,
				`escalate_job_reasons_id`,
				`date_created`,
				`deleted`,
				`active`
			)
			VALUES(
				{$job_id},
				{$verify_tenant_details_id},
				'".date('Y-m-d H:i:s')."',
				0,
				1
			)
		");
		
	}
		
	
}

function isDHAagencies($agency_id){
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
	if( in_array($agency_id, $dha_agencies) ){
		return true;
	}else{
		return false;
	}
}


function isDHAagenciesV2($fg_id){
	// Defence Housing
	if( $fg_id == 14 ){
		return true;
	}else{
		return false;
	}
}

function agencyHasMaintenanceProgram($agency_id){
	
	$sql = mysql_query("
		SELECT *
		FROM `agency_maintenance`
		WHERE `agency_id` = {$agency_id}
		AND `maintenance_id` > 0
		AND `status` = 1		
	");
	
	if( mysql_num_rows($sql)>0 ){
		return true;
	}else{
		return false;
	} 
	
}

function getMainRegions($country_id,$state=''){
	
	$state_str = ($state!="")?" AND `region_state` = '{$state}' ":'';
	
	return mysql_query("
		SELECT *
		FROM `regions`
		WHERE `status` = 1
		AND country_id = {$country_id}
		{$state_str}
		ORDER BY `region_name`
	");
}

function getSubRegions($region_id,$country_id){
	return mysql_query("
		SELECT * 
		FROM `postcode_regions` AS pr
		LEFT JOIN `countries` AS c ON pr.`country_id` = c.`country_id`
		WHERE pr.`deleted` = 0 
		AND pr.`country_id` = {$country_id}
		AND pr.`region` = {$region_id}
		ORDER BY pr.`postcode_region_name` ASC
	");
}

// get map job listing with region filter
function getJobsByRegionSort($tech_id,$date,$sub_regions,$country_id='',$agency_id,$distinct,$sort_list){
	
	
	$region_str = getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id,'','','',$agency_id);
	
	$sel_str = "
		j.`id` AS jid, 
		j.`sort_order`, 
		j.`job_type`, 
		j.time_of_day, 
		j.`tech_notes`, 
		j.`status` AS j_status, 
		j.`completed_timestamp`, 
		j.`job_reason_id`, 
		j.`ts_completed`, 
		j.`service` AS j_service, 
		j.`urgent_job`, 
		j.`created`, 
		j.`comments` AS j_comments,
		j.`key_access_required`,

		p.`property_id`, 
		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3, 
		p.`state` AS p_state, 
		p.`postcode` AS p_postcode, 
		p.`key_number`, 
		p.`lat` AS p_lat, 
		p.`lng` AS p_lng,

		a.`agency_id`, 
		a.`agency_name`, 
		a.`address_1` AS a_address_1, 
		a.`address_2` AS a_address_2, 
		a.`address_3` AS a_address_3, 
		a.`state` AS a_state, 
		a.`postcode` AS a_postcode, 
		a.`phone` AS a_phone,
		a.`allow_dk`
	";
	
	// if distinct
	if( $distinct!='' ){
		
		switch($distinct){
			case 'a.agency_id':
				$sel_str = 'DISTINCT a.`agency_id`, a.`agency_name`';
			break;
		}
		
	}
	
	$sort_str = "j.`sort_order` ASC";
	
	if( $sort_list!='' ){
		$sort_str = "{$sort_list}";
	}
	
	
	$str = "
		SELECT 
		{$sel_str}	
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		{$region_str}
		ORDER BY {$sort_str}
	";
	//echo "<hr />";
	
	return mysql_query($str);
}


function getSTRnewlyAddedListing($tech_run_id,$tech_id,$date,$sub_regions,$country_id='',$isAssigned="",$display_only_booked="",$agency_id){
	
	
	$region_str = getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id,$tech_run_id,$isAssigned,$display_only_booked,$agency_id);
	
	
	$str = "
		SELECT 
			j.`id` AS jid, 
			j.`sort_order`, 
			j.`job_type`, 
			j.time_of_day, 
			j.`tech_notes`, 
			j.`status` AS j_status, 
			j.`completed_timestamp`, 
			j.`job_reason_id`, 
			j.`ts_completed`, 
			j.`service` AS j_service, 
			j.`urgent_job`, 
			j.`created`, 
			j.`comments` AS j_comments,
			j.`key_access_required`,

			p.`property_id`, 
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode, 
			p.`key_number`, 
			p.`lat` AS p_lat, 
			p.`lng` AS p_lng,

			a.`agency_id`, 
			a.`agency_name`, 
			a.`address_1` AS a_address_1, 
			a.`address_2` AS a_address_2, 
			a.`address_3` AS a_address_3, 
			a.`state` AS a_state, 
			a.`postcode` AS a_postcode, 
			a.`phone` AS a_phone,
			a.`allow_dk`
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		{$region_str}
		AND j.`id` NOT IN(
			SELECT trr.`row_id`
			FROM  `tech_run_rows` AS trr 
			WHERE  trr.`row_id_type` =  'job_id'
			AND trr.`status` = 1
			AND trr.`tech_run_id` = {$tech_run_id}
		)
		ORDER BY j.`sort_order`
	";
	
	//echo "<hr />";
	return mysql_query($str);
}

// query needed for sub region filter to work
function getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id,$tech_run_id="",$isAssigned="",$display_only_booked="",$agency_id){

	// initiate sats crm class
	$crm = new Sats_Crm_Class;	

	// if electrician?
	$tsql = mysql_query("
		SELECT * 
		FROM  `staff_accounts` 
		WHERE `StaffID` = {$tech_id}
		AND `is_electrician` = 1	
	");
	$isElectrician = ( mysql_num_rows($tsql)>0 )?true:false;
	//echo "is Electrician? ".var_dump($isElectrician);
	
	// standard filter condition
	
	$sql_str = "
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
	";
	

	
	//echo "Tech Run: {$tech_run_id}";
	
	if( $tech_run_id!="" ){
		
		// if run complete
		$rt_sql = mysql_query("
			SELECT * 
			FROM  `tech_run` 
			WHERE  `tech_run_id` = {$tech_run_id}
			AND `run_complete` = 1
		");
		
		$isRunComplete = ( mysql_num_rows($rt_sql)>0 )?true:false;
		
	}else{
		$isRunComplete = false;
	}
	
	//echo "Is run complete: ".var_dump($isRunComplete);
	
	
	// show only booked if run complete
	if( $isRunComplete == true || $display_only_booked==1 ){
		
		// if complete
		$sql_str .= " 
			AND j.`assigned_tech` ={$tech_id}
			AND j.`date` = '{$date}'
			AND (
				j.`status` = 'Booked'
				OR j.`status` = 'Pre Completion'
				OR j.`status` = 'Merged Certificates'
				OR j.`status` = 'Completed'
				OR (
					j.`status` = 'To Be Booked' AND
					j.`door_knock` = 1	
				)
			) 
		";
		
	}else{
		
		// if region filter is present
		if($sub_regions!=""){

								
				
				// enable/disable on hold
				$append_onhold = " OR j.`status` = 'On Hold' ";
				
				
				if($isAssigned==1){
					
					// fetch job via assigned
					$sql_str .= "		
						AND ( 
							j.`status` = 'To Be Booked' 
							OR j.`status` = 'Booked' 
							OR j.`status` = 'DHA' 
							OR j.`status` = 'Escalate'
							{$append_onhold}
							OR j.`status` = 'Allocate'
						) 
						AND j.`assigned_tech` = {$tech_id} 
						AND j.`date` = '{$date}'
					";
					
				}else{									

					// get all postcode that belong to passed multiple sub region
					$sel_query = "pc.`postcode`";                
					$sub_region_params = array(
						'sel_query' => $sel_query,
						'sub_region_id_imp' => $sub_regions,                                                          
						'deleted' => 0,
						'display_query' => 0
					);
					$postcode_sql = $crm->get_postcodes($sub_region_params);					
					
					$postcodes_arr = [];
					while ( $postcode_row = mysql_fetch_array($postcode_sql)) {
						$postcodes_arr[] = $postcode_row['postcode'];
					}

					if( count($postcodes_arr) > 0 ){
						$postcodes_imp = implode(",", $postcodes_arr);
					}    
					
						
					// Agency filter
					$sql_str_filter = '';
					if( $agency_id!='' ){
						$sql_str_filter .= "
							AND a.`agency_id` IN ({$agency_id}) 
						"; 
					}	
						
					$sql_str .= "		
						AND p.`postcode` IN ( {$postcodes_imp} )	
						AND (
							j.`status` = 'To Be Booked' 
							OR j.`status` = 'Booked' 
							OR j.`status` = 'DHA'
							OR j.`status` = 'Escalate'
							{$append_onhold}
							OR j.`status` = 'Allocate'
						)
						AND (
							j.`assigned_tech` = {$tech_id} 
							OR j.`assigned_tech` = 0
							OR j.`assigned_tech` IS NULL
						)
						AND(
							j.`date` = '{$date}'
							OR j.`date` IS NULL
							OR j.`date` = '0000-00-00'
							OR j.`date` = ''
						)
						{$sql_str_filter}
					";
					
				}
				
				
				
			}else{
				
				// if no regions 
				$sql_str .= "	
					AND (
						j.`status` = 'To Be Booked' 
						OR j.`status` = 'Booked' 
						OR j.`status` = 'DHA'
						OR j.`status` = 'Escalate'
						{$append_onhold}
						OR j.`status` = 'Allocate'
					)
					AND j.`assigned_tech` ={$tech_id}
					AND j.`date` = '{$date}'
				";
				
			}
		
	}
	
	return $sql_str;
	
}

function getSatsTechnician(){
	return mysql_query ("
		SELECT sa.StaffID, sa.FirstName, sa.LastName, sa.active, sa.is_electrician
		FROM `staff_accounts` AS sa
		LEFT JOIN `staff_accounts` AS sa ON t.`id` = sa.`TechID`
		LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
		WHERE ca.`country_id` ={$_SESSION['country_default']}
		AND sa.`active` =1
		ORDER BY sa.`FirstName` ASC
	");
}

function getTechRunRows($tech_run_id,$country_id,$params=""){
	
	/*
	if( $params['checkLatLng']==1 ){
		
		// check property address lat/lng
		$filter .= '
			AND p.`lat` IS NULL
			AND p.`lng` IS NULL
		';
		
	}
	*/
	
	if( $params['hide_hidden']==1 ){
		$filter .= " AND trr.`hidden` = 0 ";
	}
	
	if( $params['postcode_regions']!="" ){
		$filter .= " AND p.`postcode` IN ( {$params['postcode_regions']} ) ";
	}
	
	if( $params['custom_select'] != '' ){
		
		$sel_str = "SELECT {$params['custom_select']}";
		
	}else if( $params['distinct']==1 ){
		
		// check property address lat/lng
		switch( $params['distinct_val'] ){
			case 'a.`agency_id`':
				$sel_str = '
					SELECT DISTINCT (a.`agency_id`), a.`agency_name`
				';
			break;
				
		}
		
	}else{
		$sel_str = "SELECT *, j.`id` AS jid, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`postcode` AS p_postcode";
	}
	
	
	// if run complete
	$tr_sql = mysql_query("
		SELECT *
		FROM `tech_run`
		WHERE `tech_run_id` = {$tech_run_id}
	");
	$tr = mysql_fetch_array($tr_sql);
	
	// enable/disable on hold
	$append_onhold = " OR j.`status` = 'On Hold' ";
	
	
	if( $tr['run_complete']==1 || $params['display_only_booked']==1 ){
		
		$filter .= "
			AND(
				j.`status` = 'Booked'
				OR j.`status` = 'Pre Completion'
				OR j.`status` = 'Merged Certificates'
				OR j.`status` = 'Completed'
				OR (
					j.`status` = 'To Be Booked' AND
					j.`door_knock` = 1	
				)
			)
			AND (
				j.`assigned_tech` = {$tr['assigned_tech']} 
				AND j.`date` = '{$tr['date']}'
			)
			OR (
				trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tech_run_id}
			)
			OR (
				trr.`row_id_type` = 'supplier_id' AND tr.`tech_run_id` = {$tech_run_id}
			)
		";
		
	}else{
		
			
		// job listing only, exclude keys and supplier row, used in add keys dropdown
		if( $params['job_rows_only'] == 1 ){

			$append_keys_and_supplier_row = "
			AND `row_id_type` = 'job_id'
			";			
			
		}else{ // default

			$append_keys_and_supplier_row = "
			OR (
				trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tech_run_id}
			)
			OR (
				trr.`row_id_type` = 'supplier_id' AND tr.`tech_run_id` = {$tech_run_id}
			)
			";

		}
		
		$filter .= "
			AND (
				j.`status` = 'To Be Booked'	
				OR j.`status` = 'Booked' 
				OR j.`status` = 'DHA'
				OR j.`status` = 'Escalate'
				{$append_onhold}
				OR j.`status` = 'Allocate'
			)
			AND ( 
				j.`assigned_tech` = {$tr['assigned_tech']} 
				OR j.`assigned_tech` = 0
				OR j.`assigned_tech` IS NULL 
			) 
			AND(
				j.`date` = '{$tr['date']}'
				OR j.`date` IS NULL
				OR j.`date` = '0000-00-00'
				OR j.`date` = ''
			)
			{$append_keys_and_supplier_row}
		";
		
		
	}

	// custom sort 
	$sort_str = null;
	if( $params['custom_sort'] != '' ){
		$sort_str = $params['custom_sort'];
	}else{
		$sort_str = "trr.`sort_order_num` ASC, p.`address_3` ASC, p.`address_2` ASC";
	}
	
	
	// paginate
	if($params['paginate']!=""){
		if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
			$pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
		}
	}
	
	//$params = "";
	$sql_str = "
		{$sel_str}
		FROM `tech_run_rows` AS trr
		LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`
		LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
		WHERE tr.`tech_run_id` = {$tech_run_id}
		AND tr.`country_id` = {$country_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
		{$filter}
		ORDER BY {$sort_str}
		{$pag_str}
	";
	
	//echo "<hr />";
	return mysql_query($sql_str);
	
}

function getJobRowData($job_id,$country_id){
	return mysql_query("
		SELECT 
			j.`id` AS jid, 
			j.`sort_order`, 
			j.`job_type`, 
			j.time_of_day, 
			j.`tech_notes`, 
			j.`status` AS j_status, 
			j.`completed_timestamp`, 
			j.`job_reason_id`, 
			j.`ts_completed`, 
			j.`service` AS j_service, 
			j.`urgent_job`, 
			j.`created`, 
			j.`comments` AS j_comments,
			j.`key_access_required`,
			j.`date` AS jdate,
			j.`door_knock`,
			j.`start_date`,
			j.`due_date`,
			j.`unavailable`,
			j.`unavailable_date`,
			j.`job_entry_notice`,
			j.`preferred_time`,
			j.`call_before`,
			j.`call_before_txt`,
			j.`booked_with`,
			j.`survey_ladder`,
			j.`job_priority`,
			j.`is_eo`,
			j.`property_vacant`,

			p.`property_id`, 
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
			p.`postcode` AS p_postcode, 
			p.`key_number`,
			p.`qld_new_leg_alarm_num`,
			p.`lat` AS p_lat, 
			p.`lng` AS p_lng,
			p.`tenant_firstname1`,
			p.`tenant_lastname1`,
			p.`tenant_firstname2`,
			p.`tenant_lastname2`,
			p.`tenant_email1`,
			p.`tenant_email2`,
			p.`tenant_mob1`,
			p.`tenant_mob2`,
			p.`no_keys`,
			p.`comments` AS p_comments,
			p.`no_en`,
			p.`no_dk`,
			p.`requires_ppe`,
			p.`service_garage`,
			p.`holiday_rental`,
			DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline,

			a.`agency_id`, 
			a.`agency_name`, 
			a.`address_1` AS a_address_1, 
			a.`address_2` AS a_address_2, 
			a.`address_3` AS a_address_3, 
			a.`state` AS a_state, 
			a.`postcode` AS a_postcode, 
			a.`phone` AS a_phone,
			a.`allow_dk`,
			a.`key_allowed`,
			a.`agency_hours`,
			a.`electrician_only`,
			a.`send_entry_notice`,
			a.`allow_en`,
			aght.`priority`
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		LEFT JOIN `agency_priority` AS aght ON a.`agency_id` = aght.`agency_id`
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND j.`id` = {$job_id}
	");
}

function getTechRunKeys($tech_run_keys_id,$country_id){
	
	$tech_id = ($tech_id!="")?mysql_real_escape_string($tech_id):'';
	$date = ($date!="")?mysql_real_escape_string($date):'';
	$country_id = ($country_id!="")?mysql_real_escape_string($country_id):$_SESSION['country_default'];
	
	return mysql_query("
		SELECT 
			thk.`tech_run_keys_id`, thk.`action`, thk.`number_of_keys`, thk.`agency_staff`, thk.`completed`, thk.`completed_date`, thk.`sort_order`,
			a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`,			
			agen_add.`id` AS agen_add_id,
			agen_add.`address_1` AS agen_add_street_num, 
			agen_add.`address_2` AS agen_add_street_name, 
			agen_add.`address_3` AS agen_add_suburb, 
			agen_add.`state` AS agen_add_state, 
			agen_add.`postcode` AS agen_add_postcode,
			agen_add.`lat` AS agen_add_lat,
			agen_add.`lng` AS agen_add_lng			
		FROM `tech_run_keys` AS thk
		LEFT JOIN `agency` AS a ON thk.`agency_id` = a.`agency_id`
		LEFT JOIN `agency_addresses` AS agen_add ON thk.`agency_addresses_id` = agen_add.`id`
		WHERE thk.`tech_run_keys_id` = {$tech_run_keys_id}
		AND a.`country_id` = {$country_id}
		AND ( 
			thk.`deleted` = 0 
			OR thk.`deleted` IS NULL 
		)
	");
	
}


function clearTechRunRows($tech_run_id,$tech_id){
	$sql = "
		DELETE trr
		FROM `tech_run_rows` AS trr
		LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
		WHERE trr.`tech_run_id` = {$tech_run_id}
		AND trr.`row_id_type` =  'job_id'
		AND j.`status` NOT IN('Booked','Pre Completion','Merged Certificates','Completed')
		AND trr.`highlight_color` IS NULL
	";
	//echo "<hr />";
	mysql_query($sql);
}


function getTechRunCurrentListings($tech_run_id,$row_type=''){
	
	if( $row_type!="" ){
		$str .= " AND `row_id_type` = '{$row_type}' ";
	}
	
	return mysql_query("
		SELECT `row_id` AS jid
		FROM  `tech_run_rows` 
		WHERE `status` = 1
		AND `tech_run_id` = {$tech_run_id}
		{$str}
	");
}


function getTechRunNewListings($tech_run_id,$tech_id,$date,$country_id){
	
	// if run complete
	$tr_sql = mysql_query("
		SELECT `run_complete`
		FROM `tech_run`
		WHERE `tech_run_id` = {$tech_run_id}
		AND `run_complete` = 1
	");
	
	if( mysql_num_rows($tr_sql)>0 ){
		
		$filter .= "
			AND (
				j.`status` = 'Booked'
				OR j.`status` = 'Pre Completion'
				OR j.`status` = 'Merged Certificates'
				OR j.`status` = 'Completed'
				OR (
					j.`status` = 'To Be Booked' AND
					j.`door_knock` = 1	
				)
			) 
		";
		
	}else{
		$filter .= " AND (
			j.`status` = 'To Be Booked'
			OR j.`status` = 'DHA'
			OR j.`status` = 'Booked' 
		) ";
	}
	
	$sql = "
		SELECT *, j.`id` AS jid
		FROM jobs AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND j.`assigned_tech` ={$tech_id}
		AND j.`date` = '{$date}'
		{$filter}
		AND j.`id` NOT IN(
			SELECT trr.`row_id`
			FROM  `tech_run_rows` AS trr 
			WHERE  trr.`row_id_type` =  'job_id'
			AND trr.`status` = 1
			AND trr.`tech_run_id` = {$tech_run_id}
		)
	";
	return mysql_query($sql);
}

function appendTechRunNewListings($tech_run_id,$tech_id,$date,$sub_regions,$country_id,$isAssigned="",$display_only_booked=""){
	
	$tr_sql = mysql_query("
		SELECT `agency_filter`
		FROM `tech_run`
		WHERE `tech_run_id` = {$tech_run_id}
	");
	$tr = mysql_fetch_array($tr_sql);

	$j_sql = getSTRnewlyAddedListing($tech_run_id,$tech_id,$date,$sub_regions,$country_id,$isAssigned,$display_only_booked,$tr['agency_filter']);
	
	$num_rows = mysql_num_rows($j_sql);
	
	if( $num_rows>0 ){
		
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
					{$tech_run_id},
					'job_id',
					{$j['jid']},
					999999,
					0,
					'".date('Y-m-d H:i:s')."',
					1
				)
			";
			mysql_query($str3);
			$num_jobs++;
		}
		
	}
	
	// delete duplicates
	deleteTechRunDuplicates($tech_run_id);
	
	return $num_rows;

	
}

function techRunDragAndDropSort($tr_id,$trw_ids){
	
	
	$i = 2;
	foreach($trw_ids as $trw_id){
		
		if($trw_id!=""){
			
			$sql = "
				UPDATE `tech_run_rows`
				SET 
					`sort_order_num` = {$i},
					`dnd_sorted` = 1
				WHERE `tech_run_rows_id` = {$trw_id}
				AND `tech_run_id` = {$tr_id}
			";

			mysql_query($sql);

			$i++;

			
		}
		
	}
	
}

function techRunUpdateStartEndPoint($tr_id,$start,$end){
	
	if( $start!="" || $end!="" ){
			
				// check lat/lng
				// start point
				if($start!=""){
					
					// start
					// get accomodation address
					$a_sql = mysql_query("
						SELECT *
						FROM `accomodation`
						WHERE `accomodation_id` = {$start}
						AND `lat` IS NULL
						AND `lng` IS NULL
					");

					if(mysql_num_rows($a_sql)>0){
						$a = mysql_fetch_array($a_sql);
						// get geocode
						$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
						// update agency lat/lng
						mysql_query("
							UPDATE `accomodation`
							SET 
								`lat` = '{$coor['lat']}',
								`lng` = '{$coor['lng']}'
							WHERE `accomodation_id` = {$start}
						");
					}
					
				}

				// end point
				if($end!=""){
					
					// end
					// get accomodation address
					$a_sql = mysql_query("
						SELECT *
						FROM `accomodation`
						WHERE `accomodation_id` = {$end}
						AND `lat` IS NULL
						AND `lng` IS NULL
					");

					if(mysql_num_rows($a_sql)>0){
						$a = mysql_fetch_array($a_sql);
						// get geocode
						$coor = getGoogleMapCoordinates("{$a['address']}, Australia");
						// update agency lat/lng
						mysql_query("
							UPDATE `accomodation`
							SET 
								`lat` = '{$coor['lat']}',
								`lng` = '{$coor['lng']}'
							WHERE `accomodation_id` = {$end}
						");
					}

					
				}

				// update start and end point
				$sql = "
				UPDATE `tech_run`
				SET `start` = '{$start}',
					`end` = '{$end}'
				WHERE `tech_run_id` = {$tr_id}
				";
				mysql_query($sql);
				
			
			
				
			
			
		}
	
}

function techRunAddAgencyKeys($params){
	
	// data
	$tech_run_id = $params['tech_run_id'];
	$keys_agency = $params['keys_agency'];
	$tech_id = $params['tech_id'];
	$date = $params['date'];
	$country_id = $params['country_id'];
	$agency_addresses_id = $params['agency_addresses_id'];
	
	// get tech run total rows
	$trr_sql = getTechRunRows($tech_run_id,$country_id);
	$count = mysql_num_rows($trr_sql);

	$i = ($count)+2;
		
	$keys_array = array(
		'Pick Up',
		'Drop Off'
	);

	//$k = 2;
	foreach($keys_array as $val){
		
		
		// check agency lat/lng
		$a_sql = mysql_query("
			SELECT *
			FROM `agency`
			WHERE `agency_id` = {$keys_agency}
			AND `lat` IS NULL
			AND `lng` IS NULL
		");
		if(mysql_num_rows($a_sql)>0){
			$a = mysql_fetch_array($a_sql);
			// get geocode
			$coor = getGoogleMapCoordinates("{$a['address_1']} {$a['address_2']} {$a['address_3']} {$a['state']} {$a['postcode']}");
			// update agency lat/lng
			mysql_query("
				UPDATE `agency`
				SET 
					`lat` = '{$coor['lat']}',
					`lng` = '{$coor['lng']}'
				WHERE `agency_id` = {$keys_agency}
			");
		}
		
		// insert keys 
		$k_sql = mysql_query("
			INSERT INTO
			`tech_run_keys`(
				`assigned_tech`,
				`date`,
				`action`,
				`agency_id`,
				`sort_order`,
				`agency_addresses_id`
			)
			VALUES(
				{$tech_id},
				'{$date}',
				'{$val}',
				'{$keys_agency}',
				{$i},
				'{$agency_addresses_id}'
			)
		");	
		$key_id = mysql_insert_id();
		
		//  insert tech run rows
		$tr_sql = "
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
				{$tech_run_id},
				'keys_id',
				{$key_id},
				{$i},
				'".date('Y-m-d H:i:s')."',
				1
			)
		";
		mysql_query($tr_sql);
		

		$i++;
		
	}
	
}

function TechRunSortBySuburb($tech_run_id,$country_id){
	
	$sql_str = "
		SELECT * , j.`id` AS jid
		FROM  `tech_run_rows` AS trr
		LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
		LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE tr.`tech_run_id` = {$tech_run_id}
		AND tr.`country_id` = {$country_id}
		AND trr.`row_id_type` =  'job_id'
		ORDER BY p.`address_3`
	";
	return mysql_query($sql_str);
	
}

function TechRunSortByStreet($tech_run_id,$country_id){
	
	$sql_str = "
		SELECT * , j.`id` AS jid
		FROM  `tech_run_rows` AS trr
		LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
		LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE tr.`tech_run_id` = {$tech_run_id}
		AND tr.`country_id` = {$country_id}
		AND trr.`row_id_type` =  'job_id'
		ORDER BY p.`address_2`
	";
	return mysql_query($sql_str);
	
}

function TechRunSortByColor($tech_run_id,$country_id){
	
	$sql_str = "
		SELECT *
		FROM  `tech_run_rows` AS trr
		LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
		LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
		WHERE tr.`tech_run_id` = {$tech_run_id}
		AND tr.`country_id` = {$country_id}
		AND trr.`row_id_type` =  'job_id'
		ORDER BY CASE WHEN trr.`highlight_color` IS NULL THEN 1 ELSE 0 END, trr.`highlight_color` ASC
	";
	return mysql_query($sql_str);
	
}

function getGoogleMapDistance($orig,$dest){
	
	// init curl object        
	$ch = curl_init();

	// api key
	$API_key = GOOGLE_DEV_API;

	$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".rawurlencode($orig)."&destinations=".rawurlencode($dest)."&key={$API_key}";

	// define options
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);

	// apply those options
	curl_setopt_array($ch, $optArray);

	// execute request and get response
	$result = curl_exec($ch);


	$result_json = json_decode($result);


	curl_close($ch);

	return $result_json;

}


function getTechRonJobRows($tech_run_id,$country_id){
	return mysql_query("
		SELECT 
			*, 
			j.`id` AS jid,
			j.`status` AS jstatus,
			p.`address_1` AS paddress1,
			p.`address_2` AS paddress2,
			p.`address_3` AS paddress3,
			a.`address_1` AS aaddress1,
			a.`address_2` AS aaddress2,
			a.`address_3` AS aaddress3,
			a.`postcode` AS apostcode,
			a.`phone` AS aphone
		FROM  `tech_run_rows` AS trr
		LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
		LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE tr.`tech_run_id` = {$tech_run_id}
		AND tr.`country_id` = {$country_id}
		AND trr.`row_id_type` =  'job_id'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		ORDER BY trr.`sort_order_num`
	");
}


function getDynamicStateViaCountry($country_id){
	// NZ
	if($country_id==2){ 
		$state_str = 'Region';
	}else{
		$state_str = 'State';
	}
	return $state_str;
}

function getDynamicRegionViaCountry($country_id){
	// NZ
	if($country_id==2){ 
		$region_str = 'District';
	}else{
		$region_str = 'Region';
	}
	return $region_str;
}

function getServiceColor($ajt_id){
	switch($ajt_id){
		case 2:
			$serv_color = "#b4151b";
		break;
		case 5:
			$serv_color = "orange";
		break;
		case 6:
			$serv_color = "green";
		break;
		case 7:
			$serv_color = "blue";
		break;
		default:
			$serv_color = "#9B30FF";
	}
	
	return $serv_color;
}

// call google map URL shortener API
function convertToGoogleUrlShortener($url){
	
	// init curl object        
	$ch = curl_init();

	// API key
	// LIVE - SATS gmail
	//$API_key = GOOGLE_DEV_API;
	// TEST - my personal gmail
	//$API_key = 'AIzaSyAlg-wLGSmPTbQ1Fgi5UXOPOhdLLtcbkdY';
	
	// old api key, url shortener for new API created after May 30, 2018 doesnt work
	// google api annoucement: Starting May 30, 2018, only projects that have accessed URL Shortener APIs before today can create short links.
	if( $_SESSION['country_default'] == 1 ){ // AU
		$API_key = 'AIzaSyCBTFejS6It4Z4hIWzNNwlwN1mBzR_1MuU';
	}else if( $_SESSION['country_default'] == 2 ){ // NZ
		$API_key = 'AIzaSyBqYJ80rXXfOv5qrbQxXwIpU4H_WHctHHM';
	}else{
		$API_key = 'AIzaSyCBTFejS6It4Z4hIWzNNwlwN1mBzR_1MuU'; // IF NONE, use AU
	}
	

	// google url shortener API to call
	$c_url = "https://www.googleapis.com/urlshortener/v1/url?key={$API_key}";

	// POST data
	$data = array("longUrl" => $url);                                                                   
	$data_string = json_encode($data);   

	// define options
	$optArray = array(
		CURLOPT_URL => $c_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data_string,
		CURLOPT_HTTPHEADER => array(                                                                          
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($data_string)
		)   
	);

	// apply those options
	curl_setopt_array($ch, $optArray);

	// execute request and get response
	$result = curl_exec($ch);

	$result_json = json_decode($result);

	$short_url = $result_json->id;

	curl_close($ch);
	
	return $short_url;
	
}

// track agency login time
function agencyTracking_TrackLoggedInDateTime($session_id,$agency_id){
	$today = date('Y-m-d H:i:s');
	mysql_query("
		INSERT INTO 
		`agency_tracking`(
			`session_id`,
			`agency_id`,
			`logged_in_datetime`,
			`date_created`,
			`active`,
			`deleted`
		)
		VALUES(
			'{$session_id}',
			{$agency_id},
			'{$today}',
			'{$today}',
			1,
			0
		)
	");
	return mysql_insert_id();
}

function agencyTracking_TrackLoggedOutDateTime($session_id,$agency_tracking_id){
	$today = date('Y-m-d H:i:s');
	$sql_str = "
		UPDATE `agency_tracking`
		SET
			`logged_out_datetime` = '{$today}'
		WHERE `session_id` = '{$session_id}'
		AND `agency_tracking_id` = {$agency_tracking_id}
	";
	mysql_query($sql_str);
}

function getTechRunDuplicates($tech_run_id){
	return mysql_query("
		SELECT trr.`tech_run_rows_id` 
		FROM  `tech_run_rows` AS trr
		WHERE trr.`row_id_type` =  'job_id'
		AND trr.`tech_run_id` ={$tech_run_id}
		GROUP BY trr.`row_id` 
		HAVING COUNT( trr.`row_id` ) >1
	");
}

function deleteTechRunRowDuplicates($trr_id){
	mysql_query("
		DELETE 
		FROM `tech_run_rows`
		WHERE `tech_run_rows_id` = {$trr_id}
	");
}

function deleteTechRunDuplicates($tech_run_id){
	
	$dup_sql = getTechRunDuplicates($tech_run_id);
	if( mysql_num_rows($dup_sql)>0 ){
		while( $dup = mysql_fetch_array($dup_sql) ){
			deleteTechRunRowDuplicates($dup['tech_run_rows_id']);
		}
	}
	
}

function jGetPostcodeViaRegion($region){
	
	$postcodes_imp = null;

	// get all postcode that belong to a region
	$sel_query = "pc.`postcode`";                
	$postcode_params = array(
		'sel_query' => $sel_query,
		'region_id' => $region,                                                          
		'deleted' => 0,
		'display_query' => 0
	);
	$postcode_sql = Sats_Crm_Class::get_postcodes($postcode_params);					

	$postcodes_arr = [];
	while ( $postcode_row = mysql_fetch_array($postcode_sql)) {
		$postcodes_arr[] = $postcode_row['postcode'];
	}

	if( count($postcodes_arr) > 0 ){
		$postcodes_imp = implode(",", $postcodes_arr);
	}

	return $postcodes_imp;
	
}

function getPropertiesFilterRegionCount($country_id,$postcode){

$str = "
	SELECT count(p.`property_id`) AS jcount
	FROM `property` AS p
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE p.`deleted` =0
	AND a.`status` = 'active'
	AND a.`country_id` = {$country_id}	
	AND p.`postcode` IN ( {$postcode} )		
";
$sql = mysql_query($str);
$row = mysql_fetch_array($sql);
return $row['jcount'];

}

function getAgencyFilterRegionCount($country_id,$postcode,$agency_status){

$agency_status_str = '';
if( $agency_status != '' ){
	if( $agency_status == 'all' ){
		$agency_status_str = '';
	}else{
		$agency_status_str = " AND `status` = '{$agency_status}' ";
	}	
}else{
	$agency_status_str = " AND `status` = 'active' ";
}	


$str = "
	SELECT count(`agency_id`) AS jcount
	FROM `agency` 
	WHERE `country_id` = {$country_id}	
	{$agency_status_str}
	AND `postcode` IN ( {$postcode} )		
";
$sql = mysql_query($str);
$row = mysql_fetch_array($sql);
return $row['jcount'];

}


function techRunAddSuppliers($params){
	
	// data
	$tech_run_id = $params['tech_run_id'];
	$supplier = $params['supplier'];
	$country_id = $params['country_id'];
	
	// get tech run total rows
	$trr_sql = getTechRunRows($tech_run_id,$country_id);
	$count = mysql_num_rows($trr_sql);

	$i = ($count)+2;
		
		
	// check supplier lat/lng
	$sup_str = "
		SELECT *
		FROM `suppliers`
		WHERE `suppliers_id` = {$supplier}
		AND `lat` IS NULL
		AND `lng` IS NULL
		AND `address` != ''
	";
	$sup_sql = mysql_query($sup_str);
	if(mysql_num_rows($sup_sql)>0){
		
		$sup = mysql_fetch_array($sup_sql);
		// get geocode
		$coor = getGoogleMapCoordinates("{$sup['address']}");
		// update supplier lat/lng
		$update_str = "
			UPDATE `suppliers`
			SET 
				`lat` = '{$coor['lat']}',
				`lng` = '{$coor['lng']}'
			WHERE `suppliers_id` = {$supplier}
		";
		mysql_query($update_str);
	}
	
	
	// insert supplier 
	$k_sql = mysql_query("
		INSERT INTO
		`tech_run_suppliers`(
			`suppliers_id`,
			`created_date`,
			`active`,
			`deleted`
		)
		VALUES(
			{$supplier},
			'".date('Y-m-d H:i:s')."',
			1,
			0
		)
	");	
	$tr_sup_id = mysql_insert_id();
	
	//  insert tech run rows
	$tr_sql = "
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
			{$tech_run_id},
			'supplier_id',
			{$tr_sup_id},
			{$i},
			'".date('Y-m-d H:i:s')."',
			1
		)
	";
	mysql_query($tr_sql);
	

	$i++;
	
	
}


function getTechRunSuppliers($tech_run_suppliers_id){
	
	return mysql_query("
		SELECT 
			trs.`tech_run_suppliers_id`, sup.`suppliers_id`, sup.`company_name`, sup.`address` AS sup_address, sup.`phone`, sup.`lat`, sup.`lng`, sup.`on_map`			
		FROM `tech_run_suppliers` AS trs
		LEFT JOIN `suppliers` AS sup ON trs.`suppliers_id` = sup.`suppliers_id`
		WHERE trs.`tech_run_suppliers_id` = {$tech_run_suppliers_id}
		AND ( 
			trs.`deleted` = 0 
			OR trs.`deleted` IS NULL 
		)
	");
	
}


function assignTechRunPinColors($trr_id_arr,$trr_hl_color,$tr_id){
	
	$str = '';
	
	$trr_hl_color2 = ($trr_hl_color!=-1)?$trr_hl_color:'NULL';
	
	foreach( $trr_id_arr as $trr_id ){
		
		$sql_str = "
			UPDATE `tech_run_rows`
			SET `highlight_color` = {$trr_hl_color2}
			WHERE `tech_run_id` = {$tr_id}
			AND `tech_run_rows_id` = ".mysql_real_escape_string($trr_id)."
		";
	
		// update
		mysql_query($sql_str);
		
		//$str .= "{$sql_str}<br />";
		
	}
	
	//return $str;
	
}



// compute check digit
function getCheckDigit($number){
	
	$sumTable = array(array(0,1,2,3,4,5,6,7,8,9),array(0,2,4,6,8,1,3,5,7,9));
	$length = strlen($number);
			$sum = 0;
			$flip = 1;
			// Sum digits (last one is check digit, which is not in parameter)
			for($i=$length-1;$i>=0;--$i) $sum += $sumTable[$flip++ & 0x1][$number[$i]];
			// Multiply by 9
			$sum *= 9;

	return (int)substr($sum,-1,1);
	
}

// get Service with interconnected smoke alarms
function getICService(){
	
	$sql = mysql_query("
	SELECT `id`
	FROM `alarm_job_type`
	WHERE `active` = 1
	AND `is_ic` = 1
	");

	$ic_serv_arr = [];
	while( $row = mysql_fetch_object($sql) ){
		$ic_serv_arr[] = $row->id;
	}

	return $ic_serv_arr;

}

// get service full name
function getServiceFullName($ajt_id){
	
	$ajt_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$ajt_id}
	");
	$ajt = mysql_fetch_array($ajt_sql);
	
	switch($ajt_id){
		case 6:
			$serv = 'Window Coverings';
		break;
		case 9:
			$serv = 'Smoke Alarms, Safety Switch & Window Coverings';
		break;
		case 12:
			$serv = 'Smoke Alarms';
		break;
		case 13:
			$serv = 'Smoke Alarm & Safety Switch';
		break;
		case 14:
			$serv = 'Smoke Alarms, Safety Switch & Window Coverings';
		break;
		default:
			$serv = $ajt['type'];
	}
	
	return $serv;
	
}


function getUpdatedRegion($postcode,$agency_id){
	
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
		
		$sql = "
			UPDATE `agency`
			SET `postcode_region_id` = {$pcr_id}
			WHERE `agency_id` = {$agency_id}
		";
		mysql_query($sql);
	
}

function autoUpdateAgencyRegion($agency_id){
	
	$a_sql = mysql_query("
		SELECT `postcode`
		FROM `agency` 
		WHERE `agency_id` = {$agency_id}
	");
	$a = mysql_fetch_array($a_sql);
	
	// get sub region via postcode
	$sel_query = "sr.`sub_region_id`";                
	$postcode_params = array(
		'sel_query' => $sel_query,
		'postcode' => $a['postcode'],                                                          
		'deleted' => 0,
		'display_query' => 0
	);
	$postcode_sql = Sats_Crm_Class::get_postcodes($postcode_params);					
	$postcode_row = mysql_fetch_array($postcode_sql);		
	$sub_region_id = $postcode_row['sub_region_id'];

	
	$sql = "
		UPDATE `agency`
		SET `postcode_region_id` = {$sub_region_id}
		WHERE `agency_id` = {$agency_id}
	";
	mysql_query($sql);
	
}


function deleteDir($dirPath){
	
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
	
}


function displayGreenPhone2($jid,$jstatus){
	// phone call
	$chk_logs_str = "
		SELECT *
		FROM job_log j 
		LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
		WHERE j.`job_id` = {$jid}
		AND j.`deleted` = 0 
		AND j.`eventdate` = '".date('Y-m-d')."'
		AND j.`contact_type` = 'Phone Call'
		ORDER BY j.`log_id` DESC 
		LIMIT 1
	";
	$chk_logs_sql = mysql_query($chk_logs_str);
	$chk_log = mysql_fetch_array($chk_logs_sql);
	
	$current_time = date("Y-m-d H:i:s");
	$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
	$last4hours = date("Y-m-d H:i",strtotime("-3 hours"));
	//echo "Current time: {$current_time }<br />Log Time: {$job_log_time}<br /> last 4 hours: ".$last4hours;
	
	if( $jstatus=='To Be Booked' && mysql_num_rows($chk_logs_sql)>0 && ( $job_log_time >= $last4hours && $job_log_time <= $current_time ) ){
		//echo '<img src="/images/green_phone.png" style="cursor: pointer; margin-right: 10px;" title="Phone Call" />';
		return true;
	}else{
		return false;
	}
	
	
}


function dynamicDearEmailFormat($tenants_names_arr){

	$tenants_str = '';
	$num_tenants = count($tenants_names_arr);
	
	for( $z=0; $z<$num_tenants; $z++ ){
		if($z==0){
			$tenants_txt_sep = "";
		}else if($z==($num_tenants-1)){
			$tenants_txt_sep = " and ";
		}else{
			$tenants_txt_sep = ", ";
		}
		$tenants_str .= "{$tenants_txt_sep}{$tenants_names_arr[$z]}";
	}
	
	return $tenants_str;

}

function getDynamicDomainUrl($current_domain,$current_url){
	
	// get correct crm domain for account manager image
	if( $current_domain == 'sats.com.au' ){
		
		if( strpos($current_url,"agencydev")===false ){ //live
			$dom = "//crm.sats.com.au/";
		}else{	// dev
			$dom = "//crmdev.sats.com.au/";
		}	
		
	}else if( $current_domain == 'sats.co.nz' ){

		if( strpos($current_url,"agencydev")===false ){ //live
			$dom = "//crm.sats.co.nz/";
		}else{	// dev
			$dom = "//crmdev.sats.co.nz/";
		}
	
	}
	
	return $dom;

}


function getCurrentMaxTenants(){
	$num_tenants = 4;
	return $num_tenants;
}

// get the grand total of job price, new alarms and subcharge
function getJobAmountGrandTotal($job_id,$country_id){
	
	$grand_total = 0;
	
	$sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`id` = {$job_id}
		AND a.`country_id` = {$country_id}
	");

	$row = mysql_fetch_array($sql);

	// get amount
	$grand_total = $row['job_price'];

	// get alarms
	$a_sql = mysql_query("
		SELECT *
		FROM `alarm`
		WHERE `job_id`  = {$job_id}	
		AND `new` = 1
		AND `ts_discarded` = 0
	");
	while($a = mysql_fetch_array($a_sql))
	{		
		$grand_total += $a['alarm_price'];
	}


	// surcharge
	$sc_sql = mysql_query("
		SELECT *, m.`name` AS m_name 
		FROM `agency_maintenance` AS am
		LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
		WHERE am.`agency_id` = {$row['agency_id']}
		AND am.`maintenance_id` > 0
	");
	$sc = mysql_fetch_array($sc_sql);
	if( $grand_total!=0 && $sc['surcharge']==1 ){
		
		$grand_total += $sc['price'];
		
	}
	
	return $grand_total;
	
}

?>