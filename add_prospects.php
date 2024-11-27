<?

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "Add Prospects";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


include('inc/agency_class.php');
$agency = new Agency_Class();

function rand_string( $length ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);
}

?>

<div id="mainContent">

  <div class="sats-middle-cont">
  
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add Agency" href="/add_prospects.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    <div id="time"><?php echo date("l jS F Y"); ?></div>
    
    <div class="formholder addagency">
      <form id="form1" name="form1" method="POST" action="agency_controller.php">
        


		
        
        <div class="row">
          <label class="addlabel" for="agency_name">Agency Name <span style="color:red">*</span></label>
          <input class="addinput" type="text" name="agency_name" id="agency_name">
        </div> 


		
		
		
			<div class="row">
			  <label class="addlabel" for="fullAdd">Address</label>
			  <input class="addinput" type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" />
			 </div>
			
			  
		
          <div class="row">
          <label class="addlabel" for="street_number">Street Number</label>
          <input class="addinput" type="text" name="street_number" id="street_number">
         </div> 
          <div class="row">
          <label class="addlabel" for="street_name">Street Name</label>
          <input class="addinput" type="text" name="street_name" id="street_name">
         </div> 
          <div class="row">
          <label class="addlabel" for="suburb">Suburb</label>
          <input class="addinput" type="text" name="suburb" id="suburb">
          </div>
          
		  <?php 
		  if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
			 <div class="row">
			  <label class="addlabel" for="state">State <span style="color:red">*</span></label>
			  <select class="addinput" name="state" id="state">          
			  <option value="">----</option>
			  <?php
			  $state_sql = getCountryState();
			  while($state = mysql_fetch_array($state_sql)){ ?>
				<option value='<?php echo $state['state']; ?>'><?php echo $state['state_full_name']; ?></option>
			  <?php	  
			  }
			  ?>			  			 
			  </select>
			  </div>
		  <?php	
		  }else{ ?>
			
			
				<div class="row">
				<label class="addlabel" for="state">Region</label>
				<input type="text" name="state" id="state" class="addinput" />
				 </div>
				
			<?php	
			}
			?>
        
          <div class="row"> 
          <label class="addlabel" for="postcode">Postcode <span style="color:red">*</span></label>
          <input class="addinput" type="text" name="postcode" id="postcode" onkeypress="return numbersonly(event)" />
          </div>
		  

		  
		  <div class="row">
          <label class="addlabel" for="phone">Landline</label>
          <input class="addinput" type="text" name="phone" id="phone">
         </div>
         
          
		  
		  
		
		  
		  <div class="row">
          <label class='addlabel' for='website'>Website</label>
          <input class="addinput" type="text" name="website" />        
          </div>
		  
		   
		  
		  
          
          <h2 class="heading">Agency Contact</h2>
          
          <div class="row">
          <label class='addlabel' for='totproperties'>Email</label>
          <input class="addinput" type="text" name="ac_email">
          </div> 
			
			
		<!-- agency status -->
		<input type="hidden" name="agen_stat" value="target" />
		<!-- Staff User: NZ Prospect -->	
		<?php
		if( strpos(URL,"dev")==false ){ // LIVE
		
			if( $_SESSION['country_default']==1 ){ // AU
				$salesrep = 2195;
			}else if( $_SESSION['country_default']==2 ){ // NZ
				$salesrep = 2184;
			}
			
		}else{ // DEV
		
			if( $_SESSION['country_default']==1 ){ // AU
				$salesrep = 2141;
			}else if( $_SESSION['country_default']==2 ){ // NZ
				$salesrep = 2140;
			}
			
		}
		?>
		<input type="hidden" name="salesrep" value="<?php echo $salesrep; ?>" />
		
		
		
		
		<label for="submit">
            <button class="submitbtnImg" type="button" name="add_agency" id="add_agency" style="margin-top: 10px;">Add Agency</button>
          </label>
        
      </form>
   
    
  </div>
  
</div>

</div>

<br class="clearfloat" />



<style>
.price_div{
	display:none;
}
</style>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.2/themes/black-tie/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<style>
.ui-autocomplete li {
    text-align: left!important;
    font-size:	14px;
	font-family: arial,​sans-serif;
	font-weight	400;
}
</style>
<script>

// google map autocomplete
var placeSearch, autocomplete;

// test
var componentForm2 = {
  route: { 
	'type': 'long_name', 
	'field': 'street_name' 
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
  
  autocomplete = new google.maps.places.Autocomplete(
     (document.getElementById('fullAdd')),
     options
	  );

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  autocomplete.addListener('place_changed', fillInAddress);
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
  
  // street name
  var ac = jQuery("#fullAdd").val();
  var ac2 = ac.split(" ");
  var street_number = ac2[0];
  console.log(street_number);
  jQuery("#street_number").val(street_number);
  
  // suburb
  jQuery("#suburb").val(place.vicinity);
  
  console.log(place);
  
  var postcode = jQuery("#postcode").val();
  getRegionViaPostcode(postcode);
  
}
// end google autocomplete

function toTitleCase(str){
	return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}


function getRegionViaPostcode(postcode){
		
	if( postcode!="" ){
		jQuery.ajax({
			type: "POST",
			url: "ajax_getRegionViaPostCode.php",
			data: { 
				postcode: postcode
			},
			dataType: 'json'
		}).done(function( ret ) {
			//window.location="/main.php";
			jQuery("#postcode_region_name").val(ret.postcode_region_name);
			jQuery("#region").val(ret.postcode_region_id);
		});	
	}
	
}

jQuery(document).ready(function(){
	
	// submission
	jQuery("#add_agency").click(function(){
	
		var agen_stat = jQuery("#agen_stat").val();
		var agency_name = jQuery("#agency_name").val();
		var franchise_group = jQuery("#franchise_group").val();
		var state = jQuery("#state").val();
		var postcode = jQuery("#postcode").val();
		var region = jQuery("#region").val();
		var agency_emails = jQuery("#agency_emails").val();
		var account_emails = jQuery("#account_emails").val();
		var country = jQuery("#country").val();
		var error = "";
		var flag = 0;
		
		
		if(agency_name==""){
			error += "Agency Name is required\n";
		}
		if(franchise_group==""){
			error += "Franchise Group is required\n";
		}
		if(state==""){
			error += "<?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?> is required\n";
		}
		
		if(postcode==""){
			error += "Postcode is required\n";
		}else{
			if(postcode.length<4){
				error += "Postcode cannot be less than 4 digit \n";
			}
		}
		
		/*
		if(region==""){
			error += "<?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?> is required\n";
		}
		*/
		if(country==""){
			error += "Country is required\n";
		}
		
		if(agen_stat=='active'){
			// agency emails
			if(agency_emails==""){
				error += "Agency emails are required\n";
			}else{
				agency_e = agency_emails.split("\n");
				var email_error = 0;
				// loop through emails and validate them
				for (var i=0; i < agency_e.length; i++){
					// invalid email
					if(validate_email(agency_e[i])==false){					
						email_error = 1;
					}
				}
				if(email_error==1){
					error += "One or more of Agency email is invalid format\n";
				}
			}
			
			// account emails
			if(account_emails==""){
				error += "Account emails is required\n";
			}else{
				account_e = account_emails.split("\n");
				var email_error = 0;
				// loop through emails and validate them
				for (var i=0; i < account_e.length; i++){
					// invalid email
					if(validate_email(account_e[i])==false){					
						email_error = 1;
					}
				}
				if(email_error==1){
					error += "One or more of Account email is invalid format\n";
				}
			}
		}
		
		jQuery(".req").each(function(){
			if(jQuery(this).val()==""){
				flag = 1;
			}
		});
		if(flag == 1){
			error += "Approved Price is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#form1").submit();
		}
		
	});

	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
</body></html>