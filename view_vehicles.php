<?php

$title = "View Vehicles";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="View Vehicles" href="/view_vehicles.php"><strong>View Vehicles</strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">Update Successful</div>
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
			<div class="fl-left">
				<a href="view_vehicles.php?view_all=1"><button class="submitbtnImg" type="button">View All</button></a>	
			</div>
			<div class="fl-left">
				<a href="export_vehicle.php"><button type="button" class="submitbtnImg">Export</button></a>
			</div>	
		</div>

		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">				
				<th>Plant ID</th>
				<th>Make</th>
				<th>Model</th>				
				<th>Key #</th>
				<th>Vin #</th>
				<th>Number Plate</th>
				<th>Driver</th>				
				<th>Kms</th>
				<th>Kms Updated</th>
				<th>Next Service</th>
				<th>Rego Expires</th>
				<th>Tech Vehicle</th>
				<th>Active</th>
				<th>Edit</th>
			</tr>
			<?php
			$v_sql = mysql_query("
				SELECT *, v.`active` AS v_active
				FROM `vehicles` AS v
				LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = v.`StaffID`
				WHERE v.`country_id` = {$_SESSION['country_default']}
				".(($_GET['view_all']!=1)?' AND v.`active` = 1 ':'')."
				ORDER BY v.`plant_id`
			");
			while($v = mysql_fetch_array($v_sql)){ ?>
				<tr class="body_tr jalign_left">
				
					<td>
						<span class="txt_lbl"><?php echo $v['plant_id']; ?></span>
						<input type="text" name="plant_id" class="txt_hid plant_id" value="<?php echo $v['plant_id']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo $v['make']; ?></span>
						<input type="text" name="make" class="txt_hid make" value="<?php echo $v['make']; ?>" />
						<input type="hidden" name="vehicles_id" class="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo $v['model']; ?></span>
						<input type="text" name="model" class="txt_hid model" value="<?php echo $v['model']; ?>" />
					</td>					
					<td>
						<span class="txt_lbl"><?php echo $v['key_number']; ?></span>
						<input type="text" name="key_number" class="txt_hid key_number" value="<?php echo $v['key_number']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo $v['vin_num']; ?></span>
						<input type="text" name="vin_num" class="txt_hid vin_num" value="<?php echo $v['vin_num']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><a href="/view_vehicle_details.php?id=<?php echo $v['vehicles_id']; ?>"><?php echo $v['number_plate']; ?></a></span>
						<input type="text" name="make number_plate" class="txt_hid number_plate" value="<?php echo $v['number_plate']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo $v['FirstName'].' '.$v['LastName']; ?></span>
						<?php
							$sql = mysql_query("
								SELECT *
								FROM `staff_accounts`
								WHERE `active` =1
								AND `Deleted` =0
								ORDER BY `FirstName` ASC, `LastName` ASC
							");
						?>
						<select name="staff_id" class="txt_hid staff_id">
							<option value="">----</option>
							<?php 
							while($row=mysql_fetch_array($sql)){ ?>
								<option value="<?php echo $row['StaffID']; ?>" <?php echo ($v['StaffID']==$row['StaffID'])?'selected="selected"':''; ?>><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
							<?php
							}
							?>
						</select>
					</td>
					
					
					
					 <?php
						$kms_sql = mysql_query("
							SELECT *
							FROM `kms`
							WHERE `vehicles_id` = {$v['vehicles_id']}
							ORDER BY `kms_updated` DESC
							LIMIT 0, 1
						");
						$kms = mysql_fetch_array($kms_sql);
						?>		
					<td>
						<span class="txt_lbl"><?php echo $kms['kms']; ?></span>
						<input type="text" name="kms" class="txt_hid kms" value="<?php echo $kms['kms']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo ( $crm->isDateNotEmpty($kms['kms_updated']) == true )?date("d/m/Y",strtotime($kms['kms_updated'])):''; ?></span>
						<span class="txt_hid "><?php echo  date("d/m/Y",strtotime($kms['kms_updated'])); ?></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo $v['next_service']; ?></span>
						<input type="text" name="next_service" class="txt_hid next_service" value="<?php echo $v['next_service']; ?>" />
					</td>
					<td>
						<span class="txt_lbl"><?php echo ( $crm->isDateNotEmpty($v['rego_expires']) == true )?date("d/m/Y",strtotime($v['rego_expires'])):''; ?></span>
						<span class="txt_hid "><input type="text" name="rego_expires" class="txt_hid datepicker rego_expires" value="<?php echo  date("d/m/Y",strtotime($v['rego_expires'])); ?>" /></span>
					</td>
					<td>
						<span class="txt_lbl"><?php echo ($v['tech_vehicle']==1)?'Yes':''; ?></span>
						<select class="txt_hid tech_vehicle" name="tech_vehicle" id="tech_vehicle">
							<option value="">--Select--</option>
							<option value="1" <?php echo ($v['tech_vehicle']==1)?'selected="selected"':''; ?>>Yes</option>
							<option value="0" <?php echo ($v['tech_vehicle']==0)?'selected="selected"':''; ?>>No</option>
						</select>
					</td>
					
					<td>
						<span class="txt_lbl"><?php echo ($v['v_active']==1)?'Yes':''; ?></span>
						<select class="txt_hid active" name="active" id="active" style="width: auto !important;">
							<option value="">--Select--</option>
							<option value="1" <?php echo ($v['v_active']==1)?'selected="selected"':''; ?>>Active</option>
							<option value="0" <?php echo ($v['v_active']==0)?'selected="selected"':''; ?>>Inactive</option>
						</select>
					</td>
					
					<td>			
						<button class="blue-btn submitbtnImg btn_update">Update</button>
						<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
						<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
						<!--<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>-->
					</td>					
				</tr>
			<?php
			}
			?>			
		</table>
	
     <div class="row" style="display:block; padding-top: 20px; clear: both;">
        	<button style="float: left;" type="button" id="btn_add_vehicle" class="submitbtnImg" onclick="location.href='add_vehicle.php'">Add Vehicle</button>
        </div>
	
	</div>
	
</div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){

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
		var key_number = jQuery(this).parents("tr:first").find(".key_number").val();
		var vin_num = jQuery(this).parents("tr:first").find(".vin_num").val();
		var number_plate = jQuery(this).parents("tr:first").find(".number_plate").val();
		var staff_id = jQuery(this).parents("tr:first").find(".staff_id").val();
		var tech_vehicle = jQuery(this).parents("tr:first").find(".tech_vehicle").val();		
		var kms = jQuery(this).parents("tr:first").find(".kms").val();
		var next_service = jQuery(this).parents("tr:first").find(".next_service").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();	
		var rego_expires = jQuery(this).parents("tr:first").find(".rego_expires").val();
		var error = "";
		
		if(number_plate==""){
			error += "Number Plate is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_vehicles.php",
				data: { 
					vehicles_id: vehicles_id,
					make: make,
					model: model,
					plant_id: plant_id,
					key_number: key_number,
					vin_num: vin_num,
					number_plate: number_plate,
					staff_id: staff_id,
					kms: kms,
					next_service: next_service,
					tech_vehicle: tech_vehicle,
					active: active,
					rego_expires: rego_expires
				}
			}).done(function( ret ) {
				window.location="/view_vehicles.php?success=1";
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_vehicle.php",
				data: { 
					vehicles_id: vehicles_id,
				}
			}).done(function( ret ){
				window.location = "/view_vehicles.php";
			});	
		}
	});
	
	

});
</script>
</body>
</html>
