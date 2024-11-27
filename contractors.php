<?php

$title = "Contractors";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



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
	
	
	
		<div class="sats-breadcrumb">
			<ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="Contractors" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>Contractors</strong></a></li>
			</ul>
		</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Contractors Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Contractors Successfully Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Name</th>
				<th>Area</th>
				<th>Address</th>
				<th>Phone</th>
				<th>Email</th>
				<th>Rate</th>
				<th>Comment</th>
				<th>Edit</th>
			</tr>
				<?php
				
				$sql = mysql_query("
					SELECT *
					FROM `contractors`	
					WHERE `country_id` = {$_SESSION['country_default']}
					ORDER BY `area` ASC
				");
				
				if(mysql_num_rows($sql)>0){
					while($row = mysql_fetch_array($sql)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span class="txt_lbl"><?php echo $row['name']; ?></span>
								<input type="text" class="txt_hid name" value="<?php echo $row['name']; ?>" />
								<input type="hidden" class="contractors_id" value="<?php echo $row['contractors_id']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['area']; ?></span>
								<input type="text" class="txt_hid area" value="<?php echo $row['area']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['address']; ?></span>
								<input type="text" class="txt_hid address" value="<?php echo $row['address']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['phone']; ?></span>
								<input type="text" class="txt_hid phone" value="<?php echo $row['phone']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['email']; ?></span>
								<input type="text" class="txt_hid email" value="<?php echo $row['email']; ?>" />
							</td>
							<td>$
								<span class="txt_lbl"><?php echo $row['rate']; ?></span>
								<input type="text" class="txt_hid rate" value="<?php echo $row['rate']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['comment']; ?></span>
								<input type="text" class="txt_hid comment" value="<?php echo $row['comment']; ?>" />
							</td>
							<td>
								<button class="blue-btn submitbtnImg btn_update">Update</button>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
								<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
							</td>
						</tr>
				<?php
					}
				}else{ ?>
					<td colspan="8" align="left">Empty</td>
				<?php
				}
				?>
		</table>	

		<div class="jalign_left">
		
			<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_accomodation" method="post" action="/contractors_script.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="title">Name</label>
						<input type="text" name="name" id="name" class="fname">
					</div>         			
					<div class="row">
						<label class="addlabel" for="title">Area</label>
						<input type="text" name="area" id="area" class="fname">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Address</label>
						<input type="text" name="address" id="address" class="fname">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Phone</label>
						<input type="text" name="phone" id="phone" class="fname">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Email</label>
						<input type="text" name="email" id="email" class="fname">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Rate</label>
						<input type="text" name="rate" id="rate" class="fname">
					</div>
					<div class="row">
						<label class="addlabel" for="title">Comment</label>
						<input type="text" name="comment" id="comment" class="fname">
					</div>
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="submit" class="submitbtnImg" style="width: auto;" name="btn_submit" value="Submit" />
					</div>
				</form>
			</div>			
			
		</div>
		
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

	jQuery("#form_accomodation").submit(function(event){
		
		var name = jQuery("#name").val();		
		var email = jQuery("#email").val();
		var rate = jQuery("#rate").val();
		var error = "";
		
		if(name==""){
			error += "Accomodation name  is required\n";
		}
		
		if(email!="" && validate_email(email)==false){
			error += "Invalid email\n";
		}
		
		if(rate!="" && is_numeric(rate)==false){
			error += "Rate must be numeric\n";
		}
		
		
		if(error!=""){
			alert(error);
			event.preventDefault();	
		}else{
			return true;
		}
	});

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
	
		var contractors_id = jQuery(this).parents("tr:first").find(".contractors_id").val();
		var name = jQuery(this).parents("tr:first").find(".name").val();
		var area = jQuery(this).parents("tr:first").find(".area").val();
		var address = jQuery(this).parents("tr:first").find(".address").val();
		var phone = jQuery(this).parents("tr:first").find(".phone").val();		
		var email = jQuery(this).parents("tr:first").find(".email").val();
		var rate = jQuery(this).parents("tr:first").find(".rate").val();
		var comment = jQuery(this).parents("tr:first").find(".comment").val();
		var error = "";
		
		if(name==""){
			error += "Update Accomodation name field is required\n";
		}
		
		if(email!="" && validate_email(email)==false){
			error += "Update Email field Invalid\n";
		}
		
		if(rate!="" && is_numeric(rate)==false){
			error += "Update Rate field must be numeric\n";
		}
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_contractors.php",
				data: { 
					contractors_id: contractors_id,
					name: name,
					area: area,
					address: address,
					phone: phone,
					email: email,
					rate: rate,
					comment: comment
				}
			}).done(function( ret ) {
				window.location="/contractors.php?success=2";
			});				
			
		}		
		
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var contractors_id = jQuery(this).parents("tr:first").find(".contractors_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_contractors.php",
				data: { 
					contractors_id: contractors_id
				}
			}).done(function( ret ) {	
				window.location.reload();
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