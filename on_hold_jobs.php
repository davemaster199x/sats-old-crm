<?php

$title = "On Hold";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'On Hold';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.start_date';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion);
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion));




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
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong><?php echo $title; ?></strong></a></li>
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

				 
	
					<div class="fl-left">
						<label>Job Type:</label>
						<select name="job_type" style="width: 125px;">
							<option value="">Any</option>
							<?php
							$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'j.`job_type`');
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
				  
					 <?php
					$ajt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'j.`service`');
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
					
			
					<div class="fl-left">
						<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
						<select id="state" name="state" style="width: 70px;">
						<option value="">Any</option> 			
						<?php
						$jstate_sql = $jc->getJobs('','',$sort,$order_by,'',$job_status,'','','','','p.`state`');
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['state']; ?>" <?php echo ($jstate['state']==$state) ? 'selected="selected"':''; ?>><?php echo $jstate['state']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
					<style>
					
						button.ui-multiselect {
							background: white none repeat scroll 0 0;
							box-shadow: 0 0 2px #404041 inset;
							color: #000000;
							font-family: arial,sans-serif;
							font-size: 13.3333px;
							font-style: normal;
							font-weight: 400;
							line-height: 22px;
							padding: 5px;	
						}
						.ui-multiselect-checkboxes span{
							font-family: arial,sans-serif;
							font-size: 13.3333px;
							font-style: normal;
							font-weight: 400;
						}	

					</style>
					
					<div class="fl-left">
						<label><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?>:</label>
						<input type="text" readonly="readonly" name='region_ms' id='region_ms' class='addinput searchstyle vwjbdtp' style='width: 100px !important;' />
						<style>
							#region_dp_div{
								width:auto; 
								border-radius: 5px;
								padding: 7px;
								position: absolute;
								top: 112px;
								background: #ffffff;
								border: 1px solid #cccccc;
								display: none;
								z-index: 99999;
							}
							.region_dp_header{
								background: #b4151b none repeat scroll 0 0;
								border-radius: 10px;
								color: #ffffff;
								padding: 6px;
								text-align: left;
							}
							#region_dp_div ul{
								list-style: outside none none;	
								padding: 0;
								margin: 0;
								text-align: left !important;
							}	
							.reg_db_main_reg{
								color: #b4151b;
								cursor: pointer;
								font-weight: bold;
								text-align: center;
							}
							#region_dp_div input{
								width:auto;
								float:none;
							}
							.region_wrapper{
								border-bottom: 1px solid;
								color: #b4151b;
							}
							</style>
							<div id="region_dp_div">
							<div class="region_dp_header">
								<ul>
								<?php
								// get state
								$jstate_sql = mysql_query("
									SELECT DISTINCT (
										p.`state`
									)
									FROM  `jobs` AS j
									LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
									LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
									WHERE p.`deleted` =0
									AND a.`status` =  'active'
									AND j.`del_job` =0
									AND a.`country_id` = {$_SESSION['country_default']}
									AND p.`state` != '' 
									AND p.`state` IS NOT NULL
									AND j.`status` = '{$job_status}'
									ORDER BY p.`state`
								");
								while($jstate =  mysql_fetch_array($jstate_sql)){ 
								
								// get state regions
								$main_reg_pc = "";
								$temp_sql = mysql_query("
									SELECT * 	
									FROM  `regions`
									WHERE `region_state` = '{$jstate['state']}'
									AND `country_id` = {$_SESSION['country_default']}
									AND `status` = 1
								");
								while( $temp = mysql_fetch_array($temp_sql) ){
									$main_reg_pc .= ','.$jc->getSubRegionPostcodes($temp['regions_id']);
								}
								
								$reg_arr1 = explode(",",$main_reg_pc);
								$reg_arr2 = array_filter($reg_arr1);
								$main_region_postcodes = implode(",",$reg_arr2);
								//$main_region_postcodes = substr($main_reg_pc,1);
								$jcount = $jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status);
								?>
									<li>
										<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo ($jcount>0)?"({$jcount})":''; ?></span>
										<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
									</li>
								<?php	
								} 
								?>
								</ul>
							</div>
							<div class="region_dp_body">								
							</div>
							</div>
							<script>
							jQuery(document).ready(function(){
								
								// clicking out the container script :)
								jQuery(document).mouseup(function (e)
								{
									var container = jQuery("#region_dp_div");

									if (!container.is(e.target) // if the target of the click isn't the container...
										&& container.has(e.target).length === 0) // ... nor a descendant of the container
									{
										container.hide();
									}
								});
								
								jQuery("#region_ms").click(function(){

								  jQuery("#region_dp_div").show();

								});
								
								/*
								jQuery(document).on("click",".reg_db_main_reg",function(){
									
									var sub_reg_vis = jQuery(this).parents("li:first").find(".reg_db_sub_reg").css("display");
									if(sub_reg_vis=='block'){
										jQuery(this).parents("li:first").find(".reg_db_sub_reg").hide();
									}else{
										jQuery(this).parents("li:first").find(".reg_db_sub_reg").show();
									}
								
								});
								*/
								
							});
							</script>
					</div>
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>
					
					<? 
					$cron_ctry = '';
					if( $_SESSION['country_default'] == 1 ){ // au
						$cron_ctry = 'au';
					}else if( $_SESSION['country_default'] == 2 ){ // nz
						$cron_ctry = 'nz';
					}
					?>
					<div class='fl-right'>
						<a href="/cronjobs/cron_on_hold_jobs_move_for_booking_<?php echo $cron_ctry ?>.php" target="_blank">
							<button type="button" id="btn_run_cron" class="blue-btn submitbtnImg">Run Cron</button>
						</a>
					</div>
					

					<!--
					<div class='fl-right'>
						<button type="button" id="btn_to_be_booked" class="blue-btn submitbtnImg">MOVE for Booking</button>
					</div>
					-->

				<!-- duplicated filter here -->

					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['sort']==""){
				$sort_arrow = 'up';
			}
			
			?>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">

				<th>Start Date</th>
				
				<th>Age</th>
				
				<th><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
			
				<th>Job Type</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				
				
				
				<th>Address</th>
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>
				<th style="width:20%">Comments</th>
				<th>Job #</th>
				<th>Last Contact</th>
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						$tdf_start_date = date("Y-m-d",strtotime($row['start_date']." -1 days"));		
						
						$row_color = "style='background-color:#ffff9d;'";
						
						if( date("Y-m-d")>=$tdf_start_date ){
							$row_color = "style='background-color:#ffffff;'";
						}
						
						// urgent jobs
						if($row['urgent_job']==1){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// jobs not completed
						if($row['job_reason_id']>0){
							$row_color = "style='background-color:#ffff9d;'";
						}
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($row['start_date']!="" && $row['start_date']!="0000-00-00")?date("d/m/Y",strtotime($row['start_date'])):''; ?></td>
							
							<td>
							<?php
							// Age
							$date1=date_create($row['jcreated']);
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$age = $diff->format("%r%a");
							echo (((int)$age)!=0)?$age:0;
							?>
							</td>
							
							<td>
							<?php 
							// region				
							$pr_sql = mysql_query("
								SELECT *
								FROM `postcode_regions`
								WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
								AND `country_id` = {$_SESSION['country_default']}
								AND `deleted` = 0
							");
							$pr = mysql_fetch_array($pr_sql);
							
							echo $pr['postcode_region_name'];
							?>
							</td>
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
						
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
							<td><?php echo $row['comments']; ?></td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							<td>
							<?php
							$lc_sql = $jc->getLastContact($row['jid']);	
							$lc = mysql_fetch_array($lc_sql);
							
							echo ( $lc['eventdate']!="" && $lc['eventdate']!="0000-00-00 00:00:00" )?date("d/m/Y",strtotime($lc['eventdate'])):'';
							?>
							</td>
							<td>
								<input type="checkbox" class="maps_chk_box" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
							</td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="12" align="left">Empty</td>
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

		<div style="margin-top: 15px; float: right; display:none;" id="map_div">
			Tech:
			<select id="maps_tech">
				<option value="">-- select --</option>
				<?php
				$tech_sql = mysql_query("
				SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active
				FROM `staff_accounts` AS sa
				LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
				WHERE ca.`country_id` ={$_SESSION['country_default']}
				AND sa.`Deleted` = 0
				AND sa.`ClassID` = 6
				AND sa.`active` = 1
				ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
				");
				while($tech = mysql_fetch_array($tech_sql)){ ?>
				
					<option value="<?php echo $tech['StaffID']; ?>">
						<?php echo $crm->formatStaffName($tech['FirstName'],$tech['LastName']).( ( $tech['is_electrician'] == 1 )?' [E]':null ); ?>
					</option>
				
				<?php	
				}
				?>
			</select>
			Date:
			<input type="text" id="maps_date" class="datepicker" />
			<button type="button" id="btn_assign" class="blue-btn submitbtnImg">Assign</button>
			
		</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	jQuery("#btn_run_cron").click(function(e){
		
		
		if( confirm("Are you sure you want to proceed?") ){
			
		 //return true;
			
		}else{
			e.preventDefault();
		}
		
	});
	
	
	// region multi select - region check all sub
	jQuery(document).on("click",".region_check_all",function(){
		var chk_state = jQuery(this).prop("checked");
		if(chk_state==true){
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",true);			
		}else{
			jQuery(this).parents("li:first").find(".reg_db_sub_reg input").prop("checked",false);
		}
		
	});
	
	// region multi select script
	jQuery(".state_ms").click(function(){
		
		var state = jQuery(this).val();
		var state_chk = jQuery(this).prop("checked");
		
		//console.log(state_sel);
		
		
		
		if(state_chk==true){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_regionFilter_getMainRegionCount.php",
				data: { 
					state: state,
					job_status: '<?php echo $job_status; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});
			
		}else{
			jQuery("."+state+"_regions").remove();
		}

			
				
	});
	
	
	// region multiselect - get sub region
	jQuery(document).on("click",".reg_db_main_reg",function(){
		
		var obj = jQuery(this);
		var region = obj.parents("li:first").find(".regions_id").val();
		var sub_reg_space = obj.parents("li:first").find(".reg_db_sub_reg").html();
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");
		
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_regionFilter_getSubRegionCount.php",
				data: { 
					region: region,
					job_status: '<?php echo $job_status; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
				if( check_all == true ){
					obj.parents("li.main_region_li").find(".postcode_region_id").prop("checked",true);
				}
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
		
		
		
		

			
				
	});
	
	
	
	
	/*
	// to be booked script
	jQuery("#btn_to_be_booked").click(function(){
		var num_jobs = jQuery(".maps_chk_box:checked").length;
		var num_jobs_str = (num_jobs==1)?'this 1':'these '+num_jobs;
		
		<?php
		$tbb_sql = mysql_query("
			SELECT j.`id`, p.`address_1`, p.`address_2`, p.`address_3` 
			FROM  `jobs` AS j
			LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
			LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
			WHERE a.`country_id` ={$_SESSION['country_default']}
			AND j.`status` =  'On Hold'
			AND p.`deleted` =0
			AND a.`status` =  'active'
			AND j.`del_job` = 0
			AND CURDATE( ) >= (
			j.`start_date` - INTERVAL 3 
			DAY
			)
		");
		
		$str = 'Are you sure you want to move '.mysql_num_rows($tbb_sql).' for booking?  \n';
		while( $tbb = mysql_fetch_array($tbb_sql) ){
			$tbb_prop = trim("{$tbb['address_1']} {$tbb['address_2']}, {$tbb['address_3']}");
			$str .=$tbb_prop.' \n';
		}
		
		?>
		
		var conf_msg = '<?php echo $str; ?>';
		if(confirm(conf_msg)==true){
			// run ajax
			jQuery.ajax({
				type: "POST",
				url: "ajax_preallocation_send_tbb.php",
				data: { 
					country_id: <?php echo $_SESSION['country_default']; ?>
				}
			}).done(function( ret ){
				window.location='/on_hold_jobs.php';
			});	
		}
	});
	*/
	
	// check all toggle
	jQuery("#maps_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".maps_chk_box:visible").prop("checked",true);
		jQuery("#map_div").show();
	  }else{
		jQuery(".maps_chk_box:visible").prop("checked",false);
		jQuery("#map_div").hide();
	  }
	  
	});
	
	
	// toggle hide/show remove button
	jQuery(".maps_chk_box").click(function(){

	  var chked = jQuery(".maps_chk_box:checked").length;
	  
	  if(chked>0){
		jQuery("#map_div").show();
	  }else{
		jQuery("#map_div").hide();
	  }

	});
	
	// move to maps 
	jQuery("#btn_assign").click(function(){
		
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var date = jQuery("#maps_date").val();
		
		jQuery(".maps_chk_box:checked").each(function(){
			job_id.push(jQuery(this).val());
		});
		jQuery.ajax({
			type: "POST",
			url: "ajax_move_to_maps.php",
			data: { 
				job_id: job_id,
				tech_id: tech_id,
				date: date
			}
		}).done(function( ret ){
			//window.location='/maps.php?tech_id=<?php echo $tech_id; ?>&day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>';
			location.reload();
		});	
				
	});
	
});
</script>
</body>
</html>