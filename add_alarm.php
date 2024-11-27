<?php

$title = "Add Smoke Alarm";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
#jform textarea{
	width: 450px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/alarm_guide.php">Alarm Guide</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">New Alarms Added</div>
	<?php
	}
	?>
      	
	<form action="add_alarm_script.php" method="post" id="jform" style="font-size: 14px;" enctype="multipart/form-data">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Make</label>
			<input type="text"  class="addinput" name="make" id="make">
		</div>
		<div class="row">
			<label class="addlabel">Model</label>
			<input type="text"  class="addinput" name="model" id="model">
		</div>
		<div class="row">
			<label class="addlabel">Power Type</label>
			<select name="power_type" id="power_type" class="power_type">
				<option value="">----</option>				
				<option value="1">3v</option>
				<option value="2">3vli</option>
				<option value="3">9v</option>
				<option value="4">9vli</option>
				<option value="5">240v</option>
				<option value="6">240vli</option>
			</select>
		</div>	
		<div class="row">
			<label class="addlabel">Detection Type</label>			
			<select name="detection_type" id="detection_type" class="detection_type">
				<option value="">----</option>				
				<option value="1">Photo-Electric</option>
				<option value="2">Ionisation</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Expiry/ Manufacture Date</label>
			<select name="expiry_manuf_date" id="expiry_manuf_date" class="expiry_manuf_date">
				<option value="">----</option>				
				<option value="1">Expiry</option>
				<option value="0">Manufacture</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Location of Date</label>
			<input type="text" class="addinput loc_of_date" id="loc_of_date" name="loc_of_date" id="loc_of_date" />
		</div>
		<div class="row">
			<label class="addlabel">Removable Battery</label>
			<select name="remove_battery" id="remove_battery" class="remove_battery">
				<option value="">----</option>				
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Hush Button</label>
			<select name="hush_button" id="hush_button" class="hush_button">
				<option value="">----</option>				
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Common faults</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="common_faults" id="common_faults"></textarea>
		</div>
		<div class="row">
			<label class="addlabel">How to Remove Alarm</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="how_to_rem_al" id="how_to_rem_al"></textarea>
		</div>
		<div class="row">
			<label class="addlabel">Additional Notes</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="adntl_notes" id="adntl_notes"></textarea>
		</div>
		<div class="row">
			<label class="addlabel">Front Image</label>
			<input type="file"  class="addinput" name="front_image" id="front_image" capture="camera" accept="image/*"  />
		</div>
		<div class="row">
			<label class="addlabel">Rear Image 1 </label>
			<input type="file"  class="addinput" name="rear_image_1" id="rear_image_1" capture="camera" accept="image/*"  />
		</div>
		<div class="row">
			<label class="addlabel">Rear Image 2 </label>
			<input type="file"  class="addinput" name="rear_image_2" id="rear_image_2" capture="camera" accept="image/*"  />
		</div>
		<div class="row">
        	<button class="submitbtnImg" id="btn_add_alarm" type="button" style="float: left;">Submit</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){
	
	
	
	jQuery("#btn_add_alarm").click(function(){
	
		var make = jQuery("#make").val();
		var model = jQuery("#model").val();		
		var power_type = jQuery("#power_type").val();
		var detection_type = jQuery("#detection_type").val();
		var expiry_manuf_date = jQuery("#expiry_manuf_date").val();
		var loc_of_date = jQuery("#loc_of_date").val();
		var remove_battery = jQuery("#remove_battery").val();
		var hush_button = jQuery("#hush_button").val();
		var common_faults = jQuery("#common_faults").val();
		var how_to_rem_al = jQuery("#how_to_rem_al").val();
		var adntl_notes = jQuery("#adntl_notes").val();
		var front_image = jQuery("#front_image").val();
		var rear_image_1 = jQuery("#rear_image_1").val();
		var rear_image_2 = jQuery("#rear_image_2").val();
		var error = "";
		
		if(make==""){
			error += "Make is required\n";
		}
		if(model==""){
			error += "Model is required\n";
		}
		if(power_type==""){
			error += "Power Type is required\n";
		}
		if(detection_type==""){
			error += "Detection Type is required\n";
		}
		if(expiry_manuf_date==""){
			error += "Expiry/Manufacture Date is required\n";
		}
		if(loc_of_date==""){
			error += "Location of Date\n";
		}
		if(remove_battery==""){
			error += "Removable Battery\n";
		}
		if(hush_button==""){
			error += "Hush Button\n";
		}
		if(common_faults==""){
			error += "Common faults\n";
		}
		if(how_to_rem_al==""){
			error += "How to Remove Alarm\n";
		}
		if(adntl_notes==""){
			error += "Additional Notes\n";
		}
		if(front_image==""){
			error += "Front Image\n";
		}
		if(rear_image_1==""){
			error += "Rear Image 1\n";
		}
		if(rear_image_2==""){
			error += "Rear Image 2\n";
		}
		

		
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#jform").submit();
		}
		
		
	});

	
	
});
</script>

</body>
</html>
