<?php
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

/*
echo "
tas_connected: {$tas['tas_connected']}
agency_id: {$rsGet_propme['agency_id']}
";
*/

?>
<style type="text/css">
.jinner_tab{
	/*width:850px*/
	width:auto;
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
#new_tenant_form table input {
	width: 130px;
}
.send_sms_icon{
	position: relative;
	top: 3px;
}
#tabsss .c-tab__content {
    overflow: auto;
}
#new_tenant_form table tr td:nth-child(1) input {
    width: 15px !important;
}
</style>

<div style="display:none;" id="new_tenant_form">
		<table style="table-layout: fixed;width: 100%;">
			<!-- <thead> -->
				<tr>
					<th width="5">Primary</th>
					<th width="18">First Name</th>
					<th width="18">Last Name</th>
					<th width="18">Mobile</th>
					<th width="18">Landline</th>
					<th width="18">Email</th>
					<th width="10">&nbsp;</th>
				</tr>
			<!-- </thead> -->
			<!-- <tbody> -->
				<tr>
					<td><input type="checkbox" name="is_primary" id="new_t_is_primary" value="0"></td>
					<td><input type="text" id="new_t_fname"></td>
					<td><input type="text" id="new_t_lname"></td>
					<td><input type="text" class="tenant_mobile_field" id="new_t_mobile"></td>
					<td><input type="text" class="tenant_phone_field" id="new_t_landline"></td>
					<td><input type="text" id="new_t_email"></td>
					<td style="text-align: center;">
						<button type="button" id="save_new_tenant_btn" class="submitbtnImg">
							<img class="inner_icon" src="images/button_icons/save-button.png">
							<span class="inner_icon_span">Save</span>
						</button>

					</td>
				</tr>
			<!-- </tbody> -->
		</table>
</div>

<div id="tabsss" class="c-tabs no-js">
	<div class="c-tabs-nav">
		<a href="#" data-tab_index="11" data-tab_name="active_tenant" class="nav_tenants is-active">Active</a>	
		<a href="#" data-tab_index="12" data-tab_name="inactive_tenant" class="nav_tenants">Inactive</a>		
	</div>
	<div class="c-tab-tenants is-active" data-tab_cont_name="active_tenant">
		<div class="c-tab__content" style="padding:0px !important;">	
			<div class="jinner_tab">
				<?php include 'tenant_panel_with_api.php'; ?>
			</div>
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
<script>
  var myTabs = tabs({
	el: '#tabsss',
	tabNavigationLinks: '.nav_tenants',
	tabContentContainers: '.c-tab-tenants'
  });

  myTabs.init();
</script>	







<script type="text/javascript">
jQuery(document).ready(function(){
	
	
	
	// fancy box
	jQuery(".sms_fb_link").fancybox();
	
	jQuery(".sms_icon_new").click(function(){
		
		jQuery(this).parents("td.job_action_div").find(".sms_fb_link").click();
		
	});
	
	
	<?php
	if( $tas['tas_connected'] == 1 ){ ?>
		jQuery(".propertyme_tenant_tab").click(function(){
		
			//console.log("pm tab");
			
			var pm_tab_cont = jQuery(".pm_api_tbl").length;
			
			if( pm_tab_cont == 0 ){
				
				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "ajax_get_pm_tab_api.php",
					data: { 
						property_id: <?php echo $property_id; ?>,
						job_id: <?php echo $job_id; ?>
					}
				}).done(function( ret ){	
							
					jQuery(".ajax_pm_div").html(ret);
					// run tenants validation
					tenants_validation('pm_tab');
					jQuery("#load-screen").hide();
					//window.location="view_job_details_v2.php?id=<?php echo $job_id; ?><?php echo $added_param; ?>";		
				});	
				
			}
			
			
		});
	<?php
	}
	?>



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
		var new_t_is_primary = $("#new_t_is_primary").val();
		var new_t_fname = $("#new_t_fname").val();
		var new_t_lname = $("#new_t_lname").val();
		var new_t_mobile = $("#new_t_mobile").val();
		var new_t_landline = $("#new_t_landline").val();
		var new_t_email = $("#new_t_email").val();

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
				data: { 'property_id': <?php echo $property_id ?>, 'tenant_priority' : new_t_is_primary, 'tenant_firstname' : new_t_fname, 'tenant_lastname' : new_t_lname, 'tenant_mobile' : new_t_mobile, 'tenant_landline' : new_t_landline, 'tenant_email' : new_t_email, 'active': 1 }
			}).done(function( ret ){
				window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";	
			});

	});
	
	
});


$('.edit-tenant').on('click', function(){
	var id = $(this).attr('tid');
	$('.tenant_fields' + id).css('display','block');
	$('.save-buttton' + id).css('display','block');
	$('.tenant_labels' + id).css('display','none');
	$('.default-buttton' + id).css('display','none');
	$('.tenant_priority'+id).css('display','none');
  $('.tenant_priority_cb'+id).css('display','block');
});

$('.cancel-tenant').on('click', function(){
	var id = $(this).attr('tid');
	$('.tenant_fields' + id).css('display','none');
	$('.save-buttton' + id).css('display','none');
	$('.tenant_labels' + id).css('display','block');
	$('.default-buttton' + id).css('display','inline-block');
	$('.tenant_priority'+id).css('display','block');
  $('.tenant_priority_cb'+id).css('display','none');
});


$('.reactivate-tenant').on('click', function(){
	var id = $(this).attr('tid');

	if(confirm('Are you sure you want to reactivate this tenant?')){
		$.ajax({
			url: 'ajax_function_tenants.php?f=reActivateTenant',
			type: 'POST',
			data: { 'tenant_id' : id}
		}).done(function( ret ){	
			window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";		
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
	var tenant_priority = $('.tenant_priority_cb' + id).val();

	var errMsg = "";
	if(tenant_firstname==""){
		errMsg +="Please Enter First Name \n";
	}
	/*
	if(tenant_lastname==""){
		errMsg +="Please Enter Last Name \n";
	}
	if(tenant_landline==""){
		errMsg +="Please Enter Landline \n";
	}
	if(tenant_mobile==""){
		errMsg +="Please Enter Mobile \n";
	}
	if(tenant_email==""){
		errMsg += "Please Enter Email \n";
	}
	*/
	
	if(errMsg!=""){
		alert(errMsg);
		return false;
	}

	$.ajax({
		url: 'ajax_function_tenants.php?f=saveTenant',
		type: 'POST',
		data: { 'tenant_id' : id, 'tenant_priority': tenant_priority, 'tenant_firstname' : tenant_firstname, 'tenant_lastname' : tenant_lastname, 'tenant_mobile' : tenant_mobile, 'tenant_landline' : tenant_landline, 'tenant_email' : tenant_email }
	}).done(function( ret ){	
		window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";		
	});
});

$('.delete-tenant').on('click', function(){
	if(confirm('Are you sure you want to deactivate this tenant?')){
		$.ajax({
			url: 'ajax_function_tenants.php?f=deleteTenant',
			type: 'POST',
			data: { 'tenant_id' : $(this).attr('tid') }
		}).done(function( ret ){	
			window.location="<?php echo $page_url; ?>?id=<?php echo $job_id; ?><?php echo $added_param; ?>";		
		});
	}
});


$("#new_t_is_primary, .tp_checkbox").on('click', function() {
    if ($(this).is(":checked") == true) {
        $(this).val(1);
        $(this).attr('checked', 'checked');
    } else {
        $(this).val(0);
        $(this).removeAttr('checked');
    }
});
</script>
