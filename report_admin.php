<?

$title = "SATS Reports";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

function jmissed_jobs($from_date,$to_date,$tech,$country_id){

	/*
	if( $from_date!="" && $to_date!="" ){
		$from_date_str = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date_str = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date_str = date('Y-m-d');
		$to_date_str = date('Y-m-d');
	}
	
	if($tech!=""){
		$str .= " AND jl.`staff_id` = {$tech} ";
	}
	
	$str .= " ORDER BY ass_tech.`FirstName`, ass_tech.`LastName` ";

	$jr_str = "";
	$jr_sql = mysql_query("
		SELECT * 
		FROM `job_reason` 
		".(($reason!='')?" WHERE `name` = '{$reason}' ":"")."
	");
	while($jr = mysql_fetch_array($jr_sql)){
		$jr_str .= ",'{$jr['name']}', '{$jr['name']} DK'";
	}
	
	$fr_filter = substr($jr_str,1);
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs
		FROM  `job_log` AS jl
		LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `staff_accounts` AS ass_tech ON j.`assigned_tech` = ass_tech.`StaffID`
		LEFT JOIN `staff_accounts` AS sa ON jl.`staff_id` = sa.`StaffID`
		WHERE jl.`contact_type` 
		IN (
			{$fr_filter}
		)
		AND a.`status` =  'active'
		AND p.`deleted` =0
		AND j.`del_job` = 0
		AND jl.`eventdate` BETWEEN '{$from_date_str}' AND '{$to_date_str}'
		AND a.`country_id` ={$country_id}		
		{$str}
	";

	//echo "<div style='display:none;'>{$sql}</div>";
	
	$mj_sql = mysql_query($sql);
	$mj = mysql_fetch_array($mj_sql);
	return $mj['num_jobs'];
	*/
	
}

function ra_dk_completed($from_date,$to_date,$tech,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	if($tech!=""){
		$str .= " AND j.`assigned_tech` = {$tech} ";
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs 
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND j.`status` = 'Completed'
		AND j.`door_knock` = 1
		AND a.`country_id` = {$country_id}	
		{$str}
	";

	//echo "<div style='display:none;'>{$sql}</div>";
	
	$mj_sql = mysql_query($sql);
	$mj = mysql_fetch_array($mj_sql);
	return $mj['num_jobs'];
	
}


function getStaffBookedJobs($from_date,$to_date,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs, sa.`StaffID`, sa.FirstName, sa.LastName  
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
		GROUP BY j.`booked_by`
		ORDER BY sa.FirstName, sa.LastName  
	";
	
	return mysql_query($sql);
	
}

// Entrty Notice
function getStaffENBookedJobs($from_date,$to_date,$staff_id,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND sa.`StaffID` = {$staff_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
		AND j.`job_entry_notice` = 1
	";
	
	return mysql_query($sql);
	
}


// Door Knock
function getStaffDKBookedJobs($from_date,$to_date,$staff_id,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND sa.`StaffID` = {$staff_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
		AND j.`door_knock` = 1
	";
	
	return mysql_query($sql);
	
}


// Not entry notice and Door knocks
function getStaffNoEnNoDKBookedJobs($from_date,$to_date,$staff_id,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND sa.`StaffID` = {$staff_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
		AND j.`door_knock` = 0
		AND j.`job_entry_notice` = 0
	";
	
	return mysql_query($sql);
	
}

function getTechCompletedJobs($from_date,$to_date,$country_id){
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs, sa.`StaffID`, sa.FirstName, sa.LastName 
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`TechID`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}	
		AND j.`status` = 'Completed'
		GROUP BY j.`assigned_tech`
		ORDER BY sa.FirstName, sa.LastName 
	";
	
	return mysql_query($sql);
	
}


function ra_job_type_count($from_date,$to_date,$tech,$job_type='',$country_id){
	
	
	
	if( $from_date!="" && $to_date!="" ){
		$from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
		$to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
	}else{
		$from_date = date('Y-m-d');
		$to_date = date('Y-m-d');
	}
	
	if($tech!=""){
		$str .= " AND j.`assigned_tech` = {$tech} ";
	}
	
	$sql = "
		SELECT COUNT(j.`id`) AS num_jobs 
		FROM jobs AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`date` >= '{$from_date}' 
		AND j.`date` <= '{$to_date}'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND j.`job_type` = '{$job_type}'
		AND a.`country_id` = {$country_id}
		AND j.`status` = 'Completed'
		{$str}
	";

	//echo "<div style='display:none;'>{$sql}</div>";
	
	$mj_sql = mysql_query($sql);
	$mj = mysql_fetch_array($mj_sql);
	return $mj['num_jobs'];
	
}



# Report Parameters

if(isValidDate($_GET['from']) && isValidDate($_GET['to']))
{
	$to = convertDate($_GET['to']);
	$from = convertDate($_GET['from']);
}
else
{
	# Default back to month to date if nothing entered or bad input
	$to = date('Y-m-d');
	$from = date('Y-m-') . "01";
}

# Get dates for << prev day and next day >> links
$tmp = explode("-", $from);

$prev_day = array(
	'from' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('-1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => '<span class="arw-lft2">&nbsp;</span> Previous Day'
);

$next_day = array(
	'from' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'to' => date('Y-m-d', strtotime('+1 day', mktime(0,0,0, $tmp[1], $tmp[2], $tmp[0]))),
	'title' => 'Next Day <span class="arw-rgt2">&nbsp;</span>',
	'css' => 'float: right;'
);


# Create predefined date ranges
$today = date('Y-m-d');

$date_ranges = array();

$date_ranges[] = array(
'title' => 'Today',
'from' => $today,
'to' => $today
);

$date_ranges[] = array(
'title' => 'Yesterday',
'from' => date('Y-m-d', (strtotime('-1 days'))),
'to' => date('Y-m-d', (strtotime('-1 days')))
);

$date_ranges[] = array(
'title' => 'Last Week',
'from' => date('Y-m-d', (strtotime('-7 days'))),
'to' => $today
);

$date_ranges[] = array(
'title' => 'Next Week',
'from' => $today,
'to' => date('Y-m-d', (strtotime('+7 days')))
);

/*
$date_ranges[] = array(
'title' => 'Month to date',
'from' => date('Y-m-') . "01",
'to' => $today
);
 */

$date_ranges[] = array(
'title' => date('F', strtotime("this month")),
'from' => date('Y-m-', strtotime("this month")) . "01",
'to' => date('Y-m-t', strtotime("this month"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("last month")),
'from' => date('Y-m-', strtotime("last month")) . "01",
'to' => date('Y-m-t', strtotime("last month"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("-2 months")),
'from' => date('Y-m-', strtotime("-2 months")) . "01",
'to' => date('Y-m-t', strtotime("-2 months"))
);

$date_ranges[] = array(
'title' => date('F', strtotime("-3 months")),
'from' => date('Y-m-', strtotime("-3 months")) . "01",
'to' => date('Y-m-t', strtotime("-3 months"))
);


# Display dates in dd/mm/yyyy
$to_display = convertDateAus($to);
$from_display = convertDateAus($from);


$staff_id = (isset($_GET['sid']) ? (int)$_GET['sid']: "z");
$tech_id = (isset($_GET['tid']) ? (int)$_GET['tid']: "z");

# Get Staff details for display if needed
if($staff_id === 0)
{
	$staff_details['FirstName'] = "SATS System";
}
elseif(is_int($staff_id))
{
	$staff_details = $user->getUserDetails($staff_id);
}

# Get Tech details for display if needed
if($tech_id === 0)
{
	$tech_details['FirstName'] = "Unassigned";
}
elseif(is_int($tech_id))
{
	$tech_details = $user->getTechDetails($tech_id);
}


# Staff and tech id's to filter
$staff_filter = array(
'staff_id' => $staff_id,
'tech_id' => $tech_id
);

$report_params = array('from' => $from, 'to' => $to, 'staff_id' => $staff_id, 'tech_id' => $tech_id);

$report = new Report();

/*
$data = $report -> getReportData($report_params);

$all_status_types = $report->getAllStatuses();
$all_job_types = $report->getAllJobTypes();
*/

?>
<div id="mainContent">


 <div class="sats-middle-cont">
   
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="SATS Reports" href="/report_admin.php"><strong>SATS Reports</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
    
    <table cellpadding=0 cellspacing=0 >
    <tr class="tbl-view-prop">
        <td>
          <form action="" id="date_range" method="get">
            <div class="aviw_drop-h">
            
			  <div class="fl-left">
				<label>Report from:</label>
     <input type="text" name="from" value="<?php echo $from_display; ?>" class="addinput searchstyle datepicker">

			  </div>
			
  <div class="fl-left">
   <label>to:</label>
    <input type="text" name="to" value="<?php echo $to_display; ?>" class="addinput searchstyle datepicker">

  </div>
  
  <div class="fl-left pull-left">    
    <?php if(is_int($staff_id)): ?>
	<input type="hidden" name="sid" value="<?php echo $staff_id; ?>" class="submitbtnImg">	
	<?php endif; ?>	
	<?php if(is_int($tech_id)): ?>
	<input type="hidden" name="tid" value="<?php echo $tech_id; ?>" class="submitbtnImg">	
	<?php endif; ?>
	<input type="hidden" name="get_sats" value="1" />
	<input type="submit" value="Get Stats" class="submitbtnImg">
  </div>
  
</div>

<div class="aviw_drop-h qlnk">

<span class="float-left content-black"><?php echo $report->generateLink($prev_day, $staff_filter); ?></span>

Quick Links&nbsp;|&nbsp;<?php foreach($date_ranges as $index=>$range): ?>
		<?php echo $report->generateLink($range, $staff_filter); ?>
		<? if($index < sizeof($date_ranges) - 1): ?>
		&nbsp;|&nbsp;
		<? endif; ?>		
		<?php endforeach; ?>
        
<span class="float-right pg-tp-rg content-black"><?php echo $report->generateLink($next_day, $staff_filter); ?></span>		
</div>
            
 </form>           
            
     </td>
  </tr>
</table>

<?php
if( $_GET['get_sats']==1 ){ ?>


<h2 class="heading">
	Report for <?php echo $from_display;?>
	to <?php echo $to_display;?>
</h2>
	<?php
# Alert that viewing Staff or Tech indivudal reports if neded, and offer to reset
if(is_int($staff_id))
{
	echo "<div class='success'>Currently viewing statistics for staff member: " . $staff_details['FirstName'] . " " . $staff_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
}
if(is_int($tech_id))
{
	echo "<div class='success'>Currently viewing statistics for technician: " . $tech_details['FirstName'] . " " . $tech_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
}

?>


<?php
$countries = array(
	1=>'au',
	2=>'nz'
);
?>

<?php

foreach($countries AS $country_id=>$country_iso){
	


?>

<h2 class="heading" style="margin-top: 50px;">Type Breakdown</h2>
<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red jtable'>
			<tr class="<?php echo $country_iso; ?>_bg_color">	
				<th>Yearly Maintenance</th>
				<th>Change of Tenancy</th>
				<th>Lease Renewal</th>
				<th>240v Rebook	</th>
				<th>Fix or Replace</th>
				<th>Once-off</th>
				<th>None</th>
				<th>Total</th>
			</tr>
			<tr>
				<td><?php echo $ym_tot = ra_job_type_count($from,$to,'','Yearly Maintenance',$country_id); ?></td>
				<td><?php echo $cot_tot = ra_job_type_count($from,$to,'','Change of Tenancy',$country_id); ?></td>
				<td><?php echo $lr_tot = ra_job_type_count($from,$to,'','Lease Renewal',$country_id); ?></td>
				<td><?php echo $tfv_r_tot = ra_job_type_count($from,$to,'','240v Rebook',$country_id); ?></td>
				<td><?php echo $fr_tot = ra_job_type_count($from,$to,'','Fix or Replace',$country_id); ?></td>
				<td><?php echo $of_tot = ra_job_type_count($from,$to,'','Once-off',$country_id); ?></td>
				<td><?php echo $n_tot = ra_job_type_count($from,$to,'','None Selected',$country_id); ?></td>
				<?php
				$type_tot = $ym_tot + $cot_tot + $lr_tot + $tfv_r_tot + $fr_tot + $of_tot + $n_tot;
				?>
				<td class='<?php echo ($type_tot > 0 ? "highlighted" : "no_result");?>'><?php echo $type_tot; ?></td>
			</tr>
</table>


<div class='report_box'>    
<h2 class="heading">Tech Completed Jobs</h2>    
<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
			<tr class="<?php echo $country_iso; ?>_bg_color">
				<th width='210'>Tech Name</th>
				<th>Completed Jobs</th>
				<th>Missed Jobs</th>
				<th>% Missed Jobs</th>
				<th>DKs Achieved</th>
			</tr>
			<?php 
			$tech_sql = getTechCompletedJobs($from,$to,$country_id);
			while( $tech = mysql_fetch_array($tech_sql) ){ ?>
			<tr>
				<td><?php echo "{$tech['FirstName']} {$tech['LastName']}"; ?></td>
				<td align='center' class="highlighted"><?php echo $comp_j = $tech['num_jobs']; ?></td>
				<td align='center'>
				<?php 
				echo $mj_count = jmissed_jobs($from,$to,$tech['StaffID'],$country_id);
				?>
				</td>
				<td>
				<?php
				$perc = ($mj_count/$comp_j)*100;
				echo number_format($perc, 2, '.', '')."%";
				?>
				</td>
				<td>
				<?php 
				echo $dk_count = ra_dk_completed($from,$to,$tech['assigned_tech'],$country_id);
				?>
				</td>
			</tr>	
			<?php } ?>
	</tr>
</table>    
</div>



<div class='report_box_right'>    
<h2 class="heading">Staff Booked Jobs</h2>
<table border=0 cellspacing=0 cellpadding=5 width=100% class='table-center tbl-fr-red'>
			<tr class="<?php echo $country_iso; ?>_bg_color">
				<th width='210'>Staff Name</th>
				<th>Booked</th>
				<th>Entry Notices</th>
				<th>Door Knocks</th>
				<th>Total Booked</th>
			</tr>
			<?php 
			$staff_sql = getStaffBookedJobs($from,$to,$country_id);
			while($staff = mysql_fetch_array($staff_sql)){
				//$booked_by_tot = ra_booked_by($from,$to,$staff['staff_accounts_id']);
				//if($booked_by_tot!=0){ 
				?>
					<tr>
						<td>
							<?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?>
						</td>
						<td>
						<?php
							$sbnon_endk_sql = getStaffNoEnNoDKBookedJobs($from,$to,$staff['StaffID'],$country_id); 
							$sbnon_endk = mysql_fetch_array($sbnon_endk_sql);
							echo $sbnon_endk['num_jobs'];
						?>								
						</td>
						<td class="staff_booked_total">
						<?php
							$sben_sql = getStaffENBookedJobs($from,$to,$staff['StaffID'],$country_id); 
							$sben = mysql_fetch_array($sben_sql);
							echo $sben['num_jobs'];
						?>
						</td>
						<td>
							<?php 
							$sbdk_sql = getStaffDKBookedJobs($from,$to,$staff['StaffID'],$country_id); 
							$sbdk = mysql_fetch_array($sbdk_sql);
							echo $sbdk['num_jobs'];
							?>
						</td>
						<td class="highlighted">
							<?php echo $staff['num_jobs']; ?>	
						</td>
					</tr>	
				<?php	
				//}
			} 
			?>
	</tr>
</table>
</div>

<br class="clearfloat" />


<?php	

}

}else{ ?>

	<h2 style="text-align:left;">Press 'Get Stats' to Display Results</h2>

<?php	
}
?>	
    
	

</div>

</div>

<br class="clearfloat" />
<style>
.jtable tr td, .jtable tr th{
	border: 1px solid #cccccc;
}



.custom_gap{
	border-bottom: medium hidden !important;
    border-top: medium hidden !important;
	background-color: white;
}
</style>
<script>
jQuery(document).ready(function(){
	
	
	
	/*
	// hide 0 data on booked total
	jQuery(".staff_booked_total").each(function(){
	  if(jQuery(this).html()==0){
		jQuery(this).parents("tr:first").hide();
	  }
	});
	*/
	
});
</script>
</body>
</html> 
