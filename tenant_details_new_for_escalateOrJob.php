<?php

include('inc/init.php');
$crm = new Sats_Crm_Class();

$property_id = $_POST['property_id'];

$sqlGet_propme = mysql_query("SELECT `propertyme_prop_id`,`agency_id` FROM `property` WHERE `property_id`=".$property_id);
$rsGet_propme = mysql_fetch_array($sqlGet_propme);
if($rsGet_propme['propertyme_prop_id'] != "" OR !empty($rsGet_propme['propertyme_prop_id'])){


$getA = mysql_query("SELECT `propertyme_agency_id` FROM `agency` WHERE `agency_id`=".$rsGet_propme['agency_id']);
$rsA = mysql_fetch_array($getA);
//$propertyme_api->getAgencyDetails($rsA['propertyme_agency_id']);

//$prop = $propertyme_api->getPropertyDetails($rsGet_propme['propertyme_prop_id']);

}

// get Trust Acct. Software  
$tas_sql = mysql_query("
	SELECT `tas_connected`
	FROM `agency`
	WHERE `agency_id` = {$rsGet_propme['agency_id']}
");
$tas= mysql_fetch_array($tas_sql);


?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">

<style type="text/css">
.jinner_tab{
	width:870px
}
.jinner_tab .empty{
	position: relative;
	left: 10px;
}
#inner_new_tenants_tbl tr td{
	margin: 0;	
}
#inner_new_tenants_tbl tr td .addinput{
	margin: 0;	
}
.cancel-tenant .inner_icon{
	margin: 0;
}
#tabsss table tr td{
	padding: 0 0 0 10px;
	font-size:13px;
}
#tabsss a.nav_tenants{
	font-size:13px;
}
#new_tenant_form table th{
	color:#000;
}
#new_tenant_form{
	border: 1px solid;
	margin-bottom: 15px;
	border:1px solid #cccccc;
}
.c-tab__content{
    height: auto !important;
}
</style>


<div style="float:right;clear:both;margin-bottom:5px;">
                                <button type="button" id="add_new_tenant_btn" class="submitbtnImg blue-btn">
									<img class="inner_icon" src="images/button_icons/add-button.png"> 
									<span class="inner_icon_txt">Tenant</span>
							    </button>
                            </div>
                            <div style="clear:both;"></div>
<div style="display:none;" id="new_tenant_form">
		<table>
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Mobile</th>
					<th>Landline</th>
					<th>Email</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="text" id="new_t_fname"></td>
					<td><input type="text" id="new_t_lname"></td>
					<td><input type="text" class="tenant_mobile_field" id="new_t_mobile"></td>
					<td><input type="text" class="tenant_phone_field" id="new_t_landline"></td>
					<td><input type="text" id="new_t_email"></td>
					<td>
						<button type="button" id="save_new_tenant_btn" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/save-button.png">
							<span class="inner_icon_span">Save</span>
						</button>

					</td>
				</tr>
			</tbody>
		</table>
</div>

<div id="tabsss" class="c-tabs no-js">
	<div class="c-tabs-nav">
		<a href="#" data-tab_index="11" data-tab_name="active_tenant" class="nav_tenants is-active">Active</a>	
		<a href="#" data-tab_index="12" data-tab_name="inactive_tenant" class="nav_tenants">Inactive</a>		
	</div>
	<div class="c-tab-tenants is-active" data-tab_cont_name="active_tenant">
		<div class="c-tab__content" style="padding:0px !important;">	
			<?php 
		//$sqlGetTenants = mysql_query("SELECT * FROM `property_tenants` WHERE `active` = 1 AND `property_id`=".$property_id);

		$params = array('property_id' => $property_id,'active' => 1);
		$sqlGetTenants = $crm->getNewTenantsData($params);
		
		//echo "booked_with: ".$row['booked_with'];

		?>
		<?php if(mysql_num_rows($sqlGetTenants) > 0){?>
		<div class="jinner_tab">
		<table id="inner_new_tenants_tbl" border="0" cellpadding="5" cellspacing="4" style="border-bottom:1px solid #FFF !important; border-left:2px solid #FFF !important; margin-left:-3px !important; border-right:2px solid #FFF !important; margin-right:-3px !important;">
			<tr style="height:36px !important;">
				<td style="font-weight:bold !important;">First Name</td>
				<td style="font-weight:bold !important;">Last Name</td>
				<td style="font-weight:bold !important;">Mobile</td>
				<td style="font-weight:bold !important;">Landline</td>
				<td style="font-weight:bold !important;">Email</td>
				<td style="font-weight:bold !important;">Action</td>
				
			</tr>
			<?php while($rsTenants = mysql_fetch_array($sqlGetTenants)){
			
			// booked with mobile
			if( $row['booked_with'] == $rsTenants['tenant_firstname'] ){
				$booked_with_mobile = $rsTenants['tenant_mobile'];
			}
			
			?>
			<tr style="height:36px !important;" class="jtenant_div">
				<td>
					<input type="text" name="tenant_firstname" id="tenant_firstname_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_fname_field" style="display:none; width:100px !important;" value="<?=stripslashes($rsTenants['tenant_firstname'])?>">
					<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_firstname_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_firstname']?></span>
				</td>
				<td>
					<input type="text" name="tenant_lastname" id="tenant_lastname_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_lname_field" style="display:none; width:100px !important;" value="<?=stripslashes($rsTenants['tenant_lastname'])?>">
					<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_lastname_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_lastname']?></span>
				</td>
				<td>
					<input type="text" name="tenant_mobile" id="tenant_mobile_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_mobile_field" style="display:none; width:100px !important;" value="<?=$rsTenants['tenant_mobile']?>">
					<?php if($rsTenants['tenant_mobile'] != ""){?>
					<a href="tel:<?=trim($rsTenants['tenant_mobile'])?>">
					<span  class="tenant_labels<?=$rsTenants['property_tenant_id']?> tenant_mobile_field_v2" id="tenant_mobile_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_mobile']?></span>
					</a>
					<?php }?>
				</td>
				<td>
					<input type="text" name="tenant_landline" id="tenant_landline_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_phone_field" style="display:none; width:100px !important;" value="<?=$rsTenants['tenant_landline']?>">
					<?php if($rsTenants['tenant_landline'] != "" OR $rsTenants['tenant_landline'] == "__ ____ ____"){?>
					<a href="tel:<?=trim($rsTenants['tenant_landline'])?>">
					<span  class="tenant_labels<?=$rsTenants['property_tenant_id']?> tenant_phone_field_v2" id="tenant_landline_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_landline']?></span>
					</a>
					<?php }?>
				</td>
				<td>
					<input type="email" name="tenant_email[]" id="tenant_email_det<?=$rsTenants['property_tenant_id']?>" class="addinput tenant_fields<?=$rsTenants['property_tenant_id']?> tenant_email tenant_email_field" style="display:none; width:200px !important;" value="<?=$rsTenants['tenant_email']?>">
					<span class="tenant_labels<?=$rsTenants['property_tenant_id']?>" id="tenant_email_lbl<?=$rsTenants['property_tenant_id']?>"><?=$rsTenants['tenant_email']?></span>
				</td>
				
				<td style="white-space:normal;padding:10px;" class="job_action_div">

					<a title="Edit" class="edit-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
					<img src="/images/button_icons/edit-tenant.png" class="default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
					</a>

					<a title="Deactivate" class="delete-tenant" tid="<?=$rsTenants['property_tenant_id']?>">
					<img src="/images/button_icons/delete-tenant.png" class="default-buttton<?=$rsTenants['property_tenant_id']?>" style="cursor: pointer;" />
					</a>

				
				

				
					
					

					

					
					<button style="display:none; float: left; margin-right: 5px;" type="button" class="blue-btn submitbtnImg save-buttton<?=$rsTenants['property_tenant_id']?> btn-save" tid="<?=$rsTenants['property_tenant_id']?>">
						Save
					</button>
					<button style="display:none; float: left;" type="button" class="submitbtnImg cancel-tenant save-buttton<?=$rsTenants['property_tenant_id']?> btn-cancel" tid="<?=$rsTenants['property_tenant_id']?>">
						Cancel
					</button>
				</td>
				
			</tr>

			<?php 
			}
			//echo "booked_with_mobile: {$booked_with_mobile}";
			?>
			
		</table>
		</div>
		<?php }else{
			echo "<div class='jinner_tab'><p class='empty'>No tenant found.</p></div>";
		}?>
		</div>
	</div>
	<div class="c-tab-tenants inactive_tenant_tab_div"  data-tab_cont_name="inactive_tenant">
		<div class="c-tab__content" style="padding:0px !important;">
			<?php 
		$sqlGetInActiveTenants = mysql_query("SELECT * FROM `property_tenants` WHERE `active` = 0 AND `property_id`=".$property_id);
		?>
		<?php if(mysql_num_rows($sqlGetInActiveTenants) > 0){?>
		<div class="jinner_tab">
		<table border="0" cellpadding="5" cellspacing="4" style="border-bottom:1px solid #FFF !important; border-left:2px solid #FFF !important; margin-left:-3px !important; border-right:2px solid #FFF !important; margin-right:-3px !important;">
			<tr style="height:36px !important;">
				<td style="font-weight:bold !important;">First Name</td>
				<td style="font-weight:bold !important;">Last Name</td>
				<td style="font-weight:bold !important;">Mobile</td>
				<td style="font-weight:bold !important;">Landline</td>
				<td style="font-weight:bold !important;">Email</td>
				<td style="font-weight:bold !important;">Inactive Date</td>
				<td style="font-weight:bold !important;" class="jcenter">Reactivate</td>
			</tr>
			<?php while($rsInActiveTenants = mysql_fetch_array($sqlGetInActiveTenants)){?>
			<tr style="height:36px !important;">
				<td style="opacity: 0.5;"><?=$rsInActiveTenants['tenant_firstname']?></td>
				<td style="opacity: 0.5;"><?=$rsInActiveTenants['tenant_lastname']?></td>
				<td style="opacity: 0.5;"><?=$rsInActiveTenants['tenant_mobile']?></td>
				<td style="opacity: 0.5;"><?=$rsInActiveTenants['tenant_landline']?></td>
				<td style="opacity: 0.5;"><?=$rsInActiveTenants['tenant_email']?></td>
				<td style="opacity: 0.5;"><?=date('d/m/Y H:i', strtotime($rsInActiveTenants['modifiedDate']))?></td>
				<td class="jcenter">
					<center>
						<a title="Reactivate" class="reactivate-tenant" tid="<?=$rsInActiveTenants['property_tenant_id']?>">
						<img src="/images/button_icons/reactivate-tenant.png" style="height: 20px; cursor: pointer;" />
					</a>
					</center>
				</td>
			</tr>
			<?php }?>
		</table>
		</div>
		<?php }else{
			echo "<div class='jinner_tab'><p class='empty'>No inactive tenant found.</p></div>";
		}?>
		</div>
	</div>

	<div class="c-tab-tenants propertyme_tenant_tab_div"  data-tab_cont_name="propertyme_tenant">
		<div class="c-tab__content" style="padding:0px !important;">
			<div class="ajax_pm_div">
				<div class="jinner_tab"><p class="empty">This Agency is not connected to PropertyMe.</p></div>
			</div>
		</div>
	</div>	
	
</div>
<link rel="stylesheet" href="css/responsive_tab_v2.css">
<script src="js/responsive_tabs.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>


<script type="text/javascript">
  var myTabs = tabs({
	el: '#tabsss',
	tabNavigationLinks: '.nav_tenants',
	tabContentContainers: '.c-tab-tenants'
  });

  myTabs.init();
</script>	







<script type="text/javascript">
jQuery(document).ready(function(){




	// invoke maskinput plugin
		// mobile
		var mobile_mask = '<?php echo ($_SESSION["country_default"]==1)?'?9999 999 999':'?999 9999 9999'; ?>';
		jQuery(".tenant_mobile_field").mask(mobile_mask);

		jQuery(".tenant_mobile_field").blur(function(){
			
			var mobile = jQuery(this).val();
			
			var mobile_err_msg_format = 'Format to be <?php echo ($_SESSION["country_default"]==1)?'0412 222 222':'041 2222 2222'; ?>';
			//var mobile_err_msg_format = 'Format to be 0412 222 222';
			
			var mobile_length = <?php echo ($_SESSION["country_default"]==1)?12:13; ?>;
			
			if(mobile.length!=mobile_length){
				//alert("Phone Number format should be xx xxxx xxxx");
				//jQuery(this).addClass('error_border');
				//jQuery(this).removeClass('green_border');
				if(mobile.length!=0){
					//jQuery(this).parents(".jtenant_div:first").find(".tenant_mobile_error").css("visibility","visible");
					jQuery(this).addClass('jred_border_higlight');
					jQuery(this).attr('title',mobile_err_msg_format);
				}				
				jQuery(this).click(function(e){e.preventDefault();});
			}else{
				jQuery(this).removeClass('jred_border_higlight');
				jQuery(this).removeAttr('title');
				//jQuery(this).addClass('green_border');
				//jQuery(this).parents(".jtenant_div:first").find(".tenant_mobile_error").css("visibility","hidden");
			}
			
		});

		// landline
	var phone_mask = '<?php echo ($_SESSION["country_default"]==1)?'?99 9999 9999':'?99 9999 999'; ?>';
	jQuery(".tenant_phone_field").mask(phone_mask);

	jQuery(".tenant_phone_field").blur(function(){
		
		var phone_err_msg_format = 'Format to be <?php echo ($_SESSION["country_default"]==1)?'02 2222 2222':'02 2222 222'; ?>';

		//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").html(phone_err_msg_format );
		
		var phone = jQuery(this).val();
		var phone_length = <?php echo ($_SESSION["country_default"]==1)?12:11; ?>;
		
		if(phone.length!=phone_length){

			//jQuery(this).removeClass('green_border');
			if(phone.length!=0){
				//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").css("visibility","visible");
				jQuery(this).addClass('jred_border_higlight');
				jQuery(this).attr('title',phone_err_msg_format);
			}			
			jQuery(this).click( function(e){ e.preventDefault(); } );
			
		}else{
			jQuery(this).removeClass('jred_border_higlight');
			jQuery(this).removeAttr('title');
			//jQuery(this).addClass('green_border');
			//jQuery(this).parents(".jtenant_div:first").find(".tenant_phone_error").css("visibility","hidden");
		}
		
	});
	
	
	
	// fancy box
	jQuery(".sms_fb_link").fancybox();
	
	jQuery(".sms_icon_new").click(function(){
		
		jQuery(this).parents("td.job_action_div").find(".sms_fb_link").click();
		
	});




	$('#add_new_tenant_btn').click(function(e){
		
		var btn_txt = jQuery(this).find(".inner_icon_txt").html();
		var orig_btn_txt = 'Tenant';
		var orig_btn_icon = 'images/button_icons/add-button.png';
		var cancel_btn_icon = 'images/button_icons/cancel-button.png';
		
		if( btn_txt == orig_btn_txt ){
			jQuery(this).removeClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html('Cancel');
			jQuery(this).find(".inner_icon").attr("src",cancel_btn_icon)
			jQuery("#new_tenant_form").show();
		}else{
			jQuery(this).addClass('blue-btn');
			jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
			jQuery(this).find(".inner_icon").attr("src",orig_btn_icon)
			jQuery("#new_tenant_form").hide();
		}
		
	});

	$('#save_new_tenant_btn').click(function(e){
		e.preventDefault();
		var new_t_fname = $("#new_t_fname").val();
		var new_t_lname = $("#new_t_lname").val();
		var new_t_mobile = $("#new_t_mobile").val();
		var new_t_landline = $("#new_t_landline").val();
		var new_t_email = $("#new_t_email").val();
		var obj = $(this);

		var errorMsg = "";
		if(new_t_fname==""){
			errorMsg +="Please Enter First Name \n";
		}
		/*
		if(new_t_lname==""){
			errorMsg +="Please Enter Last Name \n";
		}
		if(new_t_mobile==""){
			errorMsg +="Please Enter Mobile \n";
		}
		if(new_t_landline==""){
			errorMsg +="Please Enter Landline \n";
		}
		if(new_t_email==""){
			errorMsg +="Please Enter Email \n";
		}
		*/
		if(errorMsg!=""){
			alert(errorMsg);
			return false;
		}
		
			$.ajax({
				url: 'ajax_function_tenants.php?f=newTenant',
				type: 'POST',
				data: { 'property_id': <?php echo $property_id ?>, 'tenant_firstname' : new_t_fname, 'tenant_lastname' : new_t_lname, 'tenant_mobile' : new_t_mobile, 'tenant_landline' : new_t_landline, 'tenant_email' : new_t_email, 'active': 1 }
			}).done(function( ret ){

						$.alert({
							title: 'Success',
							content: 'Tenant succesfully added',
							useBootstrap: false,
							boxWidth: '250px',
							buttons:{
									OK: function () {
										obj.parents(".tenant_v2_box").empty().load('tenant_details_new_for_escalateOrJob.php', {property_id: <?php echo $property_id ?>});
									}
							}
						});

			});

	});
	
	
});


$('.edit-tenant').on('click', function(){
	var id = $(this).attr('tid');
	$('.tenant_fields' + id).css('display','block');
	$('.save-buttton' + id).css('display','block');
	$('.tenant_labels' + id).css('display','none');
	$('.default-buttton' + id).css('display','none');
});

$('.cancel-tenant').on('click', function(){
	var id = $(this).attr('tid');
	$('.tenant_fields' + id).css('display','none');
	$('.save-buttton' + id).css('display','none');
	$('.tenant_labels' + id).css('display','block');
	$('.default-buttton' + id).css('display','inline-block');
});


$('.reactivate-tenant').on('click', function(){
	var id = $(this).attr('tid');
	var obj = $(this);

	if(confirm('Are you sure you want to reactivate this tenant?')){
		$.ajax({
			url: 'ajax_function_tenants.php?f=reActivateTenant',
			type: 'POST',
			data: { 'tenant_id' : id}
		}).done(function( ret ){	

					$.alert({
						title: 'Success',
						content: 'Tenant succesfully reactivated',
						useBootstrap: false,
						boxWidth: '250px',
						buttons:{
								OK: function () {
									obj.parents('.tenant_v2_box').empty().load('tenant_details_new_for_escalateOrJob.php', {property_id: <?php echo $property_id ?>});
								}
						}
					});	

		});
	}

});

$('.btn-save').on('click', function(){
	var id = $(this).attr('tid');
	var tenant_firstname = $('#tenant_firstname_det' + id).val();
	var tenant_lastname = $('#tenant_lastname_det' + id).val();
	var tenant_mobile = $('#tenant_mobile_det' + id).val();
	var tenant_landline = $('#tenant_landline_det' + id).val();
	var tenant_email = $('#tenant_email_det' + id).val();
	var obj = $(this);

	var errMsg = "";
	if(tenant_firstname==""){
		errMsg +="Please Enter First Name \n";
	}
	
	
	if(errMsg!=""){
		alert(errMsg);
		return false;
	}

	$.ajax({
		url: 'ajax_function_tenants.php?f=saveTenant',
		type: 'POST',
		data: { 'tenant_id' : id, 'tenant_firstname' : tenant_firstname, 'tenant_lastname' : tenant_lastname, 'tenant_mobile' : tenant_mobile, 'tenant_landline' : tenant_landline, 'tenant_email' : tenant_email }
	}).done(function( ret ){

					$.alert({
						title: 'Success',
						content: 'Tenant successfully updated',
						useBootstrap: false,
						boxWidth: '250px',
						buttons:{
								OK: function () {
									obj.parents('.tenant_v2_box').empty().load('tenant_details_new_for_escalateOrJob.php', {property_id: <?php echo $property_id ?>});
								}
					}
					});
	});
});

$('.delete-tenant').on('click', function(){
	var obj = $(this);
	if(confirm('Are you sure you want to deactivate this tenant?')){
		$.ajax({
			url: 'ajax_function_tenants.php?f=deleteTenant',
			type: 'POST',
			data: { 'tenant_id' : $(this).attr('tid') }
		}).done(function( ret ){	

				$.alert({
					title: 'Success',
					content: 'Tenant successfully deactivated',
					useBootstrap: false,
					boxWidth: '250px',
					buttons:{
							OK: function () {
								obj.parents('.tenant_v2_box').empty().load('tenant_details_new_for_escalateOrJob.php', {property_id: <?php echo $property_id ?>});
							}
					}
				});

		});
	}
});





</script>
