<?php

if(!defined('TECH_SHEET_INC'))
{
    exit();
}


// delete window
if (is_numeric($_GET['delwindow'])) {
	$query = "DELETE FROM `corded_window` WHERE `corded_window_id` = " . $_GET['delwindow'] . " AND job_id = " . $_GET['id'];
	mysql_query($query) or die(mysql_error());
}

//$upload_image_width = 760;
$upload_image_width = 500;

if($_GET['action']=='update'){

	$cw_edited = $_POST['cw_edited'];
	$corded_window_id = $_POST['corded_window_id'];
	$num_of_windows = $_POST['num_of_windows'];
	$location = $_POST['location'];
	
	foreach($cw_edited as $index=>$val){
		//edited
		if($val==1){
			mysql_query("
				UPDATE `corded_window`
				SET 
					`location` = '{$location[$index]}',
					`num_of_windows` = '{$num_of_windows[$index]}'
				WHERE `corded_window_id` = '{$corded_window_id[$index]}'
				AND `job_id` = '{$_GET['id']}'
			");
		}
	}
	
	$ts_items_tested = mysql_real_escape_string($_POST['ts_items_tested']);
	// update tickbox
	mysql_query("
		UPDATE `jobs`
		SET 
			`cw_items_tested` = '{$ts_items_tested}',
			`cw_techconfirm` = 1
		WHERE `id` = {$_GET['id']}
	");

}


// add new cw
if($_POST['new_cw_submitted']==1){


	$location = $_POST['new_location'];
	$num_of_windows = $_POST['new_num_of_windows'];
	
	foreach ($location as $index=>$loc){
		
		// dont upload if empty
		if( $loc!='' ){
									
			// store image path
			mysql_query("
				INSERT INTO 
				`corded_window`(
					`job_id`,
					`location`,
					`num_of_windows`
				)
				VALUES(
					{$job_id},
					'{$loc}',
					'{$num_of_windows[$index]}'					
				)
			");
			
		}
		
	}
	

}


?>
<script type="text/javascript">

    $(document).ready(function() {
	
	
		jQuery("#btn_comp_ts").click(function(){
		
		var item_tested = jQuery("#ts_items_tested").val();
		var error = "";		
		
		if(item_tested==""){
			error += "Please enter item tested \n";
		}		
		
		if(jQuery("#cw_techconfirm").prop("checked")==false){
				error += "Please tick the confirmation box \n";
		}
		
		if(error!=""){
			alert(error);
		}else{	
			jQuery("#btn_comp_ts_submit").val(1);
			jQuery("form#techsheetform").submit();
		}	
		
		});
	
    
        var _ROW_COUNTER = 0;

        // Set Variable for next item number
        var _NEXT_ITEM_NUMBER = <?php echo $next_item_number; ?>;

        updateCordedWindowCount();
    
        $("button#add_corded_window").live('click', function() {

            var error = "";

            // Retrieve form values and prepare to validate / submit
            var job_id = $("input#job_id").val();
			var new_location = $("#new_location").val();  
			var str = "";
			

            // Minimum we need is Type / Pass / Reason / Applianace
            if(new_location=="")
            {
                error += "Location is required\n";
            }
			

          

            if(error!="")
            {
                alert(error);
            }
            else
            {
			
			
			
				
			
				
                // Prepare Ajax Statement
                $.ajax({
                    type: "POST",
                    data: "job_id=" + job_id + 
							"&new_location=" + new_location,
                    url: "ajax/add_corded_window.php",
                    cache: false,
                    dataType: "json",
                    success: function(data){
						
						
					str += '<tr>';
					str += '<td>';
						str += '<input type="text" class="addinput cw_data" name="location[]" value="'+new_location+'" />';
					str += '</td>';	
					str += '<td>';
						str += '<img src="/images/camera_blue.png" /><br />';
						str += '<input type="file" capture="camera" accept="image/*" name="cw_image[]" class="cw_image" style="margin-top: 2px;" />';
						str += '<input type="hidden" name="cw_image_touched[]" class="cw_image_touched" value="" />';
					str += '</td>';
					
					str += '<td>';
						str += '<a href="?id=<?=$job_id;?>&delwindow='+data.data.alarm_id+'" onclick="return confirm(\'Are you sure you want to delete this corded window?\');" class="green">';
							str += 'Delete';
						str += '</a>';
					str += '</td>';
				str += '</tr>';
				
				
					jQuery("table#cw_exist_tbl tbody").append(str);
					var cw_count = parseInt(jQuery(".corded_window_count").html());
					jQuery(".corded_window_count").html(cw_count+1);					
					
					alert("New Windows Added");
				
						
                    }
                });
				
				
				
				
				
				
            }


            return false;
        });
    });

</script>


<?php
// get corded windows
$cw_sql = mysql_query("
	SELECT *
	FROM `corded_window`
	WHERE `job_id` ={$job_id}
")
?>


<input type="hidden" name="corded_window_count" value="<?=$num_existing_ss;?>" />
<? if(mysql_num_rows($cw_sql)==0){
?>
<div class="error" id="appliance_error">
    This Property has no Corded Windows on file. Please add Windows below.
</div>
<?php } ?>

<?php
if(mysql_num_rows($cw_sql)>0){ ?>

<table border=0 cellspacing=0 cellpadding=0 width=100% class="tech_table existing_corded_window" id="cw_exist_tbl">
	<thead>
	<tr>
		<td class="greenrow techsheet_header" colspan="10">Existing Corded Window Data</td>
	</tr>
	<tr>
		<td>Location</td>
		<td>Number of windows</td>
		<td style="border-right: 1px solid #ccc;">Delete</td>
	</tr>
	</thead>
	<tbody>
		<?php

		$x = 0;
		$tot_num_of_windows = 0;
		while($cw = mysql_fetch_array($cw_sql)){
			
			$tot_num_of_windows += $cw['num_of_windows'];

		?>
		<tr class="<?php echo $row_clr = ($x % 2 == 0 ? "grey" : "off"); ?>">		
			<td>
				<input type="text" class="addinput cw_data" name="location[]" value="<?php echo $cw['location']; ?>" />
			</td>	
			<td>
				<input type="text" class="addinput cw_data" name="num_of_windows[]" value="<?php echo $cw['num_of_windows']; ?>" />
			</td>
			<td style="border-right: 1px solid #ccc;">
				<input type="hidden" name="corded_window_id[]" class="corded_window_id" value="<?php echo $cw['corded_window_id'] ?>" />
				<input type="hidden" name="cw_edited[]" class="cw_edited" value="0" />
				<?php
				if($_GET['bundle_id']!=""){
					$url = "/view_job_details_tech.php?id={$job_id}&service={$_GET['service']}&bundle_id={$_GET['bundle_id']}&delwindow={$cw['corded_window_id']}&cw_del=1";
				}else{
					$url = "/view_job_details_tech.php?id={$job_id}&service={$_GET['service']}&ajt_id={$_GET['ajt_id']}&delwindow={$cw['corded_window_id']}&cw_del=1";
				}
				?>
				<a href="<?php echo $url; ?>" onclick="return confirm('Are you sure you want to delete this corded window?');" class="green">
					Delete
				</a>
			</td>
		</tr>
		<?php 
		$x++;
		} ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="10" class="greenrow techsheet_header" style="border: 1px solid #00AE4D;">Total Windows: <span class='corded_window_count_disabled'><?php echo $tot_num_of_windows; ?></span></td>
	</tr>
	</tfoot>
</table>
<input type="hidden" id="cw_touched_flag" name="cw_touched_flag" value="" />
<?php
}
?>

	
	
	<div style="margin-bottom: 9px;text-align: left;">
	<a class="inlineFB" href="#cw_window">
		<button type="button" class="submitbtnGreen submitbtnImg" id="btn_add_entry">ADD Window</button>
	</a>
	
	<button style="display:none;" type="button" id="cw_force_sync_button" class="submitbtnImg bluebutton">Sync CW</button>
	</div>
	
   
	
	

				
				
	<table border=0 cellspacing=0 cellpadding=5 width=98% class="tech_table">
		<tr class="row-green">
			<td class="greenrow techsheet_header">Technician</td>				
			<td class="greenrow techsheet_header">Date</td>
			<td class="greenrow techsheet_header">Items Tested</td>
			<td class="greenrow techsheet_header">Job Notes</td>
			<td class="greenrow techsheet_header">Property Notes</td>
		</tr>


		<tr class="grey">
			<td><?=$job_details['tech_first_name'];?> <?=$job_details['tech_last_name'];?></td>				
			<td class="sm-wdth"><input type="text" name="ts_signoffdate" value="<?=$job_details['ts_signoffdate'];?>" class="addinput inputauto"></td>
			<?php
			$cw_tested_sql = mysql_query("
				SELECT `cw_items_tested`
				FROM `jobs`
				WHERE `id` = {$_GET['id']}
			");
			$cw_tested = mysql_fetch_array($cw_tested_sql);
			?>
			<td class="sm-wdth"><input type="number" name="ts_items_tested" id="ts_items_tested"  value="<?=$cw_tested['cw_items_tested'];?>" class="addinput inputauto"></td>
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
		$tb_sql = mysql_query("
			SELECT `cw_techconfirm`
			FROM `jobs`
			WHERE `id` = {$_GET['id']}
		");
		$tb = mysql_fetch_array($tb_sql);
		
		if($serv2['bundle']==1){
			$tickbox = ( $tb['cw_techconfirm'] == 1 ) ? 'checked' : '';
		}else{
			$tickbox = ( $tb['cw_techconfirm'] == 1 && $job_details['ts_completed'] == 1 ) ? 'checked' : '';
		}
		?>
				<td class="vjdtch-row" colspan="4">
					<div><input type="checkbox" id="cw_techconfirm" class="required" name="cw_techconfirm" <?=$tickbox?> value="1"></div>
				<div><label for="cw_techconfirm">I confirm that all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.</label></div>
				</td>
				<td colspan="1">
					<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" id="job_id">
				<input type="hidden" name="tab" id="tab" value="<?php echo $job_tech_sheet_job_types[0]['html_id']; ?>-tab">
				<input type="hidden" name="btn_comp_ts_submit" id="btn_comp_ts_submit" value="0">				
				<button type="button" id="btn_comp_ts" class="submitbtnImg bluebutton">SUBMIT COMPLETED TECHSHEET</button>						
				</td>
			</tr>
		
	</table>
	
	


	
	
	<script>
	jQuery(document).ready(function(){	
	
	
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
	
	
	
	
		/*
		jQuery("#form_add_cw").on("change",".new_cw_image",function(){
			
			//alert('trigger');
			jQuery(this).parents("tr:first").removeClass("greenrow");
			jQuery(this).parents("tr:first").addClass("cwFileSelectedHL");
			
		});
		*/
	
	
		// save new CW
		jQuery("#btn_save_cw").click(function(){
			
			var error = '';
			var no_location_flag = 0;
			var no_image_flag = 0;
			
			// location
			jQuery(".new_location").each(function(){
				if(jQuery(this).val()==""){
					no_location_flag = 1;					
				}
			});
			
			if( no_location_flag == 1 ){
				error += "Location is required\n";
			}
			
			// image
			jQuery(".new_cw_image").each(function(){
				if(jQuery(this).val()==""){
					no_image_flag = 1;					
				}
			});
			
			if( no_image_flag == 1 ){
				error += "Image is required\n";
			}
			
			if(error!=''){
				jQuery("#new_cw_submitted").val(0);
				alert(error);
			}else{
				jQuery("#new_cw_submitted").val(1);
				// $.fancybox.close();
				jQuery("#uploading-screen").show();
				//jQuery("#uploading_image_gif").show();
				jQuery("#form_add_cw").submit();
			}
			
		});
	
		// add new CW
		jQuery("#btn_add_cw").click(function(){
			
			// needed to make the height auto
			jQuery("#fancybox-content").css('height','auto');
			
			// copy cw form fields
			var add_cw_form = jQuery("#cw_window .add_cw_tbody:last").clone();
			// clear grey shade
			add_cw_form.find("tr").removeClass("cwFileSelectedHL");
			// clear fields
			add_cw_form.find(".new_location").val('');
			add_cw_form.find(".new_num_of_windows").val('');
			// insert new row
			add_cw_form.insertAfter("#cw_window .add_cw_tbody:last");
			
			// check for not empty and highlight it grey
			jQuery("#form_add_cw tr.greenrow input[type='text'], #form_add_cw tr.greenrow input[type='file']").each(function(){
				
				if( jQuery(this).val()!='' ){
					jQuery(this).parents("tr:first").addClass("cwFileSelectedHL");
				}
				
				
			});
			
			
			
			
			/*
			var str = '<tr class="greenrow">'+
						'<td>Location</td>'+
						'<td>'+
							'<input type="text" class="addinput new_location" name="new_location[]" id="new_location" />'+
						'</td>'+
					'</tr>'+
					'<tr class="greenrow">'+					
						'<td><img src="/images/camera_white.png" class="camera_white" /> Take Photo</td>'+
						'<td>'+
							'<input type="file" style="color: black;" capture="camera" accept="image/*" name="new_cw_image[]" class="addinput new_cw_image" />'+
						'</td>'+
					'</tr>';
			
			jQuery("#tbl_add_cw tbody").append(str);
			*/
			
		});
		
	
		
		
		
		
		
		
		
	
		// invoke fancybox
		jQuery('.fancybox').fancybox();
		
		// mark corded window as touched
		jQuery(".cw_image").change(function(){		
			jQuery(this).parents("tr:first").find(".cw_image_touched").val(1);	
			jQuery("#cw_touched_flag").val(1);
		});
	
		// marked as edited
		jQuery(".cw_data").change(function(){
		  jQuery(this).parents("tr:first").find(".cw_edited").val(1);
		});
	
		/*
		jQuery("#btn_comp_ts").click(function(){
			jQuery("#btn_comp_ts_submit").val(1);
			$("form#techsheetform").submit();			
		});
		*/
	
		/*
		jQuery("#btn_add_entry").click(function(){
			jQuery("#add_entry_tbl").show();
		});
		*/
	});
	</script>
