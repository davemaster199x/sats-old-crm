<?php

$title = "To Be Invoiced";
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

if($_POST['postcode_region_id']){
	$filterregion = implode(",",$_POST['postcode_region_id']);
	//print_r($region2);
}else if($_GET['postcode_region_id']){
	$filterregion = $_GET['postcode_region_id'];
	//echo $filterregion;
}
$date = ($_REQUEST['date']!="")?jFormatDateToBeDbReady($_REQUEST['date']):'';
$is_urgent = ($_REQUEST['is_urgent']!="")?mysql_real_escape_string($_REQUEST['is_urgent']):'';
$job_status = 'To Be Invoiced';


// sort

$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'j.job_type';
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'ASC';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "&sort={$sort}&order_by={$order_by}&job_type=".urlencode($job_type)."&service=".urlencode($service)."&state=".urlencode($state)."&date=".urlencode($date)."&phrase=".urlencode($phrase)."&agency=".urlencode($agency)."&postcode_region_id=".$filterregion;

$page_filter_arr = array(
	'job_type' => urlencode($job_type),
	'service' => urlencode($service),
	'state' => urlencode($state),
	'agency' => urlencode($agency),
	'date' => urlencode($date),
	'phrase' => urlencode($phrase),
);
$page_filter = http_build_query($page_filter_arr);

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

if( $sort=='j.date' ){
	$use_plain_sort = 1;
}


// url parameter
$url_params = http_build_query($_REQUEST);

// paginated list
$custom_select = "
	apd.`api`,
	apd.`api_prop_id`,
	j.`id` AS jid,
	j.`service` AS jservice,
	j.`urgent_job`,
	j.`job_reason_id`,
	j.`date` AS jdate,
	j.`job_type`,
	j.`created` AS jcreated,
	j.job_price,

	DATEDIFF(CURDATE(), Date(j.`created`)) AS age,
	DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline,

	p.`property_id`,
	p.`address_1` AS p_address_1, 
	p.`address_2` AS p_address_2, 
	p.`address_3` AS p_address_3, 
	p.`state` AS p_state,
	p.`postcode` AS p_postcode,
	p.`propertyme_prop_id`,	
	p.`palace_prop_id`,	
	p.`retest_date`,


	a.`agency_id`,
	a.`agency_name`,
	a.`allow_dk`,
	a.`pme_supplier_id`,
	a.`palace_diary_id`
";

$sql_params = array(
	'start' => $start,
	'limit' => $limit,
	'sort' => $sort,
	'order_by' => $order_by,
	'job_type' => $job_type,
	'job_status' => $job_status,
	'service' => $service,
	'state' => $state,
	'date' => $date,
	'phrase' => $phrase,
	'distinct' => $distinct,
	'send_emails' => $send_emails,
	'client_emailed' => $client_emailed,
	'send_combined_invoice' => $send_combined_invoice,
	'agency' => $agency,
	'postcode_region_id' => $postcode_region_id,
	'del_job' => $del_job,
	'from_date' => $from_date,
	'to_date' => $to_date,
	'is_urgent' => $is_urgent,
	'tech_id' => $tech_id,
	'getCOTredhiglightsCount' => $getCOTredhiglightsCount,
	'use_plain_sort' => $use_plain_sort,
	'country_id' => $country_id,
	'created_date' => $created_date,

	'custom_select' => $custom_select,

	'display_query' => 0
);
$plist = $jc->getJobs_v2($sql_params);

// get all rows
$custom_select = "COUNT(j.`id`) AS jcount";
$sql_params = array(
	'job_type' => $job_type,
	'job_status' => $job_status,
	'service' => $service,
	'state' => $state,
	'date' => $date,
	'phrase' => $phrase,
	'distinct' => $distinct,
	'send_emails' => $send_emails,
	'client_emailed' => $client_emailed,
	'send_combined_invoice' => $send_combined_invoice,
	'agency' => $agency,
	'postcode_region_id' => $postcode_region_id,
	'del_job' => $del_job,
	'from_date' => $from_date,
	'to_date' => $to_date,
	'is_urgent' => $is_urgent,
	'tech_id' => $tech_id,
	'getCOTredhiglightsCount' => $getCOTredhiglightsCount,
	'use_plain_sort' => $use_plain_sort,
	'country_id' => $country_id,
	'created_date' => $created_date,
	
	'custom_select' => $custom_select
);
$total_sql = $jc->getJobs_v2($sql_params);
$total_row = mysql_fetch_array($total_sql);
$ptotal = $total_row['jcount'];

function getLastCompletedJob($property_id){
	
	return mysql_query("
		SELECT j.`date` AS jdate, j.`job_type`, j.`assigned_tech`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE p.`property_id` = {$property_id}
		AND j.`status` = 'Completed'
		AND p.`deleted` = 0
		AND j.`del_job` = 0
		AND a.`status` = 'active'
		AND j.`assigned_tech` != 1
		AND j.`assigned_tech` != 2
		ORDER BY j.`date` DESC
		LIMIT 1
	");
	
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
		<li class="other first"><a title="<?php echo $title; ?>" href="/to_be_invoiced_jobs.php"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successful</div>';
		}
		
		if($_GET['create_job_success']==1){
			echo '<div class="success">Create Job Successful</div>';
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
				
					
					
					<div class='fl-left'>
						<label>Date:</label><input type=label name='date' value='<?php echo $_REQUEST['date']; ?>' class='addinput searchstyle datepicker'>		
					</div>
					
					
					<div class='fl-left'><label>Phrase:</label><input type=label name='phrase' value="<?php echo $_REQUEST['phrase']; ?>" class='addinput searchstyle vwjbdtp' style='width: 100px !important;'></div>
					
					<div class='fl-left' style="float:left;">
						<input type='submit' class='submitbtnImg' value='Search' />
					</div>

					<!--
					<div style="float:right; margin-top: 16px;">
						<a href="/jobs_export.php?export=1<?php echo $params; ?>"><button class="submitbtnImg" id="btn_export" type="button">Export</button></a>
					</div>-->

				<!-- duplicated filter here -->
				
				
					<div style="text-align:left; float:left;">
						<ol>
							<li>First create a job if required</li>
							<li>Create invoice. Property will disappear off screen (Job Type will be changed to YM)</li>
						</ol>
					</div>

					  
					  
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
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.date&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&<?php echo $page_filter; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.date')?'active':''; ?>"></div>
					</a>
				</th>
				
			
			
				<th>Job Type</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Age</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=age&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&<?php echo $page_filter; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='age')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Service</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&<?php echo $page_filter; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">Price</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.job_price&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&<?php echo $page_filter; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.job_price')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>Address</th>
		
				<th><?php echo getDynamicStateViaCountry($country_id); ?></th>
				
				<th>Open Job</th>
				
				<th>Agency</th>
			

				
				<th>Last Job</th>
				<th>Last Job Type</th>
				<th>Note</th>
				<th>
					<div class="tbl-tp-name colorwhite bold">Deadline</div>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=deadline&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&<?php echo $page_filter; ?>"> 
						<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='deadline')?'active':''; ?>"></div>
					</a>
				</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">
						<input type="checkbox" id="create_job_check_all" />
						Create Job
					</div>
				</th>
				
				<th>
					<div class="tbl-tp-name colorwhite bold">
						<input type="checkbox" id="invoice_check_all" />
						Invoice
					</div>
				</th>
				
				
			</tr>
				<?php
				
				
				$i= 0;
				$age_val_tot = 0;
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){

						$note_txt = null;
						
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						// if alarms 240v or 240vli are expired
						if( $row['jservice']==2 ){
							$a_sql = mysql_query("
								SELECT *
								FROM `alarm`
								WHERE `job_id` ={$row['jid']}
								AND (
									alarm_power_id = 2 OR
									alarm_power_id = 4
								)
								AND `expiry` <= '".date("Y")."'
							");
							if( mysql_num_rows($a_sql) ){
								$row_color = "style='background-color:#FFCCCB;'";
								
							}
						}
						
						// urgent jobs
						if($row['urgent_job']==1){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// jobs not completed
						if($row['job_reason_id']>0){
							$row_color = "style='background-color:#ffff9d;'";
						}


						// Pme supplier check
						if( $row['api_prop_id'] == '' && $row['api'] == 1 && $row['pme_supplier_id'] != '' ){
							$note_txt .= "Needs PMe Link<br />";
						}

						if( $row['api_prop_id'] != '' && $row['api'] == 1 && $row['pme_supplier_id'] != '' ){
							$note_txt .= "Move to Merge<br />";
						}

						// Palace API check
						if( $row['api_prop_id'] == '' && $row['api'] == 4 && $row['palace_diary_id'] != '' ){
							$note_txt .= "Needs Palace Link<br />";
						}
						
					
				?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							
							
							
							<td>
								<a href="/view_job_details.php?id=<?php echo $row['jid']; ?>">
									<?php echo getJobTypeAbbrv($row['job_type']); ?>
								</a>
							</td>
							
							<td>
							<?php
							// Age
							/*
							$date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
							$date2=date_create(date('Y-m-d'));
							$diff=date_diff($date1,$date2);
							$age = $diff->format("%r%a");
							$age_val = (((int)$age)!=0)?$age:0;
							echo $age_val;
							$age_val_tot += $age_val;
							*/

							echo $row['age'];
							$age_val_tot += $row['age'];
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
							<td><?php echo $row['job_price']; ?></td>
							
							<td><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a></td>
							
							<td><?php echo $row['p_state']; ?></td>
							
							<td>
							<?php							
							// get open job (Any job that is NOT completed / cancelled / deleted)
							$open_job_sql_str = "
							SELECT `id` AS jid
							FROM `jobs`
							WHERE `property_id` = {$row['property_id']}
							AND `status` != 'Completed'
							AND `status` != 'Cancelled'
							AND `del_job` != 1
							AND `id` != {$row['jid']}
							";
							$open_job_sql = mysql_query($open_job_sql_str);

							$open_job_arr = [];
							while( $open_job_row = mysql_fetch_array($open_job_sql) ){ 
								$open_job_arr[] = "<a target='_blank' href='/view_job_details.php?id={$open_job_row['jid']}'>{$open_job_row['jid']}</a>";
							}	

							// display via comma separated
							if( count($open_job_arr) > 0 ){
								echo implode(",",$open_job_arr);								
							}
							?>
							</td>
							
							<td class="agency_td"><?php echo $row['agency_name']; ?></td>					
							
							<?php
							$last_job_sql = getLastCompletedJob($row['property_id']);
							$lj = mysql_fetch_array($last_job_sql);
							$last_job_date = $lj['jdate'];
							$last_job_type = $lj['job_type'];
							?>
							<td><?php echo ( $crm->isDateNotEmpty($last_job_date) )?date('d/m/Y',strtotime($last_job_date)).( ($lj['assigned_tech']==1)?' <strong>(OS)</strong>':null ):''; ?></td>	
							<td><?php echo $last_job_type; ?></td>

							<td><?php echo $note_txt; ?></td>

							<td>
								<?php
								/*
								// get deadline age
								$retest_date_ts = date_create(date('Y-m-d', strtotime($row['retest_date'])));
								$today_ts = date_create(date('Y-m-d'));
								$diff = date_diff($today_ts,$retest_date_ts);
								$age = $diff->format("%r%a");
								$age_val = (((int) $age) != 0) ? $age : 0; 
								
								echo ( $age_val >= 0 )?$age_val:"<span class='colorItRed'>{$age_val}</span>";	
								*/
																
								echo ( $row['deadline'] >= 0 )?$row['deadline']:"<span class='colorItRed'>{$row['deadline']}</span>";
								?>
							</td>
							
							<td>
								<!--<input type="checkbox" class="create_job_chk" value="<?php echo $row['jid']; ?>" <?php echo ($lj['assigned_tech']==1)?'style="display:none;"':null; ?> />-->
								<input type="checkbox" class="create_job_chk" value="<?php echo $row['jid']; ?>" />
							</td>
							
							<td>
								<input type="checkbox" class="invoice_chk_box" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="hid_job_id" value="<?php echo $row['jid']; ?>" />
								<input type="hidden" class="property_id" value="<?php echo $row['property_id']; ?>" />
								<input type="hidden" class="ajt_id" value="<?php echo $row['jservice']; ?>" />
								<input type="hidden" class="is_dk_allowed" value="<?php echo $row['allow_dk']; ?>" />
								<input type="hidden" class="agency_id" value="<?php echo $row['agency_id']; ?>" />
							</td>
							
							
						</tr>
						
				<?php
					$i++;
					}
					?>
					<tr class="body_tr jalign_left">
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

		<!--
		<div style="margin-top: 15px; float: right; display:none;" id="invoice_btn_div">
			Tech:
			<select id="maps_tech">
				<option value="">-- select --</option>
				<?php
				$tech_sql = mysql_query("
					SELECT *
					FROM `staff_accounts`
					WHERE `active` = 1
					AND ( 
						`FirstName` != '' AND `LastName` != ''
					)
					ORDER BY `FirstName`, `LastName`
				");
				while($tech = mysql_fetch_array($tech_sql)){ ?>
				
					<option value="<?php echo $tech['StaffID']; ?>"><?php echo "{$tech['FirstName']} {$tech['LastName']}"; ?></option>
				
				<?php	
				}
				?>
			</select>
			Date:
			<input type="text" id="maps_date" class="datepicker" />
			<button type="button" id="btn_assign" class="blue-btn submitbtnImg">Assign</button>
			<button type="button" id="btn_assign_dk" class="submitbtnImg">Assign Door Knock</button>
			<button style=" float: right; margin-left: 5px;" class="submitbtnImg" id="btn_create_rebook" type="button">Rebook</button>
			
		</div>-->
		
		<div style="margin-top: 15px; float: right; display:none;" id="invoice_btn_div">
			<button type="button" id="btn_do_invoice" class="submitbtnImg">Move to Merged</button>
		</div>
		
		<div style="margin-top: 15px; float: right; display:none;" id="create_job_btn_div">
			<button type="button" id="btn_create_job" class="submitbtnImg">Create Job</button>
		</div>
		
	</div>
</div>




<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// hide sucess message in 10 seconds
	setTimeout(function(){ 
		jQuery(".success").fadeOut();
	}, 10000);
	
	
	// invoice script
	jQuery("#btn_do_invoice").click(function(){
		
		if(confirm("Are you sure you want to continue?")==true){
			
			var job_id = new Array();
			jQuery(".invoice_chk_box:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			//console.log(job_id);
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_do_invoice.php",
				data: { 
					job_id: job_id
				}
			}).done(function( ret ){				
				window.location="to_be_invoiced_jobs.php";
			});				
			
		}
		
	});
	
	
	
	// invoice script
	jQuery("#btn_create_job").click(function(){
		
		if(confirm("Are you sure you want to create job?")==true){
			
			//var job_id = new Array();
			var item_count = jQuery(".create_job_chk:checked").length;
			var i = 0;
			
			
			jQuery(".create_job_chk:checked").each(function(){
				
				var job_id = jQuery(this).val();
				var property_id = jQuery(this).parents("tr:first").find(".property_id").val();
				var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
				var ajt_id = jQuery(this).parents("tr:first").find(".ajt_id").val();
				
				// call ajax		
				jQuery.ajax({
					type: "POST",
					url: "ajax_create_job.php",
					data: {
						property_id: property_id,
						alarm_job_type_id: ajt_id,
						job_type: 'Annual Visit',
						price: 0,
						staff_id: <?php echo $_SESSION['USER_DETAILS']['StaffID']; ?>,
						agency_id: agency_id
					}
				}).done(function(ret){
					i++;
					if( i == item_count ){
						window.location="to_be_invoiced_jobs.php?create_job_success=1";
					} 
				});	
				
			});
			
			
			
			
			
						
			
		}
		
	});
	
	
	
	// invoice check all toggle
	jQuery("#invoice_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".invoice_chk_box:visible").prop("checked",true);
		jQuery(".invoice_chk_box:visible").parents("tr").addClass("yello_mark");
		jQuery("#invoice_btn_div").show();
	  }else{
		jQuery(".invoice_chk_box:visible").prop("checked",false);
		jQuery(".invoice_chk_box:visible").parents("tr").removeClass("yello_mark");
		jQuery("#invoice_btn_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".invoice_chk_box").click(function(){

	  var chked = jQuery(".invoice_chk_box:checked").length;
	  
	  if(jQuery(this).prop("checked")==true){
		jQuery(this).parents("tr:first").addClass("yello_mark");
	  }else{
		jQuery(this).parents("tr:first").removeClass("yello_mark");
	  }
	 
	  
	  if(chked>0){		
		jQuery("#invoice_btn_div").show();
	  }else{
		
		jQuery("#invoice_btn_div").hide();
	  }

	});
	
	
	
	
	// create job check all toggle
	jQuery("#create_job_check_all").click(function(){
		
	 
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".create_job_chk:visible").prop("checked",true);
		jQuery(".create_job_chk:visible").parents("tr").addClass("yello_mark");
		//jQuery("#create_job_btn_div").show();
	  }else{
		jQuery(".create_job_chk:visible").prop("checked",false);
		jQuery(".create_job_chk:visible").parents("tr").removeClass("yello_mark");
		//jQuery("#create_job_btn_div").hide();
	  }
	  
	   var chked = jQuery(".create_job_chk:checked").length;	
	  
	  if(chked>0){		
		jQuery("#create_job_btn_div").show();
	  }else{
		
		jQuery("#create_job_btn_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".create_job_chk").click(function(){

	  var chked = jQuery(".create_job_chk:checked").length;
	  
	  if(jQuery(this).prop("checked")==true){
		jQuery(this).parents("tr:first").addClass("yello_mark");
	  }else{
		jQuery(this).parents("tr:first").removeClass("yello_mark");
	  }
	 
	  
	  if(chked>0){		
		jQuery("#create_job_btn_div").show();
	  }else{
		
		jQuery("#create_job_btn_div").hide();
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
	
	
	
	
	
});
</script>
</body>
</html>