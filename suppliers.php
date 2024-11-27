<?php

$title = "Suppliers";
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





<div id="mainContent">

   
    <div class="sats-middle-cont">
	
	
	
	<div class="sats-breadcrumb">
		<ul>
		<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Suppliers" href="/suppliers.php"><strong>Suppliers</strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Suppliers Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Suppliers Successfully Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Company Name</th>
				<th>Service Provided</th>
				<th>Address</th>
				<th>Contact Name</th>
				<th>Phone</th>
				<th>Email</th>
				<th>Website</th>
				<th style="width: 10%;">Notes</th>
				<th>On Map</th>
				<th>Edit</th>
			</tr>
				<?php
				
				
				$sql = mysql_query("
					SELECT *
					FROM `suppliers`	
					WHERE `country_id` = {$_SESSION['country_default']}
					AND `status` = 1
					ORDER BY `company_name` ASC
				");
				
				
				if(mysql_num_rows($sql)>0){
					$i = 1;
					while($row = mysql_fetch_array($sql)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span class="txt_lbl"><?php echo $row['company_name']; ?></span>
								<input type="text" class="txt_hid company_name" value="<?php echo $row['company_name']; ?>" />
								<input type="hidden" class="suppliers_id" value="<?php echo $row['suppliers_id']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['service_provided']; ?></span>
								<input type="text" class="txt_hid service_provided" value="<?php echo $row['service_provided']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['address']; ?></span>
								<input type="text" id="address<?php echo $i ?>" class="txt_hid address" value="<?php echo $row['address']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['contact_name']; ?></span>
								<input type="text" class="txt_hid contact_name" value="<?php echo $row['contact_name']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['phone']; ?></span>
								<input type="text" class="txt_hid phone" value="<?php echo $row['phone']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['email']; ?></span>
								<input type="text" class="txt_hid email" value="<?php echo $row['email']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['website']; ?></span>
								<input type="text" class="txt_hid website" value="<?php echo $row['website']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['notes']; ?></span>
								<input type="text" class="txt_hid notes" value="<?php echo $row['notes']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo ($row['on_map']==1)?'Yes':'No'; ?></span>
								<select class="txt_hid on_map" style="width: auto !important;">									
									<option value="0" <?php echo ($row['on_map']==0)?'selected="selected"':''; ?>>No</option>
									<option value="1" <?php echo ($row['on_map']==1)?'selected="selected"':''; ?>>Yes</option>
								</select>
							</td>
							<td>
								<button class="blue-btn submitbtnImg btn_update">Update</button>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<button class="submitbtnImg btn_cancel" style="display:none;">Cancel</button>
								<button class="blue-btn submitbtnImg btn_delete" style="display:none;">Delete</button>
							</td>
						</tr>
				<?php
					$i++;
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
				<form id="form_add_supplier" method="post" action="/add_supplier.php" style="display:none;">
					<div class="row">
						<label class="addlabel" for="company_name">Company Name <span style="color:red">*</span></label>
						<input type="text" name="company_name" id="company_name" class="company_name">
					</div>         			
					<div class="row">
						<label class="addlabel" for="service_provided">Service Provided <span style="color:red">*</span></label>
						<input type="text" name="service_provided" id="service_provided" class="service_provided">
					</div>
					<div class="row">
						<label class="addlabel" for="address">Address</label>
						<input type="text" name="address" id="address" class="address">
					</div>
					<div class="row">
						<label class="addlabel" for="contact_name">Contact Name</label>
						<input type="text" name="contact_name" id="contact_name" class="contact_name">
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
						<label class="addlabel" for="website">Website</label>
						<input type="text" name="website" id="website" class="website">
					</div>
					<div class="row">
						<label class="addlabel" for="notes">Notes</label>
						<textarea name="notes"></textarea>
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


// google map autocomplete
var placeSearch, autocomplete;

// test
var componentForm2 = {
  route: { 
	'type': 'long_name', 
	'field': 'address_2' 
  },
  administrative_area_level_1: { 
	'type': 'short_name', 
	'field': 'state' 
  },
  postal_code: { 
	'type': 'short_name', 
	'field': 'postcode' 
  }
};

function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.
  
  var options = {
	  types: ['geocode'],
	  componentRestrictions: {country: '<?php echo CountryISOName($_SESSION['country_default']); ?>'}
	};
  
  
  // singe - add new form
  autocomplete = new google.maps.places.Autocomplete(
     (document.getElementById('address')),
     options
	  );
	  
	  
	  
	  
	  // multi - listings
	  var i = 1;
	  jQuery(".address").each(function(){

		  autocomplete = new google.maps.places.Autocomplete(
		 (document.getElementById('address'+i)),
		 options
		  );
		  
		  i++;
		  
	  });
	  
	

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  //autocomplete.addListener('place_changed', fillInAddress);
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  // test
   for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm2[addressType]) {
      var val = place.address_components[i][componentForm2[addressType].type];
      document.getElementById(componentForm2[addressType].field).value = val;
    }
  }
  
  /*
  // street name
  var ac = jQuery("#fullAdd").val();
  var ac2 = ac.split(" ");
  var street_number = ac2[0];
  console.log(street_number);
  jQuery("#address_1").val(street_number);
  
  // suburb
  jQuery("#address_3").val(place.vicinity);
  
  console.log(place);
  console.log("lat: "+place.geometry.location.lat());
  console.log("lng: "+place.geometry.location.lng());
  */
}
// end google autocomplete

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

	jQuery("#form_add_supplier").submit(function(event){
		
		var company_name = jQuery("#company_name").val();		
		var service_provided = jQuery("#service_provided").val();
		var email = jQuery("#email").val();
		var error = "";
		
		//console.log(company_name);
		
		//event.preventDefault();	
		
		
		if(company_name==""){
			error += "Company Name is required\n";
		}
	
		if(service_provided==""){
			error += "Service Provided is required\n";
		}
		
		if(email!="" && validate_email(email)==false){
			error += "Invalid email\n";
		}
		
		//console.log(error);
		
		
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
	
		var suppliers_id = jQuery(this).parents("tr:first").find(".suppliers_id").val();
		var company_name = jQuery(this).parents("tr:first").find(".company_name").val();
		var service_provided = jQuery(this).parents("tr:first").find(".service_provided").val();
		var address = jQuery(this).parents("tr:first").find(".address").val();
		var contact_name = jQuery(this).parents("tr:first").find(".contact_name").val();
		var phone = jQuery(this).parents("tr:first").find(".phone").val();		
		var email = jQuery(this).parents("tr:first").find(".email").val();
		var website = jQuery(this).parents("tr:first").find(".website").val();
		var notes = jQuery(this).parents("tr:first").find(".notes").val();
		var on_map = jQuery(this).parents("tr:first").find(".on_map").val();
		var error = "";
		
		if(company_name==""){
			error += "Update Company Name field is required\n";
		}
		
		if(service_provided==""){
			error += "Update Service Provided field is required\n";
		}
		
		if(email!="" && validate_email(email)==false){
			error += "Update Email field Invalid\n";
		}
		
		
		if(error!=""){
			alert(error);
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_suppliers.php",
				data: { 
					suppliers_id: suppliers_id,
					company_name: company_name,
					service_provided: service_provided,
					address: address,
					contact_name: contact_name,
					phone: phone,
					email: email,
					website: website,
					notes: notes,
					on_map: on_map
				}
			}).done(function( ret ) {
				window.location="/suppliers.php?success=2";
			});			
			
		}		
		
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var suppliers_id = jQuery(this).parents("tr:first").find(".suppliers_id").val();
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_suppliers.php",
				data: { 
					suppliers_id: suppliers_id
				}
			}).done(function( ret ) {	
				window.location.reload();
			});	
			
		}				
	});

	jQuery("#btn_add_new").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#form_add_supplier").slideDown();
	},function(){
		jQuery(this).html("Add New");		
		jQuery("#form_add_supplier").slideUp();
	});
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>