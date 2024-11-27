<?php

$title = "Add Vehicle";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add Vehicle" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Add Vehicle</strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success">New vehicle added</div>
	<?php
	}
	?>
      	
	<form action="add_vehicle_script.php" method="post" id="frm_vehicle" style="font-size: 14px;">
	<div class="addproperty">
		<div class="row">
			<label class="addlabel" for="make">Make</label>
			<input type="text"  class="addinput" name="make" id="make">
		</div>
		<div class="row">
			<label class="addlabel" for="model">Model</label>
			<input type="text"  class="addinput" name="model" id="model">
		</div>
		<div class="row">
			<label class="addlabel" for="year">Year</label>
			<select name="year" id="year">
			<option value="">----</option>
			<?php
			$year =  range (2035,2005);
			foreach($year as $val){ ?>
				<option value="<?php echo $val; ?>"><?php echo $val; ?></option>
			<?php
			}
			?>
			</select>
		</div>
		<div class="row">
			<label class="addlabel" for="number_plate">Number Plate</label>
			<input type="text"  class="addinput" name="number_plate" id="number_plate">
		</div>
		<div class="row">
			<label class="addlabel" for="rego_expires">Rego Expires<span style="color:red">*</span></label>
			<input type="text"  class="addinput datepicker" name="rego_expires" id="rego_expires">
		</div>
		<div class="row">
			<label class="addlabel" for="key_number">Key Number</label>
			<input type="text"  class="addinput" name="key_number" id="key_number">
		</div>
		<div class="row">
			<label class="addlabel" for="warranty_expires">Warranty Expires </label>
			<input type="text"  class="addinput datepicker" name="warranty_expires" id="warranty_expires">
		</div>
		<div class="row">
			<label class="addlabel" for="fuel_type">Fuel Type</label>
			<select name="fuel_type" id="fuel_type">
				<option value="">----</option>
				<option value="Unleaded">Unleaded</option>	
				<option value="Premium">Premium</option>
				<option value="Diesel">Diesel</option>
				<option value="LPG">LPG</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel" for="etag_num">eTag Number</label>
			<input type="text"  class="addinput" name="etag_num" id="etag_num">
		</div>
		<div class="row">
			<label class="addlabel" for="serviced_by">Serviced By</label>
			<input type="text"  class="addinput" name="serviced_by" id="serviced_by">
		</div>
		<div class="row">
			<label class="addlabel" for="fuel_card_num">Fuel Card Number </label>
			<input type="text"  class="addinput" name="fuel_card_num" id="fuel_card_num">
		</div>
		<div class="row clear">
			<label class="addlabel" for="pol_exp">Fuel Card Pin</label>
			<input type="text"  class="addinput" name="fuel_card_pin" id="fuel_card_pin">
		</div>
		<div class="row">
			<label class="addlabel" for="purchase_date">Purchase Date</label>
			<input type="text"  class="addinput datepicker" name="purchase_date" id="purchase_date">
		</div>
		<div class="row">
			<label class="addlabel" for="purchase_price">Purchase Price</label>

				<div style="display: block; float: left; margin-top: 6px; position: absolute; margin-left: 215px;">$</div>
				<input type="text" id="purchase_price" name="purchase_price" class="addinput">

		</div>
		<div class="row clear">
			<label class="addlabel" for="ra_num">Roadside assistance Number</label>
			<input type="text"  class="addinput" name="ra_num" id="ra_num">
		</div>
		<div class="row clear">
			<label class="addlabel" for="ins_pol_num">Insurance Policy #</label>
			<input type="text"  class="addinput" name="ins_pol_num" id="ins_pol_num">
		</div>
		<div class="row clear">
			<label class="addlabel" for="pol_exp">Policy Expires</label>
			<input type="text"  class="addinput datepicker" name="pol_exp" id="pol_exp">
		</div>	
		<div class="row clear">
			<label class="addlabel" for="vin_num">VIN No.</label>
			<input type="text"  class="addinput" name="vin_num" id="vin_num">
		</div>	
		<div class="row clear">
			<label class="addlabel" for="vin_num">Plant ID</label>
			<input type="text"  class="addinput" name="plant_id" id="plant_id">
		</div>	
		<div class="row clear">
			<label class="addlabel" for="tech_vehicle">Tech Vehicle</label>
			<select name="tech_vehicle" id="tech_vehicle">
				<option value="">--Select--</option>
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
		</div>	
		<div class="row">
			<?php
				$sql = getStaffByCountry();
			?>
			<label class="addlabel" for="atsu">Assign to SATS User<span style="color:red">*</span></label>
			<select name="staff_id" id="staff_id">
				<option value="1">Unassigned</option>
				<?php 
				while($row=mysql_fetch_array($sql)){ ?>
					<option value="<?php echo $row['staff_accounts_id']; ?>"><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
				<?php
				}
				?>
			</select>
		</div>
		<div class="row">
        	<button class="submitbtnImg" id="btn_add_vehicle" type="button" style="float: left;">Add Vehicle</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){

	jQuery("#btn_add_vehicle").click(function(){
	
		var rego_expires = jQuery("#rego_expires").val();
		var staff_id = jQuery("#staff_id").val();
		var error = "";
		
		if(rego_expires==""){
			error += "Rego expiry date is required\n";
		}
		if(staff_id==""){
			error += "SATS user is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_vehicle").submit();
		}
		
	});

	
	
});
</script>

</body>
</html>
