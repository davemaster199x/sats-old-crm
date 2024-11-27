<?php
$title = "Jobs 60-90 Days";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

//$region = $_REQUEST['region'];
if($_POST['postcode_region_id']){
	$region2 = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$region2 = $_GET['postcode_region_id'];
	//echo $region2;
}

// Initiate job class
$jc = new Job_Class();

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'j.created';

$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;
$this_page = $_SERVER['PHP_SELF'];

$params = "&sort={$sort}&order_by={$order_by}&job_type=".urlencode($job_type)."&state=".urlencode($state)."&agency=".urlencode($agency)."&postcode_region_id=".$region2;
$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$date_span_from = date('Y-m-d', strtotime("-90 days"));
$date_span_to = date('Y-m-d', strtotime("-60 days"));

$custom_filter = " AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' ";

$plist = getAgeingJobs($offset,$limit,$region2,$job_type,$state,$agency,null,$order_by,$sort,$custom_filter);
$ptotal = mysql_num_rows(getAgeingJobs('','',$region2,$job_type,$state,$agency,null,null,null,$custom_filter));




?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.red_hl{
	background-color: #fcbdb6;
}
.grey_hl{
	background-color: #eeeeee;
}
.white_hl{
	background-color: white;
}
.tick_icon {
    width: 20px;
    position: relative;
    left: 7px;
    top: 4px;
	display: none;
}
</style>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
		 <?php
		  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
		  
		 $tech_id = $_SESSION['USER_DETAILS']['TechID'];
		  
		  $day = date("d");
		  $month = date("m");
		  $year = date("y");
		  
		  include('inc/tech_breadcrumb.php');
		  
		  }else{ ?>
		  
			<div class="sats-breadcrumb">
			  <ul>
				<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
				<li class="other first"><a title="<?php echo $title; ?>" href="/ageing_jobs_60_to_90.php"><strong><?php echo $title; ?></strong></a></li>
			  </ul>
			</div>
		  
		  <?php
		  }  
		  ?>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-60 days"));
		
		
		?>
		
		
		<form method="post" name='example' id='example' action='/ageing_jobs_60_to_90.php' style="margin:0;">
			<input type='hidden' name='status' value='<?php echo $status ?>'>

			<table border=1 cellpadding=0 cellspacing=0 width="100%">
				<tr class="tbl-view-prop">
				<td>

				<div class="aviw_drop-h aviw_drop-vp" id="view-jobs">

				 
				  
				  <?php
				  //if(ifCountryHasState($_SESSION['country_default'])==true){ 
				  
				 
				  
				  ?>
				  
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
								$jstate_sql = getAgeingJobs('','',$region2,$job_type,$state,$agency,'p.`state`',null,null,$custom_filter);
								while($jstate =  mysql_fetch_array($jstate_sql)){ 
								
								// get state regions
								$main_reg_pc = [];
								$temp_sql = mysql_query("
									SELECT * 	
									FROM  `regions`
									WHERE `region_state` = '{$jstate['state']}'
									AND `country_id` = {$_SESSION['country_default']}
									AND `status` = 1
								");
								while( $temp = mysql_fetch_array($temp_sql) ){
									$main_reg_pc[] =  str_replace(',,',',',jGetPostcodeViaRegion($temp['regions_id']));
								}
								
								$pc_merge_arr2 = array_filter($main_reg_pc);
								$pc_merge = implode(",",$pc_merge_arr2);
								$main_region_postcodes = $pc_merge;

								$region_state_count = mysql_num_rows(getAgeingJobs('','',$main_region_postcodes,$job_type,$state,null,null,null,null,$custom_filter));
								?>
									<li>
										<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> 
										<span><?php echo $jstate['state']; ?> (<?php echo $region_state_count; ?>)</span>
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
				  
				  <?php	  
				 // }
				  ?>
					
				   
				  
					<div class="fl-left">
						<label>Job Type:</label>
						<?php
						$curr_jt_sql = getAgeingJobs('','',$region2,$job_type,$state,$agency,'j.`job_type`','j.`job_type`','ASC',$custom_filter);
						?>
						<select name="job_type" style="width: 125px;">
							<option value="">ALL</option>
							<?php
								while($curr_jt = mysql_fetch_array($curr_jt_sql)){ ?>
									<option value="<?php echo $curr_jt['job_type']; ?>" <?php echo ($curr_jt['job_type']==$job_type)?'selected="selected"':''; ?>><?php echo $curr_jt['job_type']; ?></option>
								<?php	
								}
								?>
						</select>
					</div>
				  
				
					
					<?php
					//if(ifCountryHasState($_SESSION['country_default'])==true){ 
						$curr_state_sql = getAgeingJobs('','',$region2,$job_type,$state,$agency,'p.`state`','p.`state`','ASC',$custom_filter);					
					?>
						<div class="fl-left">
							<label><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?>:</label>
							<select id="state" name="state" style="width: 70px;">
								<option value="">ALL</option> 
								<?php
								while($curr_state = mysql_fetch_array($curr_state_sql)){ 
									if($curr_state['state']!=''){
								?>
									<option value="<?php echo $curr_state['state']; ?>" <?php echo ($curr_state['state']==$state)?'selected="selected"':''; ?>><?php echo $curr_state['state']; ?></option>
								<?php
									}
								}
								?>
							 </select>
						</div>
					<?php	
					//}
					?>
					
					
					<?php
					$agen_sql =  getAgeingJobs('','',$region2,$job_type,$state,$agency,'a.`agency_id`','a.`agency_name`','ASC',$custom_filter);
					?>
					
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option> 			
						<?php
						while($agen =  mysql_fetch_array($agen_sql)){ ?>
							<option value="<?php echo $agen['agency_id']; ?>" <?php echo ($agen['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $agen['agency_name']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' value='Search'></div>       
					
					
					
				</div>

				

				<!-- duplicated filter here -->

					  
					  
				</td>
				</tr>
			</table>	  
				  
			</form>
			
			
			
			<?php
			
			// no sort yet
			if($_REQUEST['order_by']==""){
				$sort_arrow = 'up';
			}
			
			?>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>Age</th>
				<th>Job Type</th>
				<th>Service</th>
				<th>Address</th>
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>
				<th>Job #</th>
				<th>Preferred Time</th>
				<th>
					<div class="tbl-tp-name colorwhite bold" title="Outside of Tech Hours">OOTH</div>
					<?php 
					$field_sort = 'j.out_of_tech_hours';
					?>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $field_sort; ?>&sort=<?php echo ($_REQUEST['sort']=='ASC')?'DESC':'ASC'; ?>"> 
						<div class="arw-std-<?php echo ( $sort=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $sort=='ASC' )?'up':'dwn'; ?>-<?php echo ($order_by==$field_sort)?'active':''; ?>"></div>
					</a>
				</th>
				<th>Booking</th>
				<th>Access Notes</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						$row_alt_color = ( $i%2 == 0 )?'white_hl':'grey_hl';
						$bg_color = $crm->isDateNotEmpty($row['jdate'])?'red_hl':$row_alt_color;
				?>
						<tr class="body_tr jalign_left <?php echo $bg_color; ?>">
							<td><?php echo $crm->isDateNotEmpty($row['jdate'])?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							<td><?php 
							// Age
							$date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$age = $diff->format("%a");
							echo $age;
							//echo date("d/m/Y",strtotime($row['jcreated']));
							?></td>
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>
							<td><?php echo $row['preferred_time']; ?></td>
							<td><?php echo ($row['out_of_tech_hours']==1)?'Yes':''; ?></td>
							<td>
								<?php
								
									// region	
									$pr_sql_txt = "
										SELECT *
										FROM `postcode_regions` 
										WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
										AND `deleted` = 0
										AND `country_id` = {$_SESSION['country_default']}
									";
									$pr_sql = mysql_query($pr_sql_txt);
									$pr = mysql_fetch_array($pr_sql);

								
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
							<td>
								<input type="text" class="addinput access_note" style="width:200px; margin: 0;" value="<?php echo $row['access_notes']; ?>" />
								<input type="hidden" class="job_id" value="<?php echo $row['jid']; ?>" />
								<img src="/images/check_icon2.png" class="tick_icon" />
							</td>
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
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
		

		
	</div>
</div>

<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
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
				url: "ajax_ageing_jobs_regionFilter_getMainRegionCount.php",
				data: { 
					state: state,
					days_filter: '60-90'
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
				url: "ajax_ageing_jobs_regionFilter_getSubRegionCount.php",
				data: { 
					region: region,
					days_filter: '60-90'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
			
	});



	// access note ajax update
	jQuery(".access_note").change(function(){
		
		var obj = jQuery(this);
		var access_note = obj.val();
		var job_id = obj.parents("tr:first").find(".job_id").val();
		
		// show loader
		jQuery("#load-screen").show();
		// ajax call	
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_job_access_notes.php",
			data: { 
				job_id: job_id,
				access_note: access_note
			}
		}).done(function( ret ){
			
			// hide loader
			jQuery("#load-screen").hide();
			// show tick
			obj.parents("tr:first").find(".tick_icon").fadeIn();
			// fade
			setTimeout(function(){ 
				obj.parents("tr:first").find(".tick_icon").fadeOut();
			}, 10000);
			
		});

			
				
	});
	
});
</script>
</body>
</html>