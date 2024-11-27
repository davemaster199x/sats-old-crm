<?php
$start_load_time = microtime(true);
$title = "Merged Jobs";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function mergeJobSentSmsCount(){
	$ss_sql = mysql_query("
		SELECT COUNT(*) AS jcount
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.`status` = 'Merged Certificates'
		AND CAST( j.`sms_sent_merge` AS date )  = '".date("Y-m-d")."'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` = {$_SESSION['country_default']}		
	");
	$ss = mysql_fetch_array($ss_sql);
	return $ss['jcount'];
}

function mj_getAlarmTotalPrice($job_id){
	$sql = mysql_query("
		SELECT SUM(alarm_price) AS tot_alarm_price
		FROM  `alarm` 	
		WHERE `job_id` = {$job_id}
		AND `new` = 1
		AND `ts_discarded` = 0
	");
	$row = mysql_fetch_array($sql);
	return $row['tot_alarm_price'];
}

function mj_getMergeJobTotalJobPrice(){
	
	$sql = mysql_query("
		SELECT SUM(j.`job_price`) AS jprice
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE j.`status` = 'Merged Certificates'
		AND p.`deleted` =0 
		AND a.`status` = 'active' 
		AND j.`del_job` = 0 
		AND a.`country_id` = {$_SESSION['country_default']}	
	");
	
	$row = mysql_fetch_array($sql);
	return $row['jprice'];
}

function mj_getMergeJobTotalAlarmPrice(){
	
	$sql = mysql_query("
		SELECT SUM(alrm.`alarm_price`) AS aprice
		FROM `alarm` AS alrm 
		LEFT JOIN `jobs` AS j ON  alrm.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id` 
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id` 
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.`status` = 'Merged Certificates' 
		AND p.`deleted` =0 
		AND a.`status` = 'active' 
		AND j.`del_job` = 0 
		AND a.`country_id` = {$_SESSION['country_default']}	
		AND alrm.`new`	= 1
		AND alrm.`ts_discarded` = 0
	");
	
	$row = mysql_fetch_array($sql);
	return $row['aprice'];
}

function mj_getMergeJobTotalSubCharge(){
	
	// surcharge
	$sql = mysql_query("
		SELECT SUM(am.`price`) AS am_price
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `agency_maintenance` AS am on a.`agency_id` = am.`agency_id`
		WHERE j.`status` = 'Merged Certificates' 
		AND p.`deleted` =0 
		AND a.`status` = 'active' 
		AND j.`del_job` = 0 
		AND a.`country_id` = {$_SESSION['country_default']}	
		AND am.`surcharge` = 1
	");
	$row = mysql_fetch_array($sql);
	return $row['am_price'];
	
}



function mj_getTotalAge(){
	$sql = mysql_query("
		SELECT SUM( DATEDIFF( '".date('Y-m-d')."', CAST( j.`created` AS DATE ) ) ) AS sum_age
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE p.`deleted` =0 
		AND a.`status` = 'active' 
		AND j.`del_job` = 0 
		AND a.`country_id` = {$_SESSION['country_default']}	
		AND j.`status` = 'Merged Certificates' 	
	");
	$row = mysql_fetch_array($sql);
	return $row['sum_age'];
}

function mj_getAllJobCount(){
	$sql = mysql_query("
		SELECT COUNT( j.`id` ) AS jcount
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE p.`deleted` =0 
		AND a.`status` = 'active' 
		AND j.`del_job` = 0 
		AND a.`country_id` = {$_SESSION['country_default']}	
		AND j.`status` = 'Merged Certificates' 	
	");
	$row = mysql_fetch_array($sql);
	return $row['jcount'];
}

//echo mergeJobSentSmsCount();

// send email
if (isset($_REQUEST['sendemails']) && $_REQUEST['sendemails'] == "yes") {
	$num_emails_sent = batchSendInvoicesCertificates();
}

//include('inc/precompleted_jobs_functions.php'); 

// Initiate job class
$jc = new Job_Class();

$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$job_type = mysql_real_escape_string($_REQUEST['job_type']);
$service = mysql_real_escape_string($_REQUEST['service']);
$state = mysql_real_escape_string($_REQUEST['state']);
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$job_status = 'Merged Certificates';

// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

$plist = $jc->getJobs($offset,$limit,$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,'');
$ptotal = mysql_num_rows($jc->getJobs('','',$sort,$order_by,$job_type,$job_status,$service,$state,$date,$phrase,''));




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
		<li class="other first"><a title="<?php echo $title; ?>" href="/merged_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php
		if( $num_emails_sent>0 ){ ?>
			<div class="success">
				Emails have been processed
			</div>
		<?php	
		}
		?>
		
		
		<?php
		if( $_GET['sms_sent']==1 ){ ?>
			<div class="success">
				SMS sent
			</div>
		<?php	
		}
		?>
		
	
		<?php
		
		if ($date) {
			$filter = " AND j.date='{$date}'";
		}
		
		$email_stats_query = "(
		SELECT 'sent' as result_type, COUNT(j.id) AS result
		FROM jobs j, property p, agency a 
		WHERE j.property_id = p.property_id 
		AND j.status = '{$job_status}'" . $filter . "
		AND  p.agency_id = a.agency_id		
		AND a.account_emails LIKE '%@%'
		AND j.client_emailed IS NOT NULL
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` ={$_SESSION['country_default']}
	)
	
	UNION ALL
	(
		SELECT 'total', COUNT(j.id) AS result
		FROM jobs j, property p, agency a 
		WHERE j.property_id = p.property_id 
		AND j.status = '{$job_status}'" . $filter . "
		AND  p.agency_id = a.agency_id		
		AND a.account_emails LIKE '%@%'
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` ={$_SESSION['country_default']}
	)";

	$email_stats = mysqlMultiRows($email_stats_query);

	$total_to_print = $ptotal - $email_stats[1]['result'];

	$print_query = "(
		SELECT COUNT(*) as to_print FROM jobs j, property p, agency a 
		WHERE j.status = 'Merged Certificates'
		AND j.property_id = p.property_id
		AND p.agency_id = a.agency_id		
		AND a.send_combined_invoice = 0
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		AND a.`country_id` ={$_SESSION['country_default']}
		)
		UNION
		(
			SELECT COUNT(*) as to_print FROM jobs j, property p, agency a 
			WHERE j.status = 'Merged Certificates'
			AND j.property_id = p.property_id
			AND p.agency_id = a.agency_id			
			AND a.send_combined_invoice = 1
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` ={$_SESSION['country_default']}
		)";
		
	$print_totals = mysqlMultiRows($print_query);
		
		
	?>
	<ul class="job_steps">
		<li class="vj-colbcm-hld">
			<div class="dark placeholder">
				<div class="color-breadcrumb">
					
					
					<a href="/merged_jobs.php?sendemails=yes" onclick="return confirm('Are you sure you want to email the invoices / certificates?');" <?php echo($email_stats[0]['result']==$email_stats[1]['result'])?'class="breadcrumb-active"':''; ?>>Email ALL Certificates/Invoices (<?php echo $email_stats[0]['result'] . "/" . $email_stats[1]['result']; ?> Sent)</a>
					
					
					<a id="btn_sms_tenant" href="javascript:void(0);" <?php echo ($email_stats[1]['result']==mergeJobSentSmsCount())?'class="breadcrumb-active"':''; ?>>SMS Tenants (<?php echo mergeJobSentSmsCount(); ?>/<?php echo $email_stats[1]['result']; ?> Sent)</a>
					
					<?php 
					if ($total_to_print > 0){ ?>
						<a href="/batch_view_certificate.php" target="_blank">Print ALL Certificates (<?php echo $print_totals[0]['to_print']; ?>)</a>
						<a href="/batch_view_invoices.php" target="_blank">Print ALL Invoices (<?php echo $print_totals[0]['to_print']; ?>)</a>
						<a href="batch_view_combined.php" target="_blank">Print ALL Combined (<?php echo $print_totals[1]['to_print']; ?>)</a>
					<?php
					}else{ ?>
						<a href="javascript:void(0);" class="breadcrumb-active">Print ALL Certificates (<?php echo $total_to_print; ?>)</a>
						<a href="javascript:void(0);" class="breadcrumb-active">Print ALL Invoices (<?php echo $total_to_print; ?>)</a>
						<a href="javascript:void(0);" class="breadcrumb-active">Print ALL Combined (<?php echo $total_to_print; ?>)</a>
					<?php
					}
					?>
					
					<a href="/export_myob.php">MYOB Export</a>
					
					<a href="/mark_completed.php" onclick="return confirm('Are you sure you want to mark all jobs as completed?');" <?php echo (mysql_num_rows($plist)==0)?'class="breadcrumb-active"':''; ?>>Mark ALL Completed</a> 
					
				</div>
			</div>
		</li>
		
	</ul>
		
		
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
					
				
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker vwjbdtp' style="width:85px!important;">		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float: left;">
						<button type="submit" id="btn_change_agency" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/search-button.png">
							<span class="inner_icon_span">Search</span>
						</button>
					</div>
    
					<div class='fl-right' style="margin-right: 23px; margin-top: 22px;">
						<?php 
						$ue_sql = mysql_query("
							SELECT `cron_merged_cert`
							FROM `crm_settings`
							WHERE `country_id` = {$_SESSION['country_default']}
						");
						$ue = mysql_fetch_array($ue_sql);
						$ae_val = $ue['cron_merged_cert'];
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
						<input type="checkbox" style="width: auto;" id="chk_cron_merged_cert_toggle" <?php echo $is_checked; ?> /> <span style="color:<?php echo $ae_color; ?>">Auto Emails <?php echo $ae_txt; ?></span>					
					</div>
					
					
				</div>
				
				<!--<div class='fl-right'>
					<a href="/check_myob.php"><button type="button" class="blue-btn submitbtnImg">CHECK MYOB</button></a>
				</div>-->
				
				
				
				
				

				

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
		
				<th><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></th>
				<th>Agency</th>
			
				<th>Job #</th>
				
				<th>Email Sent</th>
				
				<th>SMS Sent</th>
				
				<!--<th>In MYOB</th>-->
				
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
								echo (((int)$age)!=0)?$age:0;
								
								$age_tot += (int)$age;
								
								?>
							</td>
							
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
							<td>
								<?php 
								$tot_price = getJobAmountGrandTotal($row['jid'],$_SESSION['country_default']);
								echo '$'.number_format($tot_price,2);
								?>
							</td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							<td><?php echo $row['agency_name']; ?></td>
						
							<td><a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $row['jid']; ?></a></td>							
							
							<td>
							<?php
							if (stristr($row['account_emails'], "@")) {
								if ($row['client_emailed'] != NULL) {
									echo "<span class='green'>Yes</span>";
								} else {
									echo "<span class='red'>No</span>";
								}
							} else {
								echo "N/A";
							}
							?>
							</td>
							<td><?php echo ( date("Y-m-d",strtotime($row['sms_sent_merge']))==date("Y-m-d") )?"<span class='green'>Yes</span>":"<span class='red'>No</span>"; ?></td>
							
							<!--<td><?php echo ( $row['at_myob']==1 )?"<span class='green'>Yes</span>":"<span class='red'>No</span>"; ?></td>-->
							
						</tr>
						
						
				<?php
					$i++;
					}
				}else{ ?>
					<td colspan="13" align="left">Empty</td>
				<?php
				}
				?>
				
				
				<tr>
					<td colspan="2"><strong>TOTAL</strong></td>
					<td style="text-align:left;">
					<?php 
						$total_age = mj_getTotalAge();
						$total_job_count = mj_getAllJobCount();
						echo round($total_age/$total_job_count); 
					?>
					<input type="hidden" id="total_age" value="<?php echo $total_age; ?>" />
					<input type="hidden" id="total_job_count" value="<?php echo $total_job_count; ?>" />
					</td>
					<td>&nbsp;</td>
					<?php
					$final_total = mj_getMergeJobTotalJobPrice()+mj_getMergeJobTotalAlarmPrice()+mj_getMergeJobTotalSubCharge();
					?>
					<td style="text-align:left;">$<?php echo number_format($final_total, 2, '.', '');; ?></td>
					<td colspan="8"></td>
				</tr>
				
		</table>

		<?php

		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>
		
		
		<div style="margin-top: 15px; float: right; display:none;">
			<a href="/export_myob.php?save_to_server=1"><button type="button" class="blue-btn submitbtnImg">Import to MYOB</button></a>
		</div>
		
		

		<div style="margin-top: 15px; float: right; display:none;" id="rebook_div">
			<button type="button" id="btn_create_240v_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create 240v Rebook</button>
			<button type="button" id="btn_create_rebook" class="blue-btn submitbtnImg" onclick="return confirm('Are you sure you want to create a Rebook?')">Create Rebook</button>
			<button type="button" id="btn_move_to_merged" class="submitbtnImg" style="background-color:green">Move to Merged</button>
		</div>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	
	// auto email script
	jQuery("#chk_cron_merged_cert_toggle").change(function(){
		
		var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
		var cron_file = 'merged_email_all_cron';
		var db_field = 'cron_merged_cert';
		
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
				
				window.location.href="/merged_jobs.php?auto_emails_set=1";
				
			});	
		}		
		
	});
	
	
	
	
	
	// send sms script
	jQuery("#btn_sms_tenant").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			window.location="/merged_jobs_sms_send.php";			
			
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
	jQuery("#maps_check_all").click(function(){
		
		if(jQuery(this).prop("checked")==true){
			jQuery(".chkbox").prop("checked",true);
			jQuery("#rebook_div").show();
		}else{
			jQuery(".chkbox").prop("checked",false);
			jQuery("#rebook_div").hide();
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
<?php 
if( isset($start_load_time) ){
	$time_elapsed_secs = microtime(true) - $start_load_time;
	echo "<p style='text-align:center;'>Execution Time: {$time_elapsed_secs}</p>";
}
?>