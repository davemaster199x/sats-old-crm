<?php 

include('inc/init_for_ajax.php');

$propertyme_api = new Propertyme_api;

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);



$sqlGet_propme = mysql_query("SELECT `propertyme_prop_id`,`agency_id` FROM `property` WHERE `property_id`=".$property_id);
$rsGet_propme = mysql_fetch_array($sqlGet_propme);
if($rsGet_propme['propertyme_prop_id'] != "" OR !empty($rsGet_propme['propertyme_prop_id'])){


$getA = mysql_query("SELECT `propertyme_agency_id` FROM `agency` WHERE `agency_id`=".$rsGet_propme['agency_id']);
$rsA = mysql_fetch_array($getA);
$propertyme_api->getAgencyDetails($rsA['propertyme_agency_id']);

$prop = $propertyme_api->getPropertyDetails($rsGet_propme['propertyme_prop_id']);

}


if(!empty($prop['Tenancy']) AND count($prop['Tenancy']) > 0){
$tenants = $propertyme_api->getContactDetails($prop['Tenancy']['ContactId']);
?>
<div class="jinner_tab">
<table class="pm_api_tbl" border="0" cellpadding="5" cellspacing="4" style="border-bottom:1px solid #FFF !important; border-left:2px solid #FFF !important; margin-left:-3px !important; border-right:2px solid #FFF !important; margin-right:-3px !important;">
<tr>
<td style="font-weight:bold !important;">First Name</td>
<td style="font-weight:bold !important;">Last Name</td>
<td style="font-weight:bold !important;">Mobile</td>
<td style="font-weight:bold !important;">Home</td>
<td style="font-weight:bold !important;">Email</td>
<td style="font-weight:bold !important;" class="jcenter">Actions</td>
</tr>	
<?php  
$xapi = 0;
foreach($tenants['ContactPersons'] as $tenant){ 
$xapi++;
?>
<tr>
<td>
	<?=$tenant['FirstName']?>
	<input type="hidden" class="pm_FirstName" value="<?=$tenant['FirstName']?>" />
</td>
<td>
	<?=$tenant['LastName']?>
	<input type="hidden" class="pm_LastName" value="<?=$tenant['LastName']?>" />
</td>
<td>
	<?php if($tenant['CellPhone'] != ""){?>
	<a href="tel:0<?=trim($tenant['CellPhone'])?>">
	<span style="border-bottom:1px solid #b4151b;" class="tenant_mobile_field_v2"><?=$tenant['CellPhone']?></span>
	</a>
	<?php }?>
	<input type="hidden" class="pm_CellPhone" value="<?=$tenant['CellPhone']?>" />
</td>
<td>
	<?php 
	$phone_class =  'tenant_phone_field_v2';
	if($tenant['HomePhone'] != ""){
	?>	
		<?php if(count($tenant['HomePhone']) < 10){?>
			<a href="javascript:;" onclick="alert('Invalid Phone Number.\nPhone Number must be 10 digits.');">
				<span style="border-bottom:1px solid #b4151b;" class="<?php echo $phone_class; ?>"><?=$tenant['HomePhone']?></span>
			</a>
		<?php }else { ?>
			<a href="tel:0<?=trim($tenant['HomePhone'])?>">
				<span style="border-bottom:1px solid #b4151b;" class="<?php echo $phone_class; ?>"><?=$tenant['HomePhone']?></span>
			</a>
		<?php }?>
	
	<?php }?>
	<input type="hidden" class="pm_HomePhone" value="<?=$tenant['HomePhone']?>" />
</td>
<td>
	<?=$tenant['Email']?>
	<input type="hidden" class="pm_Email" value="<?=$tenant['Email']?>" />
</td>
<td class="jcenter">


	<button type="button" class="save_pm_tenants_btn blue-btn submitbtnImg" data-pm_tenant_id="<?=$tenant['Id']?>">
		<img class="inner_icon" src="images/button_icons/save-button.png">
		<span class="inner_icon_span">Save</span>						
	</button>


	<?php if($tenant['Email'] != ""){?>
	<a target="_blank" id="custom_email_link" href="send_email_template.php?job_id=<?=$job_id?>&to_email=<?=$tenant['Email']?>">
		<img src="/images/button_icons/mail-tenant.png" class="email_icon" style="cursor: pointer;" />
	</a>
	<?php }else { ?>
		<img src="/images/button_icons/mail-tenant-disable.png" class="email_icon" />									
	<?php }?>

	<?php if( $propertyme_api->checkSmsforToday($job_id) ){ ?>
	<div class="row" style="display:inline-block;">
		<?php if($tenant['CellPhone'] != ""){?>
		<img src="/images/button_icons/sms-tenant.png" onclick="document.getElementById('api_tenant<?=$xapi?>').style.display='block'" class="sms_icon" style="cursor: pointer;" />									
		<?php }else { ?>
		<img src="/images/button_icons/sms-tenant-disable.png" class="sms_icon" />									
		<?php }?>
	</div> 
	<?php }?>

	<div id="api_tenant<?=$xapi?>" class="w3-modal">
		<div class="w3-modal-content">
		  <div class="w3-container" style="padding-bottom:100px !important;">
			<span onclick="document.getElementById('api_tenant<?=$xapi?>').style.display='none'" class="w3-button w3-display-topright">&times;</span>
			
			<div class="sms_div sms_div_<?=$tenant['Id']?>">
				<h2 class="heading">SMS Template</h2>
				<?php
					// SMS TEMPLATE TEXTBOX
					foreach( $sms_temp_arr as $index => $sms_temp ){ ?>
						<div class="sms_temp_div_row">
							<div class="sms_temp_lbl">
								<div><strong><?php echo $sms_temp['sms_temp_name'] ?></strong></div>
								<?php
								if( $sms_temp['sms_temp_desc'] ){ ?>
									<div>(<?php echo $sms_temp['sms_temp_desc'] ?>)</div>
								<?php
								}								
								if( $sms_temp['sms_temp_ins'] ){ ?>
									<div class="sms_temp_insert_date jItalic"><?php echo $sms_temp['sms_temp_ins'] ?></div>
								<?php
								}
								?>												
								<div><span class="sms_temp_char_count"><?php echo $sms_temp['sms_temp_boxtext_cout'] ?></span> Char</div>
								<div><span class="sms_num_count"><span class="sms_num_count_val"><?php echo $sms_temp['sms_temp_num_count'] ?></span> SMS</span></div>
								<div><input type="radio" name="sms_temp_rad" class="sms_temp_rad" /></div>
							</div>	
							<textarea class="sms_temp_txtbox" style="width:600px !important;"><?php echo $sms_temp['sms_temp_boxtext']; ?></textarea>		
							<input type="hidden" class="tenant_mobile" value="<?=$tenant['CellPhone']?>" />							
							<input type="hidden" class="sms_type" value="<?php echo $sms_temp['sms_type']; ?>" />
							<input type="hidden" class="sms_sent_to_tenant" value="<span style='color:red'>(PM)</span> <?=$tenant['FirstName']?>" />
							<button class="submitbtnImg btn_sms" id="btn_sms" type="button">
								<img class="inner_icon" src="images/button_icons/sms_icon.png">
								SMS
							</button>	
						</div>
						<br />
					<?php
					}
					?>
				<br /><br />
			</div>
		  </div>
		</div>
	  </div>
</td>
</tr>
<?php }?>
</table>
</div>
<?php } else {
echo '<div class="jinner_tab"><p class="empty">This Agency is not connected to PropertyMe.</p></div>';
}
?>
<script>
jQuery(document).ready(function(){
	
	jQuery(".save_pm_tenants_btn").click(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first")
		var pm_tenant_id = obj.attr("data-pm_tenant_id");
		var pm_FirstName = row.find(".pm_FirstName").val();
		var LastName = row.find(".pm_LastName").val();
		var pm_CellPhone = row.find(".pm_CellPhone").val();
		var pm_HomePhone = row.find(".pm_HomePhone").val();
		var pm_Email = row.find(".pm_Email").val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_save_pm_tenants.php",
			data: { 
				property_id: <?php echo $property_id; ?>,
				pm_tenant_id: pm_tenant_id,
				pm_FirstName: pm_FirstName,
				LastName: LastName,
				pm_CellPhone: pm_CellPhone,
				pm_HomePhone: pm_HomePhone,
				pm_Email: pm_Email
			}
		}).done(function( ret ){	
			jQuery("#load-screen").hide();
			location.reload();
		});	
		
	});
	
});
</script>