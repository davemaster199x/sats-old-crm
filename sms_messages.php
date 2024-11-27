<?php

$title = "SMS Messages";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
.txt_hid{
	display:none;
}
#tbl_sms_msg th, #tbl_sms_msg td{
	text-align: left;
}
#sms_tags{
	margin: 0;
	padding: 0;
}
#sms_tags li{
	text-align: left;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="SMS Messages" href="/sms_messages.php"><strong>SMS Messages</strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success">New Message Created</div>
	<?php
	}else if($_GET['success']==2){ ?>
		<div class="success">Message Updated</div>
	<?php
	}
	?>
	
	<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;" id="tbl_sms_msg">
				<tr class="toprow jalign_left">
					<th>Title</th>
					<th>Message</th>
					<th>Edit</th>
				</tr>
					<?php
				
					// get sms messages
					$sql = mysql_query("
						SELECT *
						FROM `sms_messages`
						WHERE `country_id` = {$_SESSION['country_default']}
						ORDER BY `title` ASC
					");					
									
					
					if(mysql_num_rows($sql)>0){
						$i = 0;
						while($row = mysql_fetch_array($sql)){
					?>
							<tr class="body_tr jalign_left">		
								<td>
									<input type="hidden" name="sms_msg_id" class="sms_msg_id" value="<?php echo $row['sms_messages_id']; ?>" />
									<span class="txt_lbl"><?php echo $row['title'] ?></span>
									<input type="text" name="title" class="txt_hid title" value="<?php echo $row['title'] ?>" />
								</td>
								<td>
									<span class="txt_lbl"><?php echo $row['message'] ?></span>
									<input type="text" name="msg" class="txt_hid msg" value="<?php echo $row['message'] ?>" />
								</td>
								<td>
									<button class="blue-btn submitbtnImg btn_update" style="display:none;">Update</button>
									<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
									<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
									<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
								</td>
							<tr>
					<?php
						$i++;
						}
					}else{ ?>
						<td colspan="3" align="left">Empty</td>
					<?php
					}
					?>
			</table>
      	
		
	<button class="submitbtnImg" id="btn_add_msg" type="button" style="float: left;">Add Message</button>
	<br class="clearfloat" />


		

		
		<div class="addproperty" id="add_msg_div" style="display:none;">	
		
		
			
			
			<div style="float:left;">
				<p>To Merge fields into your message please use the following tags:</p>
				<ul id="sms_tags">
					<li>Property Address {address}</li>
					<li>Property Booking Date {date}</li>
					<li>Property Booking Time {time}</li>
					<li>Property Tenant First Name {name}</li>
					<li>Service Type {service}</li>
					<li>Tenant Number {tenant_number}</li>
				</ul>
			</div>
			<br class="clearfloat" />
		
			<form action="sms_messages_script.php" method="post" id="frm_sms" style="font-size: 14px;">
			<div style="margin: 30px 0;">
				<div class="row">					
					<label class="addlabel" for="make">Title</label>
					<input type="text"  class="addinput" name="title" id="title">
				</div>
				<div class="row">
					<label class="addlabel" for="make">Message</label>
					<textarea class="addinput" style="height: 125px; margin-left: 0; width: 365px;" name="msg" id="msg"></textarea>
				</div>	
			</div>
				
			<div class="row">
				<button class="submitbtnImg" id="btn_submit" type="button" style="float: left;">Submit</button>
			</div>	
			</form>				
		</div>
	
	
	


    
  </div>

<br class="clearfloat" />

<script>
jQuery(document).ready(function(){

	// edit toggle
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
	
		var sms_msg_id = jQuery(this).parents("tr:first").find(".sms_msg_id").val();
		var title = jQuery(this).parents("tr:first").find(".title").val();
		var msg = jQuery(this).parents("tr:first").find(".msg").val();
		
		var error = "";
		
		if(title==""){
			error += "Title is required";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_sms_message.php",
				data: { 
					sms_msg_id: sms_msg_id,
					title: title,
					msg: msg
				}
			}).done(function( ret ) {
				window.location = "/sms_messages.php?success=2";
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete").click(function(){
	
		var sms_msg_id = jQuery(this).parents("tr:first").find(".sms_msg_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_sms_message.php",
				data: { 
					sms_msg_id: sms_msg_id,
				}
			}).done(function( ret ){
				window.location = "/sms_messages.php";
			});	
		}
	});


	//  opportunity show/hide form toggle
	jQuery("#btn_add_msg").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#add_msg_div").slideDown();
	},function(){
		jQuery(this).html("Add Message");		
		jQuery("#add_msg_div").slideUp();
	});

	jQuery("#btn_submit").click(function(){
	
		var title = jQuery("#title").val();
		var msg = jQuery("#msg").val();
		var error = "";
		
		if(title==""){
			error += "Title is required\n";
		}
		if(msg==""){
			error += "Message is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_sms").submit();
		}
		
	});
	
});
</script>

</body>
</html>
