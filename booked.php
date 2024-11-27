<?php

$title = "Booked Report";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$tech = mysql_real_escape_string($_REQUEST['tech']);
//$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$date = ($_GET['day']!='' && $_GET['month']!='' && $_GET['year']!='')?"{$_GET['year']}-{$_GET['month']}-{$_GET['day']}":date("Y-m-d");

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.date';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&date=".urlencode($date)."&date=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = bkd_getPrecompletedJobs($offset,$limit,$sort,$order_by,$job_type,$service,$date,$phrase,$tech);
$ptotal = mysql_num_rows(bkd_getPrecompletedJobs('','',$sort,$order_by,$job_type,$service,$date,$phrase,$tech));




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
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/booked.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example'>
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
	
				
				  
					 <?php
					// get alarm job type
					$ajt_sql = mysql_query("
						SELECT DISTINCT (
							j.`service`
						), ajt.`id` , ajt.`type`
						FROM `jobs` AS j
						LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
						LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
						LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
						WHERE a.`country_id` = {$_SESSION['country_default']}
						AND j.`date` = '{$date}'
						AND j.`service` != ''
						AND p.`deleted` =0
						AND a.`status` = 'active'
						ORDER BY ajt.`type`
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
					
				
					
					
				
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					
					
					 <?php
					// get alarm job type
					$t_sql = mysql_query("
						SELECT DISTINCT j.`assigned_tech`, sa.`FirstName` , sa.`LastName`
						FROM `jobs` AS j
						LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
						LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
						LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
						LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
						WHERE a.`country_id` = {$_SESSION['country_default']}
						AND j.`date` = '{$date}'
						AND p.`deleted` =0
						AND a.`status` = 'active'
						ORDER BY sa.`FirstName` , sa.`LastName`
					");
				  ?>
					<div class="fl-left">
						<label>Technician</label>
						<select name="tech" style="width: 125px;">
							<option value="">Any</option>
							<?php
							while($ajt=mysql_fetch_array($t_sql)){ ?>
								<option <?php echo ($ajt['assigned_tech']==$tech) ? 'selected="selected"':''; ?> value="<?php echo $ajt['assigned_tech']; ?>" ><?php echo "{$ajt['FirstName']} {$ajt['LastName']}"; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search' />
					
    
					
					
					
				</div>

				

				<!-- duplicated filter here -->

					  
					  
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
			
				<th>
					<div class="tbl-tp-name colorwhite bold">Date</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.date&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.date')?'active':''; ?>"></div>
					</a>
				</th>
				
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
				<th>Job #</th>
				
			</tr>
				<?php
				
				
				$i= 0;
				$tot_price = 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						$row_color = '';
						$reason = '';
						$hide_ck = 0;
						
						/*
						if( 
							$row['job_reason_id']>0 || 
							bkd_isAlarmExpiryDatesMatch($row['jid'])==true || 
							bkd_isJobZeroPrice_Ym($row['jid'])==true ||
							bkd_isJobHasNewAlarm($row['jid'])==true ||
							bkd_isPropertyAlarmExpired($row['property_id'])==true
						){
							$row_color = 'yello_mark';
						}else{
							$row_color = '';
						}
						*/
						
						
						/*
						// Expiry Dates don't match
						if( bkd_isAlarmExpiryDatesMatch($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Expiry Dates Don't Match <br />";
						}
						
						// Job is $0 and YM
						if( bkd_isJobZeroPrice_Ym($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Job is $0 and YM <br />";
						}
						
						// New Alarms Installed
						if( bkd_isJobHasNewAlarm($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "New Alarms Installed <br />";
						}
						
						// Property has Expired Alarms
						if( bkd_isPropertyAlarmExpired($row['jid'],$row['property_id'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= "Expired Alarms <br />";
						}
						
						// COT FR and LR price must be 0
						if( bkd_CotLrFrPriceMustBeZero($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= getJobTypeAbbrv($row['job_type'])." must be $0 <br />";
						}
						
						// If 240v has 0 price
						if( bkd_is240vPriceZero($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " Check Job Type <br />";
						}
						
						// display error for these agencies
						if( bkd_ifDHAAgencies($row['jid'])==true ){
							$hide_ck = 1;
							$row_color = 'green_mark';
							$reason .= " DHA Property <br />";
						}
						
						// if 240v rebook
						if($row['job_type']=='240v Rebook'){
							$hide_ck = 1;
							$row_color = 'green_mark';
							//$reason .= "240v Rebook <br />";
						}
						*/
						
						// if completed 
						if($row['ts_completed']==1){
							$hide_ck = 1;
							$row_color = 'green_mark';
							//$reason .= "240v Rebook <br />";
						}
						
						// MUST BE THE LAST - not completed due to = job reason
						if( $row['job_reason_id']>0 && $row['ts_completed']==0 ){
							$hide_ck = 0;
							$row_color = 'yello_mark';
							$reason .= "{$row['jr_name']} <br />";
						}
						
				?>
						<tr class="body_tr jalign_left <?php echo $row_color; ?>">
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
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
							<td>$<?php echo $row['job_price']; ?></td>
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td>
								<?php
								
								$tr_sql = mysql_query("
									SELECT * 
									FROM  `tech_run` AS tr 
									WHERE tr.`date` = '{$row['jdate']}'
									AND tr.`assigned_tech` = {$row['assigned_tech']}
									AND tr.`country_id` = {$_SESSION['country_default']}			
								");
								$tr = mysql_fetch_array($tr_sql);
								
								?>
								<a href="/tech_day_schedule.php?tr_id=<?php echo $tr['tech_run_id']; ?>">
									<?php echo "{$row['FirstName']} ".strtoupper(substr($row['LastName'],0,1))."."; ?>
								</a>	
							</td>
							<td><?php echo (($row['door_knock']==1)?'DK':''); ?></td>
							<td><?php echo $reason; ?></td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
						</tr>
						
				<?php
					//$tot_price += $row['job_price'];
					$i++;
					}
					?>
					
					<tr>
						<td colspan="2"></td>
						<td style="text-align: left;">$<?php echo (bkd_getPriceTotal($date)+bkd_alarmPriceTotal($date)); ?></td>
						<td colspan="6"></td>
					</tr>
					
					<?php
				}else{ ?>
					<td colspan="9" align="left">Empty</td>
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
		
		<a href="/booked.php?day=<?php echo date('d'); ?>&month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>">Today ($<?php echo (bkd_getPriceTotal($date)+bkd_alarmPriceTotal($date)) ?>)</a> |
		<?php
		for($i=1;$i<=3;$i++){ 
		$day = date('d',strtotime("+{$i} days"));
		$month = date('m',strtotime("+{$i} days"));
		$year = date('Y',strtotime("+{$i} days"));
		$dynamic_date = date('Y-m-d',strtotime("+{$i} days"));
		?>
			<a href="/booked.php?day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"><?php echo date('M d',strtotime("+{$i} days")); ?> ($<?php echo (bkd_getPriceTotal($dynamic_date)+bkd_alarmPriceTotal($dynamic_date)) ?>)</a> <?php echo ($i<3)?'|':''; ?>
		<?php	
		}
		?>
		
		

		<div style="margin-top: 15px; float: right; display:none;" id="rebook_div">
			<button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create 240v Rebook</button>
			<button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create Rebook</button>
			<button type="button" id="btn_move_to_merged" class="submitbtnImg" style="background-color:green">Move to Merged</button>
		</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	
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
		
		if(confirm("Are you sure you want to continue?")==true){
			
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