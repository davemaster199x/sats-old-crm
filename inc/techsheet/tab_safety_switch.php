<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}


$job_id = mysql_real_escape_string($_GET['id']);


if($_GET['del_ss']==1){ ?>
	<div class="success">Safety Switch Delete Successful</div>
<?php
}

if($_POST['ss_added']==1){ ?>
	<div class="success">Safety Switch Added</div>
<?php
}




if( $_POST['btn_comp_ts_submit'] || $_POST['ss_added']==1 ){
	
	
	// update safety switch location and quantity
	$ts_safety_switch = mysql_real_escape_string($_POST['ts_safety_switch']);
	$ss_location = mysql_real_escape_string($_POST['ss_location']);
	$ss_quantity = mysql_real_escape_string($_POST['ss_quantity']);
	$tech_comments = mysql_real_escape_string($_POST['tech_comments']);
	$safety_switch_reason = mysql_real_escape_string($_POST['safety_switch_reason']);
	$ts_items_tested = mysql_real_escape_string($_POST['ts_items_tested']);

	// update switch survey
	mysql_query("
		UPDATE `jobs`
		SET 
			`ts_safety_switch` = '{$ts_safety_switch}',
			`ss_location` = '{$ss_location}',
			`ss_quantity` = '{$ss_quantity}',
			`tech_comments` = '{$tech_comments}',
			`ts_safety_switch_reason` = '{$safety_switch_reason}',
			`ss_items_tested` = '{$ts_items_tested}'
		WHERE `id` = {$job_id}
	");
	
	
	// update safety switch data
	$ss_changed_flag = $_POST['ss_changed_flag'];
	foreach($ss_changed_flag as $index=>$ss){
		if($ss==1){
			$ss_id = $_POST['ss_id'];
			$ss_make = $_POST['update_ss_make'];
			$ss_model = $_POST['update_ss_model'];
			$ss_test = $_POST['update_ss_test'];

			$ss_test_fin = ( is_numeric($ss_test[$index]) )?"'".mysql_real_escape_string($ss_test[$index])."'":"NULL";			

			mysql_query("
			UPDATE `safety_switch`
				SET 
					`make` = '".mysql_real_escape_string($ss_make[$index])."',
					`model` = '".mysql_real_escape_string($ss_model[$index])."',
					`test` = {$ss_test_fin}
				WHERE `safety_switch_id` = {$ss_id[$index]}
			");
		}
	}
	
	// update property notes
	$prop_comments =  mysql_real_escape_string($_POST['prop_comments']);
	mysql_query("
		UPDATE `property`
		SET 
			`comments` = '{$prop_comments}'
		WHERE `property_id` = {$job_details['property_id']}
	");
	
	
	// update ss image
	if( $_POST['ss_image_touched']==1 ){
	
		$sw_files = $_FILES['ss_image'];
		
		
		
		// dont upload if empty
		if($sw_files['name']!=''){
			
			
			// delete old image
			$c_sql = mysql_query("
				SELECT `ss_image`
				FROM `jobs`
				WHERE `id` = {$job_id}
			");
			$c = mysql_fetch_array($c_sql);

			if( $c['ss_image']!='' ){
				$file_to_delete = 'ss_image/'.$c['ss_image'];
				if( $file_to_delete!="" ){
					$crm->deleteFile($file_to_delete);
				}
			}
			
			
			
			// upload image
			$uparams = array(
				'files' => $sw_files,
				'id' => $job_id,
				'upload_folder' => 'ss_image',
				'image_size' => 760
			);
			$upload_ret = $crm->masterDynamicUpload($uparams);
			
			
			// store image path
			mysql_query("
				UPDATE `jobs`
				SET `ss_image` = '{$upload_ret['image_name']}'
				WHERE `id` = {$job_id}
			");
			
		}
		
		
	}
	
	if($_POST['btn_comp_ts_submit']){
		
		//echo "Blue Button pressed";
		
		// update tickbox
		mysql_query("
			UPDATE `jobs`
			SET 
				`ss_techconfirm` = 1
			WHERE `id` = {$_GET['id']}
		");
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
<input type="hidden" name="alarm_count" value="<?php echo $num_existing_alarms; ?>" />
        <table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table" id="vjdt-ftable">
            <tr bgcolor="#f15a22">
                <th class="techsheet_header colorwhite bold" colspan="100%">Property Survey</th>              
            </tr>          
			
				<?php
				$ss_sql = mysql_query("
					SELECT *
					FROM `jobs`
					WHERE `id` = {$_GET['id']}
				");
				$ss = mysql_fetch_array($ss_sql);
				?>
			
				<tr class="safety_switch_toggle safety_switch_2 grey">
					<td><div class="cw_lbl">Fusebox Viewed</div></td>
					<td style="width: 93px !important;">
						<input type="radio" style="display:none;" onclick="" name="ts_safety_switch" class="safety_switch_toggle" id="safety_switch_yes" <?php echo ($ss['ts_safety_switch'] == '2')?'checked':''; ?> value="2" />
						<label for="safety_switch_yes" style="margin-left: 0;">Yes</label>
						<input type="radio" style="display:none;" onclick="" name="ts_safety_switch" class="safety_switch_toggle radiobut-red"  id="safety_switch_no"  <?=($ss['ts_safety_switch'] == '1') ? 'checked' : '';?> value="1" />
						<label for="safety_switch_no" style="margin-left: 0;">No</label>
					</td>
					<td>
						<div class="cw_lbl ssp_yes" style="display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>">Fuse Box Location</div>
						<div class="cw_lbl ssp_no" style="display:<?php echo ($ss['ts_safety_switch']==1)?'block':'none'; ?>">Reason</div>
					</td>
					<td>
						<input type="text" name="ss_location" id="ss_location_view" class="fsbxl addinput widthauto m-l-n ssp_yes" value="<?php echo $ss['ss_location'];?>" style="display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>">
						<select name="safety_switch_reason" id="safety_switch_reason" class="ssp_no" style="display:<?php echo ($ss['ts_safety_switch']==1)?'block':'none'; ?>"> 
							<option value="">----</option>
							<option value="0" <?php echo (( is_numeric($ss['ts_safety_switch_reason']) && $ss['ts_safety_switch_reason'] == 0) ? "selected" : ""); ?>>Circuit Breaker Only</option>
							<option value="1" <?php echo ($ss['ts_safety_switch_reason'] == 1 ? "selected" : ""); ?>>Unable to Locate</option>
							<option value="2" <?php echo ($ss['ts_safety_switch_reason'] == 2 ? "selected" : ""); ?>>Unable to Access</option>
						</select>					
					</td>
					<td><div class="cw_lbl ssp_yes" style="display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>">Safety Switch Quantity</div></td>
					<td>
						<input type="number" style="width: 160px; display:<?php echo ($ss['ts_safety_switch']==2)?'block':'none'; ?>" name="ss_quantity" id="ss_quantity_view" class="fsbxl addinput widthauto m-l-n ssp_yes" value="<?php echo $ss['ss_quantity'];?>" />
					</td>
					<td>Switch Board Image</td>
					<td>
						<?php
						if($ss['ss_image']!=''){ 
							
							// dynamic switch of ss image
							if ( file_exists("{$_SERVER['DOCUMENT_ROOT']}/images/ss_image/{$ss['ss_image']}") ) {   
								// old techsheet 
								$ss_image_upload_folder = '/images/ss_image';
							}else{ // tecsheet CI 
								$ci_domain = $crm->getDynamicCiDomain();
								$ss_image_upload_folder = "{$ci_domain}/uploads/switchboard_image";
							}
							
							?>
							
							<a href="<?php echo $ss_image_upload_folder ?>/<?php echo $ss['ss_image']; ?>" class="fancybox" target="_blank">
								<img src="/images/camera_orange.png" />
							</a> 
							<span style="position: relative; bottom: 5px; left: 4px; margin-right: 9px; color:#f15a22;">Image Stored</span>
							<input type="file" capture="camera" accept="image/*" name="ss_image" id="ss_image" style="margin-top: 2px;" />
							<input type="hidden" name="ss_image_touched" id="ss_image_touched" value="" />
						
						<?php	
						}else{ ?>
						
							<img src="/images/camera_white.png" style="position: relative; top: 5px; right: 4px;" />
							<input type="file" capture="camera" accept="image/*" name="ss_image" id="ss_image" style="margin-top: 2px;" />
							<input type="hidden" name="ss_image_touched" id="ss_image_touched" value="" />
						
						<?php	
						}
						?>
						
					</td>	
				</tr>

				
			           
        </table> 


		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table" id="vjdt-ftable">
            <tr bgcolor="#f15a22">
                <th class="techsheet_header colorwhite bold" colspan="4">Safety Switch</th>              
            </tr>          
			
			<tr>
				<td>Make</td>
				<td>Model</td>
				<td>Test</td>
				<td>Delete</td>
			</tr>
			
			<?php
			// get safety switch
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
							<option value="1" <?php echo ($ss['test']==1)?'selected="selected"':''; ?>>Pass</option>
							<option value="0" <?php echo ( is_numeric($ss['test']) && $ss['test']==0 )?'selected="selected"':''; ?>>Fail</option>
							<option value="2" <?php echo ($ss['test']==2)?'selected="selected"':''; ?>>No Power</option>
							<option value="3" <?php echo ($ss['test']==3)?'selected="selected"':''; ?>>Not Tested</option>
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
			<tr bgcolor="#f15a22">
				<th  class="techsheet_header colorwhite bold" colspan="4">Total Switch: <span class='ss_count'><?php echo mysql_num_rows($ss_sql); ?></span><input type="hidden" id="total_switch" value="<?php echo mysql_num_rows($ss_sql); ?>" /></th>
			</tr>
			<?php
			}else{ ?>
				<tr>
					<td colspan="5">Empty</td>
				</tr>
			<?php
			}
			?>
			           
        </table> 		
		
		<a class="inlineFB" href="#ss_window">
			<button type="button" id="btn_add_ss" class="submitbtnImg colorwhite" style="background-color: #f15a22;float: left;margin: 10px 0;">ADD Safety Switch</button>
		</a>
		
		<div style="clear:both;"></div>
		
		
		<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table">
			<tr bgcolor="#f15a22">
				<th class="techsheet_header colorwhite bold">Technician</th>				
				<th class="techsheet_header colorwhite bold">Date</th>
				<th class="techsheet_header colorwhite bold">Items Tested</th>
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
				<td><input type="text" name="ts_signoffdate" value="<?php echo ($job_details['status']=='Completed')?(($jc['completed_timestamp']!="")?date("d/m/Y",strtotime($jc['completed_timestamp'])):''):$job_details['ts_signoffdate']; ?>" class="addinput inputauto"></td>
                <?php
				$cw_tested_sql = mysql_query("
					SELECT `ss_items_tested`
					FROM `jobs`
					WHERE `id` = {$_GET['id']}
				");
				$cw_tested = mysql_fetch_array($cw_tested_sql);
				?>
				<td><input type="number" name="ts_items_tested" id="ts_items_tested"  value="<?=$cw_tested['ss_items_tested'];?>" class="addinput inputauto"></td>
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
				SELECT `ss_techconfirm`
				FROM `jobs`
				WHERE `id` = {$_GET['id']}
			");
			$tb = mysql_fetch_array($tb_sql);
			
			
			if($serv2['bundle']==1){
				$tickbox = ( $tb['ss_techconfirm'] == 1 ) ? 'checked' : '';
			}else{
				$tickbox = ( $tb['ss_techconfirm'] == 1 && $job_details['ts_completed'] == 1 ) ? 'checked' : '';
			}
			?>
			<tr>
				<td colspan="3" class="vjdtch-row">
					<div><input type="checkbox" id="ss_techconfirm" class="required" name="ss_techconfirm" <?=$tickbox?> value="1"></div>
				<div><label for="ss_techconfirm">I confirm that all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.</label></div>
				</td>
				<td colspan="2">
					<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" id="job_id">
					<input type="hidden" name="tab" id="tab" value="<?php echo $job_tech_sheet_job_types[0]['html_id']; ?>-tab">
					<input type="hidden" name="btn_comp_ts_submit" id="btn_comp_ts_submit" value="0">
					<input type="hidden" name="ss_added" id="ss_added" value="0" />
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

	// mark safety switch as touched
	jQuery("#ss_image").change(function(){		
		jQuery("#ss_image_touched").val(1);		
	});


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
		var update_ss_test = jQuery(".update_ss_test");

		var error = "";
		
		<?php
		if( $ss['ts_safety_switch']==2 ){ ?>
			if( item_tested!=0 ){
				if(ss_loc==""){
					error += 'Fuse Box Location is required \n';
				}		
				if(ss_quan==""){
					error += 'Safety Switch Quantity is required \n';
				}
			}			
		<?php	
		}
		?>		
		if(jQuery("#ss_techconfirm").prop("checked")==false){
			error += "Please tick the confirmation box \n";
		}
		if(item_tested==""){
			error += "Please enter item tested \n";
		}

		// find empty ss test
		var has_empty_ss_test = 0;
		update_ss_test.each(function(){

			var test = jQuery(this).val();
			if( test == '' ){
				has_empty_ss_test++;
			}

		});

		//console.log('has_empty_ss_test: '+has_empty_ss_test);

		if( has_empty_ss_test > 0 ){
			error += "Safety Switch Test Result cant be blank\n";
		}
		
		
		
		if( error != '' ){	

			alert(error);					
			
		}else{

			
			var ss_quantity = jQuery("#ss_quantity_view").val();
			var item_tested = jQuery("#ts_items_tested").val();
			if(parseInt(ss_quantity)!=parseInt(item_tested)){
			  if(confirm("Items Tested NOT Equal to Safety Switch Quantity. Proceed?")){
				jQuery("#btn_comp_ts_submit").val(1);
				jQuery("#techsheetform").submit();
			  }
			}else{
				jQuery("#btn_comp_ts_submit").val(1);
				jQuery("#techsheetform").submit();
			}
			
			
		}
		
		
	});
	
	
	jQuery("#btn_add_ss_tbody").click(function(){
		
		// needed to make the height auto
		jQuery("#fancybox-content").css('height','auto');
		
		var ss_tbody = jQuery("#tbl_add_ss tbody.add_ss_tbody:last").clone();
		//jQuery("#tbl_add_ss").append(ss_tbody);
		
		
		// check for not empty and highlight it grey
		jQuery("#form_add_ss input[type='text'], #form_add_ss select").each(function(){
			
			if( jQuery(this).val()!='' ){
				jQuery(this).parents("tr:first").addClass("ssFileSelectedHL");
			}
			
			
		});
		
		ss_tbody.insertAfter("#tbl_add_ss tbody.add_ss_tbody:last");
		
	});


	jQuery("#btn_save_ss").click(function(){
	
		var job_id = <?php echo $_GET['id']; ?>;
		var ss_make_flag = 0;
		var ss_model_flag = 0;
		var ss_test_flag = 0;
		var error = "";
		
		// make
		jQuery("#tbl_add_ss .ss_make").each(function(){
			if(jQuery(this).val()==""){
				ss_make_flag = 1;					
			}
		});
		
		if( ss_make_flag == 1 ){
			error += "Make is required\n";
		}
		
		// model
		jQuery("#tbl_add_ss .ss_model").each(function(){
			if(jQuery(this).val()==""){
				ss_model_flag = 1;					
			}
		});
		
		if( ss_model_flag == 1 ){
			error += "Model is required\n";
		}

		// test
		jQuery("#tbl_add_ss .ss_test").each(function(){
			if(jQuery(this).val()==""){
				ss_test_flag = 1;					
			}
		});
		
		if( ss_test_flag == 1 ){
			error += "Test is required\n";
		}
		
		
		
		
		
		if(error==""){
			
			var num_ss = jQuery(".ss_make").length;
			var i = 1;
			
			jQuery(".ss_make").each(function(){
				
						
				var ss_make = jQuery(this).val();
				var ss_model = jQuery(this).parents("tbody.add_ss_tbody:first").find(".ss_model").val();
				var ss_test = jQuery(this).parents("tbody.add_ss_tbody:first").find(".ss_test").val();
				
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
					
					if( i == num_ss ){
						jQuery("#ss_added").val(1);
						jQuery("#techsheetform").submit();
					}
					
					i++;
				});	

		
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

	/*
	// hide/show add safety switch
	jQuery("#btn_add_ss").toggle(function(){
	  jQuery(this).html("Cancel");
	  jQuery("#add_ss_div").show();
	},function(){
	  jQuery(this).html("ADD Safety Switch");
	  jQuery("#add_ss_div").hide();
	});
	*/


	jQuery("#btn_add_alarm").click(function(){
		jQuery("#alarm_div1").show();
	});
});
</script>