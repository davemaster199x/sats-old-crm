<?

$title = "Update Pending Jobs";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

$crm = new Sats_Crm_Class;


?>

  <div id="mainContent">
  
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first">
			<a title="Service Due" href="/service_due_jobs.php"><strong>Service Due</strong></a>
		</li>
      </ul>
    </div>
	<div id="time"><?php echo date("l jS F Y"); ?></div>
  
  
   
	
    
<?php
	
// init the variables
$jobs  = "";
$jid = array();
$status = "To Be Booked";
$doaction = $_POST['submit'];

$checkbox = isset($_POST['chkbox']) ? $_POST['chkbox'] : array();
//print_r($_POST);
//echo "<br>";

	// update the details in the database.
	foreach($checkbox as $ky => $v){
		//echo "Checkbox $ky: $v<br>";
		$jid[] = $v;
		$jobs .= "id = $v OR ";
	}
	$jobs = substr($jobs, 0, -4);
	//echo "Selected jobs: $jobs<br>";
	//print_r($jid); 
	
	
	if($doaction == "Create Job"){
	
			if(count($checkbox)>0){
			
				foreach($checkbox as $index=>$val){
									
						$date = date("d/m/Y");	
						$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
						
						// get property id
						$j_sql = mysql_query("
							SELECT *
							FROM `jobs`
							WHERE `id` = {$val}
						");
						$j = mysql_fetch_array($j_sql);
						$property_id = $j['property_id'];
					
						//$job_type = determinePendingJobType($property_id);
						
						// if alarm is 240v and expiry is current year, changed job type to 240v rebook
						$jalarm_sql = mysql_query("
							SELECT *
							FROM `alarm`
							WHERE `job_id` = {$val}
							AND `expiry` <= '".date("Y")."'
							AND ( 
								`alarm_power_id` = 2
								OR `alarm_power_id` = 4
							)
						");
						if(mysql_num_rows($jalarm_sql)>0){
							$job_type = '240v Rebook';
						}else{
							$job_type = 'Yearly Maintenance';
						}

						$Query = "UPDATE jobs SET 
					  						
						status='To Be Booked', job_type='{$job_type}', auto_renew=2 WHERE `status` = 'Pending' AND property_id = {$property_id} AND `id` = {$val};";

						mysql_query ($Query);
						
						$job_id = $val;
						
						//echo "<br />";
						
						// get service name
						$ajt_sql = mysql_query("
							SELECT `type`
							FROM `alarm_job_type` 
							WHERE `id` = {$j['service']}
						");
						$ajt = mysql_fetch_array($ajt_sql);
						
						// add logs
						$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
						VALUES (".$property_id.", '{$staff_id}', '{$ajt['type']} Service Renewed', 'By SATS', '".date('Y-m-d H:i:s')."')";
		
						mysql_query($insertLogQuery);
						
						
						
						// insert job logs
						mysql_query("
							INSERT INTO 
							`job_log` (
								`contact_type`,
								`eventdate`,
								`comments`,
								`job_id`, 
								`staff_id`
							) 
							VALUES (
								'Job Pending',
								'" . date('Y-m-d') . "',
								'Job Created on ".date('d/m/Y')." by {$staff_name}', 
								'{$job_id}',
								'{$staff_id}'
							)
						");

						
				
				}
				
				$success_msg =  "Jobs has been successfully updated";
			
			}else{
			
				$error_msg = "No job selected";
			
			}
			
		 	
	
	}
	

	elseif($doaction == "No Longer Manage"){
		
			$date = date("d/m/Y");
			$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
			$nlm_chk_flag = 0;
			$nlm_prop_arr = [];
			
			if(count($checkbox)>0){
			
			
				foreach($checkbox as $index=>$val){
			
					// get property id
					$j_sql = mysql_query("
						SELECT *, 
							p.`address_1` AS p_address_1, 
							p.`address_2` AS p_address_2, 
							p.`address_3` AS p_address_3,
							p.`state` AS p_state,
							p.`postcode` AS p_postcode
						FROM `jobs` AS j 
						LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
						WHERE j.`id` = {$val}
					");
					$j = mysql_fetch_array($j_sql);
					
					$property_id = $j['property_id'];
					$prop_name = "{$j['p_address_1']} {$j['p_address_2']}, {$j['p_address_3']} {$j['p_state']} {$j['p_postcode']}";
					
					if( $crm->NLMjobStatusCheck($property_id)==true ){
						
						$nlm_chk_flag = 1;
						$nlm_prop_arr[] = array(
							'prop_id' => $property_id,
							'prop_name' => $prop_name
						);
						
					}else{
						
						// check if property has money owing and needs to verify paid
						$update_verify_paid_marker_str = '';
						if( $crm->check_verify_paid($property_id) == true ){
							$update_verify_paid_marker_str = '`nlm_display` = 1,';
						}
						
						$Query = "
							UPDATE property as p 
							SET 
								p.deleted=1, 
								p.`agency_deleted`=0, 
								p.`deleted_date` = '".date('Y-m-d H:i:s')."', 
								p.booking_comments = 'No longer managed as of $date - by SATS.', 
								`is_nlm` = 1, 
								`nlm_timestamp` = '".date('Y-m-d H:i:s')."',
								{$update_verify_paid_marker_str}
								`nlm_by_sats_staff` = '{$staff_id}'
							WHERE p.property_id = {$property_id};
						";
						mysql_query ($Query);

						//echo "<br />";
						
						$query2 = "
						UPDATE jobs 
							SET 
								`status` = 'Cancelled',
								`cancelled_date` = '".date('Y-m-d')."',
								`comments` = 'This property was marked No Longer Managed by SATS on ".date("d/m/Y")." and all jobs cancelled'
						WHERE `status` != 'Completed' 
						AND property_id = {$property_id}
						";
						mysql_query($query2);
						
						/*
						// update status change
						mysql_query("
						UPDATE `property_services`
						SET `status_changed` = '".date("Y-m-d H:i:s")."'
						WHERE `property_id` = '{$property_id}'
						");
						*/
						
						// add logs
						$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
						VALUES (".$property_id.", '{$staff_id}', 'No Longer Managed', 'By {$staff_name}', '".date('Y-m-d H:i:s')."')";
						mysql_query($insertLogQuery);
						
						
					}
				
					
				
				}
			
				
				
				
				
				$success_msg = "Property selected is/are no longer managed";
				
			
			
			}else{			
				$error_msg =  "No job selected";			
			}							
			
		
    }else if($doaction == "MOVE to On-Hold"){
		
		if(count($checkbox)>0){
			
			foreach($checkbox as $index=>$val){
								
					$date = date("d/m/Y");	
					$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
					
					// get property id
					$j_sql = mysql_query("
						SELECT *
						FROM `jobs`
						WHERE `id` = {$val}
					");
					$j = mysql_fetch_array($j_sql);
					$property_id = $j['property_id'];
				
					//$job_type = determinePendingJobType($property_id);
					
					// if alarm is 240v and expiry is current year, changed job type to 240v rebook
					$jalarm_sql = mysql_query("
						SELECT *
						FROM `alarm`
						WHERE `job_id` = {$val}
						AND `expiry` <= '".date("Y")."'
						AND ( 
							`alarm_power_id` = 2
							OR `alarm_power_id` = 4
						)
					");
					if(mysql_num_rows($jalarm_sql)>0){
						$job_type = '240v Rebook';
					}else{
						$job_type = 'Yearly Maintenance';
					}

					/*
					$Query = "UPDATE jobs SET 
										
					status='To Be Booked', job_type='{$job_type}', auto_renew=2 WHERE `status` = 'Pending' AND property_id = {$property_id} AND `id` = {$val};";
					*/

					$Query = "UPDATE jobs SET 
										
					status='On Hold', job_type='{$job_type}', auto_renew=2 WHERE `status` = 'Pending' AND property_id = {$property_id} AND `id` = {$val};";
					
					
					mysql_query ($Query);
					
					$job_id = $val;
					
					//echo "<br />";
					
					// get service name
					$ajt_sql = mysql_query("
						SELECT `type`
						FROM `alarm_job_type` 
						WHERE `id` = {$j['service']}
					");
					$ajt = mysql_fetch_array($ajt_sql);
					
					// add logs
					$insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date) 
					VALUES (".$property_id.", '{$staff_id}', '{$ajt['type']} Service Auto Renewed', 'By SATS', '".date('Y-m-d H:i:s')."')";

					mysql_query($insertLogQuery);
					
					
					
					// insert job logs
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
							'Service Due',
							'" . date('Y-m-d') . "',
							'Job Auto Created on ".date('d/m/Y')." by SATS', 
							'{$job_id}',
							'{$staff_id}',
							'".date("H:i")."'
						)
					");

					
			
			}
			
			$success_msg =  "Jobs has been successfully updated";
		
		}else{
		
			$error_msg = "No job selected";
		
		}
		
	}
	
	
	
	

	if( empty($jobs) ){
			$error_msg = "No jobs has been selected, please go back to perform this action again.";
	}    
    
	if($success_msg!=""){
		echo "<div class='success'>
			{$success_msg}<br />
			<a href='/service_due_jobs.php'>Return to Service Due</a>
			</div>";
	}
	
	if($nlm_chk_flag==1){
		
		echo "<div>These Properties cannot be NLM because it has active jobs:";
		if(count($nlm_prop_arr)>0){
			echo "<ul>";
			foreach( $nlm_prop_arr as $prop_data ){
				echo "<li><a href='view_property_details.php?id={$prop_data['prop_id']}'>{$prop_data['prop_name']}</a></li>";
			}
			echo "</ul></div>";
		}		
		
	}
	
	
	if($error_msg!=""){
		echo "<div class='error'>{$error_msg}
		<br />
		<a href='/service_due_jobs.php'>Return to Service Due</a>
		</div>";
	}
	
    
?>

<br />

</form>


    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<!-- end #container --></div>
</body>
</html>
