<?php

$title = "Escalate";
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
$mp_id = mysql_real_escape_string($_REQUEST['mp_id']);
$job_status = 'Escalate';

$country_id = $_SESSION['country_default'];

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}

//echo $filterregion;

// sort
//$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.`id`';
//$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'j.`id`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';



// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

// list
$jparams = array(
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(	
		array(
			'order_by' => 'a.`agency_name`',
			'sort' => 'ASC'
		)
	),
	'count_jobs_by_agency' => 1,
	'job_status' => $job_status,
	'country_id' => $country_id,
	'agency_id' => $agency,
	'phrase' => $phrase,
	'a_postcode_region_id' => $filterregion,
	'a_state' => $state,
	'group_by' => 'a.`agency_id`',
	'mp_join' => 1,
	'maintenance_id' => $mp_id,
	'display_echo' => 0
);
$plist = $crm->getJobsData($jparams);


// paginate
$jparams = array(
	'count_jobs_by_agency' => 1,
	'job_status' => $job_status,
	'country_id' => $country_id,
	'agency_id' => $agency,
	'phrase' => $phrase,
	'a_postcode_region_id' => $filterregion,
	'a_state' => $state,
	'group_by' => 'a.`agency_id`',
	'mp_join' => 1,
	'maintenance_id' => $mp_id
);
$ptotal = mysql_num_rows($crm->getJobsData($jparams));





?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update, .tenant_details_row, .tas_hid_elem{
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
						<label>State:</label>
						<select id="state" name="state" style="width: 70px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'count_jobs_by_agency' => 1,
							'job_status' => $job_status,
							'country_id' => $country_id,
							'agency_id' => $agency,
							'phrase' => $phrase,
							'postcode_region_id' => $filterregion,
							'distinct' => 'a.`state`',
							'group_by' => 'a.`state`',
							'custom_filter' => " AND a.`state`!= '' "
						);
						$state_sql = $crm->getJobsData($jparams);
										
						while($state_row =  mysql_fetch_array($state_sql)){ ?>
							<option value="<?php echo $state_row['state']; ?>" <?php echo ($state_row['state'] == $state)?'selected="selected"':''; ?>><?php echo $state_row['state']; ?></option>
						<?php	
						}
						?>	
					 </select>
					</div>
					
					
					<div class="fl-left">
						<label>Maintenance Program:</label>
						<select id="mp_id" name="mp_id" style="width: 130px;">
						<option value="">Any</option>
						<?php
						$jparams = array(
							'job_status' => $job_status,
							'country_id' => $country_id,
							'distinct' => 'am.`maintenance_id`',
							'sort_list' => array(
								array(
									'order_by' => 'm.`name`',
									'sort' => 'ASC'
								)
							),
							'mp_join' => 1
						);
						$m_sql = $crm->getJobsData($jparams);					
						while($mp =  mysql_fetch_array($m_sql)){ 
							if( $mp['maintenance_id'] > 0 ){
							?>
								<option value="<?php echo $mp['maintenance_id']; ?>" <?php echo ($mp['maintenance_id'] == $mp_id)?'selected="selected"':''; ?>><?php echo $mp['m_name']; ?></option>
							<?php
							}
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
								// paginate
								$jparams = array(
									'count_jobs_by_agency' => 1,
									'job_status' => $job_status,
									'country_id' => $country_id,
									'agency_id' => $agency,
									'phrase' => $phrase,
									'postcode_region_id' => $filterregion,
									'distinct' => 'a.`state`',
									'group_by' => 'a.`state`',
									'custom_filter' => " AND a.`state`!= '' "									
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
								
		
								$jcount_txt = "(".$jc->getMainRegionCountForEscalate($_SESSION['country_default'],$main_region_postcodes,'',$job_status).")";
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
					
				
					
					
					
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;">
						<input type='hidden' class='submitbtnImg' value='Search' />
						<button type='submit' class='submitbtnImg' id="btn_search">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							Search
						</button>
					</div>
				
				
				<!--
				<div class='fl-right'>
						<a href='/view_jobs_export.php?status=escalate&filterdate=<?php echo $date; ?>&search=<?php echo $phrase; ?>' class='vj-pg-e submitbtnImg export'>Export</a>
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

				<th>Jobs</th>
				<th>&nbsp;</th>
				<th>Agency</th>
				<th>Phone</th>
				<th>Left Message</th>
				<th>Emailed</th>				
				<th>Notes</th>
				<th>Last Updated</th>
				<th>Save Notes</th>
				<th>Trust Accounting Software</th>
				<th>Connected</th>
				
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						// get selected state
						$jparams = array(
							'country_id' => $country_id,
							'agency_id' => $row['agency_id'],
							'date' => date('Y-m-d')
						);
						$eai_sql = $crm->getEscalateAgencyInfo($jparams);
						$eai = mysql_fetch_array($eai_sql);
						
					// grey alternation color
					//$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";	
					
					
					// if save notes marker is checked, then display escalate notes from agency
					if($row['save_notes']==1){
						// notes from agency
						$notes_txt = $row['escalate_notes'];
						$notes_ts = ( $row['escalate_notes']!='' && $row['escalate_notes_ts']!="" )?date('d/m/Y H:i',strtotime($row['escalate_notes_ts'])):'';	
					}else{
						// everyday notes
						$notes_txt = $eai['notes'];
						$notes_ts = ( $eai['notes']!='' && $eai['notes_timestamp']!="" )?date("d/m/Y H:i",strtotime($eai['notes_timestamp'])):'';
					}
					
				?>
						<tr class="body_tr jalign_left" <?php echo ( $eai['left_message']==1 || $eai['emailed']==1 || $eai['notes']!='' )?'style="background-color:#eeeeee;"':''; ?>>
	
							<td>
								<?php echo $row['esc_num_jobs']; ?>
								<input type="hidden" class="agency_id" value="<?php echo $row['agency_id']; ?>" />
							</td>	
							<td>
								<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row['agency_id']}"); ?>
								<a href="<?php echo $ci_link; ?>">
									<img src="/images/agency_info.png" style="width:20px;" />
								</a>
							</td>
							<td><a href="/escalate_jobs.php?agency=<?php echo $row['agency_id']; ?>"><?php echo $row['agency_name']; ?></a></td>
							<td><?php echo $row['phone']; ?></td>
							<td>
								<input type="checkbox" <?php echo ($eai['left_message']==1)?'checked="checked"':''; ?> data-esc-field="left_message" name="chk_left_message[]" class="esc_info chk_left_message" value="" />
								<img class="green_check" style="display:none; width: 15px; margin-left: 3px;" src="/images/check_icon2.png" />
							</td>							
							<td>
								<input type="checkbox" <?php echo ($eai['emailed']==1)?'checked="checked"':''; ?> data-esc-field="emailed" name="chk_emailed[]" class="esc_info chk_emailed" value="" />
								<img class="green_check" style="display:none; width: 15px; margin-left: 3px;" src="/images/check_icon2.png" />
							</td>
							
							
							<td>
								<input type='text' data-esc-field="notes" name='notes' class='addinput searchstyle vwjbdtp esc_info notes' style='width: 200px !important;' value="<?php echo $notes_txt; ?>">
								<img class="green_check" style="display:none; width: 15px; margin-left: 10px; margin-top: 9px;" src="/images/check_icon2.png" />
							</td>
							
							<td>
								<label style="color:#00D1E5;" class="last_update_ts">
								<?php echo $notes_ts; ?>
							</td>

							<td>
								<input type="checkbox" <?php echo ($row['save_notes']==1)?'checked="checked"':''; ?> name="chk_save_notes[]" class="chk_save_notes" value="" />
								<img class="green_check" style="display:none; width: 15px; margin-left: 3px;" src="/images/check_icon2.png" />
							</td>
							<td>
								<?php
								if( $row['trust_account_software'] != 0 ){ ?>
									<a href="javascript:void(0);" class="tas_link"><?php echo $crm->getTrustAccountSoftware($row['trust_account_software']); ?></a>
								<?php
								}else{ ?>
									<button type="button" class="submitbtnImg blue-btn tas_link">
										<img class="inner_icon" src="images/button_icons/edit-button.png">
										Edit
									</button>
								<?php
								}
								?>
								
								
								<select class="tas_dp tas_hid_elem" style="width: 70px;">
									<option value="">Any</option>
									<?php
									$tas_arr = $crm->getTrustAccountSoftware();
									foreach( $tas_arr as $index => $tas ){ ?>
										<option value="<?php echo $tas['index']; ?>" <?php echo ( $row['trust_account_software'] == $tas['index'] )?'selected="selected"':''; ?>><?php echo $tas['value']; ?></option>
									<?php
									}
									?>
									<option value="-1" <?php echo ( $row['trust_account_software'] == -1 )?'selected="selected"':''; ?>>Other</option>
								 </select>
								 <button type="button" class="submitbtnImg tas_cancel tas_hid_elem">
									<img class="inner_icon" src="images/button_icons/cancel-button.png">
									Cancel
								</button>
								 
							</td>
							<td>
								<?php echo ( $row['propertyme_agency_id'] != '' )?'<strong style="color:green;">YES</strong>':'<strong style="color:red;">NO</strong>'; ?>
							</td>
							
							
						</tr>
						
						
				<?php
					$i++;
					}
				}else{ ?>
					<tr><td colspan="100%" align="left">Empty</td></tr>
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


function updateAgencyEscalateNotes(obj){
	
	
	
	var agency_id = obj.parents("tr:first").find(".agency_id").val();
	var save_notes_chk_status = obj.parents("tr:first").find(".chk_save_notes").prop('checked');
	var escalate_notes = obj.parents("tr:first").find(".notes").val();
	
	// if checkbox 
	var save_notes_chk = (save_notes_chk_status==true)?1:0;
	
	jQuery.ajax({
		type: "POST",
		url: "ajax_update_agency_save_notes.php",
		data: { 
			agency_id: agency_id,
			save_notes_chk: save_notes_chk,
			escalate_notes: escalate_notes
		}
	}).done(function( ret ){	
	
		obj.parents("tr:first").find(".last_update_ts").html("<?php echo date("d/m/Y H:i"); ?>");
		obj.parents("tr:first").css('background-color','#eeeeee');
		obj.parents("td:first").find(".green_check").show();
		
	});
	
}


jQuery(document).ready(function(){
	
	
	// TAS update script
	jQuery(".tas_dp").change(function(){
		
		var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
		var tas_id = jQuery(this).val();
		
		jQuery("#load-screen").show();
			
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_agency_tas.php",
			data: { 
				agency_id: agency_id,
				tas_id: tas_id
			}
		}).done(function( ret ){	
			jQuery("#load-screen").hide();
			window.location="escalate.php";
		});
		
	});
	
	
	jQuery(".tas_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".tas_link").show();
		jQuery(this).parents("tr:first").find(".tas_hid_elem").hide();
		
	});
	
	jQuery(".tas_link").click(function(){
		
		jQuery(this).hide();
		jQuery(this).parents("tr:first").find(".tas_hid_elem").show();
		
	});
	
	
	
	jQuery(".chk_save_notes").click(function(){
		
		var obj = jQuery(this);
		updateAgencyEscalateNotes(obj);
		
		
	});
	
	
	jQuery(".esc_info").change(function(){
		
		var obj = jQuery(this);
		
		// if checkbox 
		if( obj.attr('type')=='checkbox' ){
			
			if( obj.prop('checked')==true ){
				obj.val(1);
			}else{
				obj.val(0);
			}
			
		}
		
		var chk_save_notes = obj.parents("tr:first").find(".chk_save_notes").prop('checked');
		var agency_id = obj.parents("tr:first").find(".agency_id").val();
		var eai_field = obj.attr("data-esc-field");		
		var eai_val = obj.val();
		
		//console.log("save notes chk is checked?: "+chk_save_notes);
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_insert_escalate_agency_info.php",
			data: { 
				agency_id: agency_id,
				eai_field: eai_field,
				eai_val: eai_val
			}
		}).done(function( ret ){	
		
			//jQuery("#load-screen").hide();
			//jQuery(".region_dp_body").append(ret);
			//window.location="/escalate.php";
			
			obj.parents("tr:first").find(".last_update_ts").html("<?php echo date("d/m/Y H:i"); ?>");
			obj.parents("tr:first").css('background-color','#eeeeee');
			obj.parents("td:first").find(".green_check").show();
			
		});
		
		// save notes checkbox is checked, should save notes to agency
		if( chk_save_notes==true ){ 
			updateAgencyEscalateNotes(obj);
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
				url: "ajax_regionFilter_getMainRegionCountForEscalate.php",
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
		var check_all = obj.parents("li.main_region_li").find(".check_all_sub_region").prop("checked");
		
		
		
		if(sub_reg_space==""){
			
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_regionFilter_getSubRegionCountForEscalate.php",
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