<?php

$title = "Escalate Jobs";
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
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$agency = mysql_real_escape_string($_REQUEST['agency']);
$reason = mysql_real_escape_string($_REQUEST['reason']);
$job_status = 'Escalate';

$country_id = $_SESSION['country_default'];

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

// sort
//$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.`id`';
//$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'p.`address_2`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';



// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(	
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'country_id' => $country_id,
	'agency_id' => $agency,
	'ejr_id' => $reason,
	'phrase' => $phrase,
	'job_type' => $job_type,
	'job_service' => $service,
	'job_status' => $job_status,
	'state' => $state,
	'date' => $date2,
	'postcode_region_id' => $filterregion
);
$plist = $crm->getJobsData($jparams);

//$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','','','','','',1);

$jparams = array(
	'country_id' => $country_id,
	'agency_id' => $agency,
	'ejr_id' => $reason,
	'phrase' => $phrase,
	'job_type' => $job_type,
	'job_service' => $service,
	'job_status' => $job_status,
	'state' => $state,
	'date' => $date2,
	'postcode_region_id' => $filterregion
);
$ptotal = mysql_num_rows($crm->getJobsData($jparams));
//$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','','','','','',1));




?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update, .tenant_details_row{
	display:none;
}
.yello_mark{
	background-color: #ffff9d;
}
.green_mark{
	background-color: #c2ffa7;
}
.tenants_table tr, .tenants_table tr{
	border: 0 none !important;
}
.tenants_table td{
	text-align: left;
}
table.tenants_table tr:last-child {
    border-bottom: 0 none !important;
}
.tbl-sd tr td button {
    font-size: 16px;
}

.escalate_icons{
	width: 20px;
}
</style>




<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Escalate" href="/escalate.php">Escalate</a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/escalate_jobs.php?agency=<?php echo $agency; ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['tenant_update']==1){
			echo '<div class="success">Tenant Update Successfull</div>';
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
								'job_status' => $job_status,
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
						</select>
					</div>
				  
					
					<div class="fl-left">
						<label>Service:</label>
						<select name="service" style="width: 125px;">
							<option value="">Any</option>
							<?php
							$jparams = array(
								'job_status' => $job_status,
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
						<label>Agency:</label>
						<select id="agency" name="agency" style="width: 70px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'job_status' => $job_status,
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
						<label>Reason:</label>
						<select id="reason" name="reason" style="width: 70px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'job_status' => $job_status,
							'country_id' => $country_id,
							'distinct' => 'sejr.`escalate_job_reasons_id`',
							'sort_list' => array(
								array(
									'order_by' => 'ejr.`reason`',
									'sort' => 'ASC'
								)
							)
						);
						$esc_sql = $crm->getJobsData($jparams);
						while($esc =  mysql_fetch_array($esc_sql)){ ?>
							<option value="<?php echo $esc['escalate_job_reasons_id'] ?>" <?php echo ($esc['escalate_job_reasons_id']==$reason)?'selected="selected"':''; ?>><?php echo $esc['reason']; ?></option> 	
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
								$jparams = array(
									'job_status' => $job_status,
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
									$main_reg_pc .= ','.$jc->getTobeBookedPostcodeViaRegion($temp['regions_id']);
								}
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));
								$jcount_txt = "(".$jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status).")";
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
						
						<button type="submit" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							<span class="inner_icon_txt">Search</span>
						</button>

					</div>
				
				
				
				<div class='fl-right'>
					<a href='/view_jobs_export.php?status=escalate&filterdate=<?php echo $date; ?>&search=<?php echo $phrase; ?>'>	
						<button type="button" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/export.png">
							<span class="inner_icon_txt">Export</span>
						</button>
					</a>
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

				<th>Age</th>
			
				<th>Job Type</th>
				
				
				
				<th>Service</th>

				
				<th>Address</th>
		
		
				
				<th>Agency</th>
				<th>Phone</th>

				<th>Job #</th>
				<th>Last Contact</th>
				<th>Reason</th>
				<th>Response</th>
				<th>STR</th>
				<th>Show</th>
				
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					//$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";	
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							
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
							
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							
							
							<td><img class="service_icons" src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
						
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							
			
							<td><?php echo $row['agency_name']; ?></td>
							<td><?php echo $row['a_phone']; ?></td>
					
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							<td>
							<?php
							$lc_sql = $jc->getLastContact($row['jid']);	
							$lc = mysql_fetch_array($lc_sql);
							
							echo ( $lc['eventdate']!="" && $lc['eventdate']!="0000-00-00 00:00:00" )?date("d/m/Y",strtotime($lc['eventdate'])):'';
							?>
							</td>
							<td>
							<?php 
							
							// display escalate job reasons
							$sel_esc_str = "
								SELECT *
								FROM `selected_escalate_job_reasons` AS sejr
								LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
								WHERE sejr.`deleted` = 0
								AND sejr.`active` = 1
								AND sejr.`job_id` = {$row['jid']}
							";
							$sel_esc_job_sql = mysql_query($sel_esc_str);
							while( $sel_esc_job = mysql_fetch_array($sel_esc_job_sql) ){ ?>
							
								<img class="escalate_icons" title="<?php echo $sel_esc_job['reason']; ?>" src="/images/escalate_jobs/<?php echo $sel_esc_job['icon']; ?>" />
							
							<?php	
							}

							?>
							</td>
							
							
							<td>
							<?php
							if($row['agency_approve_en']==1){
								$approv_txt = 'Allow';
								$approv_clr = 'green';
							}else if( is_numeric($row['agency_approve_en']) && $row['agency_approve_en']==0){
								$approv_txt = 'Deny';
								$approv_clr = 'red';
							}else{
								$approv_txt = '';
								$approv_clr = '';
							}
							?>
							<span style="color:<?php echo $approv_clr; ?>"><?php echo $approv_txt; ?></span>
							</td>
							
							<td>
							<?php
							// fetch today and present tech runs
							$other_str_txt = "
								SELECT *, tr.`date` AS tr_date 
								FROM `tech_run_rows` AS trr
								LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
								LEFT JOIN `staff_accounts` AS a ON tr.`assigned_tech` = sa.`StaffID`
								LEFT JOIN `jobs` AS j ON j.`id` = trr.`row_id` 
								LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
								LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
								AND trr.`row_id_type` =  'job_id'
								WHERE j.`id` = {$row['jid']}
								AND tr.`date` >=  '".date('Y-m-d')."'
								AND trr.`hidden` = 0
								AND j.`del_job` = 0
								AND tr.`country_id` = {$_SESSION['country_default']}
								AND a.`country_id` = {$_SESSION['country_default']}
							";
							$other_str_sql = mysql_query($other_str_txt);
							
							$tr_links_arr = array();
							
							if( mysql_num_rows($other_str_sql)>0 ){
								while( $other_str = mysql_fetch_array($other_str_sql) ){ 
									$tr_links_arr[] = '<a href="/set_tech_run.php?tr_id='.$other_str['tech_run_id'].'">'.date('d/m',strtotime($other_str['tr_date'])).'</a>';								
								}
							}
							
							
							echo implode(", ",$tr_links_arr);
							?>
							</td>
							<td style="border-right: 1px solid #cccccc;">
								
								<button data-propid="<?php echo $row['property_id']; ?>" type="button" class="submitbtnImg blue-btn btn_show_tenant_details">
									<img class="inner_icon" src="images/button_icons/show-button.png">
									<span class="inner_icon_txt">Show</span>
								</button>

							</td>
							
						</tr>



						<!-- Hidden Box Start here... -->
						<tr class="tenant_details_row">
						<td colspan="100%" style="padding: 0;">
							
									
								  <div style="width:885px;margin-bottom:30px;margin-top:30px;" class="tenant_v2_box"></div>
									
									
								  <div style="margin-bottom:30px;width:850px;text-align:right;">
												<input type="hidden" class="property_id" value="<?php echo $row['property_id']; ?>" />
												<input type="hidden" class="job_id" value="<?php echo $row['jid']; ?>" />
												
												
												<button type="button" class="submitbtnImg btn_process" style="margin-left:11px;">
													<img class="inner_icon" src="images/button_icons/select-button.png">
													<span class="inner_icon_txt">Process</span>
												</button>

												<img src="/images/check_icon2.png" class="img_check" style="display:none; left: 15px; position: relative; top: 10px;" />
											</div>
						</td>

						</tr>
						
						
				<?php
					$i++;
					}
				}else{ ?>
					<tr><td colspan="12" align="left">Empty</td></tr>
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
	
	
	
	jQuery(".btn_add_tenant").click(function(){
		
		var row = '<tr>'+
			'<td>'+
				'<input type="text" style="width: 100px;" class="addinput tenant_firstname" />'+
				'<input type="hidden" class="addinput pt_id" />'+
			'</td>'+
			'<td><input type="text" style="width: 100px;" class="addinput tenant_lastname" /></td>'+
			'<td><input type="text" style="width: 100px;" class="addinput tenant_mobile" /></td>'+
			'<td><input type="text" style="width: 100px;" class="addinput tenant_landline" /></td>'+
			'<td><input type="text" style="width: 100px;" class="addinput tenant_email" /></td>'+
		'</tr>';
		
		jQuery(".tenant_tbody").append(row);
		
	});
	
	
	
	
	// update tenants
	// ajax update tenant details
	// ajax update tenant details
	jQuery(".btn_process").click(function(){
		
		var obj = jQuery(this);
		var btn_txt = obj.find(".inner_icon_txt").html();
		
		var job_id = obj.parents("td:first").find(".job_id").val();			
			
		// invoke ajax
		jQuery.ajax({
			type: "POST",
			url: "ajax_process_escalate_jobs.php",
			data: { 
				job_id: job_id
			}
		}).done(function( ret ){
			
			obj.parents("td:first").find(".img_check").show();
			
		});
	
		
	});
	
	
	
	// show/hide tenant details
	jQuery(".btn_show_tenant_details").click(function(){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Show';
		var orig_btn_icon = 'images/button_icons/show-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		var prop_id = $(this).attr('data-propid');
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery(this).parents("tr:first").next("tr.tenant_details_row").show();



			 //load teanants via ajax load
			 $(".tenant_v2_box").empty();  //empty first all tenant box
			 jQuery(this).parents("tr.body_tr").next('.tenant_details_row').find(".tenant_v2_box").load('tenant_details_new_for_escalateOrJob.php', {property_id: prop_id}); //load tenant via ajax


		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery(this).parents("tr:first").next("tr.tenant_details_row").hide();
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
					job_status: '<?php echo $job_status ?>'
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
				url: "ajax_regionFilter_getSubRegionCount.php",
				data: { 
					region: region,
					job_status: '<?php echo $job_status ?>'
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				obj.parents("li:first").find(".reg_db_sub_reg").html(ret);
			});
			
		}else{
			obj.parents("li:first").find(".reg_db_sub_reg").html("");
		}
	
				
	});
	
});
</script>
</body>
</html>