<?
include('inc/init.php');

$page_url = 'view_tech_calendar_v2.php';
$title = "View Tech Calendars";

include('inc/header_html.php');
include('inc/menu.php');

$logged_user_id = $_SESSION['USER_DETAILS']['StaffID'];

// staff class type
$class_id = $_REQUEST['class_id']; // default is global: 2

// get staff list
function getStaffList($staff_class_id){
	
	$sql = "
		SELECT *
		FROM `staff_accounts` AS st_ac
		LEFT JOIN `country_access` AS ca ON st_ac.`StaffID` = ca.`staff_accounts_id`
		LEFT JOIN `staff_classes` AS sc ON st_ac.`ClassID` = sc.`ClassID`
		WHERE ca.`country_id` = {$_SESSION['country_default']}
		AND st_ac.deleted = 0 
		AND st_ac.active = 1
		AND sc.ClassID = {$staff_class_id}
		ORDER BY st_ac.FirstName ASC, st_ac.LastName
	";
	return mysql_query($sql);
}

// get calendar entries
function getCalendarEntry($staff_id,$date){
	return mysql_query("
		SELECT *
		FROM `calendar`
		WHERE `staff_id` = {$staff_id}
		AND '{$date}' BETWEEN `date_start` AND `date_finish`
	");
}

function getCalendarFilters($logged_user_id){

	echo $cf_sql = "
		SELECT * 
		FROM `cal_filters` 
		WHERE StaffId = {$logged_user_id}
	";
	return mysql_query($cf_sql);

}

?>
<div id="mainContent">
	
    <div class="sats-middle-cont">

 <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Calendar" href="view_tech_calendar_v2.php?class_id=2"><strong>View Calendar</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   
   
   <div class='cal_month_nav'>
		
		
		<?php
		// Number of days in the given month
		$current_date = ($_REQUEST['current_date']!='')?$_REQUEST['current_date']:date("Y-m-d");
		$last_day = date("t",strtotime($current_date));
		$this_month_txt = date('F',strtotime($current_date));
		$this_month = date('m',strtotime($current_date));
		$this_month_year = date('Y',strtotime($current_date));
		$today = date('Y-m-d');
		$prev_cal = date('Y-m-01',strtotime($current_date." -1 month"));
		$next_cal = date('Y-m-01',strtotime($current_date." +1 month"));		
		?>
		
		<div style="float: left; margin-top: 6px;">
			<div style='color:#b4151b; float:left'>Required(<img src="images/red_house.png" />)</div> <div style="float:left; margin: 0 5px;">-</div> 
			<div style='color:#f15a22; float:left'>Pending(<img src="images/orange_house.png" />)</div> <div style="float:left; margin: 0 5px;">-</div> 
			<div style='color:#00ae4d; float:left'>Booked(<img src="images/green_house.png" />)</div>
		</div>
		
		
		<a href="<?php echo $page_url; ?>?class_id=<?php echo $class_id; ?>&current_date=<?php echo $prev_cal; ?>"><span class="arw-lft3">&nbsp;</span></a> 
		<?php echo "{$this_month_txt}  {$this_month_year}"; ?> 
		<a href="<?php echo $page_url; ?>?class_id=<?php echo $class_id; ?>&current_date=<?php echo $next_cal; ?>"><span class="arw-rgt3">&nbsp;</span></a>
		
		<!--- EXPORT --->
		<div style="float: right; margin-top: 6px;">		
			
			
			<a href="staff_calendar_csv.php?month=<?php echo $this_month; ?>&year=<?php echo $this_month_year; ?>">
				<button style="float:right; margin-left: 10px;" type="button" class="submitbtnImg vtc-exp">
					<img class="inner_icon" src="images/export.png">
					Export
				</button>
			</a>
			
			<button type="button" id="btn_payroll_export" class="submitbtnImg blue-btn">
				<img class="inner_icon" src="images/export.png">
				Payroll Export
			</button>
			
			<div id="payrol_export_dates_div">
				<form action="staff_calendar_csv.php" method="POST">
					<div class="jfloat_left">
						<span class="pe_lbl">From:</span> <input type="text" name="payroll_from" class="addinput payroll_inputs datepicker" value="<?php echo date('d/m/Y',strtotime("{$this_month_year}-{$this_month}-1")) ?>" />
						<span class="pe_lbl">To:</span> <input type="text" name="payroll_to" class="addinput payroll_inputs datepicker" value="<?php echo date('t/m/Y',strtotime("{$this_month_year}-{$this_month}-1")) ?>" />
					</div>
					<div class="jfloat_left">
						<input type="hidden" name="payroll_export" value="1" />
						<button type="submit" id="btn_payroll_export_go" class="submitbtnImg vtc-exp">Go</button>
					</div>				
				</form>
			</div>
			
		</div>
		
		<div style="clear:both;"></div>
		
		</div>
   
   
   
	<?php
	// get Staff Classes for tabs
	$staff_class_sql = mysql_query("
		SELECT * 
		FROM `staff_classes` 
	");
	$staff_class_arr = [];
	while( $sc = mysql_fetch_array($staff_class_sql) ){
		$staff_class_arr[] = array(
			'staff_class_id' => $sc['ClassID'],
			'staff_class_name' => $sc['ClassName']
		);
	}	
	?>
   <div id="tabs" class="c-tabs no-js">
		<div class="c-tabs-nav">
		<?php
		foreach( $staff_class_arr as $index => $staff_class ){ ?>
			<a href="view_tech_calendar_v2.php?class_id=<?php echo $staff_class['staff_class_id']; ?>" class="c-tabs-nav__link <?php echo ( $staff_class['staff_class_id'] == $class_id )?'is-active':''; ?>"><?php echo $staff_class['staff_class_name']; ?></a>
		<?php
		}
		?>
		</div>
		<div class="c-tab is-active">
			<div class="c-tab__content">	
			
				<table class="cal-fluid2 techcalendar" id="cal-fluid" cellpadding="0" cellspacing="0" border="0">
					<thead>
						<tr class="head visible">
							<th>Staff</th>
							<?php
							// ordinal, converts 1 to 1st, 2 to 2nd and so on...
							$locale = 'en_US';
							$nf = new NumberFormatter($locale, NumberFormatter::ORDINAL);
							// loop through all days of month
							for( $i=1; $i<=$last_day; $i++ ){ ?>
									<th><?php echo $nf->format($i); ?></th>
							<?php
							}
							?>
						</tr>
					</thead>
					<tbody>		
						<?php
						$staff_sql = getStaffList($class_id);
						while( $staff = mysql_fetch_array($staff_sql) ){ ?>
						<tr class="visible">
							<td><?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?></td>
							<?php
							// loop through all days of month
							for( $i=1; $i<=$last_day; $i++ ){ 
							
							$working_days_arr = explode(",",$staff['working_days']);
							// date per td
							$this_date = date('Y-m-d',strtotime("{$this_month_year}-{$this_month}-{$i}"));
							$week_day = date('w', strtotime($this_date));
							$day_text = date('D', strtotime($this_date));
							
							$isDayOff = false;
							$isWeekend = false;
							$hasCalEntry = false;
							$markedAsLeave = false;
							$td_bg_color = '';
							
							
							// fetch calendar
							$cal_sql = getCalendarEntry($staff['StaffID'],$this_date);
							// calendar entry found
							if( mysql_num_rows($cal_sql)>0 ){
								
								$cal = mysql_fetch_array($cal_sql);
								
								// marked as leave
								if( $cal['marked_as_leave']==1 ){
									$markedAsLeave = true;
								}
								
								// accomodation color
								$cal_link_color = '';
								if($cal['accomodation']==1){ 															
									$cal_link_color = 'green';																	
								}else if($cal['accomodation']==2){
									$cal_link_color = 'orange';
								}else if($cal['accomodation']!="" && $row['accomodation']==0){
									$cal_link_color = 'red';
								}
														
								$hasCalEntry = true;
								
								$td_day_content = '
								<a style="color: '.$cal_link_color.'" target="__blank" href="add_calendar_entry_static.php?id='.$cal['calendar_id'].'">
									'.$cal['region'].'
								</a>
								';
								
							}	
							
							
							// if weekend: 6 = Saturday, 0 = Sunday									
							if( $week_day == 6 || $week_day == 0 ){
								$td_bg_color = '#eeeeee';
								$isWeekend = true;
							}
							
							// if today
							if( $this_date == $today ){
								$td_bg_color = '#DFFFA5';
							}
							
							if( $markedAsLeave == true && $isWeekend == false ){
								$td_bg_color = '#ffcccb';
							}

							// if its day off and not weekend
							if ( !in_array($day_text, $working_days_arr) && $isWeekend == false ){
								$td_bg_color = '#ffcccb';
								$isDayOff = true;
							}
							?>
									<td style="background-color: <?php echo $td_bg_color; ?>">
										<div class="calendar_td_div">
											<?php
											
											if( $isDayOff == true ){
												echo 'OFF';
											}else if( $hasCalEntry == true ){
						
												echo $td_day_content;
													
											}																							
											?>
										</div>
									</td>
							<?php
							}
							?>
						</tr>
						<?php
						}
						?>		
					</tbody>
				</table>
				
				<?php 
				$staff_per_tab_sql = getCalendarFilters($logged_user_id); 
				$cal_fil = mysql_fetch_array($staff_per_tab_sql);
				$staff_filter = $cal_fil['StaffFilter'];
				$staff_class_filter = $cal_fil['staff_class_filter'];
				echo "
				staff_filter: {$staff_filter}<br />
				staff_class_filter: {$staff_class_filter}<br />
				";
				
				$tab_users_sql = getStaffList($class_id);
				while( $tab_users = mysql_fetch_array($tab_users_sql) ){ ?>
					<label class='vtc-chckbx-h staff_label'>
						<input type='checkbox' class='vtc-chckbx staff_chk' value='' />	
						<span class='staff_span'><?php echo "{$tab_users['FirstName']} {$tab_users['LastName']}"; ?></span>
					</label>
				<?php
				}
				?>
				
				
				
			</div>
		  </div>
	</div>
		

	<div style="margin-top: 15px;">
	
		<a href="add_calendar_entry_static.php" target="_blank">
		<button type="submit" id="submit" class="submitbtnImg" style="float:left;">
			<img class="inner_icon" src="images/add-button.png">
			Event
		</button>
		</a>


		<button type="button" class="submitbtnImg blue-btn" style="float: left; margin-left: 13px;" onclick="location.reload();">
			<img class="inner_icon" src="images/rebook.png">
			Refresh
		</button>
		
	</div>
	

</div>

</div>
</div>


<style>
.c-tab__content {
    overflow: auto;
}
.calendar_td_div {
    min-width: 60px;
	min-height: 60px;
}
#load-screen{
	display:block;
}
.payroll_inputs{
	width: 78px;
}
#payrol_export_dates_div{
	float: left;
	margin-right: 25px;
	display: none;
}
.pe_lbl{
	float: left;
	margin: 6px 0 0 10px;
}
.jfloat_left{
	float: left;
}
#btn_payroll_export_go{
	margin-left: 10px;
}
</style>
<script>
jQuery(document).ready(function(){
	
	jQuery("#load-screen").hide();
	
	jQuery("#btn_payroll_export").click(function(){
		
		jQuery("#payrol_export_dates_div").toggle();
		
	});
	
});
</script>
</body>
</html>
