<?php
$title = "To Be Booked";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$country_id = $_SESSION['country_default'];

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();
$crm = new Sats_Crm_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string(urldecode($_REQUEST['job_type']));
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$agency = mysql_real_escape_string($_REQUEST['agency']);

/*
echo "<pre>";
print_r($_POST['postcode_region_id']);
echo "</pre>";
*/

/*
echo "<pre>";
print_r($_POST['postcode_region']);
echo "</pre>";
*/


if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$is_urgent = ($_REQUEST['is_urgent']!="")?mysql_real_escape_string($_REQUEST['is_urgent']):'';
$job_status = 'To Be Booked';


// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency=".urlencode($agency)."&postcode_region_id=".$filterregion;

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

if( $sort=='j.date' ){
	$use_plain_sort = 1;
}

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','',$is_urgent,'','','',$use_plain_sort);
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','',$agency,$filterregion,0,'','',$is_urgent,'','','',$use_plain_sort));




?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.yello_mark{
	background-color: #ffff9d !important;
}
.green_mark{
	background-color: #c2ffa7;
}
<?php 
/*
if($filterregion!=""){ ?>
.pagination li, .pagination_range{
	display:none!important;
}
<?php	
}
*/
?>
</style>





<div id="mainContent">


	

	
   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/to_be_booked_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		
		
		<form method="POST" name='example' id='example' style="margin: 0;">
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
						<label><?php echo getDynamicStateViaCountry($country_id); ?>:</label>
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
						$jstate_sql = $jc->getJobs('','','a.`agency_name`','ASC','',$job_status,'','','','','p.`agency_id`','','','','','',0,'','','','','','',1);
						while($jstate =  mysql_fetch_array($jstate_sql)){ ?>
							<option value="<?php echo $jstate['agency_id']; ?>" <?php echo ($jstate['agency_id']==$agency) ? 'selected="selected"':''; ?>><?php echo $jstate['agency_name']; ?></option>
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
									AND `country_id` = {$country_id}
									AND `status` = 1
								");
								$sub_regions_postcode = [];
								while( $temp = mysql_fetch_array($temp_sql) ){
									
									// each sub region postcode
									$sr_pc = $jc->getSubRegionPostcodes($temp['regions_id']);
									if( $sr_pc!='' ){
										$sub_regions_postcode[] = $sr_pc;		
									}
										
								}
					
								//print_r($sub_regions_postcode);
								
								$rejoin_arr = implode(",",$sub_regions_postcode);
				
								$main_region_postcodes = str_replace(',,',',',$rejoin_arr);
								$jcount = $jc->getMainRegionCount($country_id,$main_region_postcodes,'',$job_status);
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
				
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker' style='width: 76px;'>		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;">
						
						<button type="submit" id="submit" class="submitbtnImg">
							<img class="inner_icon" src="images/search-button.png">
							Search
						</button>
						
					</div>

					<div style="float:right; margin-top: 16px;">
						<a href="/jobs_export.php?export=1<?php echo $params; ?>">
							<button class="submitbtnImg" id="btn_export" type="button">
								<img class="inner_icon" src="images/export.png">
								Export
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

				<th>
					<div class="tbl-tp-name colorwhite bold">Date</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.date&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.date')?'active':''; ?>"></div>
					</a>
				</th>
				
			
			
				<th>Job Type</th>
				
				<th>Age</th>
				
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
		
				<th><?php echo getDynamicStateViaCountry($country_id); ?></th>
				
				<th><?php echo getDynamicRegionViaCountry($country_id); ?></th>
				
				<th>Agency</th>
			
				<th>Job #</th>

				<th>Start Date</th>
				
				<th>Vacant</th>
				
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
			</tr>
				<?php
				
				
				$i= 0;
				$age_val_tot = 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
					// grey alternation color
					$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
					
					// if alarms 240v or 240vli are expired
					if( $crm->findExpired240vAlarm($row['jid']) == true ){
						$row_color = "style='background-color:#FFCCCB;'";							
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
							<td><?php echo $row['job_price']; ?></td>
							
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
							

							
							<td>
							<?php
								echo ( ( $row['job_type']=='Change of Tenancy' || $row['job_type']=='Lease Renewal' ) && !empty($row['start_date']) )?'<span '.( ( $row['start_date'] >= date('Y-m-d') )?'style="color:red;"':'' ).'>'.date('d/m/Y',strtotime($row['start_date'])).'</span>':'';
							?>
							</td>
							
							<td><?php echo ($row['property_vacant']==1)?'YES':''; ?></td>
							
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
					?>
					<tr>
						<td colspan="2"></td>
						<td><?php echo floor($age_val_tot/$i); ?></td>
						<td colspan="100%"></td>
					</tr>
				<?php
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
			<input type="text" id="maps_date" class="datepicker" style='width: 76px;' />
			<button type="button" id="btn_assign" class="blue-btn submitbtnImg">
				<img class="inner_icon" src="images/assign.png">
				Assign
			</button>
			<button type="button" id="btn_assign_dk" class="submitbtnImg">
				<img class="inner_icon" src="images/assign.png">
				Door Knock
			</button>
			<button style=" float: right; margin-left: 5px;" class="submitbtnImg" id="btn_create_rebook" type="button">
				<img class="inner_icon" src="images/rebook.png">
				Rebook
			</button>
			
		</div>
		
	</div>
</div>




<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	
	
	// rebook
	jQuery("#btn_create_rebook").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".maps_chk_box:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			//console.log(job_id);
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_rebook_script.php",
				data: { 
					job_id: job_id,
					is_240v: 0
				}
			}).done(function( ret ){				
				window.location="/to_be_booked_jobs.php";
			});				
			
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
					job_status: '<?php echo $job_status ?>'
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
	
	
	
	// check all toggle
	jQuery("#maps_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".maps_chk_box:visible").prop("checked",true);
		jQuery(".tbl-sd tr").addClass("yello_mark");
		jQuery("#map_div").show();
	  }else{
		jQuery(".maps_chk_box:visible").prop("checked",false);
		jQuery(".tbl-sd tr").removeClass("yello_mark");
		jQuery("#map_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".maps_chk_box").click(function(){

	  var chked = jQuery(".maps_chk_box:checked").length;
	  
	  if(jQuery(this).prop("checked")==true){
		jQuery(this).parents("tr:first").addClass("yello_mark");
	  }else{
		jQuery(this).parents("tr:first").removeClass("yello_mark");
	  }
	 
	  
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
			location.reload();
		});	
				
	});
	
	
	// assign to DK
	jQuery("#btn_assign_dk").click(function(){
		
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var date = jQuery("#maps_date").val();
		var agency_id_arr = new Array(); 
		var agency_arr = new Array(); 
		var error = '';
		
		jQuery(".maps_chk_box:checked").each(function(){
			var is_dk_allowed = jQuery(this).parents("tr:first").find(".is_dk_allowed").val();
			if( parseInt(is_dk_allowed) ==1 ){
				job_id.push(jQuery(this).val());
			}else{
				var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
				var agency_name = jQuery(this).parents("tr:first").find(".agency_td").html();
				if(jQuery.inArray(agency_id,agency_id_arr)===-1){
					agency_id_arr.push(agency_id);
					agency_arr.push(agency_name);
				}
			}			
		});
		
		console.log("DK agencies num: "+agency_arr.length);
		console.log("job_id: "+job_id.length);
		console.log("tech_id: "+tech_id);
		console.log("date: "+date);
		//console.log("number of job selected "+job_id.length);
		
		
		if( tech_id=='' ){
			error += "Tech is required\n";
		}
		
		if( date=='' ){
			error += "Date is required\n";
		}

		if( error !='' ){
			
			alert(error);
			
		}else{
			
			if( job_id.length>0 ){
			
				if(agency_arr.length>0){
					
					var msg = "These agencies are not allowed Dks: \n\n";
					for(var i=0;i<agency_arr.length;i++){
						msg += agency_arr[i]+" \n";
					}
					msg += "\n";
					msg += "Other jobs will be added as DKs \n";
					msg += "Press OK to continue";
					if(confirm(msg)){
						
						
						jQuery.ajax({
							type: "POST",
							url: "ajax_to_be_booked_assign_dk.php",
							data: { 
								job_id: job_id,
								tech_id: tech_id,
								date: date
							}
						}).done(function( ret ){							
							window.location='/to_be_booked_jobs.php';
						});	
						
						
						
					}
					
				}else{
					
					//console.log("number of not allowed DKs agencies: "+agency_arr.length);
					
					jQuery.ajax({
						type: "POST",
						url: "ajax_to_be_booked_assign_dk.php",
						data: { 
							job_id: job_id,
							tech_id: tech_id,
							date: date
						}
					}).done(function( ret ){						
						//location.reload();
						window.location='/to_be_booked_jobs.php';
					});	
					
				}
				
			}else{
				alert("All jobs selected are non DK by agency");
			}
			
		}
		
		
				
	});
	
	
});
</script>
</body>
</html>