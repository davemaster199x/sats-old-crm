<?php

$title = "Agent Documents";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$filter_state = $_POST['filter_state'];

function jgetResourceList($header_id,$filter_state){
	
	$str = ($filter_state!="")?" AND r.`states` LIKE '%{$filter_state}%' ":"";
	
	$sql = "
		SELECT *, rh.`name` AS h_name
		FROM `resources` AS r	
		LEFT JOIN `resources_header` AS rh ON r.`resources_header_id` = rh.`resources_header_id`
		WHERE r.`country_id` = {$_SESSION['country_default']}
		AND rh.`resources_header_id` = {$header_id}
		{$str}
		ORDER BY r.`states` ASC
	";
	
	return mysql_query($sql);
}

function jgetResourceHeader(){

	
	$sql = "
		SELECT *
		FROM `resources_header` 
		WHERE `country_id` = {$_SESSION['country_default']}
		AND `status` = 1
		ORDER BY `name` ASC		
	";
	
	return mysql_query($sql);
}

?>
<style>
.jalign_left{
	text-align:left;
}
.res_hid_data, .showOnEdit, .showOnEditHeader{
	display:none;
}
</style>

<link rel="stylesheet" type="text/css" href="/jquery_multiselect/css/jquery.multiselect.css" />
<script type="text/javascript" src="/jquery_multiselect/js/jquery.multiselect.js"></script>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Agent Documents</strong></a></li>
		  </ul>
		</div>
		
		
		<?php echo ($_GET['success']==1)?'<div class="success">New Document Added</div>':''; ?>
		<?php echo ($_GET['success']==2)?'<div class="success">Add Header Successful</div>':''; ?>
		<?php echo ($_GET['success']==3)?'<div class="success">Header Update Successful</div>':''; ?>
		<?php echo ($_GET['update']==1)?'<div class="success">Update Successful</div>':''; ?>
		<?php echo ($_GET['del']==1)?'<div class="success">Delete Successful</div>':''; ?>
		<?php echo ($_GET['del']==2)?'<div class="success">Header Delete Successful</div>':''; ?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<div style="margin: 40px 0 0;text-align: left;">
			<h2 class="heading">IMPORTANT</h2>
			<ul>
				<li>All Documents on this page are displayed on agency site. The title used will also be used on the agency site, so please pick the title carefully.</li>						
			</ul>
		</div>
		
		<div style="border: 1px solid #ccc;" class="aviw_drop-h">	
		
			<form method="post">
			<div class="fl-left" style="float: left;">
				<label>State:</label>
				<select name="filter_state" id="filter_state" class="filter_state">
				<option value="">----</option>
				<?php
				$states = getCountryState();
				$sel_states = explode(",",$row['states']);
				while($data =  mysql_fetch_array($states)){ ?>
					<option value="<?php echo $data['StateID']; ?>"><?php echo $data['state']; ?></option>
				<?php	
				}
				?>												
			</select>
			</div>

			<div class="fl-left" style="float: left;">
				<input type="submit" name="btn_filter_state" value="Go" class="submitbtnImg">           
			</div>
			</form>
			
		</div>
		
		<?php
		$sql_doc_header = mysql_query("
			SELECT DISTINCT rh.`resources_header_id`, rh.name
			FROM `resources` AS r	
			LEFT JOIN `resources_header` AS rh ON r.`resources_header_id` = rh.`resources_header_id`
			WHERE r.`country_id` = {$_SESSION['country_default']}
			{$str}
			ORDER BY rh.`name` ASC
		");
		while( $doc_header = mysql_fetch_array($sql_doc_header) ){ ?>
		
		<h2 class="heading"><?php echo $doc_header['name']; ?></h2>
		
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
		<tr class="toprow jalign_left">
			<th style="width:350px;">Document Name/URL</th>
			<th style="width:350px;">Title on Agency Site</th>
			<?php
			if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
				<th style="width:230px;">State</th>
			<?php	
			}
			?>	
			<th>Due Date</th>
			<th>Last Updated</th>
			<th class="showOnEditHeader">Header</th>
			<th>Edit</th>
		</tr>
			<?php
			$sql = jgetResourceList($doc_header['resources_header_id'],$filter_state);
			if(mysql_num_rows($sql)>0){
				while($row = mysql_fetch_array($sql)){
			?>
					<tr class="body_tr jalign_left">
						<td>
							<input type="hidden" class="resources_id" value="<?php echo $row['resources_id']; ?>" />
							<input type="hidden" class="type" value="<?php echo $row['type']; ?>" />
							<?php
							if($row['type']==1){ ?>
								<input type="hidden" class="del_path" value="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>" />
								<a href="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>"><?php echo $row['filename']; ?></a>
							<?php	
							}else{ ?>
								<a href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
							<?php	
							}
							?>
							
						</td>
						<td>
							<span class="res_lbl"><?php echo $row['title']; ?></span>
							<input type="text" class="res_hid_data edit_title" value="<?php echo $row['title']; ?>" />
						</td>
						<?php 
						if(ifCountryHasState($_SESSION['country_default'])==true){ 
						?>
							<td>									
								<div class="row">
									<span class="res_lbl">
									<?php
										if($row['states']!=""){
											$s_sql = mysql_query("
												SELECT *
												FROM `states_def`
												WHERE `StateID` IN({$row['states']})
											");
											$s_arr = array();
											while($s = mysql_fetch_array($s_sql)){
												$s_arr[] = $s['state'];
											}
											echo implode(',',$s_arr);
										}else{
											echo "N/A";
										}																						
									?>
									</span>
									<div class="res_hid_data">
										<select id="edit_state" class="multi_select edit_state" multiple="multiple">
											<option value="">----</option>
											<?php
											$states = getCountryState();
											$sel_states = explode(",",$row['states']);
											while($data =  mysql_fetch_array($states)){ ?>
												<option value="<?php echo $data['StateID']; ?>" <?php echo (in_array($data['StateID'],$sel_states))?'selected="selected"':''; ?>><?php echo $data['state']; ?></option>
											<?php	
											}
											?>												
										</select>
									</div>										
								</div>
							</td>						
						<?php	
						}
						?>						
						<td>
							<?php 
							$due_date = ($crm->isDateNotEmpty($row['due_date'])==true)?$crm->formatDate($row['due_date'],'d/m/Y'):''
							?>
							<span class="res_lbl">
								<?php echo $due_date; ?>
							</span>
							<input type="text" class="res_hid_data datepicker edit_due_date" value="<?php echo $due_date; ?>" />
						</td>
						<td><?php echo ($crm->isDateNotEmpty($row['date'])==true)?$crm->formatDate($row['date'],'d/m/Y'):''; ?></td>
						<td class="showOnEdit">									
							<div class="row">
								<span class="res_lbl"><?php echo $row['name']; ?></span>
								<div class="res_hid_data">
									<select id="edit_heading" class="edit_heading">
										<option value="">----</option>	
										<?php
										$header_sql = jgetResourceHeader();
										while( $header =  mysql_fetch_array($header_sql) ){ ?>
											<option value="<?php echo $header['resources_header_id']; ?>" <?php echo ($header['resources_header_id']==$row['resources_header_id'])?'selected="selected"':''; ?>><?php echo $header['name']; ?></option>
										<?php	
										}
										?>	
									</select>
								</div>										
							</div>
						</td>	
						<td>
							<a href="javascript:void(0);" class="btn_edit">Edit</a>
							<div style="display:none;" class="btn_edit_div">
								<button class="blue-btn submitbtnImg btn_update" style="display: inline-block;">Update</button>
								<button style="" class="submitbtnImg btn_cancel">Cancel</button>
								<button style="" class="submitbtnImg btn_delete">Delete</button>
							</div>								
						</td>
					<tr>
			<?php
				}
			}else{ ?>
				<td colspan="100%" align="left">Empty, no documents uploaded yet</td>
			<?php
			}
			?>
		</table>
		
		<?php	
		}
		?>
		
		<div class="jalign_left">
		
			<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
			
			<div style="padding-top: 20px; display:none;" id="div_add_res" class="addproperty formholder">
			
			<ul style="list-style: outside none none; margin-bottom: 25px;">
				<li>
					<input type="radio" name="upload_option" id="res_opt_1" value="1" style="float: left; width: auto; margin-right: 10px;" /> <label>Upload File</label>
				</li>
				<li>
					<input type="radio" name="upload_option" id="res_opt_2" value="2" style="float: left; width: auto; margin-right: 10px;" /> <label>Upload Link</label>
				</li>
			</ul>
			
			
			
		<form id="form_upload_file" method="post" action="/resources_script.php" enctype="multipart/form-data" style="display:none;">
			<div class="row">
			<label class="addlabel" for="file">File</label>
			<input type="file" name="file" id="file" class="file uploadfile submitbtnImg">
			</div>
			<div class="row">
			<label class="addlabel" for="title">Title</label>
			<input type="text" name="title" id="title" class="title">
			</div>
			
			<div class="row">
				<label class="addlabel" for="heading">Heading</label>
				<select name="heading" id="heading">
					<option value="">----</option>
					<?php
					// get header
					$h_sql = getResourcesHeaders();
					while($h = mysql_fetch_array($h_sql)){ ?>
						<option value="<?php echo $h['resources_header_id']; ?>"><?php echo $h['name']; ?></option>
					<?php
					}
					?>
				</select>            
			</div>
			<?php
			if(ifCountryHasState($_SESSION['country_default'])==true){
			$states = getCountryState(); ?>
			<div class="row">
				<label class="addlabel">States</label>
				<div class="vsud-inner">
					<? while($data =  mysql_fetch_array($states)){ ?>
						<input type="checkbox"  name="states[]" class="states" value="<?=$data['StateID'];?>">
						<label for="<?=$data['StateID'];?>" class="statelabel"><?=$data['state'];?></label>   
					<? } ?> 
					<input type="checkbox" id="state_all" class="state_all" />
					<label for="" class="statelabel">ALL</label>
				</div>
			</div>
			<?php
			}		
			?>
			
			<div class="row">
				<label class="addlabel" for="due_date">Due Date</label>
				<input type="text" name="due_date" class="due_date datepicker" />
			</div>

			<div style="padding-top: 15px; text-align:left;" class="row clear">
			<button type="button" class="submitbtnImg" id="btn_upload_file">Upload</button>
			</div>
		</form>
		
		<form id="form_upload_link" method="post" action="/resources_upload_link.php" enctype="multipart/form-data" style="display:none;">
			<div class="row">
			<label class="addlabel" for="file">URL</label>
			<input type="text" name="url" id="url" class="url" />
			</div>
			<div class="row">
			<label class="addlabel" for="title">Title</label>
			<input type="text" name="title" id="title" class="title" />
			</div>
			<div class="row">
			<label class="addlabel" for="heading">Heading</label>
			<select name="heading" id="heading">
								<option value="">----</option>
								<?php
								// get header
								$h_sql = getResourcesHeaders();
								while($h = mysql_fetch_array($h_sql)){ ?>
									<option value="<?php echo $h['resources_header_id']; ?>"><?php echo $h['name']; ?></option>
								<?php
								}
								?>
							</select>            
			</div>
			<?php
			if(ifCountryHasState($_SESSION['country_default'])==true){
			$states = getCountryState(); ?>
			<div class="row">
				<label class="addlabel">States</label>
				<div class="vsud-inner">
					<? while($data =  mysql_fetch_array($states)){ ?>
						<input type="checkbox"  name="states[]" class="states" value="<?=$data['StateID'];?>">
						<label for="<?=$data['StateID'];?>" class="statelabel"><?=$data['state'];?></label>   
					<? } ?>  
					<input type="checkbox" id="state_all" class="state_all" />
					<label for="" class="statelabel">ALL</label>
				</div>
			</div>
			<?php
			}		
			?>

			<div class="row">
				<label class="addlabel" for="due_date">Due Date</label>
				<input type="text" name="due_date" class="due_date datepicker" />
			</div>

			<div style="padding-top: 15px; text-align:left;" class="row clear">
			<button type="button" class="submitbtnImg" id="btn_upload_link">Upload</button>
			</div>
		</form>
		
	</div>
			
		</div>	



		<div class="jalign_left" style="margin-top: 12px;">
		
			<button type="button" id="btn_add_edit_header" class="submitbtnImg blue-btn">Add/Edit Heading</button>
					
			<div id="header_div" style="display:none;">
			
				<?php		
					// get admin doc header
					$h_sql = getResourcesHeaders();
				if(mysql_num_rows($h_sql)>0){ ?>
					<form method="post" action="/resources_update_header.php">
                    <style>#pm_table tr td{ border: 1px solid transparent;}</style>
						<table id="pm_table" style="width: auto; margin-top: 15px; margin-bottom: 15px;">
							<tbody>
							<?php						
							while($h = mysql_fetch_array($h_sql)){ ?>
								<tr>
									<td>
										<input type="hidden" class="rh_id" name="rh_id[]"  value="<?php echo $h['resources_header_id']; ?>">
										<input type="text" class="fname pm_name" name="edit_name[]" value="<?php echo $h['name']; ?>">	
									</td>
									<td>
										<button type="button" class="submitbtnImg btn_del_rh">X</button>
									</td>
								</tr>
							<?php
							}
							?>										
							</tbody>	
						</table>
						<?php 
						if(mysql_num_rows($h_sql)>0){ ?>
							<input type="submit" class="submitbtnImg blue-btn" style="width: auto; margin-bottom: 50px;" name="btn_update_sr" value="Update" />
						<?php
						}
						?>					
					</form>
				<?php
				}else{
					echo '<div class="jalign_left" style="margin: 17px 0;">Empty</div>';
				}
				?>		

				<br />

				<button type="button" id="btn_add_header" class="submitbtnImg">Add Heading</button>
				
				<div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
					<form id="form_add_header" method="post" action="/resources_add_header.php" style="display:none;">
						<div class="row">
							<label class="addlabel" for="title">Heading Name</label>
							<input type="text" name="name" id="name" class="name">
						</div>         									
						<div style="padding-top: 15px; text-align:left;" class="row clear">
							<button type="button" class="submitbtnImg btn_header_submit" id="btn_header_submit" style="width: auto; margin-bottom: 50px;">Submit</button>
						</div>
					</form>
				</div>
				
			</div>
			
		</div>

		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_edit_div").show();
		jQuery(this).parents("tr:first").find(".res_hid_data").show();
		jQuery(this).parents("tr:first").find(".res_lbl").hide();
		
		jQuery(this).parents("table:first").find(".showOnEditHeader").show();
		jQuery(this).parents("table:first").find(".showOnEdit").show();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_edit_div").hide();
		jQuery(this).parents("tr:first").find(".res_hid_data").hide();
		jQuery(this).parents("tr:first").find(".res_lbl").show();
		
		jQuery(this).parents("table:first").find(".showOnEditHeader").hide();
		jQuery(this).parents("table:first").find(".showOnEdit").hide();
	
	});	
	
	jQuery(".btn_update").click(function(){
		
		var resources_id = jQuery(this).parents("tr:first").find(".resources_id").val();
		var title = jQuery(this).parents("tr:first").find(".edit_title").val();
		var heading = jQuery(this).parents("tr:first").find(".edit_heading").val();
		var state = jQuery(this).parents("tr:first").find(".edit_state").val();
		var due_date = jQuery(this).parents("tr:first").find(".edit_due_date").val();
		
		jQuery.ajax({
			type: "POST",
			url: "ajax_update_resources.php",
			data: { 
				resources_id: resources_id,
				title: title,
				heading: heading,
				state: state,
				due_date: due_date
			}
		}).done(function( ret ) {	
			window.location='/resources.php?update=1';
		});		
		
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var resources_id = jQuery(this).parents("tr:first").find(".resources_id").val();
			var type = jQuery(this).parents("tr:first").find(".type").val();
			var del_path = jQuery(this).parents("tr:first").find(".del_path").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_resources.php",
				data: { 
					resources_id: resources_id,
					type: type,
					del_path: del_path
				}
			}).done(function( ret ) {	
				window.location='/resources.php?del=1';
			});	
		}				
	});

	jQuery("#btn_upload").click(function(){
	
		var file = jQuery("#file").val();
		var title = jQuery("#title").val();
		var heading = jQuery("#heading").val();
		var states = [];
		jQuery(".states:checked").each(function(){
			states.push(jQuery(this).val());
		});
		var error = "";
		
		if(file==""){
			error += "Please select file to upload\n";
		}
		if(title==""){
			error += "Title is required\n";
		}
		if(heading==""){
			error += "Heading is required\n";
		}
		<?php
		if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
			if(states.length==0){
				error += "Must select at least one state";
			}
		<?php
		}
		?>		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_upload_file").submit();
		}
		
	});
	
	// add new toggle
	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#div_add_res").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#div_add_res").slideUp();
	});
	
	// heading show/hide form toggle
	jQuery("#btn_add_header").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_add_header").slideDown();
	},function(){
		jQuery(this).html("Add Heading");		
		jQuery("#form_add_header").slideUp();
	});
	
	// option
	jQuery("#res_opt_1").click(function(){
		
		jQuery("#form_upload_file").slideDown();
		jQuery("#form_upload_link").slideUp();
		
	});
	jQuery("#res_opt_2").click(function(){
		
		jQuery("#form_upload_link").slideDown();
		jQuery("#form_upload_file").slideUp();
		
	});
	
	// heading toggle
	jQuery("#btn_add_edit_header").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#header_div").slideDown();
	},function(){
		jQuery(this).html("Add/Edit Heading");		
		jQuery("#header_div").slideUp();
	});
	
	// upload file
	jQuery("#btn_upload_file").click(function(){
	
		var file = jQuery("#form_upload_file .file").val();
		var title = jQuery("#form_upload_file .title").val();		
		var error = "";
		
		if(file==""){
			error += "Please select file to upload \n";
		}
		if(title==""){
			error += "Title is required \n";
		}
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_upload_file").submit();
		}
		
	});		
	
	// upload link
	jQuery("#btn_upload_link").click(function(){
	
		var url = jQuery("#form_upload_link .url").val();
		var title = jQuery("#form_upload_link .title").val();		
		var error = "";
		
		if(url==""){
			error += "Please enter URL \n";
		}
		if(title==""){
			error += "Title is required \n";
		}
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_upload_link").submit();
		}
		
	});
	
	
	// add header validation
	jQuery("#btn_header_submit").click(function(){
	
		var name = jQuery("#form_add_header input#name").val();
		var error = "";
		
		if(name==""){
			error += "Please Enter Header";
		}

		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_add_header").submit();
		}
		
	});
	
	
	// delete header
	jQuery(".btn_del_rh").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var rh_id = jQuery(this).parents("tr:first").find(".rh_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_resources_header.php",
				data: { 
					rh_id: rh_id
				}
			}).done(function( ret ) {	
				window.location='/resources.php?del=2';
			});	
		}				
	});
	
	// states check all toggle
	jQuery(".state_all").click(function(){
	  if(jQuery(this).prop("checked")==true){
		jQuery(this).parents(".vsud-inner").find(".states").prop("checked",true);
	  }else{
		jQuery(this).parents(".vsud-inner").find(".states").prop("checked",false);
	  }
	});
	
	// multi select script
	jQuery(".multi_select").multiselect({
		//noneSelectedText: "Any"
	});
	jQuery("div.ui-widget-header").css("background","#b4151b");

	
});
</script>
</body>
</html>