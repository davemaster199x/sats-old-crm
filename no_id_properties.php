<?php

$title = "No ID Properties";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

//include('inc/no_id_properties_functions.php');

$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 50;
$this_page = $_SERVER['PHP_SELF'];
$next_link = "{$this_page}?offset=".($offset+$limit);
$prev_link = "{$this_page}?offset=".($offset-$limit);

$plist = getPropertyNoAgency($offset,$limit);
$ptotal = mysql_num_rows(getPropertyNoAgency('',''));



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
</style>




<div id="mainContent">


   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="No ID Properties" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>No ID Properties</strong></a></li>
		</ul>
	</div>
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Agency Update Successfull</div>';
		}
		
		
		?>

		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Property ID</th>
				<th>Property Name</th>
				<th>Agency ID</th>
				<th>Edit</th>
			</tr>
				<?php
				
				
				
				if(mysql_num_rows($plist)>0){
					while($row = mysql_fetch_array($plist)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo $row['property_id']; ?></a></span>
								<input type="hidden" class="property_id" value="<?php echo $row['property_id']; ?>" />
							</td>
							<td>
								<span><a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']}"; ?></a></span>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['agency_id']; ?></span>
								<?php
								$asql = mysql_query("
									SELECT *
									FROM `agency`
									WHERE status = 'active'	
									ORDER BY `agency_name` ASC
								");
								?>
								<select class="txt_hid agency_id">
									<?php
									while($a = mysql_fetch_array($asql)){ ?>
										<option value="<?php echo $a['agency_id']; ?>"><?php echo $a['agency_name']; ?></option>
									<?php
									}
									?>									
								</select>
							</td>						
							<td>
								<button class="blue-btn submitbtnImg btn_update">Update</button>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>	
							</td>
						</tr>
						
				<?php
					}
				}else{ ?>
					<td colspan="4" align="left">Empty</td>
				<?php
				}
				?>
				
		</table>	

		<?php

		// Initiate pagination class
		$jp = new jPagination();
		
		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
		
		echo $jp->display($page,$ptotal,$per_page,$offset,$params);
		
		?>
		
	</div>
</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){


	function is_numeric(num){
		if(num.match( /^\d+([\.,]\d+)?$/)==null){
			return false
		}
	}

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}


	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var property_id = jQuery(this).parents("tr:first").find(".property_id").val();
		var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
		var error = "";
		
		if(agency_id==""){
			error += "Please Select Agency\n";
		}
		
	
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_property_agency_id.php",
				data: { 
					property_id: property_id,
					agency_id: agency_id
				}
			}).done(function( ret ) {
				window.location="/no_id_properties.php?success=1";
			});				
			
		}		
		
	});


	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_accomodation").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_accomodation").slideUp();
	});
});
</script>
</body>
</html>