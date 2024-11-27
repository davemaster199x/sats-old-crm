<?php

$title = "Accomodation";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function checkIfAccomIsConctdToUser($accomodation_id){
	$sql = mysql_query("
		SELECT *
		FROM `staff_accounts`
		WHERE `accomodation_id` = {$accomodation_id}
	");
	if( mysql_num_rows($sql)>0 ){
		return true;
	}else{
		return false;
	}
}

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
		<li class="other first"><a title="Accomodation" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>Accomodation</strong></a></li>
		</ul>
	</div>
	
		
	
		
		<div id="time"><?php echo date("l jS F Y"); ?></div>
		
		
		<?php 
	
		
		
		if($_GET['success']==1){
			echo '<div class="success">Accomodation Successfully Added</div>';
		}else if($_GET['success']==2){
			echo '<div class="success">Accomodation Successfully Updated</div>';
		}
		
		
		?>
		<?php echo ($_GET['error']!="")?'<div>'.$_GET['error'].'</div>':''; ?>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px; margin-bottom: 13px;">
			<tr class="toprow jalign_left">
				<th>Name</th>
				<th>Area</th>
				<th>Address</th>
				<th style="width: 10%;">Phone</th>
				<th>Email</th>
				<th style="width: 8%;">Rate</th>
				<th>Comment</th>
				<th>Edit</th>
			</tr>
				<?php
				
				$sql = mysql_query("
					SELECT *
					FROM `accomodation`
					WHERE `country_id` = {$_SESSION['country_default']}
					ORDER BY `area` ASC
				");
				
				if(mysql_num_rows($sql)>0){
					$i = 1;
					while($row = mysql_fetch_array($sql)){
				?>
						<tr class="body_tr jalign_left">
							<td>
								<span class="txt_lbl"><?php echo $row['name']; ?></span>
								<input type="text" class="txt_hid name" value="<?php echo $row['name']; ?>" />
								<input type="hidden" class="accomodation_id" value="<?php echo $row['accomodation_id']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['area']; ?></span>
								<input type="text" class="txt_hid area" value="<?php echo $row['area']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $row['address']; ?></span>
								<?php
								if( checkIfAccomIsConctdToUser($row['accomodation_id'])==true ){ ?>
									<span><?php echo $row['address']; ?></span>									
								<?php	
								}else{ ?>
									<input type="text" id="address<?php echo $i ?>" class="txt_hid address" value="<?php echo $row['address']; ?>" />
								<?php	
								}
								?>								
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
					$i++;
					}
				}else{ ?>
					<td colspan="5" align="left">Empty</td>
				<?php
				}
				?>
		</table>	

		<div class="jalign_left">
		
			<button type="button" id="btn_add_new" class="submitbtnImg">Add New</button>
			
            <div style="padding-top: 20px;" id="div_staff" class="addproperty formholder">
				<form id="form_accomodation" method="post" action="/accomodation_process.php" style="display:none;">
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
	
		var accomodation_id = jQuery(this).parents("tr:first").find(".accomodation_id").val();
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
				url: "ajax_update_accomodation.php",
				data: { 
					accomodation_id: accomodation_id,
					name: name,
					area: area,
					address: address,
					phone: phone,
					email: email,
					rate: rate,
					comment: comment
				}
			}).done(function( ret ) {
				window.location="/accomodation.php?success=2";
			});				
			
		}		
		
	});

	jQuery(".btn_delete").click(function(){
		if(confirm("Are you sure you want to delete?")){
			var accomodation_id = jQuery(this).parents("tr:first").find(".accomodation_id").val();
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_accomodation.php",
				data: { 
					accomodation_id: accomodation_id
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
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>