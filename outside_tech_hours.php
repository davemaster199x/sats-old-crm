<?php

$title = "Outside Of Tech Hours";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];

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
//$job_status = 'DHA';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'p.`postcode`';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort=".urlencode($sort)."&order_by=".urlencode($order_by)."&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$custom_select = '
	j.`id` AS jid,
	j.`date` AS jdate,
	j.`job_type`,
	j.`created` AS jcreated,
	j.`service` AS jservice,
	j.`comments` AS j_comments,
	j.`preferred_time`,
	
	p.`property_id`,
	p.`address_1` AS p_address_1, 
	p.`address_2` AS p_address_2, 
	p.`address_3` AS p_address_3,
	p.`state` AS p_state,
	p.`postcode` AS p_postcode,	

	a.`agency_id`,
	a.`agency_name`,
	a.`allow_dk`
';
$custom_filter = " AND ( j.`status` = 'To Be Booked' OR j.`status` = 'Escalate' OR j.`status` = 'Booked' ) ";

$jparams = array(
	'custom_select' => $custom_select,
	'out_of_tech_hours' => 1,
	'country_id' => $country_id,
	'phrase' => $phrase,
	'date' => $date,
	'job_type' => $job_type,
	'service' => $service,
	'state' => $state,
	'agency_id' => $agency,	
	'custom_filter' => $custom_filter,
	'sort_list' => array(
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	)
);
$plist = $crm->getJobsData($jparams);

$jparams = array(
	'out_of_tech_hours' => 1,
	'country_id' => $country_id,
	'phrase' => $phrase,
	'date' => $date,
	'job_type' => $job_type,
	'service' => $service,
	'state' => $state,
	'agency_id' => $agency,
	'custom_filter' => $custom_filter,
	'return_count' => 1
);
$ptotal = $crm->getJobsData($jparams);





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
		<li class="other first"><a title="<?php echo $title ?>" href="/outside_tech_hours.php"><strong><?php echo $title ?></strong></a></li>
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
							$jparams = array(
								'out_of_tech_hours' => 1,
								'country_id' => $country_id,
								'phrase' => $phrase,
								'date' => $date,
								'job_type' => $job_type,
								'service' => $service,
								'state' => $state,
								'agency_id' => $agency,
								'distinct' => 'j.`job_type`',
								'custom_filter' => $custom_filter
							);
							$jt_sql = $crm->getJobsData($jparams);
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>

					<div class="fl-left">
						<label>Service:</label>
						<select name="service" style="width: 125px;">
							<option value="">Any</option>
							<?php
							$jparams = array(
								'out_of_tech_hours' => 1,
								'country_id' => $country_id,
								'phrase' => $phrase,
								'date' => $date,
								'job_type' => $job_type,
								'service' => $service,
								'state' => $state,
								'agency_id' => $agency,
								'distinct' => 'j.`service`',
								'custom_filter' => $custom_filter
							);
							$ajt_sql = $crm->getJobsData($jparams);
							while($ajt=mysql_fetch_array($ajt_sql)){ ?>
								<option <?php echo ($ajt['id']==$service) ? 'selected="selected"':''; ?> value="<?php echo $ajt['id']; ?>" ><?php echo $ajt['type']; ?></option>
							<?php
							}
							?>
						</select>
					</div>
					
			
					<div class="fl-left">
						<label><?php echo getDynamicStateViaCountry($country_id); ?>:</label>
						<select id="state" name="state" style="width: 70px;">
							<option value="">Any</option> 			
							<?php							
							$jparams = array(
								'out_of_tech_hours' => 1,
								'country_id' => $country_id,
								'phrase' => $phrase,
								'date' => $date,
								'job_type' => $job_type,
								'service' => $service,
								'state' => $state,
								'agency_id' => $agency,
								'distinct' => 'p.`state`',
								'custom_filter' => $custom_filter
							);
							$jstate_sql = $crm->getJobsData($jparams);
							while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
								<option value="<?php echo $jstate['state']; ?>" <?php echo ($jstate['state']==$state) ? 'selected="selected"':''; ?>><?php echo $jstate['state']; ?></option>
							<?php	
							} 
							?>
						 </select>
					</div>
					
					
					<div class="fl-left">
						<label><?php echo getDynamicRegionViaCountry($country_id); ?>:</label>
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
								$jparams = array(
									'out_of_tech_hours' => 1,
									'country_id' => $country_id,
									'phrase' => $phrase,
									'date' => $date,
									'distinct' => 'p.`state`',
									'job_type' => $job_type,
									'service' => $service,
									'state' => $state,
									'agency_id' => $agency,
									'custom_filter' => $custom_filter
								);
								$jstate_sql = $crm->getJobsData($jparams);
								while($jstate =  mysql_fetch_array($jstate_sql)){ 
								
								// get state regions
								$main_reg_pc = "";
								$temp_sql = mysql_query("
									SELECT * 	
									FROM  `regions`
									WHERE `region_state` = '{$jstate['state']}'
									AND `country_id` = {$country_id}
									AND `status` = 1
								");
								while( $temp = mysql_fetch_array($temp_sql) ){
									$main_reg_pc .= ','.$jc->getSubRegionPostcodes($temp['regions_id']);
								}
								
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));
								//$jcount = $jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'');
								
								// get state
								$jparams = array(
									'out_of_tech_hours' => 1,
									'country_id' => $country_id,
									'phrase' => $phrase,
									'date' => $date,
									'job_type' => $job_type,
									'service' => $service,
									'state' => $state,
									'agency_id' => $agency,
									'custom_filter' => $custom_filter,
									'postcode_region_id' => $main_region_postcodes,
									'return_count' => 1
								);
								$jcount = $crm->getJobsData($jparams);
								if( $jcount>0  ){
								$jcount_txt = "({$jcount})";
								?>
									<li>
										<input type="checkbox" name="state_ms[]" class="state_ms" value="<?php echo $jstate['state']; ?>" /> <span><?php echo $jstate['state']; ?> <?php echo $jcount_txt; ?></span>
										<input type="hidden" value="<?php echo $main_region_postcodes; ?>" />
									</li>
								<?php	
									} 
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
					
					
					<div class="fl-left">
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option> 			
						<?php						
						$jparams = array(
							'out_of_tech_hours' => 1,
							'country_id' => $country_id,
							'phrase' => $phrase,
							'date' => $date,
							'job_type' => $job_type,
							'service' => $service,
							'state' => $state,
							'agency_id' => $agency,
							'distinct' => 'p.`agency_id`',
							'custom_filter' => $custom_filter,
							'sort_list' => array(
								array(
									'order_by' => 'a.`agency_name`',
									'sort' => 'ASC'
								)
							),
							'display_echo' => 0
						);
						$jstate_sql = $crm->getJobsData($jparams);
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['agency_id']; ?>" <?php echo ($jstate['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $jstate['agency_name']; ?></option>
						<?php	
						} 
						?>
					 </select>
					</div>
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left'>
						<input type='submit' class='submitbtnImg' value='Search' />
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
				<th>Date</th>
				<th>Job Type</th>
				<th>Age</th>
				<th>Service</th>
				<th>Address</th>
				<th>State</th>
				<th>Region</th>
				<th>Agency</th>				
				<th>Job #</th>
				<th>Comments</th>
				<th>Preferred Time</th>
				<th><input type="checkbox" id="maps_check_all" /></th>
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
							
							<td>
							<?php
							// Age
							$date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$age = $diff->format("%r%a");
							$age_val = (((int)$age)!=0)?$age:0;
							echo $age_val;
							$age_val_tot += $age_val;
							?>
							</td>
							
							<td><img src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
							
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							
							<td>
							<?php
							// region				
							$pr_sql = mysql_query("
								SELECT `postcode_region_name`
								FROM `postcode_regions` 
								WHERE `postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
								AND `country_id` = {$country_id}
								AND `deleted` = 0
							");
							$pr = mysql_fetch_array($pr_sql);
							echo $pr['postcode_region_name']
							?>
							</td>
							
							<td class="agency_td"><?php echo $row['agency_name']; ?></td>
						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
							<td><?php echo $row['j_comments']; ?></td>
							<td><?php echo $row['preferred_time']; ?></td>
							
							<td>
								<input type="checkbox" class="maps_chk_box" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="is_dk_allowed" value="<?php echo $row['allow_dk']; ?>" />
								<input type="hidden" class="agency_id" value="<?php echo $row['agency_id']; ?>" />
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
				url: "ajax_oth_getMainRegion.php",
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
				url: "ajax_oth_getSubRegion.php",
				data: { 
					region: region,
					job_status: '<?php echo $job_status ?>'
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