<?php

$title = "Urgent Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$phrase = mysql_real_escape_string(urldecode($_REQUEST['phrase']));
$job_type = mysql_real_escape_string(urldecode($_REQUEST['job_type']));
$service = mysql_real_escape_string(urldecode($_REQUEST['service']));
$state = mysql_real_escape_string(urldecode($_REQUEST['state']));
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady(urldecode($_REQUEST['date'])):'';
$agency = mysql_real_escape_string(urldecode($_REQUEST['agency']));
$reason = mysql_real_escape_string(urldecode($_REQUEST['reason']));
$job_status = 'To Be Booked';
$urgent_job = 1;

$country_id = $_SESSION['country_default'];

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = urldecode($_GET['postcode_region_id']);
	//echo $filterregion;
}

// sort
//$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.`id`';
//$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'j.`id`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';



// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency=".urlencode($agency)."&postcode_region_id=".urlencode($filterregion);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


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
	'ejr_id' => $reason,
	'phrase' => $phrase,
	'job_type' => $job_type,
	'job_service' => $service,
	'job_status' => $job_status,
	'state' => $state,
	'date' => $date2,
	'postcode_region_id' => $filterregion,
	'urgent_job' => $urgent_job
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
	'postcode_region_id' => $filterregion,
	'urgent_job' => $urgent_job
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
</style>


<?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
		<div style="clear:both;"></div>
	  <?php
	  }  
	  ?>


<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>		
		<li class="other first"><a title="<?php echo $title; ?>" href="/urgent_jobs.php"><strong><?php echo $title; ?></strong></a></li>
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
								'urgent_job' => $urgent_job,
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
								'urgent_job' => $urgent_job,
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
							'urgent_job' => $urgent_job,
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
									'urgent_job' => $urgent_job,
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
								$main_region_postcodes = str_replace(',,',',',substr($main_reg_pc,1));
								$jparams = array('urgent_job'=>$urgent_job);
								$jcount_txt = "(".$jc->getMainRegionCount($_SESSION['country_default'],$main_region_postcodes,'',$job_status,$jparams).")";
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
				
				<th>Start Date</th>
				<th>End Date</th>
			
				<th>Job Type</th>
				
				
				
				<th>Service</th>

				
				<th>Address</th>
		
		
				
				<th>Agency</th>
				
				<th>Region</th>
				<th>Sub Region</th>
				
				<th>Comments</th>

				<th>Job #</th>

				
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
							
							<td><?php echo ($row['start_date']!="")?date('d/m/Y',strtotime($row['start_date'])):''; ?></td>
							<td><?php echo ($row['due_date']!="")?date('d/m/Y',strtotime($row['due_date'])):''; ?></td>
							
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							
							
							<td><img style="width: 20px;" src="images/serv_img/<?php echo getServiceIcons($row['jservice']); ?>" /></td>
						
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							
			
							<td><?php echo $row['agency_name']; ?></td>
							
							<?php
							// get region
							$reg_params = array(
								'postcode_region_postcodes' => $row['p_postcode']
							);
							$reg_sql = $crm->getRegion($reg_params);
							$reg = mysql_fetch_array($reg_sql);
							?>
							<td><?php echo $reg['region_name']; ?></td>
							<td><?php echo $reg['postcode_region_name']; ?></td>
							
							<td><?php echo $row['j_comments']; ?></td>
					
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
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

	
	// show/hide tenant details
	jQuery(".btn_show_tenant_details").click(function(){
		
		if( jQuery(this).html() == "Show" ){
			jQuery(this).html("Hide");
			jQuery(this).parents("tr:first").next().show();
		}else{
			jQuery(this).html("Show");
			jQuery(this).parents("tr:first").next().hide();
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
					job_status: '<?php echo $job_status ?>',
					urgent_job: <?php echo $urgent_job ?>
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
					job_status: '<?php echo $job_status ?>',
					urgent_job: <?php echo $urgent_job ?>
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