<?php
$title = "Service Due";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);
if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'Pending';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency=".urlencode($agency)."&postcode_region_id=".urlencode($filterregion);

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
		<li class="other first"><a title="<?php echo $title; ?>" href="/service_due_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		if($_GET['auto_renew_all']==1){
			echo '<div class="success">AUTO renew ALL process complete</div>';
		}
		
		if( is_numeric($_GET['auto_renew_all']) && $_GET['auto_renew_all']==0){
			echo '<div class="error">There was nothing to process</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<form method="POST" name='example' id='example' style="margin:0;">
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
					
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option> 			
						<?php
						$jstate_sql = $jc->getJobs('','','a.`agency_name`','ASC','',$job_status,'','','','','p.`agency_id`');
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['agency_id']; ?>" <?php echo ($jstate['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $jstate['agency_name']; ?> <?php echo ($jstate['a_auto_renew']==0)?'(No Auto Renew)':''; ?></option>
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
								// get state
								$jstate_sql = mysql_query("
									SELECT DISTINCT (
										p.`state`
									)
									FROM  `jobs` AS j
									LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
									LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
									WHERE j.`status` =  '{$job_status}'
									AND p.`deleted` =0
									AND a.`status` =  'active'
									AND j.`del_job` =0
									AND a.`country_id` = {$_SESSION['country_default']}
									AND a.`state` != ''
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
								$jcount_txt = "(".$jc->getTobeBookedSubRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status).")";
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
					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>

					
					
					<div style="float:right;">
						<a href='service_due_jobs_export.php?filterdate=<?php echo $date; ?>&agencyid=<?php echo $agency; ?>&search=<?php echo $phrase; ?>' class='vj-pg-e export'>
							<button type='button' class='submitbtnImg'>Export</button>
						</a>
					</div>
					
					<div style="float:right; margin-right: 34px;">
					<?php
					$vip = array(2025, 2056, 11, 2155);
					if( in_array($staff_id, $vip) ){ ?>
					
						<!--<button type='button' id="btn_auto_renew_all" class='submitbtnImg blue-btn'>Auto Renew ALL</button>-->
					
					<?php	
					}
					?>
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

		<form method='POST' name='updateform' id='updateform' action='/update_pending_jobs.php'>
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
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Price</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.job_price&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.job_price')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>Address</th>
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>
			
				<th>Job #</th>
				
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
							<td><?php echo $row['job_price']; ?></td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
							<td>
							<?php
							$ar_sql = mysql_query("
								SELECT a.`auto_renew`
								FROM `jobs` AS j
								LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
								LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
								WHERE j.`id` = {$row['jid']}
							");
							$ar = mysql_fetch_array($ar_sql);
							
							if($ar['auto_renew']==0){
								$ar_str = 'style="display:none;"';
							}else{
								$ar_str = '';
							}
							?>
								<input type="checkbox" name="chkbox[]" class="chk_pending" value="<?php echo $row['jid']; ?>" <?php echo $ar_str; ?> />
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
		
		
				
		
		<div class='jobpend'>
			<input type='hidden' name='status' value='pending'>
			
			<!--<input type='submit' name='submit' value='MOVE to On-Hold' class='submitbtnImg' style='background-color: #00aeef;' />-->

			<input type="submit" name="submit" value="No Longer Manage" class="submitbtnImg" onclick="javascript:return confirm('Are you sure you want to mark properties NLM?');" />
		</div>
		
		</form>

		
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	jQuery("#btn_auto_renew_all").click(function(){
		
		var str = "You are about to renew all service due properties would you like to continue?";
		var str_caps = str.toUpperCase();
		
		if( confirm(str_caps) ){
			window.location='/auto_renew_all.php';
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
	// pending check all script
	jQuery("#maps_check_all").click(function(){
		if(jQuery(this).prop("checked")==true){
			jQuery(".chk_pending:visible").prop("checked",true);
		}else{
			jQuery(".chk_pending:visible").prop("checked",false);
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