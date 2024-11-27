<?

$title = "View Tech Calendars";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

$bodyclass = "wide";

$start_time = microtime(true);

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


// GRAB THE DATES FROM THE URL
$month = $_GET['month'];
$year = $_GET['year'];
// IF THE DATES DONT EXISIT IN URL THEN USE CURRENT
if(!isset($_GET['month'])) {
	$month = date(m);
}
if(!isset($_GET['year'])) {
	$year = date(Y);
}

//get the number of days in the month
$calendardays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$month_start = $year . "-" . $month . "-01";
$month_end = $year . "-" . $month . "-" . $calendardays;


?>
<div id="mainContent">
	
    <div class="sats-middle-cont">

 <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Calendar" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>View Calendar</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

	<?php
	
		// Fetch current staff filter for user
		$cf_sql = "SELECT StaffId, StaffFilter, `staff_class_filter` FROM cal_filters WHERE StaffId = '" . $_SESSION['USER_DETAILS']['StaffID'] . "' LIMIT 1";
		$staff_filter = array();
		$cf = mysqlSingleRow($cf_sql);
		$staff_filter = explode(",", $cf['StaffFilter']);
		$staff_class_filter = explode(",", $cf['staff_class_filter']);
	

		// fetch all the calendar entries - single dates
		$rows = array();
		$sql = "SELECT c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, s.FirstName, s.LastName, DATEDIFF(date_finish, date_start), `booking_target`, c.`accomodation`, c.`marked_as_leave`, s.`working_days`  FROM calendar c INNER JOIN staff_accounts s ON (s.StaffID = c.staff_id) WHERE s.Deleted = 0 AND c.date_start = c.date_finish AND c.date_start >= '{$month_start}' AND c.date_finish <= '{$month_end}' AND s.active = 1 AND c.`country_id` ={$_SESSION['country_default']} ORDER BY staff_id, date_start;";
		$query = mysql_query($sql, $connection);
		while($result = mysql_fetch_assoc($query)) {
			$rows[] = $result;
		}

		// fetch all the calendar entries - differing start / end dates
		$sql = "SELECT c.calendar_id, c.staff_id, c.region, c.date_start, c.date_finish, s.FirstName, s.LastName, DATEDIFF(date_finish, date_start) AS num_days, `booking_target`, c.`accomodation`, c.`marked_as_leave`, s.`working_days`  FROM calendar c INNER JOIN staff_accounts s ON (s.StaffID = c.staff_id) WHERE s.Deleted = 0 AND c.date_start != c.date_finish AND s.active = 1 AND c.`country_id` ={$_SESSION['country_default']} ORDER BY staff_id, date_start;";
		$query = mysql_query($sql, $connection);
		while($result = mysql_fetch_assoc($query)) {
			$rows[] = $result;
		}

		// USER CHECKBOX query
		//fetch all the staff who are active
		$techs = array();
		//$sql_tech = "SELECT s.StaffID, s.FirstName, s.LastName, c.ClassName FROM staff_accounts s, staff_classes c WHERE s.deleted = 0 AND s.active = 1 AND s.`ClassID` = c.`ClassID` ORDER BY c.ClassName ASC, s.StaffID ASC";
		$sql_tech = "
			SELECT DISTINCT (
			s.`StaffID`
			), s.StaffID, s.FirstName, s.LastName, c.ClassName, c.`ClassID`
			FROM `staff_accounts` AS s
			LEFT JOIN `country_access` AS ca ON s.`StaffID` = ca.`staff_accounts_id`
			LEFT JOIN `staff_classes` AS c ON s.`ClassID` = c.`ClassID`
			WHERE ca.`country_id` = {$_SESSION['country_default']}
			AND s.deleted = 0 
			AND s.active = 1
			ORDER BY c.ClassName ASC, s.StaffID ASC
		";
		
		$query_tech = mysql_query($sql_tech, $connection);
		$count = 0;
		while($result_tech = mysql_fetch_assoc($query_tech)) {
			$techs[] = $result_tech;
			$count = $count + 1;
		}
		
		// tech with sort
		$techs_with_sort = array();
		//$sql_tech2 = "SELECT s.StaffID, s.FirstName, s.LastName, c.ClassName FROM staff_accounts s, staff_classes c WHERE s.deleted = 0 AND s.active = 1 AND s.`ClassID` = c.`ClassID` ORDER BY s.FirstName ASC, s.StaffID ASC";
		$sql_tech2 = "
			SELECT DISTINCT (
			s.`StaffID`
			), s.StaffID, s.FirstName, s.LastName, c.ClassName, s.`working_days`, c.`ClassID`
			FROM `staff_accounts` AS s
			LEFT JOIN `country_access` AS ca ON s.`StaffID` = ca.`staff_accounts_id`
			LEFT JOIN `staff_classes` AS c ON s.`ClassID` = c.`ClassID`
			WHERE ca.`country_id` = {$_SESSION['country_default']}
			AND s.deleted = 0 
			AND s.active = 1
			ORDER BY c.ClassName ASC, s.StaffID ASC
		";
		
		$query_tech_sort = mysql_query($sql_tech2, $connection);
		$count_sort = 0;
		while($result_tech2 = mysql_fetch_assoc($query_tech_sort)) {
			$techs_with_sort[] = $result_tech2;
			$count_sort = $count_sort + 1;
		}

		

		//the tables rely on this to form.
		$monthname = mktime(0, 0, 0, $month, 1, $year);
		$monthname = date("F", $monthname);
		//echo '<br/>';
		
		
		

		$countday = 0;
		$themonth = array();
		
		echo "<div class='cal_staff_filter'>";

		
		//echo "<div class='clear_both'></div>";
		//ENABLE TIME TRAVEL
		//GO BACKWARDS ONE MONTH
		if($month == 1){
			$backyear = $year - 1;
			$backmonth = 12;			
		} else {
			$backmonth = $month - 1;
			$backyear = $year;
		}
		
		echo "<div class='cal_month_nav'>";
		
		
		?>
		
		
		<div style="float: left; margin-top: 6px;">
			<div style='color:#b4151b; float:left'>Required(<img src="images/red_house.png" />)</div> <div style="float:left; margin: 0 5px;">-</div> 
			<div style='color:#f15a22; float:left'>Pending(<img src="images/orange_house.png" />)</div> <div style="float:left; margin: 0 5px;">-</div> 
			<div style='color:#00ae4d; float:left'>Booked(<img src="images/green_house.png" />)</div>
		</div>
		
		<?
		
		echo date("F",mktime(0,0,0,$backmonth,1,$backyear)).'<a href="view_tech_calendar.php?month='. $backmonth .'&year='. $backyear .'" style="margin-left: 5px;"><span class="arw-lft3">&nbsp;</span>  </a> '. $monthname .' '. $year .' ';
		
		//GO FORWARDS ONE MONTH
		if($month == 12){
			$forwardyear = $year + 1;
			$forwardmonth = 1;			
		} else {
			$forwardmonth = $month + 1;
			$forwardyear = $year;
		}
		
		echo '<a href="view_tech_calendar.php?month='. $forwardmonth .'&year='. $forwardyear .'" style="margin-right: 5px;"><span class="arw-rgt3">&nbsp;</span></a>'.date("F",mktime(0,0,0,$forwardmonth,1,$forwardyear));
		
		//echo "<a href='#' class='submitbtnImg vtc-exp'>Export</a>";
		
		?>
		
		
		
		<a href="staff_calendar_csv.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>">
			<button style="float:right" type="button" class="submitbtnImg vtc-exp">
				<img class="inner_icon" src="images/export.png">
				Export
			</button>
		</a>
		
		<div style="float: right; margin: 0 15px;">			
			<button type="button" id="btn_payroll_export" class="submitbtnImg blue-btn">
				<img class="inner_icon" src="images/export.png">
				Payroll Export
			</button>
		</div>
		
		<div id="payrol_export_dates_div">
		<form action="staff_calendar_csv.php" method="POST">
			<div style="float:left;">
				<div style="float: left;"><label class="payroll_date_lbl">From:</label> <input type="text" name="payroll_from" class="addinput payroll_inputs datepicker" value="<?php echo date('d/m/Y',strtotime("{$year}-{$month}-1")) ?>" /></div>
				<div style="float: left;"><label class="payroll_date_lbl">To:</label> <input type="text" name="payroll_to" class="addinput payroll_inputs datepicker" value="<?php echo date('t/m/Y',strtotime("{$year}-{$month}-1")) ?>" /></div>
			</div>	
			<input type="hidden" name="payroll_export" value="1" />
			<button style="float:right; margin: 0 15px;" type="submit" id="btn_payroll_export_go" class="submitbtnImg vtc-exp">Go</button>
		</form>
		</div>
		
		
		
		<?php
		
		echo "</div>";

		

		while($countday < $calendardays) {
				
			$thedate = $countday + 1;
			$whiledate = $year.'-'.$month.'-'.$thedate;
			
			// check if date is weekend
			$weekDay = date('w', strtotime($whiledate));
			if($weekDay == 0 || $weekDay == 6) {
				$isWeekend = TRUE;
			} else {
				$isWeekend = FALSE;
			}
			
			$themonth[$countday]['date'] = $whiledate;
			$themonth[$countday]['weekend'] = $isWeekend;
						
			$countday = $countday + 1;
		}
		
		//Have the date running down the side outside of the scrolling div
		?>
		

  <div class="calendardates">
			<table class="techcalendar" id="cal-fixed" cellpadding="0" cellspacing="0" border="0">
				<tr id="l_row_0" class="visible" style="display:none;">
					<th>Tech</th>
					<?php
					
					$count = 1;

					foreach($techs_with_sort as $tech) {

						$class = $count % 2 == 0 ? "row" : "row_alt";

						if( !in_array($tech['StaffID'], $staff_filter) && !in_array($tech['ClassID'], $staff_class_filter) )
						{
							$class .= " visible";
						}

						echo '<tr id="l_row_' . $count . '" class="date ' . $class . ' staff' . $tech['StaffID'] . '" style="display:none;">'; 
							echo '<td class="date">';
							echo $tech['FirstName'] .' '. $tech['LastName'];
							echo '</td>';
						echo '</tr>';

						$count++;
					}
					?>
					
				</tr>				
			</table>
		</div>


	<?php
		
		echo '<div>';
		$width = $count * 160;
		echo '<div>';
		
		?>
		
	
		<style>
		.techcalendar th{
			height: 25px !important;
		}		
		.techcalendar td{
			height: 40px !important;
		}
		.fontColorLightGrey{
			/*color:red !important;*/
			color:#d3d3d3 !important;
		}
		table#cal-fluid td{
			text-align: center;
		}
		.joverflow_div {
			height: auto;
			overflow-x: scroll;
			width: auto;
		}
		.payroll_date_lbl{
			margin-left: 11px;
			position: relative;
			top: 9px;
		}
		#load-screen{
			display:block;
		}
		.payroll_inputs{
			width: 78px;
		}
		#payrol_export_dates_div{
			float: right;
			display: none;
		}
		/*
		.joverflow_div_first{
			margin-top: 18px;
		}
		*/
		</style>
		
        <div class="vtc-hhh">
        <div class="joverflow_div_first">
		
			
		
			<table class="cal-fluid1 techcalendar staff_name_tbl" id="cal-fluid" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr class="head visible">
						<th>Staff</th>
					</tr>
				</thead>				
				<?php
				foreach($techs_with_sort as $tech){ 
					if( !in_array($tech['StaffID'], $staff_filter) && !in_array($tech['ClassID'], $staff_class_filter) ){ ?>
					<tr class="date visible">
						<td class="staff_name_td"><?php echo $tech['FirstName'] .' '. (substr($tech['LastName'],0,1).'.'); ?></td>
					</tr>
					<?php
					}
				}
				?>
				<tfoot>
					<tr class="head visible">
						<th>Staff</th>
					</tr>
				</tfoot>				
			</table>
			
		</div>
		

		<div class="joverflow_div" id="calendar_div">
		<table class="cal-fluid2 techcalendar jcalendar" id="cal-fluid" cellpadding="0" cellspacing="0" border="0">
			
		<?php
		//The Table Header
		echo '<thead>';
		echo '<tr class="head visible" id="r_row_0">';
			//echo '<th>Staff</th>';
			//Print out the name of each tech
			foreach($themonth as $calday) {

				#$thedate = date("d-m-Y", strtotime($calday[date]));
				$thedate = date("jS", strtotime($calday[date]));
				echo '<th>'. $thedate .'</th>';
			}
		
		echo '</tr>';
		echo '</thead>';
		
		echo '<tfoot>';
		echo '<tr class="head visible" id="r_row_0">';
			//echo '<th>Staff</th>';
			//Print out the name of each tech
			foreach($themonth as $calday) {

				#$thedate = date("d-m-Y", strtotime($calday[date]));
				$thedate = date("jS", strtotime($calday[date]));
				echo '<th>'. $thedate .'</th>';
			}
		
		echo '</tr>';
		echo '</tfoot>';
		
		$count = 1;

		foreach($techs_with_sort as $tech) {

				$class = $count % 2 == 0 ? "row" : "row_alt";

				if( !in_array($tech['StaffID'], $staff_filter) && !in_array($tech['ClassID'], $staff_class_filter) )
				{
					$class .= " visible";
				}

				echo '<tr class="date ' . $class . ' staff' . $tech['StaffID'] . '" id="r_row_' . $count . '">'; 
				
				$sql = mysql_query("
					SELECT *
					FROM `staff_accounts` 
					WHERE `StaffID` = {$tech['StaffID']}
				");
				
			
				
				$a = mysql_fetch_array($sql);				
				//echo "<td>{$a['FirstName']} {$a['LastName']}</td>";
				
				$inner_count = 1;

				foreach($themonth as $calday) {
					
					$inner_class = $inner_count % 2 == 0 ? "cell" : "cell_alt";
					$inner_class .= $calday[weekend] == 1 ? " weekend" : "";
					$today_txt = date("D",strtotime($calday['date']));
					
					$col_color = '';
					
					// highlight if today's date
					if(date("Y-m-j")==$calday['date']){					
						$col_color = 'style="background-color:#DFFFA5 !important"';
					}
					
					// day off highlight
					if( strchr($tech['working_days'],$today_txt)==false && $calday[weekend]==0 ){
						$col_color = 'style="background-color:#ffcccb !important"';
					}

					echo '<td  '.$col_color.' class="' . $inner_class . ' clickable" rel="' . date('d-m-Y', strtotime($calday['date'])) . '_' . $tech['StaffID'] . '">';				 					
					
					// test echo
					//echo $tech[StaffID].' - '.$calday['date'].' - '.$today_txt;
					
					if( strchr($tech['working_days'],$today_txt)==false && $calday[weekend]==0 ){
						echo "OFF";
					}else{
						
						foreach($rows as $key=>$row) {
							
							
								
								$caldate = strtotime($calday['date']);
								$startdate = strtotime($row['date_start']);
								$finishdate = strtotime($row['date_finish']);
								if($caldate >= $startdate && $caldate <= $finishdate) {
									if($tech[StaffID] == $row[staff_id]) {
										
										// if leave on weekend, then hide it
										$hide_str = '';
										if( $row['marked_as_leave']==1 && $calday[weekend]==1 ){
											$hide_str = 'display:none;';
										}
									
										$acc_color = "";
									
										// accomodation color
										if($row['accomodation']==1){ 															
											$acc_color = 'green';																	
										}else if($row['accomodation']==2){
											$acc_color = 'orange';
										}else if($row['accomodation']!="" && $row['accomodation']==0){
											$acc_color = 'red';
										}
										
										echo '<div '.(($row['marked_as_leave']==1 && $calday[weekend]!=1 )?'class="jhighlight"':'').' style="'.$hide_str.'">';
										
										echo '<a style="color:'.$acc_color .'" rel="'. $row[calendar_id] .'" href="add_calendar_entry_static.php?id='. $row[calendar_id] .'">'. $row[region] .'</a> '.(($row['booking_target']!=0 && $row['booking_target']!='')?$row['booking_target']:'');
										
										echo '</div>';

										if($startdate == $finishdate)
										{
											// Delete if no longer needed - saves looping through again, speeds things up slighty :/
											unset($rows[$key]);
										}

									} else {
									}
								}
								
							
							
						}
						
					}
					
					

					$inner_count++;
					
					?>
					<script>
					jQuery(".jhighlight").each(function(){
						jQuery(this).parents("td:first").attr('style','background-color: #ffcccb !important;'); 
					});
					</script>
					<?php

					echo '</td>';
					
				}
				echo '</td>';
			echo '</tr>';

			$count++;
		}
		?>
		
		</table>
		</div>
        </div>
		
		</div>
        
</div>

<div style="clear:both;"></div>

   <div style="margin-top: 15px;">
   <a href="add_calendar_entry_static.php">
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
   
<div class="clear">&nbsp;</div>

<!--
<div style="display:none;">
Check All<input type="checkbox" id="chk_staff_check_all" />
<button type="button" onclick="window.location='/view_tech_calendar.php'">Refresh</button>
</div>

<div class="row" style="text-align: left;">
Check All<input type="checkbox" id="chk_staff_class_check_all" />
</div>
-->

<?php 

$curr_group = "";

		$newline_count = 1;
		foreach($techs as $index=>$tech)
		{
			if($tech['ClassName'] != $curr_group)
			{
				if($curr_group != "")
				{
					// Close off old fieldset
					echo "</div>";
					$newline_count = 1;
				}
				
				$allowed_sc = !in_array($tech['ClassID'], $staff_class_filter);

				// Draw new legend
				echo "<div class='row staff_class_div'>";
				echo "<h2 class='heading staff_class_header ".( ( $allowed_sc )?'':'fontColorLightGrey' )."' style='float: left;'>" . $tech['ClassName'] . "</h2>";
				echo "<input type='checkbox' style='float: left; margin: 22px 19px 0 11px;' class='staff_class_chk' ".( ( $allowed_sc )?'checked="checked"':'' )." value='{$tech['ClassID']}' />";
				echo '<div class="row staff_div" style="text-align: left; margin-top: 18px;">
					<span class="staff_check_all_span fontColorLightGrey">Check All</span> <input type="checkbox" class="staff_check_all_chk" />
					</div>
					';
				echo '<div style="clear:both;"></div>';

				$curr_group = $tech['ClassName'];
			}
			
			//if($allowed_sc){

			echo "<label class='vtc-chckbx-h staff_label'><input type='checkbox' class='vtc-chckbx staff_chk'";

			$allowed_staff = !in_array($tech['StaffID'], $staff_filter);
			if( $allowed_staff )
			{
				echo " checked ";
			}

			
				
				echo " value='" . $tech['StaffID'] . "' /> <span class='staff_span ".( ( $allowed_staff && $allowed_sc )?'':'fontColorLightGrey' )."'>" . $tech['FirstName'] . " " . (substr($tech['LastName'],0,1)) . ".</span></label>";

				//echo $newline_count % 4 == 0 && $newline_count != 0  ? "<div class='clear_both'></div>" : "";
				$newline_count++;
				
			//}		

		}
		
		echo "</div>";

?>


</div>

</div>
</div>
</div>

<div class="clear">&nbsp;</div>


<script>


/*
function DoubleScroll(element) {
    var scrollbar = document.createElement('div');
    scrollbar.appendChild(document.createElement('div'));
    scrollbar.style.overflow = 'scroll';
    scrollbar.style.overflowY = 'hidden';
    scrollbar.firstChild.style.width = element.scrollWidth+'px';	
    scrollbar.firstChild.style.paddingTop = '1px';
    //scrollbar.firstChild.appendChild(document.createTextNode('\xA0')); 
	console.log("Height: "+scrollbar.clientHeight);	
    scrollbar.onscroll = function() {
        element.scrollLeft = scrollbar.scrollLeft;
    };
    element.onscroll = function() {
        scrollbar.scrollLeft = element.scrollLeft;
    };
    element.parentNode.insertBefore(scrollbar, element);
}
*/


function fakeScroll(elem,insertBefore){

	// get inner container width
	var container_width = elem.width();
	
	// fake scroll html format
	var scroll_format = ''+
		'<div class="cloned_top_scroll" style="overflow-x: scroll; overflow-y: hidden;">'+
			'<div class="inner_scroll" style="width: '+container_width+'px; padding-top: 1px;"></div>'+
		'</div>';

	// insert the fake scroll on top
	insertBefore.before(scroll_format);
	
	// sync boths scrolls
	jQuery(".cloned_top_scroll").scroll(function(){
		
		var scroll_left = jQuery(this).scrollLeft();
		jQuery("#calendar_div").scrollLeft(scroll_left);
		
	});
	
	jQuery("#calendar_div").scroll(function(){
		
		var scroll_left = jQuery(this).scrollLeft();
		jQuery(".cloned_top_scroll").scrollLeft(scroll_left);
		
	});

}





jQuery(".jhighlight").each(function(){
	jQuery(this).parents("td:first").attr('style','background-color: #ffcccb !important;'); 
});

$(document).ready(function() {
	
	jQuery("#load-screen").hide();
	
	
	jQuery("#btn_payroll_export").click(function(){
		
		jQuery("#payrol_export_dates_div").toggle();
		
	});
	
	
	// top scroll script
	//DoubleScroll(document.getElementById('calendar_div'));
	//jQuery(".joverflow_div_first").css('margin-top','18px');
	
	
	var elem_scroll = jQuery(".jcalendar");
	var insert_before = jQuery("#calendar_div");
	fakeScroll(elem_scroll,insert_before);
	var scroll_clone = jQuery(".cloned_top_scroll").clone();
	var staff_name_with = jQuery(".staff_name_td").width()
	scroll_clone.css('width',staff_name_with+'px');
	scroll_clone.css('visibility','hidden');
	jQuery(".staff_name_tbl").before(scroll_clone);
	//var fakeScroll_height = jQuery(".cloned_top_scroll").height();
	//jQuery(".joverflow_div_first").css('margin-top',fakeScroll_height);
	

	
	// check all class script
	jQuery(".staff_class_div").each(function(){

		var staff = jQuery(this).find(".staff_chk").length;
		var staff_checked = jQuery(this).find(".staff_chk:checked").length;
		
		//console.log("Staff: "+staff+" Checked Staff: "+staff_checked);
		
		if( staff_checked == staff ){
			jQuery(this).find(".staff_check_all_chk").prop("checked",true);
			//jQuery(this).find(".staff_check_all_span").removeClass("fontColorLightGrey");
		}

	});
	
	
	// check all/uncheck all toggle
	jQuery("#chk_staff_check_all").click(function(){
		if(jQuery(this).prop("checked")==true){
			jQuery(".vtc-chckbx").prop("checked",true);			
		}else{
			jQuery(".vtc-chckbx").prop("checked",false);
		}
		//window.location='/view_tech_calendar.php';
	});
	
	
	jQuery(".staff_check_all_chk").click(function(){
		
		if(jQuery(this).prop("checked")==true){
			jQuery(this).parents(".staff_class_div:first").find(".staff_chk").prop("checked",true);	
			jQuery(this).parents(".staff_class_div:first").find(".staff_span").removeClass("fontColorLightGrey");			
		}else{
			jQuery(this).parents(".staff_class_div:first").find(".staff_chk").prop("checked",false);
			jQuery(this).parents(".staff_class_div:first").find(".staff_span").addClass("fontColorLightGrey");			
		}
		
	});
	
	jQuery(".staff_chk").click(function(){
		
		if(jQuery(this).prop("checked")==true){
			jQuery(this).parents(".staff_label:first").find(".staff_span").removeClass("fontColorLightGrey");			
		}else{
			jQuery(this).parents(".staff_label:first").find(".staff_span").addClass("fontColorLightGrey");			
		}
		
	});
	
	// check all/uncheck all toggle
	jQuery("#chk_staff_class_check_all").click(function(){
		if(jQuery(this).prop("checked")==true){
			jQuery("#load-screen").show();
			$.ajax({
				type: "POST",
				data: "CheckAllStaffClass=1&check_all=1",
				url: "ajax/ajax.php",
				dataType: 'json',
				cache:false,
				success: function(response){			
					jQuery("#load-screen").hide();
				}
			});
			jQuery(".staff_class_chk").prop("checked",true);			
		}else{
			jQuery("#load-screen").show();
			$.ajax({
				type: "POST",
				data: "CheckAllStaffClass=1&check_all=0",
				url: "ajax/ajax.php",
				dataType: 'json',
				cache:false,
				success: function(response){
					jQuery("#load-screen").hide();
				}
			});
			jQuery(".staff_class_chk").prop("checked",false);
		}
		//window.location='/view_tech_calendar.php';
	});

	

	// Re Zebra
	//zebraTable();

	/*
	// Loop through left / right tables and set same height
	$(".calendardates table tr").each(function() {

		var l_attr = $(this).attr("id");
		var l_height = $(this).height();
		var r_attr = l_attr.replace("l", "r");
		var r_height = $("tr#" + r_attr).height();

		$("table.techcalendar tr#" + l_attr).height(Math.max(l_height, r_height) + "px");
		$("table.techcalendar tr#" + r_attr).height(Math.max(l_height, r_height) + "px");
	});
	*/

	// staff filter
	$("input.staff_chk, .staff_check_all_chk").on("change", function(){
		
		var staff_id = $(this).val();

		/*
		if($(".staff_chk").is(":checked"))
		{
			$("tr.staff" + staff_id).fadeIn().addClass("visible");
			jQuery(this).parents("label.staff_label:first").find(".staff_span").removeClass("fontColorLightGrey");
		}
		else
		{
			$("tr.staff" + staff_id).hide().removeClass("visible");
			jQuery(this).parents("label.staff_label:first").find(".staff_span").addClass("fontColorLightGrey");
		}
		*/

		// Re Zebra
		//zebraTable();

		var serialized = "";

		// Send unchecked boxes to db
		$("input.staff_chk:not(:checked)").each(function() {
			serialized += "," + $(this).val();
		});
		
		jQuery("#load-screen").show();
		$.ajax({
			type: "POST",
			data: "UpdateCalFilter=1&serialized=" + serialized,
			url: "ajax/ajax.php",
			dataType: 'json',
			cache:false,
			success: function(response){		
				jQuery("#load-screen").hide();
			}
		});
	});
	
	
	// staff class filter
	$("input.staff_class_chk").on("change", function(){
		
		var staff_class_id = $(this).val();

		
		if($(this).is(":checked"))
		{
			//$("tr.staff" + staff_id).fadeIn().addClass("visible");
			jQuery(this).parents(".staff_class_div:first").find(".staff_class_header").removeClass("fontColorLightGrey");
		}
		else
		{
			//$("tr.staff" + staff_id).hide().removeClass("visible");
			jQuery(this).parents(".staff_class_div:first").find(".staff_class_header").addClass("fontColorLightGrey");
		}
		

		// Re Zebra
		//zebraTable();

		var sc_serialized = "";

		// Send unchecked boxes to db
		$("input.staff_class_chk:not(:checked)").each(function() {
			sc_serialized += "," + $(this).val();
		});
		
		jQuery("#load-screen").show();
		$.ajax({
			type: "POST",
			data: "UpdateCalStaffClassFilter=1&sc_serialized="+sc_serialized,
			url: "ajax/ajax.php",
			dataType: 'json',
			cache:false,
			success: function(response){
				jQuery("#load-screen").hide();
			}
		});
	});
	
	

	

	// Make cells clickable
	$("td.clickable").on('dblclick', function() {
		//get date and staff ID
		var rel = $(this).attr("rel").split("_");

		var url = 'add_calendar_entry_static.php?startdate=' + rel[0] + '&staff_id=' + rel[1];

		// Popup with the add calendar page
		newwindow=window.open(url,'name','height=600,width=460,scrollbars=yes');
		if (window.focus) {newwindow.focus()}
		return false;
		
	});

	// Intercept link click and open popup too
	$("td.clickable a").on('click', function() {

		/*var id = $(this).attr("rel");
		var url = 'add_calendar_entry_popup.php?id=' + id;
		*/
		var url = jQuery(this).attr("href");
		newwindow=window.open(url,'name','height=600,width=460,scrollbars=yes');
		if (window.focus) {newwindow.focus()}

		return false;
	});
	
});

function zebraTable()
{
	$("table.techcalendar tr").removeClass("row").removeClass("row_alt");
	$("table#cal-fluid tr.visible:odd").addClass("row");
	$("table#cal-fluid tr.visible:even").addClass("row_alt");
	$("table#cal-fixed tr.visible:odd").addClass("row");
	$("table#cal-fixed tr.visible:even").addClass("row_alt");
}

</script>
</body>
</html>
