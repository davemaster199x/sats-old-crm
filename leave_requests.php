<?php

$title = "Leave Summary";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$search_staff = mysql_real_escape_string($_REQUEST['search_staff']);
$search_phrase = mysql_real_escape_string($_REQUEST['search_phrase']);
$country_id = $_SESSION['country_default'];

$employee = mysql_real_escape_string($_REQUEST['employee']);
$line_manager = mysql_real_escape_string($_REQUEST['line_manager']);
$status = ( $_REQUEST['status'] != '' )?mysql_real_escape_string($_REQUEST['status']):'Pending';

// pagination
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 25;

$this_page = $_SERVER['PHP_SELF'];
$params = "";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;


$jparams = array(
	'emp_id' => $employee,
	'lm_id' => $line_manager,
	'l_status' => $status,
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'sort_list' => array(
		'order_by' => 'l.`date`',
		'sort' => 'DESC'
	),
	'country_id' => $country_id
);
$tools_sql = $crm->getLeave($jparams);
$jparams = array(
	'emp_id' => $employee,
	'lm_id' => $line_manager,
	'l_status' => $status,
	'country_id' => $country_id
);
$ptotal = mysql_num_rows($crm->getLeave($jparams));

?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.approvedHL{
	color:green; 
}
.pendingHL{
	color:red; 
	font-style: italic;
}
.approvedHLstatus{
	color:green; 
	font-weight:bold;
}
.deniedHLstatus{
	color:red; 
	font-weight:bold;
}
.pendingHLstatus{
	color:red; 
	font-style: italic;
}

.timestampHeading{
	width: 100px;
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
		if($_GET['success']==1){ ?>
			<div class="success">New Tools Added</div>
		<?php
		}
		?>
		
		 <?php
		if($_GET['add_success']==1){ ?>
			<div class="success">New vehicle added</div>
		<?php
		}
		?>
		
		
		<div class="aviw_drop-h" style="border: 1px solid #ccc;">
		
			<form id="form_search" method="post" action="leave_requests.php">			
				
				
				<?php	
						
				// list
				$list_params = array(
					'l_status' => $status,
					'distinct_sql' => 'sa_emp.`StaffID`, sa_emp.`FirstName`, sa_emp.`LastName`',
					'country_id' => $country_id,
					'sort_list' => array(
						'order_by' => 'sa_emp.`FirstName`',
						'sort' => 'ASC'
					)
				);
				$emp_sql = $crm->getLeave($list_params);					
				?>
				<div class="fl-left">
					<label style="margin-right: 9px;">Name:</label>
					<select name="employee">
						<option value="">--- Select ---</option>
						<?php
						while( $emp = mysql_fetch_array($emp_sql) ){ ?>
							<option value="<?php echo $emp['StaffID']; ?>" <?php echo ($emp['StaffID']==$employee)?'selected="selected"':''; ?>><?php echo "{$emp['FirstName']} {$emp['LastName']}"; ?></option>
						<?php
						}
						?>
					</select>
				</div>
				
				
				<?php	
						
				// list
				$list_params = array(
					'l_status' => $status,
					'distinct_sql' => 'sa_lm.`StaffID`, sa_lm.`FirstName`, sa_lm.`LastName`',
					'country_id' => $country_id,
					'sort_list' => array(
						'order_by' => 'sa_lm.`FirstName`',
						'sort' => 'ASC'
					)
				);
				$lm_sql = $crm->getLeave($list_params);					
				?>
				<div class="fl-left">
					<label style="margin-right: 9px;">Line Manager:</label>
					<select name="line_manager">
						<option value="">--- Select ---</option>
						<?php
						while( $lm = mysql_fetch_array($lm_sql) ){ 
							
							?>
								<option value="<?php echo $lm['StaffID']; ?>" <?php echo ($lm['StaffID']==$line_manager)?'selected="selected"':''; ?>><?php echo "{$lm['FirstName']} {$lm['LastName']}"; ?></option>
							<?php
							
						}
						?>
					</select>
				</div>
				
				
				<div class="fl-left">
					<label style="margin-right: 9px;">Status:</label>
					<select name="status" class="status">
						<option value="Pending" <?php echo ($status=='Pending')?'selected="selected"':''; ?>>Pending</option>
						<option value="Approved" <?php echo ($status=='Approved')?'selected="selected"':''; ?>>Approved</option>
						<option value="Denied" <?php echo ($status=='Denied')?'selected="selected"':''; ?>>Declined</option>
					</select>
				</div>
				
				<div class="fl-left" style="float:left; margin-left: 10px;">
					<button type="submit" name="btn_submit" class="submitbtnImg">
						<img class="inner_icon" src="images/search.png">
						Go 
					</button>
				</div>	
			</form>
		
		</div>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Date of Request</th>
				<th>Employee</th>
				<th>Line Manager</th>
				<th>First Day of Leave</th>
				<th>Last Day of Leave</th>
				<th>Reason</th>
				<th class="timestampHeading">HR Approved</th>
				<th class="timestampHeading">Line Manager Approved</th>				
				<th class="timestampHeading">Added to Calendar</th>
				<th class="timestampHeading">Staff notified in writing</th>
				<th>Status</th>
				<th>PDF</th>
				<th>Delete</th>
				<!--<th>Edit</th>-->
			</tr>
			<?php				
			if( mysql_num_rows($tools_sql)>0 ){
				while($t = mysql_fetch_array($tools_sql)){ ?>
					<tr class="body_tr jalign_left">
						<td>
							<span class="txt_lbl"><a href="/leave_details.php?id=<?php echo $t['leave_id']; ?>"><?php echo date('d/m/Y',strtotime($t['date'])); ?></a></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $crm->formatStaffName($t['emp_fname'],$t['emp_lname']); ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $crm->formatStaffName($t['lm_fname'],$t['lm_lname']); ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo date('d/m/Y',strtotime($t['lday_of_work'])); ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo date('d/m/Y',strtotime($t['fday_back'])); ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $t['reason_for_leave']; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<?php 
							$hlclass = '';
							$timestamp_str = '';
							if( is_numeric($t['hr_app']) && $t['hr_app']==1 ){
								$hlclass = 'approvedHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['hr_app_timestamp']));
							}else if( is_numeric($t['hr_app']) && $t['hr_app']==0 ){
								$hlclass = 'pendingHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['hr_app_timestamp']));
							}
							?>
							<span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<?php 
							$hlclass = '';
							$timestamp_str = '';
							if( is_numeric($t['line_manager_app']) && $t['line_manager_app']==1 ){
								$hlclass = 'approvedHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['line_manager_app_timestamp']));
							}else if( is_numeric($t['line_manager_app']) && $t['line_manager_app']==0 ){
								$hlclass = 'pendingHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['line_manager_app_timestamp']));
							}
							?>
							<span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>						
						<td>
							<?php 
							$hlclass = '';
							$timestamp_str = '';
							if( is_numeric($t['added_to_cal']) && $t['added_to_cal']==1 ){
								$hlclass = 'approvedHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['added_to_cal_timestamp']));
							}else if( is_numeric($t['added_to_cal']) && $t['added_to_cal']==0 ){
								$hlclass = 'pendingHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['added_to_cal_timestamp']));
							}
							?>
							<span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<?php 
							$hlclass = '';
							$timestamp_str = '';
							if( is_numeric($t['staff_notified']) && $t['staff_notified']==1 ){
								$hlclass = 'approvedHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['staff_notified_timestamp']));
							}else if( is_numeric($t['staff_notified']) && $t['staff_notified']==0 ){
								$hlclass = 'pendingHL';
								$timestamp_str = date('d/m/Y H:i',strtotime($t['staff_notified_timestamp']));
							}
							?>
							<span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<?php
							switch($t['status']){
								case 'Approved':
									$hl_class = 'approvedHLstatus';
									$status_text = $t['status'];
								break;
								case 'Pending':
									$hl_class = 'pendingHLstatus';
									$status_text = $t['status'];
								break;
								case 'Denied':
									$hl_class = 'deniedHLstatus';
									$status_text = strtoupper($t['status']);
								break;
							}
							?>
							<span class="txt_lbl <?php echo $hl_class; ?>" ><?php echo $status_text; ?></span>
							<input type="text" name="description" class="txt_hid description" value="" />
						</td>
						<td>
							<a href="/leave_details_pdf.php?id=<?php echo $t['leave_id']; ?>">
								<img src="images/pdf.png">
							</a>
						</td>
						<td>
							<input type="hidden" class="leave_id" value="<?php echo $t['leave_id']; ?>" />
							<a href="/delete_leave.php?id=<?php echo $t['leave_id']; ?>" class="link_delete">Delete</a>
						</td>
						<!--
						<td>			
							<button class="blue-btn submitbtnImg btn_update">Update</button>
							<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
							<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
						</td>	
						-->
					</tr>
				<?php
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
	
     <div class="row" style="display:block; padding-top: 20px; clear: both;">
        <a href="/leave_form.php"><button style="float: left;" type="button" id="btn_add_vehicle" class="submitbtnImg">Add Leave Request</button></a>
     </div>
	
	</div>
	
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(e){
	
	
	jQuery(".link_delete").click(function(e){
	
		var leave_id = jQuery(this).parents("tr:first").find(".leave_id").val();
		e.preventDefault();
	
		if( confirm("Are you sure you want to delete?") ){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_leave.php",
				data: { 
					leave_id: leave_id,
				}
			}).done(function( ret ){
				window.location = "/leave_requests.php";
			});	
			
		}
	});
	

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
		var make = jQuery(this).parents("tr:first").find(".make").val();
		var model = jQuery(this).parents("tr:first").find(".model").val();
		var plant_id = jQuery(this).parents("tr:first").find(".plant_id").val();
		var number_plate = jQuery(this).parents("tr:first").find(".number_plate").val();
		var staff_id = jQuery(this).parents("tr:first").find(".staff_id").val();
		var tech_vehicle = jQuery(this).parents("tr:first").find(".tech_vehicle").val();		
		var kms = jQuery(this).parents("tr:first").find(".kms").val();
		var next_service = jQuery(this).parents("tr:first").find(".next_service").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();		
		var error = "";
		
		if(number_plate==""){
			error += "Number Plate is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "test_ajax_update_tools.php",
				data: { 
					vehicles_id: vehicles_id,
					make: make,
					model: model,
					plant_id: plant_id,
					number_plate: number_plate,
					staff_id: staff_id,
					kms: kms,
					next_service: next_service,
					tech_vehicle: tech_vehicle,
					active: active
				}
			}).done(function( ret ) {
				//window.location="/view_vehicles.php?success=1";
			});				
			
		}		
		
	});
	
	
	
	

});
</script>
</body>
</html>
