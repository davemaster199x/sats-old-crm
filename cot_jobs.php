<?php
$title = "COT";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
//$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$job_type = ($_REQUEST['job_type']!="")?$_REQUEST['job_type']:'cot & lr';
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($filterregion);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'To Be Booked';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.`due_date`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'DESC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

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
		
		
		<form method="POST" name='example' id='example' style="margin-bottom: 0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">
				
				
				
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option>
						<?php
						$jt_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'p.`agency_id`');				
						while($jt =  mysql_fetch_array($jt_sql)){ ?>
							<option value="<?php echo $jt['agency_id']; ?>" <?php echo ($jt['agency_id'] == $agency)?'selected="selected"':''; ?>><?php echo $jt['agency_name']; ?></option>
						<?php	
						}
						?>	
					 </select>
					</div>

				 
	
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
						$jstate_sql = $jc->getJobs('','',$sort,$order_by,$job_type,$job_status,'','','','','p.`state`');
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['state']; ?>" <?php echo ($jstate['state']==$state) ? 'selected="selected"':''; ?>><?php echo $jstate['state']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
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
								
								if($job_type!=""){
									if($job_type=='cot & lr'){
										$jt_str .= " AND ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' ) ";
									}else{
										$jt_str .= " AND j.job_type = '{$job_type}' ";
									}			  
								}
								
								// get state
								$jstate_sql = mysql_query("
									SELECT DISTINCT (
										a.`state`
									)
									FROM  `jobs` AS j
									LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
									LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
									WHERE j.`status` =  'To Be Booked'
									{$jt_str}
									AND a.`status` =  'active'
									AND j.`del_job` =0
									AND a.`country_id` = {$_SESSION['country_default']}
									ORDER BY a.`state`
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
									$main_reg_pc .= ','.$jc->getTobeBookedPostcodeViaRegion($temp['regions_id']);
								}
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));
								$jcount_txt = "(".$jc->getTobeBookedSubRegionCount($_SESSION['country_default'],$main_region_postcodes,$job_type).")";
								?>
									<li>
										<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo $jcount_txt ?></span>
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
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search' />
					
    
					
					
					
				</div>

				

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
				<th>End Date</th>
				<th>Date</th>
				
				<th><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
				<th style="width:10%;">Booking</th>
			
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
				
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						$tdf_start_date = date("Y-m-d",strtotime($row['start_date']." -3 days"));
						$tdf_end_date = date("Y-m-d",strtotime($row['due_date']." -3 days")); 
						
						$row_color = "style='background-color:#ffff9d;'";
						
						if( date("Y-m-d")>=$tdf_start_date ){
							$row_color = "style='background-color:#ffffff;'";
						}
						
						if( ( $row['due_date']!="" && $row['due_date']!="0000-00-00" ) && date("Y-m-d")>=$tdf_end_date ){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// urgent jobs
						if($row['urgent_job']==1){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// jobs not completed
						if($row['job_reason_id']>0){
							$row_color = "style='background-color:#ffff9d;'";
						}
						
						// if start/end date is empty
						if( ( $row['start_date']=="" || $row['start_date']=="0000-00-00" ) && ( $row['due_date']=="" || $row['due_date']=="0000-00-00" ) && $row['no_dates_provided']==0 ){
							$row_color = "style='background-color:#ffcccc;'";
						}
						
						// if start/end date is empty and N/A
						if( ( $row['start_date']=="" || $row['start_date']=="0000-00-00" ) && ( $row['due_date']=="" || $row['due_date']=="0000-00-00" ) && $row['no_dates_provided']==1 ){
							$row_color = "style='background-color:white;'";
						}
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							
							<td><?php echo ($row['start_date']!="" && $row['start_date']!="0000-00-00" && $row['start_date']!="1970-01-01" )?date("d/m/Y",strtotime($row['start_date'])):(($row['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
							<td><?php echo ($row['due_date']!="" && $row['due_date']!="0000-00-00" && $row['due_date']!="1970-01-01" )?date("d/m/Y",strtotime($row['due_date'])):(($row['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
						
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							<td>
							<?php 
							// region				
							$pr_sql = mysql_query("
								SELECT *
								FROM `postcode_regions` 
								WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
								AND `deleted` = 0
								AND `country_id` = {$_SESSION['country_default']}
							");
							$pr = mysql_fetch_array($pr_sql);
							
							echo $pr['postcode_region_name'];
							?>
							</td>
							
							<td>
							<?php
							// fetch all STR created on this region
								$str_sql = mysql_query("
									SELECT * 
									FROM  `tech_run` 
									WHERE `sub_regions` LIKE '%{$pr['postcode_region_id']}%'	
									AND `date` >= '".date('Y-m-d')."'
									AND `country_id` = {$_SESSION['country_default']}
									ORDER BY `date`
								");
								$fcount = 0;
								while( $str = mysql_fetch_array($str_sql) ){ 
								
									$reg_arr = explode(",",$str['sub_regions']);
									//print_r($reg_arr);

									if( in_array($pr['postcode_region_id'], $reg_arr) ){ 
									
									echo ($fcount!=0)?', ':'';  
									
									?><a href="/set_tech_run.php?tr_id=<?php echo $str['tech_run_id'] ?>"><?php echo date('d/m',strtotime($str['date'])); ?></a><?php	
									$fcount++;
									
									}else{
										$no_set_date_flag = 1;
									}
								
								}
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
					<?php 
						echo $crm->formatStaffName($tech['FirstName'],$tech['LastName']).( ( $tech['is_electrician'] == 1 )?' [E]':null ); 
					?>
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
				url: "ajax_getMainRegionsViaState.php",
				data: { 
					state: state,
					job_type: '<?php echo $job_type; ?>'
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
		
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_getSubRegionsViaRegion.php",
				data: { 
					region: region,
					job_type: '<?php echo $job_type; ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
		
		
		
		

			
				
	});
	
	

	
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