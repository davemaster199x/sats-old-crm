<?php

$title = "Vehicle Details";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$vehicle_id = $_GET['id'];

$crm = new Sats_Crm_Class;

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
.vvd-holder .vvd-row.left-flow {
    width: 57%;
}



.c-tab table td, .c-tab table th{
	width: auto;
	padding: 0px;
	margin: 0px;
}



.c-tab__content .table tr{
	border:none !important;
}

.c-tabs td{
	text-align: left;
}
.c-tabs label{
	float: left !important; 
	margin-top: 8px !important; 
}
.c-tabs .addinput{
	margin: 0;
}
h5{
	text-align: left;
}
.c-tabs .div_col{
	/*width: 50%;*/
    float: left;
}
    
.tools_table td, .tools_table th{
	border: 0.5px solid #cccccc !important;
    text-align: left !important;
	padding: 10px !important;
}

.c-tab .addinput{
	margin-bottom: 10px;
	margin-right: 5px;
}

#frm_vehicle_log td{
	padding:4px !important;
}

.c-tab .table{
	width:auto;
}

.c-tab{
	font-size: 13px;
}

.c-tab h5{
	font-size: 13.28px;
}

.jdateWidth{
	width: 77px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Vehicles" href="/view_vehicles.php">View Vehicles</a></li>
        <li class="other second"><a title="Vehicle Details" href="/view_vehicle_details.php?id=<?php echo $_GET['id']; ?>"><strong>Vehicle Details</strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success">New Vehicle Added</div>
	<?php
	}else if($_GET['success']==2){ ?>
		<div class="success">Update Successful</div>
	<?php
	}else if($_GET['success']==3){ ?>
		<div class="success">Upload Successful</div>
	<?php
	}
	?>
	
	<?php
	if($_GET['error']!=""){ 
		echo $_GET['error'];
	}
	?>
    
	<?php
	$v_sql = mysql_query("
		SELECT *
		FROM `vehicles`
		WHERE `vehicles_id` ={$vehicle_id}
	");
	$v = mysql_fetch_array($v_sql);
	?>
	
    
	<div id="tabs" class="c-tabs no-js">
	
		<div class="c-tabs-nav">
					
			<a href="#" data-tab_index="0" data-tab_name="vehicle" class="c-tabs-nav__link is-active">Vehicle Details</a>
			<a href="#" data-tab_index="1" data-tab_name="insurance" class="c-tabs-nav__link">Insurance/Rego/Finance</a>
			<a href="#" data-tab_index="2" data-tab_name="tools" class="c-tabs-nav__link">Tools</a>
			<a href="#" data-tab_index="3" data-tab_name="servicing" class="c-tabs-nav__link">Servicing</a>
			<a href="#" data-tab_index="4" data-tab_name="vehicle_files" class="c-tabs-nav__link">Vehicle Files</a>
			
		</div>
		
		
		<form action="update_vehicle_details.php" method="post" id="frm_vehicle" enctype="multipart/form-data">
		
			<div class="c-tab is-active" data-tab_cont_name="vehicle">
				<div class="c-tab__content vehicle_details_tab_cont">
						
					<div class="div_col" style="margin-right:30px;">
							
						<!-- Vehicle -->
						<h5>Vehicle Details</h5>
						<table class="table">
							<tr>
								<td>Plant ID</td>
								<td>Make</td>
								<td>Model</td>
							</tr>
							<tr>
								<td>
									<input type="text"  class="addinput" name="plant_id" id="plant_id" value="<?php echo $v['plant_id']; ?>" />
								</td>
								<td><input type="text"  class="addinput" name="make" id="make" value="<?php echo $v['make']; ?>"></td>
								<td><input type="text"  class="addinput" name="model" id="model" value="<?php echo $v['model']; ?>"></td>
							</tr>
							<tr>
								<td>Year</td>
								<td>VIN Number</td>							
								<td>Engine Number</td>
							</tr>
							<tr>								
								<td>
									<select name="year" id="year" class="addinput">
										<option value="">----</option>
										<?php
										$year =  range (2035,2005);
										foreach($year as $val){ ?>
										<option value="<?php echo $val; ?>" <?php echo ($v['year']==$val)?'selected="selected"':''; ?>><?php echo $val; ?></option>
										<?php
										}
										?>
									</select>
								</td>
								<td>
									<input type="text"  class="addinput" name="vin_num" id="vin_num" value="<?php echo $v['vin_num']; ?>" />
								</td>							
								<td>
									<input type="text"  class="addinput" name="engine_number" id="engine_number" value="<?php echo $v['engine_number']; ?>" />
								</td>
							</tr>
						</table>
						
						<!-- Purchase Details -->
						<h5>Purchase Details</h5>
						<table class="table">
							<tr>
								<td>Purchase Date</td>
								<td>Purchase Price</td>
								<td>Warranty Expires</td>
							</tr>
							<tr>
								<td>
									<input type="text"  class="addinput datepicker" name="purchase_date" id="purchase_date" style="width:80px;" value="<?php echo ($v['purchase_date']!="0000-00-00"&&$v['purchase_date']!="")?date("d/m/Y",strtotime($v['purchase_date'])):""; ?>">
								</td>
								<td>
									<input type="text" id="purchase_price" name="purchase_price" class="addinput" style="width:80px;" value="<?php echo $v['purchase_price']; ?>">
								</td>
								<td>
									<input type="text"  class="addinput datepicker" name="warranty_expires" id="warranty_expires" style="width:80px;" value="<?php echo ($v['warranty_expires']!="0000-00-00"&&$v['warranty_expires']!="")?date("d/m/Y",strtotime($v['warranty_expires'])):""; ?>">
								</td>							
							</tr>
						</table>

						<!-- Fuel -->
						<h5>Fuel</h5>
						<table class="table">
							<tr>
								<td>Fuel Type</td>
								<td>Fuel Card Number</td>
								<td>Fuel Card Pin</td>
							</tr>
							<tr>
								<td>
									<select name="fuel_type" id="fuel_type" class="addinput">
										<option value="">----</option>
										<option value="Unleaded" <?php echo ($v['fuel_type']=="Unleaded")?'selected="selected"':''; ?>>Unleaded</option>	
										<option value="Premium" <?php echo ($v['fuel_type']=="Premium")?'selected="selected"':''; ?>>Premium</option>
										<option value="Diesel" <?php echo ($v['fuel_type']=="Diesel")?'selected="selected"':''; ?>>Diesel</option>
										<option value="LPG" <?php echo ($v['fuel_type']=="LPG")?'selected="selected"':''; ?>>LPG</option>
									</select>
								</td>		
								<td><input type="text"  class="addinput" name="fuel_card_num" id="fuel_card_num" value="<?php echo $v['fuel_card_num']; ?>" style="width: 140px;"></td>
								<td><input type="text"  class="addinput" name="fuel_card_pin" id="fuel_card_pin" value="<?php echo $v['fuel_card_pin']; ?>" style="width: 90px;"></td>
							</tr>
						</table>

						<div style="float:left;">
							<!-- eTag -->
							<h5>Toll Pass</h5>
							<table class="table">
								<tr>
									<td>eTag Number</td>
								</tr>
								<tr>
									<td>
										<input type="text"  class="addinput" name="etag_num" id="etag_num" value="<?php echo $v['etag_num']; ?>">
									</td>									
								</tr>
							</table>
						</div>
						
						<div style="float:left;">
							<!-- Driver -->						
							<h5>Driver</h5>
							<table class="table">
								<tr>
									<td>Driver Name</td>
								</tr>
								<tr>
									<td>
										<?php
											$sql = mysql_query("
												SELECT *
												FROM `staff_accounts`
												WHERE `active` =1
												AND `Deleted` =0
												ORDER BY `FirstName` ASC, `LastName` ASC
											");
										?>
										<select name="staff_id" id="staff_id" class="addinput">
										<option value="">----</option>
										<?php 
										while($row=mysql_fetch_array($sql)){ ?>
											<option value="<?php echo $row['StaffID']; ?>" <?php echo ($v['StaffID']==$row['StaffID'])?'selected="selected"':''; ?>><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
										<?php
										}
										?>
										</select>
										<input type="hidden" name="og_driver" value="<?php echo $v['StaffID']; ?>">
									</td>												
								</tr>
								<tr>
									<td colspan="100%" align="right">
										<button class="submitbtnImg btn_update_vehicle" id="btn_update_vehicle" type="button" style="float: right; margin-right: 21px;">
											<img class="inner_icon" src="images/save-button.png">
											Update Details
										</button>
									</td>
								</tr>
							</table>
						</div>

						<div style="float:left;">
							<!-- eTag -->
							<h5>Ownership</h5>
							<table class="table">
								<tr>
									<td>Usage</td>
								</tr>
								<tr>
									<td>
										<select name="vehicle_ownership" id="vehicle_ownership" class="addinput vehicle_ownership">
											<option value="">----</option>
											<option value="1" <?php echo ($v['vehicle_ownership']==1)?'selected="selected"':''; ?>>Company</option>
											<option value="2" <?php echo ($v['vehicle_ownership']==2)?'selected="selected"':''; ?>>Personal</option>
										</select>
									</td>									
								</tr>
							</table>
						</div>
											
						<div style="clear:both;"></div>		
						
					
					</div>	
					
					<div style="float: left;">					
							
						<!-- Image -->
						<h5>Vehicle Image</h5>
						<table class="table">
							<tr>
								<td>
										<?php 
								if($v['image']!=''){ ?>
									<img src="/images/vehicle/<?php echo $v['image']; ?>" />
									<br />
								<?php	
								} 
								?>					
								<input type="file" class="addinput" name="vehicle_image" style="width: 200px;" />
								</td>								
							</tr>
						</table>						
						
						<!-- PDF -->
						<h5>Export Vehicle Details</h5>
						<table class="table">
							<tr>
								<td>
									<a target="__blank" href="vehicle_details_pdf.php?vehicle_id=<?php echo $vehicle_id; ?>">
										<img src="images/pdf.png" />
									</a>
								</td>
								<td>
									<input type="checkbox" name="serviced_booked" value="1" style="margin-left: 40px;" <?php echo ( $v['serviced_booked']==1 )?'checked="checked"':'' ?> />
								</td>
								<td>Service Booked</td>								
							</tr>
						</table>						
						
						<?php
						// KMS
						$kms_sql = mysql_query("
							SELECT *
							FROM `kms`
							WHERE `vehicles_id` = {$v['vehicles_id']}
							ORDER BY `kms_updated` DESC
							LIMIT 0, 1
						");
						$kms = mysql_fetch_array($kms_sql);
						?>		
						<h5>Kilometres</h5>
						<table class="table">		
							<tr>
								<td>Kms</td>
								<td>Kms Updated</td>
								<td>Next Service</td>
							</tr>
							<tr>			
								<td>
									<input type="text"  class="addinput" name="kms" id="kms" value="<?php echo $kms['kms']; ?>" style="float:left; width:80px;">
									<input type="hidden"  name="orig_kms" id="orig_kms" value="<?php echo $kms['kms']; ?>">
								</td>
								<td>
									<input type="text"  class="addinput" name="kmsupdate" id="kmsupdate" readonly="readonly" value="<?php echo ($kms['kms_updated']!="0000-00-00 00:00:00"&&$kms['kms_updated']!="")?date('d/m/Y',strtotime($kms['kms_updated'])):""; ?>" style="float:left; width:80px;" />
								</td>
								<td>
									<input type="text"  class="addinput" name="next_service" id="next_service" value="<?php echo $v['next_service']; ?>" style="float:left; width:80px;">
								</td>
							</tr>	
						</table>
		
					</div>						
						
					<div style="clear:both;">&nbsp;</div>
					
				
			
				</div>
			</div>
			
			<!-- Insurance/Rego/Finance -->
			<div class="c-tab" data-tab_cont_name="insurance">
				<div class="c-tab__content">
						
				  
						<div class="row">				
							<div class="div_col" style="margin-right:30px;">
							
								<!-- Insurance -->
								<h5>Insurance</h5>
								<table class="table">
									<tr>
										<td>Policy Number</td>
										<td>Insurer</td>
										<td>Policy Expires</td>
									</tr>
									<tr>
										<td>
											<input type="text"  class="addinput" name="ins_pol_num" id="ins_pol_num" value="<?php echo $v['ins_pol_num']; ?>">
										</td>
										<td>
											<input type="text"  class="addinput" name="insurer" id="insurer" value="<?php echo $v['insurer']; ?>">
										</td>
										<td>
											 <input type="text"  class="addinput jdateWidth datepicker" name="policy_expires" id="policy_expires" value="<?php echo ($v['policy_expires']!="0000-00-00 00:00:00"&&$v['policy_expires']!="")?date("d/m/Y",strtotime($v['policy_expires'])):""; ?>">
										</td>						
									</tr>
								</table>
								
								<!-- Registration -->
								<h5>Registration</h5>
								<table class="table">
									<tr>
										<td>Number Plate</td>
										<td>Cust. Rego #</td>
										<td>Rego Expires</td>										
										<td>Key Number</td>
									</tr>
									<tr>
										<td>
											<input type="text"  class="addinput jdateWidth" name="number_plate" id="number_plate" value="<?php echo $v['number_plate']; ?>">
										</td>
										<td>
											<input type="text"  class="addinput" name="cust_reg_num" id="cust_reg_num" value="<?php echo $v['cust_reg_num']; ?>" />
										</td>
										<td>
											 <input type="text"  class="addinput jdateWidth datepicker" name="rego_expires" id="rego_expires" value="<?php echo ($v['rego_expires']!="0000-00-00 00:00:00")?date("d/m/Y",strtotime($v['rego_expires'])):""; ?>">
										</td>											
										<td>
											<input type="text"  class="addinput jdateWidth" name="key_number" id="key_number" value="<?php echo $v['key_number']; ?>" />
										</td>
									</tr>									
								</table>
								
								
								<!-- Insurance -->
								<h5>Finance</h5>
								<table class="table">
									<tr>
										<td>Bank</td>
										<td>Loan Number</td>
										<td>Term (Months)</td>
										<td>Monthly $</td>
										<td>Start Date</td>
										<td>End Date</td>
									</tr>
									<tr>
										<td>
											<input type="text"  class="addinput jdateWidth" name="finance_bank" id="finance_bank" value="<?php echo $v['finance_bank']; ?>" />
										</td>
										<td>
											<input type="text"  class="addinput" name="finance_loan_num" id="finance_loan_num" value="<?php echo $v['finance_loan_num']; ?>" />
										</td>	
										<td>
											<input type="text"  class="addinput jdateWidth" name="finance_loan_terms" id="finance_loan_terms" value="<?php echo $v['finance_loan_terms']; ?>" />
										</td>
										<td>
											<input type="text"  class="addinput jdateWidth" name="finance_monthly_repayments" id="finance_monthly_repayments" value="<?php echo $v['finance_monthly_repayments']; ?>" />
										</td>
										<td>
											 <input type="text" class="addinput jdateWidth datepicker" name="finance_start_date" id="finance_start_date" value="<?php echo ( $v['finance_start_date']!='' && $v['finance_start_date']!='0000-00-00' )?date('d/m/Y',strtotime($v['finance_start_date'])):''; ?>" />
										</td>
										<td>
											 <input type="text" class="addinput jdateWidth datepicker" name="finance_end_date" id="finance_end_date" value="<?php echo ( $v['finance_end_date']!='' && $v['finance_end_date']!='0000-00-00' )?date('d/m/Y',strtotime($v['finance_end_date'])):''; ?>" />
										</td>
									</tr>
									<tr>
										<td colspan="100%" align="right">
											<button class="submitbtnImg btn_update_vehicle" id="btn_update_vehicle" type="button" style="float: right; margin-right: 4px;">
												<img class="inner_icon" src="images/save-button.png">
												Update Details
											</button>
										</td>
									</tr>
								</table>
								
							
							</div>
						</div>
						
						<div style="clear:both;">&nbsp;</div>

				</div>
			</div>
					
			<div class="c-tab" data-tab_cont_name="tools">
				<div class="c-tab__content">
					
					<table class="table tools_table" style="width:auto;">		
						<tr>
							<th>Item ID</th>
							<th>Brand</th>
							<th>Description</th>
						</tr>
						<?php 
						// tools
						$tools_sql = $crm->getVehicleTools($vehicle_id);
						while( $tool = mysql_fetch_array($tools_sql) ){ ?>
						<tr>			
							<td><?php echo $tool['item_id']; ?></td>
							<td><?php echo $tool['brand']; ?></td>
							<td><?php echo $tool['description']; ?></td>
						</tr>
						<?php	
						}
						?>
					</table>
					
		
				</div>
			</div>
			
			<input type="hidden" name="vehicles_id" value="<?php echo $v['vehicles_id']; ?>">
		
		</form>
		
		<!-- SERVICING -->
		<div class="c-tab" data-tab_cont_name="servicing">
			<div class="c-tab__content">
			
				<form action="add_vehicle_log.php" id="frm_vehicle_log" name="frm_vehicle_log" method="post">
					<table class="table vehicle_log_tbl" style="width:100%;">
						<tbody>
							<tr class="padding-none border-none" bgcolor="#ECECEC" style="border: 1px solid #CCCCCC !important;">
								<td style="width: 100px;">
									<label class="vpr-adev" for="eventdate">Date</label>
									<input type="text" class="addinput vpr-adev-in datepicker" id="log_date" name="log_date" style="width:80px;" value="<?php echo date("d/m/Y"); ?>" />
								</td>
								<td style="width: 121px;">
									<label class="vpr-adev" style="padding-left: 13px;">Price</label>
									<div style="clear:both;"></div>
									<div>
										<div style="float:left; margin-right: 5px; margin-top: 7px;">$</div>
										<input class="addinput vpr-adev-txt" id="log_price" name="log_price" style="float:left; width:80px;" />
										<div style="clear:both;"></div>
									</div>
								</td>
								<td>
									<label class="vpr-adev">Details</label>
									<input class="addinput vpr-adev-txt" id="log_details" name="log_details" style="width: 95%;" />
								</td>
								
								<td style="width: 79px;">
									<input type="hidden" value="Add Event" name="add_event">
									<button class="submitbtnImg vpr-adev-btn" id="btn_vehiclelog" type="button">
										<img class="inner_icon" src="images/add-button.png">
										Log
									</button>
									<input type="hidden" name="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />
								</td>
							</tr>
						</tbody>
					</table>
					
					<table cellpadding="5" border="0" class="vvd-odd-cl all-table tbl-fr-red view-property-table-inner">
						<tbody>
							<tr bgcolor="#b4151b" class="redrow-v padding-none" style="border: 1px solid #b4151b !important;">
								<td class="colorwhite bold">Date</td>
								<td class="colorwhite bold">Who</td>
								<td class="colorwhite bold">Details</td>
								<td class="colorwhite bold">Price</td>
								<td class="colorwhite bold">Delete</td>
							</tr>
							<?php
							$logs_sql = mysql_query("
								SELECT *
								FROM `vehicles_log` AS vl
								LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = vl.`staff_id`
								WHERE vl.`vehicles_id` = {$v['vehicles_id']}
								ORDER BY `date` DESC
							");
							
							
							
							if(mysql_num_rows($logs_sql)>0){ 
								while($logs=mysql_fetch_array($logs_sql)){ ?>
								
								
									<tr>
										<td><?php echo date("d/m/Y",strtotime($logs['date'])) ?></td>
										<td><?php echo $logs['FirstName'].' '.$logs['LastName']; ?></td>
										<td><?php echo $logs['details'] ?></td>
										<td>$<?php echo $logs['price'] ?></td>
										<td>
											<a class="btn_del_logs" href="javascript:void(0);">Delete</a>
											<input type="hidden" class="vehicles_log_id" value="<?php echo $logs['vehicles_log_id'] ?>" />
										</td>
									</tr>
								<?php
								}
							}else{ ?>
								<tr><td colspan="5" style="text-align:left">No Entries</td></tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</form>
				
			</div>
		</div>
		
		
		
		
		<div class="c-tab" data-tab_cont_name="vehicle_files">
			<div class="c-tab__content">					
			  
				<form id="form_resources" method="post" action="/upload_vehicle_files.php" enctype="multipart/form-data">					
					<div style="text-align:left; margin-top: 10px;">
						
						<button type="button" class="submitbtnImg" id="btn_add_vf">
							<img class="inner_icon" src="images/add-button.png">
							Vehicle Files
						</button>
						
						<div id="upload_vf_div" style="display:none;  margin-top: 10px;">
							<input type="file" name="file" id="file" class="fname uploadfile submitbtnImg">	
							<input type="hidden" name="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />			
							<input type="submit" class="submitbtnImg" style="margin-left: 10px;" value="Upload" />
						</div>
						
						<div style="clear:both;"></div>
						
						<div style="margin-top: 10px;">
						<?php
							$vf_sql = mysql_query("
								SELECT *
								FROM `vehicle_files`
								WHERE `vehicles_id` = {$v['vehicles_id']}
							");
							if(mysql_num_rows($vf_sql)>0){
							?>
								<table style="width:auto; margin-bottom: 18px; border-collapse: inherit;">
								<?php
								while($vf = mysql_fetch_array($vf_sql)){ ?>
									<tr style="border: 0 none;">
										<td style="padding-right: 15px;"><a target="__blank" href="<?php echo $vf['path']."/".$vf['filename']; ?>"><?php echo $vf['filename']; ?></a></td>
										<td>
											<input type="hidden" class="vehicle_files_id" value="<?php echo $vf['vehicle_files_id']; ?>" />
											<input type="hidden" class="vf_path" value="<?php echo $vf['path']."/".$vf['filename']; ?>" />
											<a href="javascript:void(0);" class="btn_del_vf">Delete</a>
										</td>
									</tr>
								<?php
								}
								?>
								</table>
								<?php
							}else{
							?>
							<div style="margin-top: 10px;" class="align-left vpd-fupload">No Files Yet</div>
							<?php
							}
						?>
						</div>
						
					</div>				
				</form>
				
			</div>
		</div>
		
		
	</div>
	
	
	
	


	<div style="clear:both;"></div>

	
			

			
		
		


    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){
	
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
	
	// selects the previous tab on load
	var curr_tab = $.cookie('vvd_tab_index');
	if( curr_tab!='' ){
		
		if(curr_tab!=''){
			myTabs.goToTab(curr_tab);
		}else{
			myTabs.goToTab(0);
		}
		
	}
	
	// keep tab script
	jQuery(".c-tabs-nav__link").click(function(){
		
		var tab_index = jQuery(this).attr('data-tab_index');
		console.log(tab_index);
		$.cookie('vvd_tab_index', tab_index);
		
	});
	
	
	$("table.vvd-odd-cl tr:even").addClass("vvtbl-odd");
	

	jQuery("#btn_add_vf").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#upload_vf_div").show();
	},function(){
		jQuery(this).html("Add Vehicle Files");
		jQuery("#upload_vf_div").hide();
	});

	// delete logs
	jQuery(".btn_del_vf").click(function(){
	
		var vehicle_files_id = jQuery(this).parents("tr:first").find(".vehicle_files_id").val();
		var vf_path = jQuery(this).parents("tr:first").find(".vf_path").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_vehicle_files.php",
				data: { 
					vehicle_files_id: vehicle_files_id,
					vf_path: vf_path
				}
			}).done(function( ret ){
				window.location = "/view_vehicle_details.php?id=<?php echo $v['vehicles_id']; ?>";
			});	
		}
	});
	

	// delete logs
	jQuery(".btn_del_logs").click(function(){
	
		var vehicles_log_id = jQuery(this).parents("tr:first").find(".vehicles_log_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_vehicle_logs.php",
				data: { 
					vehicles_log_id: vehicles_log_id,
				}
			}).done(function( ret ){
				window.location = "/view_vehicle_details.php?id=<?php echo $v['vehicles_id']; ?>";
			});	
		}
	});

	// add vehicle logs
	jQuery("#btn_vehiclelog").click(function(){
	
		var log_date = jQuery("#log_date").val();
		var log_price = jQuery("#log_price").val();
		var log_details = jQuery("#log_details").val();
		var error = "";
		
		if(log_date==""){
			error += "Log date date is required\n";
		}
		if(log_price==""){
			error += "Log price is required\n";
		}
		if(log_details==""){
			error += "Log details is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_vehicle_log").submit();
		}
	
	});


	// update vehicle
	jQuery(".btn_update_vehicle").click(function(){
	
		var rego_expires = jQuery("#rego_expires").val();
		var staff_id = jQuery("#staff_id").val();
		var error = "";
		
		if(rego_expires==""){
			error += "Rego expiry date is required\n";
		}
		
		/*
		if(staff_id==""){
			error += "SATS user is required\n";
		}
		*/
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_vehicle").submit();
		}
		
	});

	$('#staff_id').on('change', function() {
		var obj = $(this);
		var thisval = $(this).val();
		//$('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_duplicate_vehicle_user.php",
			dataType: "json",
			data: { 
				staffid: thisval,
			}
		}).done(function( ret ){
			//$('#load-screen').hide();
			if(ret.status==true){
				alert('Technician has already been assigned to a vehicle');
				obj.find('option:first').prop('selected', 'selected');
				return false;
			}
		});	

	});

	
	
});
</script>
<script src="js/responsive_tabs.js"></script>
<script>
  var myTabs = tabs({
	el: '#tabs',
	tabNavigationLinks: '.c-tabs-nav__link',
	tabContentContainers: '.c-tab'
  });

  myTabs.init();
</script>
</body>
</html>
