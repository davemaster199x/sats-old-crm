<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}






$wm_sql = getWaterMeter($_GET['id']);
$wm = mysql_fetch_array($wm_sql);



if($_POST['wm_submit']==1){
	
	$location = mysql_real_escape_string($_POST['location']);
	$reading = mysql_real_escape_string($_POST['reading']);
	
	/*
	echo "Water Meter: {$_POST['wm_submit']}";
	echo "<pre>";
	print_r($_FILES);
	echo "</pre>";*/
	
	//echo "Location: {$location}<br />Reading: {$reading}";
	
	// update
	if(mysql_num_rows($wm_sql)>0){
		
		$db_ret = proccessWmUpload2($_FILES,$_POST,$_GET['id']);
		
		if($db_ret['meter_image']!=""){
			$db_str .= "`meter_image` = '".$db_ret['meter_image']."',";
		}
		
		if($db_ret['meter_reading_image']!=""){
			$db_str .= "`meter_reading_image` = '".$db_ret['meter_reading_image']."',";
		}
		
		$sql = "
			UPDATE `water_meter`
			SET
				`location` = '{$location}',
				{$db_str}
				`reading` = '{$reading}'
			WHERE `job_id` = {$_GET['id']}
		";
		mysql_query($sql);
		
		
		
	}else{ // insert
	
		$location = mysql_real_escape_string($_POST['location']);
		$reading = mysql_real_escape_string($_POST['reading']);
	
		$db_ret = proccessWmUpload2($_FILES,$_POST,$_GET['id']);
		
		mysql_query("
			INSERT INTO 
			`water_meter` (
				`job_id`,
				`location`,
				`reading`,
				`meter_image`,
				`meter_reading_image`
			)
			VALUES (
				{$_GET['id']},
				'{$location}',
				{$reading},
				'".$db_ret['meter_image']."',
				'".$db_ret['meter_reading_image']."'
			)
		");
		
	}
	
	// update tickbox
	mysql_query("
		UPDATE `jobs`
		SET 
			`wm_techconfirm` = 1
		WHERE `id` = {$_GET['id']}
	");	
	
	
	//echo "<script>window.location='/view_job_details_tech.php?id={$_GET['id']}&service={$_GET['service']}'</script>";

	//$error = $db_ret['error'];
	
	if($db_ret['error']!=""){
		$error = $db_ret['error'];
	}else{
		echo "<script>window.location='/view_job_details_tech.php?id={$_GET['id']}&service={$_GET['service']}".(($_GET['bundle_id']!='')?"&bundle_id={$_GET['bundle_id']}":"")."'</script>";
	}
	
}


?>
<style>
.safety_switch_toggle.safety_switch_2.grey > td {
    width: 52px !important;
}
.ssp_yes, .ssp_no{
	display: none;
}
</style>


<?php
if($error!=""){ ?>
<div class="error"><?php echo $error; ?></div>
<?php   
}
?>

<input type="hidden" name="alarm_count" value="<?php echo $num_existing_alarms; ?>" />
        


		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table" id="vjdt-ftable">
            <tr bgcolor="#00aeef">
				<th class="techsheet_header colorwhite bold">Location</th>
				<th class="techsheet_header colorwhite bold">Reading</th>
				<th class="techsheet_header colorwhite bold">Meter Image</th>
				<th class="techsheet_header colorwhite bold">Meter Reading Image</th>
            </tr>          
			
			<?php
			
			/*
			// get Water Meter
			$ss_sql = mysql_query("
				SELECT *
				FROM `safety_switch`
				WHERE `job_id` = {$_GET['id']}
				ORDER BY `make` 
			");
			if(mysql_num_rows($ss_sql)>0){
				while($ss = mysql_fetch_array($ss_sql)){ ?>
				<tr class="grey">
					<td><input type="text" class="fsbxl addinput widthauto m-l-n ss_field" name="update_ss_make[]" class="update_ss_make" value="<?php echo $ss['make']; ?>" /></td>
					<td><input type="text" class="fsbxl addinput widthauto m-l-n ss_field" name="update_ss_model[]" class="update_ss_model" value="<?php echo $ss['model']; ?>" /></td>
					<td>
						<select style="width: 160px;" name="update_ss_test[]" class="update_ss_test ss_field">
							<option value="">---</option>
							<option value="1" <?php echo ($ss['test']!="")?($ss['test']==1)?'selected="selected"':'':''; ?>>Pass</option>
							<option value="0" <?php echo ($ss['test']!="")?($ss['test']==0)?'selected="selected"':'':''; ?>>Fail</option>
							<option value="2" <?php echo ($ss['test']!="")?($ss['test']==2)?'selected="selected"':'':''; ?>>No Power</option>
						</select>
					<td>
						<input type="hidden" name="ss_changed_flag[]" class="ss_changed_flag" value="0" />
						<input type="hidden" name="ss_id[]" class="ss_id" value="<?php echo $ss['safety_switch_id']; ?>" />
						<a href="javascript:void(0);" class="btn_delete_ss">Delete</a>
					</td>
				</tr>
				<?php
				}
				?>
			<tr bgcolor="#00aeef">
				<th  class="techsheet_header colorwhite bold" colspan="4">Total Switch: <span class='ss_count'><?php echo mysql_num_rows($ss_sql); ?></span><input type="hidden" id="total_switch" value="<?php echo mysql_num_rows($ss_sql); ?>" /></th>
			</tr>
			<?php
			}else{ ?>
				<tr>
					<td colspan="5">Empty</td>
				</tr>
			<?php
			}
			*/
			
			
			
			?>
			
			<tr class="grey">
				<td><input type="text" name="location" id="location" style="width:100%;" value="<?php echo $wm['location']; ?>" /></td>
				<td><input type="number" name="reading" id="reading" style="width:80px;" value="<?php echo $wm['reading']; ?>" /></td>
				<td>
				<?php
				if($wm['meter_image']!=""){ ?>
					<a href="<?php echo $wm['meter_image']; ?>" class="fancybox" style="bottom: 7px; left: 5px; position: relative;"><img src="<?php echo $wm['meter_image']; ?>" style="width:100px;height:100px;" /></a><br />
				<?php	
				}
				?>
				<input type="file" capture="camera" accept="image/*" name="meter_image" id="meter_image" style="margin-top: 2px;" />
				<input type="hidden" name="meter_image_chaged" id="meter_image_changed" />
				</td>
				<td>
				<?php
				if($wm['meter_reading_image']!=""){ ?>
					<a href="<?php echo $wm['meter_reading_image']; ?>" class="fancybox" style="bottom: 7px; left: 5px; position: relative;"><img src="<?php echo $wm['meter_reading_image']; ?>" style="width:100px;height:100px;" /></a><br />			
				<?php	
				}
				?>				
				<input type="file" capture="camera" accept="image/*" name="meter_reading_image" id="meter_reading_image" style="margin-top: 2px;" />
				<input type="hidden" name="meter_reading_image_changed" id="meter_reading_image_changed" />
				</td>
			</tr>
			           
        </table> 		
		
		
		
		<div style="clear:both;"></div>
		
		
				<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table">
			<tr bgcolor="#00aeef">
				<th class="techsheet_header colorwhite bold">Technician</th>				
				<th class="techsheet_header colorwhite bold">Date</th>
				<th class="techsheet_header colorwhite bold">Job Notes</th>
                <th class="techsheet_header colorwhite bold">Property Notes</th>
			</tr>


			<tr class="grey">
				<td><?=$job_details['tech_first_name'];?> <?=$job_details['tech_last_name'];?><br /><br /></td>	
				<?php 
				$jc_sql = mysql_query("
					SELECT `completed_timestamp`
					FROM `jobs`
					WHERE `id` = {$_GET['id']}
				"); 
				$jc = mysql_fetch_array($jc_sql);
				?>
				<td><input type="text" style="width: 80px !important;" name="ts_signoffdate" value="<?php echo ($job_details['status']=='Completed')?(($jc['completed_timestamp']!="")?date("d/m/Y",strtotime($jc['completed_timestamp'])):''):$job_details['ts_signoffdate']; ?>" class="addinput inputauto"></td>
                
				<td>
					<a class="inlineFB" href="#job_lb_div">
						<textarea name="tech_comments" id="tech_comments" class="techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($job_details['tech_comments']) ? $job_details['tech_comments'] : $job_details['tech_comments']));?></textarea>
					</a>
				</td>
				<td>
					<a class="inlineFB" href="#prop_lb_div">
						<textarea name="prop_comments" id="prop_comments" class="techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($p['comments']) ? $p['comments'] : $p['comments']));?></textarea>
					</a>
				</td>
			</tr>
			
			
			
			<?php
			$tb_sql = mysql_query("
				SELECT `wm_techconfirm`
				FROM `jobs`
				WHERE `id` = {$_GET['id']}
			");
			$tb = mysql_fetch_array($tb_sql);
			
			
			if($serv2['bundle']==1){
				$tickbox = ( $tb['wm_techconfirm'] == 1 ) ? 'checked' : '';
			}else{
				$tickbox = ( $tb['wm_techconfirm'] == 1 && $job_details['ts_completed'] == 1 ) ? 'checked' : '';
			}
			?>
			<tr>
				<td colspan="3" class="vjdtch-row">
					<div><input type="checkbox" id="wm_techconfirm" class="required" name="wm_techconfirm" <?=$tickbox?> value="1"></div>
				<div><label for="wm_techconfirm">I confirm that I have read the water meter and accurately recorded its data.</label></div>
				</td>
				<td colspan="2">
					<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" id="job_id">
					<input type="hidden" name="tab" id="tab" value="<?php echo $job_tech_sheet_job_types[0]['html_id']; ?>-tab">
					<input type="hidden" name="btn_comp_ts_submit" id="btn_comp_ts_submit" value="0">
					<input type="hidden" name="wm_submit" id="wm_submit" value="0" />
					<button type="button" id="btn_comp_ts" class="submitbtnImg bluebutton">SUBMIT COMPLETED TECHSHEET</button>					
				</td>
			</tr>
		</table>
				

                
<style>
.disable{
display:none!important;
}
</style>
<script>
jQuery(document).ready(function(){
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();

	/*

	jQuery("#safety_switch_yes").click(function(){
		jQuery(".ssp_yes").show();
		jQuery(".ssp_no").hide();
	});

	jQuery("#safety_switch_no").click(function(){
		jQuery(".ssp_yes").hide();
		jQuery(".ssp_no").show();
	});


	jQuery(".ss_field").change(function(){
		jQuery(this).parents("tr:first").find(".ss_changed_flag").val(1);
	});

	jQuery("#btn_comp_ts").click(function(){
		var ss_loc = jQuery("#ss_location_view").val();
		var ss_quan = jQuery("#ss_quantity_view").val();
		var ss_techconfirm = jQuery("#ss_techconfirm").val();
		var item_tested = jQuery("#ts_items_tested").val();
		var error = "";
						
		if(ss_loc==""){
			error += 'Fuse Box Location is required \n';
		}		
		if(ss_quan==""){
			error += 'Water Meter Quantity is required \n';
		}
		if(jQuery("#ss_techconfirm").prop("checked")==false){
			error += "Please tick the confirmation box \n";
		}
		if(item_tested==""){
			error += "Please enter item tested \n";
		}
		
		
		
		if(error==""){			
			var ss_quantity = jQuery("#ss_quantity_view").val();
			var item_tested = jQuery("#ts_items_tested").val();
			if(parseInt(ss_quantity)!=parseInt(item_tested)){
			  if(confirm("Items Tested NOT Equal to Water Meter Quantity. Proceed?")){
				jQuery("#btn_comp_ts_submit").val(1);
				jQuery("#techsheetform").submit();
			  }
			}else{
				jQuery("#btn_comp_ts_submit").val(1);
				jQuery("#techsheetform").submit();
			}
			
		}else{
			alert(error);
		}
		
	});

	jQuery("#btn_save_ss").click(function(){
	
		var job_id = <?php echo $_GET['id']; ?>;
		var ss_make = jQuery("#ss_make").val();
		var ss_model = jQuery("#ss_model").val();
		var ss_test = jQuery("#ss_test").val();
		
		var error = "";
		
		
		if(ss_make==""){
			error += 'Water Meter Make is required \n';
		}
		
		if(ss_model==""){
			error += 'Water Meter Model is required \n';
		}
		
		if(error==""){
		
			jQuery.ajax({
				type: "POST",
				url: "ajax_add_safety_switch.php",
				data: { 
					job_id: job_id,
					ss_make: ss_make,
					ss_model: ss_model,
					ss_test: ss_test
				}
			}).done(function( ret ){
				//window.location='/view_job_details_tech.php?id=<?php echo $_GET['id']; ?>&service=<?php echo $_GET['service']; ?>&bundle_id=<?php echo $_GET['bundle_id']; ?>&ss_added=1';
				jQuery("#ss_added").val(1);
				jQuery("#techsheetform").submit();
			});	
		
		}else{
			alert(error);
		}
	
		
	
	});

	// update key number
	jQuery(".btn_delete_ss").click(function(){
		var job_id = <?php echo $_GET['id']; ?>;
		var ss_id = jQuery(this).parents("tr:first").find(".ss_id").val();
		if(confirm('Are you sure you want to delete?')){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_safety_switch.php",
				data: { 
					job_id: job_id,
					ss_id: ss_id
				}
			}).done(function( ret ){
				window.location='/view_job_details_tech.php?id=<?php echo $_GET['id']; ?>&service=<?php echo $_GET['service']; ?>&bundle_id=<?php echo $_GET['bundle_id']; ?>&del_ss=1';
			});	
		}					
	});

	// hide/show add Water Meter
	jQuery("#btn_add_ss").toggle(function(){
	  jQuery(this).html("Cancel");
	  jQuery("#add_ss_div").show();
	},function(){
	  jQuery(this).html("ADD Water Meter");
	  jQuery("#add_ss_div").hide();
	});


	jQuery("#btn_add_alarm").click(function(){
		jQuery("#alarm_div1").show();
	});
	
	*/
	
	// mark as changed
	jQuery("#meter_image").change(function(){
		//console.log('trigger');
		jQuery("#meter_image_changed").val(1);
	});
	
	jQuery("#meter_reading_image").change(function(){
		//console.log('trigger');
		jQuery("#meter_reading_image_changed").val(1);
	});
	
	// validation
	jQuery("#btn_comp_ts").click(function(){
		var location = jQuery("#location").val();
		var reading = jQuery("#reading").val();
		var error = "";
						
		if(location==""){
			error += 'Location is required \n';
		}		
		if(reading==""){
			error += 'Reading is required \n';
		}
		if(jQuery("#wm_techconfirm").prop("checked")==false){
			error += "Please tick the confirmation box \n";
		}
		
		
		if(error==""){	
			jQuery("#btn_comp_ts_submit").val(1);
			jQuery("#wm_submit").val(1);
			jQuery("#techsheetform").submit();			
		}else{
			alert(error);
		}
		
	});
	
});
</script>