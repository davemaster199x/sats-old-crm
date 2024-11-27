<?php
$pageLoadStart = microtime(TRUE);

$title = "View Property Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
include('inc/ourtradie_api_class.php'); 

$crm = new Sats_Crm_Class;
$agency_api = new Agency_api;

$encrypt_decrypt = new Openssl_Encrypt_Decrypt();

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$id = mysql_real_escape_string($_GET['id']);
$property_id = mysql_real_escape_string($_GET['id']);

# Get Different Tech Sheet Job Types
$tech_sheet_job_types = getTechSheetJobTypes();

# Get types currently assigned to property
$job_tech_sheet_job_types = getTechSheetAlarmTypesJob($id, true);

$sql = "SELECT * FROM job_type";
$jobtypes = mysqlMultiRows($sql);

// get lockbox data
$lockbox_sql = mysql_query("
SELECT `code`
FROM `property_lockbox`
WHERE `property_id` = {$property_id}
");
$lockbox_sql_row = mysql_fetch_object($lockbox_sql);

function get_sel_prop_serv($property_id,$alarm_job_type_id,$service){
	$ps_sql = mysql_query("
		SELECT *
		FROM `property_propertytype`
		WHERE `alarm_job_type_id` = {$alarm_job_type_id}
		AND `property_id` = {$property_id}
		AND `service` = {$service}
	");
	return mysql_num_rows($ps_sql);
}


// property logs
if($_POST){

	if(isset($_POST['add_event'])){

		// format date
		$event_date = date('Y-m-d',strtotime(str_replace("/","-",$_POST['eventdate'])));
		$event_time = date('H:i:s');
		$event_comp = "{$event_date} {$event_time}";
		$important = ($_POST['important']!="")?1:0;

		$insertEventquery = "INSERT INTO property_event_log(property_id, staff_id, event_type, event_details, log_date, `important`)
							VALUES($id, $staff_id, '".$_POST['contact_type']."', '".mysql_real_escape_string($_POST['comments'])."', '".$event_comp."', '".$important."' )";

		mysql_query($insertEventquery, $connection);
	}

	if(isset($_POST['del_pelid']) && $_POST['del_pelid'] != ""){
		$query = "DELETE FROM property_event_log WHERE id = ". $_POST['del_pelid'];
		mysql_query($query, $connection);
	}

}



function getPropertyPM($pm_id){


	//$new_pm = 0;
	$new_pm = NEW_PM;

	if( $new_pm == 1 ){ // NEW PM

		return mysql_query("
			SELECT *
			FROM `agency_user_accounts`
			WHERE `agency_user_account_id` = {$pm_id} ORDER BY fname
		");

	}else{ // OLD PM

		return mysql_query("
			SELECT *
			FROM `property_managers`
			WHERE `property_managers_id` = {$pm_id} ORDER BY name
		");

	}


}

//get all pm by agency (gherx)
$agency_id_query = mysql_query("
SELECT agency_id, prop_upgraded_to_ic_sa
FROM `property`
WHERE `property_id` = {$property_id}
");
$agency_id_arr = mysql_fetch_array($agency_id_query);
$agency_id_row = $agency_id_arr['agency_id'];
$ic_upgrade = $agency_id_arr['prop_upgraded_to_ic_sa'];

function get_all_property_pm($agency_id){
	return mysql_query("
		SELECT agency_user_account_id,fname,lname,email
		FROM `agency_user_accounts`
		WHERE `active` = 1
		AND `agency_id` = {$agency_id} ORDER BY fname
	");
}
//get all pm (gherx) end

function checkSmsforToday($job_id){
    $sql_str = "
        SELECT *
        FROM  `job_log`
        WHERE  `contact_type` =  'SMS sent'
        AND  `job_id` ={$job_id}
        AND  `eventdate` =  '".date('Y-m-d')."'
    ";
    return mysql_query($sql_str);
}

# Upload property file
function uploadfile2($files_arr, $property_id)
{
	$success = false;
	$error = null;

	if( $property_id > 0 ){

		#security measure, don't allow ..
		if(stristr($files_arr['fileupload']['name'], "..")){
			$error = "The file name contains ' .. ', and couldn't be uploaded.";	
			$success = false;		
		}else{

			# if subdir doesn't exist then create it first
			if(!is_dir(UPLOAD_PATH_BASE . $property_id))
			{
				@mkdir(UPLOAD_PATH_BASE . $property_id, 0777);
			}

			$filename = preg_replace('/#+/', 'num', $files_arr['fileupload']['name']);
			$filename2 = preg_replace('/\s+/', '_', $filename);
			$filename3 = rand().date('YmdHis').$filename2;

			echo "<div class='upload_debug' style='display:none;'>
			from: {$files_arr['fileupload']['tmp_name']}<br />
			to: ".UPLOAD_PATH_BASE . $property_id . "/" .$filename3."
			</div>";

			if(move_uploaded_file($files_arr['fileupload']['tmp_name'], UPLOAD_PATH_BASE . $property_id . "/" .$filename3))
			{

				// appended - insert log
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
						'File Upload',
						'".mysql_real_escape_string($filename3)." Uploaded',
						'".date('Y-m-d H:i:s')."',
						1
					)"
				);

				$success = true;
			}
			else {
				$success = false;
				$error = "Technical Problem. Please try again";
			}

		}

		$ret_arr = array(
			'success' => $success,
			'error' => $error
		);

		return $ret_arr;

	}	
}

/**
 ON LOAD RUN QUERY (gherx)
 get/check recent job != onceoff/!=upgront
 update property retest_date
 */

//First query without assigned_tech and status filter
$sql_recent_job_no_tech_filter = mysql_query("
SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE j.`property_id` = {$property_id}
AND j.`del_job` = 0
AND a.`country_id` = {$_SESSION['country_default']}
ORDER BY j.`date` DESC
LIMIT 1
");
$sql_recent_job_no_tech_filter_fet_arr = mysql_fetch_array($sql_recent_job_no_tech_filter);

if($sql_recent_job_no_tech_filter_fet_arr['jdate'] > '2015-12-31'){
	//$assigned_tech_filter = "AND j.`assigned_tech` IS NOT NULL AND j.`assigned_tech` !=1 AND j.`assigned_tech` !=2"; // Disabled > Reason: We are assuming that SOMEONE has attended that property at that time, so we only need to attend a year after that point
	$assigned_tech_filter = "AND j.`assigned_tech` IS NOT NULL AND j.`assigned_tech` !=2"; // New > removed Other Supplier filter > Reason: We are assuming that SOMEONE has attended that property at that time, so we only need to attend a year after that point
}

//Second query with assigned_tech filter option based on job date condition
$sql_recent_job = mysql_query("
	SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE j.`property_id` = {$property_id}
	AND j.`status` = 'Completed'
	AND j.`del_job` = 0
	AND a.`country_id` = {$_SESSION['country_default']}
	{$assigned_tech_filter}
	ORDER BY j.`date` DESC
	LIMIT 1
");

$recent_jobdate_fetch_arr = mysql_fetch_array($sql_recent_job);
$recent_jobdate = $recent_jobdate_fetch_arr['jdate'];
$recent_job_type = $recent_jobdate_fetch_arr['j_type'];

if( mysql_num_rows($sql_recent_job)>0 ){ //recent completed job found
	if( isset($_GET['id']) && $_GET['id']!="" ){ //check property id url param
		if($recent_job_type=="Once-off"){ //once-off job > update retest_date to 1521-03-16
			mysql_query("
				UPDATE `property`
				SET `retest_date` = '1521-03-16'
				WHERE `property_id` = {$property_id}
			");
		}else{ // not once-off job > update retest_date to job_date+1year
			mysql_query("
				UPDATE `property`
				SET `retest_date` = DATE_ADD('$recent_jobdate', INTERVAL 1 YEAR)
				WHERE `property_id` = {$property_id}
			");
		}

	}
}else{ //if empty result > find job != Completed if return row update retest_date to job_date+365 otherwise update to null
	$sql_not_completed_job = mysql_query("
		SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type, j.created as j_created
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`property_id` = {$property_id}
		AND j.`status` != 'Completed'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}
		ORDER BY j.`date` DESC
		LIMIT 1
	");
	$recent_not_completed_jobdate_fetch_arr = mysql_fetch_array($sql_not_completed_job);
	$recent_not_completed_jobdate = $recent_not_completed_jobdate_fetch_arr['j_created'];
	if( mysql_num_rows($sql_not_completed_job)>0 ){ //active job found update to job date + 635 days
		if( isset($_GET['id']) && $_GET['id']!="" ){ //check property id
			mysql_query("
				UPDATE `property`
				SET `retest_date` = DATE_ADD('$recent_not_completed_jobdate', INTERVAL 1 YEAR)
				WHERE `property_id` = {$property_id}
			");
		}
	}else{ //no active job found > update retest_date to NULL
		if( isset($_GET['id']) && $_GET['id']!="" ){ //check property id
			mysql_query("
				UPDATE `property`
				SET `retest_date` = '1521-03-17'
				WHERE `property_id` = {$property_id}
			");
		}
	}

}
// ON LOAD RUN QUERY (gherx) end

//get retest date by propertyid / return retest date
function get_retest_date($prop_id){
	$retest_date_query = mysql_query("
		SELECT retest_date
		FROM `property`
		WHERE `property_id` = {$prop_id}
	");
	$retest_date_row = mysql_fetch_array($retest_date_query);

	return ($retest_date_row['retest_date']!=NULL && $retest_date_row['retest_date']!='1521-03-16' && $retest_date_row['retest_date']!='1521-03-17') ? date('d/m/Y', strtotime($retest_date_row['retest_date'])) : 'N/A';
}

// (2) Run the query

	//$new_pm = 0;
	$new_pm = NEW_PM;

	if( $new_pm == 1 ){ // NEW PM

		$pm_field = '`pm_id_new`,';

	}else{ // OLD PM

		$pm_field = '`property_managers_id`,';

	}

   $Query = "SELECT is_sales, api, api_prop_id, address_1, address_2, address_3, state, postcode, comments, a1_type, a1_exp, a2_type, a2_exp, a3_type, a3_exp, a4_type, a4_exp, a5_type, a5_exp, a6_type, a6_exp, testing_comments, new_alarms_installed, authority_recd, DATE_FORMAT(tenant_ltr_sent, '%d/%m/%Y'), DATE_FORMAT(booking_date, '%d/%m/%Y'), phone_booking, DATE_FORMAT(test_date,'%d/%m/%Y'), booking_comments, inv_number, DATE_FORMAT(retest_date,'%d/%m/%Y'), tenant_firstname1, tenant_lastname1, tenant_ph1, tenant_firstname2, tenant_lastname2, tenant_ph2, landlord_firstname, landlord_lastname, landlord_email, inv_number, a1_pwr, a1_make, a1_model, a2_pwr, a2_make, a2_model, a3_pwr, a3_make, a3_model, a4_pwr, a4_make, a4_model, a5_pwr, a5_make, a5_model, a6_pwr, a6_make, a6_model, price, DATE_FORMAT(test_date, '%d/%m/%Y'), service, agency_deleted, deleted, tenant_email1, tenant_email2, tenant_mob1, tenant_mob2, key_number, comments, tenant_changed, `holiday_rental`, `no_keys`, `alarm_code`, `landlord_mob`, `landlord_ph`, `no_en`, `is_nlm`, tenant_firstname3,tenant_lastname3,tenant_mob3,tenant_ph3,tenant_email3,tenant_firstname4,tenant_lastname4,tenant_mob4,tenant_ph4,tenant_email4,`nlm_timestamp`,`nlm_by_sats_staff`,`nlm_by_agency`, `nlm_display`, `prop_upgraded_to_ic_sa`, `propertyme_prop_id`, `ourtradie_prop_id`, `palace_prop_id`, {$pm_field} `compass_index_num`, `bne_to_call`, `no_dk`, `is_sales`, `send_to_email_not_api`, `requires_ppe`, `manual_renewal`, `qld_new_leg_alarm_num`, `subscription_billed`, `service_garage` 
   FROM property LEFT JOIN api_property_data ON property.property_id = api_property_data.crm_prop_id where (property_id='".$id."');";


     $result = mysql_query ($Query, $connection);

	 $row = mysql_fetch_array($result);
	 /*
	 print_r($row);
	 echo "<br />";
	 echo "<br />";
	 echo "<br />";
	 */
	 
	$bn_sql_str = "
		SELECT `building_name`
		FROM `other_property_details`
		WHERE `property_id` = {$id} 
		";        
	$bn_sql = mysql_query($bn_sql_str); 
	$bn_sql_row = mysql_fetch_object($bn_sql);



?>

  <div id="mainContent">
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Properties" href="<?=URL;?>view_properties.php">View Properties</a></li>
        <li class="other second"><a title="View Property Details" href="/view_property_details.php?id=<?php echo $_REQUEST['id']; ?>"><strong>View Property Details</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

    <?php
    # Process Upload
	if($_FILES['fileupload']['error'] == 0 && $_FILES['fileupload']['size'] > 0)
	{

		// check if filename already exist
		if(file_exists(UPLOAD_PATH_BASE . $id . "/" . $_FILES['fileupload']['name'])){
			echo "<div class='error'>Filename Already Exists. Please use a Unique File Name</div>";
		}else{

			$upload_ret = uploadfile2($_FILES, $id);

			if( $upload_ret['success'] == true ){
				echo "<div class='success'>File Uploaded Successfully</div>";
			}else{
				echo "<div class='error'>{$upload_ret['error']}</div>";
			}

		}


	}

	# Process Delete
	if(isset($_GET['delfile']))
	{

		$delfile = rawurldecode($_GET['delfile']);
		if(deleteFile($delfile, $id))
		{

			// appended - insert log
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
					{$id},
					{$staff_id},
					'File Upload',
					'".mysql_real_escape_string($delfile)." Deleted',
					'".date('Y-m-d H:i:s')."',
					1
				)"
			);

			echo "<div class='success'>File Deleted Successfully</div>";
		}
		else
		{
			echo "<div class='error'>Technical Problem. Please try again</div>";
		}
	}
    ?>

   <?php
   if($_GET['update_price_success']==1){ ?>
		<div class="success">Update Price Successful</div>
   <?php
   }
   ?>

   <?php
   if($_GET['update_price_success']==2){ ?>
		<div class="success">Add Property Successful</div>
   <?php
   }
   ?>

    <?php
   if($_GET['pending']==1){ ?>
		<div class="success">Other Supplier Job Successfully Added</div>
   <?php
   }
   ?>

   <?php
   if($_GET['job_moved']==1){ ?>
		<div class="success">Job Successfully Moved</div>
   <?php
   }
   ?>


    <?php
   if($_GET['add_prop_success']==1){ ?>
		<div class="success">New Property Created</div>
   <?php
   }
   ?>

	<?php
   if($_GET['ic_service_type_update']==1){ ?>
		<div class="success">IC Service Type Update Successful</div>
   <?php
   }
   ?>

	<?php
	if($_GET['service_type_update']==1){ ?>
			<div class="success">Service Type Update Successful</div>
	<?php
	}
	?>


	<?php
	if($_GET['add_new_service_type']==1){ ?>
			<div class="success">New Service Type Added</div>
	<?php
	}
	?>

	<?php
	if($_GET['non_active_service_update_success']==1){ ?>
			<div class="success">New Service Type Added</div>
	<?php
	}
	?>


	<?php
	if($_GET['remove_property_variation_success']==1){ ?>
			<div class="success">Property Variation Removed</div>
	<?php
	}
	?>



<?php
$q = "SELECT a.`agency_id`, a.`agency_name`, a.`phone`, a.`franchise_groups_id`, a.`status`, aght.`priority` FROM agency a
LEFT JOIN property p ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
WHERE (p.`property_id` ='".$id."')"; //(a.agency_id = p.agency_id)";
$res = mysql_query ($q, $connection);
$row1 = mysql_fetch_row($res);
// Franchise Group
$franchise_group = $row1[3];
$private_fg = 0;

if( $crm->getAgencyPrivateFranchiseGroups($franchise_group) == true ){ // private FG
	$private_fg = 1;
}
?>
<div <?php echo ($row['deleted']==1)?'style="background-color:#ECECEC"':''; ?>>


<table border=1 cellpadding=0 cellspacing=0 width=100% class="view-property-table">
<tr class="padding-none">
<td class="padding-none">









<?php

$id = $_GET['id'];











      echo "\n";
     // (4) Print out each element in $row, that is,
     // print the values of the attributes

		$row[1] = htmlspecialchars($row[1], ENT_QUOTES);
		$row[2] = htmlspecialchars($row[2], ENT_QUOTES);
		$row[3] = htmlspecialchars($row[3], ENT_QUOTES);
/*
		echo "<td>";
		echo "<input type='text' name='address_1' value='$row[0]' size=5><input type='text' name='address_2' value='$row[1]' size=25><input type='text' name='address_3' value='$row[2]' size=7>";
		echo "</td>";

		echo "<td valign=top>";
		echo "<input type='text' name='state' value='$row[3]' size=4>";
		echo "</td>";

		echo "<td valign=top'>";
		echo "<input type='text' name='postcode' value='$row[4]' size=4>";
		echo "</td>";
*/



$agency_id = $row1[0];

// check if agency preference "Does the client allow annual visits?" is NO
$agency_new_pref_sql = mysql_query("
SELECT COUNT(`id`) AS agen_pref_sel_count
FROM `agency_preference_selected`
WHERE `agency_id` = {$agency_id}
AND `agency_pref_id` = 24
AND `sel_pref_val` = 0
");
$agency_new_pref_row = mysql_fetch_object($agency_new_pref_sql);
$no_annual_visits = ( $agency_new_pref_row->agen_pref_sel_count > 0 )?1:0;


		if( $row1[4] == 'deactivated' ){ // $row1[4] is agency status, weird way to fetch field
			echo "<div id='permission_error'>Agency is deactivated: You cannot create a new job while an Agency is deactivated.</div>";
		}


		if($_REQUEST['success']){
			echo "<div class='success'>Database Updated\n</div>";
		}

		if($_REQUEST['landlord']){
			echo "<div class='success'>Landlord Updated\n</div>";
		}

		if($row['deleted'])
		{
			echo "<div id='permission_error'>This Property is Deleted! If you want to Restore this property, please contact IT</div>";
		}
		elseif($row['agency_deleted'])
		{
			echo "<div id='permission_error'>This property has been marked as no longer managed / deleted by the agency!</div>";
		}
		elseif($row['is_nlm']==1){

			echo "<div id='permission_error'>This property has been marked as no longer managed! To Restore it press the &Prime;Restore this Property&Prime; button</div>";
			
		}
		else
		{
			//echo "<form method=get action='" . URL . "change_of_tenancy.php'><input type=hidden name='id' value='" . $id . "'><input type='submit' value='Create a Job for this Property'> <select name='jobtype'><option value='ym'>Yearly Maintenance</option><option value='ct'>Change of Tenancy</option><option value='oo'>Once Off</option><option value='240v'>240v Rebook</option><option value='for'>Fix or Replace</option></select></form>\n";
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
		'agency_id' => $agency_id,
		'display_echo' => 0
		);
		$api_sql = $crm->get_agency_api_tokens($api_token_params);

		$connected_to_pme = false;
		$connected_to_palace = false;
		$connected_to_propertytree = false;
		//$api_row = mysql_fetch_array($api_sql);

		while ($api_row = mysql_fetch_array($api_sql)){

			if ( $api_row['api_id'] == 1 ){ // PMe

				$connected_to_pme = true;

				$agency_api_params = array(
					'prop_id' =>  $row['api_prop_id'],
					'agency_id' => $agency_id
				);

				$pme_prop_json = $agency_api->get_property_pme($agency_api_params);
				$pme_prop_json_dec = json_decode($pme_prop_json);

				if( $pme_prop_json_dec->IsArchived == true ){
					echo "<div id='permission_error'>This Property is Deactivated in PropertyMe</div>";
				}

			}

			
			if ( $api_row['api_id'] == 3 ){ // PropertyTree

				$connected_to_propertytree = true;

				// get tenants contact ID
				$agency_api_params = array(
					'property_id' => $property_id
				);

				$curl_ret_arr = $agency_api->get_property_tree_property($agency_api_params);

				$raw_response = $curl_ret_arr['raw_response'];
				$json_decoded_response = $curl_ret_arr['json_decoded_response'];
                $http_status_code = $curl_ret_arr['http_status_code'];

				if( $http_status_code == 200 ){ // OK
                    $crm_api_params = array(
                        'api_id' =>  $api_row['api_id'],
                        'crm_prop_id' => $property_id
                    );

					$api_prop_obj = $json_decoded_response[0];
                    $crm_prop_obj = $agency_api->get_crm_property_unlinked_property($crm_api_params);

					if( $crm_prop_obj === 1 && ($api_prop_obj->archived == true || $api_prop_obj->deleted == true) ){
						echo "<div id='permission_error'>This Property is Deactivated in PropertyTree</div>";
					}

				}else{ // error
					
					echo "<div class='error pt_api_error'><h3>This property's agency does not exist, please change the property to another agency.</h3></div>";
					
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
					'agency_id' => !empty($agency_id) ? $agency_id : null
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
					'agency_id' => !empty($agency_id) ? $agency_id : null
				);

				/*$ot_prop_json = $agency_api->get_property_ourtradie($agency_api_params);
				$ot_prop_json_dec = json_decode($ot_prop_json);

				if( $ot_prop_json_dec->IsArchived == true ){
					echo "<div id='permission_error'>This Property is Deactivated in PropertyMe</div>";
				}
				*/


			}

		}




echo "</div>";
 // Ends first-div


 echo "
	<div class='vw-pro-dtl-tn-hld vpr-left clear' style='float: none;  margin: 0;'>
	";

	   $row[28] = htmlspecialchars($row[28], ENT_QUOTES);
		$row[29] = htmlspecialchars($row[29], ENT_QUOTES);



	//echo " <div style='overflow: hidden;'><h2 class='heading tntdetail'>{$td_txt}</h2>";

	//echo "<div style='margin-top: 21px; margin-left: 10px; float: left;color:#00D1E5;text-align: left;margin-bottom: 8px; font-size: 13px;'>".(($row['tenant_changed']!="0000-00-00 00:00:00")?'Last Updated: '.date("d/m/Y",strtotime($row['tenant_changed'])):'')."</div>";

	?>

	<div style="width: 100%;">
		<div id="tabs" class="c-tabs no-js">
			<div class="c-tabs-nav">
				<a href="#" data-tab_index="0" data-tab_name="address" class="c-tabs-nav__link is-active">Address</a>
				<a href="#" data-tab_index="1" data-tab_name="tenants" class="c-tabs-nav__link">
				<?php
				$dha_agencies = array(3043,3036,3046,1902,3044,1906,1927,3045);
				$mem_details_txt = (in_array($row1[0], $dha_agencies))?'Members':'Tenants';
				echo $mem_details_txt;
				?>
				</a>
				<a href="#" data-tab_index="2" data-tab_name="landord" class="c-tabs-nav__link">Landlord</a>
				<a href="#" data-tab_index="3" data-tab_name="more_details" class="c-tabs-nav__link">More Details</a>
				<a href="#" data-tab_index="4" data-tab_name="services" class="c-tabs-nav__link">Services/Jobs</a>
				<a href="#" data-tab_index="5" data-tab_name="prop_logs" class="c-tabs-nav__link">Logs</a>
				<a href="#" data-tab_index="6" data-tab_name="prop_files" class="c-tabs-nav__link">Files</a>
			</div>

			<form id="jform" method=post name='example' id='example' action='/update_property.php'>
			<input type=hidden name='id' value='<?php echo $id; ?>'>
			<!-- ADDRESS -->
			<div class="c-tab is-active" data-tab_cont_name="address">
				<div class="c-tab__content">

					<?php  $prop_full_add = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>

					<div class='row'  style="float:left; width: 593px; margin-bottom: 20px; margin-right: 30px;">


						<div class="row">

						<div class='row' style='width: auto;'>
						 <label for='fullAdd' class=''>Google Address Bar</label>
						<input type='text' name='fullAdd' id='fullAdd' class='addinput vw-pro-dtl-tnt short-fld' style='width: 581px !important;' value="<?php echo $prop_full_add; ?>" />
						</div>

						<div style='clear:both;padding-bottom: 20px'></div>

						<div class='row'>
						 <label for='address_1' class=''>No.</label>
						<input type='text' style="width: 77px !important;" name='address_1' id='address_1' value="<?php echo $row['address_1'] ?>" class='addinput vw-pro-dtl-tnt short-fld'>
						</div>
						<div class='row'>
						 <label for='address_2' class=''>Street</label>
						<input type='text' style="width: 165px !important;" name='address_2' id='address_2' value="<?php echo $row['address_2'] ?>" class='addinput vw-pro-dtl-tnt long-fld streetinput'>
						</div>
						<div class='row'>
						<label for='address_3' class=''>Suburb</label>
						<input type='text' style='width: 141px !important;' name='address_3' id='address_3' value="<?php echo $row['address_3'] ?>" class='addinput vw-pro-dtl-tnt big-fld'>
						<input type="hidden" id="locality" />
						<input type="hidden" id="sublocality_level_1" />
					</div>

					<?php
					if(ifCountryHasState($_SESSION['country_default'])==true){

						?>

						<div class='row' style='margin-right: 10px;'>
						<label for='state' class=''>State</label>

						<select class="addinput vpr-adev-sel" name="state" id="state" style="width: 78px !important;">
							<option value="">----</option>
							<option value="NSW" <?php echo ($row['state']=='NSW')?'selected="selected"':''; ?>>NSW</option>
							<option value="VIC" <?php echo ($row['state']=='VIC')?'selected="selected"':''; ?>>VIC</option>
							<option value="QLD" <?php echo ($row['state']=='QLD')?'selected="selected"':''; ?>>QLD</option>
							<option value="ACT" <?php echo ($row['state']=='ACT')?'selected="selected"':''; ?>>ACT</option>
							<option value="TAS" <?php echo ($row['state']=='TAS')?'selected="selected"':''; ?>>TAS</option>
							<option value="SA" <?php echo ($row['state']=='SA')?'selected="selected"':''; ?>>SA</option>
							<option value="WA" <?php echo ($row['state']=='WA')?'selected="selected"':''; ?>>WA</option>
							<option value="NT" <?php echo ($row['state']=='NT')?'selected="selected"':''; ?>>NT</option>
						 </select>

						 </div>

						<?php

					}else{ ?>

						<div class='row' style='margin-right: 10px;'>
						<label for='state' class=''>Region</label>
						<input type='text' name='state' style="width: 60px !important;" id='state' value="<?php echo $row['state']; ?>" class='addinput vw-pro-dtl-tnt big-fld'>
						</div>

					<?php
					}

					?>



					<div class='row'>
							<label for='tenant_ph1' class=''>Postcode</label>
							<input type='text' style='width: 40px !important;' name='postcode' id='postcode' value='<?php echo $row['postcode']; ?>' class='addinput vw-pro-dtl-tnt'>
						</div>
					</div>

					<div class="row">
						<label for="building_name" class="">Building Name</label>
						<?php 
							if(!empty($bn_sql_row)){
								$bname = $bn_sql_row->building_name;
							}
							else{
								$bname = "";
							}
						?>
						<input type="text" style="width: 100px !important;" name="building_name" id="building_name" value="<?php echo $bname; ?>" class="addinput vw-pro-dtl-tnt long-fld streetinput">
					</div>

					<div style="clear:both;padding-bottom: 20px"></div>

					<div class='row'>
						<label for='tenant_ph1' class=''>Property Notes</label>
						<textarea name='prop_comments' id='prop_comments'  class='addtextarea vpr-adev-txt' style='width: 580px;'><?php echo $row['comments']; ?></textarea>
					</div>


					<div class='row' style="margin-top: 23px;">
							<div class='agencyName vw-pr-fl'>
							<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row1[0]}"); ?>
							<a class="<?php echo ( $row1[5] == 1 || $row1[5] == 2 || $row1[5] == 3)?'j_bold':null; ?>" href='<?php echo $ci_link; ?>'>
							<?php 
								if($row1[5] == 1){
									echo "{$row1[1]} (HT)";
								}
								else if($row1[5] == 2){
									echo "{$row1[1]} (VIP)";
								}
								else if($row1[5] == 3){
									echo "{$row1[1]} (HWC)";
								}
								else{
									echo "{$row1[1]}";
								}
								//echo "{$row1[1]}".( ( $row1[5] == 1 )?' (HT)':null ); 
							?>
							</a></div>
							<div style='float: left; margin-left: 15px; margin-right: 15px;'><?php echo $row1[2]; ?></div>
					</div>


					<div style="clear:both;"></div>


					<div class="row" style="float:right">
						<button class="submitbtnImg colorwhite btn_update_prop" type="button">Update</button>	
						<a href="<?php echo $crm->crm_ci_redirect(rawurlencode("/properties/details/?id={$_GET['id']}&tab=1")); ?>" class="submitbtnImg blue-btn" target="_blank" style="color: white;">
                            CI VPD
                        </a>					
					</div>


					</div>



					<div class="row"  style="float:left; margin-top: 14px;">
						<a href='<?php echo "change_agency_static.php?id={$id}"; ?>'><button type="button" id="btn_change_agency" class="submitbtnImg blue-btn">Change Agency</button></a>
						<?php
						if( $row['deleted']==0 && $row['agency_deleted']==0 && $row['is_nlm']==0 ){ ?>
							<button data-val="0" type="button" id="btn_no_longer_managed" class="submitbtnImg">No Longer Manage?</button>
						<?php
						}
						?>
					</div>

                    <div class="row"  style="float:left; margin-top: 14px;margin-left:20px;width:650px;margin-bottom:20px;">
                        <div id="map-canvas" style="width:100%;height:400px;border:1px solid #cccccc;"></div>
                    </div>
					<table>
					<tr>
                        <td id="nlm_td_box" colspan="8" style="padding: 0px;height:0px!important;">

                         <div id="nlm_td_box_toggle" style="display: none;">
                             <table class="no-border" id="nlm_box" style="width:100%">
                            <tr>
                              <td style="padding-top:15px;padding-bottom: 15px;">
                                  <table class="no-border nlm_box_warning" style="width:100%">
                          <tr>
						  <td style="padding:0px">
<div class="txt-center alert alert-danger alert-close alert-dismissible fade show ">
    <i class="font-icon font-icon-warning red"></i>&nbsp;&nbsp;Warning - If you proceed, All Jobs and Services will be Cancelled, and this property will be Archived </div>
                          </td>
                        </tr>
                        </table>

                            <table class="nlm_box_fields"  style="width:100%">
                            <thead>
                                 <tr>

						<th>"No Longer Managed" From *</th>
						<th>"Reason they Left*</th>
                        <th style="display: none;" id="other_reason1">"Other*</th>
                        <th>&nbsp;</th>
                    </tr>
                                </thead>
                                <tbody>
                                <tr>
									<td>
										<div class="form-group">
											<div class="input-group date flatpickr" data-wrap="true" >
												<input TYPE="text" style='width: 77px; float: none' id="nlm_from" name="nlm_from" value="" SIZE=12 class="datepicker addinput vpr-adev-in" />
											</div>
										</div>
									</td>
                                    <td>
                                    <div class="form-group">
                                        <div class="input-group date flatpickr" data-wrap="true" >
                                            <!-- <input data-validation="[NOTEMPTY]" data-validation-label="No Longer Managed From" data-input  type="date" class="form-control" name="nlm_from" id="nlm_from"> -->
											<div class="form-group">
												<label class="form-label"></label>
												<select class="form-control" id="reason_they_left" name="reason_they_left">
													<option value="">---Select Reason---</option>
													<?php
													// get leaving reason     
													$lr_sql = mysql_query("
													SELECT *
													FROM `leaving_reason`
													WHERE `active` = 1
													AND `display_on` IN(2,4,5)
													ORDER BY `reason` ASC
													");                                           
													while ($lr_row = mysql_fetch_array($lr_sql)){ ?>
														<option value="<?php echo $lr_row['id']; ?>"><?php echo $lr_row['reason']; ?></option> 
													<?php
													}                                         
													?>  
													<option value="-1">Other</option>                                                                                                                                             
												</select>
											</div>
										</div>
                                    </div>
                                    </td>
                                    <td><div class="form-group">
										<textarea style="float: none; display: none;" class="form-control addtextarea" id="other_reason" name="other_reason"></textarea>
                                    </div></td>
                                    <!-- <td><div class="form-group"><input data-validation="[NOTEMPTY]" data-validation-label="NO Longer Managed Reason" class="form-control requiredV2" type="text" id="nlm_reason" name="nlm_reason"></div></td> -->
                                    <td><div class="form-group"><button type="button" style="margin: 0;" id="btn_no_longer_managed_go" class="submitbtnImg">Proceed</button></div></td>
                                    </tr>
                                </tbody>
                            </table>
                                </td>
                              </tr>
                            </table>

                            </div>


                        </td>
                    </tr>
					</table>

				</div>
			</div>

			<!-- TENANTS -->
            <style type="text/css">
                .c-tab__content {
                    height: auto;
                    }
            </style>
			<div class="c-tab" data-tab_cont_name="tenants">
				<div class="c-tab__content">

					<div style="width:1000px;">

						<h2 class="heading" style="float: left; margin-left: 7px;">
							<?php echo $td_txt; ?>

							<?php
							if( $row['bne_to_call'] == 1 ){
								if( $_SESSION['country_default'] == 1 ){ // AU
									echo "<span style='margin-left: 80px;'>* This property must be booked by Brisbane Call Center *</span>";
								}else if( $_SESSION['country_default'] == 2 ){ // NZ
									echo "<span style='margin-left: 80px;'>* This property must be booked by Auckland Call Center *</span>";
								}

							}
							?>
						</h2>

							<?php
							// agency integrated API
							//if(  in_array($_SESSION['USER_DETAILS']['StaffID'], $crm->tester()) ){

							?>
								<?php if( $row['is_sales']!=1 ){ ?>
								<div class="pme_table_div">
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

											if( $row['propertyme_prop_id'] != '' && $agency_id != '' ){

												if( $property_id > 0 ){
													
													// get console tenants
													$console_sql = mysql_query("
													SELECT *
													FROM `console_property_tenants` AS cpt
													INNER JOIN `console_properties` AS cp ON ( cpt.`console_prop_id` = cp.`console_prop_id` AND cp.`active` = 1 )
													WHERE cp.`crm_prop_id` = {$property_id}													
													AND cpt.`active` = 1
													AND cpt.`is_landlord` = 0
													");

													if( mysql_num_rows($console_sql) > 0 ){ // no error
																						
														$console_tenants_arr = [];
														while( $console_tenants_row = mysql_fetch_object($console_sql) ){

															/*
															$pme_tenants_arr[] = array(
																'fname' => trim($pme_tenant->first_name),
																'lname' => trim($pme_tenant->last_name),
																'mobile' => $crm->remove_space(trim($pme_tenant->CellPhone)),
																'landline' => $crm->remove_space(trim($pme_tenant->HomePhone)),
																'email' => trim($pme_tenant->Email),
																'UpdatedOn' => trim($contact_json_enc->Contact->UpdatedOn),
																'company_name' => trim($pme_tenant->CompanyName)
															);
															*/
															
														}
					
													}

												}																											
				
											}

											if( $cak_row->cak_count > 0 ){ // console using webhooks ?>
												
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

												//Chops HERE!
												$api_sql = $crm->get_agency_api_tokens($api_token_params);

												while ($api_row = mysql_fetch_array($api_sql)) {
														$enableApi = false; // this is to ensure that api is completely implemented before showing it, add it new api integration on the if below.
														$controlerApi = "";
														$connTextApi = "";
														$checkIdApi = "";

														//echo "TESTING 1023";
														//echo "API".$api_row['api_id'];
														if ($api_row['api_id'] == 1) { // pme
															$enableApi = true;
															$controlerApi = 'property_me';
															$connTextApi = 'PropertyMe';
															/*	##disabled > use new generic table instead
															if(!empty($row['propertyme_prop_id'])){
																$checkIdApi = $row['propertyme_prop_id'];
															}
															else{
																$checkIdApi = $row['api_prop_id'];
															}*/

															## new > use new generic table
															$checkIdApi = $row['api_prop_id'];

														}else if ($api_row['api_id'] == 4) { // palace
															$enableApi = true;
															$controlerApi = 'palace';
															$connTextApi = 'Palace';
															/* ##disabled > use new generic table instead
															if(!empty($row['palace_prop_id'])){
																$checkIdApi = $row['palace_prop_id'];
															}
															else{
																$checkIdApi = $row['api_prop_id'];
															}
															*/
															## new > use new generic table
															$checkIdApi = $row['api_prop_id'];
														}
														else if ($api_row['api_id'] == 6) { // ourtradie

															$enableApi = true;
															$controlerApi = 'ourtradie';
															$connTextApi = 'OurTradie';
															$checkIdApi = $row['api_prop_id'];
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
															//echo "<br />";
															$crm_connected_prop_sql = mysql_query($crm_connected_prop_sql_str);
															$crm_connected_prop_row = mysql_fetch_object($crm_connected_prop_sql);

															$checkIdApi = $crm_connected_prop_row->api_prop_id;
															//echo "checkIdApi: {$checkIdApi}<br />";
															$pt_prop_id = $crm_connected_prop_row->api_prop_id;
															//echo "pt_prop_id: {$pt_prop_id}<br />";

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

																	//Chops HERE!
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
																																			<?php //echo "Data".$checkIdApi; ?>
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
								</div>
								<?php } ?>
							<?php
							//}




							//if( $row['propertyme_prop_id'] != '' && $agency_id != '' ){
							if ($row['api_prop_id'] != '' && $agency_id != '' && $row['api'] == 1) { ## PME

								// get tenants contact ID
								$pme_tenants_arr = [];
								$agency_api_params = array(
									'prop_id' =>  $row['api_prop_id'],
									'agency_id' => $agency_id
								);

								$tenant_json = $agency_api->get_tenants($agency_api_params);
								$tenant_json_enc = json_decode($tenant_json);

								if( $tenant_json_enc->ResponseStatus->ErrorCode == '' && !empty($tenant_json_enc) ){ // no error

									$tenant_contact_id = $tenant_json_enc[0]->ContactId;

									if( $tenant_contact_id != '' ){

										// get pme tenants
										$agency_api_params = array(
											'contact_id' =>  $tenant_contact_id,
											'agency_id' => $agency_id
										);
										$contact_json = $agency_api->get_contact($agency_api_params);
										$contact_json_enc = json_decode($contact_json);
										
										foreach( $contact_json_enc->ContactPersons as $pme_tenant ){
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

                            if ($row['api_prop_id'] != '' && $agency_id != '' && $row['api'] == 4) { ## PALACE

                                // get tenants contact ID
                                $agency_api_params = array(
                                    'prop_id' => $row['api_prop_id'],
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

								
								if( $http_status_code == 200 ){

									if( $api_prop_obj->tenancy != '' ){

										// get tenants contact ID
										$agency_api_params = array(
											'tenancy_id' => $api_prop_obj->tenancy,
											'agency_id' => $agency_id
										);
										$api_tenant_json = $agency_api->get_property_tree_tenancy($agency_api_params);
										$contact_arr = $api_tenant_json->contacts;
	
										//echo "contacts: <br />";
										//print_r($contact_arr);
		
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
	
										//echo "pt_tenants_arr: <br />";
										//print_r($pt_tenants_arr);
	
									}

								}	
																				 								                             

							}

							if ($row['api_prop_id'] != '' && $agency_id != '' && $row['api'] == 6) {
								
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

								/*
								$separator = "+/-]";

								$update_token_str = "
									UPDATE `agency_api_tokens`
									SET 
										`access_token` = '{$raccess_token}',
										`expiry` = '{$expiry}',
										`refresh_token` = '$refresh_token . "."+/-]"." . $tmp_arr_refresh_token[1]'
									WHERE `agency_id` = 4229
									AND `api_id` = {$api_id}
								";
								mysql_query($update_token_str) or die(mysql_error());

								echo "QUERY <br /><br />";
								echo $update_token_str;

								$access_token    = $raccess_token;

								*/
								
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
									//echo $access_token;
								}
								/*
								$update_token_str = "
									UPDATE `agency_api_tokens`
									SET 
										`access_token` = '{$access_token}',
										`expiry` = '{$token_expiry}',
										`refresh_token` = '{$refresh_token}'
									WHERE `agency_id` = {$agency_id}
									AND `api_id` = {$api_id}
								";
								mysql_query($update_token_str) or die(mysql_error());
								*/

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
										if($row1['ID'] == $row['api_prop_id']){
											$tenants_data = $row1['Tenant_Contacts'];
										}
									}
								}

								
								//echo "<br /><br />====Tenants<br />";
								//print_r($tenants_data);
								

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
								/*
								echo "<br /><br />";
								echo "==== Tenant Array: <br />";
								print_r($ourtradie_tenants_arr);
								*/

							}

							// console API tenants
							if ( $console_prop_id > 0 ) {                                    

								// get console tenants
								$console_tenant_sql_str = "
								SELECT *
								FROM `console_property_tenants` AS cpt
								INNER JOIN `console_properties` AS cp ON ( cpt.`console_prop_id` = cp.`console_prop_id` AND cp.`active` = 1 )
								WHERE cp.crm_prop_id = {$property_id}								
								AND cpt.active = 1
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

                            //if ($row['propertyme_prop_id'] != '' || $row['palace_prop_id'] != '' || $row['api_prop_id'] != '' || $pt_prop_id != '' ) {
							if ( $row['api_prop_id'] != '' || $pt_prop_id != '' ) {
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




					<div style="clear:both;"></div>
					<?php include 'tenant_details_new_for_vpd.php'; ?>




					<div style="float:right;margin:11px;margin-right:0px;">


					<button type="button" id="add_new_tenant_btn" class="submitbtnImg blue-btn">
					<img class="inner_icon" src="images/button_icons/add-button.png">
					<span class="inner_icon_txt">Tenant</span>
					</button>
                    <button type="button" id="view_all_tick_column" class="blue-btn submitbtnImg">
                        <!-- <img class="inner_icon" src="images/button_icons/show-button.png">  -->
                        &#10006; <span class="inner_icon_txt">Hide</span>
                    </button>
					</div>




					<?php
					// if property connected to PMe
					if( $row['api_prop_id'] != '' && $agency_id != '' && count($pme_tenants_arr) > 0 && false ){ // set to false to NOT display

						?>

						<div style="clear:both;"></div>



						<div id="all_pme_table">
							<h2 class="heading">
								PropertyMe Tenants
								<button type="button" id="view_all_pme_tnt_btn" class="blue-btn submitbtnImg">
									<img class="inner_icon" src="images/button_icons/show-button.png">
									<span class="inner_icon_txt">Show</span>
								</button>
							</h2>
							<table id="pme_tnt_tbl" class="table-center tbl-fr-red view-property-table-inner jtenant_table 111">
								<tr>
									<td class="j_tbl_heading">First Name</td>
									<td class="j_tbl_heading">Last Name</td>
									<td class="j_tbl_heading">Mobile</td>
									<td class="j_tbl_heading">Landline</td>
									<td class="j_tbl_heading">Email</td>
									<td class="j_tbl_heading">Action</td>
								</tr>
								<?php
								foreach( $pme_tenants_arr as $pme_tnt_row ){

									$pme_tenants_full_name = "{$pme_tnt_row['fname']} {$pme_tnt_row['lname']}";

									$row_hl = "PMe_tenant_new_bg";

									$tenant_already_exist = 0;
									$tenant_has_update = 0;
									$new_tenant = 0;
									foreach( $crm_tenants_arr as $crm_tenant ){

										$crm_tenant_full_name = "{$crm_tenant['fname']} {$crm_tenant['lname']}";

										// same all 5 fields
										if(
											$crm_tenant_full_name == $pme_tenants_full_name &&
											$crm_tenant['mobile'] == $pme_tnt_row['mobile']  &&
											$crm_tenant['landline'] == $pme_tnt_row['landline']  &&
											$crm_tenant['email'] == $pme_tnt_row['email']
										){

											$tenant_already_exist = 1;

										}else{

											if( $crm_tenant_full_name == $pme_tenants_full_name ){
												$tenant_has_update = 1;
											}else{
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
									if( $tenant_already_exist == 1 ){
										$row_hl = "PMe_tenant_exist_bg hideIt";
									}else if( $tenant_has_update == 1 ){
										$row_hl = "crm_tenant_need_update_bg";
									}else if( $new_tenant == 1 ){
										$row_hl = "PMe_tenant_new_bg";
									}

									// if tenants name is blank, use company name instead as firstname
									if( $pme_tnt_row['fname'] == '' && $pme_tnt_row['lname'] == '' ){
										$tenant_fname = $pme_tnt_row['company_name'];
										$tenant_lname = '';
									}else{
										$tenant_fname = $pme_tnt_row['fname'];
										$tenant_lname = $pme_tnt_row['lname'];
									}

								?>

									<tr class="<?php echo $row_hl; ?>">
										<td>
											<?php echo $tenant_fname; ?>
										</td>
										<td>
											<?php echo $tenant_lname; ?>
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

											<input type="hidden" class="pme_tenant_fname" value="<?php echo $tenant_fname; ?>" />
											<input type="hidden" class="pme_tenant_lname" value="<?php echo $tenant_lname; ?>" />
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
					if( $row['ourtradie_prop_id'] != '' && $agency_id != '' && count($ourtradie_tenants_arr) > 0){ // set to false to NOT display
					?>

						<div style="clear:both;"></div>

						<div id="all_pme_table" style="display: none">
							<h2 class="heading">
								OurTradie Tenants
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

								foreach( $ourtradie_tenants_arr as $ourtradie_tnt_row ){

									$ourtradie_tenants_name = "{$ourtradie_tnt_row['fname']}";

									$row_hl = "PMe_tenant_new_bg";

									$tenant_already_exist = 0;
									$tenant_has_update = 0;
									$new_tenant = 0;
									foreach( $crm_tenants_arr as $crm_tenant ){
										//echo "<br /><br />TEST";
										$crm_tenant_name = "{$crm_tenant['fname']}";

										// same all 5 fields
										if(
											$crm_tenant_name == $ourtradie_tenants_name &&
											$crm_tenant['mobile'] == $ourtradie_tnt_row['mobile']  &&
											$crm_tenant['email'] == $ourtradie_tnt_row['email']
										){

											$tenant_already_exist = 1;

										}else{

											if( $crm_tenant_name == $ourtradie_tenants_name ){
												$tenant_has_update = 1;
											}else{
												$new_tenant = 1;
											}

										}

									}

									//print_r($crm_tenants_arr);
									/*
									echo "tenant_already_exist: {$tenant_already_exist}<br />";
									echo "tenant_has_update: {$tenant_has_update}<br />";
									echo "new_tenant: {$new_tenant}<br /><br />";
									*/
									

									// highlight color
									if( $tenant_already_exist == 1 ){
										$row_hl = "PMe_tenant_exist_bg hideIt";
									}else if( $tenant_has_update == 1 ){
										$row_hl = "crm_tenant_need_update_bg";
									}else if( $new_tenant == 1 ){
										$row_hl = "PMe_tenant_new_bg";
									}

									// if tenants name is blank, use company name instead as firstname
									if( $ourtradie_tnt_row['fname'] == ''){
										$tenant_fname = $ourtradie_tnt_row['company_name'];
										$tenant_lname = '';
									}else{
										$tenant_fname = $ourtradie_tnt_row['fname'];
									}

								?>

									<tr class="<?php echo $row_hl; ?>">
										<td>
											<?php echo $tenant_fname; ?>
										</td>
										<td>
											<?php echo $tenant_lname; ?>
										</td>
										<td>
											<?php echo $ourtradie_tnt_row['mobile']; ?>
										</td>
										<td>
											<?php echo $ourtradie_tnt_row['landline']; ?>
										</td>
										<td>
											<?php echo $ourtradie_tnt_row['email']; ?>
										</td>
										<td>

											<input type="hidden" class="ourtradie_tenant_fname" value="<?php echo $tenant_fname; ?>" />
											<input type="hidden" class="ourtradie_tenant_lname" value="<?php echo $tenant_lname; ?>" />
											<input type="hidden" class="ourtradie_tenant_mobile" value="<?php echo $ourtradie_tnt_row['mobile']; ?>" />
											<input type="hidden" class="ourtradie_tenant_landline" value="<?php echo $ourtradie_tnt_row['landline']; ?>" />
											<input type="hidden" class="ourtradie_tenant_email" value="<?php echo $ourtradie_tnt_row['email']; ?>" />

											<button type="button" class="blue-btn submitbtnImg add_new_ourtradie_tenant_btn">
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


					<div style="clear:both;"></div>




				</div>
			</div>

			<!-- LANDLORD -->
			<div class="c-tab" data-tab_cont_name="landord">
				<div class="c-tab__content">

					<div class='vw-pro-dtl-tn-hld clear'>

						<div class='row'>
						 <label for='landlord_firstname' class=''>First Name</label>
						 <input class='addinput vw-pro-dtl-tnt' type='text' name='landlord_firstname' id='landlord_firstname' value='<?php echo $row['landlord_firstname']; ?>' />
						</div>

						<div class='row'>
						 <label for='landlord_lastname' class=''>Last Name</label>
						 <input class='addinput vw-pro-dtl-tnt' type='text' name='landlord_lastname' id='landlord_lastname' value='<?php echo $row['landlord_lastname']; ?>' />
						</div>

						<?php
						// if franchise group = private
						//if( $private_fg == 1 ){ disabled
							?>

							<div class='row'>
							 <label for='ll_mobile' class=''>Mobile</label>
							 <input class='addinput vw-pro-dtl-tnt tenant_mobile_field' type='text' name='ll_mobile' id='ll_mobile' value='<?php echo $row['landlord_mob']; ?>' />
							</div>

							<div class='row'>
							 <label for='ll_landline' class=''>Landline</label>
							 <input class='addinput vw-pro-dtl-tnt tenant_phone_field' type='text' name='ll_landline' id='ll_landline' value='<?php echo $row['landlord_ph']; ?>' />
							</div>

						<?php //}
						?>

						<div class='row'>
						 <label for='landlord_email' class=''>Email</label>
						 <input class='tenantinput-large addinput vpd-bg-email' type='text' name='landlord_email' id='landlord_email' value='<?php echo $row['landlord_email']; ?>' />
						</div>

						<div class='row' style="margin-left:10px;">
						 <label class=''>&nbsp;</label>
						 <button class="submitbtnImg colorwhite btn_update_prop" type="button">Update</button>
						</div>

					</div>

					<!--
					<div class='vw-pro-dtl-tn-hld clear' style='text-align: left; margin-left: 401px;'>
						<button class="submitbtnImg colorwhite btn_update_prop" type="button">Update</button>
					</div>
					-->
					<div style="clear:both;"></div>

					<?php
						##get new api id from new table
						$newAPI_ID = $row['api_prop_id'];

				        // get pme property
			            $end_points = "https://app.propertyme.com/api/v1/lots/".$newAPI_ID."/detail";
			            $api_id = 1; // PMe

			            // get access token
			            $pme_params = array(
			                'agency_id' => $agency_id,
			                'api_id' => $api_id
			            );
			            $access_token = $agency_api->getAccessToken($pme_params);

			            $pme_params = array(
			                'access_token' => $access_token,
			                'end_points' => $end_points
			            );

			            $pme_prop_json = $agency_api->call_end_points($pme_params);
				        $pme_prop_json_enc = json_decode($pme_prop_json);
				        $ownerId = $pme_prop_json_enc->Ownership->ContactId;

						// get pme owner
						$agency_api_params = array(
							'contact_id' =>  $ownerId,
							'agency_id' => $agency_id
						);
						$contact_json = $agency_api->get_contact($agency_api_params);
						$contact_json_enc = json_decode($contact_json);

						$pme_landlord_arr = [];
						foreach( $contact_json_enc->ContactPersons as $pme_tenant ){
							$pme_landlord_arr[] = array(
								'fname' => trim($pme_tenant->FirstName),
								'lname' => trim($pme_tenant->LastName),
								'mobile' => $crm->remove_space(trim($pme_tenant->CellPhone)),
								'landline' => $crm->remove_space(trim($pme_tenant->HomePhone)),
								'email' => trim($pme_tenant->Email)
							);
						}
					?>
					<h2 class="heading" style="display: <?=empty($pme_landlord_arr) ? "none" : ""; ?>">
						PropertyMe Landlord
					</h2>
						<?php
							$countLandlord = 0;
							foreach ($pme_landlord_arr as $landlord) {
						?>
						<div class='vw-pro-dtl-tn-hld clear api_landlord_div'>
							<div class='row'>
							 <label for='landlord_firstname' class=''><?=$countLandlord>0 ? "":"First Name"?></label>
							 <input class='addinput vw-pro-dtl-tnt landlord_firstname_api' type='text' value='<?php echo $landlord['fname'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='landlord_lastname' class=''><?=$countLandlord>0 ? "":"Last Name"?></label>
							 <input class='addinput vw-pro-dtl-tnt landlord_lastname_api' type='text' value='<?php echo $landlord['lname'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='ll_mobile' class=''><?=$countLandlord>0 ? "":"Mobile"?></label>
							 <input class='addinput vw-pro-dtl-tnt tenant_mobile_field ll_mobile_api' type='text' value='<?php echo $landlord['mobile'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='ll_landline' class=''><?=$countLandlord>0 ? "":"Landline"?></label>
							 <input class='addinput vw-pro-dtl-tnt tenant_phone_field ll_landline_api' type='text' value='<?php echo $landlord['landline'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='landlord_email' class=''><?=$countLandlord>0 ? "":"Email"?></label>
							 <input class='tenantinput-large addinput vpd-bg-email landlord_email_api' type='text' value='<?php echo $landlord['email'] ?>' readonly="readonly" disabled="disabled" />
							</div>

						
							<div class='row'>
							 <label for='landlord_email' class=''><?=$countLandlord>0 ? "":"&nbsp;"?></label>
							<!--<button class="submitbtnImg colorwhite btn_update_landlord_api" type="button" style="margin-left: 10px;">Update</button>-->
							<button class="submitbtnImg colorwhite copy_to_crm_btn" type="button" style="margin-left: 10px;">Copy to CRM</button>
							</div>
						
							
						</div>
						<?php
								$countLandlord++;
							}
						?>


					<?php
                        $agency_api_params = array(
                            'prop_id' => $row['api_prop_id'],
                            'agency_id' => $agency_id
                        );

                        $owner_json = $agency_api->get_palace_landlord($agency_api_params);
                        $owner_json_enc = $owner_json;

						$palace_landlord_arr = [];
						foreach( $owner_json_enc as $palace_owner ){
							$palace_landlord_arr[] = array(
								'fname' => trim($palace_owner->OwnerFirstName),
								'lname' => trim($palace_owner->OwnerLastName),
								'mobile' => $crm->remove_space(trim($palace_owner->OwnerMobile)),
								'landline' => $crm->remove_space(trim($palace_owner->OwnerPhoneHome)),
								'email' => trim($palace_owner->OwnerEmail1)
							);
						}
					?>
					<h2 class="heading" style="display: <?=empty($palace_landlord_arr) ? "none" : ""; ?>">
						Palace Landlord
					</h2>
						<?php
							$palace_countLandlord = 0;
							foreach ($palace_landlord_arr as $landlord) {
						?>
						<div class='vw-pro-dtl-tn-hld clear api_landlord_div'>
							<div class='row'>
							 <label for='landlord_firstname' class=''><?=$palace_countLandlord>0 ? "":"First Name"?></label>
							 <input class='addinput vw-pro-dtl-tnt landlord_firstname_api' type='text' value='<?php echo $landlord['fname'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='landlord_lastname' class=''><?=$palace_countLandlord>0 ? "":"Last Name"?></label>
							 <input class='addinput vw-pro-dtl-tnt landlord_lastname_api' type='text' value='<?php echo $landlord['lname'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='ll_mobile' class=''><?=$palace_countLandlord>0 ? "":"Mobile"?></label>
							 <input class='addinput vw-pro-dtl-tnt tenant_mobile_field ll_mobile_api' type='text' value='<?php echo $landlord['mobile'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='ll_landline' class=''><?=$palace_countLandlord>0 ? "":"Landline"?></label>
							 <input class='addinput vw-pro-dtl-tnt tenant_phone_field ll_landline_api' type='text' value='<?php echo $landlord['landline'] ?>' readonly="readonly" disabled="disabled" />
							</div>

							<div class='row'>
							 <label for='landlord_email' class=''><?=$palace_countLandlord>0 ? "":"Email"?></label>
							 <input class='tenantinput-large addinput vpd-bg-email landlord_email_api' type='text' value='<?php echo $landlord['email'] ?>' readonly="readonly" disabled="disabled" />
							</div>

						
							<div class='row'>
							 <label for='landlord_email' class=''><?=$palace_countLandlord>0 ? "":"&nbsp;"?></label>
							<!--<button class="submitbtnImg colorwhite btn_update_landlord_api" type="button" style="margin-left: 10px;">Update</button>-->
							<button class="submitbtnImg colorwhite copy_to_crm_btn" type="button" style="margin-left: 10px;">Copy to CRM</button>
							</div>
						

						</div>
						<?php
								$palace_countLandlord++;
							}
						?>

				</div>
			</div>

			<!-- MORE DETAILS -->
			<div class="c-tab" data-tab_cont_name="more_details">
				<div class="c-tab__content">

					<div class='vw-pro-dtl-tn-hld' style='float:left; width:600px;'>

						<div class='row' style="float:none;">
						<?php
						//if( $row['propertyme_prop_id'] != '' ){
						if( $row['api_prop_id'] != '' ){

							/* disabled and use new generic table apd
							$isConnectedCheck_str = "
							SELECT
								`property_id`,
								`address_1`,
								`address_2`,
								`address_3`,
								`state`,
								`postcode`,
								`deleted`
							FROM `property`
							WHERE `propertyme_prop_id` = '{$row['propertyme_prop_id']}'
							AND `property_id` != {$property_id}
							ORDER BY `address_2` ASC, `address_3` ASC, `address_1` ASC
							";*/
							$isConnectedCheck_str = "
							SELECT
								p.`property_id`,
								p.`address_1`,
								p.`address_2`,
								p.`address_3`,
								p.`state`,
								p.`postcode`,
								p.`deleted`
							FROM `property` as p
							LEFT JOIN `api_property_data` AS apd ON p.`property_id` = apd.`crm_prop_id`
							WHERE apd.`api_prop_id` = '{$row['api_prop_id']}'
							AND apd.api = 1
							AND p.`property_id` != {$property_id}
							ORDER BY p.`address_2` ASC, p.`address_3` ASC, p.`address_1` ASC
							";
							$connected_prop_sql = mysql_query($isConnectedCheck_str);
							if( mysql_num_rows($connected_prop_sql) ){
							?>
								<div style="float:left;text-align:left;">This ID already in use:
									<ul>
										<?php
										while( $connected_prop_row = mysql_fetch_array($connected_prop_sql) ){
										$connected_prop_full_add  = "{$connected_prop_row['address_1']} {$connected_prop_row['address_2']}, {$connected_prop_row['address_3']} {$connected_prop_row['state']}, {$connected_prop_row['postcode']}";
										?>
											<li><a href="/view_property_details.php?id=<?php echo $connected_prop_row['property_id'] ?>"><?php echo $connected_prop_full_add; ?></a></li>
										<?php
										}
										?>

									<ul>
								</div>
								<div style="clear:both;"></div>
							<?php
							}

						}
						?>
						 <!-- <label for='pm_prop_id' class='more_tenant_label'>PropertyME Property ID</label> -->
						 <label for='pm_prop_id' class='more_tenant_label'>API's ID</label>
						 <?php 
							/* ##not used anymore > direct use api_prop_id from new table instead
							if(!empty($row['propertyme_prop_id'])){
								$api_prop_id = $row['propertyme_prop_id'];
							}
							else if(!empty($row['palace_prop_id'])){
								$api_prop_id = $row['palace_prop_id'];
							}
							else if(!empty($row['api_prop_id'])){
								$api_prop_id = $row['api_prop_id'];
							}else if(!empty($pt_prop_id)){ // property tree
								$api_prop_id = $pt_prop_id;
							}
							else{
								//echo "TEST";
								$api_prop_id = "";
							}*/
							
						 ?>
						 <!--<input id='pm_prop_id' style='width: 250px; margin-bottom: 7px; float: left;' class='pm_prop_id addinput vw-pro-dtl-tnt' type='text' name='pm_prop_id' value='<?php //echo empty($row['propertyme_prop_id']) ? $row['palace_prop_id'] : $row['propertyme_prop_id'] ?>' /> -->
						  <?php 
						 if( $console_prop_id > 0 ){ // console

							$api_prop_id = $console_prop_id;

						 }else{ // other API

							$api_prop_id = $row['api_prop_id'];

						 } 
						 ?>
						 <input id='pm_prop_id' style='width: 250px; margin-bottom: 7px; float: left;' class='pm_prop_id addinput vw-pro-dtl-tnt' type='text' name='pm_prop_id' value='<?php echo $api_prop_id; ?>' />
						 <?php
						 	/* Disabled > use new api from generic table instead
							 if (!empty($row['propertyme_prop_id'])) {
						 		$api_used = 1;
						 	}else if (!empty($row['palace_prop_id'])) {
						 		$api_used = 4;
						 	}else if (!empty($row['api'])) {
								$api_used = 6;
							}else {
						 		$api_used = 0;
						 	}
							 */

							## New generic table
							if( $row['api']!="" ){
								$api_used = $row['api'];
							}else{
								$api_used = 0;
							}
							
						 ?>
						 <button type="button" id="remove_pm_prop_id_btn" class="submitbtnImg" style="float: left;">
							<img class="inner_icon" src="images/button_icons/cancel-button.png">
							Remove
						 </button>
						</div>

						<div style="clear:both;"></div>

						<?php

						if( $_SESSION['country_default'] == 1 ){ // AU
							$fg_compass_housing = 39;
						}else if( $_SESSION['country_default'] == 2 ){ // NZ
							$fg_compass_housing = null;
						}

						?>


						<div class='row' style="float:none; <?php echo (  $franchise_group == $fg_compass_housing || ( $agency_id == 1598 && $_SESSION['country_default'] == 1 ) )?null:'display:none;'; ?>">
						<label for='compass_index_num' class='more_tenant_label'><?php echo (  $franchise_group == $fg_compass_housing )?'Compass Index Number':' Property Code'; ?></label>
						 <input id='compass_index_num' style='width: 250px; margin-bottom: 7px; float: left;' class='compass_index_num addinput vw-pro-dtl-tnt' type='text' name='compass_index_num' value='<?php echo $row['compass_index_num'] ?>' />
						</div>

						<div style="clear:both;"></div>


						<?php
						// PMe
						//if( $row['propertyme_prop_id'] != '' && $connected_to_pme == true && $agency_id > 0 ){
						if( $row['api_prop_id'] != '' && $connected_to_pme == true && $agency_id > 0 ){

							$pme_key_number = null;

							// get pme property pm
							/*$pme_get_pm_params = array(
								'prop_id' =>  $row['propertyme_prop_id'],
								'agency_id' => $agency_id
							);*/

							$pme_get_pm_params = array(
								'prop_id' =>  $row['api_prop_id'],
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
						<div class='row' style="float:none;">
						 <label for='key_number' class='more_tenant_label'>Key Number</label>
						 <input id='key_number' style='width: 80px; margin-bottom: 7px;' class='key_number addinput vw-pro-dtl-tnt' type='text' name='key_number' value='<?php echo $row['key_number'] ?>'>
						 <label class="pme_key_number"><?php echo $pme_key_number; ?></label>

						 <?php if($pme_get_pm_params_dec->KeyNumber != ""){ ?>
							<input type="hidden" id="pme_key_number_field" value="<?php echo $pme_get_pm_params_dec->KeyNumber ?>">
							<button type="button" class="btn_update_key_number submitbtnImg">Update</button>
						 <?php } ?>

						</div>

						<div style="clear:both;"></div>

						<div class='row' style="float:none;">
						 	<label for='key_number' class='more_tenant_label'>Agency Keys</label>
						 	<select name="keys_agency" id="keys_agency" style="width: 262px; margin-bottom: 7px; float: left; margin-left: 0;">
								<option value="">-- Select --</option>
								<?php
								// First Natioanl Agency
								$fn_agency_arr = $crm->get_fn_agencies();
								$fn_agency_main = $fn_agency_arr['fn_agency_main'];
								$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
								$fn_agency_sub_imp = implode(",",$fn_agency_sub);

								// Vision Real Estate
								$vision_agency_arr = $crm->get_vision_agencies();
								$vision_agency_main = $vision_agency_arr['vision_agency_main'];
								$vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
								$vision_agency_sub_imp = implode(",",$vision_agency_sub);

								$add_keys_params = array(
									'distinct' => 1,
									'distinct_val' => 'a.`agency_id`',
									'job_rows_only' => 1
								);
								$agency_sql = getTechRunRows($tr_id,$_SESSION['country_default'],$add_keys_params);
								?>
								<?php
										// First National added list
										if( $agency_id == $fn_agency_main ){
											$fn_agency_sub_sql_str = "
												SELECT `agency_id`, `agency_name`
												FROM `agency`
												WHERE `agency_id` IN({$fn_agency_sub_imp})
											";
											$fn_agency_sub_sql = mysql_query($fn_agency_sub_sql_str);
											while( $fn_agency_sub_row = mysql_fetch_array($fn_agency_sub_sql) ){
											?>
												<option value="<?php echo $fn_agency_sub_row['agency_id'];  ?>"><?php echo $fn_agency_sub_row['agency_name'];  ?></option>
											<?php
											}
										}
										// // Vision Real Estate added list
										if($agency_id == $vision_agency_main ){
											echo $vision_agency_sub_sql_str = "
												SELECT `agency_id`, `agency_name`
												FROM `agency`
												WHERE `agency_id` IN({$vision_agency_sub_imp})
											";
											$vision_agency_sub_sql = mysql_query($vision_agency_sub_sql_str);
											while( $vision_agency_sub_row = mysql_fetch_array($vision_agency_sub_sql) ){
											?>
												<option value="<?php echo $vision_agency_sub_row['agency_id'];  ?>"><?php echo $vision_agency_sub_row['agency_name'];  ?></option>
											<?php
											}
										}

										// Agency Address
										$agency_add_sql_str = "
										SELECT 
											`address_1`, 
											`address_2`, 
											`address_3`,
											`state`,
											`postcode`
										FROM `agency`
										WHERE `agency_id`={$agency_id}";
										$agency_add_sql = mysql_query($agency_add_sql_str);
										
										if( mysql_num_rows($agency_add_sql) > 0 ){																								
											while( $agency_add_row = mysql_fetch_object($agency_add_sql) ){
												$address_1 = $agency_add_row->address_1;
												$address_2 = $agency_add_row->address_2;
												$address_3 = $agency_add_row->address_3;
												$state = $agency_add_row->state;
												$postcode = $agency_add_row->postcode;
												$check_address_str = "SELECT `id` FROM `agency_addresses` WHERE `agency_id`={$agency_id}'";
												$check_address_sql = mysql_query($check_address_str);
												if( mysql_num_rows($check_address_sql) == 0 ){
													$agency_add_row_add_comb = "{$address_1} {$address_2}, {$address_3}"; 
													echo "<option value='$agency_id'>Default {$agency_name} $key_add_num {$agency_add_row_add_comb}</option>";
												}
											}
										}

										$key_add_num = 1;
										// display key address for agency that has it
										$agency_add_sql_str = "
										SELECT 
											`id` AS agen_add_id,
											`address_1` AS agen_add_street_num, 
											`address_2` AS agen_add_street_name, 
											`address_3` AS agen_add_suburb 	
										FROM `agency_addresses`
										WHERE `agency_id`={$agency_id}
										AND `type` = 2
										";
										$agency_add_sql = mysql_query($agency_add_sql_str);
										if( mysql_num_rows($agency_add_sql) > 0 ){																							
											while( $agency_add_row = mysql_fetch_object($agency_add_sql) ){
												$agen_add_comb = "{$agency_add_row->agen_add_street_num} {$agency_add_row->agen_add_street_name}, {$agency_add_row->agen_add_suburb}"; 

												$check_address_sql = mysql_query("SELECT `id` FROM `property_keys` WHERE `property_id`='{$property_id}' AND `agency_addresses_id`='{$agency_add_row->agen_add_id}'");
												$is_selected = (mysql_num_rows($check_address_sql) > 0 ? 'selected': '');
												echo "<option value='$agency_id' data-agency_addresses_id='$agency_add_row->agen_add_id' $is_selected>$agency_name Key #$key_add_num $agen_add_comb</option>";
												$key_add_num ++;
											}
										}
									//}
								//}
								?>
							</select>
						</div>
						<div style="clear:both;"></div>

						<div class='row' style="float:none;">
						 <label for='key_number' class='more_tenant_label'>Alarm Code</label>
						 <input id='alarm_code' style='width: 80px; margin-bottom: 7px;' class='alarm_code addinput vw-pro-dtl-tnt' type='text' name='alarm_code' value='<?php echo $row['alarm_code'] ?>'>
						</div>

						<div style="clear:both;"></div>

						<div class='row' style="float:none;">
						 <label for='lockbox_code' class='more_tenant_label'>Lockbox Code</label>
						 <input id='lockbox_code' style='width: 80px; margin-bottom: 7px;' class='lockbox_code addinput vw-pro-dtl-tnt' type='text' name='lockbox_code' value="<?php echo $lockbox_sql_row->code ?>" />
						</div>

						<div style="clear:both;"></div>


						<div class='row' style='float:none;'>
						 <label class="more_tenant_label">Office to call tenant only</label>
						 <input id='bne_to_call' class='md_chk bne_to_call addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='bne_to_call' <?php echo (($row['bne_to_call']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>


						<div class='row' style="float:none;">
						 <label for='holiday_rental' class='more_tenant_label'>Short Term Rental</label>
						 <input id='holiday_rental' class='md_chk holiday_rental addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='holiday_rental' <?php echo (($row['holiday_rental']==1)?'checked="checked"':''); ?> value='1' />
						<label class="md_chk_lbl md_chk_lbl_green" style="<?php echo ($row['holiday_rental']==1)?'display:block; color: green;':'display:none'; ?>">This property is a Short Term Rental</label>
						</div>

						<?php
						// only show on NSW
						if( $row['state'] == 'NSW' ){ ?>

							<div style="clear:both;"></div>

							<div class='row' style='float:none;'>
								<label for='service_garage'  class='more_tenant_label'>Attached garage requires alarm</label>
								<input id='service_garage' class='md_chk service_garage addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='service_garage' <?php echo (($row['service_garage']==1)?'checked="checked"':''); ?> value='1' />
							</div>

						<?php
						}
						?>

						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						 <label for='no_keys'  class='more_tenant_label'>No Keys at Agency</label>
						 <input id='no_keys' class='md_chk no_keys addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='no_keys' <?php echo (($row['no_keys']==1)?'checked="checked"':''); ?> value='1' />
						<label class="md_chk_lbl" style="<?php echo ($row['no_keys']==1)?'display:block':'display:none'; ?>">There are NO keys at the Agency</label>
						</div>

						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						 <label for='no_en' class='more_tenant_label'>No Entry Notice Allowed</label>
						 <input id='no_en' class='md_chk no_en addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='no_en' <?php echo (($row['no_en']==1)?'checked="checked"':''); ?> value='1' />
						<label class="md_chk_lbl" style="<?php echo ($row['no_en']==1)?'display:block':'display:none'; ?>">DO NOT Entry Notice</label>
						</div>


						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						 <label for='no_dk' class='more_tenant_label'>No Door Knock Allowed</label>
						 <input id='no_dk' class='md_chk no_dk addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='no_dk' <?php echo (($row['no_dk']==1)?'checked="checked"':''); ?> value='1' />
						<label class="md_chk_lbl" style="<?php echo ($row['no_dk']==1)?'display:block':'display:none'; ?>">DO NOT Door Knock</label>
						</div>


						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						 <label for='nlm_display' class='more_tenant_label nlm_lbl'><?php echo ($row['nlm_display']==1)?'<strong style="color:red;">Verify Payment</strong>':'Payment is Verified'; ?></label>
						 <input id='nlm_display' class='md_chk nlm_display addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='nlm_display' <?php echo (($row['nlm_display']==1)?'checked="checked"':''); ?> value='1' />
						</div>


						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						<label for='is_sales'  class='more_tenant_label'>Sales Property</label>
						 <input id='is_sales' class='md_chk is_sales addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='is_sales' <?php echo (($row['is_sales']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>


						<div class='row' style='float:none;'>
						<label for='requires_ppe'  class='more_tenant_label'>Requires PPE to enter</label>
						<input id='requires_ppe' class='md_chk requires_ppe addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='requires_ppe' <?php echo (($row['requires_ppe']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>


						<div class='row' style='float:none;'>
						<label for='send_to_email_not_api'  class='more_tenant_label'>Send invoice to email instead of API</label>
						 <input id='send_to_email_not_api' class='md_chk send_to_email_not_api addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='send_to_email_not_api' <?php echo (($row['send_to_email_not_api']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>



						<div class='row' style='float:none; text-align: left;'>
						 <label for='nlm_display' class='more_tenant_label nlm_lbl'>Property Manager</label>

						<!--//new dropdown (gherx)-->

						<?php
							$pm_sql = get_all_property_pm($agency_id_row);

							$pm_sql2 = getPropertyPM($row['pm_id_new']);
							$pm_sel_query = mysql_fetch_array($pm_sql2);
						?>
							<select name="pm_id_new" style="float:left;">
								<option value="">Please Select</option>
								<?php
								while( $pm_row = mysql_fetch_array($pm_sql) ){
									$pm_sel = ($pm_row['agency_user_account_id']==$pm_sel_query['agency_user_account_id'])? 'selected="true"' : NULL;
								?>
									<option <?php echo $pm_sel; ?> value="<?php echo $pm_row['agency_user_account_id'] ?>"><?php echo "{$pm_row['fname']} {$pm_row['lname']}" ?></option>
								<?php
								}
								?>
							</select>

						<!-- //new dropdown (gherx) end -->

						<?php

						// PMe
						//if( $row['propertyme_prop_id'] != '' &&  $connected_to_pme == true && $agency_id > 0 ){
						if( $row['api_prop_id'] != '' &&  $connected_to_pme == true && $agency_id > 0 ){

							// get pme property pm
							$pme_get_pm_params = array(
								//'prop_id' =>  $row['propertyme_prop_id'],
								'prop_id' =>  $row['api_prop_id'],
								'agency_id' => $agency_id
							);

							$pme_get_pm_params_json = $agency_api->get_pme_prop_pm($pme_get_pm_params);
							$pme_get_pm_params_dec = json_decode($pme_get_pm_params_json);
							//print_r($pme_get_pm_params_dec);

							$pme_prop_pm =  "{$pme_get_pm_params_dec->FirstName} {$pme_get_pm_params_dec->LastName} (PMe)";

						}

						//PALACE PM
						if( $row['api_prop_id'] != '' &&  $connected_to_palace == true && $agency_id > 0 ){

							$palace_pm_params = array(
								'prop_id' =>  $row['api_prop_id'],
								'agency_id' => $agency_id
							);
							$palace_pm_q = $agency_api->get_property_palace($palace_pm_params);
							$palace_get_pm_params_dec = json_decode($palace_pm_q);

							$pme_prop_pm =  "{$palace_get_pm_params_dec->PropertyAgentFullName} (Palace)";

						}

					
						//PropertyTree PM
						if( $row['api_prop_id'] != '' &&  $connected_to_propertytree == true && $agency_id > 0 ){

							$propTree_pm_params = array(
								'property_id' => $property_id
							);
			
							$propTree_q = $agency_api->get_property_tree_property($propTree_pm_params);
							$propTree_q_json_decoded_response = $curl_ret_arr['json_decoded_response'];
							$prop_tree_http_status_code = $curl_ret_arr['http_status_code'];

							if( $prop_tree_http_status_code == 200 ){ // OK
								$api_prop_obj = $propTree_q_json_decoded_response[0];
								//echo "<pre>";
								//var_dump($api_prop_obj->agents);
								foreach( $api_prop_obj->agents as $index=>$value ){
									if($index==0){
										$agent_params = array(
											'agent_id' => $value,
											'property_id' => $property_id
										);
										$agenty_req_obj = $agency_api->get_property_tree_agent_by_id($agent_params);

										if( $agenty_req_obj['http_status_code'] == 200 ){
											$agenty_req_obj_res = $agenty_req_obj['json_decoded_response'];
											$pme_prop_pm =  "{$agenty_req_obj_res->first_name} {$agenty_req_obj_res->last_name} (PropertyTree)";
										}
										
									}
								}
								
							}
							
						}
				
						?>

						


						<label class="pme_prop_pm"><?php echo $pme_prop_pm; ?></label>

						</div>

						<div style="clear:both;"></div>

						<div class='row' style='float:none; margin-top: 8px; <?php echo ( $_SESSION['country_default'] == 2 )?'display:none':null; ?>'>
						 <label class='more_tenant_label'>Property Upgraded <span class="colorItRed">(QLD only)</span></label>
						 <select name="prop_upgraded_to_ic_sa" style="float: left;">
							<option value="">--- Select ---</option>
							<option value="1" <?php echo ( $row['prop_upgraded_to_ic_sa'] == 1 )?'selected="selected"':''; ?>>Yes</option>
							<option value="0" <?php echo ( is_numeric($row['prop_upgraded_to_ic_sa']) && $row['prop_upgraded_to_ic_sa'] == 0 )?'selected="selected"':''; ?>>No</option>
						 </select>
						</div>

						<div style="clear:both;"></div>


						<div class='row' style='float:none;'>
						<label for='manual_renewal'  class='more_tenant_label'>Manual Renewal</label>
						<input id='manual_renewal' class='md_chk manual_renewal addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='manual_renewal' <?php echo (($row['manual_renewal']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>

						<div class='row' style='float:none;'>
						<label for='subscription_billed'  class='more_tenant_label'>Subscription Billed</label>
						<input id='subscription_billed' class='md_chk subscription_billed addinput vw-pro-dtl-tnt' style='width:auto;' type='checkbox' name='subscription_billed' <?php echo (($row['subscription_billed']==1)?'checked="checked"':''); ?> value='1' />
						</div>

						<div style="clear:both;"></div>

						<div class='row'>
							<label class='more_tenant_label'>Property Price Variation</label>
							<select name="agency_price_variation" style="float: left; margin-right: 8px;">
								<option value="">--- Select ---</option>	
								<?php
								// get property variation								
								$pv_sql = mysql_query("
								SELECT `agency_price_variation`
								FROM `property_variation`
								WHERE `property_id` = {$property_id}                    
								AND `active` = 1
								");
								$pv_row = mysql_fetch_object($pv_sql);

								// agency price variation
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
								WHERE apv.`agency_id` = {$agency_id}                    
								AND apv.`active` = 1
								AND apv.`scope` = 1
								AND (
									apv.`expiry` >= '".date('Y-m-d')."'
									OR apv.`expiry` IS NULL
								)
								ORDER BY 
									apv.`type` ASC, 
									apv.`scope` ASC,
									apvr.`reason` ASC
								");                        
								while( $apv_row = mysql_fetch_object($apv_sql) ){ ?>
									<option value="<?php echo $apv_row->id; ?>" <?php echo ( $pv_row->agency_price_variation == $apv_row->id )?'selected':null; ?>>
										$<?php echo number_format($apv_row->amount, 2); ?> 
										(<?php echo ( $apv_row->type == 1 )?'Discount':'Surcharge';  ?> - <?php echo $apv_row->apvr_reason; ?>)
									</option>
								<?php
								}
								?>						
							</select>

							<?php
							if( mysql_num_rows($pv_sql) > 0 ){ ?>
								<button class="submitbtnImg colorwhite" id="remove_property_variation_btn" type="button">Remove</button>
							<?php
							}
							?>														
						</div>

						<div style="clear:both;"></div>

						<div class='row' style='float:none; margin-top: 8px;'>
							<label class='more_tenant_label'>Source of Property</span></label>
							<select name="from_other_company" style="float: left;">
								<option value="">--- Select ---</option>
								<?php
								$sa_comp_sql = mysql_query("
								SELECT `sac_id`, `company_name`
								FROM `smoke_alarms_company`
								WHERE `active` = 1
								");

								// selected
								$pfoc_sql = mysql_query("
								SELECT `company_id`
								FROM `properties_from_other_company`
								WHERE `property_id` = {$property_id}
								AND `active` = 1
								");
								$pfoc_row = mysql_fetch_object($pfoc_sql);
								while( $sa_comp_row = mysql_fetch_object($sa_comp_sql) ){ ?>
									<option 
										value="<?php echo $sa_comp_row->sac_id; ?>" 
										<?php echo ( $sa_comp_row->sac_id == $pfoc_row->company_id )?'selected':null; ?>
									>
										<?php echo $sa_comp_row->company_name; ?>
									</option>
								<?php
								}
								?>															
							</select>
						</div>

						<div style="clear:both;"></div>

						<div class='vw-pro-dtl-tn-hld clear' style='float: left; margin-top: 29px;'>
							<button class="submitbtnImg colorwhite btn_update_prop" id="more_details_btn" type="button">Update</button>
							<div style="clear:both;"></div>
						</div>


					</div>


					<div class='vw-pro-dtl-tn-hld' style='float:right;'>
						<?php
							if($row['deleted'] || $row['agency_deleted'] || $row['is_nlm']==1){ ?>
								<button type='button' id='restoreProb_btn' class='submitbtnImg'>Restore this Property</button>
							<?php
							}else{
							?>

								<!-- <button type='button' id='deact_prop' class='submitbtnImg'>Deactivate Property</button> -->

							<?php
							}

							// Can delete property?
							$staff_perm_sql_str = "
							SELECT COUNT(`id`) AS sp_count
							FROM `staff_permissions`
							WHERE `staff_id` = {$_SESSION['USER_DETAILS']['StaffID']}
							AND `has_permission_on` = 2
							";
							$staff_perm_sql = mysql_query($staff_perm_sql_str);
							$staff_perm_row = mysql_fetch_object($staff_perm_sql);
							$can_delete_prop = ( $staff_perm_row->sp_count > 0 )?true:false;

							if( $can_delete_prop == true ){
							?>
								<select name="delete_reason" id="delete_reason">
									<option value="">-- Select Reason --</option>
									<option value="Duplicate Property">Duplicate Property</option>
									<option value="Other">Other</option>
								</select>
								<input type="hidden" name="redirect" id="redirect" value="<?php echo $_GET['r'] ?>" />
								<button type='button' id='btn_delete_permanently' class='submitbtnImg'>DELETE</button>
							<?php
							}
							?>


					</div>



				</div>
			</div>


			<!-- SERVICES -->
			<div class="c-tab" data-tab_cont_name="services">
				<div class="c-tab__content">

					<table style='margin-bottom: 10px;' border=0 cellspacing=1 cellpadding=5 width=100% class="table-center tbl-fr-red view-property-table-inner" id="tbl_services">

					<?php

					echo "<tr style='color:black' class='border-none align-left'>";
					echo "<td class='bold'>Services</td>";
					echo "<td class='bold'>Prices</td>";
					echo "<td class='bold'>Service Status</td>";
					echo "<td class='bold'>Change Service</td>";
					echo "<td class='bold' style='width: 15%;'>Create Job</td>";
					echo "</tr>";

					?>

					<?php
					$ps_sql = mysql_query("
						SELECT *
						FROM `agency_services` AS ps
						LEFT JOIN `alarm_job_type` AS ajt ON ps.`service_id` = ajt.`id`
						WHERE ps.`agency_id` = {$agency_id}
						AND ajt.`active` = 1
						ORDER BY `agency_services_id` ASC
					");

					$piea_sql = mysql_query("
					SELECT *
					FROM `price_increase_excluded_agency`
					WHERE `agency_id` = {$agency_id}                  
					AND (
						`exclude_until` >= '".date('Y-m-d')."' OR
						`exclude_until` IS NULL
					)
					");  

					$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;

					// check if it has service type serviced to SATS
					$service_to_sats_sql_str = "
					SELECT ps.`alarm_job_type_id`, ps.`service`
					FROM `property` AS p 
					INNER JOIN `property_services` AS ps ON p.`property_id` = ps.`property_id`
					LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
					INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND ps.`alarm_job_type_id` = agen_serv.`service_id` )
					WHERE p.`property_id` = 92
					AND ps.`service` = 1
					";
					$service_to_sats_sql = mysql_query($service_to_sats_sql_str);
					?>

						<?php
						$i = 0;
						$has_service_to_sats = false;
						if(mysql_num_rows($ps_sql)>0){
							while($ps = mysql_fetch_array($ps_sql)){

									if( $is_price_increase_excluded == 1 ){ // orig price
										$agency_price = $ps['price'];
									} else {
										$price_var_params = array(
											'service_type' => $ps['id'],
											'agency_id' => $agency_id
										);
										$price_var_arr = $crm->get_agency_price_variation($price_var_params);
										$agency_price = $price_var_arr['price_breakdown_text']; // agency service price 
									}
									$show_service_type_row = false;

									$pp_sql = mysql_query("
										SELECT *
										FROM `property_services` AS ps
										LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
										WHERE ps.`alarm_job_type_id` = {$ps['id']}
										AND ps.`property_id` = {$id}
										AND ajt.`active` = 1
									");

									if(mysql_num_rows($pp_sql)>0){
										$pp = mysql_fetch_array($pp_sql);
										$property_services_id = $pp['property_services_id'];
										$alarm_job_type_id = $pp['alarm_job_type_id'];
										$serv = $pp['service'];
										$price = $pp['price'];
									}else{
										$property_services_id = '';
										$alarm_job_type_id = $ps['service_id'];
										$serv = "";
										$price = $ps['price'];
									}

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
									if( $is_price_increase_excluded == 1 ){ 
										$job_price = $price;
									} else {
										$price_var_params = array(
											'service_type' => $alarm_job_type_id,
											'property_id' => $property_id
										);
										$price_var_arr = $crm->get_property_price_variation($price_var_params);
										$job_price = $price_var_arr['dynamic_price_total']; // job price
									}

									// DIY(0) or Other provider(3), only if no service type serviced to SATS
									//Gherx: new added condition > show NR if price has changed when adding prop
									if( ( ( is_numeric($serv) && $serv == 0 ) || $serv == 3 || ($serv==2 && $pp['price']!=$ps['price']) ) && mysql_num_rows($service_to_sats_sql) == 0 ){
										$show_service_type_row = true;
									}else if(  $serv == 1 ){ // service to SATS
										$show_service_type_row = true;
										$has_service_to_sats = true;
									}

									if( $show_service_type_row == true ){										

										?>
										<tr  class="border-none align-left">
										<td style="display:none;">
											<input type="hidden" name="property_services_id[]" class="property_services_id" value="<?php echo $property_services_id; ?>">
											<input type="hidden" name="alarm_job_type_id[]" class="alarm_job_type_id" value="<?php echo $alarm_job_type_id; ?>">
											<input type="hidden" name="agency_price[]" class="agency_price" value="<?php echo $agency_price; ?>">
											<input type="hidden" name="price[]" class="price" value="<?php echo $job_price; ?>">
											<input type="hidden" name="service_name[]" class="service_name" value="<?php echo $ps['type']; ?>">
											<input type="hidden" name="is_updated[]" class="is_updated" value="0" />
											<input type="hidden" name="service_status[]" class="service_status" value="<?php echo $serv; ?>" />
											<?php
											if($alarm_job_type_id==2 && $serv==1){ ?>
												<input type="hidden" id="hid_smoke_price" value="<?php echo $price; ?>" />
											<?php
											}
											?>

										</td>
										<td>
											<?php echo $ps['type']; ?>
										</td>
										<td>
											<?php
											if( $is_price_increase_excluded == 1 ){ // orig price ?>
												<a href="javascript:void(0);" class="price_lbl">$<?php echo $price; ?></a>
											<?php 
											}else{ // new price, price variation
			
												$price_var_params = array(
													'service_type' => $alarm_job_type_id,
													'property_id' => $property_id
												);
												$price_var_arr = $crm->get_property_price_variation($price_var_params);
												echo "<div id='price_breakdown_text_div'>{$price_var_arr['price_breakdown_text']}</div>";
			
											}    
											?>											
											<div class="change_price_div" style="display:none;">

												<table style="margin: 10px 0;" id="vpdlatest">
													<tbody>
														<tr style="border: none;">
															<td>Price:</td>
															<td>
																<span class="fllefdl">$</span><input type="text" style="display: inline-block;float: none;" class="tenantinput addinput price_field" value="<?php echo $price; ?>" />
															</td>
														</tr>
														<tr style="border: none;">
															<td>Reason:</td>
															<td>
																<select name="price_reason[]" class="addinput price_reason">
																	<option value=""></option>
																	<option value="FOC">FOC</option>
																	<option value="Price match">Price match</option>
																	<option value="Multiple properties">Multiple properties</option>
																	<option value="Agents Property">Agents Property</option>
																	<option value="Other">Other</option>
																</select>
															</td>
														</tr>
														<tr style="border: none !important;">
															<td>Details:</td>
															<td>
																<input type="text" name="price_details[]" class="proptenantinput tenantinput addinput price_details">
															</td>
														</tr>
													</tbody>
												</table>
												<button type="button" class="blue-btn submitbtnImg colorwhite btn_update_price">Update Price</button>
											</div>
										</td>

										
										<td style='width: 20%;'>											
											<input type="radio" style="display:none;" value="1" class="serv_sats" name="service<?php echo $i; ?>" <?php echo ($serv==1)?'checked="checked"':''; ?>> <span style="color:<?php echo ($serv==1)?'black':'#cccccc'; ?>; display:none;">SATS</span>											
											<input type="radio" value="0" class="serv_sats" name="service<?php echo $i; ?>" <?php echo ( is_numeric($serv) && $serv==0 )?'checked="checked"':''; ?>> <span style="color:<?php echo (is_numeric($serv) && $serv==0)?'black':'#cccccc'; ?>">DIY</span>
											<input type="radio" style="display:none;" value="2" class="serv_sats" name="service<?php echo $i; ?>" <?php echo ($serv==2||$serv=="")?'checked="checked"':''; ?>> <span style="color:<?php echo ($serv==2||$serv=="")?'black':'#cccccc'; ?>; display: none;">No Response</span>
											<input type="radio" value="3" class="serv_sats" name="service<?php echo $i; ?>" <?php echo ($serv==3)?'checked="checked"':''; ?>> <span style="color:<?php echo ($serv==3)?'black':'#cccccc'; ?>">Other Provider</span>
										</td>
										
										<td>

											<button type='button' class='submitbtnImg blue-btn change_service_btn'>Change Service</button>

											<div class="change_service_div">
												
												<input type="hidden" class="from_service_type" value="<?php echo $alarm_job_type_id; ?>">
												<select class="to_service_type">
												<option value="">---</option>
													<?php
													$agen_serv_sql = mysql_query("
													SELECT 
														agen_serv.`price`,

														ajt.`id` AS ajt_id,
														ajt.`type` AS ajt_type
													FROM `agency_services` AS agen_serv
													LEFT JOIN `alarm_job_type` AS ajt ON agen_serv.`service_id` = ajt.`id`
													WHERE agen_serv.`agency_id` = {$agency_id}
													AND agen_serv.`service_id` NOT IN(
														SELECT `alarm_job_type_id`
														FROM `property_services` 
														WHERE `property_id` = {$id}
														AND `service` = 1
													)									
													");

													$piea_sql = mysql_query("
													SELECT *
													FROM `price_increase_excluded_agency`
													WHERE `agency_id` = {$agency_id}                  
													AND (
														`exclude_until` >= '".date('Y-m-d')."' OR
														`exclude_until` IS NULL
													)
													");  

													$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;

													while( $agen_serv_row = mysql_fetch_object($agen_serv_sql)  ){ ?>													
														<option value="<?php echo $agen_serv_row->ajt_id; ?>"><?php echo "{$agen_serv_row->ajt_type} - ";
														if( $is_price_increase_excluded == 1 ){ // orig price 
															echo '$'.$agen_serv_row->price;
														} else {
															$price_var_params = array(
																'service_type' => $agen_serv_row->ajt_id,
																'property_id' => $property_id
															);
															$price_var_arr = $crm->get_property_price_variation($price_var_params);
															echo $price_var_arr['price_breakdown_text']; // agency service price 
														}
														?></option>													
													<?php
													}
													?>
												</select>

												<button type='button' class='submitbtnImg blue-btn change_service_save_btn'>Save</button>
											</div>

										</td>									

										<td>

											<a href="javascript:void(0);" class="create_job" style="display:<?php echo ($serv==1)?'block':'none'; ?>">Create</a>

											<div class="create_job_div" style="display:none;">
												<?php

													/*
													$ls_str = ($row['state']=='QLD')?", 'Lease Renewal'":"";


													// SA, SA(IC) and SASS
													$service_to_show_all_ajt_arr = array(2,12,8);
													if( in_array($alarm_job_type_id, $service_to_show_all_ajt_arr) ){
														$jt_Sql = mysql_query("
															SELECT *
															FROM `job_type`
														");
													}else{
														$jt_Sql = mysql_query("
															SELECT *
															FROM `job_type`
															WHERE `job_type`
															IN (
															'Yearly Maintenance', 'Fix or Replace', 'Change of Tenancy' {$ls_str}
															)
														");
													}
													*/

													// if this property has IC service type and has a completed IC Upgrade jobs, hide IC upgrade
													
													// property service
													$ps_sql2_str = "
													SELECT COUNT(ps.`property_services_id`) AS ps_count
													FROM `property_services` AS ps
													LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
													WHERE ps.`property_id` = {$property_id}
													AND ajt.`is_ic` = 1
													AND ps.`service` = 1
													"; 
													$ps_sql2 = mysql_query($ps_sql2_str);
													$ps_row2 = mysql_fetch_object($ps_sql2);

													// jobs
													$jobs_sql_str2 = "
													SELECT COUNT(`id`) AS j_count
													FROM `jobs`
													WHERE `property_id` = {$property_id}
													AND `job_type` = 'IC Upgrade' 
													AND `status` = 'Completed'
													"; 
													$jobs_sql2 = mysql_query($jobs_sql_str2);
													$jobs_row2 = mysql_fetch_object($jobs_sql2);

													if( $ps_row2->ps_count > 0 && $jobs_row2->j_count > 0 ){

														$jt_Sql_filter = "WHERE `job_type` != 'IC Upgrade'";

													}else{

														// non QLD dont show Lease Renewal and IC Upgrade
														$jt_Sql_filter = null;
														if( $row['state'] != 'QLD' ){ // non QLD

															if( $row['state'] == 'NSW' ){ // NSW
																
																if( $row['holiday_rental'] != 1 ){ // for non short term rental, dont show 'IC Upgrade'
																	$jt_Sql_filter = "WHERE `job_type` != 'IC Upgrade'";
																}													

															}else{ // non NSW

																$jt_Sql_filter = "WHERE NOT `job_type` IN('Lease Renewal','IC Upgrade')";

															}																								

														}
														
													}
																																				

													$jt_Sql = mysql_query("
														SELECT *
														FROM `job_type`
														{$jt_Sql_filter}
														ORDER BY `job_type` ASC
													");

												?>
												<select name="job_type"style="width: 150px;" class="job_type addinput vpr-adev-sel">
													<option value="">Please Select</option>
													<?php while($jt = mysql_fetch_array($jt_Sql)){ ?>
														<option value="<?php echo $jt['job_type']; ?>"><?php echo $jt['job_type']; ?></option>
													<?php
													} ?>
												</select><br />
												<div class="vacant_from clear" style="display:none; padding-top: 10px;">
													Vacant From<br />
													<input type="text" class="datepicker vacant_from_input addinput no-l-m" value=""><br />
												</div>
												<div class="new_ten_start clear" style="display:none; padding-top: 10px;">
													New Tenancy Starts<br />
													<input type="text" class="datepicker new_ten_start_input addinput no-l-m" value=""><br />
												</div>
												<div class="desc_prob clear" style="display:none; padding-top: 10px;">
													Describe Problem<br />
													<input type="text" class="problem_input addinput no-l-m" value="">
												</div>

												<div style="clear:both;"></div>

												<span class="delete_tenant_span" style="display:none;"><input type="checkbox" name="delete_tenant" class="delete_tenant" id="delete_tenant" value="1" />Delete Tenant Details</span><br />
												<span class="vacant_prop_span" style="display:none;"><input type="checkbox" name="vacant_prop" class="vacant_prop" id="vacant_prop" value="1" />Vacant</span><br />

												<textarea rows="5" style="display:none;" name="workorder_notes" class="addtextarea vw-jb-tar workorder_notes" placeholder="workorder notes"></textarea>

												<!-- Work order field (by:gherx) -->
												<input style="margin-bottom:7px;display:none;" type="text" class="work_order" name="work_order" placeholder="Work Order #">
												<br/>
												<!-- Work order field (by:gherx) end -->

												<select name="job_status" class="job_status addinput vpr-adev-sel" style="display:none; width: 150px;">
													<option value='To Be Booked'>To Be Booked</option>
													<option value='Send Letters'>Send Letters</option>
													<option value='On Hold'>On Hold</option>
													<option value='Booked'>Booked</option>
													<option value='Pre Completion'>Pre Completion</option>
													<option value='Merged Certificates'>Merged Certificates</option>
													<option value='Completed'>Completed</option>
													<option value='Pending'>Pending</option>
													<option value='Cancelled'>Cancelled</option>
													<option value='Action Required'>Action Required</option>
													<option value='DHA'>DHA</option>
													<option value='To Be Invoiced'>To Be Invoiced</option>
													<option style='color:red;' value='Escalate'>Escalate **</option>
													<option style='color:red;' value='Allocate'>Allocate **</option>
												</select><br />

												<div class="onhold_date_div" style="display:none;">
													<div class="clear" style="padding-top: 10px;">
														Start Date<br />
														<input type="text" style="width: 140px;" class="datepicker onhold_start_date addinput no-l-m" /><br />
													</div>
													<div class="clear" style="padding-top: 10px;">
														End Date<br />
														<input type="text" style="width: 140px;" class="datepicker onhold_end_date addinput no-l-m" /><br />
													</div>
												</div>

												<div class="jdate_div" style="display:none;">
													<div class="clear" style="padding-top: 10px;">
														Job Date<br />
														<input type="text" style="width: 140px;" class="datepicker job_date addinput no-l-m" value="<?php echo date('d/m/Y'); ?>" /><br />
													</div>
												</div>

												<div class="jtech_div" style="display:none;">
													<div class="clear" style="padding-top: 10px;">
														Technician<br />
														<?php
														$jtech_sql = mysql_query ("
															SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
															FROM `staff_accounts` AS sa
															LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
															WHERE ca.`country_id` ={$_SESSION['country_default']}
															AND sa.`ClassID` = 6
															ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
														");
														?>
														<select id="jtech_sel" class="jtech_sel" style="width: 150px;">
															<option value="">--- Select ---</option>
															<?php
															while( $jtech_row = mysql_fetch_array($jtech_sql) ) { ?>
																<option value="<?php echo $jtech_row['StaffID']; ?>" <?php echo ( $jtech_row['StaffID'] == 1 )?'selected="selected"':''; ?>>
																<?php
																	echo $crm->formatStaffName($jtech_row['FirstName'],$jtech_row['LastName']).
																	( ( $jtech_row['is_electrician'] == 1 )?' [E]':null ).
																	( ( $jtech_row['sa_active'] == 0 )?' (Inactive)':null );
																?>
																</option>
															<?php
															}
															?>
														</select>
													</div>
												</div>

												<div class="preferred_alarm_div">

													<select name="preferred_alarm_id" class="preferred_alarm_id addinput vpr-adev-sel">
														<option value=''>--- Select Alarm Preference ---</option>	
														<?php																						
														$pref_al_sql_str = "
														SELECT al_p.`alarm_pwr_id`, al_p.`alarm_pwr`, al_p.`alarm_make`
														FROM `agency_alarms` AS aa
														LEFT JOIN `alarm_pwr` AS al_p ON aa.`alarm_pwr_id` = al_p.`alarm_pwr_id`
														WHERE aa.`agency_id` = {$agency_id}
														AND aa.`alarm_pwr_id` IN (10,14,22)
														";
														$pref_al_sql = mysql_query($pref_al_sql_str);
														$pref_al_count = mysql_num_rows($pref_al_sql);

														if( $pref_al_count > 0 ){
															while( $pref_al_row = mysql_fetch_array($pref_al_sql) ){

																$alar_pwr_comb = $pref_al_row['alarm_make'];

														?>
															<option value='<?php echo $pref_al_row['alarm_pwr_id']; ?>' <?php echo ( $pref_al_count == 1 )?'selected="selected"':null; ?>><?php echo $alar_pwr_comb; ?></option>	
														<?php
															}
														}
														?>										
													</select>
													<div style="clear:both;"></div>

													<div class="qld_new_leg_alarm_num_div">
													Total Number of alarms required to meet NEW legislation: <?php echo $row['qld_new_leg_alarm_num']; ?>
													</div>

												</div>

												<button type="button" class="btn_create_job submitbtnImg colorwhite" style="display:none; margin-top: 10px;">Create Repair Job</button>
											</div>
										</td>


										<?php
										$querydate = "SELECT MAX(j.created), j.retest_interval FROM jobs j, property p WHERE (j.property_id = p.property_id AND p.property_id = $id) GROUP BY j.property_id";
										$resultdate = mysql_query ($querydate, $connection);

										$retestd_date = null;
										if (mysql_num_rows($resultdate) != 0){
											while($row_date = mysql_fetch_row($resultdate)){
												$retestd_date = $row_date[0];
												$dt = new DateTime($retestd_date);
												$dt->modify('+365 days');
												$retestd_date = $dt->format('d-m-Y');
											}
										}
										?>

										</tr>
										<?php
										$i++;

									}
							}
						}
						?>
						
						<?php
						if( $has_service_to_sats == false ){  ?>
						<tr class="border-none">
							<td colspan="3" class="align-left">No active SATS service.</td>
							<td colspan="2" class="align-left">

								<button type='button' class='submitbtnImg blue-btn' id='add_new_service_btn'>Add New Service</button>
								<div id="add_new_service_type_div">

									<div>
										<select id="new_service_type">
											<option value="">---</option>
											<?php
											$agen_serv_sql = mysql_query("
											SELECT 
												agen_serv.`price`,

												ajt.`id` AS ajt_id,
												ajt.`type` AS ajt_type
											FROM `agency_services` AS agen_serv
											LEFT JOIN `alarm_job_type` AS ajt ON agen_serv.`service_id` = ajt.`id`
											WHERE agen_serv.`agency_id` = {$agency_id}	
											AND agen_serv.`service_id` NOT IN(
												SELECT `alarm_job_type_id`
												FROM `property_services` 
												WHERE `property_id` = {$id}
												AND `service` = 1
											)							
											");

											$piea_sql = mysql_query("
											SELECT *
											FROM `price_increase_excluded_agency`
											WHERE `agency_id` = {$agency_id}                  
											AND (
												`exclude_until` >= '".date('Y-m-d')."' OR
												`exclude_until` IS NULL
											)
											");  

											$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;

											while( $agen_serv_row = mysql_fetch_object($agen_serv_sql)  ){ ?>																									
												<option value="<?php echo $agen_serv_row->ajt_id; ?>"><?php echo "{$agen_serv_row->ajt_type} - ";
												if( $is_price_increase_excluded == 1 ){ // orig price 
													echo '$'.$agen_serv_row->price;
												} else {
													$price_var_params = array(
														'service_type' => $agen_serv_row->ajt_id,
														'property_id' => $property_id
													);
													$price_var_arr = $crm->get_property_price_variation($price_var_params);
													echo $price_var_arr['price_breakdown_text']; // agency service price 
												}
												?></option>													
											<?php
											}
											?>
										</select>
									</div>	
									
									<div>
										<select id="new_service_type_status" style="margin-bottom:5px;">
											<option value="">---</option>
											<option value="1">SATS</option>
											<option value="0">DIY</option>
											<option value="3">OTHER PROVIDER</option>																				
										</select>
									</div>

									<button type='button' class='submitbtnImg blue-btn' id='add_new_service_type_submit_btn'>Submit</button>

								</div>

							</td>
						</tr>							
						<?php
						}
						?>

					</table>




					<div style="clear:both;"></div>




					<table border=0 cellspacing=1 cellpadding=5 width=100% class="table-center tbl-fr-red view-property-table-inner" id="tbl_job_type">

						<?php



						echo "<tr bgcolor=#b4151b  class='border-none align-left'>
						<td class='colorwhite bold '>Job Type</td>
						<td class='colorwhite bold'>Service</td>
						<td class='colorwhite bold'>Price</td>
						<td class='colorwhite bold'>Total Price</td>
						<td class='colorwhite bold'>Date</td>
						<td class='colorwhite bold'>Job Status</td>
						<td class='colorwhite bold' align='center'>Invoice</td>
						<td class='colorwhite bold' align='center'>Certificate</td>
						<td class='colorwhite bold' align='center'>Combined</td>";

						if( $row['state'] == 'QLD' && $ic_upgrade != 1 ){

							echo "
							<td class='colorwhite bold' align='center'>Brooks Quote</td>
							<td class='colorwhite bold' align='center'>".$crm->get_quotes_new_name(22)." Quote</td>
							<td class='colorwhite bold' align='center'>Combined Quote</td>
							";

						}


						echo "</tr>";

						$plog_sql_str = "
							SELECT
								j.`id` AS jid,
								j.`job_type`,
								j.`service` AS jservice,
								j.`date` AS jdate,
								j.`status` AS jstatus,
								j.`assigned_tech`,
								j.`job_price`,
								j.`property_id`,
								j.`del_job`,

								p.`prop_upgraded_to_ic_sa`
							FROM `jobs` AS j
							LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
							WHERE j.`property_id` = {$id}
							AND j.`del_job` = 0
							ORDER BY j.`date` DESC
						";

						$plog_sql = mysql_query($plog_sql_str);
						if ( mysql_num_rows($plog_sql) >0 ){

							$has_ic_upgrade_job =  false;
							while ( $plog_row = mysql_fetch_array($plog_sql) )
							{

								if(

									// condition 1
									( $plog_row['job_type'] == 'IC Upgrade' && $plog_row['jstatus'] == 'Merged Certificates' ) ||

									// condition 2
									(
										$plog_row['prop_upgraded_to_ic_sa'] == 1 && $plog_row['jstatus'] == 'Merged Certificates'  &&
										$plog_row['job_type'] != 'IC Upgrade' && !in_array($plog_row['j_service'], $ic_serv)
									)

								){
									$has_ic_upgrade_job =  true; // show button
									$job_to_upgrade_to_ic_service = $plog_row['jid'];
								}



							echo "<tr class='align-left job_row' ".(($plog_row['assigned_tech']==1)?'style="background-color:#eeeeee;"':'').">";


							echo "
								<td>
									<a href='view_job_details.php?id={$plog_row['jid']}&service={$plog_row['jservice']}'>{$plog_row['job_type']}</a> ({$plog_row['jid']}) ".(($plog_row['assigned_tech']==23)?'(Other Supplier)':'')."
								";

								// empty, OS and UB
								if( $plog_row['assigned_tech'] == 1 || $plog_row['assigned_tech'] == 2 ){
									echo '<img src="/images/no_tech.png" class="j_icons no_tenants_icon" />';
								}

							echo "
								</td>
								<td>";

									// display icons
									$job_icons_params = array(
										'job_id' => $plog_row['jid']
									);
									echo $crm->display_job_icons_v2($job_icons_params);

							echo "</td>";

									$job_id = $plog_row['jid'];
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
									
									$p_n_a_total = ($plog_row['job_price'] + $alarm_tot_price);
									$final_job_price_total = $p_n_a_total;

									// get job variation
									$jv_sql = mysql_query("
									SELECT 
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
									}
								// $tot_job_price = $plog_row['job_price'];

								echo "<td>$".number_format($plog_row['job_price'], 2)."</td>";

								$tot_job_price = getJobAmountGrandTotal($plog_row['jid'],$_SESSION['country_default']);

							echo "<td>$".number_format($final_job_price_total, 2)."</td>
								<td>".( ( $plog_row['jdate']!="" && $plog_row['jdate']!="0000-00-00" )?date("d/m/Y",strtotime($plog_row['jdate'])):'' )."</td>
								<td>{$plog_row['jstatus']}</td>";

								if( $plog_row['jstatus']=='Completed' && $plog_row['assigned_tech']!=1 ){
									$encode_encrypt_id = rawurlencode($encrypt_decrypt->encrypt($plog_row['jid']));
									$pdf_invoice_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_id}"));
									$pdf_certificate_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_certificate/?job_id={$encode_encrypt_id}"));
									$pdf_combine_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_combined/?job_id={$encode_encrypt_id}"));
									//$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}"));

									echo "<td style='vertical-align: middle;' align='center'><a href='$pdf_invoice_ci_link_view' target='_blank'><img src='images/pdf.png' /></a></td>";

									if( $plog_row['assigned_tech']!=1 && $plog_row['assigned_tech']!=2 ){
										echo "<td style='vertical-align: middle;' align='center'><a href='$pdf_certificate_ci_link_view' target='_blank'><img src='images/pdf.png' /></a></td>
										<td style='vertical-align: middle;' align='center'><a href='$pdf_combine_ci_link_view' target='_blank'><img src='images/pdf.png' /></a></td>";
									}else{
										echo "<td>&nbsp;</td><td>&nbsp;</td>";
									}
									


										if( $row['state'] == 'QLD' && $ic_upgrade != 1 ){

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

												// brooks pdf
												$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}&qt=brooks"));

												echo "
												<td style='vertical-align: middle;' align='center'>
													<a href='$pdf_quote_ci_link_view' target='_blank'><img src='images/pdf.png' /></a>
												</td>";

												$has_brooks_quote = true;

											}
										}



										/*if( $row['state'] == 'QLD' && $ic_upgrade != 1 ){

											// check if 240v RF cavius available on agency alarms
											$get_240v_rf_cavius_sql_str = "
											SELECT COUNT(`agency_alarm_id`) AS agen_al_count
											FROM `agency_alarms`
											WHERE `agency_id` = {$agency_id}
											AND `alarm_pwr_id` = 14
											";
											$get_240v_rf_cavius_sql = mysql_query($get_240v_rf_cavius_sql_str);
											$get_240v_rf_cavius_row = mysql_fetch_array($get_240v_rf_cavius_sql);

											if( $get_240v_rf_cavius_row['agen_al_count'] > 0 ){

												// cavius pdf
												$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}&qt=cavius"));

												echo "
												<td style='vertical-align: middle;' align='center'>
													<a href='$pdf_quote_ci_link_view' target='_blank'><img src='images/pdf.png' /></a>
												</td>";

												$has_cavius_quote = true;

											}

										}*/


										if( $row['state'] == 'QLD' && $ic_upgrade != 1 ){

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
												$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}&qt=emerald"));
												
												echo "
												<td style='vertical-align: middle;' align='center'>
													<a href='$pdf_quote_ci_link_view' target='_blank'><img src='images/pdf.png' /></a>
												</td>";

												$has_emerald_quote = true;
			
											} 

										}										


										//if( $has_brooks_quote == true && $has_cavius_quote == true && $has_emerald_quote == true && $ic_upgrade != 1 ){
										if( $has_brooks_quote == true && $has_emerald_quote == true && $ic_upgrade != 1 ){

											// combined
											$pdf_quote_ci_link_view = $crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}&qt=combined"));

											echo "
											<td style='vertical-align: middle;' align='center'>
												<a href='$pdf_quote_ci_link_view' target='_blank'><img src='images/pdf.png' /></a>
											</td>";

										}


								}else{

									echo "<td valign=top colspan='10'></td>";

								}


								echo "</tr>";

							}

						}else{
							echo "<tr class='align-left'><td colspan='100%'>No active jobs</td></tr>";
						}

						?>


						</table>


						<div style="clear:both;"></div>

						<div style='float: right; text-align: left; margin-top: 18px;'>

							<?php

							if( $has_ic_upgrade_job == true ){ ?>

								<div style='float: left; margin-right: 7px;'>
									<button type='button' class='submitbtnImg blue-btn' id='update_to_ic_service_btn'>Upgrade property to IC Service</button>
								</div>

							<?php
							}

							?>

							<div style='float: left; margin-right: 10px;'>
								<button style='margin-right: 9px;' class="submitbtnImg colorwhite btn_update_prop" id="btn_update_prop_service_tab" type="button">Update</button>
								<button class="submitbtnImg colorwhite btn_cancel_update_prop" id="btn_cancel_update_prop" type="button">Cancel</button>
							</div>
							<div style='float: left; margin-right: 7px;'>
								<button type='button' class='submitbtnImg blue-btn' id='btn_create_pending'>+ Service Due Job</button>
							</div>


							<div style='float: left; margin-right: 7px;'>								

								<button type='button' class='submitbtnImg blue-btn' id='show_non_active_services'>Show Non-Active Services</button>

								<div id="non_active_service_div">
								<table id="non_active_service_tbl">
										<tr>
											<th>Service</th>
											<th>Status</th>
										</tr>

										<?php
										$ps_sql = mysql_query("
										SELECT 
											ps.`property_services_id`,
											ps.`price`,
											ps.`service` AS serv_status,

											ajt.`id` AS ajt_id,
											ajt.`type` AS ajt_type
										FROM `property_services` AS ps
										LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
										WHERE ps.`property_id` = {$id}
										AND ps.`service` != 1
										");
										while( $ps_row = mysql_fetch_object($ps_sql)  ){ ?>

											<tr>
												<td><?php echo "{$ps_row->ajt_type} - \$";
												$price_var_params = array(
													'service_type' => $ps_row->ajt_id,
													'property_id' => $id
												);
												$price_var_arr = $crm->get_property_price_variation($price_var_params);
												echo $price_var_arr['dynamic_price_total']; // property service price 
												?></td>
												<td>
													<input type="hidden" class="non_active_ps_id" value="<?php echo $ps_row->property_services_id; ?>" /> 													
													<input type="radio"  class="non_active_service_status non_active_service_<?php echo $ps_row->property_services_id; ?>" name="non_active_service_<?php echo $ps_row->property_services_id; ?>" value="0" <?php echo ( is_numeric($ps_row->serv_status) && $ps_row->serv_status == 0 )?'checked':null; ?> />DIY													
													<input type="radio"  class="non_active_service_status non_active_service_<?php echo $ps_row->property_services_id; ?>" name="non_active_service_<?php echo $ps_row->property_services_id; ?>" value="3" <?php echo ( $ps_row->serv_status == 3 )?'checked':null; ?> />Other Provider
												</td>
											</tr>

										<?php
										}
										?>									
									</table>

									<button type='button' class='submitbtnImg blue-btn' id='non_active_service_update_btn'>Update</button>
								</div>

							</div>

							<div style='float: left; margin-right: 7px;'>
								
								<?php
								if( $has_service_to_sats == true ){ ?>
									<button type='button' class='submitbtnImg blue-btn' id='add_new_service_btn'>Add New Service</button>

									<div id="add_new_service_type_div">

										<div>
											<select id="new_service_type">
												<option value="">---</option>
												<?php
												$agen_serv_sql = mysql_query("
												SELECT 
													agen_serv.`price`,

													ajt.`id` AS ajt_id,
													ajt.`type` AS ajt_type
												FROM `agency_services` AS agen_serv
												LEFT JOIN `alarm_job_type` AS ajt ON agen_serv.`service_id` = ajt.`id`
												WHERE agen_serv.`agency_id` = {$agency_id}	
												AND agen_serv.`service_id` NOT IN(
													SELECT `alarm_job_type_id`
													FROM `property_services` 
													WHERE `property_id` = {$id}
													AND `service` = 1
												)							
												");

												$piea_sql = mysql_query("
												SELECT *
												FROM `price_increase_excluded_agency`
												WHERE `agency_id` = {$agency_id}                  
												AND (
													`exclude_until` >= '".date('Y-m-d')."' OR
													`exclude_until` IS NULL
												)
												");  

												$is_price_increase_excluded = ( mysql_num_rows($piea_sql) > 0 )?1:0;

												while( $agen_serv_row = mysql_fetch_object($agen_serv_sql)  ){ ?>																									
													<option value="<?php echo $agen_serv_row->ajt_id; ?>"><?php echo "{$agen_serv_row->ajt_type} - ";
													if( $is_price_increase_excluded == 1 ){ // orig price 
														echo '$'.$agen_serv_row->price;
													} else {
														$price_var_params = array(
															'service_type' => $agen_serv_row->ajt_id,
															'property_id' => $property_id
														);
														$price_var_arr = $crm->get_property_price_variation($price_var_params);
														echo $price_var_arr['price_breakdown_text']; // agency service price 
													}
													?></option>													
												<?php
												}
												?>
											</select>
										</div>
										
										<div>
											<select id="new_service_type_status" style="margin-bottom:5px;">
												<option value="">---</option>
												<option value="1">SATS</option>
												<option value="0">DIY</option>
												<option value="3">OTHER PROVIDER</option>																				
											</select>
										</div>

										<button type='button' class='submitbtnImg blue-btn' id='add_new_service_type_submit_btn'>Submit</button>

									</div>
								<?php
								}
								?>																	
								
								
							</div>
												

						</div>						


						<div style="float:left;margin-top:15px;" class='align-left'>

							<span><b>Retest date:</b></span> <span><?php echo get_retest_date($property_id); ?></span> || 

							<?php
							// get property subscription.
							$prop_subs_sql = mysql_query("
							SELECT 
								`subscription_date`,
								`source`
							FROM `property_subscription`
							WHERE `property_id` = {$property_id}
							");
							$prop_subs_row = mysql_fetch_object($prop_subs_sql);

							$today = date('Y-m-d');
							$this_year = date("Y");

							$sub_date_month = date("m",strtotime($prop_subs_row->subscription_date));
							$sub_date_day = date("d",strtotime($prop_subs_row->subscription_date));
							$sub_date_year = date("Y",strtotime($prop_subs_row->subscription_date));

							// this year using subscription month and day
							$sub_date_this_year = date('Y-m-d', strtotime("{$this_year}-{$sub_date_month}-{$sub_date_day}"));	

							// if today's date is within the subscription date this year
							if( $today >= date('Y-m-d', strtotime($sub_date_this_year) )  ){ 

								$sub_valid_from = date('Y-m-d', strtotime($sub_date_this_year));
							} else if($today < date('Y-m-d', strtotime($prop_subs_row->subscription_date)) ) {

								$sub_valid_from = date("{$sub_date_year}-{$sub_date_month}-{$sub_date_day}");
							} else { // else get previous year, but using subscript date month and day

								$sub_valid_from = date("Y-{$sub_date_month}-{$sub_date_day}", strtotime("-1 year"));
							}

							// subscription valid to = add 1 year then - 1 day
							$sub_valid_to_temp = date('Y-m-d', strtotime("{$sub_valid_from} +1 year"));
							$sub_valid_to = date('Y-m-d', strtotime("{$sub_valid_to_temp} -1 day"));

							// serviced to SATS
							$ps_sql3 = mysql_query("
							SELECT COUNT(`property_services_id`) AS ps_count
							FROM `property_services`
							WHERE `property_id` = {$property_id}
							AND `service` = 1
							");

							$ps_sql3_row = mysql_fetch_object($ps_sql3);
							?>
							<span>Subscription Start Date:</span> 
							<span>
								<input type="text" class="datepicker" name="subscription_date" id="subscription_date" style="width: 78px;" value="<?php echo ( $crm->isDateNotEmpty($prop_subs_row->subscription_date) == true )?date('d/m/Y',strtotime($prop_subs_row->subscription_date)):null; ?>" />
								<input type="hidden" id="subscription_date_orig" value="<?php echo ( $crm->isDateNotEmpty($prop_subs_row->subscription_date) == true )?date('d/m/Y',strtotime($prop_subs_row->subscription_date)):null; ?>" />
							</span> 
							<span>Source:</span> 
							<span>
								<?php
								$subs_source_sql = mysql_query("
								SELECT *
								FROM `subscription_source`	
								ORDER BY `source_name` ASC	
								");
								?>
								<select name="subscription_source" id="subscription_source">
									<option value="">---</option>
									<?php
									while( $subs_source_row = mysql_fetch_object($subs_source_sql) ){ ?>
										<option value="<?php echo $subs_source_row->id; ?>" <?php echo ( $subs_source_row->id == $prop_subs_row->source )?'selected':null; ?>><?php echo $subs_source_row->source_name; ?></option>
									<?php
									}
									?>									
								</select>
								<input type="hidden" id="subscription_source_orig" value="<?php echo $prop_subs_row->source; ?>" />
							</span> 
							<?php
							if( $prop_subs_row->subscription_date != '' && $ps_sql3_row->ps_count > 0 ){ ?>
								<span>Subscription valid from <?php echo date('d/m/Y',strtotime($sub_valid_from)); ?> to <?php echo date('d/m/Y',strtotime($sub_valid_to)); ?></span>
							<?php
							}
							?>	
							
							<button type="button" class="submitbtnImg" id="fetch_sub_date_btn">Fetch Date</button>

						</div>

				</div>

			</div>


			</form>

			<!-- PROPERTY LOGS -->
			<div class="c-tab" data-tab_cont_name="prop_logs">
				<div class="c-tab__content">

					<table width="100%" class="table-center tbl-fr-red view-property-table-inner">
					<tr class="padding-none border-none">
					<td style="padding: 0px;">
					<form method="POST" name="property_event" id="property_event" action="view_property_details.php?id=<?php echo $id;?>">
					<table width="100%" cellspacing="1" cellpadding="5" border="0" class="table-center tbl-fr-red view-property-table-inner">
						<tr class="padding-none border-none">
							<td>
								<label for="eventdate" class="vpr-adev">Date</label>
								<div style="clear:both;"></div>
								<input TYPE="text" style='width: 77px;' NAME="eventdate" value="<?php echo date("d/m/Y"); ?>" SIZE=12 class="datepicker addinput vpr-adev-in" />
							</td>
							<td>
								<label for="contact_type"  class="vpr-adev">Event</label>
								<div style="clear:both;"></div>
								<select name="contact_type" style='width: 102px;' class="addinput vpr-adev-sel">
									<option value="Phone Call">Phone Call</option>
									<option value="E-mail">E-mail</option>
									<option value="Other">Other</option>
									<option value="Work Order">Work Order</option>
									<option value="Duplicate Property">Duplicate Property</option>
								</select>
							</td>
							<td style='width: 65%;'>
								<label  class="vpr-adev">Details</label>
								<div style="clear:both;"></div>
								<textarea style='width:99%' name="comments" id="comments" lengthcut="true" class="addtextarea vpr-adev-txt"></textarea>
							</td>
							<td>
								<label style='margin-right: 5px;' for="eventdate" class="vpr-adev">Important</label>
								<input type="checkbox" id="important" name="important" style="display: block; margin-top: 8px;" value="1" />
							</td>
							<td>
								<input type="hidden" name="add_event" value="Add Event" />
								<button type="button" id="add_event" class="submitbtnImg vpr-adev-btn">
									Add Event
								</button>
							</td>
						</tr>
					</table>
					</form>
					</td>
					</tr>
					<tr class="padding-none border-none">
					<td class="padding-none border-none">
					<form method="POST" name="property_event_del" action="view_property_details.php?id=<?php echo $id;?>">
						<input type="hidden" name="del_pelid" id="del_pelid" value="" />
					<table width="100%" style='border: 1px solid #cccccc !important;' cellspacing="1" cellpadding="5" border="0" class="vw-pr-lst align-left" id="vpd-de">
						<tr bgcolor="#b4151b" class="padding-none border-none">
							<td class="colorwhite bold">Date</td>
							<td class="colorwhite bold">Time</td>
							<td class="colorwhite bold">Event</td>
							<td class="colorwhite bold">Who</td>
							<td class="colorwhite bold">Details</td>
							<td class="colorwhite bold"></td>
						</tr>
						<?php
							//$Query = "SELECT pl.id, staff_id, s.FirstName, s.LastName, event_type, event_details, DATE_FORMAT(log_date, '%d/%m/%Y'), pl.`property_id` FROM property_event_log pl, staff_accounts s where pl.staff_id = s.StaffID AND (property_id='".$id."')ORDER BY log_date DESC";
								$Query = "
									SELECT *
									FROM property_event_log AS pl
									LEFT JOIN `staff_accounts` AS sa ON pl.`staff_id` = sa.`StaffID`
									WHERE pl.property_id ={$id}
									ORDER BY pl.`id` DESC
								";
							$result = mysql_query ($Query, $connection);

							 if (mysql_num_rows($result) == 0)
								echo "<tr class='padding-none border-none'><td colspan='5' >No property event logs returned.</td></tr>";

							$odd=0;

						   while ($row = mysql_fetch_array($result))
						   {


							if($row['log_agency_id']!=""){
								$agency2_sql = mysql_query("
									SELECT *
									FROM `agency`
									WHERE `agency_id` = {$row['log_agency_id']}
								");
								$agency2 = mysql_fetch_array($agency2_sql);
								$who = $agency2['agency_name'];
							}else if($row['staff_id']!=0){
								$who = $crm->formatStaffName($row['FirstName'],$row['LastName']);
							}else{
								$who = 'Agency';
							}

								$odd++;
								if (is_odd($odd)) {
									echo "<tr ".(($row['important']==1)?'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"':'bgcolor="#FFFFFF"')." class='border-none'>";
								} else {
									echo "<tr ".(($row['important']==1)?'style="background-color:#FFCCCB!important; border: 1px solid #b4151b!important; box-shadow: 0 0 2px #b4151b inset!important;"':'bgcolor="#eeeeee"')." class='border-none b-rg'>";
								}

								$date = ($row['log_date']=='0000-00-00 00:00:00')?'':date('d/m/Y',strtotime($row['log_date']));
								echo '<td>'.$date.'</td>';

								$time = ($row['log_date']=='0000-00-00 00:00:00')?'':date('H:i',strtotime($row['log_date']));
								echo '<td>'.$time.'</td>';

								echo '<td class="even_type_td">'.$row['event_type'].'</td>';
								echo '<td>'.$who.'</td>';
								echo '<td>'.$row['event_details'].'</td>';
								if(
									strpos(strtolower($row['event_type']),"deleted")!==false ||
									strpos(strtolower($row['event_type']),"deactivated")!==false ||
									strpos(strtolower($row['event_type']),"restored")!==false ||
									strpos(strtolower($row['event_type']),"'Price Changed'")!==false ||
									$row['event_type']=='Property Added' ||
									$row['event_type']=='NLM Property' ||
									$row['event_type']=='No Longer Managed' ||
									$row['event_type']=='Property Service updated' ||
									$row['hide_delete'] == 1
								){
									echo '<td>&nbsp;</td>';
								}else{
									echo '<td><a href="javascript: submit_property_event_del('.$row['id'].');" class="btn_delete_logs">Delete</a></td>';
								}
								echo '</tr>';
							}
						?>
					</table>
					</form>
					</td>
					</tr>
				</table>




				<?php
				//if( strpos($_SERVER['SERVER_NAME'],"crmdev") !== false ){

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
						l.`auto_process`,

						ltit.`title_name`,

						aua.`fname`,
						aua.`lname`,
						aua.`photo`,

						sa.`StaffID`,
						sa.`FirstName`,
						sa.`LastName`
					',
					'property_id' => $property_id,
					'display_in_vpd' => 1,
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
					'property_id' => $property_id,
					'display_in_vpd' => 1,
					'deleted' => 0
				);
				$ptotal = mysql_num_rows($crm->getNewLogs($params));

				?>
				<h2 class="heading">New logs</h2>
				<table style="border:1px solid #cccccc !important" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">
					<tr bgcolor="#b4151b">
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

			<!-- css table tweak for new logs -->
			<style type="text/css">
				table.vjc-log tr.border-none td{
					border-bottom: 1px solid #CCCCCC !important;
					border-top: 1px solid #CCCCCC !important;
				}
			</style>


			<!-- PROPERTY FILES -->
			<div class="c-tab" data-tab_cont_name="prop_files">
				<div class="c-tab__content">


					<div class="align-left vpd-fupload">
					<script type="text/javascript">

					$('a.delfile').live('click', function() {

									var d_confirm = confirm("Are you sure you want to delete this file?");
									if(d_confirm) {
										return true;
									}
									else
									{
										return false;
									}

								});

					</script>

					<form action="?id=<?=$id;?>#uploads" enctype="multipart/form-data" method="post">
						<p>
						<input type="file" id="fileupload" name="fileupload" class="submitbtnImg">
						<input type="submit" value="Upload Now" id="btn_upload_now" style="display:none;" class="submitbtnImg">
						</p>
					</form>

					<?php

					# Get Property uploaded Files
					/* COMMENT OUT FOR NOW by Gherx

					$property_files = getPropertyFiles($id);

					if(sizeof($property_files) == 0)
					{
						echo "This Property Has No Uploaded Files. Upload One Below";
					}
					else {
						echo "<ul>";
						foreach($property_files as $file)
						{
							echo "<li><a href='" . UPLOAD_DIR . $id . "/" . $file . "' target='_blank'>" . $file . "</a> - <a href='?id=" . $id . "&delfile=" . rawurlencode($file) . "#uploads' class='delfile'>Delete</a></li>";
						}
						echo "</ul>";
					}*/

					?>

					<!-- GHERX ADDED -->
					<?php

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

					$property_file_query = mysql_query("
					SELECT `property_files_id`,`property_id`,`path`,`filename`
					FROM `property_files`
					WHERE `property_id` = {$id}
					");


					#old property files start
					# Get Property uploaded Files
					$property_files = getPropertyFiles($id);

					if(sizeof($property_files) == 0 && mysql_num_rows($property_file_query)==0)
					{
						echo "This Property Has No Uploaded Files. Upload One Below";
					}
					else {
						echo "<ul>";
						foreach($property_files as $file)
						{
							echo "<li><a href='" . UPLOAD_DIR . $id . "/" . $file . "' target='_blank'>" . $file . "</a> - <a href='?id=" . $id . "&delfile=" . rawurlencode($file) . "#uploads' class='delfile'>Delete</a></li>";
						}
						echo "</ul>";
					}
					#old property files end


					echo "<ul>";
					while ($new_file = mysql_fetch_array($property_file_query))
					{
						$prop_link = "{$crm_ci_link}{$new_file['path']}{$new_file['filename']}";
					?>
						<li><a target="_blank" href="<?php echo $prop_link ?>"><?php echo $new_file['filename'] ?></a> - <a href="#">Delete</a></li>
					<?php
					}
					echo "</ul>";
					?>
					<!-- GHERX ADDED END -->

					</div>

				</div>
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



	<!--
	<div class='float-left'>
	<div class='vw-pro-dtl-tn-hld vpr-left'>
		<style>
		.junderline_colored{
			color: red;
			text-decoration: underline;
		}
		.agency_pass_div{
			display:none;
		}
		.ex-pad {
			margin-top: 10px;
		}
		</style>
		<h2 class='heading'>Agency Verification</h2>
		<div style="margin:9px 0; font-size: 18px; color: black; text-align: left; width:500px;" class="error">
		Before we continue, I just need to verify you for security purposes. <span class='junderline_colored'>NAME</span> what is your agency password?<br /><br />

		<?php
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		$agen_sql = mysql_query("
			SELECT *
			FROM `agency`
			WHERE `agency_id` = {$agency_id}
		");
		$agen = mysql_fetch_array($agen_sql);
		if(UTF8_USED)
		  {
			   $pass = $encrypt->decrypt(utf8_decode($agen['password']));
			}
		  else
		  {
			  $pass = $encrypt->decrypt($agen['password']);
		  }
		?>

		<div style="float: left; margin-right: 10px;">
			<button type="button" id="show_agency_pass" class="submitbtnImg vpr-adev-btn" style="margin: 0;">Show Password</button>
		</div>
		<div class="agency_pass_div" style="margin-top: 4px;">
			<a href="/edit_agency_details.php?id=<?php echo $agency_id; ?>" style="font-style: italic;"><input type="text" readonly="readonly" style="margin-top: 20px;" class="addinput" value="<?php echo $pass; ?>" /></a>
		</div>
		<div style="clear:both;"></div>
		<div class="agency_pass_div">
		<br />Thank you<br /><br />
			If asked why? We have introduced an additional layer of security to protect all parties
		</div>

		</div>
	</div>
	</div>
	-->

	 <?php






echo "</div>";
 // Ends vpd-maindv




?>





</td>
</tr>

</table>


</div>

  </div>


</div>

<br class="clearfloat" />

<div id="dialog-confirm" title="Confirm" style="display:none;">
  <p>Do you want to delete tenant data?</p>
</div>
<style>
.more_tenant_label{
	float: left !important;
	margin-top: 8px !important;
	width: 200px;
}
.vpd-bg-email{
	width: 211px !important;
	margin-right: 0 !important;
}
.md_chk_lbl{
	float: left !important;
	color: red;
	position: relative;
	top: 6px;
}

.md_chk_lbl_green{
	color: green !important;
}
.pme_check_icon,
.pme_x_icon{
	position: relative;
	top: 8px;
	right: 4px;
	width: 27px;
}
.timestamp_style{
	color: #00D1E5;
	font-style: italic;
}
.grey-btn{
	background-color: #dedede;
	border: 1px solid #8c2226;
}
.pme_btn_color{
	background-color: #14cdeb !important;
    border-color: #14cdeb !important;

}
.property_api_tbl tr,
.property_api_tbl td{
	border: 0 !important;
	padding: 0;
}
.property_api_tbl .timestamp_style{
	text-align: center;
}

#all_pme_table{
	/*display:none ;*/
}

.no_tenants_icon{
	position: relative;
	top: 4px;
	left: 5px;
}
.pme_prop_pm,
.pme_key_number{
	display: inline !important;
	position: relative;
	left: 12px;
	top: 7px;
	float: left !important;
}
.preferred_alarm_div{
	display: none;
	padding-top: 20px;
}
.qld_new_leg_alarm_num_div{
	margin-top: 5px;
}
#change_service_table,
#new_service_type,
#non_active_service_tbl{
	width: auto;
	margin: 10px 0px;
}
.change_service_div,
#add_new_service_type_div,
#non_active_service_div,
#btn_cancel_update_prop{
	display: none;
}
.change_service_btn{
	float: left;
	margin-right: 9px;
}
</style>
<script type="text/javascript">

// google map autocomplete
var placeSearch, autocomplete;

// google address prefill
var componentForm2 = {
  route: {
	'type': 'long_name',
	'field': 'address_2'
  },
  locality: {
	'type': 'long_name',
	'field': 'locality'
  },
  sublocality_level_1: {
	'type': 'long_name',
	'field': 'sublocality_level_1'
  },
  administrative_area_level_1: {
	'type': 'short_name',
	'field': 'state'
  },
  postal_code: {
	'type': 'short_name',
	'field': 'postcode'
  }
};

function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.

	var options = {
		types: ['geocode'],
		componentRestrictions: {
			country: '<?php echo CountryISOName($_SESSION['country_default']); ?>'
		}
	};

  autocomplete = new google.maps.places.Autocomplete(
     (document.getElementById('fullAdd')),
     options
	  );

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  autocomplete.addListener('place_changed', fillInAddress);
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  // test
   for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm2[addressType]) {

		var val = place.address_components[i][componentForm2[addressType].type];
		document.getElementById(componentForm2[addressType].field).value = val;

	}

  }

  // street name
  var ac = jQuery("#fullAdd").val();
  var ac2 = ac.split(" ");
  var street_number = ac2[0];
  jQuery("#address_1").val(street_number);

  // get suburb from locality or sublocality
  var sublocality_level_1 = jQuery("#sublocality_level_1").val();
  var locality = jQuery("#locality").val();

  var suburb = ( sublocality_level_1 != '' )?sublocality_level_1:locality;
  jQuery("#address_3").val(suburb);

  // get suburb from google object 'vicinity'
  if( jQuery("#address_3").val() == '' ){
	jQuery("#address_3").val(place.vicinity);
  }


  console.log(place);
}
// end google autocomplete

	jQuery("#reason_they_left").change(function(){

	var reason_they_left = $('#reason_they_left').val();
	// alert(reason_they_left);
	if( reason_they_left == -1 ){
		jQuery("#other_reason1").show();
		jQuery("#other_reason").show();
	}else{
		jQuery("#other_reason1").hide();
		jQuery("#other_reason").hide();
	}            

	});

jQuery(document).ready(function(){

	<?php
	if( $_REQUEST['display_price_increase_warning'] == 1 ){ ?>
		alert('This property has an active job that may be on a previous pricing agreement, please check this and manually update it if needed');
	<?php
	}
	?>

	// show all PMe tenants
	jQuery("#view_all_pme_tnt_btn").click(function(){

		var obj = jQuery(this);
		var btn_txt = obj.find(".inner_icon_txt").html();
		var orig_btn_txt = 'Show';

		if( btn_txt == orig_btn_txt ){
			jQuery("#pme_tnt_tbl tr.PMe_tenant_exist_bg").show();
			obj.find(".inner_icon_txt").html("Hide");
		}else{
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
	jQuery('.add_new_pme_tenant_btn').click(function(){

		var new_t_fname = jQuery(this).parents("td:first").find(".pme_tenant_fname").val();
		var new_t_lname = jQuery(this).parents("td:first").find(".pme_tenant_lname").val();
		var new_t_mobile = jQuery(this).parents("td:first").find(".pme_tenant_mobile").val();
		var new_t_landline = jQuery(this).parents("td:first").find(".pme_tenant_landline").val();
		var new_t_email = jQuery(this).parents("td:first").find(".pme_tenant_email").val();
        var pme_api_txt = jQuery(this).parents("td:first").find(".pme_api_txt").val();

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
			//console.log(ret);
			//window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";
			location.reload();
		});

	}


	});

	$("#more_details_btn").click(function(){
		var agency_id = $('#keys_agency').val();
		var agency_addresses_id = $("#keys_agency").find(':selected').data("agency_addresses_id");
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_save_agency_keys.php",
			data: { 
				agency_id: agency_id,
				agency_addresses_id: (agency_addresses_id ? agency_addresses_id : 0),
				property_id: <?php echo $property_id; ?>
			}
		}).done(function( ret ){	
			jQuery("#load-screen").hide();
		});		
	});




	// clear PM prop ID
	jQuery("#remove_pm_prop_id_btn").click(function(){
		var api_used = <?php echo $api_used ?>;
		//console.log(api_used);
		//return false;

		if( confirm("Are you sure you want to clear?") ){
			jQuery.ajax({
				type: "POST",
				url: "ajax_clear_pm_prop_id.php",
				data: {
					property_id: <?php echo $id ?>,
					api_used: api_used
				}
			}).done(function( ret ){
				window.location="/view_property_details.php?id=<?php echo $id ?>";
			});
		}

	});



	jQuery("#add_tenants_btn").click(function(){

		var tenants_tr = jQuery(".tenants_tr:last").clone();
		tenants_tr.find(".addinput").val('');
		jQuery(".tenants_tbody").append(tenants_tr);

	});



	// show upload button script
	jQuery("#fileupload").change(function(){

		var file = jQuery(this).val();
		if( file != '' ){
			jQuery("#btn_upload_now").show();
		}

	});



	// more details checkbox script
	jQuery(".md_chk").change(function(){

		var checked = jQuery(this).prop('checked');

		if(checked==true){
			jQuery(this).parents("div.row:first").find(".md_chk_lbl").show();
			jQuery(this).parents("div.row:first").find(".nlm_lbl").html('<strong style="color:red;">Verify Payment</strong>');
		}else{
			jQuery(this).parents("div.row:first").find(".md_chk_lbl").hide();
			jQuery(this).parents("div.row:first").find(".nlm_lbl").html('Payment is Verified');
		}

	});




	// selects the previous tab on load
	var curr_tab = $.cookie('vpd_tab_index');
	if( curr_tab!='' ){

		if(curr_tab!=''){
			myTabs.goToTab(curr_tab);
		}else{
			myTabs.goToTab(0);
		}

	}


	// keep tab script
	jQuery(".c-tabs-nav__link").click(function(){

		var tab_index = jQuery(this).attr('data-tab_index');
		console.log(tab_index);
		$.cookie('vpd_tab_index', tab_index);

	});



	// ajax update property details
	jQuery(".btn_update_prop").click(function(){

		jQuery("#jform").submit();

	});

	// ajax update landlord details
	jQuery(".btn_update_landlord_api").click(function(){

		if(confirm("Are you sure you want to continue?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_landlord_api.php",
				data: {
					landlord_firstname_api: $(this).parents().find(".landlord_firstname_api").val(),
					landlord_lastname_api: $(this).parents().find(".landlord_lastname_api").val(),
					ll_mobile_api: $(this).parents().find(".ll_mobile_api").val(),
					ll_landline_api: $(this).parents().find(".ll_landline_api").val(),
					landlord_email_api: $(this).parents().find(".landlord_email_api").val(),
					id: <?=$id?>
				}
			}).done(function( ret ){
				window.location="/view_property_details.php?id=<?php echo $id ?>&landlord=1";
			});
		}

	});



	// mark property as no EN
	jQuery("#no_en").click(function(){

		var no_en = (jQuery(this).prop("checked")==true)?1:0;

		jQuery.ajax({
			type: "POST",
			url: "ajax_mark_property_as_no_en.php",
			data: {
				no_en: no_en,
				prop_id: <?php echo $id ?>
			}
		}).done(function( ret ){
			//jQuery("#load-screen").hide();
			//obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
		});


	});


	<?php
	// if franchise group = private
	if( $private_fg == 1 ){ ?>

		// for agency = private only
		jQuery("form#example").submit(function(){

			var ll_mobile = jQuery("#ll_mobile").val();
			var ll_landline = jQuery("#ll_landline").val();
			var error = '';

			if( ll_mobile =='' && ll_landline =='' ){
				error = 'Landlord Mobile or Landline must not be empty';
			}

			if( error!='' ){
				alert(error);
				return false;
			}else{
				return true;
			}

		});

	<?php
	}
	?>


	jQuery("#show_agency_pass").click(function(){

		btn_txt = jQuery(this).html();
		if( btn_txt == 'Show Password' ){ // show
			jQuery(this).html("Hide Password");
			jQuery(".agency_pass_div").show();
		}else{ // hide
			jQuery(this).html("Show Password");
			jQuery(".agency_pass_div").hide();
		}

	});


	function run_NLM(){
		var reason_they_left = $("#reason_they_left").val();
		var other_reason = $("#other_reason").val();
		var nlm_from = $("#nlm_from").val();

		error = '';
		if( reason_they_left == '' ){
			alert('Reason They Left is required');
			error = '1';
		}else{
			if( reason_they_left == -1 && other_reason == '' ){
				alert('Other Reason is required');
			error = '1';
			}
		}

		if (error == '') {
			jQuery.ajax({
				type: "POST",
				url: "ajax_set_no_longer_managed.php",
				data: {
					property_id: <?php echo $id ?>,
					reason_they_left: reason_they_left,
					nlm_from:nlm_from,
					other_reason: other_reason
				},
				dataType: 'json'
			}).done(function(ret){

				var nlm_chk_flag = parseInt(ret.nlm_chk_flag);
				if( nlm_chk_flag==1 ){
					alert(ret.ret_msg);
				}else{
					window.location="/view_property_details.php?id=<?php echo $id ?>";
				}

			});
		}
		

	}

	// no longer manager toggle tweak
	$('#btn_no_longer_managed').on('click',function(e){
            e.preventDefault();
            var dataVal = $(this).data('val');
            var btnVal = $(this).html();
            $('#nlm_td_box_toggle').slideToggle(function(){
                if(btnVal=="No Longer Manage?"){
                    $('#btn_no_longer_managed').html('Cancel').attr('data-val',1);
                }else{
                    $('#btn_no_longer_managed').html('No Longer Manage?').attr('data-val',0);
                }
            });
        });


	// no longer manage script
	jQuery("#btn_no_longer_managed_go").click(function(){


		// invoice payment check
		jQuery.ajax({
			type: "POST",
			url: "ajax_invoice_payment_check.php",
			data: {
				property_id: <?php echo $_GET['id']; ?>
			}
		}).done(function (ret2) {

			var inv_pay_count = parseInt(ret2);

			if( inv_pay_count > 0 ){

				if(confirm("This property has a job with an attached payment, are you sure you want to NLM?")==true){

					run_NLM();

				}

			}else{

				if(confirm("Are you sure you want to mark this property as NLM?")==true){

					run_NLM();

				}

			}


		});



	});


	jQuery("#btn_other_supplier").click(function(){

		var text = jQuery(this).html();
		var orig_btn_txt = '+ Other Supplier Job (SA ONLY)';
		if( text == orig_btn_txt ){
			jQuery("#job_date_div").show();
			jQuery(this).removeClass('blue-btn');
			jQuery(this).html("Cancel");
		}else{
			jQuery("#job_date_div").hide();
			jQuery(this).addClass('blue-btn');
			jQuery(this).html(orig_btn_txt);
		}


	});

	// restore property script
	jQuery("#restoreProb_btn").click(function(){

		// added confirm
		if( confirm("Are you sure you want to restore this property?") ){

			jQuery( "#dialog-confirm" ).dialog({

				resizable: false,
				modal: true,
				buttons: {
					"Yes": function() {
					console.log('yes');
					window.location='undelete_property.php?id=<?php echo $_GET['id']; ?>&del_tenant=1';
					jQuery( this ).dialog( "close" );
					},
					"No": function() {
					console.log('no');
					window.location='undelete_property.php?id=<?php echo $_GET['id']; ?>';
					jQuery( this ).dialog( "close" );
					}
				}

			});

		}


	});

	// deactivate property script
	jQuery("#deact_prop").click(function(){
		if(confirm("Are you sure you want to deactivate property?")==true){
			window.location='delete_property.php?id=<?php echo $_GET['id']; ?>';
		}
	});


	// call ajax for delete property
	jQuery("#btn_delete_permanently").click(function(){
		var redirect = jQuery("#redirect").val();
		var delete_reason = jQuery("#delete_reason").val();

		if (delete_reason != '') {
			// invoice payment check
			jQuery.ajax({
				type: "POST",
				url: "ajax_invoice_payment_check.php",
				data: {
					property_id: <?php echo $_GET['id']; ?>
				}
			}).done(function (ret) {

				var inv_pay_count = parseInt(ret);

				if( inv_pay_count > 0 ){
					alert("This property cannot be deleted as it has a job with an attached payment.")
				}else{

					if(confirm("Are you sure you want to continue?")==true){

						jQuery.ajax({
							type: "POST",
							url: "ajax_delete_property_permanently.php",
							data: {
								property_id: <?php echo $_GET['id']; ?>,
								delete_reason: delete_reason
							}
						}).done(function(ret2){
							if(redirect == 1){
								window.location="https://crmdevci.sats.com.au/properties/active_properties"
							}
							else if(redirect == 2){
								window.location="https://crmci.sats.com.au/properties/active_properties"
							}
							else{
								window.location="/view_properties.php?perm_del=1";
							}
							//window.location="/view_properties.php?perm_del=1";
						});

					}

				}


			});
		} else {
			alert('Please select reason!');
		}




	});



	jQuery("#tbl_services input[type='radio']").change(function(){
	  jQuery(this).parents("tr:first").find(".is_updated").val(1);
	});


	jQuery("#btn_create_dummy").click(function(){

		var hid_smoke_price = jQuery("#hid_smoke_price").val();
		var dummy_date = jQuery("#dummy_date").val();

		if(confirm("This will create a Other Supplier Job?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_create_dummy.php",
				data: {
					property_id: <?php echo $id ?>,
					hid_smoke_price: hid_smoke_price,
					dummy_date: dummy_date,
					agency_id: '<?php echo $agency_id; ?>'
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&pending=1";
			});
		}

	});

	jQuery("#btn_create_pending").click(function(){

		var hid_smoke_price = jQuery("#hid_smoke_price").val();

		if(confirm("Are you sure you want to continue?")==true){
			jQuery.ajax({
				type: "POST",
				url: "ajax_create_pendings.php",
				data: {
					property_id: <?php echo $id ?>,
					hid_smoke_price: hid_smoke_price,
					agency_id: '<?php echo $agency_id; ?>'
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&pending=1";
			});
		}

	});


	// tenants updated mark script
	jQuery(".tenant_fields").change(function(){
	  jQuery("#tenants_changed").val(1);
	});

	jQuery(".btn_update_price").click(function(){

		var psid = jQuery(this).parents("tr:first").find(".property_services_id").val();
		var ajt = jQuery(this).parents("tr:first").find(".alarm_job_type_id").val();
		var price = jQuery(this).parents("tr:first").find(".price_field").val();
		var price_reason = jQuery(this).parents("tr:first").find(".price_reason").val();
		var price_details = jQuery(this).parents("tr:first").find(".price_details").val();

			jQuery.ajax({
				type: "POST",
				url: "ajax_property_service_update_price.php",
				data: {
					property_id: <?php echo $id ?>,
					psid: psid,
					ajt: ajt,
					price: price,
					price_reason: price_reason,
					price_details: price_details
				}
			}).done(function(ret){
				window.location="/view_property_details.php?id=<?php echo $id ?>&update_price_success=1";
			});

	});


	// show/hide change price container
	jQuery(".price_lbl").click(function(){
		jQuery(this).parents("tr:first").find(".change_price_div").toggle();
	});



	// log required script
	jQuery("#add_event").click(function(){

		var error = "";
		var comments = jQuery("#comments").val();
		if(comments==""){
			error += "Details is required\n";
		}

		if(error!=""){
			alert(error);
		}else{
			jQuery("#property_event").submit();
		}

	});


	jQuery(".btn_create_job").click(function(){
		var property_id = <?php echo $id;  ?>;
		var alarm_job_type_id = jQuery(this).parents("tr:first").find(".alarm_job_type_id").val();
		var job_type = jQuery(this).parents("tr:first").find(".job_type").val();
		var price = jQuery(this).parents("tr:first").find(".price").val();
		var service_name = jQuery(this).parents("tr:first").find(".service_name").val();

		var vacant_from = jQuery(this).parents("tr:first").find(".vacant_from_input").val();
		var new_ten_start = jQuery(this).parents("tr:first").find(".new_ten_start_input").val();
		var problem = jQuery(this).parents("tr:first").find(".problem_input").val();
		var delete_tenant = jQuery(this).parents("tr:first").find(".delete_tenant:checked").val();
		var delete_tenant2 = (delete_tenant=="1")?1:0;
		var vacant_prop = jQuery(this).parents("tr:first").find(".vacant_prop:checked").val();
		var vacant_prop2 = (vacant_prop=="1")?1:0;
		var workorder_notes = jQuery(this).parents("tr:first").find(".workorder_notes").val();

		var job_status = jQuery(this).parents("tr:first").find(".job_status").val();
		var onhold_start_date = jQuery(this).parents("tr:first").find(".onhold_start_date").val();
		var onhold_end_date = jQuery(this).parents("tr:first").find(".onhold_end_date").val();

		var job_date = jQuery(this).parents("tr:first").find(".job_date").val();
		var jtech_sel = jQuery(this).parents("tr:first").find(".jtech_sel").val();

		var work_order = jQuery(this).parents("tr:first").find(".work_order").val();
		var preferred_alarm_id = jQuery(this).parents("tr:first").find(".preferred_alarm_id").val();
		var no_annual_visits = '<?php echo $no_annual_visits; ?>';
		var error = '';
		var proceed_create_job = false;

		if( job_type == 'IC Upgrade' && preferred_alarm_id == '' ){
			error += "Alarm Preference is required\n";
		}		

		if( error != '' ){
			alert(error);
		}else{

			if( no_annual_visits == 1 && job_type == 'Annual Visit'  ){

				if( confirm("Agency preference does not allow for an Annual Visit to be created, do you want to continue?") ){
					proceed_create_job = true;
				}

			}else{ // default
				proceed_create_job = true;
			}
			
			if( proceed_create_job == true ){

				// call ajax
				jQuery.ajax({
					type: "POST",
					url: "ajax_create_job.php",
					data: {
						property_id: property_id,
						alarm_job_type_id: alarm_job_type_id,
						job_type: job_type,
						price: price,
						vacant_from: vacant_from,
						new_ten_start: new_ten_start,
						problem: problem,
						service_name: service_name,
						staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
						delete_tenant : delete_tenant2,
						vacant_prop: vacant_prop2,
						agency_id: '<?php echo $agency_id; ?>',
						workorder_notes: workorder_notes,

						job_status: job_status,
						onhold_start_date: onhold_start_date,
						onhold_end_date: onhold_end_date,

						job_date: job_date,
						jtech_sel: jtech_sel,

						work_order: work_order,
						preferred_alarm_id:preferred_alarm_id
					}
				}).done(function(ret){
					window.location.reload();g
				});

			}							

		}

	});

	// invoke datepicker
	jQuery(".datepicker").datepicker({
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: "2006:2026", // year range
		dateFormat: "dd/mm/yy"	// date format
	});

	jQuery(".create_job").click(function(){
		var agency_status = '<?php echo $row1[4]; ?>';
		if(agency_status == 'deactivated'){
			alert('Error: Unable to do this while an Agency is Deactivated.');
		} else if(agency_status =='target'){
			alert('Error: Unable to do this while an Agency is Target.');
		} else {
			jQuery(this).parents("tr:first").find(".create_job_div").show();  
        }
	});
	jQuery(".job_type").change(function(){

		var btn_txt;

		jQuery(this).parents("tr:first").find(".workorder_notes").show();
		jQuery(this).parents("tr:first").find(".job_status").val('To Be Booked'); // default to most jobs
		jQuery(this).parents("tr:first").find(".preferred_alarm_div").hide();

		if(jQuery(this).val()=="Fix or Replace"){
			btn_txt = "Create Repair Job";
			jQuery(this).parents("tr:first").find(".desc_prob").show();
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
		}else if(jQuery(this).val()=="Change of Tenancy"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".vacant_from").show();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
		}else if(jQuery(this).val()=="Lease Renewal"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").show();
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
		}else if(jQuery(this).val()==""){
			jQuery(this).parents("tr:first").find(".create_job_div").hide();
		}else if(jQuery(this).val() == "Once-off"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".job_status").val('Send Letters');
		}else if(jQuery(this).val() == "Yearly Maintenance" && jQuery(".job_row").length == 0 ){ // if YM and no job = send letters
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".job_status").val('Send Letters');
		}else if(jQuery(this).val()=="IC Upgrade"){
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".preferred_alarm_div").show();
		}else{
			btn_txt = "Create "+jQuery(this).val()+" Job";
			jQuery(this).parents("tr:first").find(".desc_prob").hide();
			jQuery(this).parents("tr:first").find(".vacant_from").hide();
			jQuery(this).parents("tr:first").find(".new_ten_start").hide();
		}


		jQuery(".btn_create_job").show();
		jQuery(".delete_tenant_span").show();
		jQuery(".vacant_prop_span").show();
		jQuery(".job_status").show();
		jQuery(".work_order").show();
		jQuery(".btn_create_job").html(btn_txt);

	});


	// job status script
	jQuery(".job_status").change(function(){

		var job_status = jQuery(this);

		// clear
		// on hold
		jQuery(this).parents("tr:first").find(".onhold_date_div .onhold_start_date").val('');
		jQuery(this).parents("tr:first").find(".onhold_date_div .onhold_end_date").val('');
		jQuery(this).parents("tr:first").find(".onhold_date_div").hide();

		// job date
		jQuery(this).parents("tr:first").find(".jdate_div").hide();

		// tech
		jQuery(this).parents("tr:first").find(".jtech_div").hide();

		if( job_status.val() == 'On Hold' ){
			jQuery(this).parents("tr:first").find(".onhold_date_div").show();
		}else if( job_status.val() == 'Completed' ){
			jQuery(this).parents("tr:first").find(".jdate_div").show();
			jQuery(this).parents("tr:first").find(".jtech_div").show();
		}

	});

	// Vacant tick/untick script
	jQuery("#vacant_prop").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		// show/hide start and end date
		if( is_ticked == true ){
			jQuery(this).parents("tr:first").find(".onhold_date_div").show();
		}else{
			jQuery(this).parents("tr:first").find(".onhold_date_div").hide();
		}
		

	});

	<?php
	// if it finds a job for IC upgrade
	if( $job_to_upgrade_to_ic_service > 0 ){ ?>

		jQuery("#update_to_ic_service_btn").click(function(){

		var job_to_upgrade_to_ic_service = <?php echo $job_to_upgrade_to_ic_service; ?>;

		if( confirm("Are you sure you want to update the property to an IC service?") ){

			jQuery.ajax({
				url: 'ajax_update_property_service_to_ic.php',
				type: 'POST',
				data: {
					'job_to_upgrade_to_ic_service': <?php echo $job_to_upgrade_to_ic_service ?>
				}
			}).done(function( ret ){

				window.location="/view_property_details.php?id=<?php echo $id ?>&ic_service_type_update=1";
				//location.reload();

			});

		}

		});

	<?php
	}
	?>
	
	
	// non-active services hide/show toggle script
	jQuery("#show_non_active_services").click(function(){

		jQuery("#non_active_service_div").toggle();

	});


	
	jQuery("#non_active_service_update_btn").click(function(){
		
		// loop through all non active services
		var non_active_ps_id_arr = [];
		var non_active_service_status_arr = [];
		jQuery(".non_active_ps_id").each(function(){

			var non_active_ps_id_dom = jQuery(this);
			var parents = non_active_ps_id_dom.parents("tr:first");
			var non_active_ps_id = non_active_ps_id_dom.val();

			var non_active_service_status = parents.find(".non_active_service_status:checked").val();

			if( non_active_ps_id > 0 ){
				non_active_ps_id_arr.push(non_active_ps_id);
				non_active_service_status_arr.push(non_active_service_status);
			}

		});	
		
		if( confirm("Are you sure you want to update service status of non-active services?") ){

			jQuery.ajax({
				url: 'ajax_non_active_service_update.php',
				type: 'POST',
				data: {
					property_id: <?php echo $id ?>,					
					non_active_ps_id_arr: non_active_ps_id_arr,
					non_active_service_status_arr: non_active_service_status_arr
				}
			}).done(function( ret ){
							
				window.location="/view_property_details.php?id=<?php echo $id ?>&non_active_service_update_success=1";
				//location.reload();
				
			});

		}

	});
	


	// add new service type hide/show toggle script
	jQuery("#add_new_service_btn").click(function(){

		var add_service_btn_dom = jQuery(this);

		if( add_service_btn_dom.text() == 'Add New Service' ){

			add_service_btn_dom.text("Select Service");

		}else{

			add_service_btn_dom.text("Add New Service");

		}		

		jQuery("#add_new_service_type_div").toggle();

	});


	jQuery("#add_new_service_type_submit_btn").click(function(){

		var new_service_type = jQuery("#new_service_type").val();		
		var new_service_type_status = jQuery("#new_service_type_status").val();
		var error = '';

		if( new_service_type == '' ){
			error += "Please Select Service Type\n";
		}

		if( new_service_type_status == '' ){
			error += "Service Type Status is required\n";
		}

		if( error != '' ){
			alert(error);
		}else{

			if( confirm("Are you sure you want to add new service type?") ){

				jQuery.ajax({
					url: 'ajax_add_new_service_type.php',
					type: 'POST',
					data: {
						property_id: <?php echo $id ?>,
						agency_id: <?php echo $agency_id; ?>,
						new_service_type: new_service_type,
						new_service_type_status: new_service_type_status
					}
				}).done(function( ret ){
								
					//window.location="/view_property_details.php?id=<?php echo $id ?>&add_new_service_type=1";
					location.reload();
					
				});

			}

		}		

	});

	// change service hide/show toggle script
	jQuery(".change_service_btn").click(function(){

		var change_service_btn_dom = jQuery(this);
		var parents_tr = change_service_btn_dom.parents("tr:first");

		parents_tr.find(".change_service_div").toggle();

	});


	jQuery(".change_service_save_btn").click(function(){

		var change_service_save_btn_dom = jQuery(this);
		var parents_tr = change_service_save_btn_dom.parents("tr:first");

		var from_service_type = parents_tr.find(".from_service_type").val();
		var to_service_type = parents_tr.find(".to_service_type").val();

		if( confirm("Are you sure you want to update service type?") ){

			if( from_service_type > 0 && to_service_type > 0 ){

				jQuery.ajax({
					url: 'ajax_update_service_type.php',
					type: 'POST',
					data: {
						property_id: <?php echo $id ?>,
						agency_id: <?php echo $agency_id; ?>,
						from_service_type: from_service_type,
						to_service_type: to_service_type
					}
				}).done(function( ret ){
								
					window.location="/view_property_details.php?id=<?php echo $id ?>&service_type_update=1";
					//location.reload();
					
				});

			}			

		}

	});


	// copy to CRM
	jQuery(".copy_to_crm_btn").click(function(){

		var copy_to_crm_btn_dom = jQuery(this);
		var api_landlord_div = copy_to_crm_btn_dom.parents(".api_landlord_div");
		console.log(api_landlord_div);

		var landlord_firstname_api = api_landlord_div.find(".landlord_firstname_api").val();		
		var landlord_lastname_api = api_landlord_div.find(".landlord_lastname_api").val();		
		var ll_mobile_api = api_landlord_div.find(".ll_mobile_api").val();		
		var ll_landline_api = api_landlord_div.find(".ll_landline_api").val();
		var landlord_email_api = api_landlord_div.find(".landlord_email_api").val();				

		jQuery("#landlord_firstname").val(landlord_firstname_api);
		jQuery("#landlord_lastname").val(landlord_lastname_api);
		jQuery("#ll_mobile").val(ll_mobile_api);
		jQuery("#ll_landline").val(ll_landline_api);
		jQuery("#landlord_email").val(landlord_email_api);		

	});

	jQuery("#remove_property_variation_btn").click(function(){

		var property_id = <?php echo $id ?>;

		if( confirm("Are you sure you want to remove property variation") ){

			if( property_id > 0 ){

				jQuery.ajax({
					url: 'ajax_remove_property_variation.php',
					type: 'POST',
					data: {
						property_id: property_id
					}
				}).done(function( ret ){
								
					window.location="/view_property_details.php?id=<?php echo $id ?>&remove_property_variation_success=1";
					
				});

			}			

		}

	});

	$('.btn_update_key_number').click(function(){
		var prop_id = <?php echo $property_id ?>;
		var key_num = $('#pme_key_number_field').val();
		jQuery.ajax({
				type: "POST",
				url: "ajax_update_key_number.php",
				data: { 
					prop_id: prop_id,
					key_num: key_num
				}
			}).done(function( ret ){
				alert('Key Number Update Success');
				location.reload();
			});		
	})

	
	jQuery("#fetch_sub_date_btn").click(function(){

		var prop_id = <?php echo $property_id ?>;
		var fetch_sub_date_btn_dom = jQuery(this);		

		jQuery("#load-screen").show();

		jQuery("#subscription_date").val('');
		jQuery("#subscription_source").css('border','unset');

		jQuery.ajax({
			type: "POST",
			url: "ajax_fetch_subscription_date.php",
			data: { 
				prop_id: prop_id
			},
			dataType: 'json'
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			if( ret ){
				
				jQuery("#subscription_date").val(ret.jdate);

				if( ret.assigned_tech == 1 || ret.assigned_tech == 2 ){ // if Other Supplier(1) OR Upfront Bill(2)
					jQuery("#subscription_source").css('border','1px solid red');
				}else{
					jQuery("#subscription_source").val(8); // select "SATS"
				}

				jQuery("#btn_update_prop_service_tab").text('Save'); 
				jQuery("#btn_cancel_update_prop").show();

			}else{
				alert("No data found. Please enter subscription start date manually");
			}			

		});	

	});

	jQuery("#btn_cancel_update_prop").click(function(){

		var subscription_date_orig = jQuery("#subscription_date_orig").val();
		var subscription_source_orig = jQuery("#subscription_source_orig").val();

		jQuery("#subscription_date").val(subscription_date_orig);
		jQuery("#subscription_source").val(subscription_source_orig);
		jQuery("#btn_update_prop_service_tab").text('Update'); 
		jQuery("#btn_cancel_update_prop").hide();
		
	});


});

	function submit_property_event_del(pelid){
		if(document.property_event_del.onsubmit && !document.property_event_del.onsubmit()){return;}

		var con = confirm('Are you sure you want to Delete this file?');
		if(con){
			$('#del_pelid').val(pelid);
			document.property_event_del.submit();
		}
	}
</script>

 <script type="text/javascript">

     function initialize() {
       initMap();
       initAutocomplete();
     }


     var geocoder;
     var map;
     var address = "<?php echo $prop_full_add; ?>";

      function initMap() {

              geocoder = new google.maps.Geocoder();
              var latlng = new google.maps.LatLng(-25.363, 131.044);
              var myOptions = {
                zoom: 15,
                center: latlng,
                mapTypeControl: true,
                mapTypeControlOptions: {
                  style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                },
                navigationControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
              };
              map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
              if (geocoder) {
                geocoder.geocode({
                  'address': address
                }, function(results, status) {
                  if (status == google.maps.GeocoderStatus.OK) {
                    if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                      map.setCenter(results[0].geometry.location);

                      var infowindow = new google.maps.InfoWindow({
                        content: '<b>' + address + '</b>',
                        size: new google.maps.Size(150, 50)
                      });

                      var marker = new google.maps.Marker({
                        position: results[0].geometry.location,
                        map: map,
                        title: address
                      });
                      google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(map, marker);
                      });

                    } else {
                      console.log("No results found");
                    }
                  } else {
                    console.log("Geocode was not successful for the following reason: " + status);
                  }
                });
              }

      }
     google.maps.event.addDomListener(window, 'load', initMap);
    </script>




<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&signed_in=true&libraries=places&callback=initialize" async defer></script>

<style type=text/css>
	.vw-pro-dtl-tn-hld .row label{
		margin-top: 5px !important;
		padding-bottom: 5px !important;
	}
</style>


</body>
</html>

<?php
$pageLoadEnd = microtime(TRUE);
$pageLoadDuration = floor(($pageLoadEnd - $pageLoadStart) * 1000);

$page = 'VPD';
$created = date('Y-m-d H:i:s');

$sql = "
INSERT INTO logged_page_durations
    (`page`, `duration`, `created`)
VALUES
    ('{$page}', {$pageLoadDuration}, '{$created}')
";

mysql_query($sql);
?>
