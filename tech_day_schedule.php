<?

//$title = "View Technician Schedule Day";

include('inc/init.php');

$crm = new Sats_Crm_Class();

// get tech run
$tr_id = $_GET['tr_id'];

$tr_sql = mysql_query("
	SELECT * 
	FROM  `tech_run` 
	WHERE  `tech_run_id` = {$tr_id}
");

$tr = mysql_fetch_array($tr_sql);

$hasTechRun = ( mysql_num_rows($tr_sql)>0 )?true:false;

$tech_id = $tr['assigned_tech'];
$day = date("d",strtotime($tr['date']));
$month = date("m",strtotime($tr['date']));
$year = date("Y",strtotime($tr['date']));
$date = $tr['date'];
$sub_regions = $tr['sub_regions'];


//get tech name
$t_sql = mysql_query("
	SELECT `StaffID`, `FirstName`, `LastName`, `is_electrician`, `ContactNumber`
	FROM `staff_accounts`
	WHERE `StaffID` = {$tech_id}
");
$t = mysql_fetch_array($t_sql);

$isElectrician = ( $t['is_electrician']==1 )?true:false;
$tech_name = "{$t['FirstName']} {$t['LastName']}";
$tech_mob1 = $t['ContactNumber'];

$title = $tech_name;




include('inc/header_html.php');
include('inc/menu.php');



// updates the map listing to it's newest state
//updateMapListing($tech_id,$date);


if( $hasTechRun == true ){
	

	// get new jobs from via region
	// appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default'],'',1);

	// get new jobs from via assigned
	$isAssigned = 1;
	//appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$_SESSION['country_default'],$isAssigned,1);
	


}




function cutFullAddress($address){

	$find_array = array('NSW','VIC','QLD','ACT','TAS','SA','WA','NT');
	
	foreach( $find_array as $state ){
		
		$cut_start = strpos($address, $state);
		//echo "index: {$cut_start}";
		if( $cut_start>0 ){
			$cut_index = $cut_start;
			
		}

	}
	
	$find_array = array('New South Wales','Australian Capital Territory','Victoria','South Australia','Queensland','Tasmania','Western Australia','Northern Territory');
	
	foreach( $find_array as $state ){
		
		$cut_start = strpos($address, $state);
		//echo "index: {$cut_start}";
		if( $cut_start>0 ){
			$cut_index = $cut_start;
			
		}

	}
	
	return substr($address,0,$cut_index);
	
}

function getNumberOfBookedKeys($tech_id,$date,$country_id,$agency_id){
	
	$sql_str = "
	SELECT j.`id` AS jid
	FROM jobs AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$country_id}	
	AND j.`assigned_tech` ={$tech_id}
	AND j.`date` = '{$date}'
	AND a.`agency_id` = {$agency_id}
	AND j.`key_access_required` = 1
	AND(
		j.`status` = 'Booked'
		OR j.`status` = 'Pre Completion'
		OR j.`status` = 'Merged Certificates'
		OR j.`status` = 'Completed'
	)
	";
	$sql = mysql_query($sql_str);
	
	return mysql_num_rows($sql);
	
}

?>


<?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
  <?php
  }  
  ?>

  <style>
	.jrow_highlight{
		background-color: #ececec;
	}
	.time_img{
		position: relative; 
		top: 6px;
		cursor: pointer;
	}
	.time_div_toggle{
		margin-top:9px; 
		display:none;
	}
	.jfloatLeft{
		float:left;
	}
	.ladder_icon{
		width: 25px;
	}
	.key_num_span{
		display: none;
	}
	.key_icon{
		cursor: pointer;
	}
  </style>
  <div id="mainContent">
  
   <div class="sats-middle-cont">
   
   <?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
  
  $tech_id = $_SESSION['USER_DETAILS']['StaffID'];
  
  $day = date("d");
  $month = date("m");
  $year = date("y");
  
  include('inc/tech_breadcrumb.php');
   
  }else{ ?>

  
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Schedule for the Day" href="/tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>"><strong>View Schedule for the Day</strong></a></li>
      </ul>
    </div>
  
  <?php
  }  
  ?>

   
    
    
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  
  
	<?php
	if($_GET['success']=="kms"){ ?>
		<div class="success">Kms Submitted</div>
	<?php
	}
	?>
	
	
	<?php
	if($_GET['screenshot_success']==1){ ?>
		<div class="success">Screenshot Sent</div>
	<?php
	}
	?>
  
  
	<div id="new_job_success_msg" class="success" style="background-color: #ffff00; display:none;">&nbsp;</div>


	<div id="searching_for_new_jobs_div" style="background-color: #ececec; display:none;"><img style="width: 30px; margin: 7px 0;" src="/images/loading.gif" /><span style="bottom: 14px; left: 8px; position: relative;">Searching New Jobs...</span> </div>


<div id="screenshot_target" style="background-color:#ffffff">
<div class="vtsd-tp-chrm ap-vw-reg-sch aviw_drop-h" style="height: auto;padding-bottom: 5px; text-align: center; border-bottom: 1px solid #ccc;">


<div class="vtsd-tp-left">


	<?php
	$sql = mysql_query("
		SELECT *
		FROM `staff_accounts` AS sa
		LEFT JOIN `vehicles` AS v ON v.`StaffID` = sa.`StaffID` 
		WHERE sa.`StaffID` = {$tech_id}
	");
	$v = mysql_fetch_array($sql);
	
	$kms_sql = mysql_query("
		SELECT *
		FROM `kms` AS k		
		LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
		WHERE k.`vehicles_id` = {$v['vehicles_id']}
		ORDER BY k.`kms_updated` DESC
		LIMIT 0, 1
	");
	$kms = mysql_fetch_array($kms_sql);
	?>
	<span>
		<div style="float:left; margin-right: 7px; position: relative; top: 4px;"><?php echo $kms['number_plate']; ?></div> 
		<div style="float:left; margin-right: 7px;">Kms: <input type="number" style="float:none;" id="kms" value="<?php echo $kms['kms']; ?>" /></div>
		<input type="hidden" id="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />
		<button type="button" class="submitbtnImg" id="update_kms" style="float:left; margin-right: 7px;">Submit Kms</button>
		 <?php
		$kms_sql = mysql_query("
			SELECT *
			FROM `kms` AS k
			LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
			WHERE k.`vehicles_id` = {$v['vehicles_id']}
			AND v.`country_id` = {$_SESSION['country_default']}
			ORDER BY k.`kms_updated` DESC
			LIMIT 1
		");
		$kms = mysql_fetch_array($kms_sql);
		?>	
		<span style="color:#00D1E5; font-size: 12px; float:left; margin-right: 7px;">Updated <br /><?php echo date("d/m/Y",strtotime($kms['kms_updated'])); ?></span>
		
		<?php
		//if( date("D")=='Mon' || date("D")=='Sat' || date("Y-m-d")==date("Y-m-t") ){ 
			$sa_sql = mysql_query("
				SELECT `StaffID`, `FirstName`, `LastName`
				FROM `staff_accounts` 
				WHERE `StaffID` = {$tech_id}
				AND `Deleted` = 0
				AND `active` = 1
			");
			$sa = mysql_fetch_array($sa_sql);
		?>
			
			<a href="/update_tech_stock.php?id=<?php echo $sa['StaffID']; ?>" style="float:left;">
				<button id="no-letterhead" class="vts-midbtn blue-btn submitbtnImg btn_stocktake">Stocktake</button>
			</a>
			
			 <?php
			$ts_sql = mysql_query("
				SELECT *
				FROM `tech_stock`
				WHERE `staff_id` = {$sa['StaffID']}
				ORDER BY `date` DESC
				LIMIT 0, 1
			");
			$ts = mysql_fetch_array($ts_sql);
			if($ts['date']!=""){ ?>
				<span style="color:#00D1E5; font-size: 12px; float:left; margin-right: 7px;">Updated <br /><?php echo date("d/m/Y",strtotime($ts['date'])); ?></span>
			<?php	
			}
			?>	
			
			
		<?php	
		//}
		?>
		
		<?php //echo date("D")."-".date("t"); ?>
		
		
		<a href="/tech_run_keys.php?tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>&tr_id=<?php echo $tr_id; ?>" style="float:left;">
			<button id="no-letterhead" class="vts-midbtn blue-btn submitbtnImg btn_stocktake">KEYS</button>
		</a>
		
	</span>
    
    </div>
    
	<span class="vtsd-tp-center">Jobs Completed (<span style="color:green" id="jobs_completed_count_span"><?php echo $comp_count; ?></span>/<span style="color:#b4151b" id="jobs_count_span"><?php echo $jr_count; ?></span>)</span>
  
	
	
	

<div class="vtsd-tp-right">
	<?php 
		if($_SESSION['USER_DETAILS']['ClassName'] <> "TECHNICIAN"){ ?>
		<!--
		<a href='/export_techsheet.php?id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>' style="float:right;"><button class='submitbtnImg'>Export</button></a>
		<button id="no-letterhead" class="vts-midbtn blue-btn submitbtnImg batch_print_entry_letter">Batch Print Entry Notices</button>
		<button id="letterhead" class="submitbtnImg batch_print_entry_letter" style="margin-right: 5px;float:right;">+ Letter Head</button>
		-->
	<?php
	
	
		  $vip = array(2025,2070,58);
		 /* if (in_array($_SESSION['USER_DETAILS']['StaffID'], $vip)){ ?>
		  <form method="POST" enctype="multipart/form-data" action="/capture_screenshot.php" id="screenshot_form">
			<input type="hidden" name="image_data" id="image_data" />
			<!--<button type="button" class="submitbtnImg" style="margin-right: 5px;float:right;">Screenshot</button>-->
			
			<button type="button" id="btn_screenshot" class="submitbtnImg btn_screenshot" style="margin-right: 5px;float:right;">Screenshot</button>
			<input type="hidden" name="tr_id" id="tr_id" value="<?php echo $tr_id; ?>" />
			<input type="hidden" name="bulk_screenshot" id="bulk_screenshot" value="<?php echo $_REQUEST['bulk_screenshot']; ?>" />
			<input type="hidden" name="tech_name" id="tech_name" value="<?php echo $tech_name; ?>" />
			<input type="hidden" name="tr_date" id="tr_date" value="<?php echo $date; ?>" />
			<input type="hidden" name="tr_country_id" id="tr_country_id" value="<?php echo ($_GET['country_id']!='')?$_GET['country_id']:$_SESSION['country_default']; ?>" />
		 </form>
		  <?php	  
		  }
		  */
	
		}
	?>
	
    </div>
    
</div>


<? if($_SESSION['USER_DETAILS']['ClassName'] <> "TECHNICIAN"): ?>
<div class="vw-tch-sc">
<input type="hidden" id="hidden_tech_id" name="hidden_tech_id" value="<?php echo $tech_id; ?>">
<input type="hidden" id="hidden_day" name="hidden_day" value="<?php echo $day; ?>">
<input type="hidden" id="hidden_month" name="hidden_month" value="<?php echo $month; ?>">
<input type="hidden" id="hidden_year" name="hidden_year" value="<?php echo $year; ?>">
</div>

<div class="vtsd-tpc">

	
<span style="margin-right: 50px; margin-left: 5px; float: left;"><?php echo $crm->formatStaffName($t['first_name'],$t['last_name']).' '.date("d/m/Y",strtotime("{$year}-{$month}-{$day}")); ?></span>

<?php endif; ?>


<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<span style="background-color: pink; display: inline-block;margin-left: 5px; float: right; margin-right: 5px;">ERROR on Tech sheet</span>
	<span style="background-color: #fffca3; display: inline-block;margin-left: 5px; float: right; margin-right: 5px;">Unable to Complete</span>
	<span style="background-color: #c2ffa7; display: inline-block;margin-left: 50px; float: right; margin-right: 5px;">Completed</span>
<?php	
}
?>

<?php 
	if ( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
	<span style="display: inline-block;margin-left: 50px; float: right; margin-right: 5px;">
	<?php
	
	// only show on these guys
  //$vip = array(2025,2070,58);
  //if (in_array($_SESSION['USER_DETAILS']['StaffID'], $vip)){
		$domain = $_SERVER['SERVER_NAME'];
		//$domain = 'crmdev.com.au';
		$dev_str = (strpos($domain,"crmdev")===false)?'':'_dev';
		//var_dump(strpos($domain,"awdad"));
		echo '<div style="float:right; margin-right: 23px; margin-top: 5px;">View Map: <a href="'.PUBlIC_MAP_DOMAIN.'/tech_run'.$dev_str.'.php?api_key=sats123&tr_id='.$tr_id.'&country_id='.$_SESSION['country_default'].'"><img src="/images/google_map/main_pin_icon.png"></a></div>';
  //}
	
	?>
	</span>
	<?php
	 }
	?>

</div>

<?php

	if($_GET['order_by']){
		if($_GET['order_by']=='ASC'){
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
	$active = ($_GET['sort']=="")?'arrow-top-active':''; 	

?>

<table border=0 cellspacing=0 cellpadding=5 width=100% id="tbl_maps"  class="table-left tbl-fr-red" style="margin-bottom: 12px;">
<tr bgcolor="#b4151b" class="nodrop nodrag">
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<th><b>#</b></th>
<?php	
}
?>
<th><b>Status</b></th>
<th><b>Service</b></th>
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<th><b>Age</b></th>
	<th><b>Details</b></th>
<?php	
}
?>
<th><b>Job Type</b></th>
<th>
	<?php
	if( $_SESSION['country_default'] == 2 ){ // NZ only ?>
		<b>Alarm</b>
	<?php
	}
	?>	
</th>
<th><b>Ladder</b></th>
<th style="width: 300px;"><b>Address</b></th>
<th><b>Key #</b></th>
<th><b>Notes</b></th>
<th><b>Time</b></th>
<th><b>Agent</b></th>
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<th><a href="/view_tech_schedule_day.php?id=<?php echo $tech_id; ?>&day=<?php echo $_GET['day']; ?>&month=<?php echo $_GET['month']; ?>&year=<?php echo $_GET['year']; ?>&sort=j.completed_timestamp&order_by=<?php echo ($_GET['sort']=='j.completed_timestamp')?$ob:'ASC'; ?>" style="color:#ffffff"><div class="tbl-tp-name colorwhite bold"><b>Completed</b></div> <?php echo ($_GET['sort']=='j.completed_timestamp')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></th>
	<th><b><input type="checkbox" id="chk_all" /></b></th>
<?php
} 
?>
</tr>


<?php

	
	
	// start
	$start_acco_sql = mysql_query("
		SELECT *
		FROM `accomodation`
		WHERE `accomodation_id` = {$tr['start']}
		AND `country_id` = {$_SESSION['country_default']}
	");
	$start_acco = mysql_fetch_array($start_acco_sql);
	$accom_name = $start_acco['name'];
	$start_agency_address = $start_acco['address'];
?>


<tr class="nodrop nodrag">
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<td>1</td>
<?php	
}
?>
<td><?php echo $accom_name; ?></td>
<td></td>
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
<?php	
}
?>
<td colspan="3">&nbsp;</td>
<td><?php echo cutFullAddress($start_agency_address); ?></td>
<td></td>
<td><?php echo $tech_mob1; ?></td>
<td></td>
<td colspan="4">&nbsp;</td>
</tr>


<?php

	
$job_ctr = 0;

$trr_params = array(
	'custom_select' => "
		trr.`tech_run_rows_id`,
		trr.`row_id_type`,
		trr.`row_id`,
		trr.`hidden`,
		trr.`dnd_sorted`,
		trr.`highlight_color`,
		
		trr_hc.`tech_run_row_color_id`,
		trr_hc.`hex`,
		
		j.`id` AS jid,
		j.`precomp_jobs_moved_to_booked`,
		j.`completed_timestamp`,		

		p.`property_id`,

		a.`agency_id`,
		a.`allow_upfront_billing`
	",
	'display_only_booked'=>1
);
$jr_list2 = getTechRunRows($tr_id,$_SESSION['country_default'],$trr_params);
$j = 2;
$comp_count = 0;
$jobs_count = 0;
while( $row = mysql_fetch_array($jr_list2) ){ 

	

		// JOBS
		if( $row['row_id_type'] == 'job_id' ){
			
			$jr_sql = getJobRowData($row['row_id'],$_SESSION['country_default']);
			$row2 = mysql_fetch_array($jr_sql);
			
			$showRow = 1;
			
			/*
			// only show 240v rebook to electrician 
			if( $row2['job_type']=='240v Rebook' && $isElectrician==false  ){
				$showRow = 0;
			}else{
				$showRow = 1;
			}
			*/
			
			if( $row2['jdate']=="" || $row2['jdate']=="0000-00-00" || $row2['jdate']==$date ){
				
			if( $showRow==1 ){
				
			$jobs_count++;
			
			$bgcolor = "#FFFFFF";
			if($row2['job_reason_id']>0){
				$bgcolor = "#fffca3";
			}else  if($row2['ts_completed']==1){
				$bgcolor = "#c2ffa7";
				$comp_count++;
			}
			
			
			
			
			$j_created = date("Y-m-d",strtotime($row2['created']));
			$last_60_days = date("Y-m-d",strtotime("-60 days"));
		
			
			if( $row2['job_type']=='Lease Renewal' || $row2['job_type']=='Change of Tenancy' || $row2['j_status']=='DHA' || $row2['job_type']=='Fix or Replace' ){
				//$bgcolor = '#ffe5e5';
			}
			
			if($row2['j_status']=='Booked'){
				//$bgcolor = "#eeeeee";
			}
			
			if( $row['dnd_sorted']==0 ){
				$bgcolor = '#FFFF00';
			}
			
			// color row pink if precomp jobs was moved to booked and is techsheet complete
			if( $row['precomp_jobs_moved_to_booked']==1 ){
				$bgcolor = 'pink';
			}
			
			
			$serv_color = getServiceColor($row2['j_service']);
			
			// if job type is 'IC Upgrade' show IC upgrade icon
			$show_ic_icon = ( $row2['job_type'] == 'IC Upgrade' )?1:0;

			
		?>
			<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo $bgcolor; ?>">
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td><?php echo $j; ?></td>
				<?php	
				}
				?>
				
				
				<?php
				
				
				switch($row2['j_status']){
					case 'Merged Certificates':
						$jstatus_txt = 'Merged';
					break;
					case 'Pre Completion':
						$jstatus_txt = 'Pre Comp';
					break;
					default:
						$jstatus_txt = $row2['j_status'];
				}
				
				
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td class="jstatus">
						<a href="view_job_details.php?id=<?php echo $row2['jid']; ?>">
							<?php echo $jstatus_txt; ?>
						</a>
						<a href="view_job_details_tech.php?id=<?php echo $row2['jid']; ?>">[ts]</a>
						
					</td>
				<?php	
				}else{ ?>
					<td class="jstatus">
						<a href="view_job_details_tech.php?id=<?php echo $row2['jid']; ?>"><?php echo $jstatus_txt; ?></a>
						
					</td>
				<?php	
				}
				?>
				
				<td>
					<?php
					// display icons
					$job_icons_params = array(
						'service_type' => $row2['j_service'],
						'job_type' => $row2['job_type']
					);
					echo $crm->display_job_icons($job_icons_params);	 
					?>
				</td>
				
				<?php
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td>
					<?php
					// Age
					$date1=date_create($j_created);
					$date2=date_create(date('Y-m-d'));
					$diff=date_diff($date1,$date2);
					$age = $diff->format("%r%a");
					echo (((int)$age)!=0)?$age:0; 
					?>
					</td>
					
					<td>
					<?php
					
					// old job
					//echo (($j_created<$last_60_days)?'<img src="/images/hourglass.png" class="jicon" style="margin-right: 7px; cursor:pointer;" title="Old job" />':'');
					
					// if first visit
					if( $crm->check_prop_first_visit($row2['property_id']) == true   ){
						$fv = '<img src="/images/first_icon.png" class="jicon" style="width: 25px; margin-right: 7px; cursor:pointer;" title="First visit" />';
					}else{
						$fv = '';
					}
					
					echo $fv;
					
					
					//  if job type = COT, LR, FR, 240v or if marked Urgent
					if( 
						$row2['job_type'] == "Change of Tenancy" || 
						$row2['job_type'] == "Lease Renewal" || 
						$row2['job_type'] == "Fix or Replace" || 
						$row2['job_type'] == "240v Rebook" || 
						$row2['urgent_job'] == 1 
					){
						echo '<img src="/images/caution.png" class="jicon" style="height: 25px; cursor:pointer;" title="Priority Job" />';
					}
					
					if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
						echo '<img src="/images/key_icon_green.png" style="height: 25px;" title="Key Access Required" />';
					}
					
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
					$last4hours = date("Y-m-d H:i",strtotime("-4 hours"));
					//echo "Current time: {$current_time }<br />Log Time: {$job_log_time}<br /> last 4 hours: ".$last4hours;
					
					if( 
						$row2['j_status']=='To Be Booked' 
						&& mysql_num_rows($chk_logs_sql)>0 
						&& ( $job_log_time >= $last4hours && $job_log_time <= $current_time )
					){
						echo '<img src="/images/green_phone.png" style="height: 25px" title="Phone Call" />';
					}									
					
					?>
					</td>
					
				<?php	
				}
				?>
				
				
				
				
				<td>
				<?php
				// job type
				switch($row2['job_type']){
					case 'Once-off':
						$jt = 'Once-off';
					break;
					case 'Change of Tenancy':
						$jt = 'COT';
					break;
					case 'Yearly Maintenance':
						$jt = 'YM';
					break;
					case 'Fix or Replace':
						$jt = 'FR';
					break;
					case '240v Rebook':
						$jt = '240v';
					break;
					case 'Lease Renewal':
						$jt = 'LR';
					break;
				}
				?>
				<?php echo $jt; ?>
				</td>

				<td>
					<?php
					if( $_SESSION['country_default'] == 2 ){ // NZ only
						
						$has_orca_0_price = false;
						$has_cavi = false;

						$agen_al_sql_str = "
							SELECT aa.`agency_alarm_id`, aa.`price`, ap.`alarm_make` 
							FROM `agency_alarms` AS aa
							LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
							WHERE aa.`agency_id` = {$row2['agency_id']}		
							AND ap.`active` = 1											
						";
						$agen_al_sql = mysql_query($agen_al_sql_str);
						
						while( $agen_al_row = mysql_fetch_array($agen_al_sql) ){

							if( $agen_al_row['alarm_make'] == 'Orca' && $agen_al_row['price'] == 0 ){
								$has_orca_0_price = true;
							}

							if( $agen_al_row['alarm_make'] == 'Cavius' ){
								$has_cavi = true;
							}

						}

						if( $has_orca_0_price == true && $has_cavi == false ){
							echo "Orca";
						}else{
							echo "Cavius";
						}

					}					
					?>
				</td>
				
				<td>
				<?php
				if( $row2['survey_ladder']!='' ){ 
				
					// 4ft was changed to 3ft. older data already 4ft so just change labels
					$survey_ladder = '';
					if($row2['survey_ladder']=='4FT'){
						$survey_ladder = '3FT';
					}else{
						$survey_ladder = $row2['survey_ladder'];
					}
				
				?>
				
					<div class="jfloatLeft"><img src="images/tools/ladder.png" class="ladder_icon" /></div>
					<div class="jfloatLeft" style="margin-top: 6px;">(<?php echo $survey_ladder; ?>)</div>
				
				<?php
				}
				?>					
				</td>
				
				<?php
				$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
					<td><a href="view_property_details.php?id=<?php echo $row2['property_id']; ?>"><?php echo $paddress; ?></a></td>						
				<?php				
				}else{ ?>
					<td><a href="http://maps.google.com/?q=<?php echo $paddress; ?>" target="_blank"><?php echo $paddress; ?></a></td>						
				<?php	
				}
				?>
				
				
				<td>
					<span class="key_num_span"><?php echo $row2['key_number']; ?></span>
					<?php
					if( $row2['key_access_required'] == 1 ){ ?>
						<img class="key_icon" src="/images/key_icon.png" />
						<?php
						// if job is entry notice, show pdf link
						if( $row2['job_entry_notice']==1 ){ 									
							$orig_url = "view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid']);
						?>
							<a target="_blank" href="<?php echo $orig_url; ?>"><img src="/images/pdf.png" /></a>					
						<?php	
						}
						?>
					<?php
					}
					?>					
				</td>
				
				<td><?php echo $row2['tech_notes']; ?></td>
				<td>				
					<div style="position: relative; bottom: 4px;">
						<?php 
						echo $row2['time_of_day']; 
						?>
						
						<?php
						if($row2['p_comments']!=''){ ?>
							<img class="time_img img_pnotes" src="/images/notes.png" />
						<?php	
						}					
						?>
						
						<?php
						if( $row2['call_before']==1 && $row2['call_before_txt']!='' ){ ?>
							<img class="time_img img_call_before" src="/images/red_phone2.png" title="Phone Call" />
							<span style="color:#b4151b;"><?php echo $row2['call_before_txt']; ?></span>
							<div class="time_div_toggle booked_with_tenant_div">
								<?php
								// tricky, need to get the booked with tenant phone
								
								$pt_params = array( 
									'property_id' => $row2['property_id'],
									'active' => 1
								 );
								$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
								
								$tenant_phone = '';
								while( $pt_row = mysql_fetch_array($pt_sql) ){
									
									if( $pt_row['tenant_firstname'] == $row2['booked_with'] ){
										$tenant_phone = $pt_row['tenant_mobile'];
									}
									
								}
								?>
								<?php echo $row2['booked_with']; ?> <?php echo $tenant_phone; ?>
							</div>						
						<?php	
						}
						?>
						
						<?php
						if($row2['p_comments']!=''){ ?>
							<div class="time_div_toggle property_notes_div">
								<?php echo $row2['p_comments']; ?>
							</div>
						<?php	
						}
						?>	
					</div>
				</td>	
				
				<?php 
				if( $_SESSION['USER_DETAILS']['ClassName'] != 'TECHNICIAN' ){ ?>
				
					<td>
						<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row2['agency_id']}"); ?>
						<a href="<?php echo $ci_link; ?>" class="agency_td">
							<?php echo str_replace('*do not use*','',$row2['agency_name']); ?>
						</a>
						<br /><?php echo $row2['a_phone']; ?>
					</td>
				
				<?php	
				}else{ ?>
					<td>
						<a href="javascript:void(0);" class="agency_name_link">
							<?php echo str_replace('*do not use*','',$row2['agency_name']); ?>
						</a>						
						<input type="hidden" class="agency_address_txt" name="agency_address_txt" value="<?php echo "{$row2['a_address_1']} {$row2['a_address_2']} {$row2['a_address_3']} {$row2['a_postcode']} \n{$row2['a_phone']}"; ?>" />
					</td>
				<?php	
				}
				?>
				
				
				
				
				<?php
				
				if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){
					echo "<td>".(($row['completed_timestamp']!="")?date("H:i",strtotime($row['completed_timestamp'])):'')."</td>";
					
					echo '
					<td>
						<input type="checkbox" class="chk_job_id" value="'.$row['jid'].'" style="display: block; float: left; margin: 9px 5px 0 0; width: auto;" />
					</td>';
				}
				
				?>
			</tr>
		<?php	
			// store it on property address array
			$prop_address[$i]['address'] = "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}, {$_SESSION['country_name']}";
			$prop_address[$i]['status'] = $row2['j_status'];
			$prop_address[$i]['created'] = date("Y-m-d",strtotime($row2['created']));
			$prop_address[$i]['urgent_job'] = $row2['urgent_job'];
			$prop_address[$i]['lat'] = $row2['p_lat'];
			$prop_address[$i]['lng'] = $row2['p_lng'];
			$i++;
			
			$j++;
			
			}
			
			}
				
			}else if( $row['row_id_type'] == 'keys_id' ){ 
			
				// KEYS
				$k_sql = getTechRunKeys($row['row_id'],$_SESSION['country_default']);
				$kr = mysql_fetch_array($k_sql);

				// FN script
				$fn_agency_arr = $crm->get_fn_agencies();
				$fn_agency_main = $fn_agency_arr['fn_agency_main'];
				$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
				//$fn_agency_sub_imp = implode(",",$fn_agency_sub);
				
				$nobk = getNumberOfBookedKeys($tech_id,$date,$_SESSION['country_default'],$kr['agency_id']);
				
				if( $nobk > 0 || in_array($kr['agency_id'],$fn_agency_sub) ){ // only show agency keys, that has remaining booked keys
				

				?>
					<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo ($kr['completed']==1)?'#c2ffa7':'#eeeeee'; ?>;">
						
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td><?php echo $j; ?></td>
						<?php	
						}
						?>
						<td>
							<?php 
								if($kr['completed']==1){
									$kr_act = explode(" ",$kr['action']);
									$temp2 = ($kr['action']=="Drop Off")?'p':'';
									$temp = "{$kr_act[0]}{$temp2}ed";
									$action = "{$temp} {$kr_act[1]}";
								}else{
									$action = $kr['action'];
								}
								echo $action;
							?>
						</td>	
						<td></td>
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						<?php	
						}
						?>
						<td><img src="/images/key_icon_green.png" /></td>	
						<td></td>
						<td></td>
						<td><?php echo "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}"; ?><?php echo ($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':''; ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><?php echo $kr['agency_hours']; ?></td>	

						<?php 
						if( $_SESSION['USER_DETAILS']['ClassName'] != 'TECHNICIAN' ){ ?>
						
							<td>
							<?php $ci_link2 = $crm->crm_ci_redirect("/agency/view_agency_details/{$kr['agency_id']}"); ?>
								<a href="<?php echo $ci_link2; ?>">
									<?php echo str_replace('*do not use*','',$kr['agency_name']); ?>
								</a><br /><?php echo $kr['phone']; ?>
							</td>
						
						<?php	
						}else{ ?>
							<td>
								<a href="javascript:void(0);" class="agency_name_link">
									<?php echo str_replace('*do not use*','',$kr['agency_name']); ?>
								</a>								
								<input type="hidden" class="agency_address_txt" name="agency_address_txt" value="<?php echo "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']} \n{$kr['a_phone']}"; ?>; ?>" />
							</td>
						<?php	
						}
						?>
							
						
						
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td>
								<?php echo ($kr['completed_date']!="")?date("H:i",strtotime($kr['completed_date'])):"";	?>
							</td>
							<td>&nbsp;</td>
						<?php	
						}
						?>				
					</tr>
				<?php
				
				
				// get gecode
				$prop_address[$i]['address'] = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}, {$_SESSION['country_name']}";
				$prop_address[$i]['is_keys'] = 1;
				$prop_address[$i]['lat'] = $kr['lat'];
				$prop_address[$i]['lng'] = $kr['lng'];
				$i++;
				
				$j++;
				
				}
				
			}else if( $row['row_id_type'] == 'supplier_id' ){ 
			
				// supplier
				$sup_sql = getTechRunSuppliers($row['row_id']);
				$sup = mysql_fetch_array($sup_sql);
				
				if($sup['on_map']==1){
				

				?>
					<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:#eeeeee;">
						
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td><?php echo $j; ?></td>
						<?php	
						}
						?>
						<td>Supplier</td>
						<td></td>
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						<?php	
						}
						?>
						<td colspan="3">&nbsp;</td>						
						<td><?php echo cutFullAddress($sup['sup_address']); ?></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>						
						
						<?php 
						if( $_SESSION['USER_DETAILS']['ClassName'] != 'TECHNICIAN' ){ ?>
						
							<td>
								<a href="suppliers.php">
									<?php echo $sup['company_name']; ?>						
								</a><br />
								<?php echo $sup['phone']; ?>
							</td>
						
						<?php	
						}else{ ?>
							<td>
								<a href="javascript:void(0);" class="agency_name_link">
								<?php echo $sup['company_name']; ?>						
								</a><br />
								<?php echo $sup['phone']; ?>							
								<input type="hidden" class="agency_address_txt" name="agency_address_txt" value="<?php echo  "{$sup['sup_address']} \n{$sup['phone']}"; ?>" />
							</td>
						<?php	
						}
						?>
									
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						<?php	
						}
						?>				
					</tr>
				<?php
				// get gecode
				$prop_address[$i]['address'] = $sup['sup_address'];
				$prop_address[$i]['is_keys'] = 1;
				$prop_address[$i]['lat'] = $sup['lat'];
				$prop_address[$i]['lng'] = $sup['lng'];
				$i++;
				
				$j++;
				
				}
				
			}
		
	
		
	
		
		

}

?>
	
	
<?php

$end_acco_sql = mysql_query("
	SELECT *
	FROM `accomodation`
	WHERE `accomodation_id` = {$tr['end']}
	AND `country_id` = {$_SESSION['country_default']}
");
$end_acco = mysql_fetch_array($end_acco_sql);
$accom_name = $end_acco['name'];
$end_agency_address = $end_acco['address'];

?>
<tr class="nodrop nodrag">
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<td>
	<?php
	$end_point_index = $j; 

	echo $end_point_index;
	?>
	</td>
<?php
}
?>
<td><?php echo $accom_name; ?></td>
<td></td>
<?php
if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
<?php	
}
?>
<td colspan="3">&nbsp;</td>
<td><?php echo cutFullAddress($end_agency_address); ?></td>
<td>&nbsp;</td>
<td><?php echo $tech_mob1; ?></td>
<td colspan="4">&nbsp;</td>
</tr>

</table>

<?php
if($_SESSION['USER_DETAILS']['ClassName'] == "TECHNICIAN"){ ?>
	<span style="background-color: pink; display: inline-block;margin-left: 5px; float: right; margin-right: 5px;">ERROR on Tech sheet</span>
	<span style="background-color: #fffca3; display: inline-block;margin-left: 5px; float: right; margin-right: 5px;">Unable to Complete</span>
	<span style="background-color: #c2ffa7; display: inline-block;margin-left: 50px; float: right; margin-right: 5px;">Completed</span>
<?php	
}
?>


</div>

<input type="hidden" id="jobs_count" value="<?php echo $jobs_count; ?>" />
<input type="hidden" id="comp_count" value="<?php echo $comp_count; ?>" />

<div style="margin-top: 15px; float: right; display:none;" id="assign_div">
			Time:
			<input type="text" id="job_time" />
			<button type="button" id="btn_set_time" class="blue-btn submitbtnImg">Set Time</button><br />
			
			<button style=" float: right; margin-top: 10px;" class="submitbtnImg" id="btn_create_rebook" type="button">Rebook</button>
			
</div>

  </div>

</div>

<br class="clearfloat" />


<script type="text/javascript" src="/js/html2canvas.js"></script>
<script type="text/javascript" src="/js/jquery.plugin.html2canvas.js"></script>
<script>

function markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys){
	
	jQuery.ajax({
		type: "POST",
		url: "ajax_mark_key_as_completed.php",
		data: { 
			kr_id: kr_id,
			key_completed: key_completed,
			agency_staff: agency_staff,
			number_of_keys: number_of_keys
		}
	}).done(function( ret ) {
		
		/*
		var k_comp = parseInt(ret);
		if(k_comp==1){
			var k_color = "#c2ffa7";
			var key_acction = obj.parents("tr:first").find(".key_action").val();
			if(key_acction=='Pick Up'){
				var k_txt = 'Picked Up';
			}else if(key_acction=='Drop Off'){
				var k_txt = 'Dropped Off';
			}
			obj.parents("tr:first").find(".agency_staff_span").show();
			obj.parents("tr:first").find(".agency_staff_div").hide	();
		}else{
			var k_color = "#FFFFFF";
			var key_acction = obj.parents("tr:first").find(".key_action").val();
			if(key_acction=='Pick Up'){
				var k_txt = 'Pick Up';
			}else if(key_acction=='Drop Off'){
				var k_txt = 'Drop Off';
			}
		}
		obj.parents("tr:first").find(".key_completed").val(ret);
		obj.parents("tr:first").attr("bgcolor",k_color);
		obj.parents("tr:first").find(".link_keys").html(k_txt);
		if(agency_staff!=""){
			obj.parents("tr:first").find(".agency_staff_span").html("Agency Staff: "+agency_staff+"<br /> Number of Keys:"+number_of_keys);
		}
		*/
		
		window.location="/view_tech_schedule_day.php?id=<?php echo $tech_id; ?>&day=<?php echo $_GET['day']; ?>&month=<?php echo $_GET['month']; ?>&year=<?php echo $_GET['year']; ?>";
	
	});	
			
}

// jobs completed count script
function jobsCompletedCount(){
	var comp_count = jQuery("#comp_count").val();
	var jobs_count = jQuery("#jobs_count").val();
	
	jQuery("#jobs_completed_count_span").html(comp_count);
	jQuery("#jobs_count_span").html(jobs_count);
}

function getTechRunNewLists(gao){

	jQuery("#searching_for_new_jobs_div").show();
	jQuery.ajax({
		type: "POST",
		url: "ajax_tech_run_get_new_list.php",
		data: { 
			tr_id: '<?php echo $tr_id; ?>',
			tech_id: '<?php echo $tech_id; ?>',
			date: '<?php echo $date; ?>',
			sub_regions: '<?php echo $sub_regions; ?>',
			get_assigned_only: gao
		}
	}).done(function( ret ){
		
		jQuery("#searching_for_new_jobs_div").hide();
		console.log('new jobs: '+ret);
		var msg = '';
		
		if(parseInt(ret)>0){
			//alert('New Jobs avavilable');			
			msg = 'New Jobs Available! <a href="/tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>">Refresh</a>';
			jQuery("#new_job_success_msg").html(msg);
			jQuery("#new_job_success_msg").slideDown();
		}else{
			//msg = 'No New Jobs Found';
		}
		
		
		
		//obj.parents("tr:first").find(".en_a_link").prop("href",ret);
		
		//window.location='/set_tech_run.php?tr_id=<?php echo $tr_id; ?>';
		//location.reload();
		//window.location='/main.php';
	});
	
}

function screenShotScript(){
	jQuery('#screenshot_target').html2canvas({
		onrendered: function (canvas) {
			//Set hidden field's value to image data (base-64 string)
			var image_data = canvas.toDataURL("image/png");
			//console.log(image_data);
			jQuery('#image_data').val(image_data);
			//Submit the form manually
			jQuery('#screenshot_form').submit();
		}
	});
}

$(document).ready(function() {
	
	
	
	// key num toggle
	jQuery(".key_icon").click(function(){
		jQuery(this).hide();
		jQuery(this).parents("tr:first").find(".key_num_span").show();
	});
	
	
	
	
	// agency name show address script
	jQuery(".agency_name_link").click(function(){

		var agency_address = jQuery(this).parents("td:first").find(".agency_address_txt").val();
		alert(agency_address);	

	});
	
	
	
	
	jQuery(".img_pnotes").click(function(){
		
		jQuery(this).parents("tr:first").find(".property_notes_div").toggle();
		
	});
	
	
	jQuery(".img_call_before").click(function(){
		
		jQuery(this).parents("tr:first").find(".booked_with_tenant_div").toggle();
		
	});
	
	
	
	// screenshot script
	jQuery("#btn_screenshot").click(function(){
		
		<?php
		if( $_REQUEST['bulk_screenshot'] == 1 ){ ?>
			screenShotScript();
		<?php	
		}else{ ?>
			if( confirm("This will capture screenshot and send to tech email... proceed?") ){
				screenShotScript();
			}	
		<?php	
		}
		?>
		
			
		
	});
	
	
	<?php
	if( $_REQUEST['bulk_screenshot'] == 1 ){ ?>
		jQuery("#btn_screenshot").click();
	<?php	
	}
	?>
	
	
	<?php
	if( $hasTechRun == true ){ ?>
		getTechRunNewLists(1);
	<?php	
	}
	?>
	
	
	
	// run jobs complete count script
	jobsCompletedCount();
	
	// rebook script
	// rebook
	jQuery("#btn_create_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".chk_job_id:checked").each(function(){
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
				//window.location="/view_tech_schedule_day.php?id=<?php echo $tech_id; ?>&day=<?php echo $_GET['day']; ?>&month=<?php echo $_GET['month']; ?>&year=<?php echo $_GET['year']; ?>";
				window.location="/tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>";
			});				
			
		}
		
	});
	
	// check all toggle
	jQuery("#chk_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".chk_job_id:visible").prop("checked",true);
		jQuery(".job_row").addClass("jrow_highlight");
		jQuery("#assign_div").show();
	  }else{
		jQuery(".chk_job_id:visible").prop("checked",false);
		jQuery(".job_row").removeClass("jrow_highlight");
		jQuery("#assign_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".chk_job_id").click(function(){
		
		var state = jQuery(this).prop("checked");
		
		if(state==true){
			jQuery(this).parents("tr.job_row").addClass("jrow_highlight");
		}else{
			jQuery(this).parents("tr.job_row").removeClass("jrow_highlight");
		}
		

	  var chked = jQuery(".chk_job_id:checked").length;
	  
	  if(chked>0){		
		jQuery("#assign_div").show();
	  }else{
		jQuery("#assign_div").hide();
	  }

	});
	
	// update time script
	jQuery("#btn_set_time").click(function(){
		
		var job_id = new Array();
		jQuery(".chk_job_id:checked").each(function(){
			job_id.push(jQuery(this).val());
		});
		var job_time = jQuery("#job_time").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_job_time.php",
			data: { 
				job_id: job_id,
				job_time: job_time
			}
		}).done(function( ret ) {
			window.location="/tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>";
		});	
		
	});
	
	/*
	// keys link
	jQuery(".link_keys").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".key_routes_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		
		if(key_completed==0 ){
			obj.parents("tr:first").find(".agency_staff_span").hide();
			obj.parents("tr:first").find(".agency_staff_div").show();
		}else{
			markKeysCompleted(obj,kr_id,key_completed);
		}
		
	});
	*/
	
	// keys update
	jQuery(".btn_agency_staff").click(function(){
		
		var obj = jQuery(this);
		var kr_id = obj.parents("tr:first").find(".key_routes_id").val();
		var key_completed = obj.parents("tr:first").find(".key_completed").val();
		var key_action = obj.parents("tr:first").find(".key_action").val();
		var agency_staff = obj.parents("tr:first").find(".agency_staff").val();
		var number_of_keys = obj.parents("tr:first").find(".number_of_keys").val();
		
		markKeysCompleted(obj,kr_id,key_completed,agency_staff,number_of_keys);
		
	});
	

	jQuery("#update_kms").click(function(){
		var vehicles_id = jQuery(this).parents(".aviw_drop-h:first").find("#vehicles_id").val();
		var kms = jQuery(this).parents(".aviw_drop-h:first").find("#kms").val();
		jQuery.ajax({
			type: "POST",
			url: "ajax_add_kms.php",
			data: { 
				kms: kms,
				vehicles_id: vehicles_id
			}
		}).done(function( ret ) {
			var full_url = window.location.href;
			window.location=full_url+"&success=kms";
		});	
	});	

	$("button.batch_print_entry_letter").click(function() {
		var tech_id = $("#hidden_tech_id").val();
		var day = $("#hidden_day").val();
		var month = $("#hidden_month").val();
		var year = $("#hidden_year").val();

		if(this.id == "no-letterhead")
		{
			var letterhead = 0;
		}
		else
		{
			var letterhead = 1;
		}

		var win=window.open("<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/batch_view_entry_notice.php?'; ?>letterhead=" + letterhead + "&tech_id=" + tech_id + "&day=" + day + "&month=" + month + "&year=" + year, '_blank');
        win.focus();
        return false;

		console.log(tech_id + " " + day + " " + month + " " + year);
	});
	
	/*
	// invoke table DND
	jQuery("#tbl_maps").tableDnD({
    	onDrop: function(table, row) {
			var job_id = jQuery.tableDnD.serialize({
				'serializeRegexp': null
			});
			
			jQuery.ajax({
				method: "GET",
				url: "ajax_update_map_sort.php?tech_id=<?php echo $tech_id; ?>&"+job_id
			}).done(function( ret ) {				
				//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
				jQuery("#btn_update_map").show();
			});	
			
		}
    });
	*/
	
	jQuery("#tbl_maps").tableDnD({
    	onDrop: function(table, row) {
			var job_id = jQuery.tableDnD.serialize({
				'serializeRegexp': null
			});
			
			jQuery.ajax({
				method: "GET",
				url: "ajax_sort_tech_run.php?tr_id=<?php echo $tr_id; ?>&"+job_id
			}).done(function( ret ) {				
				window.location='/tech_day_schedule.php?tr_id=<?php echo $tr_id; ?>';
				//jQuery("#btn_update_map").show();
			});	
			
		}
    });
	
});
</script>
</body>
</html>
