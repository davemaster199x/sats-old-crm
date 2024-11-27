<?php

$title = "Technician Documents";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getTechDocHeader(){
	return mysql_query("
		SELECT *
		FROM `tech_doc_header`
	");	
}

?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>

<?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
  <?php
  }  
  ?>


<div id="mainContent">




	
   
    <div class="sats-middle-cont">

	<?php
	if($_SESSION['USER_DETAILS']['ClassID']==6){ 
	
	$tech_id = $_SESSION['USER_DETAILS']['StaffID'];
	
	$day = date("d");
	$month = date("m");
	$year = date("y");
	
	include('inc/tech_breadcrumb.php');
	
	}else{ ?>
	
		<div class="sats-breadcrumb">
			<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Technician Documents" href="/tech_doc_tech.php"><strong>Technician Documents</strong></a></li>
			</ul>
		</div>
	
	<?php
	}
	?>  
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<?php
		$tdh_sql = mysql_query("
			SELECT DISTINCT(td.`tech_doc_header_id`), tdh.`name`	
			FROM `technician_documents` AS td
			LEFT JOIN `tech_doc_header` AS tdh ON tdh.`tech_doc_header_id` = td.`tech_doc_header_id`
			WHERE tdh.`country_id` = {$_SESSION['country_default']}
		");
		if(mysql_num_rows($tdh_sql)>0){
			while($td = mysql_fetch_array($tdh_sql)){ ?>
				<h2 class="heading"><?php echo $td['name']; ?></h2>
				
				
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
				<tr class="toprow jalign_left">
					<th style="width: 100px;">Title</th>
				</tr>
					<?php
					
					$sql = mysql_query("
						SELECT *
						FROM `technician_documents` AS td		
						LEFT JOIN `tech_doc_header` AS tdh ON tdh.`tech_doc_header_id` = td.`tech_doc_header_id`
						WHERE td.`tech_doc_header_id` = {$td['tech_doc_header_id']}
						AND tdh.`country_id` = {$_SESSION['country_default']}
						ORDER BY td.`title`
					");
					
					
					if(mysql_num_rows($sql)>0){
						$total = 0;
						while($row = mysql_fetch_array($sql)){
					?>
							<tr class="body_tr jalign_left">
								<td>
									<span class="txt_lbl">										
										<?php
										if($row['type']==1){ ?>											
											<a href="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>"><?php echo $row['title'] ?></a>
										<?php	
										}else{ ?>
											<a href="<?php echo $row['url']; ?>"><?php echo $row['title']; ?></a>
										<?php	
										}
										?>
									</span>
								</td>
							</tr>
					<?php
						}
					}else{ ?>
						<td colspan="3" align="left">Empty</td>
					<?php
					}
					?>
			</table>
				
		<?php
			}
		}else{
			echo '<div class="jalign_left" style="margin: 17px 0;">Empty</div>';
		}
		?>		
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
				url: "ajax_delete_tech_doc_header.php",
				data: { 
					tdh_id: tdh_id
				}
			}).done(function( ret ) {	
				window.location='/tech_doc_tech.php?del=2';
			});	
		}				
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var tech_doc_id = jQuery(this).parents("tr:first").find(".tech_doc_id").val();
			var del_path = jQuery(this).parents("tr:first").find(".del_path").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_tech_doc.php",
				data: { 
					tech_doc_id: tech_doc_id,
					del_path: del_path
				}
			}).done(function( ret ) {	
				window.location='/tech_doc_tech.php?del=1';
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
			jQuery("#form_tech_document").submit();
		}
		
	});


	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_tech_document").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_tech_document").slideUp();
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
	

	
});
</script>
</body>
</html>