<?php


$title = "Add Property";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



# Get Different Tech Sheet Job Types
$tech_sheet_job_types = getTechSheetJobTypes();


$pm_passed_agency_id = $_GET['agency_id']; 
$pm_prop_id = $_GET['pid'];

//$new_tenants = 1;
$new_tenants = NEW_TENANTS;

//$propertyme_api = new Propertyme_api;
//$prop = $propertyme_api->getPropertyDetails($_GET['pid']);


/*
echo "<pre>";
print_r($prop);
echo "</pre>";
*/



/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/;


$url = $_SERVER['SERVER_NAME'];
if($_SESSION['country_default']==1){ // AU

	if( strpos($url,"crmdev")===false ){ // live 
		$compass_fg_id = 39;
	}else{ // dev 
		$compass_fg_id = 34;
	}
	
}

$isCompassFg = false;
if( $_SESSION['rem_fg_id'] == $compass_fg_id ){
	$isCompassFg = true;
}


?>
    
    <div id="mainContent">
      
      <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Add a Property" href="/add_property_static.php"><strong>Add a Property</strong></a></li>
      </ul>
    </div>
    
   <div id="time"><?php echo date("l jS F Y"); ?></div>
      
      	
	<div class="addproperty">
    
      <?php
	  if($_GET['success']==1){ ?>
		<div class="success">New Property Added</div>	
	  <?php
	  }

	
	  ?>
      
    	<form id="form1" name="form1" method="POST" action="<?=URL;?>add_property.php" enctype="multipart/form-data" >
    	<input type="hidden" name="propertyme_prop_id" id="propertyme_prop_id">
          <div class="add_prop_frstform">
            <div class="row frow" style="float:left;">
            <label class="addlabel" for="agency">Select Agency</label>
            <select class="addinput" name="agency" id="agency_name">
			<option value="">----</option>
    
    <?php
       // (1) Open the database
       
    
    
       // (2) Run the query 
       $result = mysql_query ("SELECT agency_id, agency_name, address_3, `franchise_groups_id`, `allow_indiv_pm` FROM agency WHERE `status` = 'active' AND `country_id` = {$_SESSION['country_default']} AND `agency_id` != 1 ORDER BY `agency_name` ASC", $connection);
    
        $odd=0;
    
       // (3) While there are still rows in the result set,
       // fetch the current row into the array $row
       while ($row = mysql_fetch_row($result))
       {
    
         // (4) Print out each element in $row, that is,
         // print the values of the attributes
		 
		 // franchise group
		 $fg =  $row[3];
		 // allow PM
		 $allow_pm = $row[4];
    
            echo "<option value='" . $row[0] . "' data-fg='".$fg."' data-allow_pm='".$allow_pm."' ";
            echo ($_SESSION['remember_agency']==$row[0])?"selected='selected'":"";
            echo ">";		
            echo $row[1];	// . ", " . $row[2];
            echo "</option>\n";
    
          // Print a carriage return to neaten the output
          echo "\n";
       }
       // (5) Close the database connection
    
            
    
    ?>
            </select>	
            <div class="add_pr-sec remember_agency_div">
                <input type="checkbox" name="remember_agency" id="remember_agency" value="1" <?php echo ($_SESSION['remember_agency']!="")?'checked="checked"':''; ?> /> Remember Agency 
            </div>	
            </div>
			
		<div id="jprop_div" style="display:none;">
		
		
			<div class="upper_div">
		
			
			<div class="row">
				<label class="addlabel" for="fullAdd">Address <span style="color:red">*</span></label>
				<input class="addinput" size=5 type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" style="float: left;" />
				<div class="pm_full_address"></div>
			</div>
			
			
			
            <div class="row">
            <label class="addlabel" for="address_1">Street Number <span style="color:red">*</span></label>
            <input class="addinput" size=5 type="text" name="address_1" id="address_1" onkeydown="return keypress(event);">
            </div>
            <div class="row">
            <label class="addlabel" for="address_2">Street Name <span style="color:red">*</span></label>
            <input class="addinput" type="text" name="address_2" id="address_2" onkeydown="return keypress(event);">
            </div>
            <div class="row">
            <label class="addlabel" for="address_3">Suburb <span style="color:red">*</span></label>
            <input class="addinput" type="text" name="address_3" id="address_3" onkeydown="return keypress(event);"> 
            </div>
			<?php
			if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
			
				<div class="row">
				<label class="addlabel" for="state">State</label>
				<select class="addinput" name="state" id="state" onkeydown="return keypress(event);" style="width:75%">
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
            <input class="addinput" type="text" name="postcode" id="postcode" /> 
            </div>
			
			
		
			<div class="row pm_div">
				<label class="addlabel" for="postcode">Property Manager</label>
				<select name="property_manager" id="property_manager" style="width: 75%; float: left;">						
				</select>      
				<input type="hidden" class="hid_allow_pm" name="hid_allow_pm" value="0" />
				
				<div class="pm_prop_mngr"></div>
			  </div>
			  
			  
			  
			  
			  
			<div class="row compass_index_num_div" style="display:<?php echo ( $isCompassFg == true )?'block':'none'; ?>">
				<label class="addlabel" for="compass_index_num">Compass Index Number</label>
				<input class="addinput" type="text" name="compass_index_num" id="compass_index_num" /> 
            </div>
			  
			  
			  </div>
			
			
	           
<?php

   
	
	
		echo '<div style="clear:both;"></div>';
		echo "<h2 class='heading'>Landlord</h2>";
		

		echo "<label for='landlord_firstname' class='tenantlabel first left'>First Name <span class='ll_req' style='color:red; display:none;'>*</span></label>";
		echo "<input class='tenantinput addinput left' type=text name='landlord_firstname' id='landlord_firstname' value='' onkeydown='return keypress(event);'>\n";

		
		echo "<label for='landlord_lastname' class='tenantlabel left'>Last Name</label>";
		echo "<input class='tenantinput addinput left' type=text name='landlord_lastname' id='landlord_lastname' value='' onkeydown='return keypress(event);'>\n";
	
		
	
		echo "<label for='landlord_email' class='tenantlabel left'>Email <span class='ll_req' style='color:red; display:none;'>*</span></label>";
		echo "<input class='tenantinput addinput last left' type=text name='landlord_email' id='landlord_email' value='' onkeydown='return keypress(event);'>\n";


		echo "<div style='margin-top: 50px;'>";

		echo "<label for='landlord_mobile' class='tenantlabel first left' style=' width: 68px;'>Mobile <span class='ll_req' style='color:red; display:none;'>*</span></label>";
		echo "<input class='tenantinput addinput last left tenant_mobile_field' type=text name='landlord_mobile' id='landlord_mobile' value='' onkeydown='return keypress(event);'>\n";

		echo "<label for='landlord_landline' class='tenantlabel left' style='margin-left: 10px; width: 67px;'>Landline</label>";
		echo "<input class='tenantinput addinput last left tenant_phone_field' type=text name='landlord_landline' id='landlord_landline' value='' onkeydown='return keypress(event);'>\n";
		

		echo "<br><br>\n";
		echo "</div>";
		
		
		echo '<div id="sevices_div"></div>';
		

		
		?>
		
		<div id="tenants_div" class="formhidden">

		<div class="row" style="width: 800px !important;">
			<div style="float:left;">
				<label class="tenantlabel addlabel" style="float:left; width: 114px;">Key Number</label>
				<input class="tenantinput addinput" style="float:left;width: 120px;margin-right: 47px;" type="text" name="key_num" >
			</div>
			<div style="float:left; margin-right: 33px;">
			<label class="tenantlabel addlabel lbl_wo" style="float:left; width: 150px;">Work Order Number</label>
			<input class="tenantinput addinput" style="float:left;width: 120px;" type="text" name="work_order_num" id="work_order_num">
			</div>
			
			<div style="clear:both;"></div>
			<div style="float:left; margin-right: 47px; margin-top: 9px;">
			<label class="tenantlabel addlabel lbl_wo" style="float:left; width: 114px;">Alarm Code</label>
			<input class="tenantinput addinput" style="float:left;width: 120px;" type="text" name="alarm_code" id="alarm_code">
			</div>
			
			<div style="float:left; margin-top: 9px;">
			<label class="tenantlabel addlabel" style=" float: left; margin-right: 10px; width: auto;">Short Term Rental</label>
			<input class="tenantinput addinput" style="float:left; width: auto;" type="checkbox" name="holiday_rental" id="holiday_rental" value="1">
			</div>
			
			<div style="float:left; margin-top: 9px; margin-left: 37px;">
			<label class="tenantlabel addlabel" style=" float: left; margin-right: 10px; width: auto;">Property Vacant</label>
			<input class="tenantinput addinput" style="float:left; width: auto;" type="checkbox" name="prop_vacant" id="prop_vacant" value="1">
			</div>
        </div>
		
		<div id="dha_agencies_fields" style="display:none;">
		 <div class="row">
            <label class="addlabel" for="tech_notes">Run Sheet Notes</label>
            <input class="addinput" type="text" name="tech_notes" id="tech_notes" /> 
          </div>
		  
		   <div class="row">
            <label class="addlabel" for="start_end_date">Start Date/End Date</label>
            <input type="text" class="addinput vw-jb-inpt datepicker" name="start_date" style="width: 100px; margin-right: 12px; float: left;" />
			<input type="text" class="addinput vw-jb-inpt datepicker" name="due_date" style="width: 100px;" />			
          </div>
		 </div>
		 
            
		
		<div id="main_tenant_div">
            <h3 style="text-align:left;">Tenants</h3>
			<table>
				<thead>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Mobile</th>
						<th>Landline</th>
						<th>Email</th>
					</tr>
				</thead>
				<tbody class="new_tenant_tbody">
					<tr class="new_tenant_row">
						<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_firstname[]" /> </td>
						<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_lastname[]" /></td>
						<td> <input style="width:100%" class="tenantinput addinput tenant_mobile_field" type="text" name="tenant_mob[]" /></td>
						<td> <input style="width:100%" class="tenantinput addinput tenant_phone_field" type="text" name="tenant_ph[]" /></td>
						<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_email[]" /></td>
					</tr>
				</tbody>
			</table>
			<div style="text-align:left;padding-top:7px;">
				<button type="button" id="add_new_tenant_row" class="blue-btn submitbtnImg">
					<img class="inner_icon" src="images/button_icons/add-button.png"> 
					<span class="inner_icon_txt">Tenant</span>
				</button>
			</div>
		</div>
           
            
            
            
		<script src="js/responsive_tabs.js"></script>
		<script>
		  var myTabs = tabs({
			el: '#tabs',
			tabNavigationLinks: '.c-tabs-nav__link',
			tabContentContainers: '.c-tab'
		  });

		  myTabs.init();
		</script>
		
		 
		
		
		
		<br />
		<div class='row'>
			<label class='tenantlabel'>File Upload</label>
			<input type="file" id="fileupload" name="fileupload" class="tenantinput addinput" />	
		</div>	

		<br />
		<div class='row'>
			<label class='tenantlabel'>Work Order Notes</label>
			<textarea rows="5" name="workorder_notes" class="addtextarea vw-jb-tar" style="width: 400px;"></textarea>
		</div>	

		</div>	

		<br />

		<div class='row'>
			<label class='addlabel'>&nbsp;</label>
			<input type="hidden" name="pm_passed_agency_id" value="<?php echo $pm_passed_agency_id; ?>" />
			<input type="hidden" name="pm_prop_id" value="<?php echo $pm_prop_id; ?>" />
			<button class="submitbtnImg submitbutton" type="button" style="float:left">
				<img class="inner_icon" src="images/add-button.png">
				Add Property
			</button>
		</div>

		
		
	</div>
		
		
    </form>
    </div>
	</div>
    
  </div>


  
</div>

</div>

<br class="clearfloat" />

<style>
.ui-autocomplete li {
    text-align: left!important;
    font-size:	14px;
	font-family: arial,​sans-serif;
	font-weight	400;
}
.add_prop_frstform .row input.addinput {
    width: 75%;
}
.pm_div .pm_full_address_div{
	display: none;
}
.service_type {
    width: 200px !important;
}

.lbl_price {
    width: 81px;
}
.add_prop_frstform {
    width: 100%;
}
#sevices_div {
    width: 100%;
}
.add_prop_frstform .row.frow {
    width: 100%;
}
.addproperty {
    width: 100%;
}
.upper_div input, .upper_div select{
    width: 45% !important;
}
#tenants_div{
	display:none; 
	margin-top: 38px; 
}
.remember_agency_div{
	float: left;
	margin-left: 15px;	
	margin-top: 3px;
}
.formhidden {
    width: 570px;
}
.c-tab__content {
    height: auto;
}
.tenants_tbl tr,
.tenants_tbl tr td{
	border: none;
}
.tenants_tbl tr th{
	text-align: left;
}
.tenants_tbl thead tr td{
	text-align: left;
}
.pm_prop_mngr, .pm_full_address{
	float: left;
	position: relative;
	left: 15px;
	top: 6px;
	color: red;
}
</style>
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
	console.log("addressType: "+addressType);
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
  jQuery("#address_1").val(street_number);
  
  // suburb
  jQuery("#address_3").val(place.vicinity);
  
  console.log(place);
  console.log("lat: "+place.geometry.location.lat());
  console.log("lng: "+place.geometry.location.lng());
}
// end google autocomplete

function toTitleCase(str){
	return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}


// get Agency PM
function getAgencyPM(agency_id){
	
	// check Private FG
	jQuery.ajax({
		type: "POST",
		url: "ajax_get_agency_pm.php",
		data: {	
			agency_id: agency_id
		}
	}).done(function(ret){
		
		jQuery(".hid_allow_pm").val(1);
		jQuery("#property_manager").html(ret);
		jQuery(".pm_div").show();
		
	});
	
}



jQuery(document).ready(function(){
	
	
	<?php
	if( $_SESSION['remember_agency'] != '' ){ ?>
	
		getAgencyPM(<?php echo $_SESSION['remember_agency']; ?>);
	
	<?php
	}
	?>
	
	
	
	
	
	jQuery("#add_tenants_btn").click(function(){
		
		var tenants_tbody = jQuery(".tenants_tbody:last").clone();
		tenants_tbody.find(".addinput").val('');
		jQuery(".tenants_tbl").append(tenants_tbody);
		
	});
	
	
	jQuery(document).on('click','.serv_radio',function(){

		//jQuery(this).parents("div.add-services-inner-radio:first").find(".serv_lbl_txt").addClass('fadeOutText');
		jQuery(this).parents("div.add-prop-st-mid:first").find(".serv_lbl_txt").addClass('fadeOutText');
		jQuery(this).parents("div.serv_indiv_div:first").find(".serv_lbl_txt").removeClass('fadeOutText');

	});

	
	<?php
	if($_SESSION['remember_agency']!=""){ ?>
			
			
		var dha_agencies = ['3043','3036','3046','1902','3044','1906','1927','3045'];
		if( dha_agencies.indexOf(<?php echo $_SESSION['remember_agency']; ?>)>-1 ){
			jQuery("#dha_agencies_fields").show();
		}else{
			jQuery("#dha_agencies_fields").hide();
		}
		
		jQuery("#jprop_div").show();
		get_agency_hidden_details(<?php echo $_SESSION['remember_agency']; ?>);
		
	
		
	<?php
	}
	?>
	
	
	
	

	

	
	function validate_form(){
		var street_num = jQuery("#address_1").val();
		var street_name = jQuery("#address_2").val();
		var suburb = jQuery("#address_3").val();
		var rwo = jQuery("#require_work_order").val();
		var work_order_num = jQuery("#work_order_num").val();
		var postcode = jQuery("#postcode").val();
		
		var ll_fname = jQuery("#landlord_firstname").val();
		var ll_email = jQuery("#landlord_email").val();
		var ll_mobile = jQuery("#landlord_mobile").val();
		var is_private_fg = jQuery("#is_private_fg").val();
		var error = "";
		
		if(street_num==""){
			error += "Street number is required\n";
		}
		if(street_name==""){
			error += "Street name is required\n";
		}
		if(suburb==""){
			error += "Suburb is required\n";
		}		
		if(rwo==1){
			if(work_order_num==""&&jQuery(".serv_sats:checked").length>0){
				error += "Word Order Number is required\n";
			}
		}
		if(postcode==""){
			error += "Postcode is required\n";
		}else{
			if(postcode.length<4){
				error += "Postcode cannot be less than 4 digit \n";
			}
		}
		
		// change price validation
		jQuery(".price_changed").each(function(){
			if(jQuery(this).val()==1){
				var serv = jQuery(this).parents("tr:first").find(".service_type").html();
				if(jQuery(this).parents("tr:first").find(".price_reason").val()==""){
					error += serv+" Change Price Reason is Required\n";
				}
				if(jQuery(this).parents("tr:first").find(".price_details").val()==""){
					error += serv+" Change Price Detail is Required\n";
				}
			}
		});
		
		// if agency is private
		if( is_private_fg == 1 ){
			
			if(ll_fname==""){
				error += "Landlord First Name is required\n";
			}
			if(ll_email==""){
				error += "Landlord Email is required\n";
			}
			if(ll_mobile==""){
				error += "Landlord Mobile is required\n";
			}
			
		}
		
		return error;
		
	}
	
	function get_agency_hidden_details(agency_id){
		// get agency services
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_agency_services.php",
			async: false,
			data: {
				agency_id: agency_id
			}
		}).done(function(ret){
			jQuery("#sevices_div").html(ret);
			jQuery("#agency_id").val(agency_id);
		});
		
		<?php
		
		if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
			// get agency state
			jQuery.ajax({
				type: "POST",
				url: "ajax_get_agency_state.php",
				async: false,
				data: {
					agency_id: agency_id
				},
				dataType: 'json'
			}).done(function(ret){
				
				jQuery("#state option").each(function(){
					//console.log(ret.state+'-'+jQuery(this).val());
					if(jQuery(this).val()==ret.state){
						jQuery(this).prop("selected",true);
						jQuery(".submitbutton").show();
					}							
				});
				
				if(ret.require_work_order==1){
					jQuery(".lbl_wo").append(' <span id="mark_req" style="color:red">*</span>');
					jQuery("#require_work_order").val(1);
				}else{
					jQuery("#mark_req").remove();
					jQuery("#require_work_order").val(0);
				}
			});
		<?php	
		}
		
		?>
		
	}

	<?php
	// Add property from api 
	if((isset($_GET['exec']) AND $_GET['exec'] == "api") AND (isset($_GET['agency_id']) AND $_GET['agency_id'] != "")) {
	?>
	$('#agency_name option[value=<?=$_GET["agency_id"]?>]').attr('selected', 'selected');
		onChangeAgency('','','<?=$_GET["agency_id"]?>','<?=$_GET["fg"]?>','<?=$_GET["allow_pm"]?>');
		jQuery("#load-screen").show();
		$.ajax({
			url: 'ajax_propertyme.php?getPropertyDetails',
			type: 'POST',
			data: { 'pid' : '<?=$_GET["pid"]?>' },
			dataType: 'json',
			success: function(data){
				jQuery("#load-screen").hide();
				console.log(data.coordinate);
				console.log(data.formatted_address);
				$('#fullAdd').val(data.AddressText);
				$('#address_1').val(data.AddressNumber);
				$('#address_2').val(data.AddressStreet);
				$('#address_3').val(data.AddressSuburb);
				console.log("address_3: "+data.AddressSuburb+"\nstate: "+data.AddressState+"\npostcode: "+data.AddressCode);
				$('#state').val(data.AddressState);				
				$('#postcode').val(data.AddressCode);
				$('#propertyme_prop_id').val(data.Id);

				$('#landlord_firstname').val(data.LLName);
				$('#landlord_email').val(data.LLEmail);
				$('#landlord_landline').val(data.LLLandline);
				$('#landlord_mobile').val(data.LLMobile);
				//console.log(data.PropertyManager);
				
				var pm_prop_man = ((data.PropertyManager).trim()).toLowerCase();
				jQuery(".pm_prop_mngr").html("'"+pm_prop_man+"'");	

				jQuery(".pm_full_address").html(data.pm_full_address);
				
			}
		});
	<?php }?>
	
	function onChangeAgency(t, isOnChange = '', agency_id = '',fg='' ,allow_pm='')
	{
		if(isOnChange == 'change') {
			var agency_id = jQuery(t).val();
			var dp_sel_opt = jQuery(t).find("option:selected");
			//var franchise_groups_id;
			var fg_id = dp_sel_opt.attr("data-fg");
			var allow_pm = dp_sel_opt.attr("data-allow_pm");
		} else {
			var fg_id = fg;
			var allow_pm = allow_pm;
		}

		
		console.log("agency_id: "+agency_id);
		console.log("FG: "+fg_id);
		
		var compass_fg;
		<?php
		$url = $_SERVER['SERVER_NAME'];
		if($_SESSION['country_default']==1){ // AU
		
			if( strpos($url,"crmdev")===false ){ // live ?>
				var compass_fg = 39;
			<?php
			}else{ // dev ?>
				var compass_fg = 34;
			<?php	
			}
			
		}
		
		?>
		
		//console.log('fg_id: '+fg_id);
		console.log('compass_fg: '+compass_fg);
		if( parseInt(fg_id) == parseInt(compass_fg) ){
			jQuery(".compass_index_num_div").show();
		}else{
			jQuery(".compass_index_num_div").hide();
		}
		
		if( agency_id != "" ){
			
			// allow pm
			if( parseInt(allow_pm) == 1 ){
				
				getAgencyPM(agency_id)
				
			}else{
				jQuery(".hid_allow_pm").val(0);
				jQuery("#property_manager").html('');
				jQuery(".pm_div").hide();
			}

			// check Private FG
			jQuery.ajax({
				type: "POST",
				url: "ajax_check_private_fg.php",
				data: {	
					fg_id: fg_id
				}
			}).done(function(ret){
				
				var is_dha_private = parseInt(ret);
				
				if( is_dha_private == 1 ){
					jQuery("#is_private_fg").val(1);
					jQuery(".ll_req").show();
				}else{
					jQuery(".ll_req").hide();
					jQuery("#is_private_fg").val(0);
				}
				
			});
			
			// check DHA agencies
			jQuery.ajax({
				type: "POST",
				url: "ajax_check_dha_agencies.php",
				data: {	
					fg_id: fg_id
				}
			}).done(function(ret){
				
				var is_dha_agency = parseInt(ret);
				
				if( is_dha_agency == 1 ){
					jQuery("#dha_agencies_fields").show();
				}else{
					jQuery("#dha_agencies_fields").hide();
				}
				
			});
			
			
			jQuery("#jprop_div").show();
			get_agency_hidden_details(agency_id);
			
		}else{
			
			jQuery("#jprop_div").hide();
			
		}
	}	
	
	// repopulate franchise group
	jQuery("#agency_name").change(function(){
		
		onChangeAgency(this,'change');
		
	});	
	
	jQuery(document).on("click",".serv_sats",function(){
		jQuery("#tenants_div").show();
	});
	
	jQuery(document).on("click",".serv_not_sats",function(){
		if( jQuery(".serv_sats:checked").length==0 ){
			jQuery("#tenants_div").hide();
		}		
	});
	
	jQuery(".submitbutton").click(function(){
		if(validate_form()!=""){
			alert(validate_form());
		}else{
			jQuery("#form1").submit();
		}
	});
        
        
       jQuery('#add_new_tenant_row').click(function(e){
           e.preventDefault();
           
           var content_html = '<tr class="new_tenant_row">'+
                            '<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_firstname[]" /> </td>'+
                            '<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_lastname[]" /></td>'+
							'<td> <input style="width:100%" class="tenantinput addinput tenant_mobile_field" type="text" name="tenant_mob[]" /></td>'+
                            '<td> <input style="width:100%" class="tenantinput addinput tenant_phone_field" type="text" name="tenant_ph[]" /></td>'+
                            '<td> <input style="width:100%" class="tenantinput addinput" type="text" name="tenant_email[]" /></td>'+
                            '</tr>';
           
           $('.new_tenant_tbody').append(content_html);

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


        }); 
        
        
        
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
