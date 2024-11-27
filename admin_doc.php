<?php

$title = "Admin Documents";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getAdminDocHeader(){
	return mysql_query("
		SELECT *
		FROM `admin_doc_header`
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
			<li class="other first"><a title="Admin Documents" href="/admin_doc.php"><strong>Admin Documents</strong></a></li>
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
			echo '<div class="success">Admin Documents Deleted</div>';
		}else if($_GET['del']==2){
			echo '<div class="success">Header Deleted</div>';
		}
		
		
		
		

		$adh_sql = mysql_query("
			SELECT DISTINCT tdh.`admin_doc_header_id`, tdh.name
			FROM `admin_documents` AS td		
			LEFT JOIN `admin_doc_header` AS tdh ON tdh.`admin_doc_header_id` = td.`admin_doc_header_id`
			WHERE tdh.`country_id` = {$_SESSION['country_default']}
			ORDER BY tdh.`name`
		");
		while($adh = mysql_fetch_array($adh_sql)){ ?>
			<h2 class="heading"><?php echo $adh['name'] ?></h2>
			<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
				<tr class="toprow jalign_left">
					<th style="width: 40%;">Document Name</th>
					<th style="width: 30%;">Title</th>
					<th>Uploaded</th>
					<th>Delete</th>
				</tr>
					<?php
					$sql = mysql_query("
						SELECT *
						FROM `admin_documents` AS td		
						LEFT JOIN `admin_doc_header` AS tdh ON tdh.`admin_doc_header_id` = td.`admin_doc_header_id`
						WHERE tdh.`country_id` = {$_SESSION['country_default']}
						AND td.`admin_doc_header_id` = {$adh['admin_doc_header_id']} 
					");
					if(mysql_num_rows($sql)>0){
						while($row = mysql_fetch_array($sql)){
					?>
							<tr class="body_tr jalign_left">
								<td>
									<input type="hidden" class="admin_doc_id" value="<?php echo $row['admin_documents_id']; ?>" />
									<input type="hidden" class="del_file" value="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>" />
                                    
                                    <?php
                                    if($row['type']==2){
                                    ?>
                                        <a href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
                                   <?php }else{ ?>
                                        <a href="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>"><?php echo $row['filename']; ?></a>
                                   <?php } ?>
									
                                    
                                    
								</td>
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
		?>
		
		
		
		
		
		<div class="jalign_left">
		
		<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
		<div style="padding-top: 20px;display:none;" id="div_staff" class="addproperty formholder div_add_res">
            
            
            <ul style="list-style: outside none none; margin-bottom: 25px;">
				<li>
					<input type="radio" name="upload_option" id="res_opt_1" value="1" style="float: left; width: auto; margin-right: 10px;"> <label>Upload File</label>
				</li>
				<li>
					<input type="radio" name="upload_option" id="res_opt_2" value="2" style="float: left; width: auto; margin-right: 10px;"> <label>Upload Link</label>
				</li>
			</ul>
            
            
            
            <!-- UPLOAD FILE FORM -->
			<form id="form_admin_document" method="post" action="/admin_doc_script.php" enctype="multipart/form-data" style="display:none;">
				<div class="row">
				<label class="addlabel" for="title">Heading</label>
					<select name="header">
						<option value="">----</option>
						<?php
						// get admin doc header
						$tdh_sql = getAdminDocHeader();

						while($tdh = mysql_fetch_array($tdh_sql)){ ?>
							<option value="<?php echo $tdh['admin_doc_header_id']; ?>"><?php echo $tdh['name']; ?></option>
						<?php
						}
						?>
					</select>
				</select>
				</div> 
				<div class="row">
					<label class="addlabel" for="file">File</label>
					<input type="file" name="file" id="file" class="fname uploadfile submitbtnImg">
				</div>
				<div class="row">
					<label class="addlabel" for="title">Title</label>
					<input type="text" name="title" id="title" class="fname">
				</div>         				
				<div style="padding-top: 15px; text-align:left;" class="row clear">
					<button type="button" class="submitbtnImg" id="btn_upload">Upload</button>
				 </div>
			</form>
            <!-- UPLOAD FILE FORM END -->
            
             <!-- UPLOAD LINK FORM -->
            <form id="form_admin_doc_upload_link" method="post" action="/admin_doc_upload_link.php" enctype="multipart/form-data" style="display: none;">
                 <div class="row">
                    <label class="addlabel" for="heading">Heading</label>
                    <select name="header">
						<option value="">----</option>
						<?php
						// get admin doc header
						$tdh_sql = getAdminDocHeader();

						while($tdh = mysql_fetch_array($tdh_sql)){ ?>
							<option value="<?php echo $tdh['admin_doc_header_id']; ?>"><?php echo $tdh['name']; ?></option>
						<?php
						}
						?>
					</select>           
                </div>
                <div class="row">
                    <label class="addlabel" for="file">URL</label>
                    <input type="text" name="url" id="url" class="url">
                </div>
                <div class="row">
                    <label class="addlabel" for="title">Title</label>
                    <input type="text" name="title" id="title_link" class="title">
                </div>
               
              


                <div style="padding-top: 15px; text-align:left;" class="row clear">
                <button type="button" class="submitbtnImg" id="btn_upload_link">Upload</button>
                </div>
		</form>
        <!-- UPLOAD LINK FORM END -->
            
            
		</div>	



		<div class="jalign_left" style="margin-top:15px;">
		
			<button type="button" id="btn_add_edit_header" class="submitbtnImg blue-btn">Add/Edit Heading</button>
					
			<div id="header_div" style="display:none;">
			
				<?php		
					// get admin doc header
					$tdh_sql = getAdminDocHeader();
				if(mysql_num_rows($tdh_sql)>0){ ?>
					<form method="post" action="/admin_doc_update_header.php">
                    <style>#pm_table tr td{ border: 1px solid transparent;}</style>
						<table id="pm_table" style="width: auto; margin-top: 15px; margin-bottom: 15px;">
							<tbody>
							<?php						
							while($tdh = mysql_fetch_array($tdh_sql)){ ?>
								<tr>
									<td>
										<input type="hidden" class="tdh_id" name="tdh_id[]"  value="<?php echo $tdh['admin_doc_header_id']; ?>">
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
					<form id="form_add_header" method="post" action="/admin_doc_add_header.php" style="display:none;">
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
				url: "ajax_delete_admin_doc_header.php",
				data: { 
					tdh_id: tdh_id
				}
			}).done(function( ret ) {	
				window.location='/admin_doc.php?del=2';
			});	
		}				
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var admin_doc_id = jQuery(this).parents("tr:first").find(".admin_doc_id").val();
			var del_file = jQuery(this).parents("tr:first").find(".del_file").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_admin_doc.php",
				data: { 
					admin_doc_id: admin_doc_id,
					del_file: del_file
				}
			}).done(function( ret ) {	
				window.location='/admin_doc.php?del=1';
			});	
		}				
	});

	jQuery("#btn_upload").click(function(){
	
		var file = jQuery("#file").val();
		var title = jQuery("#title").val();		
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
			jQuery("#form_admin_document").submit();
		}
		
	});
    
    //upload link validation
    jQuery("#btn_upload_link").click(function(){
	
	
		var title = jQuery("#title_link").val();		
		var error = "";
		
		if(title==""){
			error += "Title is required\n";
		}
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form_admin_doc_upload_link").submit();
		}
		
	});

    
    // add new toggle
	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery(".div_add_res").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery(".div_add_res").slideUp();
	});
	
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
    
    
    
    // option
	jQuery("#res_opt_1").click(function(){
		
		jQuery("#form_admin_document").slideDown();
		jQuery("#form_admin_doc_upload_link").slideUp();
		
	});
	jQuery("#res_opt_2").click(function(){
		
		jQuery("#form_admin_doc_upload_link").slideDown();
		jQuery("#form_admin_document").slideUp();
		
	});
	
	
});
</script>
</body>
</html>