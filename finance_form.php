<?php

$title = "Finance Form";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_REQUEST['job_id']);



$sql_str = "
	SELECT 
		p.`address_1` AS p_address1,
		p.`address_2` AS p_address2,
		p.`address_3` AS p_address3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		p.`landlord_firstname`,
		p.`landlord_lastname`,
		p.`landlord_email`,
		a.`contact_email`
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`		
	WHERE j.`id` = {$job_id}
";
$job_sql = mysql_query($sql_str);
$job = mysql_fetch_array($job_sql);


$street_address = "{$job['p_address1']} {$job['p_address2']} {$job['p_address3']} {$job['p_state']}";
$postcode = $job['p_postcode'];
$landlord_name = "{$job['landlord_firstname']} {$job['landlord_lastname']}";
$landlord_email = $job['landlord_email'];
$ac_email = $job['contact_email'];

$from_email = 'info@sats.com.au';
$subject = "QLD Upgrade finance for {$street_address}";

// spaces are important here and will be rendered exactly on textarea
$email_body = "
Good Morning/Afternoon

Smoke Alarm Testing Services are pleased to provide you the below finance option to assist in upgrading {$street_address} to meet the new QLD Smoke Alarm requirements. Please see the below information and unique link that allows for easy payment.

{INSERT FORM TEXT HERE}

Once payment is made we will organise to attend the property and perform the upgrade as per quote.

Regards,
Smoke Alarm Testing Services (SATS)
";




?>
<style>
.left_panel{
	float: left; 
	margin-right: 10px;
}
.right_panel2{
	float: right;
	width: 43%;
}
#email_body{
	height: 500px; 
	margin: 0;
}
#finance_link_btn{
	float: left; 
	margin-bottom: 23px;
}
.left_panel_lbl{
	float: left;
	padding-top: 50px;
	margin-left: 10px;
}
.right_lbl{
	text-align: left;
	font-size: 13px;
	position: absolute;
}
.street_address_lbl{
	top: 449px;
}
.postcode_lbl{
	top: 468px;
}
.landlord_name_lbl1 {
    top: 488px;
}
.landlord_name_lbl2 {
    top: 506px;
}
.landlord_email_lbl{
	top: 576px;
}
.finance_btn_add{
	top: 670px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="finance_form.php?job_id=<?php echo $job_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Email Sent</div>
	<?php
	}
	?>
		

		
		
		
		
		<div class="left_panel">
		
		
			<div style="margin: 40px 0;text-align: left;">
				<h2 class="heading">STEPS</h2>
				<ol>
					<li>Press 'Finance Form' Button</li>
					<li>Fill out the form like below</li>
					<li>Press 'Add' button on from</li>
					<li>Press 'eFund(TM)' button</li>
					<li>Copy text from Pop up into the body of the email templates</li>
					<li>Press "Send" Button</li>
				</ol>
			</div>
		
			<div style="clear:both;"></div>
		
			<div>
				<a href="https://efund.com.au/Principal/Logon.aspx?lo=1" target="_blank">
					<button class="submitbtnImg blue-btn" id="finance_link_btn" type="submit">
						Finance Form
					</button>
				</a>
			</div>
			
			<div style="clear:both;"></div>
		
			<div style="text-align:left;">
				
				<div style="float:left;">
					<img src="images/principal_finance_business_loan_form.png" />
				</div>
	
				
		
				<div class="left_panel_lbl">
		
					<div class="right_lbl street_address_lbl"><?php echo ( $street_address!='' )?"{$street_address}":'&nbsp;'; ?></div>
					<div class="right_lbl postcode_lbl"><?php echo ( $postcode!='' )?"{$postcode}":'&nbsp;'; ?></div>
					
					<div class="right_lbl landlord_name_lbl1"><?php echo ( $landlord_name!='' )?"{$landlord_name}":'&nbsp;'; ?></div>
					<div class="right_lbl landlord_name_lbl2"><?php echo ( $landlord_name!='' )?"{$landlord_name}":'&nbsp;'; ?></div>
					<div class="right_lbl landlord_email_lbl"><?php echo ( $landlord_email!='' )?"{$landlord_email}":'&nbsp;'; ?></div>
					
					<div class="right_lbl finance_btn_add">PRESS HERE</div>
					
				</div>
	
				
			</div>
			
		</div>
		
		
		
		<div class="right_panel2">
		<form id="jform" action="finance_form_submit.php" method="post">
			<table>
				<tr>
					<td>To</td>
					<td>
						<input type="text"  class="addinput" name="to_email" id="to_email" value="<?php echo ( $ac_email!='' )?$ac_email:''; ?>" />
					</td>
				</tr>
				<tr>
					<td>From</td>
					<td>
						<input type="text"  class="addinput" name="from_email" id="from_email" value="<?php echo $from_email; ?>" readonly="readonly" />
					</td>
				</tr>
				<tr>
					<td>Subject</td>
					<td>
						<input type="text"  class="addinput" name="subject" id="subject" value="<?php echo $subject; ?>" />
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<textarea class="addtextarea" name="email_body" id="email_body"><?php echo $email_body; ?></textarea>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<button class="submitbtnImg" id="btn_send_email" type="submit" style="float: left;">Send</button>
					</td>
				</tr>				
			</table>
			<input type="hidden" name="job_id" value="<?php echo $job_id; ?>" />
		</form>
		</div>
		
		

    
  </div>

<br class="clearfloat" />


<script>
jQuery(document).ready(function(){
	
	
	
	jQuery("#jform").submit(function(){
	
		var to_email = jQuery("#to_email").val();
		var from_email = jQuery("#from_email").val();
		var subject = jQuery("#subject").val();
		var email_body = jQuery("#email_body").val();
		
		if( to_email == "" ){
			error += "To email is required\n";
		}
		if( from_email == "" ){
			error += "From email is required\n";
		}
		if( subject == "" ){
			error += "Subject is required\n";
		}
		if( email_body == "" ){
			error += "Email Body is required\n";
		}
		
		
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
		
		
	});

	
	
});
</script>

</body>
</html>
