<?php
$title = "Send Email Template";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

//$sats_query = new Sats_query();

//print_r($_SESSION);

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_class_id = $_SESSION['USER_DETAILS']['ClassID'];
$logged_user_email = $_SESSION['USER_DETAILS']['Email'];
$job_id = mysql_real_escape_string($_REQUEST['job_id']);

$to_email = mysql_real_escape_string($_REQUEST['to_email']);

// get job data
$jparams = array(
	'job_id' => $job_id,
	'remove_deleted_filter' => 1,
	'a_status' => 'all',
	'display_echo' => 0
);
$job_sql = $crm->getJobsData($jparams);
$row = mysql_fetch_array($job_sql);
$property_id = $row['property_id'];
$assigned_tech = $row['assigned_tech'];


// put account emails into an array
$account_emails_exp = explode("\n",trim($row['account_emails']));
// put agency emails into an array
$agency_emails_exp = explode("\n",trim($row['agency_emails']));

?>

	
    
    <div id="mainContent">
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>				
		<li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER[REQUEST_URI]; ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Email Template Sent</div>
	<?php
	}
	?>
	
	 <?php
	if($_GET['error']==1){ ?>
		<div class="error" style="margin-bottom: 12px;">
			Email Template Sending Failed			
			<?php
			if( count($_GET['upload_errors'])>0 ){ ?>
			</p>
				<ul>
				<?
					foreach( $_GET['upload_errors'] as $error_txt ){ ?>
						<li><?php echo $error_txt; ?></li>
					<?php
					}?>
				</ul>
			<p>
			<?php
			}
			?>			
		</div>
	<?php
	}
	?>
      	
	<form id="jform" action="send_email_template_script.php" method="POST" enctype="multipart/form-data">
	<div class="addproperty">
		
	<div id="popup_custom_email_template">
		
		<div class="addproperty">
		
			<div style="width:75%; float:left;">
			
				<div class="row email_to_row_div">
					<label class="addlabel">
						From:						
					</label>
					<input type="text" name="from_email" class="addinput et_from" id="et_from" value="<?php echo INFO_EMAIL; ?>" />
				</div>
			
				<div class="row email_to_row_div">
					<label class="addlabel">
						To:
						<img id="et_to_icon" data-target-id="et_to" class="inner_icon email_to_icon" src="images/profile.png" />
					</label>
					<input type="text" name="to_email" class="addinput et_to" id="et_to" style="padding-left: 40px;" value="<?php echo $to_email; ?>" />
				</div>
			
				<div class="row email_to_row_div">
					<label class="addlabel">
						CC:
						<img id="et_cc_icon" data-target-id="et_cc" class="inner_icon email_to_icon" src="images/profile.png" />
					</label>					
					<input type="text" name="cc_email" class="addinput et_cc" id="et_cc" style="padding-left: 40px;" />
				</div>
	
				<div class="row">
					<label class="addlabel">Subject:</label>
					<input type="text" class="addinput subject" name="subject" id="et_subject" />
				</div>
				
				<div class="row">
					<label class="addlabel">Body:</label>
					<textarea class="addtextarea et_body" id="et_body"  name="body"></textarea>
				</div>			
				
				<div class="row">
					<label class="addlabel" style="height: 69px;">
						 Attachment: 
					</label>
					
					<div>
						<div class="et_attachment_icon_div">
							<img class="inner_icon" id="et_attachment_icon" src="images/attachment.png" />						
						</div>
						<div id="et_attachment_hid_div" style="display:none;">
							<div id="et_attachment_file">													
								<div style="float:left; margin-right: 10px;">
									<input type="checkbox" name="job_pdf[]" value="inv" class="addinput et_attachment_checkbox" id="pdf_inv_chk" /> <span class="attachment_job_pdf fadeIt">Invoice</span> 
								</div>
								<?php
								// hide on upfront bill
								if( $assigned_tech != 2 ){ ?>

									<div style="float:left; margin-right: 10px;">
										<input type="checkbox" name="job_pdf[]" value="cert" class="addinput et_attachment_checkbox" id="pdf_cert_chk" /> <span class="attachment_job_pdf fadeIt">Certificate</span> 
									</div>
									<div style="float:left; margin-right: 10px;">
										<input type="checkbox" name="job_pdf[]" value="comb" class="addinput et_attachment_checkbox" id="pdf_comb_chk" /> <span class="attachment_job_pdf fadeIt">Combined</span>  
									</div>

								<?php
								}
								?>								
								<div style="float:left; margin-right: 10px;">
									<input type="checkbox" name="job_pdf[]" value="cred" class="addinput et_attachment_checkbox" id="pdf_comb_chk" /> <span class="attachment_job_pdf fadeIt">Credit Note</span>  
								</div>
								<div style="float:left; margin-right: 10px;">
									<input type="checkbox" name="marked_as_copy" value="1" class="addinput et_attachment_checkbox" /> <span class="fadeIt">Mark as Copy</span>  
								</div>								
							</div>
							<div id="browse_file">
								<input type="file" name="et_file_upload" id="et_file" class="addinput et_file" />
							</div>
						</div>
						
					</div>
				</div>
				
				<div class="row">
				
					<label class="addlabel">&nbsp;</label>

					<div style="float: left;">								
					
						<input type="hidden" id="email_var_target" />
						
						<input type="hidden" id="email_temp_id" />
						
						<a id="preview_email_temp_link" href="#preview_email_temp_div">
							<button type="button" id="btn_et_preview" class="submitbtnImg grey-btn">
								<img class="inner_icon" src="images/show-button.png" /> <span id="btn_et_preview_icon">Preview</span>
							</button>
						</a>
						
						<button type="button" id="btn_et_clear" class="submitbtnImg grey-btn" style="margin-right: 20px;">
							<img class="inner_icon" src="images/cancel-button.png" /> Clear
						</button>
						
						
						<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" />
					
					</div>
					
					
					<div style="float: right;">	
						<button type="submit" id="btn_et_send" class="submitbtnImg grey-btn" style="margin-right: 33px;">
							<img class="inner_icon" src="images/select-button.png"/> Send
						</button>
					</div>
				
				</div>
				
		

			</div>
			
			<div id="et_div2" style="width: 25%; float:left;">
			
				<div id="send_to_div" style="display:none;">
					<h2 class="heading tag_header_div" id="send_to_header">Send To:</h2>
					<?php
					
					$pt_params = array( 
						'property_id' => $row['property_id'],
						'active' => 1,
						'echo_query' => 0
					 );
					$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
					
					$pt_i = 1;
					while( $pt_row = mysql_fetch_array($pt_sql) ){
						
						$tl_tenants[] = array(
							'email' => $pt_row['tenant_email'],
							'firstname' => $pt_row['tenant_firstname']
						); 
					?>
						
						<div class="tag_div">
							<button class="submitbtnImg <?=( $pt_row['tenant_email'] != '' ) ? 'green-btn':'grey-btn';?> email_variables_btn et_to_emails" id="btn_tag" data-email-to="<?=$pt_row['tenant_email']?>" type="button">
							<img class="inner_icon" src="images/left-arrow.png">
							<?=$pt_row['tenant_firstname']?><br />
							<span style="font-size:12px !important; margin-left:25px;">{tenant_<?=$pt_i?>}</span>
							</button>
						</div>
						
					<?php
					$pt_i++;
					}
					
					//print_r($tl_tenants);
					
					?>

					<?php 
					/*
					$PMTenantsList = $sats_query->getTenantsFromPM_Job($_REQUEST['job_id'])['ContactPersons'];
					$ptl_tenants = [];
					if($PMTenantsList){
							$ptl_count=0;
							foreach($PMTenantsList as $ptl){ 
								$ptl_count++;
								$ptl_tenants[] = array('email' => $ptl['Email'], 'firstname' => $ptl['FirstName']);
							?>
							<div class="tag_div">
								<button class="submitbtnImg <?=( $ptl['Email'] != '' ) ? 'green-btn':'grey-btn';?> email_variables_btn et_to_emails" id="btn_tag" data-email-to="<?=$ptl['Email']?>" type="button">
								<img class="inner_icon" src="images/left-arrow.png">
								(PM) <?=$ptl['FirstName']?><br />
								<span style="font-size:12px !important; margin-left:25px;">{pm_tenant_<?=$ptl_count?>}</span>
								</button>
							</div>
					<?php	}
					}
					*/
					?>

					<textarea name="tl_tenants_arr" style="display:none;"><?=serialize($tl_tenants)?></textarea>
					<textarea name="ptl_tenants_arr" style="display:none;"><?=serialize($ptl_tenants)?></textarea>

					<div class="tag_div">
						<button class="submitbtnImg <?php echo ( $agency_emails_exp != '' )?'green-btn':'grey-btn'; ?> email_variables_btn et_to_emails" id="btn_tag" type="button" data-email-to="<?php echo implode(';',$agency_emails_exp); ?>">
						<img class="inner_icon" src="images/left-arrow.png">
						Agency (General)
						</button>
					</div>
					<div class="tag_div">
						<button class="submitbtnImg <?php echo ( $account_emails_exp != '' )?'green-btn':'grey-btn'; ?> email_variables_btn et_to_emails" id="btn_tag" type="button" data-email-to="<?php echo implode(';',$account_emails_exp); ?>">
						<img class="inner_icon" src="images/left-arrow.png">
						Agency (Accounts)
						</button>
					</div>
					<input type="hidden" id="email_to_target" value="et_to" />
					
					
					<h2 class="heading tag_header_div" style="margin-top: 0px;  margin-top: 35px;">Email Templates:</h2>
					<?php
					
					// get email templates
					$temp_type = 2; //  Email Template Type - Jobs
					if( $logged_user_class_id == 8 ){
						// get email templates that is call centre = yes
						$et_params = array( 
							'echo_query' => 0,
							'sort_list' => array(
								 array(
									'order_by' => 'et.`template_name`',
									'sort' => 'ASC'
								 )
							),
							'active' => 1,
							'custom_filter' => ' AND et.`show_to_call_centre` = 1 '
						);	
					}else{						
						$et_params = array( 
							'echo_query' => 0,
							'sort_list' => array(
								 array(
									'order_by' => 'et.`template_name`',
									'sort' => 'ASC'
								 )
							),
							'active' => 1,
							'temp_type' => $temp_type 
						);	
					}					
					$email_temp_sql = $crm->getEmailTemplates($et_params);
					if( mysql_num_rows($email_temp_sql)>0 ){ ?>
						<select name="et_id" id="email_template_select" style="width: 100%">
							<option value="">--- Select ---</option>
						<?php
						while( $email_temp = mysql_fetch_array($email_temp_sql) ){ ?>
							<option value="<?php echo $email_temp['email_templates_id'] ?>"><?php echo $email_temp['template_name'] ?></option>					
						<?php	
						}?>
						</select>
					<?php
					}
					?>	


					<div id="bottom_text">
						TEXT HERE
					</div>

				</div>
			

				
				

			</div>

	
			
		

		</div>
		
	</div>

		
	</div>
	</form>
		
	
    
  </div>

<br class="clearfloat" />

<div style="display:none;">
	<div id="preview_email_temp_div">
		<table class="table">
			<tr>
				<td class="td_lbl">From:</td><td class="prev_et_from"></td>
			</tr>
			<tr>
				<td class="td_lbl">To:</td><td class="prev_et_to"></td>
			</tr>
			<tr>
				<td class="td_lbl">CC:</td><td class="prev_et_cc"></td>
			</tr>
			<tr>
				<td class="td_lbl">Subject:</td><td class="prev_et_subj"></td>
			</tr>
			<tr>
				<td class="td_lbl prev_et_body_lbl">Body:</td><td class="prev_et_body"></td>
			</tr>
		</table>
	</div>
</div>

<style>
#template_name, #subject{
	width: 516px;
}
.et_buttons button{
	float: left; 
	margin-top: 15px;	
}
#btn_submit{
	margin-right: 10px;
}
.addproperty {
	float: left;
}
#et_div2{
	width: 200px;
	float: left;
	text-align: left;
}
.tag_div {
    margin-bottom: 4px;
}
.jred_border_higlight{
	border: 1px solid #b4151b;box-shadow: 0 0 2px #b4151b inset;	
}
.tag_div {
    margin-bottom: 4px;
}
.email_variables_btn{
	width: 100%;
	width: 180px;
	text-align: left;
}
#popup_custom_email_template #et_attachment {

    height: auto;
    position: relative;
    top: 4px;
    margin-right: 5px;

}
#popup_custom_email_template{
	padding: 22px;
}
#popup_custom_email_template input[type="text"]{
	width: 70%
}
#et_body{
	height: 350px; 
	width: 67.1%; 
	margin: 0; 
	padding: 8px;
}
#et_attachment_file{
	text-align: left; 
	margin-top: 4px; 
}
.tag_header_div{
	margin: 5px 0;
}
.email_to_icon{
	width: 20px;
	cursor: pointer
}
#et_to_icon{
	position: relative; 
	left: 103px; 
	top: -1px; 
	
}
#et_cc_icon{
	position: relative; 
	left: 96px; 
	top: -1px; 
	
}
.grey-btn{
	background-color: #dedede;
}
.tag_div .grey-btn:hover{
	background-color: #dedede;
}
#btn_et_send:hover{
	background-color: #00AEEF;
}
#et_attachment_icon{
	float: left;
	cursor: pointer
}
.et_attachment_checkbox{
	float: left !important;
	width: auto !important;
	margin-right: 5px !important;
	position: relative; 
	bottom: 3px
}
.et_attachment_icon_div{
	float: left; 
	margin-top: 4px; 
	margin-right: 10px; 
	margin-bottom: 30px;
}
#et_preview{
	text-align: left; 
	margin-top: 4px; 
	padding-left: 120px;
}

.fadeIt{
	opacity: 0.5;
}

#preview_email_temp_div{
	width: 500px;
	text-align: left;
	padding: 22px;
}
.prev_et_body_lbl{
	vertical-align: top;
}
#preview_email_temp_div .td_lbl{
	font-weight: bold;
}
#bottom_text{
	margin-top: 30px;
	display: none;
}
</style>
<script>
jQuery(document).ready(function(){
	
	
	// fancy box
	jQuery("#preview_email_temp_link").fancybox();
	
	
	<?php
	// display right panel, if email TO: exist
	if( $to_email != '' ){ ?>
		setTimeout(function(){ 
		
			jQuery("#et_to_icon").click();
			jQuery("#btn_et_send").removeClass("grey-btn");
			jQuery("#btn_et_send").addClass("blue-btn");
			
		}, 1000);		
	<?php
	}
	?>
	
	

	jQuery(".et_attachment_checkbox").click(function(){
		
		jQuery(this).parents("div:first").find(".attachment_job_pdf").removeClass('fadeIt');
		
	});
	
	
	// change email to target script
	jQuery("#et_to, #et_cc").click(function(){
		
		var myid = jQuery(this).attr("id");
		jQuery("#email_to_target").val(myid);
		
	});
	
	jQuery("#et_to_icon").click(function(){
		
		var myid = jQuery(this).attr("data-target-id");
		jQuery("#email_to_target").val(myid);
		jQuery("#send_to_header").html("Send To:");		
		jQuery("#send_to_div").show();
		
	});
	
	jQuery("#et_cc_icon").click(function(){
		
		var myid = jQuery(this).attr("data-target-id");
		jQuery("#email_to_target").val(myid);
		jQuery("#send_to_header").html("Send CC To:");
		jQuery("#send_to_div").show();
		
	});
	
	// toggle send To div
	jQuery("#et_subject, #et_body").click(function(){		
		//jQuery("#send_to_div").hide();		
	});
	
	// repopulate email tags to field
	jQuery(".et_to_emails").click(function(){
		
		var email = jQuery(this).attr("data-email-to");
		var target = jQuery("#email_to_target").val();
		
		if( email != '' ){
			jQuery("#"+target).val(email);
			jQuery("#btn_et_send").removeClass("grey-btn");
			jQuery("#btn_et_send").addClass("blue-btn");
		}else{
			jQuery("#"+target).val("");
			jQuery("#btn_et_send").removeClass("blue-btn");
			jQuery("#btn_et_send").addClass("grey-btn");
		}			
		
	});
	
	
	// attachment toggle script
	jQuery("#et_attachment_icon").click(function(){
	
		jQuery("#et_attachment_hid_div").toggle();
		
	});
	
	
	// preview email templates
	jQuery("#popup_custom_email_template #btn_et_preview").click(function(){
				
		var obj = jQuery(this);
		var prev_btn = jQuery("#btn_et_preview_icon").html();
		var et_from = jQuery("#popup_custom_email_template #et_from").val();
		var to = jQuery("#popup_custom_email_template #et_to").val();
		var cc = jQuery("#popup_custom_email_template #et_cc").val();
		var subject = jQuery("#popup_custom_email_template #et_subject").val();
		var body = jQuery("#popup_custom_email_template #et_body").val();
		var body2 = body.replace(/(?:\r\n|\r|\n)/g, '<br />');
		var error = "";
		
		if(body==""){
			error += " Select Template to preview\n ";
		}
		
		if(error!=""){
			alert(error);
		}else{
	
			// parse body tags
			jQuery("#load-screen").show();
			jQuery.ajax({			
				type: "POST",			
				url: "ajax_preview_email_template.php",			
				data: {
				subject: subject,
				body: body2,
				job_id: '<?php echo $job_id; ?>'
				},
				dataType: 'json'		
			}).done(function( ret ) {	
			
				jQuery("#load-screen").hide();
				//jQuery("#preview_email_temp_div").html(ret);
				jQuery(".prev_et_from").html(et_from);
				jQuery(".prev_et_to").html(to);
				jQuery(".prev_et_cc").html(cc);
				jQuery(".prev_et_subj").html(ret.subject);
				jQuery(".prev_et_body").html(ret.body);
			});	

		}
					
	});
	
	
	
	// send email templates
	jQuery("#jform").submit(function(){
		
		var et_from = jQuery("#popup_custom_email_template #et_from").val();
		var to = jQuery("#popup_custom_email_template #et_to").val();
		var body = jQuery("#popup_custom_email_template #et_body").val();
		var error = '';
		
		if( et_from == "" ){
			error += "Email From is required\n";
		}
		
		if( to == "" ){
			error += "Email To is required\n";
		}
		
		if( body == "" ){
			error += "Email Body is required\n";
		}
		
		
		
		if( error != "" ){
			alert(error);
			return false;
		}else{
			return true;						
		}
					
	});
	
	
	// clear
	jQuery("#btn_et_clear").click(function(){
		
		window.location = "send_email_template.php?job_id=<?php echo $job_id ?>";
		
	});
	
	// load email template
	jQuery("#email_template_select").change(function(){
		
		var et_id = jQuery(this).val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_getEmailTemplate.php",
			data: { 
				et_id: et_id
			},
			dataType: 'json'
		}).done(function( ret ){
			
			var ptl_tenants = '<?=count($ptl_tenants)?>';
			var i;
			var pmTenant = '';
			for (i = 1; i <= ptl_tenants; i++) {
				pmTenant += "{pm_tenant_" + i + "}\n";
			}

			var tl_tenants = '<?=count($tl_tenants)?>';
			var x;
			var activeTenant = '';
			for (x = 1; x <= tl_tenants; x++) {
				activeTenant += "{tenant_" + x + "}\n";
			}

			// console.log(activeTenant);
			// console.log(pmTenant);

			// console.log(ret.body);

			var bodyEmail = ret.body;
			activeTenantTags = bodyEmail.replace('{active_tenants}',activeTenant);
			bodyEmailFiltered = activeTenantTags.replace('{pm_tenants}',pmTenant);

			jQuery("#load-screen").hide();

			// change FROM email for "account" types email templates
			var subject_str = ret.template_name;
			var string_to_find = 'Accounts -';
			//var string_to_find = 'bonjour';	
								
			if( subject_str.indexOf(string_to_find) != -1 ){				
				jQuery("#popup_custom_email_template #et_from").val('<?php echo ACCOUNTS_EMAIL; ?>');
			}
			
			jQuery("#popup_custom_email_template #email_temp_id").val(ret.email_templates_id);
			jQuery("#popup_custom_email_template #et_subject").val(ret.subject);
			jQuery("#popup_custom_email_template #et_body").val(bodyEmailFiltered);

			if( parseInt(et_id) == 35 ){ // "Permission to collect keys (email AGENT and TENANT)" template
				jQuery("#bottom_text").show();
				jQuery("#bottom_text").html("<strong style='color:red;'>YOU MUST CC AGENCY EMAIL</strong>");
			}else{
				jQuery("#bottom_text").hide();
				jQuery("#bottom_text").html("");
			}
			


		});	
		
	});
	
});
</script>
</body>
</html>
