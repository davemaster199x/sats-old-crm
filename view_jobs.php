<?php

$title = "All Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$agency = mysql_real_escape_string($_REQUEST['agency']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$date = mysql_real_escape_string($_REQUEST['date']);
$date2 = ( $crm->isDateNotEmpty($date)==true )?$crm->formatDate($date):'';
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$search = mysql_real_escape_string($_REQUEST['search']);


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

$country_id = $_SESSION['country_default'];
//$date = date('Y-m-d');
//$job_status = 'Pre Completion';


$created_date = date('Y-m-d');

// sort


$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'j.job_type';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;

$this_page = $_SERVER['PHP_SELF'];
$params = "&agency=".urlencode($agency)."&job_type=".urlencode($job_type)."&phrase=".urlencode($phrase)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&search=".urlencode($search);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

if( $search ){
	
	$jparams = array(
		'paginate' => array(
			'offset' => $offset,
			'limit' => $limit
		),
		'sort_list' => array(	
			array(
				'order_by' => 'j.`urgent_job`',
				'sort' => 'DESC'
			),
			array(
				'order_by' => $order_by,
				'sort' => $sort
			)
		),
		'country_id' => $country_id,
		'agency_id' => $agency,
		'job_type' => $job_type,
		'job_service' => $service,
		'state' => $state,
		'date' => $date2,
		'postcode_region_id' => $filterregion,
		'phrase' => $phrase,
		'display_echo' => 0
	);
	$plist = $crm->getJobsData($jparams);

	$jparams = array(
		'country_id' => $country_id,
		'agency_id' => $agency,
		'job_type' => $job_type,
		'job_service' => $service,
		'state' => $state,
		'date' => $date2,
		'postcode_region_id' => $filterregion,
		'phrase' => $phrase
	);
	$ptotal = mysql_num_rows($crm->getJobsData($jparams));
	
}


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
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
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
					<label>Agency</label>
					<select name="agency" style="width: 125px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'country_id' => $country_id,
							'distinct' => 'a.`agency_id`',
							'sort_list' => array(
								array(
									'order_by' => 'a.`agency_name`',
									'sort' => 'ASC'
								)
							)
						);
						$jt_sql = $crm->getJobsData($jparams);						
						while($jt =  mysql_fetch_array($jt_sql)){ ?>
							<option value="<?php echo $jt['agency_id']; ?>" <?php echo ($jt['agency_id'] == $agency)?'selected="selected"':''; ?>><?php echo $jt['agency_name']; ?></option>
						<?php	
						}
						?>	
					</select>
				</div>
					
			
				<div class="fl-left">
					<label>Job Type</label>
					<select name="job_type" style="width: 125px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'country_id' => $country_id,
							'distinct' => 'j.`job_type`',
							'sort_list' => array(
								array(
									'order_by' => 'j.`job_type`',
									'sort' => 'ASC'
								)
							),
							'custom_filter' => " AND j.`job_type` != '' "
						);
						$jt_sql = $crm->getJobsData($jparams);						
						while($jt =  mysql_fetch_array($jt_sql)){ ?>
							<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
						<?php	
						}
						?>	
						<option value="None Selected">No Job Type Selected</option>
					</select>
				</div>
				
				
				
				<div class="fl-left">
					<label>Service</label>
					<select name="service" style="width: 125px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'country_id' => $country_id,
							'distinct' => 'j.`service`',
							'sort_list' => array(
								array(
									'order_by' => 'ajt.`type`',
									'sort' => 'ASC'
								)
							),
							'custom_filter' => " AND j.`service` != '' "
						);
						$jt_sql = $crm->getJobsData($jparams);						
						while($jt =  mysql_fetch_array($jt_sql)){ ?>
							<option value="<?php echo $jt['id']; ?>" <?php echo ($jt['id'] == $service)?'selected="selected"':''; ?>><?php echo $jt['type']; ?></option>
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
								
								$job_status = 'To Be Booked';
								
								// get state
								$jparams = array(
									'country_id' => $country_id,
									'distinct' => 'p.`state`',
									'sort_list' => array(
										array(
											'order_by' => 'p.`state`',
											'sort' => 'ASC'
										)
									),
									'custom_filter' => " AND p.`state` != '' "
								);
								$jstate_sql = $crm->getJobsData($jparams);
								
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
								$jcount = $jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes);
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
				
				
				<div class='fl-left'><label>Date:</label><input type=label name='date' value="<?php echo $date; ?>" class='addinput searchstyle vwjbdtp datepicker' style='width: 100px !important;'></div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $phrase; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;"><input type='submit' class='submitbtnImg' name='search' value='Search' />
					
    
					
					
					
				</div>
				
				</div>

				

				<!-- duplicated filter here -->
				
				
				
					  
					  
				</td>
				</tr>
			</table>


			
				  
			</form>
			
			
			<?php
			
		
			if($_REQUEST['sort']){
				if($_REQUEST['sort']=='ASC'){
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
			$active = ($_REQUEST['order_by']=="")?'arrow-top-active':'';
			
			
			
			
			?>
			
			
			

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">
			
				<?php 
				$sort_field = 'j.date';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Date</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
				
				<?php 
				$sort_field = 'j.job_type';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Job Type</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
			
				<?php 
				$sort_field = 'j.service';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Service</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
			
				<?php 
				$sort_field = 'j.price';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Price</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
			
				<?php 
				$sort_field = 'p.address';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Address</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
				
				<?php 
				$sort_field = 'p.state';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">State</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
				
				<?php 
				$sort_field = 'a.agency_name';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Agency</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>
				
				<?php 
				$sort_field = 'j.id';
				?>
				<th>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?order_by=<?php echo $sort_field; ?>&sort=<?php echo ($_REQUEST['order_by']==$sort_field)?$ob:'ASC'; ?><?php echo $params; ?>">
						<div class="tbl-tp-name colorwhite bold">Job #</div> 
						<?php echo ($_REQUEST['order_by']==$sort_field)?$sort_arrow:'<div class="arw-std-up"></div>'; ?>
					</a>
				</th>

	
			</tr>
				<?php
				
				if($search){
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					
					while($row = mysql_fetch_array($plist)){
						
						$row_color = '';
						
						if( $row['jstatus'] == 'Booked' ){
							$row_color = '#ececec';
						}
						
						// urgent jobs
						if( $row['urgent_job']==1 && $row['jstatus']!='Completed' ){
							$row_color = "#2CFC03";
						}
						
						// jobs not completed
						if($row['job_reason_id']>0){
							$row_color = "#ffff9d";
						}
						
						
				?>
						<tr class="body_tr jalign_left" style="background-color:<?php echo $row_color; ?>">
						
							
							<td><?php echo ( $crm->isDateNotEmpty($row['jdate']) )?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
						
							<td><img src="/images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
						
							<td><?php echo $row['price']; ?></td>

							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo (ifCountryHasState($_SESSION['country_default'])==true)?$row['p_state']:'N/A'; ?></td>
							
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['a_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<?php echo $row['agency_name']; ?>
								</a>
							</td>
							
							<td><a href="/view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></td>
		
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="100%" align="left">Empty</td>
				<?php
				}
				
				}else{ ?>
					<td colspan="100%" align="left">Press Search to Display Result</td>
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

		<div style="margin-top: 15px; float: right; display:none;" id="rebook_div">
			<button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create 240v Rebook</button>
			<button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create Rebook</button>
			<button type="button" id="btn_move_to_merged" class="submitbtnImg" style="background-color:green">Move to Merged</button>
		</div>
		
	</div>
</div>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
	
	
	
	
	
	
		
		
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
					state: state
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				jQuery(".region_dp_body").append(ret);
			});
			
		}else{
			var state2 = state.replace(/ /g,"_");
			jQuery("."+state2+"_regions").remove();
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
					region: region
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
		

			
				
});
	
</script>
</body>
</html>