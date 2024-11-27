<?php

$title = "NEW Jobs";
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
$job_status = 'Send Letters';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'');
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,''));


function this_whoCreatedSendLetters($property_id){
	
	$sql = mysql_query("
		SELECT *
		FROM `property_event_log` AS pl
		WHERE pl.`property_id` ={$property_id}
		AND pl.`event_type` = 'Property Added' 
		LIMIT 1
	");
	
	$row = mysql_fetch_array($sql);
	if($row['log_agency_id']!=""){
		$who = 'AGENCY';
	}else if($row['staff_id']!=0){
		$who = 'SATS';
	}else{
		$who = 'AGENCY';
	}
	
	return $who;
	
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
	background-color: #ffff9d !important;
}
.green_mark{
	background-color: #c2ffa7;
}
.green-btn {
    background-color: green !important;
}
</style>




<div id="mainContent">

   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/send_letter_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		if($_GET['email_sent']==1){
			echo '<div class="success">Email Sent</div>';
		}
		
		if($_GET['sms_sent']==1){
			echo '<div class="success">SMS Sent</div>';
		}
		
		
		
		
		//echo date('Y-m-d', strtotime("-30 days"));
		
		
		?>
		
		
		<div style="text-align: left;">
			<ul>
				<li><img title="No Tenants" src="/images/no_tenant.png" style="width: 20px;"  /> (No Tenant Details) Job will be moved to 'Escalate’</li>
				<li><img src="/images/email_button_green.png" style="width: 20px;" /> (Tenant has email address) Tenant will be emailed an introduction email</li>
				<li><img src="/images/sms_button_green.png" style="width: 20px;" /> (Tenant has a mobile number) Tenant will be SMS'd introduction and Agent emailed</li> 
				<li><span style="background-color:#ffff9d;">Yellow Highlight</span>, read the job comments then process manually by entering job</li>											
				<li><strong>No Icon</strong> Click <i>'Export'</i>, perform mail merge, click <i>'Mark Letters sent'</i> to Email Agent</li>											
			</ul>
		</div>
		
		
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
							$jt_sql = $jc->getJobs('','',$sort,$order_by,'',$job_status,'','','','','j.`job_type`');
							while($jt =  mysql_fetch_array($jt_sql)){ ?>
								<option value="<?php echo $jt['job_type']; ?>" <?php echo ($jt['job_type'] == $job_type)?'selected="selected"':''; ?>><?php echo $jt['job_type']; ?></option>
							<?php	
							}
							?>	
						</select>
					</div>
				  
					 <?php
					$ajt_sql = $jc->getJobs('','',$sort,$order_by,'',$job_status,'','','','','j.`service`');
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
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;">
						<input type='hidden' class='submitbtnImg' value='Search' />
						<button type="submit" class="submitbtnImg blue-btn">
							<img class="inner_icon" src="images/button_icons/search-button.png" />
							Search
						</button>
					</div>

					
					
					<div style="float:right; margin: 10px 0 0 35px !important;">
						<a href='view_jobs_export.php?status=sendletters&filterdate=<?php echo $date; ?>&search=<?php echo $phrase; ?>'>
							<button class="btn_export submitbtnImg export" type="button">
								<img class="inner_icon" src="images/button_icons/export.png">
								Export
							</button>
						</a>						
					</div>
				
					<!--
					<div style="float:right; margin: 10px 0 0 35px !important;">
						<button type="button" class="submitbtnImg blue-btn mrkltsend">
							<img class="inner_icon" src="images/button_icons/select-button.png" />
							Mark Letters Sent
						</button>
					</div>
					-->
					
					<div style="float:right; margin: 16px 0 0 35px !important;">
						<?php 
						$ue_sql = mysql_query("
							SELECT `cron_send_letters`
							FROM `crm_settings`
							WHERE `country_id` = {$_SESSION['country_default']}
						");
						$ue = mysql_fetch_array($ue_sql);
						$ae_val = $ue['cron_send_letters'];
						if( $ae_val==1 ){
							$ae_txt = 'Active';
							$ae_color = 'green';
							$is_checked = 'checked="checked"';
						}else{
							$ae_txt = 'Inactive';
							$ae_color = 'red';
							$is_checked = '';
						}					
						?>
						<input type="checkbox" style="width: auto;" id="chk_cron_send_letter_toggle" <?php echo $is_checked; ?> /> <span style="color:<?php echo $ae_color; ?>">Auto Emails <?php echo $ae_txt; ?></span>					
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

		
		<table id="sl_tble" border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd vjobs-n" style="margin-top: 0px; margin-bottom: 13px;">			
			<tr class="toprow jalign_left">

	
			
				<th>Job Type</th>
				
				<th class="j_icons_col">
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
				<th style="width:20%;">Job Comments</th>
				<th style="width:15%;">Property Comments</th>
				
				<th>Details</th>
				
				<th>Start Date</th>
				<th>End Date</th>
				
				<th>Job #</th>
				<th><input type="checkbox" id="chk_email_all" style="display:none;" /></th>
				<th><input type="checkbox" id="chk_sms_all" style="display:none;" /></th>
				<th><div class="tbl-tp-name colorwhite bold"><input type="checkbox" id="maps_check_all" /></div></th>
				<th>&nbsp;</th>
			</tr>
				<?php
				
				
				$i= 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						$has_tenants = false;
						$has_tenant_email = 0;
						$has_mobile_num = 0;
				
						$pt_params = array( 
							'property_id' => $row['property_id'],
							'active' => 1
						 );
						
						$pt_sql = $crm->getNewTenantsData($pt_params);
						while( $pt_row = mysql_fetch_array($pt_sql) ){
							
							// check if it has tenants
							if(  $pt_row['tenant_firstname'] != "" || $pt_row['tenant_lastname'] != ""  ){
								$has_tenants = true;
							}
							
							// check if there is at least 1 tenant email
							if( $pt_row['tenant_email'] != ""  ){
								$has_tenant_email = 1;
							}
							
							// check if there is at least 1 tenant mobile
							if( $pt_row['tenant_mobile'] != "" ){
								$has_mobile_num = 1;
							}
							
						}
						
						//echo "Job ID: {$row['jid']} has_tenants:";
						//var_dump($has_tenants);
						//echo "<br />";
						
						if( 
						
							$row['property_vacant']==1 || $has_tenants == false
							
						){
							//$row_color = "style='background-color:#ffff9d;'";
							$is_no_tenants = 1;
						}else{
							$is_no_tenants = 0;
						}
						
						// urgent jobs
						if($row['urgent_job']==1){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// jobs not completed
						if($row['job_reason_id']>0){
							//$row_color = "style='background-color:#ffff9d;'";
						}
						
						if( $row['comments']!="" ){
							$row_color = "style='background-color:#ffff9d;'";
						}
						
					
				?>
						<tr class="body_tr jalign_left <?php echo ($is_no_tenants==1)?'no_tenants_row':'tenant_present_row'; ?> <?php echo ($has_tenant_email==1)?'has_tenants_email_row':'no_tenants_email_row'; ?> <?php echo ($has_mobile_num==1)?'has_mobile_num_row':'no_mobile_num_row'; ?>" <?php echo $row_color; ?>>
							
						
							
							<td><?php echo getJobTypeAbbrv($row['job_type']); ?></td>
							
							<td>								
								<?php
								// display icons
								$job_icons_params = array(
									'service_type' => $row['jservice'],
									'job_type' => $row['job_type']
								);
								echo $crm->display_job_icons($job_icons_params);
								?>
							</td>
							<td><?php echo $row['job_price']; ?></td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
							<td><?php echo $row['comments']; ?></td>
							<td><?php echo $row['p_comments']; ?></td>
							
							<td>
							<?php 
							if( $row['holiday_rental']==1 ){ ?>
								<img title="Short Term Rental" class="holiday_rental" src="/images/holiday.png" />
							<?php	
							}
							?>							
							<?php 
							if( $is_no_tenants == 1 ){ ?>
								<img title="No Tenants" class="no_tenant_icon" style="cursor: pointer;" src="/images/no_tenant.png" />
							<?php	
							}
							?>
							</td>
							
							<td><?php echo ($row['start_date']!="")?date('d/m/Y',strtotime($row['start_date'])):''; ?></td>
							<td><?php echo ($row['due_date']!="")?date('d/m/Y',strtotime($row['due_date'])):''; ?></td>
							
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							<td>
								<?php
								if( $has_tenant_email == 1 ){ ?>
									<img class="email_it" style="width: 20px; cursor: pointer; width:24px;" src="/images/email_button_green.png" />
									<input type="checkbox" style="bottom: 4px; position: relative;" class="job_id" value="<?php echo $row['jid']; ?>" />
								<?php
								}
								?>								
							</td>
							<td>
								<?php
								if( $has_mobile_num == 1 && $has_tenant_email!=1 ){ ?>									
									<img class="sms_it" style="width: 20px; cursor: pointer; width:24px;" src="/images/sms_button_green.png" />
									<input type="checkbox" class="job_id_sms" value="<?php echo $row['jid']; ?>" />
								<?php
								}
								?>								
							</td>
							<td>
								<?php
								if( $is_no_tenants == 1 ){ ?>
									<img title="No Tenants" class="no_tenant_icon" style="cursor: pointer;" src="/images/no_tenant.png" />
									<input type="checkbox" class="maps_chk_box" value="<?php echo $row['jid']; ?>" />
								<?php	
								}
								?>	
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
							</td>							
							<td>
							<?php
							echo ($row['jcreated']!="")?date("H:i",strtotime($row['jcreated'])):'';
							?>
							</td>							
						</tr>
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="17" align="left">Empty</td>
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

		<div style="margin: 15px 0 0 15px; float: right; display:none;" id="no_tenants_div">
			<button id="btn_no_tenants" class="btn_no_tenants submitbtnImg blue-btn" type="button">
				<img class="inner_icon" src="images/button_icons/no_tenant.png" />
				No Tenant Details
			</button>
		</div>
		
		<div style="margin-top: 15px; float: right; display:none;" id="send_tenants_div">	
			<button id="btn_send_tenants" class="btn_send_tenants submitbtnImg blue-btn" type="button">
				<img class="inner_icon" src="images/button_icons/email.png" />
				Email Tenant
			</button>
		</div>
		
		<div style="margin-top: 15px; float: right; display:none;" id="send_sms_div">			
			<button id="btn_send_tenants_sms" class="btn_send_tenants_sms submitbtnImg blue-btn" type="button">
				<img class="inner_icon" src="images/button_icons/sms_icon.png">
				SMS Tenant
			</button>
		</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// auto email script
	jQuery("#chk_cron_send_letter_toggle").change(function(){
		
		var cron_status = ( jQuery(this).prop("checked")==true )?1:0;
		var cron_file = 'cron_send_letter_functions';
		var db_field = 'cron_send_letters';
		
		if(confirm("Are You Sure You Want to Continue?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_toggle_cron_on_off.php",
				data: { 
					cron_status: cron_status,
					cron_file: cron_file,					
					db_field: db_field
				}
			}).done(function( ret ){
				
				window.location.href="/send_letter_jobs.php?auto_emails_set=1";
				
			});	
		}		
		
	});
	
	
	//SMS
	// make checkall visible if at least one sms checkbox
	if(jQuery("input.job_id_sms:visible").length>0){
		jQuery("#chk_sms_all").show();
	}

	// SMS TENANT
	// check all toggle for sms Column
	jQuery("#chk_sms_all").click(function(){

	  if(jQuery(this).prop("checked")==true){
		jQuery("#sl_tble .has_mobile_num_row").addClass("yello_mark");
		jQuery(".job_id_sms:visible").prop("checked",true);
		jQuery("#send_sms_div").show();
	  }else{
		jQuery("#sl_tble .has_mobile_num_row").removeClass("yello_mark");
		jQuery(".job_id_sms:visible").prop("checked",false);
		jQuery("#send_sms_div").hide();
	  }
	  
	});
	
	// SMS toggle hide/show send sms button
	jQuery(".job_id_sms").click(function(){

	  var chked = jQuery(".job_id_sms:checked").length;
	  
	  if(jQuery(this).prop("checked")==true){
		   jQuery(this).parents("tr:first").addClass("yello_mark");
	  }else{
		   jQuery(this).parents("tr:first").removeClass("yello_mark");
	  }
	 
	  
	  if(chked>0){
		jQuery("#send_sms_div").show();
	  }else{
		jQuery("#send_sms_div").hide();
	  }

	});
	
	
	// make checkall visible if at least one email checkbox
	if(jQuery("input.job_id:visible").length>0){
		jQuery("#chk_email_all").show();
	}
	
	// inline send tenant email
	jQuery(".email_it").click(function(){
		
		var job_id_arr = new Array();
		var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
		job_id_arr.push(job_id);
		
		if(confirm("Are you sure you want to email the tenants?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_email_it.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){

				window.location.href="/send_letter_jobs.php?email_sent=1";

			});	
		}		
		
	});
	
	// inline send not tenant email
	jQuery(".no_tenant_icon").click(function(){
		
		var job_id_arr = new Array();
		var job_id = jQuery(this).parents("tr:first").find(".maps_chk_box").val();
		job_id_arr.push(job_id);
		
		if(confirm("Are You Sure You Want to Mark No Tenant Details?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_no_tenant_email.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){

				window.location.href="/send_letter_jobs.php?email_sent=1";

			});	
		}		
		
	});
	
	// inline send tenant sms
	jQuery(".sms_it").click(function(){
		
		var job_id_arr = new Array();
		var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
		job_id_arr.push(job_id);
		
		if(confirm("Are you sure you want to sms the tenants?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_sms_it.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){

				window.location.href="/send_letter_jobs.php?sms_sent=1";

			});	
		}		
		
	});
	
	// send email in BULK
	jQuery("#btn_send_tenants").click(function(){
		
		var job_id_arr = new Array();
		jQuery(".job_id:checked").each(function(){
			var job_id = jQuery(this).val();
			job_id_arr.push(job_id);
		});
		
		if(confirm("Are you sure you want to email the tenants?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_email_it.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){
				
				window.location.href="/send_letter_jobs.php?email_sent=1";
				
			});	
		}		
		
	});
	
	
	// send sms in BULK
	jQuery("#btn_send_tenants_sms").click(function(){
		
		var job_id_arr = new Array();
		jQuery(".job_id_sms:checked").each(function(){
			var job_id = jQuery(this).val();
			job_id_arr.push(job_id);
		});
		
		if(confirm("Are you sure you want to sms the tenants?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_sms_it.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){
				
				window.location.href="/send_letter_jobs.php?sms_sent=1";
				
			});	
		}		
		
	});
	
	
	// send no tenant email in BULK
	jQuery("#btn_no_tenants").click(function(){
		
		var job_id_arr = new Array();
		jQuery(".maps_chk_box:checked").each(function(){
			var job_id = jQuery(this).val();
			job_id_arr.push(job_id);
		});
		
		if(confirm("Are You Sure You Want to Mark No Tenant Details?")){
			// email it
			jQuery.ajax({
				type: "POST",
				url: "ajax_send_letters_no_tenant_email.php",
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function( ret ){
				
				window.location.href="/send_letter_jobs.php?email_sent=1";
				
			});	
		}		
		
	});
	
	
	
	// confirm sent letter
	jQuery(".mrkltsend").click(function(){
		
		if(confirm("Are You Sure You Want to Mark All Letters Sent?")){
			window.location="/letter_sent_jobs.php";
		}
		
	});
	
	if(jQuery(".maps_chk_box:visible").length==0){
		jQuery("#maps_check_all").hide();
	}
	
	/*
	// confirm no tenants
	jQuery(".btn_no_tenants").click(function(){
		
		var str = "";
		
		jQuery(".maps_chk_box:checked").each(function(){
			str = str + ","+jQuery(this).val();
		});
		
		var job_id = str.substring(1); 
		
		//console.log(job_id);
		
		if(job_id!=""){
			if(confirm("Are You Sure You Want to Mark No Tenant Details?")){
				window.location="/no_tenant_details.php?job_ids="+job_id;
			}
		}else{
			alert("Please select at least 1 item");
		}
		
	});
	
	*/
	
	/*
	// confirm no tenants
	jQuery(".no_tenant_icon").click(function(){
		
		var job_id = jQuery(this).parents("tr:first").find(".maps_chk_box").val(); 
		
		//console.log(job_id);
		
		if(job_id!=""){
			if(confirm("Are You Sure You Want to Mark No Tenant Details?")){
				//console.log("/no_tenant_details.php?job_ids="+job_id);
				window.location="/no_tenant_details.php?job_ids="+job_id;
			}
		}else{
			alert("Please select at least 1 item");
		}
		
	});
	*/
	
	// NO TENANT
	// check all toggle
	jQuery("#maps_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery("#sl_tble .no_tenants_row").addClass("yello_mark");
		jQuery(".maps_chk_box:visible").prop("checked",true);
		jQuery("#no_tenants_div").show();
	  }else{
		jQuery("#sl_tble .no_tenants_row").removeClass("yello_mark");
		jQuery(".maps_chk_box:visible").prop("checked",false);
		jQuery("#no_tenants_div").hide();
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
		jQuery("#no_tenants_div").show();
	  }else{
		jQuery("#no_tenants_div").hide();
	  }

	});
	
	
	// SEND TENANT
	// check all toggle for Email Column
	jQuery("#chk_email_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery("#sl_tble .has_tenants_email_row").addClass("yello_mark");
		jQuery(".job_id:visible").prop("checked",true);
		jQuery("#send_tenants_div").show();
	  }else{
		jQuery("#sl_tble .has_tenants_email_row").removeClass("yello_mark");
		jQuery(".job_id:visible").prop("checked",false);
		jQuery("#send_tenants_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".job_id").click(function(){

	  var chked = jQuery(".job_id:checked").length;
	  
	  if(jQuery(this).prop("checked")==true){
		   jQuery(this).parents("tr:first").addClass("yello_mark");
	  }else{
		   jQuery(this).parents("tr:first").removeClass("yello_mark");
	  }
	 
	  
	  if(chked>0){
		jQuery("#send_tenants_div").show();
	  }else{
		jQuery("#send_tenants_div").hide();
	  }

	});
	
	
});
</script>
</body>
</html>