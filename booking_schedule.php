<?php

$title = "Booking Schedule";
include('inc/init.php');
include('inc/header_homepage.php');
include('inc/menu.php');

$url = 'booking_schedule.php';

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$arr = getHomeTotals(); 
/*
// check if STR if all unhidden jobs have phone call
function displayGreenPhoneFromSTR($tr_id,$show_hidden){
	
	$job_count = 0;
	$green_phone_count = 0;
	$hideChk = 0;
	
	// Initiate tech run class
	//$tr_class = new Tech_Run_Class();

	//$params = array('country_id'=>$_SESSION['country_default']);
	//$jr_list2 = $tr_class->getTechRunRows($tr_id,$params);
	
	$jr_list2 = getTechRunRows($tr_id,$_SESSION['country_default']);
	while( $row = mysql_fetch_array($jr_list2) ){
	 
		$hiddenText = "";
		$showRow = 1;
		$isUnavailable = 0;
	 
		if( $row['row_id_type'] == 'job_id' ){
			
			$jr_sql = getJobRowData($row['row_id'],$_SESSION['country_default']);
			$row2 = mysql_fetch_array($jr_sql);
			
			$job_count++;
					
			$chk_logs_str = "
				SELECT *
				FROM job_log j 
				LEFT JOIN staff_accounts s ON s.StaffID = j.staff_id
				WHERE j.`job_id` = {$row2['jid']}
				AND j.`deleted` = 0 
				AND j.`eventdate` = '".date('Y-m-d')."'
				AND j.`contact_type` = 'Phone Call'
			";
			$chk_logs_sql = mysql_query($chk_logs_str);
			$chk_log = mysql_fetch_array($chk_logs_sql);
			
			$current_time = date("Y-m-d H:i:s");
			$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
			$last4hours = date("Y-m-d H:i",strtotime("-3 hours"));
			//echo "Current time: {$current_time }<br />Log Time: {$job_log_time}<br /> last 4 hours: ".$last4hours;
			
			
			if( displayGreenPhone2($row2['jid'],$row2['j_status'])==true ){
				//echo '<img src="/images/green_phone.png" style="cursor: pointer; margin-right: 10px;" title="Phone Call" />';
				$green_phone_count++;
			}		
			
		}
	}
	

	
	if( ($job_count>0) && $green_phone_count == $job_count ){
		return true;
	}else{
		return false;
	}
	
	
}
*/
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
.payment_details_table td {
    padding: 5px;
	border: none;
	text-align: left;
}
.payment_details_table tr {
	border: none;
}
.save_div{
	float:right; 
	margin-bottom: 20px; 
	position: relative; 
	bottom: 85px;
	display:none;
}
.jcolorItRed{
	color: red;
}
.jcolorItGreen{
	color: green;
}
</style>





<div id="mainContent">
    <div class="sats-middle-cont">

			<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title ?></strong></a></li>
			  </ul>
			</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<div class="jtoggle-holder"> 
			<div class="jtoggle_div top-first" style="width: 100%;">
			
			<?php
			// get booking schedule number of days
			  $staff_sql = mysql_query("
				SELECT `booking_schedule_num`
				FROM `staff_accounts`
				WHERE `StaffID` = {$_SESSION['USER_DETAILS']['StaffID']}
			  ");
			  $staff = mysql_fetch_array($staff_sql);
			  
			  $bs_num_days = ($staff['booking_schedule_num']!="")?$staff['booking_schedule_num']:0;
			?>
			
			<div class="toggler-title">
			
				
				
					
					Booking Schedule<span class="toggler-arrow arrow-toggler-bottom"></span>
					-
					Display 
					<select id="sel_num_days">
						<?php
						for( $i = 0; $i<=14; $i++ ){ ?>
							<option value="<?php echo $i; ?>" <?php echo ($bs_num_days==$i)?'selected="selected"':''; ?>><?php echo $i; ?> days</option>				
						<?php	
						}
						?>
					</select>
					
					
					
			
			</div>

			
			<div class="toggler-content" style="padding:0px;">
			<table width="500"  border="0" id="jobtable" class="bs_tbl_main">
			  <tr style="border-left:none; border-right:none;">
				<th class="bg-red bs_day">Day</th>
				<th class="bg-red bs_tech">Technician</th>
				<th class="bg-red bs_area">Area</th>
				<th class="bg-red bs_area">Run Status</th>
				<th class="bg-red bs_area">Booking Staff</th>
				<th class="bg-red bs_acco"><img src="images/white_house.png" /></th>
						
				<!--<th class="bg-red bs_tar divfs4">Target</th>-->
				<th class="bg-red bs_acco jalignCenter">Completed</th>
				<th class="bg-red bs_bkd jalignCenter">Booked</th>
				<th class="bg-red bs_bkd jalignCenter">DK</th>
				<th class="bg-red bs_bill jalignCenter">Billable</th>
			  </tr>
			 
			  <?
			  
			  $num_days = ($_GET['days']!="")?$_GET['days']:$bs_num_days;
			  for($x = 0; $x < $num_days; $x++):
			   $tot_bt = 0;
			   $tot_comp = 0;
			   $tot_bkd = 0;
			   $tot_dk = 0;
			   $tot_bil = 0;
			 ?>
			  <tr style="border-left:none; border-right:none;" <?if($x > 6) echo 'class="secondset"';?>>                
				<td class="bs_day">
					<div>
						<!-- Day -->
						<?php $d = explode("-", dateafter($x, 2)); 
						echo "<a href='" . URL . "view_overall_schedule_day.php?id=&day={$d[0]}&month={$d[1]}&year={$d[2]}'>";
						echo dateafter($x,3) ?></a>
					</div>
				</td>
				 <td colspan="11">
				 
					<?php
					
					 	$jdate = "{$d[2]}-{$d[1]}-{$d[0]}";
					 
		
						 // Tech Run
						$trt_sql = mysql_query("
							SELECT * 
							FROM  `tech_run` AS tr 
							LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
							WHERE tr.`date` = '{$jdate}'
							AND tr.`country_id` = {$_SESSION['country_default']}
							ORDER BY tr.`tech_run_id`
						");
						if( mysql_num_rows($trt_sql) ){ ?>
						
							<table class="main-tr bs_tbl_inner">
							<?php
							
								$tech_count=0;
								while( $trt = mysql_fetch_array($trt_sql) ){ 



									// booking staff
									/* disable fo now > (By gherx)
									$cal_sql_str = "
									SELECT c.`calendar_id`, c.`region`, c.`details`, c.`booking_staff`, c.`accomodation`, sa.`FirstName`, sa.`LastName`
									FROM `calendar` AS c
									LEFT JOIN staff_accounts AS sa ON c.`booking_staff` = sa.`StaffID`
									WHERE c.`staff_id` = {$trt['assigned_tech']}
									AND c.`country_id` = {$_SESSION['country_default']}
									AND ('{$jdate}' BETWEEN c.`date_start` AND c.`date_finish`)
									";
									*/
									// (By gherx) > duplicated query from above but with changes for date_start and date_finish filter
									$cal_sql_str = "
									SELECT c.`calendar_id`, c.`region`, c.`details`, c.`booking_staff`, c.`accomodation`, sa.`FirstName`, sa.`LastName`
									FROM `calendar` AS c
									LEFT JOIN staff_accounts AS sa ON c.`booking_staff` = sa.`StaffID`
									WHERE c.`staff_id` = {$trt['assigned_tech']}
									AND c.`country_id` = {$_SESSION['country_default']}
									AND (c.`date_start`  >= '{$jdate}' AND c.`date_finish` <= '{$jdate}')
									";
									$cal_sql = mysql_query($cal_sql_str);
									$cal = mysql_fetch_array($cal_sql);

									
									// completed
									$completed_sql = mysql_query("
									SELECT count( j.`id` ) AS jcount
									FROM `jobs` AS j
									LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
									LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
									WHERE j.`assigned_tech` ={$trt['assigned_tech']}
									AND j.`del_job` = 0
									AND p.`deleted` =0
									AND a.`status` = 'active'
																		
									AND j.`date` = '{$jdate}'									
									AND a.`country_id` = {$_SESSION['country_default']}

									AND j.`ts_completed` = 1
									");
									$completed = mysql_fetch_array($completed_sql);

									
									// booked
									$booked_sql = mysql_query("
									SELECT count( j.`id` ) AS jcount
									FROM `jobs` AS j
									LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
									LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
									WHERE j.`assigned_tech` ={$trt['assigned_tech']}
									AND j.`del_job` = 0
									AND p.`deleted` =0
									AND a.`status` = 'active'
									
									AND j.`status` = 'Booked'
									AND j.`date` = '{$jdate}'
									AND a.`country_id` = {$_SESSION['country_default']}
									AND j.`door_knock` = 0								
									");
									$booked = mysql_fetch_array($booked_sql);

									
									// DK (Door Knock)
									$dk_sql = mysql_query("
									SELECT count( j.`id` ) AS jcount
									FROM `jobs` AS j
									LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
									LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
									WHERE j.`assigned_tech` ={$trt['assigned_tech']}
									AND j.`del_job` = 0
									AND p.`deleted` =0
									AND a.`status` = 'active'
									AND j.`date` = '{$jdate}'
									AND a.`country_id` = {$_SESSION['country_default']}
									AND j.`door_knock` = 1			
									AND (
										j.`status` = 'Booked' OR
										j.`status` = 'To Be Booked'
									)																							
									");
									$dk = mysql_fetch_array($dk_sql);

									
									// billable
									$billable_sql = mysql_query("
									SELECT count( j.`id` ) AS jcount
									FROM `jobs` AS j
									LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
									LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
									WHERE j.`assigned_tech` ={$trt['assigned_tech']}
									AND j.`del_job` = 0
									AND p.`deleted` =0
									AND a.`status` = 'active'
							
									AND j.`status` = 'Booked'								
									AND j.`date` = '{$jdate}'																
									AND a.`country_id` = {$_SESSION['country_default']}
									AND j.`door_knock` = 0

									AND j.`job_price` > 0	
									");
									$bil = mysql_fetch_array($billable_sql);
									
									
									$tr_color = "";
									$run_status_txt = '';
									
									
									
									
									// light blue
									if($trt['run_set']==1){
										$tr_color = 'background-color: LightBlue !important;';
										$run_status_txt = 'Needs to be Coloured';
									}
									
									// yellow
									if($trt['run_coloured']==1){
										$tr_color = 'background-color: #c4a0de !important;';
										$run_status_txt = 'Coloured - Please Review';
									}
									
									// no color but have icon
									if($trt['ready_to_book']==1){
										$tr_color = '';
										$str_icon_color = 'tech_run_icon_green.png';
										$run_status_txt = '1st Call Over';
									}else{
										$str_icon_color = 'tech_run_icon.png';
									}
									
									// yellow
									if($trt['first_call_over_done']==1){
										$tr_color = 'background-color: #f7d708 !important;';
										$run_status_txt = '1st Call Done - Please Review';
									}
									
									// no color
									if($trt['run_reviewed']==1){
										$tr_color = '';
										$run_status_txt = '2nd Call Over';
									}
									
							
									// yellow - 2nd Call Over Done
									if($trt['finished_booking']==1){
										$tr_color = 'background-color: #f7d708 !important;';
										$run_status_txt = '	2nd Call Done - Please Review';
									}
									
									// yellow - Additional Call Over
									if($trt['additional_call_over']==1){	
										$tr_color = '';
										$run_status_txt = 'Extra Call Over';
									}
									
									// yellow - Additional Call Over Done
									if($trt['additional_call_over_done']==1){
										$tr_color = 'background-color: #f7d708 !important;';
										$run_status_txt = 'Extra Call Done - Please Review';
									}

									// orange - ready to map
									if($trt['ready_to_map']==1){
										$tr_color = 'background-color: #0198E1 !important;';
										$run_status_txt = 'Run Ready to Map - Please Review';
									}
									
									// orange - run mapped
									if($trt['run_complete']==1){
										$tr_color = 'background-color: #ff9e00 !important;';
										$run_status_txt = 'Booked & Mapped';
									}									
									
									// green
									if($trt['no_more_jobs']==1){
										$tr_color = 'background-color: #9ccf31 !important;';
										$run_status_txt = 'Booked & Mapped FULL';
									}
									
									
								?>
									<tr class="nogreen" style="border:none !important; <?php echo $tr_color; ?>">
										<!-- Technician, STR and TDS link -->
										<td class="bs_tech dvf">
											<?php
											$crm_ci_page = "tech_run/run_sheet_admin/{$trt['tech_run_id']}";
											$view_tech_url = $crm->crm_ci_redirect($crm_ci_page);
											?>
											<a href="<?php echo $view_tech_url; ?>" target="_blank" style="float: left; margin-right: 4px;">
												<?php echo "{$trt['FirstName']} ".substr($trt['LastName'],0,1).'.'; ?>
											</a>
											<a href="/set_tech_run.php?tr_id=<?php echo $trt['tech_run_id']; ?>" style="float: left;">
												<img src="/images/<?php echo $str_icon_color; ?>" />
											</a>				
											<?php
											$tech_count++;
											?>
											<br />
										</td>
										<!-- Area -->
										<td class="bs_area dvf2">
											
											<!--
											<?php 
												$ci_cal_edit_redirect_link =  "/calendar/view_tech_calendar?popup=1&calendar_id={$cal['calendar_id']}&staff_id={$trt['assigned_tech']}";
											?>

											
											<a target="_blank" href='<?php echo $crm->crm_ci_redirect($ci_cal_edit_redirect_link); ?>'>
												<?php echo $cal['region']; ?>
											</a>
											
											<br /><br />

											-->
											
											<a class="inlineFB" data-calendar_id="<?php echo $cal['calendar_id']; ?>" href="#calendar_edit_fb">
												<?php echo $cal['region']; ?>
											</a>

										</td>
										<!-- Run Status -->
										<td class="bs_acco"><?php echo $run_status_txt; ?></td>
										<!-- Booking Staff -->
										<td class="bs_acco">
											<?php 
											echo $crm->formatStaffName($cal['FirstName'],$cal['LastName']);											
											/*
											if( displayGreenPhoneFromSTR($trt['tech_run_id'],$trt['show_hidden'])==true ){ ?>
												<img src="/images/green_phone.png" style="height: 16px" title="Phone Call" />
											<?php	
											}
											*/
											?>
										</td>
										<!-- Accomodation -->
										<td class="bs_acco">
											<?php 
											if($cal['accomodation']==1){ 															
												echo '<img src="images/green_house.png" />';																	
											}else if($cal['accomodation']!="" && $cal['accomodation']==0){
												echo '<img src="images/red_house.png" />';
											}else if($cal['accomodation']==2){
												echo '<img src="images/orange_house.png" />';
											}
											?>
										</td>
										
										<!-- Completed -->
										<td class="bs_bkd jalignCenter">
											<?php echo $completed['jcount']; ?>
										</td>
										<!-- Booked -->
										<td class="bs_bkd jalignCenter">								
											<?php
											if( $booked['jcount']==0 ){ ?>
												<img title="Booked" class="booked_icon" style="width: 17px;" src="/images/check_icon2.png" />
											<?php
											}else{
												echo $booked['jcount'];
											}
											?>
										</td>
										<!-- DK -->
										<td class="bs_bkd jalignCenter">
											<?php echo ($dk['jcount']>0)?$dk['jcount']:''; ?>
										</td>
										<!-- Billable -->
										<td class="bs_bill jalignCenter">
											<?php echo ($bil['jcount']>0)?$bil['jcount']:''; ?>
										</td>
									</tr>
									<?php			    					
								   $tot_comp += $completed['jcount'];
								   $tot_bkd += $booked['jcount'];
								   $tot_dk += $dk['jcount'];
								   $tot_bil += $bil['jcount'];
								   }
								   if( $tot_bt!=0 || $tot_comp!=0 || $tot_bkd!=0 || $tot_dk!=0 || $tot_bil!=0 ){ ?>
									<tr>
									<td colspan="4" style="font-size: 12px;"><?php echo $tech_count; ?> Technicians</td>	
									<td style="text-align: center !important">&nbsp;</strong></td>	
								
									<td class="jalignCenter"><?php echo $tot_comp; ?> <div class="div_avg">(<?php echo floor($tot_comp/$tech_count); ?> Avg)</div></td>
									<td class="jalignCenter"><?php echo $tot_bkd; ?> <div class="div_avg">(<?php echo floor($tot_bkd/$tech_count); ?> Avg)</div></td>
									<td class="jalignCenter"><?php echo $tot_dk; ?> <div class="div_avg">(<?php echo floor($tot_dk/$tech_count); ?> Avg)</div></td>
									<td class="jalignCenter"><?php echo $tot_bil; ?> <div class="div_avg">(<?php echo floor($tot_bil/$tech_count); ?> Avg)</div></td>
								   </tr>
								   <?php
								   }
								   ?>
							</table>
						
						<?php
						}
						
					
					
					?>
					
				 </td>         
			  </tr>
			  <? endfor; ?>
			
			</table>
			<?php
			if($_GET['days']){
				$disp_btn_txt = 'Display 7 Days Only'; 
				$disp_btn_param = '';		
			}else{
				$disp_btn_txt = 'Display Next 7 Days'; 
				$next_num_days = 7+$bs_num_days;
				$disp_btn_param = "?days={$next_num_days}";
			}
			?>
			
			<div style="padding: 13px;">
				<a href="/booking_schedule.php<?php echo $disp_btn_param; ?>" id="show_next7" class="submitbtnImg colorwhite" style="font-size: 12px;"><?php echo $disp_btn_txt ; ?></a>
				<a href="/booking_schedule.php?days=<?php echo 14+$bs_num_days; ?>" id="show_next7" class="submitbtnImg colorwhite" style="font-size: 12px;">Display Next 14 Days</a>
			</div>

			</div>
		</div>

		<div class="jtoggle_div top-second" style="display: none;">
			<div class="toggler-title">Jobs Breakdown<span class="toggler-arrow arrow-toggler-bottom"></span></div>
			<div class="toggler-content"><div id="chartContainer" style="height: 300px; width: 100%;"></div>
		</div>
		</div>
		</div>

		
	</div>
</div>

<br class="clearfloat" />

<?php
$staff_sql_str = "
SELECT 
	sa.`StaffID`, 
	sa.`FirstName`, 
	sa.`LastName`
FROM `staff_accounts` as sa
WHERE sa.`active` = 1
AND sa.`Deleted` = 0
ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
";
$staff_sql = mysql_query($staff_sql_str);
?>
<div style="display:none;">

	<div id="calendar_edit_fb">

		<h3>Calendar Entry Details</h3>
		<table id="calendar_edit_table" class="table">

			<tr>
				<th>Staff</th>
				<td>
					<select id="staff_id" name="staff_id" class="form-control">
						<option value="">---</option>   
						<?php
						while( $staff_row = mysql_fetch_object($staff_sql) ){ ?>
							<option value="<?php echo $staff_row->StaffID ?>"><?php echo  "{$staff_row->FirstName} {$staff_row->LastName}"; ?></option>
						<?php
						}
						?>
					</select> 
				</td>
			</tr>

			<tr>
				<th>Start Date</th>
				<td>					
					<input id="start_date" class="datepicker" type="text" />
				</td>
			</tr>

			<tr>
				<th>Start Time</th>
				<td>
					<input id="start_time" type="text" />
				</td>
			</tr>

			<tr>
				<th>Finish Date</th>
				<td>					
					<input id="finish_date" class="datepicker" type="text" />  
				</td>
			</tr>

			<tr>
				<th>Finish Time</th>
				<td>
					<input id="finish_time" type="text" />
				</td>
			</tr>

			<tr>
				<th>Region / Type of Leave</th>
				<td>
					<input type="text" class="form-control" id="leave_type" />
				</td>
			</tr>

			<tr>
				<th>Leave</th>
				<td>
					<input type="checkbox" id="marked_as_leave" value="1"  />
				</td>
			</tr>

			<?php
			$staff_sql_str = "
			SELECT 
				sa.`StaffID`, 
				sa.`FirstName`, 
				sa.`LastName`
			FROM `staff_accounts` as sa
			WHERE sa.`active` = 1
			AND sa.`Deleted` = 0
			ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
			";
			$staff_sql = mysql_query($staff_sql_str);
			?>
			<tr>
				<th>Booking Staff</th>
				<td>
					<select id="booking_staff" name="booking_staff" class="form-control">
						<option value="">---</option>   
						<?php
						while( $staff_row = mysql_fetch_object($staff_sql) ){ ?>
							<option value="<?php echo $staff_row->StaffID ?>"><?php echo  "{$staff_row->FirstName} {$staff_row->LastName}"; ?></option>
						<?php
						}
						?>
					</select> 
				</td>
			</tr>

			<tr>
				<th>Details</th>
				<td>
					<textarea id="calendar_details" class="form-control"></textarea>
				</td>
			</tr>

			<tr>			
				<td colspan="2">
					<ul>
						<li><input name="accomodation" class="accomodation_radio" type="radio" value="" checked>&nbsp;No Accomodation</li>
						<li><input name="accomodation" class="accomodation_radio" type="radio" value="0">&nbsp;Accommodation Required</li>
						<li><input name="accomodation" class="accomodation_radio" type="radio" value="2">&nbsp;Accommodation Pending</li>
						<li><input name="accomodation" class="accomodation_radio" type="radio" value="1">&nbsp;Accommodation Booked</li>
					</ul>  
				</td>
			</tr>

			<?php
			$acco_sql_str = "
			SELECT 
				`accomodation_id`, 
				`name`
			FROM `accomodation`			
			ORDER BY `name` ASC
			";
			$acco_sql = mysql_query($acco_sql_str);
			?>
			<tr>
				<th class="accomodation_dp_tr">Accomodation</th>
				<td class="accomodation_dp_tr">
					<select id="accomodation_dp" name="accomodation_dp" class="form-control">
						<option value="">---</option>   
						<?php
						while( $acco_row = mysql_fetch_object($acco_sql) ){ ?>
							<option value="<?php echo $acco_row->accomodation_id; ?>"><?php echo  $acco_row->name; ?></option>
						<?php
						}
						?>
					</select> 
				</td>
			</tr>


		</table>	

		<input type="hidden" id="calendar_id" />

		<div style="text-align:center;">
			<button type="button" id="update_calendar_btn" class="submitbtnImg">Update</button>	
		</div>
			

	</div>

</div>
 

<style>
.bs_tbl_main tr th,
.bs_tbl_inner tr td{
	width: 10%
}
.jalignCenter{
	text-align: center !important;
}
#calendar_edit_fb{
	text-align: left;	
}
#calendar_edit_table{
	margin-bottom: 5px;
}
#calendar_edit_table li{
	list-style-type: none;
}
.accomodation_dp_tr{
	visibility: hidden;
}
</style>
<script>
function clear_fancybox(){

	var fb_div = jQuery("#calendar_edit_fb");

	fb_div.find("#staff_id").val('');
	fb_div.find("#start_date").val('');
	fb_div.find("#start_time").val('');
	fb_div.find("#finish_date").val('');
	fb_div.find("#finish_time").val('');			
	fb_div.find("#leave_type").val('');
	fb_div.find("#marked_as_leave").prop("checked",false);
	fb_div.find("#calendar_details").val('');
	fb_div.find("#booking_staff").val('');			
	fb_div.find(".accomodation_radio:eq(0)").prop("checked",true);
	fb_div.find(".accomodation_dp_tr").css("visibility","hidden");
	fb_div.find("#accomodation_dp").val('');
	fb_div.find("#calendar_id").val('');

}

jQuery(document).ready(function(){

	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });

	// mark run complete 
	jQuery(".run_status").click(function(){
		
		var run_type = jQuery(this).val();
		var status = (jQuery(this).prop("checked")==true)?1:0;
		var tr_id = jQuery(this).attr("data-tr_id");
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_tech_run_update_run_status.php",
			data: { 
				run_type: run_type,
				status: status,
				tech_run_id: tr_id
			}
		}).done(function( ret ) {
			//window.location="/set_tech_run.php?tr_id=<?php echo $tr_id; ?>";
		});	
		
	});



	// KMS for sales
	<?php 
	if( $user_type == 5 ){ ?>

		jQuery("#update_kms").click(function(){
				
			var vehicles_id = jQuery(this).parents("#kms_div").find("#vehicles_id").val();
			var kms = jQuery(this).parents("#kms_div").find("#kms").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_add_kms.php",
				data: { 
					kms: kms,
					vehicles_id: vehicles_id
				}
			}).done(function( ret ) {
				//window.location="/main.php";
			});
			
		});
		
	<?php	
	}
	?>
		


	// disable link for non default countries 
	jQuery(".disable_link").bind('click', function(e){
		e.preventDefault();
	});



	// run country time
	setInterval(function(){ 
	// alert("Hello");
	var sec = new Date().getSeconds();
	//console.log('Seconds: '+sec);
	if( sec == 59 ){
		//console.log('a minute has passed');
		// javascript (client side) 
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_country_time.php",					
			dataType: 'json'
		}).done(function(ret){
			// do something
			jQuery("#main_country_time #country_time_au_qld").html(ret.au_qld);
			jQuery("#main_country_time #country_time_au_nsw").html(ret.au_nsw);
			jQuery("#main_country_time #country_time_au_sa").html(ret.au_sa);
			jQuery("#main_country_time #country_time_au_vic").html(ret.au_vic);
			jQuery("#main_country_time #country_time_nz").html(ret.nz);
		});
	}

	}, 1000);


	jQuery(".toggler-arrow").toggle(function(){
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").removeClass("arrow-toggler-bottom");
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").addClass("arrow-toggler-top");			
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-content").slideUp();
	},function(){
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").removeClass("arrow-toggler-top");
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-arrow").addClass("arrow-toggler-bottom");
	jQuery(this).parents(".jtoggle_div:first").find(".toggler-content").slideDown();
	});

	jQuery("#sel_num_days").change(function(){

	var bs_num = jQuery(this).val();

	// update booking_schedule_num
	jQuery.ajax({
		type: "POST",
		url: "ajax_update_booking_schedule_selected_days.php",
		data: { 
			staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
			bs_num: bs_num
		}
	}).done(function( ret ) {
		window.location="/booking_schedule.php";
	});	

	});


	// invoke fancybox
	jQuery('.inlineFB').fancybox();

	// mark run complete 
	jQuery(".inlineFB").click(function(){
		
		var calendar_link_dom = jQuery(this)
		var calendar_id = calendar_link_dom.attr("data-calendar_id");
		var fb_div = jQuery("#calendar_edit_fb");

		clear_fancybox(); // clear

		if( calendar_id > 0 ){

			$('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_get_calendar_data.php",
				data: { 
					calendar_id: calendar_id
				},
				dataType: 'json'
			}).done(function( ret ) {

				$('#load-screen').hide();
				var marked_as_leave = ( ret.marked_as_leave == 1 )?true:false;
			
				fb_div.find("#staff_id").val(ret.staff_id);
				fb_div.find("#start_date").val(ret.date_start_dmy);
				fb_div.find("#start_time").val(ret.date_start_time);
				fb_div.find("#finish_date").val(ret.date_finish_dmy);
				fb_div.find("#finish_time").val(ret.date_finish_time);			
				fb_div.find("#leave_type").val(ret.region);
				fb_div.find("#marked_as_leave").prop("checked",marked_as_leave);
				fb_div.find("#calendar_details").val(ret.details);
				fb_div.find("#booking_staff").val(ret.booking_staff);			
				fb_div.find(".accomodation_radio").each(function(){

					var accomodation_radio_dom =  jQuery(this);
					var accomodation = accomodation_radio_dom.val();
					
					if( accomodation == ret.accomodation ){
						accomodation_radio_dom.prop("checked",true);
					}

				});

				// accomodation booked(1) and pending(1)
				if( ret.accomodation == 1 || ret.accomodation == 2 ){
					fb_div.find(".accomodation_dp_tr").css("visibility","visible");
				}
				fb_div.find("#accomodation_dp").val(ret.accomodation_id);
				fb_div.find("#calendar_id").val(ret.calendar_id);
				
			});	

		}			
		
	});

	// update calendar entry
	jQuery("#update_calendar_btn").click(function(){

		var fb_div = jQuery("#calendar_edit_fb");
		var calendar_id = fb_div.find("#calendar_id").val();

		if( calendar_id > 0 ){
			
			var staff_id = fb_div.find("#staff_id").val();
			var start_date = fb_div.find("#start_date").val();
			var start_time = fb_div.find("#start_time").val();
			var finish_date = fb_div.find("#finish_date").val();
			var finish_time = fb_div.find("#finish_time").val();
			var leave_type = fb_div.find("#leave_type").val();
			var marked_as_leave = ( fb_div.find("#marked_as_leave").prop("checked") == true )?1:0;
			var calendar_details = fb_div.find("#calendar_details").val();
			var accomodation_radio = jQuery(".accomodation_radio:checked").val();
			var booking_staff = fb_div.find("#booking_staff").val();					
			var accomodation_dp = fb_div.find("#accomodation_dp").val();

			$('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_save_calendar_data.php",
				data: { 
					calendar_id: calendar_id,
					staff_id: staff_id,
					start_date: start_date,
					start_time: start_time,
					finish_date: finish_date,
					finish_time: finish_time,
					leave_type: leave_type,
					marked_as_leave: marked_as_leave,
					calendar_details: calendar_details,
					accomodation_radio: accomodation_radio,
					booking_staff: booking_staff,
					accomodation_dp: accomodation_dp
				}
			}).done(function( ret ) {
				
				$('#load-screen').hide();
				alert("Calendar Entry Update Successful!");
				$.fancybox.close();
				
			});	

		}		

	});


	jQuery(".accomodation_radio").click(function(){

		var accomodation_radio_dom = jQuery(this);
		var accomodation_radio = accomodation_radio_dom.val();

		// accomodation booked(1) and pending(1)
		if( accomodation_radio == 1 || accomodation_radio == 2 ){
			jQuery(".accomodation_dp_tr").css("visibility","visible");
		}else{
			jQuery(".accomodation_dp_tr").css("visibility","hidden");
		}

	});	


});
</script>
</body>
</html>