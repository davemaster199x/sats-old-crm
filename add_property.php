<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include('inc/init.php');

$crm = new Sats_Crm_Class;

$address_1 = addslashes(trim($_POST['address_1']));
//$address_2 = addslashes($_POST['address_2']);
$address_2 = mysql_prep(trim($_POST['address_2']));
$address_3 = addslashes(trim($_POST['address_3']));
$state = addslashes(trim($_POST['state']));
$postcode = addslashes(trim($_POST['postcode']));
$yearpurchase = addslashes($_POST['yearpurchase']);
$inv_number = addslashes($_POST['inv_number']);
$comments = addslashes($_POST['comments']);
$agency_id = addslashes($_POST['agency']);
$autojob = addslashes($_POST['radioService']);
//$autojob = addslashes($_POST['autojob']);

$holiday_rental = addslashes($_POST['holiday_rental']);



$landlord_firstname = mysql_prep($_POST['landlord_firstname']);
$landlord_lastname = mysql_prep($_POST['landlord_lastname']);
$landlord_email = addslashes($_POST['landlord_email']);

$alarm_code = mysql_real_escape_string($_POST['alarm_code']);


$landlord_mobile = mysql_real_escape_string($_POST['landlord_mobile']);
$landlord_landline = mysql_real_escape_string($_POST['landlord_landline']);

$prop_vacant = mysql_real_escape_string($_POST['prop_vacant']);



$hid_allow_pm = mysql_real_escape_string($_POST['hid_allow_pm']);
$property_manager = ( $hid_allow_pm == 1 )?mysql_real_escape_string($_POST['property_manager']):'';

$price = $_POST['price'];

$pm_passed_agency_id = mysql_real_escape_string($_POST['pm_passed_agency_id']);

$pm_prop_id = mysql_real_escape_string($_POST['pm_prop_id']);
$pm_prop_id_field = '';
$pm_prop_id_val = '';


// tenants 1
$tenant_firstname1 = mysql_prep($_POST['tenant_firstname1']);
$tenant_lastname1 = mysql_prep($_POST['tenant_lastname1']);
$tenant_ph1 = addslashes($_POST['tenant_ph1']);
$tenant_mob1 = addslashes($_POST['tenant_mob1']);
$tenant_email1 = addslashes($_POST['tenant_email1']);

// tenants 2
$tenant_firstname2 = mysql_prep($_POST['tenant_firstname2']);
$tenant_lastname2 = mysql_prep($_POST['tenant_lastname2']);
$tenant_ph2 = addslashes($_POST['tenant_ph2']);
$tenant_mob2 = addslashes($_POST['tenant_mob2']);
$tenant_email2 = addslashes($_POST['tenant_email2']);

// tenants 3
$tenant_firstname3 = mysql_real_escape_string($_POST['tenant_firstname3']);
$tenant_lastname3 = mysql_real_escape_string($_POST['tenant_lastname3']);
$tenant_ph3 = mysql_real_escape_string($_POST['tenant_ph3']);
$tenant_mob3 = mysql_real_escape_string($_POST['tenant_mob3']);
$tenant_email3 = mysql_real_escape_string($_POST['tenant_email3']);

// tenant 4
$tenant_firstname4 = mysql_real_escape_string($_POST['tenant_firstname4']);
$tenant_lastname4 = mysql_real_escape_string($_POST['tenant_lastname4']);
$tenant_ph4 = mysql_real_escape_string($_POST['tenant_ph4']);
$tenant_mob4 = mysql_real_escape_string($_POST['tenant_mob4']);
$tenant_email4 = mysql_real_escape_string($_POST['tenant_email4']);


$compass_index_num = mysql_real_escape_string($_POST['compass_index_num']);
$workorder_notes = mysql_real_escape_string($_POST['workorder_notes']);


$duplicates = [];

// edited old code - jc
$duplicateQuery =  "
SELECT 
	p.`property_id`,
	p.`address_1` AS p_address_1,
	p.`address_2` AS p_address_2,
	p.`address_3` AS p_address_3,
	p.`state` AS p_state,
	p.`postcode` AS p_postcode,
	p.`deleted`,
	a.`agency_id`,
	a.`agency_name`
FROM `property`AS p
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE TRIM(LCASE(p.`address_1`)) = LCASE('". $address_1 ."') 
  AND TRIM(LCASE(p.`address_2`)) = LCASE('". $address_2 ."') 
  AND TRIM(LCASE(p.`address_3`)) = LCASE('". $address_3 ."') 
  AND TRIM(LCASE(p.`state`)) = LCASE('". $state ."') 
  AND TRIM(LCASE(p.`postcode`)) = LCASE('". $postcode ."');
";

$duplicateResult = mysql_query($duplicateQuery, $connection);
while ($row = mysql_fetch_array($duplicateResult))
   {
        $duplicates[] = array(
			'property_id' => $row['property_id'],
			'prop_address' => "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}",
			'deleted' => $row['deleted'],
			'agency_id' => $row['agency_id'],
			'agency_name' => $row['agency_name']
		);
   }

$isDuplicate = empty($duplicates);


// Test for duplicate properties
if(!$isDuplicate){
    //STOP if there are duplicates
    //echo "there are duplicates";
    //var_dump($duplicates);
    
    $title = "Add Property Error";
    $onload = 1;
    $onload_txt = "zxcSelectSort('agency',1)";
    
    include('inc/header_html.php');
    include('inc/menu.php');
	
	
	//print_r($duplicates);
    
            echo "<div id='mainContent' style='text-align:left; margin-top: 54px;'>";
                    echo '<div class="success">Duplicate Property Found</div>';
                    
                    echo '<p>The following properties exist in the system with the same details:</p>';
                    echo "<table class='dup_prop_tbl' style='width:auto;'>";
					echo "
					<tr>
						<td>Adddress</td>
						<td>Status</td>
						<td>Agency</td>
						<td>Action</td>
					</tr>
					";
                    foreach($duplicates as $prop){
						
						$isSameAgency = ( $prop['agency_id'] == $pm_passed_agency_id )?true:false;
						$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$prop['agency_id']}");
                        echo '
						<tr>
							<td><a href="view_property_details.php?id='. $prop['property_id'] .'">'. $prop['prop_address'] .'</a></td>
							<td>'.( ( $prop['deleted'] == 1 )?'Deactivated':'Active' ).'</td>
							<td>
							<a href="'. $ci_link .'">'. $prop['agency_name'] .'</a> '.( ( $isSameAgency == true )?'(Same Agency)':'' ).'
							</td>
							<td>
							';
							if( $isSameAgency == true && $prop['deleted']==1 ){
								echo '
								<button data-crm_prop_id="'.$prop['property_id'].'" data-pm_prop_id="'.$pm_prop_id.'" id="restore_prop_btn" class="submitbtnImg submitbutton" type="button" style="float:left">
									<img class="inner_icon" src="images/save-button.png">
									Restore and Connect Property
								</button>
								';
							}							
						echo '
							</td>
						</tr>
						';		
                    }
                    echo "</table>";
            echo '</div>';
            echo '<br class="clearfloat" />';
			echo "
			<style>
			.dup_prop_tbl td{
				padding: 5px 10px;
			}
			</style>
			<script>
			jQuery(document).ready(function(){
				
				jQuery('#restore_prop_btn').click(function(){
					
					var obj = jQuery(this)
					var property_id = obj.attr('data-crm_prop_id');
					var pm_prop_id = obj.attr('data-pm_prop_id');
					var fade = obj.hasClass('fadeIt');
					
					if( fade == false ){
						
						if( confirm('Are you sure you want to continue?') == true ){
							
							jQuery.ajax({
								type: 'POST',
								url: 'ajax_restore_prop_and_insert_pm_id.php',
								data: {
									property_id: property_id,
									pm_prop_id: pm_prop_id
								}
							}).done(function(ret){
								obj.addClass('fadeIt');	
								window.location='view_property_details.php?id='+property_id;
							});
							
						}
						
					}
					
					
					
				});
				
				
			});
			</script>
			";
        echo '</body>';
    echo '</html>';

} else {
    //Continue if there are duplicates

 //echo "Autojob: $autojob";
 if ($autojob == "Yes") {
 	 $service = 1;
	 //echo "YES to Service";
 }
 else{ 
 	 $service = 0;
	 //echo "No to Service";
 }
 
$prop_comments = '';
 
 // Hume Community Housing Association
 if( $agency_id==1598 ){
	
	$prop_comments = 'Please install 9vLi or 240v only. DO NOT INSTALL 240vLi';
	
}




	// get lat and lng for mapping
	$address_str = "{$address_1} {$address_2} {$address_3} {$state} {$postcode}";
	$coordinate = getGoogleMapCoordinates($address_str);

	// PM property ID
	if( $pm_prop_id != '' ){
		// $pm_prop_id_field = " `propertyme_prop_id`, ";
		$pm_prop_id_field = " `api_prop_id` ";
		$pm_prop_id_val = " '{$pm_prop_id}' ";
		// $pm_prop_id_val = " '{$pm_prop_id}', ";
	}
	
	
	//$new_pm = 0;
	$new_pm = NEW_PM;
	$prom_man_field = '';

	if( $new_pm == 1 ){ // NEW PM
	
		$prom_man_field = ' `pm_id_new`, ';

	}else{ // OLD PM
	
		$prom_man_field = ' `property_managers_id`, ';
		
	}
	
					
	// add property
	$key_num = $_POST['key_num'];
	$insertQuery = "
		INSERT INTO property (
			`agency_id`,
			`address_1`,
			`address_2`,
			`address_3`,
			`state`,
			`postcode`,
			`added_by`,
			`key_number`,
			`alarm_code`,
			`holiday_rental`,
			`landlord_firstname`,
			`landlord_lastname`,
			`landlord_email`,
			`landlord_mob`,
			`landlord_ph`,
			{$prom_man_field}
			`comments`,
			
			`tenant_firstname1`, 
			`tenant_lastname1`, 
			`tenant_ph1`, 
			`tenant_mob1`, 
			`tenant_email1`,
			
			`tenant_firstname2`, 
			`tenant_lastname2`, 
			`tenant_ph2`, 
			`tenant_mob2`, 
			`tenant_email2`,
			
			`tenant_firstname3`, 
			`tenant_lastname3`, 
			`tenant_ph3`, 
			`tenant_mob3`, 
			`tenant_email3`,
			
			`tenant_firstname4`, 
			`tenant_lastname4`, 
			`tenant_ph4`, 
			`tenant_mob4`, 
			`tenant_email4`,			
			
			`lat`,
			`lng`,
			
			`compass_index_num`
		)
		VALUES (
			'{$agency_id}',
			'{$address_1}',
			'{$address_2}',
			'{$address_3}',
			'{$state}',
			'{$postcode}',		
			'{$_SESSION['USER_DETAILS']['StaffID']}',
			'".mysql_real_escape_string($key_num)."',
			'{$alarm_code}',
			'{$holiday_rental}',
			'{$landlord_firstname}',
			'{$landlord_lastname}',
			'{$landlord_email}',
			'{$landlord_mobile}',
			'{$landlord_landline}',			
			'{$property_manager}',
			'{$prop_comments}',
			
			'{$tenant_firstname1}', 
			'{$tenant_lastname1}', 
			'{$tenant_ph1}', 
			'{$tenant_mob1}', 
			'{$tenant_email1}',
			
			
			'{$tenant_firstname2}', 
			'{$tenant_lastname2}', 
			'{$tenant_ph2}', 
			'{$tenant_mob2}', 
			'{$tenant_email2}',
			
			
			'{$tenant_firstname3}', 
			'{$tenant_lastname3}', 
			'{$tenant_ph3}', 
			'{$tenant_mob3}', 
			'{$tenant_email3}',
			
			
			'{$tenant_firstname4}', 
			'{$tenant_lastname4}', 
			'{$tenant_ph4}', 
			'{$tenant_mob4}', 
			'{$tenant_email4}',
			
			
			'{$coordinate['lat']}',
			'{$coordinate['lng']}',
			
			'{$compass_index_num}'
		)
	";			

     if ((@ mysql_query ($insertQuery, $connection)) && @ mysql_affected_rows() == 1){
			$property_id = mysql_insert_id();
			$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
			$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];
			
			$prop_log_details = ( $pm_prop_id != '' )?'Added to match Active property on PropertyMe':'';
			$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
							VALUES (".$property_id.", ".$staff_id.", 'Property Added', '{$prop_log_details}', '".date('Y-m-d H:i:s')."')";
			mysql_query($insertLogQuery, $connection);

			$insertapi_property_data = "
			INSERT INTO `api_property_data` (
				`crm_prop_id`,
				`api`,
				`api_prop_id`,
				`active`
			)
			VALUES (
				{$property_id},
				1,
				'{$pm_prop_id_val}',
				0
			)
			";
			mysql_query ($insertapi_property_data, $connection);

			 // upload, old function copied from vpd file upload
			if($_FILES['fileupload']['error'] == 0 && $_FILES['fileupload']['size'] > 0){

				// check if filename already exist
				if(file_exists(UPLOAD_PATH_BASE . $property_id . "/" . $_FILES['fileupload']['name'])){
					echo "<div class='error'>Filename Already Exists. Please use a Unique File Name</div>";
				}else{
				
					if( $crm->uploadPropertyFiles_old($_FILES, $property_id) ){		
						//echo "<div class='success'>File Uploaded Successfully</div>";
					}else{
						echo "<div class='error'>Technical Problem. Please try again</div>";
					}
				
				}

				
			}


			// update propertyme id if exists
			if(isset($_POST['propertyme_prop_id']) AND $_POST['propertyme_prop_id'] != ""){
				$propertyme_prop_id = filter_input(INPUT_POST, 'propertyme_prop_id');
				mysql_query("UPDATE `api_property_data` SET `api_prop_id` = '".$propertyme_prop_id."' WHERE `property_id`=".$property_id);
			}
			
			
			// dynamic tenants
			//$new_tenants = 1;
			$new_tenants = NEW_TENANTS;

			if($new_tenants == 1){ // new tenants (trigger when tenants is new/1)

			$tenant_firstname_arr = $_POST['tenant_firstname'];
			$tenant_lastname_arr = $_POST['tenant_lastname'];
			$tenant_ph_arr = $_POST['tenant_ph'];
			$tenant_mob_arr = $_POST['tenant_mob'];
			$tenant_email_arr = $_POST['tenant_email'];

			foreach( $tenant_firstname_arr as $index => $tnt_fname ){

				$tenant_firstname = mysql_real_escape_string($tnt_fname);
				$tenant_lastname = mysql_real_escape_string($tenant_lastname_arr[$index]);
				$tenant_mob = $tenant_mob_arr[$index];
				$tenant_ph = $tenant_ph_arr[$index];
				$tenant_email = $tenant_email_arr[$index];

				if(
					$tenant_firstname != '' ||
					$tenant_lastname != ''
					//$tenant_mob != '' ||
					//$tenant_ph != '' ||
					//$tenant_email != ''
				){
					$new_tnt_sql_str = "
						INSERT INTO
						`property_tenants`(
							`property_id`,
							`tenant_firstname`,
							`tenant_lastname`,
							`tenant_mobile`,
							`tenant_landline`,
							`tenant_email`
						)
						VALUES(
							{$property_id},
							'{$tenant_firstname}',
							'{$tenant_lastname}',
							'{$tenant_mob}',
							'{$tenant_ph}',
							'{$tenant_email}'	
						)
					";	

					mysql_query($new_tnt_sql_str);
				}


				/* Jc's old code start here-------------------			
				$tenant_firstname = $tnt_fname;
				$tenant_lastname = $tenant_lastname_arr[$index];
				$tenant_mob = $tenant_mob_arr[$index];
				$tenant_ph = $tenant_ph_arr[$index];
				$tenant_email = $tenant_email_arr[$index];
				
				if( 
					$tenant_firstname != '' ||
					$tenant_lastname != '' ||
					$tenant_mob != '' ||
					$tenant_ph != '' ||
					$tenant_email != ''
				){
					
					// insert to new table
					$new_tnt_sql_str = "
						INSERT INTO
						`property_tenants`(
							`property_id`,
							`tenant_firstname`,
							`tenant_lastname`,
							`tenant_mobile`,
							`tenant_landline`,
							`tenant_email`
						)
						VALUES(
							{$property_id},
							'{$tenant_firstname}',
							'{$tenant_lastname}',
							'{$tenant_mob}',
							'{$tenant_ph}',
							'{$tenant_email}'	
						)
					";	

					mysql_query($new_tnt_sql_str);
					
				}	
				*/
				
			}
		}
			
			
			

        //echo "<h3>Property successfully added. The comments field was $comments</h3><br><br>";
	 }
     else{
        //echo "<h3>A fatal error occurred</h3><br>$insertQuery";
	 }
	 
	 /*
   $query = "SELECT MAX(property_id) from property;";
   $result = mysql_query($query, $connection);
   while ($row = mysql_fetch_row($result))
   {
		$property_id = $row[0];

    updateTechSheetAlarmTypes($property_id, $_POST['alarm_job_type']);

    
   }
   */
   
	// add property services
	$alarm_job_type_id = $_POST['alarm_job_type_id'];
	$price = $_POST['price'];
	$price_changed = $_POST['price_changed'];
	$price_reason = $_POST['price_reason'];
	$price_details = $_POST['price_details'];
   
   foreach($alarm_job_type_id as $index=>$val){
   
		$service = $_POST['service'.$index];
		
		// property services
		mysql_query("
			INSERT INTO 
			`property_services` (
				`property_id`,
				`alarm_job_type_id`,
				`service`,
				`price`,
				`status_changed`
			)
			VALUES (
				'".mysql_real_escape_string($property_id)."',
				'".mysql_real_escape_string($val)."',
				'".mysql_real_escape_string($service)."',
				'".mysql_real_escape_string($price[$index])."',
				'".date("Y-m-d H:i:s")."'
			)
		");
		
		// add jobs
		$work_order_num = $_POST['work_order_num'];
		if($service==1){
		
			// techsheet
			mysql_query("
				INSERT INTO 
				`property_propertytype` (
					`property_id`,
					`alarm_job_type_id`
				)
				VALUES (
					'".mysql_real_escape_string($property_id)."',
					'".mysql_real_escape_string($val)."'
				)
			");
			
			
			// get Franchise Group
			$agen_sql = mysql_query("
				SELECT `franchise_groups_id`
				FROM `agency`
				WHERE `agency_id` = {$agency_id}
			");
			$agen = mysql_fetch_array($agen_sql);
			
			$fg_id = $agen['franchise_groups_id'];
			
			// IF DHA agencies, franchise group = 14(Defence Housing)
			if( isDHAagenciesV2($fg_id)==true ){
				
				$tech_notes = mysql_real_escape_string($_POST['tech_notes']);
				$start_date = ($_POST['start_date']!="")?"'".date("Y-m-d",strtotime(str_replace("/","-",mysql_real_escape_string($_POST['start_date']))))."'":"NULL";	
				$due_date = ($_POST['due_date']!="")?"'".date("Y-m-d",strtotime(str_replace("/","-",mysql_real_escape_string($_POST['due_date']))))."'":"NULL";
				
				$jt_txt = 'Once-off';
				$s_txt = 'DHA';
				$add_field = '
					`tech_notes`,
					`start_date`,
					`due_date`,
				';
				$add_val = "
					'{$tech_notes}',
					{$start_date},
					{$due_date},
				";
				
			}else{
				
				$jt_txt = 'Yearly Maintenance';
				$s_txt = 'Send Letters';
				$add_field = '';
				$add_val = '';
				
			}
			
			// if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
			if( isDHAagenciesV2($fg_id)==true || agencyHasMaintenanceProgram($agency_id)==true   ){
				$dha_need_processing = 1;
			}
		
			// jobs
			mysql_query("
				INSERT INTO 
				jobs (
					`job_type`, 
					`property_id`, 
					`status`,
					`work_order`,
					`service`,
					{$add_field}
					`job_price`,
					`property_vacant`,
					`dha_need_processing`,
					`comments`
				) 
				VALUES (
					'{$jt_txt}', 
					'{$property_id}', 
					'{$s_txt}',
					'".mysql_real_escape_string($work_order_num)."',
					'".mysql_real_escape_string($val)."',
					{$add_val}
					'".mysql_real_escape_string($price[$index])."',
					'{$prop_vacant}',
					'{$dha_need_processing}',
					'{$workorder_notes}'
				)
			");
			
			// job id
			$job_id = mysql_insert_id();
			
			// AUTO - UPDATE INVOICE DETAILS
			$crm->updateInvoiceDetails($job_id);
			
			// if bundle, insert bundle
			// get alarm job type
			$ajt_sql = mysql_query("
				SELECT *
				FROM `alarm_job_type`
				WHERE `id` = {$val}
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
				}	
			}
			
		}
				
		
		// popupate alarms
		if($val==2){
		
			$aa_sql = mysql_query("
				SELECT *
				FROM `agency_alarms`
				WHERE `agency_id` = {$agency_id}
			");
			
			if(mysql_num_rows($aa_sql)>0){
			
				while($aa = mysql_fetch_array($aa_sql)){
			
					mysql_query("
						INSERT INTO
						`property_alarms` (
							`property_id`,
							`alarm_pwr_id`,
							`price`
						)
						VALUES (
							'{$property_id}',
							'{$aa['alarm_pwr_id']}',
							'{$aa['price']}'
						)
					");
					
				}
				
			}					
			
		}
		
		
		// price change logs
		// change price log
		if($price_changed[$index]==1){
		
			$serv = "";
			switch($val){
				case 2:
					$serv = "Smoke Alarms";
				break;
				case 5:
					$serv = "Safety Switch";
				break;
				case 6:
					$serv = "Corded Windows";
				break;
				case 7:
					$serv = "Pool Barriers";
				break;
			}
		
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
					'".mysql_real_escape_string($property_id)."',
					'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
					'Price Changed',					
					'New Price for {$serv}- $".$price[$index].", Reason- ".$price_reason[$index].", Details- ".$price_details[$index]."', 
					'" . date('Y-m-d H:i:s') . "'				
				)
			");
		}
		
   
   }
   
   $rem_agen = $_POST['remember_agency'];
   if($rem_agen==1){
		$_SESSION['remember_agency'] = $agency_id;
		$_SESSION['rem_fg_id'] = $fg_id;
   }else{
		unset($_SESSION['remember_agency']);
		unset($_SESSION['rem_fg_id']);
   }
	
   header("Location: view_property_details.php?id={$property_id}&add_prop_success=1");

}
?>