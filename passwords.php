<?php

$title = "Passwords";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .action_div{
	display:none;
}
.action_div button {
    margin-top: 5px;
	width: 77px;
}
</style>
<div id="mainContent">

	
   
    <div class="sats-middle-cont">
	
		
	
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/passwords.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">New Site Account Added!</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Site Account Updated!</div>';
		}else if($_GET['del']==1){
			echo '<div class="success">Site Account Deleted!</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<div style="border: 1px solid #cccccc;" class="aviw_drop-h">		 
			<div class="fl-left">
				<a href="export_site_accounts.php">
					<button class="submitbtnImg" type="button">
						<img class="inner_icon" src="images/button_icons/export.png">
						Export
					</button>
				</a>
			</div>	
		</div>

		
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd jtable" style="margin-top: 0px; margin-bottom: 13px;">
						<tr class="toprow jalign_left">
							<th>Website</th>
							<th>Email</th>
							<th>Username</th>
							<th>Password</th>
							<th>Note</th>
							<th>Expiry date</th>
							<th>Last Updated</th>
							<th style="width: 82px;">Edit</th>
						</tr>

				<?php
				$sa_sql = mysql_query("
					SELECT *
					FROM `site_accounts` 
					WHERE `country_id` = {$_SESSION['country_default']}
					ORDER BY `website`
				");
				
				
				while($row = mysql_fetch_array($sa_sql)){ ?>

				
					
							
									<tr class="body_tr jalign_left">
										<td>											
											<span class="txt_lbl"><?php echo $row['website']; ?></span>
											<input type="text" class="txt_hid website" value="<?php echo $row['website']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['email']; ?></span>
											<input type="text" class="txt_hid email" value="<?php echo $row['email']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['username']; ?></span>
											<input type="text" class="txt_hid username" value="<?php echo $row['username']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['password']; ?></span>
											<input type="text" class="txt_hid password" value="<?php echo $row['password']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo $row['notes']; ?></span>
											<input type="text" class="txt_hid notes" value="<?php echo $row['notes']; ?>" />
										</td>
										<td>
											<?php
											$expiry_date = ($row['expiry_date']!="" && $row['expiry_date']!="" && $row['expiry_date']!="1970-01-01")?date("d/m/Y",strtotime($row['expiry_date'])):'';
											?>
											<span class="txt_lbl"><?php echo $expiry_date; ?></span>
											<input type="text" class="txt_hid datepicker expiry_date" value="<?php echo $expiry_date; ?>" />
										</td>
										<td>
											<?php
											$last_updated = ($row['last_updated']!="" && $row['last_updated']!="" && $row['last_updated']!="1970-01-01")?date("d/m/Y",strtotime($row['last_updated'])):'';
											?>
											<span class="txt_lbl"><?php echo $last_updated; ?></span>
											<span class="txt_hid"><?php echo $last_updated; ?></span>
								
										</td>
										<td>
											<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
											<div class="action_div">
												<button class="blue-btn submitbtnImg btn_update">
													<img class="inner_icon" src="images/button_icons/save-button.png">
													Update
												</button>							
												<button class="blue-btn submitbtnImg btn_delete">
													<img class="inner_icon" src="images/button_icons/cancel-button.png">
													Delete
												</button>
												<button class="submitbtnImg btn_cancel">
													<img class="inner_icon" src="images/button_icons/back-to-tech.png">
													Cancel
												</button>
												<input type="hidden" class="site_accounts_id" value="<?php echo $row['site_accounts_id']; ?>" />
											</div>	
										</td>
									</tr>
							
							
					
				
				<?php	
				}
				
				?>
				
				
				</table>
		
		
			

		<div class="jalign_left">
		
			<button type="button" id="btn_add_opportunity" class="submitbtnImg blue-btn">
				<img class="inner_icon" src="images/button_icons/add-button.png">
				<span class="inner_icon_span">Website/Email</span>
			</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_site_accounts" method="post" action="/add_site_accounts.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="website">Website</label>
						<input type="text" name="website" id="website" class="website">
					</div> 
					<div class="row">
						<label class="addlabel" for="email">Email</label>
						<input type="text" name="email" id="email" class="email">
					</div> 
					<div class="row">
						<label class="addlabel" for="user">Username</label>
						<input type="text" name="user" id="user" class="user">
					</div>
					<div class="row">
						<label class="addlabel" for="pass">Password</label>
						<input type="text" name="pass" id="pass" class="pass">
					</div>
					<div class="row">
						<label class="addlabel" for="notes">Notes</label>
						<textarea name="notes" class="addtextarea"></textarea>
					</div>
					<div class="row">
						<label class="addlabel" for="expiry_date">Expiry Date</label>
						<input type="text" name="expiry_date" id="expiry_date" class="expiry_date datepicker">
					</div>
					<div style="padding-top: 15px; text-align:left;" class="row clear">
						<input type="hidden" class="submitbtnImg" name="btn_submit" value="Submit" />
						<button type="submit" id="btn_add_opportunity" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/save-button.png">
							<span class="inner_icon_span">Submit</span>
						</button>
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
		
	
	// opportunity validation check
	jQuery("#form_site_accounts").submit(function(event){
		
		var web_or_email = jQuery("#web_or_email").val();		
		var error = "";
		
		if(web_or_email==""){
			error += " Website/Email  required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	// sales rep validation check
	jQuery("#form_sales_rep").submit(function(event){
		
		var fname = jQuery("#fname").val();		
		var lname = jQuery("#lname").val();
		var error = "";
		
		if(fname==""){
			error += "Sales Rep first name is required\n";
		}
		
		if(lname==""){
			error += "Sales Rep last name is required\n";
		}
				
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
	});

	// inline edit
	jQuery(".btn_edit").click(function(){
	
		var btn_txt = jQuery(this).html();
		
		jQuery(this).hide();
		
		if( btn_txt == 'Edit' ){			
			jQuery(this).parents("tr:first").find(".action_div").show();
			jQuery(this).parents("tr:first").find(".txt_hid").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
		}else{
			jQuery(this).parents("tr:first").find(".action_div").hide();
		}
	
	});	
	
	// cancel script
	jQuery(".btn_cancel").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();	
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
	});
	
	jQuery(".btn_update").click(function(){
	
		var site_accounts_id = jQuery(this).parents("tr:first").find(".site_accounts_id").val();
		var website = jQuery(this).parents("tr:first").find(".website").val();
		var email = jQuery(this).parents("tr:first").find(".email").val();
		var username = jQuery(this).parents("tr:first").find(".username").val();
		var password = jQuery(this).parents("tr:first").find(".password").val();
		var notes = jQuery(this).parents("tr:first").find(".notes").val();
		var expiry_date = jQuery(this).parents("tr:first").find(".expiry_date").val();		
		var error = "";
		
		if(website==""){
			error += "Website/Email is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_site_account.php",
				data: { 
					site_accounts_id : site_accounts_id,
					website: website,
					email: email,
					username: username,
					password: password,
					notes: notes,
					expiry_date: expiry_date
				}
			}).done(function( ret ) {
				window.location="/passwords.php?success=2";
			});				
			
		}		
		
	});

	// delete opportunity
	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var site_accounts_id = jQuery(this).parents("tr:first").find(".site_accounts_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_site_accounts.php",
				data: { 
					site_accounts_id: site_accounts_id
				}
			}).done(function( ret ) {	
				window.location="/passwords.php?del=1";
			});	
		}				
	});
	
	// delete salesrep
	jQuery(".btn_del_sr").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var ss_sr_id = jQuery(this).parents("tr:first").find(".ss_sr_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_snapshot_sales_rep.php",
				data: { 
					ss_sr_id: ss_sr_id
				}
			}).done(function( ret ) {	
				window.location.reload();
			});	
		}				
	});

	
	jQuery("#btn_add_opportunity").click(function(){
		
		var icon = jQuery(this).find(".inner_icon");
		var icon_span = jQuery(this).find(".inner_icon_span");
		var btn_txt = icon_span.html();
		var btn_orig_txt = 'Website/Email'
		var orig_btn_src = 'images/button_icons/add-button.png';
		var cancel_btn = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == btn_orig_txt ){
			
			icon_span.html("Cancel");
			jQuery(this).removeClass("blue-btn");
			icon.attr("src",cancel_btn);
			jQuery(".preferred_time_elem").show();
			jQuery("#form_site_accounts").slideDown();
			
		}else{
			
			icon_span.html(btn_orig_txt);
			jQuery(this).addClass("blue-btn");
			icon.attr("src",orig_btn_src);
			jQuery(".preferred_time_elem").hide();
			jQuery("#form_site_accounts").slideUp();
			
			
		}
		
	});
	
	
	// main sales rep show/hide form toggle
	jQuery("#btn_add_edit_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#sales_rep_div").slideDown();
	},function(){
		jQuery(this).html("Add/Edit Sales Rep");		
		jQuery("#sales_rep_div").slideUp();
	});
	
	// sales rep show/hide form toggle
	jQuery("#btn_add_sales_rep").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_sales_rep").slideDown();
	},function(){
		jQuery(this).html("Add Sales Rep");		
		jQuery("#form_sales_rep").slideUp();
	});
	
	
	
	
	
});
</script>
</body>
</html>