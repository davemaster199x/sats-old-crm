<?php
$title = "Send Sales Emails";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

//print_r($_SESSION);

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_email = $_SESSION['USER_DETAILS']['Email'];
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);

// get job data
$jparams = array(
	'agency_id' => $agency_id
);
$job_sql = $crm->getAgency($jparams);
$row = mysql_fetch_array($job_sql);


// put account emails into an array
$account_emails_exp = explode("\n",trim($row['account_emails']));
// put agency emails into an array
$agency_emails_exp = explode("\n",trim($row['agency_emails']));

?>

    <div id="mainContent">
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>				
		<li class="other first"><a title="<?php echo $title; ?>" href="/send_sales_emails.php?agency_id=<?php echo $agency_id; ?>"><strong><?php echo $title; ?></strong></a></li>
		</ul>
	</div>
      
    
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Email Sent</div>
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
      	
	<form id="jform" action="send_sales_emails_script.php" method="POST" enctype="multipart/form-data">
	<div class="addproperty">
		
	<div id="popup_custom_email_template">
		
		<div class="addproperty">
		
			
		
			<div style="width:75%; float:left;">
			
				<h2 class="header" style="color:#b4151b"><?php echo $row['agency_name']; ?></h2>
			
				<div class="row email_to_row_div">
					<label class="addlabel">
						From:						
					</label>
					<input type="text" name="from_email" class="addinput et_from" id="et_from" value="<?php echo $logged_user_email; ?>" />
				</div>
			
				<div class="row email_to_row_div">
					<label class="addlabel">
						To:
						<img id="et_to_icon" data-target-id="et_to" class="inner_icon email_to_icon" src="images/profile.png" />
					</label>
					<input type="text" name="to_email" class="addinput et_to" id="et_to" style="padding-left: 40px;" />
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
						
						
						<input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>" />
					
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
					$temp_type = 1; //  Email Template Type - Sales
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
				<td>From:</td><td class="prev_et_from"></td>
			</tr>
			<tr>
				<td>To:</td><td class="prev_et_to"></td>
			</tr>
			<tr>
				<td>CC:</td><td class="prev_et_cc"></td>
			</tr>
			<tr>
				<td>Subject:</td><td class="prev_et_subj"></td>
			</tr>
			<tr>
				<td class="prev_et_body_lbl">Body:</td><td class="prev_et_body"></td>
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
    width: 700px;
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
</style>
<script>
jQuery(document).ready(function(){
	
	
	// fancy box
	jQuery("#preview_email_temp_link").fancybox();	
	
	
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
		jQuery("#send_to_div").hide();		
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
				agency_id: '<?php echo $agency_id; ?>'
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
		
		window.location = "send_email_template.php?agency_id=<?php echo $agency_id ?>";
		
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
			
			jQuery("#load-screen").hide();
			jQuery("#popup_custom_email_template #email_temp_id").val(ret.email_templates_id);
			jQuery("#popup_custom_email_template #et_subject").val(ret.subject);
			jQuery("#popup_custom_email_template #et_body").val(ret.body);

		});	
		
	});
	
});
</script>
</body>
</html>
