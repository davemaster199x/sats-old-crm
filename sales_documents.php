<?php

$title = "Sales Documents";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



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
			<li class="other first"><a title="Sales Documents" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Sales Documents</strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php echo ($_GET['success']==1)?'<div class="success">New Document Added</div>':''; ?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Document Name</th>
				<th>Title</th>
				<th>State</th>
				<th>Uploaded</th>
				<th>Delete</th>
			</tr>
				<?php
				$sql = mysql_query("
					SELECT *
					FROM `sales_documents`		
					WHERE `country_id` = {$_SESSION['country_default']}
				");
				if(mysql_num_rows($sql)>0){
					while($row = mysql_fetch_array($sql)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<input type="hidden" class="sales_documents_id" value="<?php echo $row['sales_documents_id']; ?>" />
								<input type="hidden" class="del_path" value="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>" />
								<a href="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>"><?php echo $row['filename']; ?></a>
							</td>
							<td><?php echo $row['title'] ?></td>
							<td>
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
							</td>
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
		<div class="jalign_left">
		
			<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
        <form id="form_sales_document" method="post" action="/sales_documents_script.php" enctype="multipart/form-data" style="display:none;">
        <div class="row">
        <label class="addlabel" for="file">File</label>
        <input type="file" name="file" id="file" class="fname uploadfile submitbtnImg">
		</div>
         <div class="row">
        <label class="addlabel" for="title">Title</label>
        <input type="text" name="title" id="title" class="fname">
		</div>         
        <?php
		if(ifCountryHasState($_SESSION['country_default'])==true){ 
		$states = getCountryState();
		?>
			<div class="row">
				<label class="addlabel">States</label>
				<div class="vsud-inner">
					<? while($data =  mysql_fetch_array($states)){ ?>
						<input type="checkbox"  name="states[]" class="states" value="<?php echo $data['StateID'];?>">
						<label for="<?php echo $data['StateID'];?>" class="statelabel"><?php echo $data['state'];?></label>  
					<? } ?>
				</div>
			</div>
		<?php	
		}		
		?>
		
        
        <div style="padding-top: 15px; text-align:left;" class="row clear">
            <button type="button" class="submitbtnImg" id="btn_upload">Upload</button>
         </div>
        </form>
	</div>			
			
		</div>		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var sales_documents_id = jQuery(this).parents("tr:first").find(".sales_documents_id").val();
			var del_path = jQuery(this).parents("tr:first").find(".del_path").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_sales_documents.php",
				data: { 
					sales_documents_id: sales_documents_id,
					del_path: del_path
				}
			}).done(function( ret ) {	
				window.location.reload();
			});	
		}				
	});

	jQuery("#btn_upload").click(function(){
	
		var file = jQuery("#file").val();
		var title = jQuery("#title").val();
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
			jQuery("#form_sales_document").submit();
		}
		
	});

	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_sales_document").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_sales_document").slideUp();
	});
});
</script>
</body>
</html>