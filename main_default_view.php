<div id="time"><?php echo date("l jS F Y"); ?></div>

<!--  <h1 class="style4">Welcome to <?php echo COMPANY_ABBREV_NAME; ?>!</h1>-->

<style>
.divfs, .divfs2, .jgreenrow .dvf, .jgreenrow .dvf2, .nogreen .dvf, .nogreen .dvf2{width: 100px;}
.divfs3, .divfs4, .divfs5, .divfs6{  text-align: center !important; width: 100px;}
.jgreenrow .dvf3, .jgreenrow .dvf4, .jgreenrow .dvf5, .jgreenrow .dvf6{ text-align: center !important; width: 100px;}
.nogreen .dvf3, .nogreen .dvf4, .nogreen .dvf5, .nogreen .dvf6{ text-align: center !important; width: 100px;}

.div_avg{
   font-size: 12px;
   font-style: italic;
   color: red;
}
.jsub_div{
width: 340px;
float: left;
margin: 0 4px;
}
.jsub_div .sats-block{
float:none; width:auto;
}
.block-color-color_purple{
background-color: #9b30ff;
}
.block-color-sass{
background-color: #f15a22;
}
.block-color-sawm{
background-color: #00aeef;
}
.block-color-allocate{
background-color: #ec1acc
}
.alerts_display_chk {
    margin-left: 10px;
}
.future_booked_jobs_div{
	color: white;
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
.block-color-deepblue {
    background-color: #00AEEF;
}
.booked_box_num{
	font-size: 70px;
	float: left;
	margin-left: 20px;
}
.booked_box_lbl{
	float: right;
	margin: 33px 20px 0px 0;
	text-align: right;
	padding: 0 !important;
	font-size: 14px;
}
.sats-block:nth-child(4n) {
    margin-right: 0.5%;
}
.fleft{
	float: left;
}
.precompleted_jobs_tbl{
	text-align: left;
}
.login_icon {
    position: relative;
    top: 3px;
}
#main_country_time .country_time{
	color: #b4151b;
}
#main_country_time{
	display: inline;
	margin-left: 30px;
}
#main_country_time .clock_img{
	margin-right: 5px;
	position: relative;
	top: 3px;
}
.upgrade_brooks_div{
	background-color: #7d49e6;
}
.upgrade_cavius_div{
	background-color: #bf74e8;
}

#popup-box .btn{
	margin:15px;
	padding:10px;
	max-width: 100%;
	background-color: #9b93db;
	color: white;
	border-radius:5px;
	border-color:#9b93db;
}

#edit-goal{
	text-align: center;
	color: white ;
	margin-bottom:50px !important;
}

.jtoggle_div{
	margin-top: 10px;
}

#popup-box{
	width: 500px;
	padding: 10px;
}
.fancybox{
	width: 500px;
	z-index: 9999;
}

.inline-group{
	display:inline-block !important;
}

.inline-group .actual{
	font-size: 3rem;
}

.inline-group .goal{
	font-size: 2rem;
}

</style>
<?php
function get_main_count($param){
	$result = mysql_query("SELECT * FROM main_page_total WHERE name='{$param}'");
	if(mysql_num_rows($result) > 0){
		$row = mysql_fetch_array($result);
		$total = number_format($row['total']);
		$total_goal = number_format($row['total_goal']);
		echo "<div class='inline-group'><span class='actual'>$total</span> <span class='goal'>$total_goal</span></div>";
	} else {
		echo "<div class='inline-group'><span class='actual'>0</span> <span class='goal'>0</span></div>";
	}
}

function findBookedWithTenantNumber($job_id){

	$sql = mysql_query("
		SELECT j.`property_id`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		WHERE j.`id` = {$job_id}
	");
	$row = mysql_fetch_array($sql);


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


// get staff details
$sa_sql = mysql_query("
	SELECT *
	FROM `staff_accounts`
	WHERE `StaffID` ={$_SESSION['USER_DETAILS']['StaffID']}
");
$sa = mysql_fetch_array($sa_sql);

?>
<!--- 4 boxes --->
<div>

	<!-- 1st row -->
	<div class="sats-block block-color-green">
		<a href='<?php echo $crm->crm_ci_redirect('/jobs/to_be_booked'); ?>' target="_blank">
			<div class="booked_box_num">
				<?php get_main_count('to-be-booked'); ?>
			</div>
			<div class='head-info booked_box_lbl'>To Be <br />Booked</div>
		</a>
	</div>

	<div class="sats-block block-color-seagreen">
		<a href='<?php echo $crm->crm_ci_redirect('/jobs/to_be_booked/?&updated_to_240v_rebook=1'); ?>' target="_blank">
			<div class="booked_box_num">
			<?php get_main_count('240v-rebook'); ?>
			</div>
			<div class='head-info booked_box_lbl'>240v  <br />Rebook</div>
		</a>
	</div>

	<div class="sats-block block-color-deeppurple">
		<a href='<?php echo $crm->crm_ci_redirect("/jobs/to_be_booked/?job_type_filter=Fix or Replace&custom_filter=j.status IN ('To Be Booked','Allocate')&job_status_filter=-1"); ?>' target="_blank">
			<div class="booked_box_num">
			<?php get_main_count('fix-or-replace'); ?>
			</div>
			<div class='head-info booked_box_lbl'>Fix and <br />Replace</div>
		</a>
	</div>	

	<div class="sats-block block-color-seagreen is_eo">
		<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/jobs/to_be_booked/?show_is_eo=1")) ?>' target="_blank">
		<div class="booked_box_num">
			<?php get_main_count('electrician-only'); ?>
			</div>
			<div class='head-info booked_box_lbl'>Electrician Only(EO)</div>
		</a>
	</div>
	<div style="clear:both;"></div>

	<!-- 2nd row -->
	<?php
	$day_loop = 1;
	for( $i = 1; $i <= 4; $i++ ){
		$booked_date_ts = strtotime("+{$day_loop} days");
		$booking_day = date('l',$booked_date_ts);
		if( $booking_day == 'Saturday' ){ // +2 days to skip Sunday
			$day_loop += 2;
		}else{
			$day_loop++;
		}
		?>
		<div class="sats-block block-color-deepblue future_booked_jobs_div">
			<div class="booked_box_num">
				<?php get_main_count(date('l',$booked_date_ts)); ?>
			</div>
			<div class='head-info booked_box_lbl'>
				Booked for <br /><?php echo date('l',$booked_date_ts) ?>
			</div>
		</div>
		<?php } ?>
	<div style="clear:both;"></div>

	<?php
	if( $_SESSION['country_default'] == 1 ){ // AU only ?>

		<div class="sats-block block-color-deeppurple">
			<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/daily/overdue_nsw_jobs")) ?>' target="_blank">
				<div class="booked_box_num">
				<?php get_main_count('nsw-overdue'); ?>
				</div>
				<div class='head-info booked_box_lbl'>NSW Overdue</div>
			</a>
		</div>

		<div class="sats-block upgrade_brooks_div">
			<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/jobs/booked/?job_type_filter=IC Upgrade")) ?>' target="_blank">
				<div class="booked_box_num">
				<?php 
				//get_main_count('upgrades-brooks'); 
				get_main_count('upgrade-booked'); 
				?>
				</div>
				<div class='head-info booked_box_lbl'>Upgrades (Booked)</div>
			</a>
		</div>

		<div class="sats-block upgrade_cavius_div">
			<?php 
				$first_day_of_month = date('01/m/Y');
				$last_day_of_month = date('t/m/Y');
			?>
			<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/jobs/completed_ic_upgrade/?dateFrom_filter={$first_day_of_month}&dateTo_filter={$last_day_of_month}")) ?>' target="_blank">
				<div class="booked_box_num">
				<?php
				 //get_main_count('upgrades-cavius'); 
				 get_main_count('upgrade-completed'); 
				?>	
				</div>
				<div class='head-info booked_box_lbl'>Upgrades (Completed) <?php echo date('F'); ?></div>
			</a>
		</div>	

		<div class="sats-block upgrade_brooks_div">
			<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/jobs/to_be_booked/?job_type_filter=IC Upgrade")) ?>' target="_blank">
				<div class="booked_box_num">
				<?php 
				//get_main_count('upgrades-emerald'); 
				get_main_count('upgrade-to-be-booked'); 
				?>
				</div>
				<div class='head-info booked_box_lbl'>Upgrades (To be booked)</div>
			</a>
		</div>
		
		<div style="clear:both;"></div>
		
		<div class="sats-block" style="background-color: #ed87ba;">
			<a href='<?php echo $crm->crm_ci_redirect(rawurlencode("/jobs/to_be_booked?is_sales=1")) ?>' target="_blank">
				<div class="booked_box_num">
					<?php get_main_count('sales-properties-to-be-booked'); ?>
				</div>
				<div class='head-info booked_box_lbl'>Sales Properties <br/>To Be Booked</div>
			</a>
		</div>

		<div class="sats-block block-color-green">
			<a>
				<div class="booked_box_num">
					<?php get_main_count('dha-to-be-booked'); ?>
				</div>
				<div class='head-info booked_box_lbl'>DHA To Be Booked</div>
			</a>
		</div>
		<div class="sats-block block-color-green">
			<a>
				<div class="booked_box_num">
					<?php get_main_count('dha-completed-last-365-days'); ?>
				</div>
				<div class='head-info booked_box_lbl'>DHA Completed <br/> last 365 days</div>
			</a>
	</div>
	<?php } ?>	

<div class="sats-block block-color-yellow">
			<a>
				<div class="booked_box_num">
					<?php get_main_count('jobs-since-june-2021'); ?>
				</div>
				<div class='head-info booked_box_lbl'>Total Jobs <br/> since June 2021</div>
			</a>
		</div>

		<div class="sats-block block-color-deeppurple">
			<a href='<?php echo $crm->crm_ci_redirect('/agency/agency_audits'); ?>' target="_blank">
				<div class="booked_box_num">
					<?php get_main_count('agency-audits-not-completed'); ?>
				</div>
				<div class='head-info booked_box_lbl'>Agency Audits <br/> Not Completed</div>
			</a>
		</div>

</div>
<div style="clear:both;"></div>
<a href="#popup-box" class="submitbtnImg fancybox" id="edit-goal">Update Goals</a>


<div class="jtoggle_div" style="width: 100%;">
	<div class="toggler-title">
		<label>Greetings</label>
		<span class="toggler-arrow arrow-toggler-bottom"></span>
	</div>

	<div class="toggler-content">
		<div style="font-size: 18px; color: black; text-align: left;" class="success">
			Good <span class='junderline_colored'>morning/afternoon</span>, Smoke Alarm Testing Services. You’re speaking with <?php echo $sa['FirstName']; ?><br /><br />
			Is there anything else I can help you with today?<br />
			Thanks <span class='junderline_colored'>NAME</span>. Have a great day
		</div>
	</div>
</div>

<!-- Booking Schedule -->
<div class="jtoggle-holder">
	<div class="jtoggle_div top-first" style="width: 100%;">

		<?php
		// get booking schedule number of days
		  $staff = $sa;

		  $bs_num_days = ($staff['booking_schedule_num']!="")?$staff['booking_schedule_num']:0;
		?>

		<div class="toggler-title">

				<label>Runs to check</label>
				-
				<label>Display only <?php echo $bs_num_days; ?> days</label>

				<span style="margin-left: 20px;">
					<label>Booking Schedule</label>
					<a href="<?php echo $crm->crm_ci_redirect('bookings/view_schedule'); ?>" target="_blank">
						<img class="login_icon" src="/images/agency_login.png" />
					</a>
				</span>

				<?php
				// AU only
				if( $_SESSION['country_default'] == 1 ){ ?>

					<span style="margin-left: 20px;">
						<label>Planner</label>
						<a href="https://docs.google.com/spreadsheets/d/1XIcN5vF0cEm4qy0M3PEE06wcbPJOlt1-WJnmQ3EVNSg/edit#gid=14323913" target="_blank">
							<img class="login_icon" src="/images/agency_login.png" />
						</a>
					</span>

				<?php
				}
				?>				

				<!-- States Clock --->
				<div id="main_country_time">

					<img src="/images/clock.png" class="clock_img" />

					<label>QLD:</label>
					<span id="country_time_au_qld" class="country_time">
						<?php
						$date = new DateTime('Australia/Brisbane');
						echo $date->format('H:i');
						?>
					</span>

					<label>NSW:</label>
					<span id="country_time_au_nsw" class="country_time">
						<?php
						$date = new DateTime('Australia/Sydney');
						echo $date->format('H:i');
						?>
					</span>


					<label>SA:</label>
					<span id="country_time_au_sa" class="country_time">
						<?php
						$date = new DateTime('Australia/Adelaide');
						echo $date->format('H:i');
						?>
					</span>

					<label>VIC:</label>
					<span id="country_time_au_vic" class="country_time">
						<?php
						$date = new DateTime('Australia/Melbourne');
						echo $date->format('H:i');
						?>
					</span>


					<label style="margin-left: 10px;">NZ:</label>
					<span id="country_time_nz" class="country_time">
						<?php
						$date = new DateTime('Pacific/Auckland');
						echo $date->format('H:i');
						?>
					</span>

				</div>

				<?php
				// only for sales
				if( $user_type == 5 ){ ?>
					<div style="float:left;" id="kms_div">

						<div style="float:left; margin-right: 7px; position: relative; top: 8px;"><?php echo $kms['number_plate']; ?></div>
						<div style="float:left; margin-right: 7px;">Kms: <input type="number" style="float:none; width:60px;" id="kms" value="<?php echo $kms['kms']; ?>" /></div>
						<input type="hidden" id="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />
						<button type="button" class="submitbtnImg" id="update_kms" style="float:left; margin-right: 7px;">Submit Kms</button>

						<?php
						$kms_sql2 = mysql_query("
							SELECT *
							FROM `kms` AS k
							LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
							WHERE k.`vehicles_id` = {$v['vehicles_id']}
							AND v.`country_id` = {$_SESSION['country_default']}
							ORDER BY k.`kms_updated` DESC
							LIMIT 1
						");
						$kms2 = mysql_fetch_array($kms_sql2);
						?>
						<span style="color:#00D1E5; font-size: 12px; float:left; margin-right: 7px;">Updated <br /><?php echo date("d/m/Y",strtotime($kms2['kms_updated'])); ?></span>

					</div>
				<?php
				}
				?>

				<span class="toggler-arrow arrow-toggler-bottom"></span>


		</div>

		<div class="toggler-content" style="padding:0px;">
		<?

		// get booking schedule number of days
		$num_days = ($_GET['days']!="")?$_GET['days']:$bs_num_days;

		$daysData = [];
		for ($x = 1; $x <= $num_days; $x++) {
			$dateTime = strtotime("+{$x} days");
			$data = [
				'dateTime' => $dateTime,
				'techRuns' => [],

			];

			$daysData[date('Y-m-d', $dateTime)] = $data;
		}

		$dates = array_keys($daysData);
		$datesString = implode(',', array_map(function($date) {
			return "'{$date}'";
		}, $dates));

		$techRuns = fetchAllArray(mysql_query("
			SELECT *
			FROM  `tech_run` AS tr
			LEFT JOIN `staff_accounts` AS sa ON tr.`assigned_tech` = sa.`StaffID`
			WHERE tr.`date` IN ({$datesString})
			AND tr.`country_id` = {$_SESSION['country_default']}
			ORDER BY tr.`date` ASC, tr.`tech_run_id` ASC
		"));

		if (!empty($techRuns)) {
			$techRunsAssoc = [];

			$calendarConditions = [];

			for ($x = 0; $x < count($techRuns); $x++) {
				$techRun = &$techRuns[$x];
				$techRun['event'] = null;
				$daysData[$techRun['date']]['techRuns'][] = &$techRun;

				$calendarConditions[] = "(c.`staff_id` = {$techRun['assigned_tech']} AND '{$techRun['date']}' BETWEEN c.`date_start` AND c.`date_finish`)";
				$techRunsAssoc[$techRun['tech_run_id']] = &$techRun;
			}

			$techRunIds = array_keys($techRunsAssoc);
			$techRunIdsString = implode($techRunIds);

			$calendarConditionsString = implode(' OR ', $calendarConditions);
			$calendarEventsQuery = "
				SELECT t.tech_run_id, c.`calendar_id`, c.`region`, c.`details`, c.`staff_id` AS booked_staff, c.`booking_staff`, bsa.`FirstName`, bsa.`LastName`
				FROM `tech_run` AS t
				INNER JOIN staff_accounts AS sa ON sa.StaffID = t.assigned_tech
				INNER JOIN calendar AS c ON sa.StaffID = c.staff_id
				LEFT JOIN staff_accounts AS bsa ON bsa.StaffID = c.booking_staff
				WHERE c.`country_id` = {$_SESSION['country_default']}
				AND t.tech_run_id IN ({$techRunIdsString})
				AND t.date BETWEEN c.date_start AND c.date_finish
			";
			$calendarEvents = fetchAllArray(mysql_query($calendarEventsQuery));

			foreach ($calendarEvents as $event) {
				$techRunsAssoc[$event['tech_run_id']]['event'] = $event;
			}
		}
		?>
		<table width="500"  border="0" id="jobtable" class="bs_tbl_main">
		  <tr style="border-left:none; border-right:none;">
			<th class="bg-red col_day">Day</th>
			<th class="bg-red bs_tech">Technician</th>
			<th class="bg-red bs_area">Area</th>
			<th class="bg-red bs_area">Run Status</th>
			<th class="bg-red bs_area">Booking Staff</th>
		</tr>

        <?
		//   for($x = 1; $x <= $num_days; $x++){

			// $current_ts = strtotime("+{$x} days");
			// $current_day = date('d',$current_ts);
			// $current_month = date('m',$current_ts);
			// $current_year = date('Y',$current_ts);

		foreach ($daysData as $dayData) {
			$current_ts = $dayData['dateTime'];
			$current_day = date('d', $current_ts);
			$current_month = date('m', $current_ts);
			$current_year = date('Y', $current_ts);


		   $tot_bt = 0;
		   $tot_assigned = 0;
		   $tot_bkd = 0;
		   $tot_dk = 0;
		   $tot_bil = 0;
		?>
		  <tr style="border-left:none; border-right:none;" <?if($x > 7) echo 'class="secondset"';?>>
			<td class="col_day">
				<div>
					<?php //$d = explode("-", dateafter($x, 2)); echo "<a href='" . URL . "view_overall_schedule_day.php?id=&day={$d[0]}&month={$d[1]}&year={$d[2]}'>"; echo dateafter($x,3) ?></a>
					<a href='/view_overall_schedule_day.php?id=&day=<?php echo $current_day; ?>&month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>'>
						<?php echo date('l',$current_ts) ?><br />
						<?php echo date('d/m/Y',$current_ts) ?>
					</a>
				</div>
			</td>
			 <td colspan="11">

				<?php

				 $jdate = date('Y-m-d', $current_ts);

					if( !empty($dayData['techRuns']) ){
					?>

						<table class="main-tr bs_tbl_inner">
						<?php

							$tech_count=0;
							foreach ($dayData['techRuns'] as $trt) {

								$call_review = false;

								$cal = $trt['event'];

								// rows
								$tr_color = "";
								$run_status_txt = '';

								// light blue
								if($trt['run_set']==1){
									$tr_color = 'background-color: LightBlue !important;';
									$run_status_txt = 'Needs to be Coloured';
									$call_review = false;
								}

								// yellow
								if($trt['run_coloured']==1){
									$tr_color = 'background-color: #f7d708 !important;';
									$run_status_txt = 'Coloured - Please Review';
									$call_review = false;
								}

								// no color but have icon
								if($trt['ready_to_book']==1){
									$tr_color = '';
									$str_icon_color = 'tech_run_icon_green.png';
									$run_status_txt = '1st Call Over';
									$call_review = false;
								}else{
									$str_icon_color = 'tech_run_icon.png';
									$call_review = false;
								}

								// yellow
								if($trt['first_call_over_done']==1){
									$tr_color = 'background-color: #f7d708 !important;';
									$run_status_txt = '1st Call Done - Please Review';
									$call_review = true;
								}

								// no color
								if($trt['run_reviewed']==1){
									$tr_color = '';
									$run_status_txt = '2nd Call Over';
									$call_review = false;
								}


								// yellow - 2nd Call Over Done
								if($trt['finished_booking']==1){
									$tr_color = 'background-color: #f7d708 !important;';
									$run_status_txt = '	2nd Call Done - Please Review';
									$call_review = true;
								}

								// yellow - Additional Call Over
								if($trt['additional_call_over']==1){
									$tr_color = '';
									$run_status_txt = 'Extra Call Over';
									$call_review = false;
								}

								// yellow - Additional Call Over Done
								if($trt['additional_call_over_done']==1){
									$tr_color = 'background-color: #f7d708 !important;';
									$run_status_txt = 'Extra Call Done - Please Review';
									$call_review = true;
								}

								// orange - run mapped
								if($trt['run_complete']==1){
									$tr_color = 'background-color: #ff9e00 !important;';
									$run_status_txt = 'Booked & Mapped';
									$call_review = false;
								}

								// green
								if($trt['no_more_jobs']==1){
									$tr_color = 'background-color: #9ccf31 !important;';
									$run_status_txt = 'Booked & Mapped FULL';
									$call_review = false;
								}

								// show only 1st call and 2nd call over reviews
								if( $call_review == true ){
								?>
									<tr class="nogreen" style="border:none !important; <?php echo $tr_color; ?> <?php //echo (  $jdate == $today && $isStillHaveBookedJobs == false   )?'display:none;':''; ?>">
										<!--  Technician -->
										<td class="bs_tech">
										<?php
											$crm_ci_page = "tech_run/run_sheet_admin/{$trt['tech_run_id']}";
											$view_tech_url = $crm->crm_ci_redirect($crm_ci_page);
										?>
											<a href="<?php echo $view_tech_url; ?>" style="float: left; margin-right: 4px;">
												<?php echo $crm->formatStaffName($trt['FirstName'],$trt['LastName']); ?>
											</a>
											<a href="<?php echo $crm->crm_ci_redirect("tech_run/set/?tr_id={$trt['tech_run_id']}"); ?>" style="float: left;">
												<img src="/images/<?php echo $str_icon_color; ?>" />
											</a>
											<?php
											$tech_count++;
											?>
											<br />
										</td>
										<!---  Area --->
										<td class="bs_area">
											<?php
												$ci_cal_edit_redirect_link =  "/calendar/view_tech_calendar?popup=1&calendar_id={$cal['calendar_id']}&staff_id={$trt['assigned_tech']}";
											?>
											<a target="_blank" href='<?php echo $crm->crm_ci_redirect($ci_cal_edit_redirect_link); ?>'>
												<?php echo $cal['region']; ?>
											</a>
										</td>
										<!--  Run Status -->
										<td class="bs_acco"><?php echo $run_status_txt; ?></td>
										<!---  Booking Staff --->
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

								   </tr>
							   <?php
									}
								}
							   ?>
						</table>

					<?php
					}



				?>

			 </td>
		  </tr>
		  <?php } ?>

		</table>
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


<!-- Precompleted -->
<div class="jtoggle-holder">
	<div class="jtoggle_div"  style="width: 100%;">

		<div class="toggler-title">

				<label>Precompleted</label>
				<input type="checkbox" name="resources_alerts" class="alerts_display_chk" data-home-display="home_display_precomp" <?php echo ( $sa['home_display_precomp'] == 1 )?'checked="checked"':''; ?> />
				<label>Display</label>
				<span style="margin-left: 20px;">
					<label>Full list</label>
					<?php
					$crmci_page = 'jobs/pre_completion';
					$crmci_page_url = $crm->crm_ci_redirect($crmci_page);
					?>
					<a href="<?php echo $crmci_page_url; ?>" target="_blank">
						<img class="login_icon" src="/images/agency_login.png" />
					</a>
				</span>
				<span class="toggler-arrow arrow-toggler-bottom"></span>

		</div>

		<?php
		if( $sa['home_display_precomp'] == 1 ){ ?>

			<div class="toggler-content precompleted_jobs_tbl" style="padding:0px;">

				<?php
				$jc = new Job_Class();
				$job_status = 'Pre Completion';

				// pagination
				$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
				$limit = 50;

				// sort
				$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.date';
				$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

				$jobs = fetchAllArray($jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status));
				$jobsById = [];
				$propertyIds = [];

				for ($x = 0; $x < count($jobs); $x++) {
					$job = &$jobs[$x];
					$job['tech_run'] = null;
					$job['alarms'] = [];
					$job['property'] = null;
					$job['safety_switches'] = [];

					$jobsById[$job['jid']] = &$job;
					$propertyIds[] = $job['property_id'];
				}

				$jobIds = array_keys($jobsById);
				$jobIdsString = implode(',', $jobIds);

				$techRunsFromJobsQuery = "
					SELECT
						tr.tech_run_id, trr.row_id AS jid, tr.assigned_tech
					FROM tech_run AS tr
					INNER JOIN tech_run_rows AS trr ON trr.tech_run_id = tr.tech_run_id AND trr.row_id_type = 'job_id'
					WHERE trr.row_id IN ({$jobIdsString})
				";
				$techRunsFromJobs = fetchAllArray(mysql_query($techRunsFromJobsQuery));

				for ($x = 0; $x < count($techRunsFromJobs); $x++) {
					$techRun =& $techRunsFromJobs[$x];

					$jobsById[$techRun['jid']]['tech_run'] =& $techRun;
				}

				$properties = fetchAllArray(mysql_query("
					SELECT
						j.`id` AS jid,
						p.`property_id`,
						p.`address_1` AS p_address_1,
						p.`address_2` AS p_address_2,
						p.`address_3` AS p_address_3,
						p.`state` AS p_state,
						p.`agency_id` AS agency_id,
						a.`status` AS agency_status
					FROM jobs AS j
					INNER JOIN property AS p ON j.property_id = p.property_id
					INNER JOIN agency AS a ON p.agency_id = a.agency_id
					WHERE j.id IN ({$jobIdsString})
				"));

				foreach ($properties as $property) {
					$jobsById[$property['jid']]['property'] = $property;
				}

				$alarms = fetchAllArray(mysql_query("
					SELECT
						`job_id`,
						`alarm_id`,
						`expiry`,
						`ts_expiry`,
						`new`,
						`ts_discarded`
					FROM `alarm`
					WHERE
						`job_id` IN ({$jobIdsString})
				"));

				foreach ($alarms as $alarm) {
					$jobsById[$alarm['job_id']]['alarms'][] = $alarm;
				}

				$safetySwitches = fetchAllArray(mysql_query("
					SELECT
						`safety_switch_id`,
						`job_id`,
						`test`
					FROM `safety_switch`
					WHERE `job_id` IN ({$jobIdsString})
				"));

				foreach ($safetySwitches as $safetySwitch) {
					$jobsById[$safetySwitch['job_id']]['safety_switches'][] = $safetySwitch;
				}
				?>

				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin:0;">
					<tr class="toprow jalign_left">
						<th>Day</th>
						<th>Tech</th>
						<th>Address</th>
						<th>Reason</th>
						<th>Comments</th>
						<th>Job #</th>
						<th>Action</th>
					</tr>
						<?php

						if (!empty($jobs)) {
							foreach($jobs as $row) {

								$row_color = '';
								$reason = '';
								$hide_ck = 0;


								// Expiry Dates don't match
								$unmatchedExpiries = array_filter($row['alarms'], function($alarm) {
									return !is_null($alarm['ts_expiry']) && $alarm['expiry'] != $alarm['ts_expiry'];
								});
								if (!empty($unmatchedExpiries)) {
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "Expiry Dates Don't Match <br />";
								}

								// hide for FG: Compass Housing
								if( $row['franchise_groups_id'] != 39 && $_SESSION['country_default'] == 1 ){

									// Job is $0 and YM
									if ($row['job_type'] == "Yearly Maintenance" && $row['job_price'] == 0) {
										$hide_ck = 1;
										$row_color = 'green_mark';
										$reason .= "Job is $0 and YM <br />";
									}

								}


								// New Alarms Installed
								$newAlarms = array_filter($row['alarms'], function($alarm) {
									return $alarm['new'] == 1;
								});
								if (!empty($newAlarms)) {
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "New Alarms Installed <br />";
								}

								// IC upgraded property not 119 check
								if( $row['prop_upgraded_to_ic_sa'] == 1 && $row['job_type'] == 'Yearly Maintenance' && $row['job_price'] != 119  ){
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "IC Job not $119<br />";
								}
								
								if($row['job_type']=='IC Upgrade'){
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "Job type can't be IC Upgrade<br />";
								}

								// Property has Expired Alarms
								// if( isPropertyAlarmExpired($row['jid'],$row['property_id'])==true ) {
								$propertiesWithExpiredAlarm = array_filter($row['alarms'], function($alarm) {
									return (
										($alarm['expiry'] != "" && $alarm['expiry'] <= date('Y'))
										|| ($alarm['ts_expiry'] != "" && $alarm['ts_expiry'] <= date('Y'))
									)
									&& $alarm['ts_discarded'] == 0;
								});

								if (!empty($propertiesWithExpiredAlarm)
									&& $row['deleted'] == 0
									&& $row['property']['agency_status'] == 'active'
									&& $row['del_job'] == 0
								) {
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "Expired Alarms <br />";
								}

								// COT FR and LR price must be 0
								// if( CotLrFrPriceMustBeZero($row['jid'])==true ){
								if (in_array($row['job_type'], ['Change of Tenancy', 'Lease Renewal', 'Fix or Replace', 'Annual Visit'])
									&& $row['job_price'] != 0) {
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= getJobTypeAbbrv($row['job_type'])." must be $0 <br />";
								}

								// If 240v has 0 price
								// if( is240vPriceZero($row['jid'])==true ){
								if ( $row['job_type'] == "240v Rebook" && $row['job_price'] == 0 ) {
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

								// if Electrician Only(EO)
								if( $row['is_eo'] == 1 ){
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "Electrician Only(EO) <br />";
								}

								// If discarded alarm is not equal to new alarm
								$discardedAlarms = array_filter($row['alarms'], function($alarm) {
									return $alarm['ts_discared'] == 1;
								});
								$newUndiscardedAlarms = array_filter($row['alarms'], function($alarm) {
									return $alarm['new'] == 1 && $alarm['ts_discarded'] == 0;
								});

								// if( isMissingAlarms($row['jid'])==true ){
								if ( count($discardedAlarms) != count($newUndiscardedAlarms) ) {
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= " Discarded Alarms don't match Installed Alarms <br />";
								}

								// If NO alarms, exclude CW
								$nonDiscardedAlarms = array_filter($row['alarms'], function($alarm) {
									return $alarm['ts_discarded'] == 0;
								});
								if( empty($nonDiscardedAlarms) && $row['jservice']!=6 ){
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= " No installed Alarms <br />";
								}

								// If job date is not today
								// if( isJobDateNotToday($row['jid'])==true ){
								if( $row['jdate'] != date('Y-m-d') ){
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
								$failedSafetySwitches = array_filter($row['safety_switches'], function($safetySwitch) {
									return $safetySwitch['test'] == 0;
								});
								if( !empty($failedSafetySwitches) ) {
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
								// DHA Agencies
								$isDHAAgency = in_array($row['property']['agency_id'], dhaAgencyIds())
									&& $row['property']['agency_status'] == 'active';
								if( $isDHAAgency && $row['ts_completed']==0 ){
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
									}else if( $row['job_reason_id']==2  || $isDHAAgency ){ // 240v rebook OR DHA agencies
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


								// Pme supplier check
								##new > get api id from new generic table
								$new_api_sql = "
									SELECT * FROM `api_property_data`
									WHERE `crm_prop_id` = {$row['property_id']}
									AND api = 1
									AND active = 1
								";
								$new_api_q = mysql_query($new_api_sql);
								$new_api_row_arr = mysql_fetch_array($new_api_q);
								
								//if( $row['propertyme_prop_id'] == '' && $row['pme_supplier_id'] != '' ){ ## disable > change to new generic table
								if( $new_api_row_arr['api_prop_id'] == '' && $row['pme_supplier_id'] != '' ){ ## new using new APD generic table
									$hide_ck = 1;
									$row_color = 'green_mark';
									$reason .= "Needs PMe Link<br />";
								}


								// if not completed, key access and reason is not 'no keys at agency' (sir Dan says this is the highest priority)
								if( $row['key_access_required']==1 && $row['ts_completed']==0 && $row['job_reason_id']!=11 ){
									$hide_ck = 1;
									$row_color = 'yello_mark';
									//$reason .= "Verify keys have been returned before Rebooking<br />";
								}

								// dont display white jobs
								if( $row_color != '' ){

								?>
								<tr class="body_tr jalign_left <?php echo $row_color; ?>">
									<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
									<td>
										<?php
										$crm_ci_page_precompleted = "tech_run/run_sheet_admin/{$row['tech_run']['tech_run_id']}";
										$view_tech_url_precompleted = $crm->crm_ci_redirect($crm_ci_page_precompleted);

										?>
										<a href="<?php echo $view_tech_url_precompleted; ?>" style="float: left; margin-right: 4px;">
											<?php echo $crm->formatStaffName($row['FirstName'],$row['LastName']); ?>
										</a>
									</td>
									<td>
										<?php if($crm->check_links() == 0){ ?>
										<a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a>
										<?php } else { ?>
										<a href="<?php echo $crm->crm_ci_redirect(rawurlencode("/properties/details/?id={$row['property_id']}&tab=1")); ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a>
										<?php } ?>
									</td>
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
										// if dha and not completed
										if( $isDHAAgency && $row['job_reason_id']>0 ){ ?>
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
								</tr>

								<?php
								}
							}
						}else{ ?>
							<td colspan="14" align="left">Empty</td>
						<?php
						}
						?>

				</table>

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

		<?php
		}
		?>


	</div>
</div>


<script type="text/javascript">
/*
window.onload = function () {
var chart = new CanvasJS.Chart("chartContainer",
{
  title:{
	text: ""
  },
  data: [
  {
   type: "doughnut",
   dataPoints: [
   {  y: 1598, indexLabel: "To Be Booked" },
   {  y: 2782, indexLabel: "Renewals" },
   {  y: 384, indexLabel: "Booked" },
   {  y: 12, indexLabel: "Pre Completed" },
   {  y: 0, indexLabel: "Send Letters" },
   {  y: 0, indexLabel: "Merged Certificates" },
   {  y: 266, indexLabel: "240v Rebooks" },
   {  y: 9, indexLabel: "Fix and Replace" },
   {  y: 113, indexLabel: "COT & LR" }
   ]
 }
 ]
});

chart.render();
}
*/
</script>
<script type="text/javascript" src="js/canvasjs.min.js"></script>

<?php
// not show on call centre
if( $user_type != 7 && $user_type != 8 ){ ?>




<div class="jtoggle-holder">

	<!-- Cars/Tools -->
	<div class="jtoggle_div">

		<div class="toggler-title">
			<label>Cars/Tools</label>
			<input type="checkbox" name="car_tools_alerts" class="alerts_display_chk" data-home-display="home_display_car_n_tools" <?php echo ( $sa['home_display_car_n_tools'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>
			<span class="toggler-arrow arrow-toggler-bottom"></span>
		</div>

		<?php
		if( $sa['home_display_car_n_tools'] == 1 ){ ?>

			<div class="toggler-content">

				<table style="">
				<?php
				// vehicle

				$vehicles = fetchAllArray(mysql_query("
					SELECT *
					FROM `vehicles` AS v
					LEFT JOIN `staff_accounts` AS s ON v.`StaffID` = s.`StaffID`
					WHERE `country_id` = {$_SESSION['country_default']}
					AND v.`active` = 1
				"));
				$vehiclesById = [];

				for ($x = 0; $x < count($vehicles); $x++) {
					$vehicle =& $vehicles[$x];
					$vehicle['kms'] = 0;

					$vehiclesById[$vehicle['vehicles_id']] =& $vehicle;
				}

				if (!empty($vehicles)) {

					$vehicleIds = array_keys($vehiclesById);
					$vehicleIdsString = implode(',', $vehicleIds);

					$kms = fetchAllArray(mysql_query("
						SELECT
							kms.vehicles_id,
							kms.kms,
							kms.kms_updated
						FROM `kms`
						INNER JOIN (
							SELECT vehicles_id, MAX(kms_updated) AS kms_updated
							FROM kms
							WHERE kms.`vehicles_id` IN ({$vehicleIdsString})
							GROUP BY vehicles_id
						) AS k2 ON k2.vehicles_id = kms.vehicles_id AND k2.kms_updated = kms.kms_updated
					"));

					foreach ($kms as $km) {
						$vehiclesById[$km['vehicles_id']]['kms'] = $km;
					}

					foreach ($vehicles as $v) {
						$k = $v['kms'];
						$crm_ci_page = '/vehicles/view_vehicle_details/' . $v['vehicles_id'];
						$page_url = $crm->crm_ci_redirect($crm_ci_page);

						$kms_left = $v['next_service'] - $k['kms'];
						if( $kms_left<=1000 || $v['serviced_booked']==1 ){ ?>
							<tr style="border:none !important;">
								<td style="text-align:left;"><a href="<?php echo $page_url; ?>"><img src="images/<?php echo ($v['serviced_booked']==1)?'car_green.png':'car.png'; ?>" /></a></td>
								<td style="text-align:left;"><a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a></td>
								<td style="text-align:left;"><?php echo $crm->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
								<td style="text-align:left;">Service in <span style="<?php echo ($kms_left<0)?'color:red;':''; ?>"><?php echo $kms_left; ?></span> kms</td>
							</tr>
						<?php
						}

						// 30 days before <rego expires>
						if( date('Y-m-d') >= date('Y-m-d',strtotime($v['rego_expires']."-30 days")) ){ ?>

							<tr style="border:none !important;">
								<td style="text-align:left;">
									<a href="<?php echo $page_url; ?>">
											<img src="images/rego_icon.png" />
									</a>
								</td>
								<td style="text-align:left;">
									<a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a>
								</td>
								<td style="text-align:left;"><?php echo $crm->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
								<td style="text-align:left;">Rego due on <?php echo date('d/m/Y',strtotime($v['rego_expires'])); ?></td>
							</tr>

						<?php
						}

						if($v['country_id'] == 2){

						// 30 days before <wof expires>
						if( date('Y-m-d') >= date('Y-m-d',strtotime($v['WOF']."-30 days")) ){
							?>

							<tr style="border:none !important;">
								<td style="text-align:left;">
									<a href="<?php echo $page_url; ?>">
											<img src="images/wof.png" />
									</a>
								</td>
								<td style="text-align:left;">
									<a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a>
								</td>
								<td style="text-align:left;"><?php echo $crm->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
								<td style="text-align:left;">WOF due on <?php echo date('d/m/Y',strtotime($v['WOF'])); ?></td>
							</tr>

						<?php
							}
						}
					}
				}else{ ?>
					<tr style="border:none !important;">
						<td style="text-align:left;" colspan="100%">No Vehicles Yet</td>
					</tr>
				<?php
				}
				?>


				<?php

				// ladder check
				$today = date('Y-m-d');
				$ladder_str = "
					SELECT
					lc1.`tools_id` ,
					lc1.`inspection_due`,
					lc1.`ladder_check_id`,
					DATEDIFF( lc1.`inspection_due` , '{$today}' ) AS insp_rem_days,
					v.`number_plate`,
					lc1.`tools_id`,
					t.`item_id`,
					t.`country_id`
				FROM ladder_check AS lc1
				LEFT JOIN `tools` AS t ON lc1.`tools_id` = t.`tools_id`
				LEFT JOIN `vehicles` AS v ON t.`assign_to_vehicle` = v.`vehicles_id`
				INNER JOIN (
					SELECT MAX( `inspection_due` ) AS lastInspection, `tools_id`
					FROM ladder_check
					GROUP BY `tools_id`
				) AS lc2 ON lc1.`tools_id` = lc2.`tools_id` AND lc1.`inspection_due` = lc2.lastInspection
				WHERE '{$today}' BETWEEN DATE_SUB( lc1.`inspection_due` , INTERVAL 30 DAY ) AND lc1.`inspection_due`
				AND t.`country_id` = {$_SESSION['country_default']}
				";
				$ladder_sql = mysql_query($ladder_str);

				while( $ladder = mysql_fetch_array($ladder_sql) ){ ?>
				<tr style="border:none !important;">
					<td style="text-align:left;"><img src="images/tools/ladder.png" /></td>
					<td style="text-align:left;"><?php echo $ladder['item_id'] ?></td>
					<td style="text-align:left;"><?php echo $ladder['number_plate']; ?></td>
					<td style="text-align:left;">Inspection due in <?php echo $ladder['insp_rem_days']; ?> days</td>
				</tr>
				<?php
				}
				?>



				<?php

				// test and tag
				$ladder_str = "
					SELECT * , DATEDIFF( tnt.`inspection_due` , '{$today}' ) AS  'insp_rem_days', v.`number_plate`
					FROM `test_and_tag` AS tnt
					LEFT JOIN `tools` AS t ON tnt.`tools_id` = t.`tools_id`
					LEFT JOIN `vehicles` AS v ON t.`assign_to_vehicle` = v.`vehicles_id`
					WHERE  '{$today}' BETWEEN DATE_SUB( tnt.`inspection_due` , INTERVAL 30 DAY ) AND tnt.`inspection_due`
					AND t.`country_id` = {$_SESSION['country_default']}
				";
				$ladder_sql = mysql_query($ladder_str);

				while( $ladder = mysql_fetch_array($ladder_sql) ){ ?>
				<tr style="border:none !important;">
					<td style="text-align:left;"><img src="images/tools/tag.png" /></td>
					<td style="text-align:left;"><?php echo $ladder['item_id'] ?></td>
					<td style="text-align:left;"><?php echo $ladder['number_plate']; ?></td>
					<td style="text-align:left;">Inspection due in <?php echo $ladder['insp_rem_days']; ?> days</td>
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



	<!-- Leave Requests -->
	<div class="jtoggle_div">

		<div class="toggler-title">
			<label>Leave Requests</label>
			<input type="checkbox" name="leave_alerts" class="alerts_display_chk" data-home-display="home_display_leave" <?php echo ( $sa['home_display_leave'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>
			<span class="toggler-arrow arrow-toggler-bottom"></span>
		</div>

		<?php
		if( $sa['home_display_leave'] == 1 ){ ?>

			<div class="toggler-content">

				<table>
					<?php
					// Leave
					$jparams = array(
						'sort_list' => array(
							'order_by' => 'l.`date`',
							'sort' => 'DESC'
						),
						'needs_approval' => 1,
						'country_id' => $_SESSION['country_default']
					);
					$leave_sql = $crm->getLeave($jparams);
					
					while( $leave = mysql_fetch_array($leave_sql) ){ 
					
					$crm_ci_page = "users/leave_details/{$leave['leave_id']}";
					$leave_det_url = $crm->crm_ci_redirect($crm_ci_page);
						
					?>
					<tr style="border:none !important;">
						<td style="text-align:left;"><a href="<?php echo $leave_det_url; ?>"><img src="images/leave.png" /></a></td>
						<td style="text-align:left;"><?php echo date('d/m/Y',strtotime($leave['date'])); ?></td>
						<td style="text-align:left;"><?php echo "{$leave['emp_fname']} {$leave['emp_lname']}"; ?></td>
						<td style="text-align:left;"><?php echo $crm->getLeaveType($leave['type_of_leave']); ?></td>
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


	<!-- Expense Requests -->
	<div class="jtoggle_div">

		<div class="toggler-title">
			<label>Expense Requests</label>
			<input type="checkbox" name="expense_alerts" class="alerts_display_chk" data-home-display="home_display_expenses" <?php echo ( $sa['home_display_expenses'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>
			<span class="toggler-arrow arrow-toggler-bottom"></span>
		</div>

		<?php
		if( $sa['home_display_expenses'] == 1 ){ ?>

			<div class="toggler-content">

				<table>
					<?php
					// Expense
					$jparams = array(
						'sort_list' => array(
							'order_by' => 'exp_sum.`date`',
							'sort' => 'DESC'
						),
						'date_reimbursed_is_null' => 1,
						'country_id' => $_SESSION['country_default']
					);
					$exp_sql = $crm->getExpenseSummary($jparams);

					while( $exp_sum = mysql_fetch_array($exp_sql) ){ ?>
					<tr style="border:none !important;">
						<td style="text-align:left;">
							<a href="<?php echo $crm->crm_ci_redirect("/reports/view_expense_summary"); ?>">
								<img src="images/expense.png" />
							</a>
						</td>
						<td style="text-align:left;"><?php echo date('d/m/Y',strtotime($exp_sum['date'])); ?></td>
						<td style="text-align:left;"><?php echo "{$exp_sum['sa_fname']} {$exp_sum['sa_lname']}"; ?></td>
						<td style="text-align:left;">$<?php echo $exp_sum['total_amount']; ?></td>
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



	<!-- Staff -->
	<div class="jtoggle_div">

		<div class="toggler-title">
			<label>Staff</label>
			<input type="checkbox" name="staff_alerts" class="alerts_display_chk" data-home-display="home_display_staff" <?php echo ( $sa['home_display_staff'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>
			<span class="toggler-arrow arrow-toggler-bottom"></span>
		</div>
		<?php
		if( $sa['home_display_staff'] == 1 ){ ?>

			<div class="toggler-content">

				<table>
				<?php
				// Birthday
				/*$dsql = mysql_query("
					SELECT * , DATEDIFF( `dob`, '".date("Y-m-d")."' ) AS jdif
					FROM `staff_accounts`
					WHERE `dob` IS NOT NULL
					AND `dob` != '0000-00-00'
				");
				while($d = mysql_fetch_array($dsql)){
					if( $d['jdif'] <= 15 && $d['jdif'] >= 0 ){ // adjusted from 14 days to 15
					?>
						<tr style="border:none !important;">
							<td style="text-align:left;"><img src="images/gift.png" alt="" /></span></td>
							<td style="text-align:left;"><?php echo date("F jS",strtotime($d['dob'])) ?></td>
							<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
							<td style="text-align:left;">Birthday <?php echo ($d['jdif']==0)?"is <span style='color:red;'>Today</span>":"in {$d['jdif']} Days"; ?></td>
						</tr>
					<?php
					}
				}*/

				/** NEW query by Gherx */
				$num_days_of_month = explode("-",date("Y-m-t"));
				$num_days_of_month_days = $num_days_of_month['2'];

				$dsql = mysql_query("
				SELECT StaffID, FirstName,LastName,dob
				FROM `staff_accounts`
				WHERE `dob` IS NOT NULL
				AND `dob` != '0000-00-00'
				AND DATE_FORMAT(`dob`, '%m%d') >= DATE_FORMAT( NOW(), '%m%d' )
				AND DATE_FORMAT(`dob`, '%m%d') <=  DATE_FORMAT( DATE_ADD(NOW(), INTERVAL 15 DAY), '%m%d' )
				AND `active` = 1
				ORDER BY DATE_FORMAT(`dob`, '%m%d') ASC
				");

				while($d = mysql_fetch_array($dsql)){

					$dob_exp = explode("-", $d['dob']);
					$exp_dob = $dob_exp[2];

					$exp_toda = explode("-", date("Y-m-d"));
					$exp_today = $exp_toda[2];

					$dob_count_days = $exp_dob - $exp_today;

					if($dob_count_days<0){
						$new_dob_count_days = $dob_count_days + $num_days_of_month_days;
					}else{
						$new_dob_count_days = $dob_count_days;
					}
					
				
				?>

					<tr style="border:none !important;">
						<td style="text-align:left;"><img src="images/gift.png" alt="" /></span></td>
						<td style="text-align:left;"><?php echo date("F jS",strtotime($d['dob'])) ?></td>
						<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
						<td style="text-align:left;">Birthday <?php echo ($new_dob_count_days==0)?"is <span style='color:red;'>Today</span>":"in {$new_dob_count_days} Days"; ?></td>
					</tr>

				<?php
				}
				/** NEW query by Gherx end */ 
				?>

				<?php 
				//By Gherx > Anniversary 
				$anivsql = mysql_query("
				SELECT StaffID, FirstName,LastName,start_date
				FROM `staff_accounts`
				WHERE `start_date` IS NOT NULL
				AND `start_date` != '0000-00-00'
				AND DATE_FORMAT(`start_date`, '%m%d') >= DATE_FORMAT( NOW(), '%m%d' )
				AND DATE_FORMAT(`start_date`, '%m%d') <=  DATE_FORMAT( DATE_ADD(NOW(), INTERVAL 15 DAY), '%m%d' )
				AND `active` = 1
				ORDER BY DATE_FORMAT(`start_date`, '%m%d') ASC
				");

				while($d = mysql_fetch_array($anivsql)){

					$anniv_exp = explode("-", $d['start_date']);
					$anniv_dob = $anniv_exp[2];

					$anniv_toda = explode("-", date("Y-m-d"));
					$anniv_today = $anniv_toda[2];

					$anniv_count_days = $anniv_dob - $anniv_today;

					if($anniv_count_days<0){
						$new_anniv_count_days = $anniv_count_days + $num_days_of_month_days;
					}else{
						$new_anniv_count_days = $anniv_count_days;
					}
				?>

					<tr style="border:none !important;">
						<td style="text-align:left;"><img style="height:32px;" src="images/anniv_icons.png" alt="" /></span></td>
						<td style="text-align:left;"><?php echo date("F jS",strtotime($d['start_date'])) ?></td>
						<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
						<td style="text-align:left;">Anniversary <?php echo ($new_anniv_count_days==0)?"is <span style='color:red;'>Today</span>":"in {$new_anniv_count_days} Days"; ?></td>
					</tr>

				<?php
				}

				//By Gherx > Anniversary end
				?>

				<?php
				// Blue Card expiry
				$dsql = mysql_query("
					SELECT * , DATEDIFF( `blue_card_expiry` , '{$today}' ) AS  'rem_days'
					FROM `staff_accounts`
					WHERE `blue_card_expiry` IS NOT NULL
					AND `blue_card_expiry` != '0000-00-00'
					AND  '{$today}' >= DATE_SUB( `blue_card_expiry` , INTERVAL 15 DAY )
				");
				while($d = mysql_fetch_array($dsql)){
					?>
						<tr style="border:none !important;">
							<td style="text-align:left;"><img src="images/rego_icon.png" alt="" /></span></td>
							<td style="text-align:left;"><?php echo date("F jS",strtotime($d['blue_card_expiry'])) ?></td>
							<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
							<td style="text-align:left;">Blue Card Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
						</tr>
					<?php
				}
				?>


				<?php
				// Licence Expiry
				$dsql_str = "
					SELECT * , DATEDIFF( `licence_expiry` , '{$today}' ) AS  'rem_days'
					FROM `staff_accounts`
					WHERE `licence_expiry` IS NOT NULL
					AND `licence_expiry` != '0000-00-00'
					AND  '{$today}' >= DATE_SUB( `licence_expiry` , INTERVAL 15 DAY )
					AND `active` = 1
				";
				$dsql = mysql_query($dsql_str);
				while($d = mysql_fetch_array($dsql)){
					?>
						<tr style="border:none !important;">
							<td style="text-align:left;"><img src="images/rego_icon.png" alt="" /></span></td>
							<td style="text-align:left;"><?php echo date("F jS",strtotime($d['licence_expiry'])) ?></td>
							<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
							<td style="text-align:left;">Licence Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
						</tr>
					<?php
				}
				?>


				<?php
					// Electrical Licence Expiry
					$dsql_str = "
						SELECT * , DATEDIFF( `elec_licence_expiry` , '{$today}' ) AS  'rem_days'
						FROM `staff_accounts`
						WHERE `elec_licence_expiry` IS NOT NULL
						AND `elec_licence_expiry` != '0000-00-00'
						AND  '{$today}' >= DATE_SUB( `elec_licence_expiry` , INTERVAL 30 DAY )
						AND `active` = 1
					";
					$dsql = mysql_query($dsql_str);
					while($d = mysql_fetch_array($dsql)){
						?>
							<tr style="border:none !important;">
								<td style="text-align:left;"><img src="images/rego_icon.png" alt="" /></span></td>
								<td style="text-align:left;"><?php echo date("F jS",strtotime($d['elec_licence_expiry'])) ?></td>
								<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
								<td style="text-align:left;">Electrical Licence Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
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


	<!-- Resources -->
	<div class="jtoggle_div">

		<div class="toggler-title">
			<label>Resources</label>
			<input type="checkbox" name="resources_alerts" class="alerts_display_chk" data-home-display="home_display_resources" <?php echo ( $sa['home_display_resources'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>
			<span class="toggler-arrow arrow-toggler-bottom"></span>
		</div>
		<?php
		if( $sa['home_display_resources'] == 1 ){ ?>

			<div class="toggler-content">

				<table>
				<?php
				// Resources
				$resources_str = "
					SELECT * , DATEDIFF( `due_date` , '{$today}' ) AS  'res_rem_days'
					FROM  `resources`
					WHERE  '{$today}' BETWEEN DATE_SUB( `due_date` , INTERVAL 30 DAY ) AND `due_date`
					AND `country_id` = {$_SESSION['country_default']}
				";
				$res_sql = mysql_query($resources_str);

				while( $res = mysql_fetch_array($res_sql) ){ ?>
				<tr style="border:none !important;">
					<td style="text-align:left;"><img src="images/house.png" /></td>
					<td style="text-align:left;"><?php echo $res['title']; ?></td>
					<td style="text-align:left;">
						<?php
						if($res['type']==1){ ?>
							<input type="hidden" class="del_path" value="<?php echo $res['path']; ?>/<?php echo $res['filename']; ?>" />
							<a href="<?php echo $res['path']; ?>/<?php echo $res['filename']; ?>"><?php echo $res['filename']; ?></a>
						<?php
						}else{ ?>
							<a href="<?php echo $res['url']; ?>"><?php echo $res['url']; ?></a>
						<?php
						}
						?>
					</td>
					<td style="text-align:left;">Due Date <?php echo ($crm->isDateNotEmpty($res['due_date'])==true)?$crm->formatDate($res['due_date'],'d/m/Y'):''; ?></td>
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






	<!-- Noticeboard -->
	<div class="jtoggle_div">
		<?php
			$n_sql = mysql_query("
				SELECT *
				FROM `noticeboard`
				WHERE `country_id` = {$_SESSION['country_default']}
			");
			$n = mysql_fetch_array($n_sql);
		?>
		<div class="toggler-title">
			Agency Notice Board.
			<input type="checkbox" name="notice_board_alerts" class="alerts_display_chk" data-home-display="home_display_notice_board" <?php echo ( $sa['home_display_notice_board'] == 1 )?'checked="checked"':''; ?> />
			<label>Display</label>

			<span class="toggler-arrow arrow-toggler-bottom"></span>
			<a href="/noticeboard.php" style="margin:0px 10px; float: right;">Update Now</a>

			<?php
			if($n['date_updated']!=""){ ?>
				Last Updated <span style="color:#00D1E5; float: right;"><?php echo date("d/m/Y @ H:i",strtotime($n['date_updated'])); ?></span>
			<?php
			}
			?>


		</div>

		<?php
		if( $sa['home_display_notice_board'] == 1 ){ ?>
			<div class="toggler-content">
				<?php
				if(mysql_num_rows($n_sql)>0){
					echo $n['notice'];
				}else{
					echo '<p style="text-align: left; margin: 5px;">No Notice Yet</p>';
				}
				?>
			</div>
		<?php
		}
		?>
	</div>

</div>






<?php
}
?>
<!-- Put Form into page -->
<div id="popup-box">
  <form method="POST" action="" id="edit-goal-form"> 
    <h3>Update Goal</h3>
	<table id="input-group" class="table">
	</table>
    <button type="submit" class="submitbtnImg" style="margin:10px;">Update</button>
  </form>
</div>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<script>
jQuery(document).ready(function(){
	$(".fancybox")
		.fancybox({
			padding: 0,
			width: 650
      });

	  jQuery("#edit-goal").click(function(e){
		e.preventDefault();
		$("#popup-box").show();
		jQuery("#load-screen").show();
		var input_group = "";
		$('#input-group').html('');
		jQuery.ajax({
			type: "POST",
			dataType: 'json',
			url: "ajax_get_goal_count_data.php",
		}).done(function(ret){
			jQuery("#load-screen").hide();
			$.each(ret, function(i, item) {
				input_group += "<tr><th>"+item.label+"</th><td><input type='number' name='"+item.name+"' value='"+item.total_goal+"' autocomplete='off' required></td></tr>";
			});
			$('#input-group').append(input_group);
		});
	});

	jQuery("#edit-goal-form").submit(function(e){
		e.preventDefault();
		$("#popup-box").show();
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			processData: false,
            contentType: false,
            cache: false,
			url: "ajax_save_goal_count_data.php",
			data: new FormData(this)
		}).done(function(ret){
			window.location="/main.php";
		});
	});



	// toggle home displays
	jQuery(".alerts_display_chk").change(function(){

		var home_display = jQuery(this).attr("data-home-display");
		var chk_val = ( jQuery(this).prop("checked") == true )?1:0;

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_toggle_home_displays.php",
			data: {
				staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
				home_display: home_display,
				chk_val: chk_val
			}
		}).done(function( ret ){
			window.location="/main.php";
		});


	});



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
				window.location="/main.php?rebook=1";
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
				window.location="/main.php?rebook=1";
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
				window.location="/main.php?rebook=1";
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
				window.location="/main.php?sms_sent=1";

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
				window.location="/main.php";
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
				window.location="/main.php";
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
					window.location="/main.php";
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
			window.location="/main.php";
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