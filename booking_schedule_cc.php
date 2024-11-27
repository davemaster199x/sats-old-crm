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
						for( $i = 0; $i<=9; $i++ ){ ?>
							<option value="<?php echo $i; ?>" <?php echo ($bs_num_days==$i)?'selected="selected"':''; ?>><?php echo $i; ?> days</option>				
						<?php	
						}
						?>
					</select>
					
					
					
			
			</div>

			
			<div class="toggler-content" style="padding:0px;">
			<table width="500"  border="0" id="jobtable" class="bs_tbl_main">
			  <tr style="border-left:none; border-right:none;">
				<th class="bg-red col_day">Day</th>
				<th class="bg-red col_tech">Technician</th>
				<th class="bg-red col_tech">Area</th>
				<th class="bg-red col_rs">Run Status</th>
				<th class="bg-red col_bs">Booking Staff</th>
			  </tr>
			 
			  <?
			  
			  $num_days = ($_GET['days']!="")?$_GET['days']:$bs_num_days;
			  for($x = 0; $x < $num_days; $x++):
			   $tot_bt = 0;
			   $tot_assigned = 0;
			   $tot_bkd = 0;
			   $tot_dk = 0;
			   $tot_bil = 0;
			?>
			  <tr style="border-left:none; border-right:none;" <?if($x > 6) echo 'class="secondset"';?>>                
				<td class="col_day">
					<div>
						<?php $d = explode("-", dateafter($x, 2)); echo "<a href='" . URL . "view_overall_schedule_day.php?id=&day={$d[0]}&month={$d[1]}&year={$d[2]}'>"; echo dateafter($x,3) ?></a>
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
							ORDER BY sa.`FirstName`, sa.`LastName`
						");
						if( mysql_num_rows($trt_sql) ){ ?>
						
							<table class="main-tr bs_tbl_inner">
							<?php
							
								$tech_count=0;
								while( $trt = mysql_fetch_array($trt_sql) ){ 
								
									// booking staff
									$cal_sql_str = "
										SELECT c.`calendar_id`, c.`region`, c.`details`, c.`booking_staff`, sa.`FirstName`, sa.`LastName`
										FROM `calendar` AS c
										LEFT JOIN staff_accounts AS sa ON c.`booking_staff` = sa.`StaffID`
										WHERE c.`staff_id` = {$trt['assigned_tech']}
										AND c.`country_id` = {$_SESSION['country_default']}
										AND ('{$jdate}' BETWEEN c.`date_start` AND c.`date_finish`)
									";
									$cal_sql = mysql_query($cal_sql_str);
									$cal = mysql_fetch_array($cal_sql);
								
									
									
									
									
									$tr_color = "";
									$run_status_txt = '';
									
									
									
									
									// light blue
									if($trt['run_set']==1){
										$tr_color = 'background-color: LightBlue !important;';
										$run_status_txt = 'Needs to be Coloured';
									}
									
									// yellow
									if($trt['run_coloured']==1){
										$tr_color = 'background-color: #f7d708 !important;';
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
									<tr class="nogreen" style="border:none !important; <?php echo $tr_color; ?> <?php //echo (  $jdate == $today && $isStillHaveBookedJobs == false   )?'display:none;':''; ?>">
										<!--  Technician -->
										<td class="col_tech">	
											<?php
											$crm_ci_page = "tech_run/run_sheet_admin/{$trt['tech_run_id']}";
											$view_tech_url = $crm->crm_ci_redirect($crm_ci_page);
											?>										
											<a href="<?php echo $view_tech_url; ?>" style="float: left; margin-right: 4px;">
												<?php echo $crm->formatStaffName($trt['FirstName'],$trt['LastName']); ?>
											</a>
											<a href="/set_tech_run.php?tr_id=<?php echo $trt['tech_run_id']; ?>" style="float: left;">
												<img src="/images/<?php echo $str_icon_color; ?>" />
											</a>
											<?php
											$tech_count++;
											?>
											<br />
										</td>
										<!---  Area --->
										<td class="col_area">
											<?php 
												$ci_cal_edit_redirect_link =  "/calendar/view_tech_calendar?popup=1&calendar_id={$cal['calendar_id']}&staff_id={$trt['assigned_tech']}";
											?>
											<a target="_blank" href='<?php echo $crm->crm_ci_redirect($ci_cal_edit_redirect_link); ?>'>
												<?php echo $cal['region']; ?>
											</a>
										</td>
										<!--  Run Status -->
										<td class="col_rs"><?php echo $run_status_txt; ?></td>
										<!---  Booking Staff --->
										<td class="col_bs">
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
			
			  <?php
			  /*
			$result_nb = mysql_query("SELECT date_updated FROM noticeboard WHERE `country_id` = {$_SESSION['country_default']}", $connection);
			$row_nb = mysql_fetch_row($result_nb);
			$date = date_create($row_nb[0]);
			$notice_timestamp = date_format($date, 'd/m/Y @ H:i');
			
			
			//print_r($_SESSION);
			
			$staff_name = $_SESSION['USER_DETAILS']['FirstName'].' '.$_SESSION['USER_DETAILS']['LastName'];
			
			*/
			
		?>
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

<style>
.bs_tbl_main tr th,
.bs_tbl_inner tr td{
	width: 22.5%;
}
.col_day{
	width: 10% !important;
}
</style>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


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
		location.reload();
	});	

	});


});
</script>
</body>
</html>