<?php

$title = "Smoke Alarm Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$smoke_alarm_id  = mysql_real_escape_string($_REQUEST['id']);
$country_id = $_SESSION['country_default'];

$jparams = array(
	'smoke_alarm_id' => $smoke_alarm_id,
	'country_id' => $country_id
);

$sql_sql = $crm->getSmokeAlarms($jparams);
$sa = mysql_fetch_array($sql_sql);

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


.jimage {
    left: 21px;
    position: relative;
    top: 69px;
    width: 200px;
}

.jimage_display{
	width: 150px;
}
</style>



    
    <div id="mainContent">
	
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="<?php echo $title; ?>" href="/alarm_guide.php">Alarm Guide</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/view_alarm_details.php?id=<?php echo $smoke_alarm_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Alarm Updated</div>
	<?php
	}
	?>
      	
	<form action="update_alarm_script.php" method="post" id="jform" style="font-size: 14px;" enctype="multipart/form-data">
	<div class="addproperty">		
		<div class="row">
			<label class="addlabel">Make</label>
			<input type="text"  class="addinput" name="make" id="make" value="<?php echo $sa['make']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Model</label>
			<input type="text"  class="addinput" name="model" id="model" value="<?php echo $sa['model']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Power Type</label>
			<select name="power_type" id="power_type" class="power_type">
				<option value="">----</option>				
				<option value="1" <?php echo ($sa['power_type']==1)?'selected':''; ?>>3v</option>
				<option value="2" <?php echo ($sa['power_type']==2)?'selected':''; ?>>3vli</option>
				<option value="3" <?php echo ($sa['power_type']==3)?'selected':''; ?>>9v</option>
				<option value="4" <?php echo ($sa['power_type']==4)?'selected':''; ?>>9vli</option>
				<option value="5" <?php echo ($sa['power_type']==5)?'selected':''; ?>>240v</option>
				<option value="6" <?php echo ($sa['power_type']==6)?'selected':''; ?>>240vli</option>
			</select>
		</div>	
		<div class="row">
			<label class="addlabel">Detection Type</label>			
			<select name="detection_type" id="detection_type" class="detection_type">
				<option value="">----</option>				
				<option value="1" <?php echo ($sa['detection_type']==1)?'selected':''; ?>>Photo-Electric</option>
				<option value="2" <?php echo ($sa['detection_type']==2)?'selected':''; ?>>Ionisation</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Expiry/ Manufacture Date</label>
			<select name="expiry_manuf_date" id="expiry_manuf_date" class="expiry_manuf_date">
				<option value="">----</option>				
				<option value="1" <?php echo ($sa['expiry_manuf_date']==1)?'selected':''; ?>>Expiry</option>
				<option value="0" <?php echo (is_numeric($sa['expiry_manuf_date']) && $sa['expiry_manuf_date']==0)?'selected':''; ?>>Manufacture</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Location of Date</label>
			<input type="text" class="addinput loc_of_date" id="loc_of_date" name="loc_of_date" value="<?php echo $sa['loc_of_date']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Removable Battery</label>
			<select name="remove_battery" id="remove_battery" class="remove_battery">
				<option value="">----</option>				
				<option value="1" <?php echo ($sa['remove_battery']==1)?'selected':''; ?>>Yes</option>
				<option value="0" <?php echo (is_numeric($sa['remove_battery']) && $sa['remove_battery']==0)?'selected':''; ?>>No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Hush Button</label>
			<select name="hush_button" id="hush_button" class="hush_button">
				<option value="">----</option>				
				<option value="1" <?php echo ($sa['hush_button']==1)?'selected':''; ?>>Yes</option>
				<option value="0" <?php echo (is_numeric($sa['hush_button']) && $sa['hush_button']==0)?'selected':''; ?>>No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Common faults</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="common_faults" id="common_faults"><?php echo $sa['common_faults']; ?></textarea>
		</div>
		<div class="row">
			<label class="addlabel">How to Remove Alarm</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="how_to_rem_al" id="how_to_rem_al"><?php echo $sa['how_to_rem_al']; ?></textarea>
		</div>
		<div class="row">
			<label class="addlabel">Additional Notes</label>
			<textarea class="addtextarea" style="height: 100px; margin: 0;" name="adntl_notes" id="adntl_notes"><?php echo $sa['adntl_notes']; ?></textarea>
		</div>
		<div class="row">
			<label class="addlabel">Front Image</label>
			<?php
			if( $sa['front_image']!='' ){ ?>	
				<div style='float:left;'>
					<a target="_blank" class="fancybox" href="images/smoke_alarms/<?php echo $sa['front_image']; ?>">
						<img src="images/smoke_alarms/<?php echo $sa['front_image']; ?>" class='jimage_display'  />
					</a>
				</div>
			<?php	
			}
			?>		
			<?php
			if( $_SESSION['USER_DETAILS']['ClassID']!=6 ){ ?>
				<input type="file"  class="addinput jimage" name="front_image" id="front_image" capture="camera" accept="image/*" <?php echo ($sa['front_image']!='')?'style="width: 200px;"':''; ?> />
				<input type="hidden" name="front_image_touched" id="front_image_touched" value="" />
			<?php	
			}
			?>		
		</div>
		<div class="row">
			<label class="addlabel">Rear Image 1 </label>
			<?php
			if( $sa['rear_image_1']!='' ){ ?>
				<div style='float:left;'>
					<a target="_blank" class="fancybox" href="images/smoke_alarms/<?php echo $sa['rear_image_1']; ?>">
						<img src="images/smoke_alarms/<?php echo $sa['rear_image_1']; ?>" class='jimage_display' />
					</a>
				</div>
			<?php	
			}
			?>
			<?php
			if( $_SESSION['USER_DETAILS']['ClassID']!=6 ){ ?>
				<input type="file"  class="addinput jimage" name="rear_image_1" id="rear_image_1" capture="camera" accept="image/*" <?php echo ($sa['front_image']!='')?'style="width: 200px;"':''; ?> />
				<input type="hidden" name="rare_image_1_touched" id="rare_image_1_touched" value="" />
			<?php	
			}
			?>			
		</div>
		<div class="row">
			<label class="addlabel">Rear Image 2 </label>
			<?php
			if( $sa['rear_image_2']!='' ){ ?>
				<div style='float:left;'>
					<a target="_blank" class="fancybox" href="images/smoke_alarms/<?php echo $sa['rear_image_2']; ?>">
						<img src="images/smoke_alarms/<?php echo $sa['rear_image_2']; ?>" class='jimage_display'  />
					</a>
				</div>
			<?php	
			}
			?>
			<?php
			if( $_SESSION['USER_DETAILS']['ClassID']!=6 ){ ?>
				<input type="file"  class="addinput jimage" name="rear_image_2" id="rear_image_2" capture="camera" accept="image/*" <?php echo ($sa['front_image']!='')?'style="width: 200px;"':''; ?> />
				<input type="hidden" name="rare_image_2_touched" id="rare_image_2_touched" value="" />
			<?php	
			}
			?>				
		</div>
		
		
		<?php
		if( $_SESSION['USER_DETAILS']['ClassID']!=6 ){ ?>
			<div class="row">
				<input type="hidden" name="sa_id" value="<?php echo $sa['smoke_alarm_id']; ?>" />
				<button class="submitbtnImg" id="btn_update_alarm" type="button" style="float: left; margin-right: 14px;">Update</button>
				<button class="submitbtnImg" id="btn_delete_alarm" type="button" style="float: left;">Delete</button>
			</div>			
		<?php	
		}
		?>
		
	</div>
	</form>


    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){
	
	
	
	jQuery("#btn_delete_alarm").click(function(){
		
		if( confirm("Are you sure you want to delete?") ){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_smoke_alarm.php",
				data: {
					sa_id: '<?php echo $smoke_alarm_id; ?>'
				}
			}).done(function( ret ) {

				window.location='alarm_guide.php?del_succ=1';
				
			});
			
		}
		
	});
	
	
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();
	
	
	
	jQuery("#btn_update_alarm").click(function(){
	
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
		
		/*
		if(front_image==""){
			error += "Front Image\n";
		}
		if(rear_image_1==""){
			error += "Rear Image 1\n";
		}
		if(rear_image_2==""){
			error += "Rear Image 2\n";
		}
		*/

		
		
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
