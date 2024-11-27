<?php

$title = "Technician Documents";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getTechDocHeader(){
	return mysql_query("
		SELECT *
		FROM `tech_doc_header`
		WHERE `country_id` = {$_SESSION['country_default']}
	");	
}

?>
<style>
.jalign_left{
	text-align:left;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Technician Documents" href="/tech_doc.php"><strong>Technician Documents</strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php
		if($_GET['success']==1){
			echo '<div class="success">New Documents Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Header Successfully Added</div>';
		}else if($_GET['success']==3){
			echo '<div class="success">Header Successfully Updated</div>';
		}
		
		
		if($_GET['del']==1){
			echo '<div class="success">Tech Documents Deleted</div>';
		}else if($_GET['del']==2){
			echo '<div class="success">Header Deleted</div>';
		}
		?>
		
			<?php
		$tdh_sql = mysql_query("
			SELECT DISTINCT td.`tech_doc_header_id`, tdh.`name`
			FROM `technician_documents` AS td		
			LEFT JOIN `tech_doc_header` AS tdh ON tdh.`tech_doc_header_id` = td.`tech_doc_header_id`
			WHERE tdh.`country_id` = {$_SESSION['country_default']}
			ORDER BY tdh.`name`
		");
		if(mysql_num_rows($tdh_sql)>0){
			while($td = mysql_fetch_array($tdh_sql)){ ?>
			
				<h2 class="heading"><?php echo strtoupper($td['name']); ?></h2>
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
					<tr class="toprow jalign_left">
						<th style="width: 400px;">Document Name</th>
						<th style="width: 300px;">Header</th>
						<th style="width: 250px;">Title</th>
						<th>Uploaded</th>
						<th>Delete</th>
					</tr>
						<?php
						$sql = mysql_query("
							SELECT *
							FROM `technician_documents` AS td		
							LEFT JOIN `tech_doc_header` AS tdh ON tdh.`tech_doc_header_id` = td.`tech_doc_header_id`
							WHERE tdh.`country_id` = {$_SESSION['country_default']}
							AND tdh.`tech_doc_header_id` = {$td['tech_doc_header_id']}
							ORDER BY tdh.`name`
						");
						if(mysql_num_rows($sql)>0){
							while($row = mysql_fetch_array($sql)){
						?>
								<tr class="body_tr jalign_left">
									<td>
										<input type="hidden" class="tech_doc_id" value="<?php echo $row['technician_documents_id']; ?>" />															
										<?php
										if($row['type']==1){ ?>
											<input type="hidden" class="del_file" value="<?php echo $row['filename']; ?>" />
											<a href="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>"><?php echo $row['filename']; ?></a>
										<?php	
										}else{ ?>
											<a href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
										<?php	
										}
										?>							
									</td>
									<td><?php echo $row['name'] ?></td>
									<td><?php echo $row['title'] ?></td>							
									<td><?php echo date("d/m/Y",strtotime($row['date'])) ?></td>
									<td>
										<a href="javascript:void(0);" class="btn_del_vf btn_delete">Delete</a>
									</td>
								<tr>
						<?php
							}
						}else{ ?>
							<td colspan="5" align="left">Empty, no documents uploaded yet</td>
						<?php
						}
						?>
				</table>
				
			<?php
			}
		}
		?>
		
		
		
		<div class="jalign_left">
		
		<button type="button" id="btn_add_new" class="submitbtnImg" style="margin-bottom: 12px;">Add New</button>

		<div style="padding-top: 20px; display:none;" id="div_add_res" class="addproperty formholder">
		
			<ul style="list-style: outside none none; margin-bottom: 25px;">
				<li>
					<input type="radio" name="upload_option" id="res_opt_1" value="1" style="float: left; width: auto; margin-right: 10px;" /> <label>Upload File</label>
				</li>
				<li>
					<input type="radio" name="upload_option" id="res_opt_2" value="2" style="float: left; width: auto; margin-right: 10px;" /> <label>Upload Link</label>
				</li>
			</ul>
				
			<div style="padding-top: 20px; display:none;" id="div_upload_file" class="addproperty formholder">
				<form id="form_tech_document" method="post" action="/tech_doc_script.php" enctype="multipart/form-data">
					<div class="row">
					<label class="addlabel" for="title">Heading</label>
						<select name="header">
							<option value="">----</option>
							<?php
							// get tech doc header
							$tdh_sql = getTechDocHeader();

							while($tdh = mysql_fetch_array($tdh_sql)){ ?>
								<option value="<?php echo $tdh['tech_doc_header_id']; ?>"><?php echo $tdh['name']; ?></option>
							<?php
							}
							?>
						</select>
					</select>
					</div> 
					<div class="row">
						<label class="addlabel" for="file">File</label>
						<input type="file" name="file" id="file" class="file uploadfile submitbtnImg">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Title</label>
						<input type="text" name="title" id="title" class="title">
					</div>         				
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<button type="button" class="submitbtnImg" id="btn_upload_file">Upload</button>
					 </div>
				</form>
			</div>	
			
			
			<div style="padding-top: 20px; display:none;" id="div_add_link" class="addproperty formholder">
				<form id="form_tech_document_add_link" method="post" action="/tech_doc_add_link.php">
					<div class="row">
					<label class="addlabel" for="title">Heading</label>
						<select name="header">
							<option value="">----</option>
							<?php
							// get tech doc header
							$tdh_sql = getTechDocHeader();

							while($tdh = mysql_fetch_array($tdh_sql)){ ?>
								<option value="<?php echo $tdh['tech_doc_header_id']; ?>"><?php echo $tdh['name']; ?></option>
							<?php
							}
							?>
						</select>
					</select>
					</div> 
					<div class="row">
						<label class="addlabel" for="url">URL</label>
						<input type="text" name="url" id="url" class="url" />
					</div>
					<div class="row">
						<label class="addlabel" for="title">Title</label>
						<input type="text" name="title" id="title" class="title">
					</div>         				
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<button type="button" class="submitbtnImg" id="btn_upload_link">Upload</button>
					 </div>
				</form>
			</div>

		</div>

		<div class="jalign_left">
		
			<button type="button" id="btn_add_edit_header" class="submitbtnImg blue-btn">Add/Edit Heading</button>
					
			<div id="header_div" style="display:none;">
			
				<?php		
					// get tech doc header
					$tdh_sql = getTechDocHeader();
				if(mysql_num_rows($tdh_sql)>0){ ?>
					<form method="post" action="/tech_doc_update_header.php">
                    <style>#pm_table tr td{ border: 1px solid transparent;}</style>
						<table id="pm_table" style="width: auto; margin-top: 15px; margin-bottom: 15px;">
							<tbody>
							<?php						
							while($tdh = mysql_fetch_array($tdh_sql)){ ?>
								<tr>
									<td>
										<input type="hidden" class="tdh_id" name="tdh_id[]"  value="<?php echo $tdh['tech_doc_header_id']; ?>">
										<input type="text" class="fname pm_name" name="edit_name[]" value="<?php echo $tdh['name']; ?>">	
									</td>
									<td>
										<button type="button" class="submitbtnImg btn_del_sr">X</button>
									</td>
								</tr>
							<?php
							}
							?>										
							</tbody>	
						</table>
						<?php 
						if(mysql_num_rows($tdh_sql)>0){ ?>
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
					<form id="form_add_header" method="post" action="/tech_doc_add_header.php" style="display:none;">
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
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	// option
	jQuery("#res_opt_1").click(function(){
		
		jQuery("#div_upload_file").slideDown();
		jQuery("#div_add_link").slideUp();
		
	});
	jQuery("#res_opt_2").click(function(){
		
		jQuery("#div_add_link").slideDown();
		jQuery("#div_upload_file").slideUp();
		
	});
	
	// add new toggle
	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#div_add_res").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#div_add_res").slideUp();
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
	jQuery(".btn_del_sr").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var tdh_id = jQuery(this).parents("tr:first").find(".tdh_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_tech_doc_header.php",
				data: { 
					tdh_id: tdh_id
				}
			}).done(function( ret ) {	
				window.location='/tech_doc.php?del=2';
			});	
		}				
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var tech_doc_id = jQuery(this).parents("tr:first").find(".tech_doc_id").val();
			var del_file = jQuery(this).parents("tr:first").find(".del_file").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_tech_doc.php",
				data: { 
					tech_doc_id: tech_doc_id,
					del_file: del_file
				}
			}).done(function( ret ) {	
				window.location='/tech_doc.php?del=1';
			});	
		}				
	});

	jQuery("#btn_upload_file").click(function(){
	
		var file = jQuery("#form_tech_document #file").val();
		var title = jQuery("#form_tech_document #title").val();		
		var error = "";
		
		if(file==""){
			error += "Please select file to upload\n";
		}
		if(title==""){
			error += "Title is required\n";
		}
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_tech_document").submit();
		}
		
	});
	
	// upload link
	jQuery("#btn_upload_link").click(function(){
	
		var url = jQuery("#form_tech_document_add_link #url").val();
		var title = jQuery("#form_tech_document_add_link #title").val();		
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
			jQuery("#form_tech_document_add_link").submit();
		}
		
	});

	/*
	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_tech_document").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_tech_document").slideUp();
	});
	*/
	
	// main heading show/hide form toggle
	jQuery("#btn_add_edit_header").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#header_div").slideDown();
	},function(){
		jQuery(this).html("Add/Edit Heading");		
		jQuery("#header_div").slideUp();
	});
	
	// heading show/hide form toggle
	jQuery("#btn_add_header").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_add_header").slideDown();
	},function(){
		jQuery(this).html("Add Heading");		
		jQuery("#form_add_header").slideUp();
	});
	
	
});
</script>
</body>
</html>