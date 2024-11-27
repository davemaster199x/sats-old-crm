<?php

$title = "DHA";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();

$crm = new Sats_Crm_Class;

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
$job_status = 'DHA';

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

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','','',$filterregion);
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'','','','','',$filterregion));




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
		<li class="other first"><a title="<?php echo $title; ?>" href="/dha_jobs.php"><strong><?php echo $title; ?></strong></a></li>
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
					
						<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
						<link rel="stylesheet" type="text/css" href="/jquery_multiselect/css/jquery.multiselect.css" />
						<script type="text/javascript" src="/jquery_multiselect/js/jquery.multiselect.js"></script>
						<label><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?>:</label>
						 <select name="postcode_region_id[]" id="postcode_region_id" class="multi_select" style="max-width: 250px;" multiple="multiple">
							<?php
							$regions = Regions::getAllRegions();
							foreach ($regions as $region) {
								echo "<option value='" . $region['postcode_region_postcodes'] . "' " . ( (strstr($filterregion,$region['postcode_region_postcodes']))?'selected="selected"':'' ) . ">";
								echo $region['postcode_region_name'];
								echo "</option>\n";
								echo "\n";
							}
							?>
						</select>
						<script type="text/javascript">
						jQuery(function(){
							jQuery(".multi_select").multiselect({
								noneSelectedText: "Any"
							});
							//jQuery(".ui-multiselect span:eq(1)").html("Any");
							jQuery("div.ui-widget-header").css("background","#b4151b");
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
				<th>Tenant Data</th>
				<th><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></th>
			
				<th style="width:10%;">Booking</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Price</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.job_price&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.job_price')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>Address</th>
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th style="width:30%">Comments</th>
				
				<th>MITM</th>
				
				<th>Job #</th>
				
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						$tenants_arr = [];
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						$tdf_start_date = date("Y-m-d",strtotime($row['start_date']." -3 days"));
						$tdf_end_date = date("Y-m-d",strtotime($row['due_date']." -3 days")); 
						
						$row_color = "style='background-color:#ffff9d;'";
						
						if( date("Y-m-d")>=$tdf_start_date ){
							$row_color = "style='background-color:#ffffff;'";
						}
						
						if( date("Y-m-d")>=$tdf_end_date ){
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
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($row['start_date']!="" && $row['start_date']!="0000-00-00")?date("d/m/Y",strtotime($row['start_date'])):''; ?></td>
							<td><?php echo ($row['due_date']!="" && $row['due_date']!="0000-00-00")?date("d/m/Y",strtotime($row['due_date'])):''; ?></td>
							<td>
							
							<?php 
							
							// new tenants switch
							//$new_tenants = 0;
							$new_tenants = NEW_TENANTS;
							$tenants_arr = [];
							
							if( $new_tenants == 1 ){ // new
							
								$pt_params = array( 
									'property_id' => $row['property_id'],
									'active' => 1,
									'return_count' => 1
								 );
								while( $pt_row = mysql_fetch_array($pt_sql) ){
	
									if( $pt_row['tenant_landline'] != '' || $pt_row['tenant_mobile'] != '' ){
										$tenants_arr[] = $pt_row['tenant_firstname'];
									}
									
								}
							
							}else{ // old
							
								$num_tenants = getCurrentMaxTenants();
								for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
									if( $row['tenant_ph'.$pt_i] != '' || $row['tenant_mob'.$pt_i] != '' ){
										$tenants_arr[] = $row['tenant_firstname'.$pt_i];
									}
								}								
								
								
							}
							
							$tenants_count = count($tenants_arr);
							
							
							//print_r($tenants_arr);
							
							if( $tenants_count > 0 ){ ?>
								<img src="images/red_phone.png" />
							<?php
							}
							?>
							
							
							</td>
							
							<td>
							<?php 
							// region				
							$pr_sql = mysql_query("
								SELECT *
								FROM `postcode_regions` AS pr
								LEFT JOIN `regions` AS r ON pr.`region` =  r.`regions_id`
								WHERE pr.`postcode_region_postcodes` LIKE '%{$row['p_postcode']}%'
								AND pr.`country_id` = {$_SESSION['country_default']}
								AND pr.`deleted` = 0
							");
							$pr = mysql_fetch_array($pr_sql);
							
							echo "{$pr['postcode_region_name']}";
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
							
							
							<td><?php echo $row['job_price']; ?></td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
		
							<td><?php echo $row['comments']; ?></td>
							
							<td><?php echo $row['work_order']; ?></td>
							
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
				
					<option value="<?php echo $tech['StaffID']; ?>"><?php echo "{$tech['first_name']} {$tech['last_name']}"; ?>
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