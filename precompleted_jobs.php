<?php
// IMPORTANT! MUST UPDATE CRON, cron_precomp_move_to_merge_au.php AND cron_precomp_move_to_merge_nz.php
$title = "Pre Completion";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'Pre Completion';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.date';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'');
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,''));


// get tenant number from countries
$ctn_sql = mysql_query("
	SELECT `tenant_number`
	FROM `countries`
	WHERE `country_id` = {$_SESSION['country_default']} 		
");
$ctn = mysql_fetch_array($ctn_sql);




function findBookedWithTenantNumber($job_id){
	
	$sql = mysql_query("
		SELECT j.`property_id`, j.`booked_with`
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		WHERE j.`id` = {$job_id}
	");
	$row = mysql_fetch_array($sql);
	

	$available_tenants_arr = [];
	$has_tenant_email = 0;

	$pt_params = array( 
		'property_id' => $row['property_id'],
		'active' => 1
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		if( $pt_row["tenant_mobile"]!="" && $pt_row['tenant_firstname'] == $row['booked_with'] ){
			$booked_with_tent_num = $pt_row["tenant_mobile"];
			$booked_with_tent_fname = $pt_row['tenant_firstname'];
		}
		
		
	}	
	
	return array(
		'booked_with_tent_num' => $booked_with_tent_num,
		'booked_with_tent_fname' => $booked_with_tent_fname
	);
	
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.jfadeIt{
	opacity: 0.5;
}
</style>



<div id="mainContent">


   
    <div class="sats-middle-cont">
	
	
	
		 <div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title; ?>" href="/precompleted_jobs.php"><strong><?php echo $title; ?></strong></a></li>
			  </ul>
			</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		if($_GET['sms_sent']==1){
			echo '<div class="success">SMS Sent</div>';
		}
		
		if($_GET['rebook']==1){
			echo '<div class="success">Job Rebooked</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
		
			
		
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>
				
				

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
				
				
					

				 
	
					<div class="fl-left">
						<label>Job Type:</label>
						<select name="job_type" style="width: 125px;">
							<option value="">Any</option>
							<?php
							$jt_sql = mysql_query("
								SELECT `job_type` 
								FROM `job_type`
							");
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
				  
					 <?php
					$ajt_sql = mysql_query("
						SELECT `id`, `type`
						FROM `alarm_job_type`
						WHERE `active` = 1
					");
				  ?>
					<div class="fl-left">
						<label>Service:</label>
						<select name="service" style="width: 125px;">
							<option value="">Any</option>
							<?php
							while($ajt=mysql_fetch_array($ajt_sql)){ ?>
								<option <?php echo ($ajt['id']==$service) ? 'selected="selected"':''; ?> value="<?php echo $ajt['id']; ?>" ><?php echo $ajt['type']; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					
			
					
					
				
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'>
					<label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'>
					</div>
					
					<div class='fl-left'>
						<label>&nbsp;</label>
						<button class="submitbtnImg" id="btn_submit" type="submit" style="margin:0;">
							<img class="inner_icon" src="images/search-button.png" />
							Search
						</button>
					</div>

				

				<!-- duplicated filter here -->
				
				
				<h4 style="margin: 18px 0 0 0;">All white jobs will auto process every 15 minutes</h4>
					  
					  
				</td>
				</tr>
			</table>


			
				  
			</form>
			
			
			<?php
			
			/*
			if($_REQUEST['order_by']){
				if($_REQUEST['order_by']=='ASC'){
					$ob = 'DESC';
					$sort_arrow = '<div class="arw-std-up arrow-top-active"></div>';
				}else{
					$ob = 'ASC';
					$sort_arrow = '<div class="arw-std-dwn arrow-dwn-active"></div>';
				}
			}else{
				$sort_arrow = '<div class="arw-std-up"></div>';
				$ob = 'ASC';
			}
			
			// default active
			$active = ($_REQUEST['sort']=="")?'arrow-top-active':'';
			*/
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>
			
			
			

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Age</th>
				<th>
					<div class="tbl-tp-name colorwhite bold">Date</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.date&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.date')?'active':''; ?>"></div>
					</a>
				</th>
				<th>Last YM</th>
				<th>Job Type</th>
				<th>
					<div class="tbl-tp-name colorwhite bold">Price</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.job_price&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.job_price')?'active':''; ?>"></div>
					</a>
				</th>
				<th>
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				<th>Address</th>
				<th>Tech</th>
				<th>DK</th>
				<th>Reason</th>
				<th>Comments</th>
				<th>Job #</th>
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
				<th>&nbsp;</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						
						$row_color = '';
						$reason = '';
						$hide_ck = 0;
						
						/*
						if( 
							$row['job_reason_id']>0 || 
							isAlarmExpiryDatesMatch($row['jid'])==true || 
							isJobZeroPrice_Ym($row['jid'])==true ||
							isJobHasNewAlarm($row['jid'])==true ||
							isPropertyAlarmExpired($row['property_id'])==true
						){
							$row_color = 'yello_mark';
						}else{
							$row_color = '';
						}
						*/
						
						
						
						// Expiry Dates don't match
						if( isAlarmExpiryDatesMatch($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Expiry Dates Don't Match <br />";
						}
						
						// hide for FG: Compass Housing
						if( $row['franchise_groups_id'] != 39 && $_SESSION['country_default'] == 1 ){
							
							// Job is $0 and YM
							if( isJobZeroPrice_Ym($row['jid'])==true ){
								$hide_ck = 1;
								$row_color = 'green_mark';
								$reason .= "Job is $0 and YM <br />";
							}
							
						}
						
						
						// New Alarms Installed
						if( isJobHasNewAlarm($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "New Alarms Installed <br />";
						}

						// if it has repair notes
						if( $row['repair_notes'] != '' ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Repair Notes <br />";
						}
						
						// IC upgraded property not 119 check, exluding CW
						if( $row['jservice'] != 6 && $row['prop_upgraded_to_ic_sa'] == 1 && $row['job_type'] == 'Yearly Maintenance' && $row['job_price'] != 119  ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "IC Job not $119<br />";
						}
						
						// if 240v rebook
						if($row['job_type']=='IC Upgrade'){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Job type can't be IC Upgrade<br />";
						}
						
						// Property has Expired Alarms
						if( isPropertyAlarmExpired($row['jid'],$row['property_id'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Expired Alarms <br />";
						}
						
						// COT FR and LR price must be 0
						if( CotLrFrPriceMustBeZero($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= getJobTypeAbbrv($row['job_type'])." must be $0 <br />";
						}
						
						// If 240v has 0 price
						if( is240vPriceZero($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Check Job Type <br />";
						}
						
						
						// if 240v rebook
						if($row['job_type']=='240v Rebook'){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "240v Rebook <br />";
						}
						
						// If discarded alarm is not equal to new alarm
						if( isMissingAlarms($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Discarded Alarms don't match Installed Alarms <br />";
						}
						
						// If NO alarms, exclude CW
						if( isNoAlarms($row['jid'])==true && $row['jservice']!=6 ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " No installed Alarms <br />";
						}
						
						// If job date is not today
						if( isJobDateNotToday($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Check Job Date <br />";
						}
						
						// If Job notes is present
						$tech_notes_pres_flag = 0;
						if( $row['tech_comments']!='' ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Check Tech notes <br />";
							$tech_notes_pres_flag = 1;
						}												
						
						// if franchise group = private
						if( $row['franchise_groups_id'] == 10 ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Payment Required Before Processing <br />";
						}
						
						
						// If Urgent
						if( $row['urgent_job']==1 ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Urgent or Out of Scope <br />";
						}
						
						//  if SS has any switched that are marked failed
						if( isSSfailed($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Safety Switch Failed <br />";
						}
						
						//  if SS has any switched that are marked failed
						if( $jc->isSafetySwitchServiceTypes($row['jservice'])==true && $row['ss_quantity']=='' ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Safety Switch Quantity is blank<br />";
						}
						
						
						
						// MUST BE THE LAST - not completed due to = job reason
						if( ifDHAAgencies($row['jid'])==true  && $row['ts_completed']==0 ){
							$hide_ck = 1;
							$row_color = 'yello_mark';
							$reason .= " DHA Property <br />";
						}
						
						// MUST BE THE LAST - not completed due to = job reason
						$reason_icon = '';
						if( $row['job_reason_id']>0 && $row['ts_completed']==0 ){
							
							
							
							
							
							
							// if 'no keys at agency' or 'keys dont work' or 'no show' hide checkbox, show red sms icon
							if( $row['job_reason_id']==11 || $row['job_reason_id']==5 || $row['job_reason_id']==1 ){
								$hide_ck = 1;
								$reason_icon .= '<img src="images/red_sms.png" style="position: relative; top: 7px;" /> ';
							}else if( $row['job_reason_id']==2  || isDHAagencies($row['agency_id'])==true ){ // 240v rebook OR DHA agencies
								$hide_ck = 1;
							}else{ // default checkbox state for this if block
								$hide_ck = 0;
							}
							
							
							
							$row_color = 'yello_mark';
							// only show on reason: 'No Longer Managed by Agent' or 'Property Vacant'
							if( $row['job_reason_id']==17 || $row['job_reason_id']==18 ){
								$reason_icon .= '<img src="images/red_phone.png" style="position: relative; top: 7px;" /> ';
							}						
							$reason .= "{$reason_icon}{$row['jr_name']} <br />";
							
						}
						
						
						// if not completed, key access and reason is not 'no keys at agency' (sir Dan says this is the highest priority)
						if( $row['key_access_required']==1 && $row['ts_completed']==0 && $row['job_reason_id']!=11 ){
							$hide_ck = 1;
							$row_color = 'yello_mark';
							//$reason .= "Verify keys have been returned before Rebooking<br />";
						}
						
				?>
						<tr class="body_tr jalign_left <?php echo $row_color; ?>">
							<td>
								<?php
								// Age
								$date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
								$date2=date_create(date('Y-m-d'));
								$diff=date_diff($date1,$date2);
								$age = $diff->format("%r%a");
								echo (((int)$age)!=0)?$age:0;
								?>
							</td>
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							<td><?php echo pcjGetLastYMCompletedDate($row['property_id'],$row['jservice']); ?></td>
							<td>
								<span class="240v_jt_lbl">
								<?php 
									if($row['job_type']=='240v Rebook'){ 
										//$hide_ck = 1;
									?>
										<a class="btn_240v" href="javascript:void(0);"><?php echo getJobTypeAbbrv($row['job_type']); ?></a>
									<?php
									}else{
										echo getJobTypeAbbrv($row['job_type']); 
									}									
								?>
								</span>	
									
									<select class="vw-jb-sel 240v_change_jt" style="display:none; width: 125px;">
										<option value="Once-off" <?php echo ($row['job_type']=='Once-off')?'selected="selected"':''; ?>>Once-off</option>
										<option value="Change of Tenancy" <?php echo ($row['job_type']=='Change of Tenancy')?'selected="selected"':''; ?>>Change of Tenancy</option>
										<option value="Yearly Maintenance" <?php echo ($row['job_type']=='Yearly Maintenance')?'selected="selected"':''; ?>>Yearly Maintenance</option>
										<option value="Fix or Replace" <?php echo ($row['job_type']=='Fix or Replace')?'selected="selected"':''; ?>>Fix or Replace</option>
										<option selected="selected" value="240v Rebook" <?php echo ($row['job_type']=='240v Rebook')?'selected="selected"':''; ?>>240v Rebook</option>
										<option value="Lease Renewal" <?php echo ($row['job_type']=='Lease Renewal')?'selected="selected"':''; ?>>Lease Renewal</option>
									</select>
							</td>
							<td><?php echo '$'.number_format(getJobAmountGrandTotal($row['jid'],$_SESSION['country_default']),2); ?></td>
							<td>
								<?php
								// display icons
								$job_icons_params = array(
									'service_type' => $row['jservice'],
									'job_type' => $row['job_type']
								);
								echo $crm->display_job_icons($job_icons_params);
								?>
							</td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>
							<?php
							
							$tr_sql = mysql_query("
								SELECT * 
								FROM  `tech_run` 
								WHERE `assigned_tech` = {$row['assigned_tech']}
								AND `date` = '{$row['jdate']}'
							");
							$tr = mysql_fetch_array($tr_sql);
							

							$crm_ci_page_precompleted = "tech_run/run_sheet_admin/{$tr['tech_run_id']}";
							$view_tech_url_precompleted = $crm->crm_ci_redirect($crm_ci_page_precompleted);
							?>
								<a href="<?php echo $view_tech_url_precompleted; ?>">
									<?php echo "{$row['FirstName']} ".strtoupper(substr($row['LastName'],0,1))."."; ?>
								</a>
							</td>
							<td><?php echo (($row['door_knock']==1)?'DK':''); ?></td>
							<td><?php echo $reason; ?></td>
							<td>
								<?php 
								if( $tech_notes_pres_flag==1 ){
									echo stripslashes($row['tech_comments']);
								}else{
									echo stripslashes($row['job_reason_comment']); 
								}																
								?>
							</td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							<td>
								<?php

								//echo "job_priority: {$row['job_priority']}";

								// if dha and not completed
								if( isDHAagencies($row['agency_id'])==true && $row['job_reason_id']>0 ){ ?>
									<button type="button" class="submitbtnImg btn_dha_rebook">DHA Rebook</button>
								<?php
								}else{ 
										// no show
										$show_rebook = 0;		
										if( $row['job_reason_id']==1 ){ 
										
											// SMS block
											if( date('Y-m-d',strtotime($row['sms_sent_no_show'])) == date('Y-m-d') ){ // if sms already sent today
												$disabled_txt = 'disabled="disabled"';
												$add_class = 'jfadeIt';
											}else{
												$disabled_txt = '';
												$add_class = '';
											}
											$show_rebook = 1;
										}
										
										// door knock and not completed
										if( $row['door_knock']==1 && $row['job_reason_id']>0 ){
											$show_rebook = 1;
										}

										// if urgent or priority
										//echo "urgent_job: {$row['urgent_job']}<br /><br />";
										//echo "job_priority: {$row['job_priority']}<br /><br />";
										if( $row['urgent_job'] == 1 || $row['job_priority'] == 1  ){

											if( $row_color != '' ){ // only hide checkbox if green or yellow highlight, not white
												$hide_ck = 1;
											}
											

										}else{
											

											// no show
											if( $row['job_reason_id']==1 ){ ?>
												<button type="button" style="margin-bottom: 5px;" <?php echo $disabled_txt; ?> class="blue-btn submitbtnImg btn_no_show_sms <?php echo $add_class; ?>">SMS</button>
											<?php
											}
											
											if( $show_rebook==1 ){ ?>
												<button type="button" class="submitbtnImg btn_no_show_rebook">Rebook</button>
											<?php
											}
																				
											// 240v rebook
											if( $row['job_reason_id']==2 ){  
											?>
												<button type="button" class="submitbtnImg btn_no_show_240v_rebook">240v Rebook</button>
											<?php
											}
										}																			
										
								}	
								
								if( $hide_ck=="" || $hide_ck==0 ){ ?>
									<input type='checkbox' name='chkbox[]' id='' class='chk_pending chkbox' value='<?php echo $row['jid']; ?>' />
								<?php								
								} 															
								?>
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="hid_prop_id" value="<?php echo $row['property_id']; ?>" />
								<?php 
								$bwt_arr = findBookedWithTenantNumber($row['jid']);
								?>
								<input type="hidden" class="booked_with_tent_num" value="<?php echo $bwt_arr['booked_with_tent_num']; ?>" />
								<input type="hidden" class="booked_with_tent_fname" value="<?php echo $bwt_arr['booked_with_tent_fname']; ?>" />
								<?php
								// private FG
								if( $crm->getAgencyPrivateFranchiseGroups($row['franchise_groups_id']) == true ){ 
									$landlord_txt = 'your landlord';
								}else{
									$landlord_txt = 'your agency';
								}

								// no show sms template
								$sms_type = 4;
								$sms_temp_params = array(
									'sms_type' => $sms_type,
									'tenant_number' => $ctn['tenant_number'],
									'landlord_txt' => $landlord_txt			
								);
								$no_show_sms_temp = $crm->getSMStemplate($sms_temp_params);
								?>
								<input type="hidden" class="sms_message" value="<?php echo $no_show_sms_temp; ?>" />
							</td>
							<td>
							<?php
							echo ($row['completed_timestamp']!="")?date("H:i",strtotime($row['completed_timestamp'])):'';
							?>
							</td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="14" align="left">Empty</td>
				<?php
				}
				?>
				
		</table>	
		
		<?php
									
		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>

		<div style="margin-top: 15px; float: right; display:none;" id="rebook_div">
			<button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">
				<img class="inner_icon" src="images/240v-rebook.png">
				Create 240v Rebook
			</button>
			<button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">
				<img class="inner_icon" src="images/rebook.png">
				Create Rebook
			</button>
			<button type="button" id="btn_move_to_merged" class="submitbtnImg" style="background-color:green">
				<img class="inner_icon" src="images/entry-button.png">
				Move to Merged
			</button>			
		</div>
		
	</div>
</div>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	
	// inline rebook 
	jQuery(".btn_no_show_rebook").click(function(){
		
		var obj = jQuery(this);
		var hid_job_id = obj.parents("tr:first").find(".hid_job_id").val();
		var job_id = new Array();
		
		job_id.push(hid_job_id);
		
		if( confirm('Are you sure you want to create a Rebook?') ){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 0
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php?rebook=1";
			});	
			
		} 
		
		
	});
	
	
	jQuery(".btn_no_show_240v_rebook").click(function(){

		var obj = jQuery(this);
		var hid_job_id = obj.parents("tr:first").find(".hid_job_id").val();
		var job_id = new Array();
		
		job_id.push(hid_job_id);
		
		if(confirm("Are you sure you want to create a 240v Rebook?")==true){
					
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 1
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php?rebook=1";
			});	
						
		}
		
	});
	
	
	jQuery(".btn_dha_rebook").click(function(){

		var obj = jQuery(this);
		var hid_job_id = obj.parents("tr:first").find(".hid_job_id").val();
		var job_id = new Array();
		
		job_id.push(hid_job_id);
		
		if(confirm("Are you sure you want to create a DHA Rebook?")==true){
					
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					isDHA: 1 
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php?rebook=1";
			});	
						
		}
		
	});
	
	
	
	// send sms script
	jQuery(".btn_no_show_sms").click(function(){
		
		var obj = jQuery(this);
		var tenant_mobile = obj.parents("tr:first").find(".booked_with_tent_num").val();
		var sms_message = obj.parents("tr:first").find(".sms_message").val();
		var job_id = obj.parents("tr:first").find(".hid_job_id").val();
		var property_id = obj.parents("tr:first").find(".hid_prop_id").val();
		var sms_type = 4 // No Show 
		var sms_sent_to_tenant = obj.parents("tr:first").find(".booked_with_tent_fname").val();
		

		if( confirm('Are you sure you want to send SMS?') ){
			
			// invoke ajax
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_sms.php",
				data: { 
					property_id: property_id,
					job_id: job_id,
					tenant_mobile: tenant_mobile,
					sms_message: sms_message,
					jr_no_show: 1,
					sms_type: sms_type,
					sms_sent_to_tenant: sms_sent_to_tenant
				}
			}).done(function( ret ){
				
				//console.log(ret);
				jQuery("#load-screen").hide();
				window.location="/precompleted_jobs.php?sms_sent=1";
				
			});	
			
		}
		
		
	});	
	
	
	
	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	
	// REBOOKS
	// 240v
	jQuery("#btn_create_240v_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".chkbox:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 1
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php";
			});				
			
		}
		
	});
	
	// rebook
	jQuery("#btn_create_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".chkbox:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 0
				}
			}).done(function( ret ){
				window.location="/precompleted_jobs.php";
			});				
			
		}
		
	});
	
	// merged certificate
	jQuery("#btn_move_to_merged").click(function(){
		
		if(confirm("Are you sure you want to move jobs to Merged Certificates?")==true){
			
			var job_id = new Array();
			var has_yellow_mark = 0;
			jQuery(".chkbox:checked").each(function(){
				if(jQuery(this).parents("tr:first").hasClass("yello_mark")==true){
					has_yellow_mark = 1;
				}else{
					job_id.push(jQuery(this).val());
				}
				
			});
			
			if(has_yellow_mark==0){
				
				jQuery.ajax({
					type: "POST",
					url: "ajax_move_to_merged.php",
					data: { 
						job_id: job_id,
						is_240v: 0
					}
				}).done(function( ret ){
					window.location="/precompleted_jobs.php";
				});	
				
			}else{
				alert("Yellow highlighted row canot be moved to merged");
			}
						
			
		}
		
	});
	
	
	
	
	
	// toggle 240v job type dropdown
	jQuery(".btn_240v").click(function(){
		
		jQuery(this).parents("tr:first").find(".240v_jt_lbl").toggle();
		jQuery(this).parents("tr:first").find(".240v_change_jt").toggle();
		
	});
	
	// update 240v job type
	jQuery(".240v_change_jt").change(function(){
		
		var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
		var job_type = jQuery(this).val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_job_type.php",
			data: { 
				job_id: job_id,
				job_type: job_type
			}
		}).done(function( ret ){
			window.location="/precompleted_jobs.php";
		});	
		
	});
	
	
	// check all toggle
	jQuery("#maps_check_all").click(function(){
		
		if(jQuery(this).prop("checked")==true){
			jQuery(".chkbox").prop("checked",true);
			jQuery("#rebook_div").show();
		}else{
			jQuery(".chkbox").prop("checked",false);
			jQuery("#rebook_div").hide();
		}
	  
	});
	
	// toggle hide/show remove button
	jQuery(".chkbox").click(function(){

	  var chked = jQuery(".chkbox:checked").length;
	  
	  console.log(chked);
	  
	  if(chked>0){
		jQuery("#rebook_div").show();
	  }else{
		jQuery("#rebook_div").hide();
	  }

	});
	
});
</script>
</body>
</html>