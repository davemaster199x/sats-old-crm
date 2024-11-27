<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}


// delete WE
if ( $_GET['we_del'] == 1 ) {

	$we_id = mysql_real_escape_string($_GET['we_id']);

	if( $we_id > 0 ){

		$query = "
		DELETE 
		FROM `water_efficiency` 
		WHERE `water_efficiency_id` = {$we_id} 
		AND job_id = " . $job_id;
		
		mysql_query($query) or die(mysql_error());

	}	

}


if($_GET['action']=='update'){

	$we_id_arr = $_POST['we_id'];
	$update_device = $_POST['update_device'];
	$update_pass = $_POST['update_pass'];
	$update_location = $_POST['update_location'];
	$update_note = $_POST['update_note'];
	
	foreach ( $we_id_arr as $index => $we_id ){
				
		if( $we_id != '' ){

			$update_pass_val = ( $update_pass[$index] != '' )?mysql_real_escape_string($update_pass[$index]):'NULL';

			$we_update_sql_str = "
			UPDATE  `water_efficiency`
			SET		
				`pass` = {$update_pass_val},		
				`location` = '".mysql_real_escape_string($update_location[$index])."',
				`note` = '".mysql_real_escape_string($update_note[$index])."'							
			WHERE `water_efficiency_id` = ".mysql_real_escape_string($we_id)."
			";
												
			mysql_query($we_update_sql_str);
			
		}
		
	}
	
	$ts_items_tested = mysql_real_escape_string($_POST['ts_items_tested']);		
	$property_leaks = ( $_POST['property_leaks'] != '' )?mysql_real_escape_string($_POST['property_leaks']):'NULL';
	$leak_notes = mysql_real_escape_string($_POST['leak_notes']);	
	
	// update job
	mysql_query("
		UPDATE `jobs`
		SET 
			`we_items_tested` = '{$ts_items_tested}',
			`property_leaks` = {$property_leaks},
			`leak_notes` = '{$leak_notes}',			
			`we_techconfirm` = 1
		WHERE `id` = {$job_id}
	");	
	

}



// add new cw
if( $_POST['new_we_submitted']==1 ){	


	$we_device = $_POST['we_device'];
	$we_pass = $_POST['we_pass'];
	$we_location = $_POST['we_location'];
	$we_notes = $_POST['we_notes'];

	
	foreach ( $we_device as $index => $device ){
		
		// dont upload if empty
		if( $device != '' ){

			$we_pass_val = ( $we_pass[$index] != '' )?mysql_real_escape_string($we_pass[$index]):'NULL';
									
			// store image path
			$insert_we_sql_str = "
			INSERT INTO 
			`water_efficiency`(
				`job_id`,
				`device`,				
				`pass`,
				`location`,
				`note`,
				`created_date`
			)
			VALUES(
				{$job_id},
				'".mysql_real_escape_string($device)."',				
				{$we_pass_val},
				'".mysql_real_escape_string($we_location[$index])."',
				'".mysql_real_escape_string($we_notes[$index])."',
				'".date('Y-m-d H:i:s')."'								
			)
			";			
			mysql_query($insert_we_sql_str);
			
		}
		
	}
	
	
	$property_leaks = ( $_POST['property_leaks_hid'] != '' )?mysql_real_escape_string($_POST['property_leaks_hid']):'NULL';
	$leak_notes = mysql_real_escape_string($_POST['leak_notes_hid']);
	
	// update property survey
	$update_job_sql_str = "
	UPDATE `jobs`
	SET 			
		`property_leaks` = {$property_leaks},
		`leak_notes` = '{$leak_notes}'
	WHERE `id` = {$job_id}
	";
	mysql_query($update_job_sql_str);	

}



// get job data
$job_sql = mysql_query("
	SELECT 
		`property_leaks`,
		`we_techconfirm`,
		`leak_notes`,
		`we_items_tested`,
		`ts_signoffdate`,
		`ts_completed`
	FROM `jobs`
	WHERE `id` = {$job_id}
");
$job_row = mysql_fetch_array($job_sql);


?>



<table border=0 cellspacing=0 cellpadding=5 width=100% class="tech_table">
	<tr style="background-color: <?php echo $service_color; ?>">
		<th class="techsheet_header colorwhite bold" colspan="100%">Property Survey</th>              
	</tr>          
	<tr>
		<td>Do any taps on the premises leak?</td>
		<td>
			<input type="radio" name="property_leaks" class="property_leaks" id="property_leaks_yes" <?php echo ( $job_row['property_leaks'] == 1 )?'checked':''; ?> value="1" />
			<label for="property_leaks_yes">Yes</label>
			<input type="radio" name="property_leaks" class="property_leaks"  id="property_leaks_no"  <?php echo ( is_numeric($job_row['property_leaks']) && $job_row['property_leaks'] == 0 ) ? 'checked' : ''; ?> value="0" />
			<label for="property_leaks_no">No</label>
		</td>	
		<td>Describe the leak location (Agency will see this note!!) <span class="desc_leak_loc_span colorItRed" style="display:<?php echo ( $job_row['property_leaks'] == 1 )?'inline':'none'; ?>">*</span></td>
		<td>
			<textarea name="leak_notes" id="leak_notes" class="addtextarea leak_notes"><?php echo $job_row['leak_notes']; ?></textarea>
		</td>				
	</tr>	
</table> 


<h5 class="item_to_test_tbl_header">Items to Test</h5>
<table class="item_to_test_tbl" style="float:left">
	<thead>
		<tr>
			<th class="do_test_col">DO TEST <img class="green_check" src="/images/green_check_new.png" /></th>
			<th class="do_not_test_col">DO NOT TEST <img class="red_cross" src="/images/red_cross_new.png" /></th>
		</tr>
	</thead>	
	<tbody>
		<tr>
			<td class="do_test_col">Shower Heads</td>
			<td class="do_not_test_col">Bathtub</td>
		</tr>
		<tr>
			<td class="do_test_col">Kitchen Sink</td>
			<td class="do_not_test_col">Washing Machine Tap</td>
		</tr>
		<tr>
			<td class="do_test_col">Bathroom Sink</td>
			<td class="do_not_test_col">Outdoor Taps</td>
		</tr>
		<tr>
			<td class="do_test_col">Toilets</td>
			<td class="do_not_test_col">Laundry Sink</td>
		</tr>
	</tbody>
</table>

<div class="toilet_reminder">All toilets to be recorded.</div>
<div style="clear: both;"></div>


<?php
// get WE data
$we_sql = mysql_query("
	SELECT 
		we.`water_efficiency_id`,
		we.`device`,
		we.`pass`,
		we.`location`,
		we.`note`,

		wed.`name` AS wed_name
	FROM `water_efficiency` AS we
	LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
	WHERE we.`job_id` = {$job_id}
	AND we.`active` = 1
");
?>


<input type="hidden" name="corded_window_count" value="<?=$num_existing_ss;?>" />
<? if(mysql_num_rows($we_sql)==0){
?>
<div class="error" id="appliance_error">
	There is no data on file. Please add Water Efficiency information
</div>
<?php } ?>

<?php
if(mysql_num_rows($we_sql)>0){ ?>

<table border=0 cellspacing=0 cellpadding=0 width=100% class="tech_table existing_corded_window" id="cw_exist_tbl">
	<thead>
	<tr>
		<td class="techsheet_header" colspan="10" style="background-color: <?php echo $service_color; ?>">Existing Water Efficiency Data</td>
	</tr>
	<tr>
		<td>Device</td>		
		<td>Toilet Type / Is water flow less than 9L per minute?</td>
		<td>Location</td>
		<td>Note</td>
		<td style="border-right: 1px solid #ccc;">Delete</td>
	</tr>
	</thead>
	<tbody>
		<?php

		$x = 0;
		$has_toilet = false;
		while( $we_row = mysql_fetch_array($we_sql) ){		
		?>
		<tr class="we_tr <?php echo $row_clr = ($x % 2 == 0 ? "grey" : "off"); ?>">		
			<td><?php echo $we_row['wed_name']; ?></td>	
			<td>
				<?php
				if( $we_row['device'] == 2 ){ // toilet
					$pass_yes = 'Dual';
					$pass_no = 'Single';	
					$has_toilet = true;									
				}else{					
					$pass_yes = 'Yes';
					$pass_no = 'No';
				}
				?>
				<input type="radio" class="update_pass update_pass_yes" name="update_pass[<?php echo $x; ?>]" value="1" <?php echo ( $we_row['pass'] == 1 )?'checked':null; ?> /> <label class="update_pass_lbl_yes"><?php echo $pass_yes; ?></label>
				<input type="radio" class="update_pass update_pass_no" name="update_pass[<?php echo $x; ?>]" value="0" <?php echo ( $we_row['pass'] == 0 && is_numeric($we_row['pass']) )?'checked':null; ?> /> <label class="update_pass_lbl_no"><?php echo $pass_no; ?></label>
			</td>	
			<td>
				<input type="text" class="addinput update_location" name="update_location[]" id="update_location" value="<?php echo $we_row['location']; ?>" />
			</td>
			<td>
				<input type="text" class="addinput update_note" name="update_note[]" id="update_note" value="<?php echo $we_row['note']; ?>" />
			</td>	
			<td style="border-right: 1px solid #ccc;">

				<input type="hidden" name="we_id[]" class="we_id" value="<?php echo $we_row['water_efficiency_id'] ?>" />
				<input type="hidden" name="we_edited[]" class="we_edited" value="0" />

				<?php			
				// delete url	
				if($_GET['bundle_id']!=""){
					$url = "/view_job_details_tech.php?id={$job_id}&service={$_GET['service']}&bundle_id={$_GET['bundle_id']}&we_id={$we_row['water_efficiency_id']}&we_del=1";
				}else{
					$url = "/view_job_details_tech.php?id={$job_id}&service={$_GET['service']}&ajt_id={$_GET['ajt_id']}&we_id={$we_row['water_efficiency_id']}&we_del=1";
				}				
				?>
				
				<a href="<?php echo $url; ?>" onclick="return confirm('Are you sure you want to delete this water efficency?');">
					Delete
				</a>
			
			</td>
		</tr>
		<?php 
		$x++;
		} ?>
	</tbody>
	<tfoot>

	</tfoot>
</table>
<input type="hidden" id="cw_touched_flag" name="cw_touched_flag" value="" />
<?php
}
?>

	
	
	<div style="margin-bottom: 9px;text-align: left;">
	<a class="inlineFB" href="#we_window">
		<button type="button" style="background-color: <?php echo $service_color; ?>" class="submitbtnImg" id="btn_add_entry">Add Item</button>
	</a>
	
	<button style="display:none;" type="button" id="cw_force_sync_button" class="submitbtnImg bluebutton">Sync CW</button>
	</div>
	
   
	
	

				
				
	<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table">
		<tr style="background-color: <?php echo $service_color; ?>">
			<td style="background-color: <?php echo $service_color; ?>" class="techsheet_header">Technician</td>				
			<td style="background-color: <?php echo $service_color; ?>" class="techsheet_header">Date</td>
			<td style="background-color: <?php echo $service_color; ?>" class="techsheet_header">Items Tested</td>
			<td style="background-color: <?php echo $service_color; ?>" class="techsheet_header">Job Notes</td>
			<td style="background-color: <?php echo $service_color; ?>" class="techsheet_header">Property Notes</td>
		</tr>


		<tr class="grey">
			<td><?=$job_details['tech_first_name'];?> <?=$job_details['tech_last_name'];?></td>				
			<td class="sm-wdth"><input type="text" name="ts_signoffdate" value="<?php echo ( $job_row['ts_signoffdate'] != '' )?$job_row['ts_signoffdate']:date('d/m/Y'); ?>" class="addinput inputauto datepicker"></td>
			<td class="sm-wdth"><input type="number" name="ts_items_tested" id="ts_items_tested"  value="<?=$job_row['we_items_tested'];?>" class="addinput inputauto"></td>
			<td>
				<a class="inlineFB" href="#job_lb_div">
					<textarea name="tech_comments" id="tech_comments" class="corderwindow-tarea techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($job_details['tech_comments']) ? $job_details['tech_comments'] : $job_details['tech_comments']));?></textarea>
				</a>
			</td>
			<td>
				<a class="inlineFB" href="#prop_lb_div">
					<textarea name="prop_comments" id="prop_comments" class="corderwindow-tarea techsheet addtextarea sig_commments" readonly="readonly"><?=stripslashes((isset($p['comments']) ? $p['comments'] : $p['comments']));?></textarea>
				</a>
			</td>
		</tr>
		
		<tr>
			<?php
			if($serv2['bundle']==1){
				$tickbox = ( $job_row['we_techconfirm'] == 1 ) ? 'checked' : '';
			}else{
				$tickbox = ( $job_row['we_techconfirm'] == 1 && $job_row['ts_completed'] == 1 ) ? 'checked' : '';
			}
			?>
			<td class="vjdtch-row" colspan="4">	
				<div><input type="checkbox" id="cw_techconfirm" class="required" name="cw_techconfirm" value="1"  <?=$tickbox?> /></div>				
				<div><label for="cw_techconfirm">I confirm that all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.</label></div>
			</td>
			<td colspan="1">				
				<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" id="job_id">
				<input type="hidden" name="tab" id="tab" value="<?php echo $job_tech_sheet_job_types[0]['html_id']; ?>-tab">
				<input type="hidden" name="btn_comp_ts_submit" id="btn_comp_ts_submit" value="0">		

				<?php
				// only allow submit techsheet if it has at least 1 toilet
				if( $has_toilet == true ){ ?>

					<button type="button" id="btn_comp_ts" class="submitbtnImg bluebutton">SUBMIT COMPLETED TECHSHEET</button>						

				<?php
				}
				?>				
			</td>
		</tr>
		
	</table>
	
	


	
<style>
	#fancybox-content{
		height: auto !important;
	}
	.we_radio{
		padding-left: 6px;
	}
	.we_radio2{
		padding: unset;
	}
	.update_device{
		margin: 0;
	}
	.item_to_test_tbl{
		width: auto;
		font-size: 13px;
		text-align: left;
		margin-bottom: 12px;
	}
	.item_to_test_tbl td,
	.item_to_test_tbl th{
		padding: 5px 20px 5px 10px !important;
	}
	.item_to_test_tbl_header{
		text-align:left;
		margin: 7px 0;
	}
	.item_to_test_tbl .green_check,
	.item_to_test_tbl .red_cross{
		width: 15px;
		position: relative;
		top: 2px;
	}
	.do_test_col{
		color: green;
	}
	.do_not_test_col{
		color: red;
	}
	.toilet_reminder{
		float: left;
		position: relative;
		top: 61px;
		left: 15px;
	}
</style>

<script>
function we_validation(){

	var error = '';
	var no_device_flag = 0;			
	var no_location_flag = 0;
	var is_empty = false;

	// location
	jQuery(".we_location").each(function(){
		if(jQuery(this).val()==""){
			no_location_flag = 1;					
		}
	});

	// device
	jQuery(".we_device").each(function(){
		if(jQuery(this).val()==""){
			no_device_flag = 1;					
		}
	});


	if( no_location_flag == 1 ){
		error += "Location is required\n";
	}

	if( no_device_flag == 1 ){
		error += "Device is required\n";
	}			


	// check for not empty fields and highlight it grey, radio is kinda tricky >.<
	jQuery(".we_pass_tr").each(function(){

		var tr_row = jQuery(this);
		var we_pass_checked_count = tr_row.find(".we_pass:checked").length;
		var we_device = tr_row.parents(".add_we_tbody:first").find(".we_device").val();				

		if( we_device > 0 && we_pass_checked_count == 0 ){
			is_empty = true;
		}			

	});

	if( is_empty == true ){
		error += "Toilet Type / Is water flow less than 9L per minute option is required \n";
	}

	return error;

	}


	function higlight_it_grey(){
		
		jQuery(".add_we_tbody tr").addClass("wfFileSelectedHL");

	}

jQuery(document).ready(function(){	


	// invoke fancybox
	jQuery('.fancybox').fancybox();
	

	/*
	// for sync cw
	jQuery("#cw_force_sync_button").click(function(){
		if(confirm("Are you sure you want to sync Corded Windows? This will overwrite all existing data. Proceed?")==true){
			var job_id = <?php echo $job_id; ?>;
			var property_id = <?php echo $job_details['property_id']; ?>;
			jQuery.ajax({
				type: "POST",
				url: "ajax_cw_force_sync.php",
				data: { 
					job_id: job_id,
					property_id: property_id
				}
			}).done(function( ret ){
				//window.location.href="/view_job_details.php?id=<?php echo $job_id; ?>&rebook_message=1<?php echo $added_param; ?>";
				//window.location='/view_job_details_tech.php?id=<?php echo $_GET['id'] ?>&service=<?php echo $_GET['service']; ?>&bundle_id=<?php echo $serv3['bundle_services_id']; ?>';
				var cw_link = jQuery(".j_cw").attr('href');
				window.location = cw_link;
			});	
		}		
	});
	*/
	


	// remew WE
	jQuery("#tbl_add_we").on("click","#btn_remove_we",function(){
		
		// remove last inserted row
		if( jQuery("#we_window .add_we_tbody").length > 1 ){
			jQuery("#we_window .add_we_tbody:last").remove();	
		}
		
		
	});
	
	// pass/fail label toggle
	jQuery("#tbl_add_we").on("change",".we_device",function(){

		var node = jQuery(this);
		var parent = node.parents(".add_we_tbody:first");
		var we_device = node.val();

		if( we_device == 2 ){ // toilet

			parent.find(".we_pass_tr").show();
			parent.find(".we_pass_lbl").html("Toilet Type");
			parent.find(".we_pass_lbl_yes").html("Dual");
			parent.find(".we_pass_lbl_no").html("Single");

		}else if( we_device == 1 || we_device == 3 ){
			
			parent.find(".we_pass_tr").show();
			parent.find(".we_pass_lbl").html("Is water flow less than 9L per minute?");
			parent.find(".we_pass_lbl_yes").html("Yes");
			parent.find(".we_pass_lbl_no").html("No");

		}else{

			parent.find(".we_pass_tr").hide();
			parent.find(".we_pass_lbl").html("Pass?");
			parent.find(".we_pass_lbl_yes").html("Yes");
			parent.find(".we_pass_lbl_no").html("No");

		}
		

	});
	


	// add new WE
	jQuery("#btn_add_we").click(function(){


		var error = we_validation();
					
		
		if(error!=''){
			
			alert(error);

		}else{

			higlight_it_grey();							
			
			// copy cw form fields
			var add_cw_form = jQuery("#we_window .add_we_tbody:last").clone();

			// clear grey shade
			add_cw_form.find("tr").removeClass("wfFileSelectedHL");

			// clear fields
			add_cw_form.find(".we_device").val('');
			add_cw_form.find(".we_pass").attr('checked', false);
			//add_cw_form.find(".we_location").val('');
			add_cw_form.find(".we_notes").val('');

			// hide
			add_cw_form.find(".we_pass_tr").hide();

			var we_pass_num = jQuery(".add_we_tbody").length;
			add_cw_form.find(".we_pass").attr("name",'we_pass['+we_pass_num+']');

			// insert new row
			add_cw_form.insertAfter("#we_window .add_we_tbody:last");	

				

		}


		
		
							
		
	});



	// save new CW
	jQuery("#btn_save_we").click(function(){

		var error = we_validation();	
		//higlight_it_grey();				
		
		if(error!=''){

			jQuery("#new_we_submitted").val(0);
			alert(error);

		}else{

			
			jQuery("#new_we_submitted").val(1);											
			jQuery("#form_add_we").submit();				

		}
		
	});
	
	// submit completed techsheet
	jQuery("#btn_comp_ts").click(function(){
	
		var item_tested = jQuery("#ts_items_tested").val();
		var property_leaks = jQuery(".property_leaks:checked").val();
		var leak_notes = jQuery("#leak_notes").val();
		var has_empty = false;
		var error = "";		
		
		if(item_tested==""){
			error += "Please enter item tested \n";
		}		
		
		if(jQuery("#cw_techconfirm").prop("checked")==false){
			error += "Please tick the confirmation box \n";
		}

		if( parseInt(property_leaks) == 1 && leak_notes == '' ){ // yes
			error += "Please note the location of the leak(s) \n";
		}

		// WE row radio required validation, radio is kinda tricky >.<
		jQuery("#cw_exist_tbl .we_tr").each(function(){

			var tr_row = jQuery(this);
			var we_pass_checked_count = tr_row.find(".update_pass:checked").length;

			if( we_pass_checked_count == 0 ){
				has_empty = true;
			}				

		});	

		if( has_empty == true ){
			error += "Toilet Type / Is water flow less than 9L per minute option is required \n";
		}

		// property leaks radio
		if( jQuery(".property_leaks:checked").length == 0 ){
			error += "Property Leak option is required\n";
		}

		
		
		if(error!=""){
			alert(error);
		}else{	
			jQuery("#btn_comp_ts_submit").val(1);
			jQuery("form#techsheetform").submit();
		}	
	
	});

	// propery leaks toggle script
	jQuery(".property_leaks").click(function(){

		var node = jQuery(this);
		var property_leaks = parseInt(node.val());

		if( property_leaks == 1 ){ // yes
			jQuery(".desc_leak_loc_span").show();
		}else{
			jQuery(".desc_leak_loc_span").hide();
		} 

	});

	// add property survey field on lightbox so it can be saved during saving WE	
	jQuery("#btn_add_entry").click(function(){

		var property_leaks = jQuery(".property_leaks:checked").val();		
		var leak_notes = jQuery(".leak_notes").val();

		jQuery("#property_leaks_hid").val(property_leaks);
		jQuery("#leak_notes_hid").val(leak_notes);

	});

});
</script>
