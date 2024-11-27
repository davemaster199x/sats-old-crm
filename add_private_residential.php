<?php


$title = "Add Private Residential";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



# Get Different Tech Sheet Job Types
$tech_sheet_job_types = getTechSheetJobTypes();

/*
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
*/;



?>
    
    <div id="mainContent">
      
      <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong><?php echo $title; ?></strong></a></li>
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

		<!--
		<div class="success" style="margin-bottom: 10px;">
			<p>Hi this is {staff_name} from Smoke Alarm Testing Services calling on behalf of <span class='junderline_colored'>{agency_name}</span> in regards to the rental property at <span class='junderline_colored'>{p_address}</span>.</p>
				<p>We have been instructed to service the {serv_text} at your property. I have a technician available this <span class='junderline_colored'>{day}</span> between <span class='junderline_colored'>TIME</span> and <span class='junderline_colored'>TIME</span><p/>
				<p>Would anybody be available to allow access?</p>
			<button type="button" class="submitbtnImg" id="">Red Button</button>
			<button type="button" class="submitbtnImg blue-btn" id="">Blue Button</button>
		</div>
		-->
      
    	<form id="form1" name="form1" method="POST" action="<?=URL;?>add_property.php">
          <div class="add_prop_frstform">
		  
            <div class="row">
            <label class="addlabel" for="agency">Select Agency</label>
            <select class="addinput" name="agency" id="agency_name" style="width:auto;"> 
			<option value="">----</option>
    
    <?php
       // (1) Open the database
       
    
    
		// (2) Run the query 
		$result = mysql_query("
		SELECT agency_id, agency_name, address_3, `franchise_groups_id` 
		FROM agency 
		WHERE `status` = 'active' 
		AND `country_id` = {$_SESSION['country_default']} 
		AND `agency_id` != 1 
		AND `franchise_groups_id` = 10
		ORDER BY `agency_name` ASC
		", $connection);
    
        $odd=0;
    
       // (3) While there are still rows in the result set,
       // fetch the current row into the array $row
       while ($row = mysql_fetch_row($result))
       {
    
         // (4) Print out each element in $row, that is,
         // print the values of the attributes
		 
		 // franchise group
		 $fg =  $row[3];
    
            echo "<option value='" . $row[0] . "' data-fg='".$fg."' ";
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
 
            </div>
			
			
			
			
			
			
			
			
		<div id="jprop_div" style="display:none;">
		
		
		
			<div class="questions_div">
			
			<div class="row boldItalic">
				<label>Can I ask where the property is currently located?</label>
			</div>
						
			<div class="row">					
				<div>				
					<label class="addlabel">In Service Area?</label>				
					<div>
						<div class="float-left">
							<input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="property_location" class="float-left service_area_chk" value="1" /> 
							<label class="float-left">Yes</label>
						</div>
						<div class="float-left">
							<input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="property_location" class="float-left service_area_chk" value="0" /> 
							<label class="float-left">No</label>
						</div>
					</div>				
				</div>
			</div>			
			
			<div class="row service_area_yes_div hidden_divs">
				<div class="row boldItalic" style="text-align: left;">
					Before we move forward, I do need to advise you at the time of booking we will require an upfront payment of $<span id="agency_smoke_alarm_price"></span> via debit or credit card. This payment will cover the service fee and secure your booking. Are you happy with that?
				</div>
				
				<div class="row">					
					<div>								
						<div>
							<div class="float-left">
								<input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="aggree_terms" class="float-left aggree_chk" value="1" /> 
								<label class="float-left">Yes</label>
							</div>
							<div class="float-left">
								<input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="aggree_terms" class="float-left aggree_chk" value="0" /> 
								<label class="float-left">No</label>
							</div>
						</div>				
					</div>
				</div>
			</div>
			
			<div class="row boldItalic hidden_divs service_area_no_div" style="text-align: left;">
				Unfortunately we currently do not service that area. I wish I could recommend someone for you but we don't have those details
			</div>
			
			<div class="row prop_ten_div_yes hidden_divs">
			
				<div class="row boldItalic" style="text-align: left;">
					Let me briefly run over our Process and Pricing. On the day of service, our Technician will survey the property and service any existing smoke alarms. If any additional smoke alarms are required, our technician will ONLY install the minimum amount to bring the property up and in line with the current standards. The costs of these will be $40 for a 10-year Lithium Ion smoke alarm or $60 for a 240v hard-wired smoke alarm. If need, we will invoice you for these after the attendance and you can call us to make the payment
				</div>
				
				
				
				<div class="row">				
					<div>				
						<label class="boldItalic">Is your property tenanted or are you currently living there?</label>				
						<div>
							<div class="float-left"><input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="property_tenanted" class="float-left serv_sats property_tenanted_chk" value="0" /> <label class="float-left">Living in</label></div>
							<div class="float-left"><input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="property_tenanted" class="float-left serv_sats property_tenanted_chk" value="1" /> <label class="float-left">Tenanted</label></div>
						</div>				
					</div>				
				</div>
				
			</div>
			
			<div class="row prop_ten_div_no hidden_divs" style="text-align: left;">
				Answer any questions they have and advise that we are not able to proceed without an upfront payment
			</div>
			
			<div class="row boldItalic hidden_divs property_tenanted_div_no">
				Can I start with the property address?
			</div>
			
			<div class="row hidden_divs property_tenanted_div_yes">
				<div class="row">					
					<div>				
						<label class="boldItalic">Is this privately managed or Managed by an agency?</label>				
						<div>
							<div class="float-left"><input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="private_managed" class="float-left serv_sats private_managed_chk" value="0" /> <label class="float-left">Agency</label></div>
							<div class="float-left"><input type="radio" style="width:auto; float:left; margin: 6px 6px 0 6px;" name="private_managed" class="float-left serv_sats private_managed_chk" value="1" /> <label class="float-left">Private</label></div>
						</div>				
					</div>
				</div>
				
				<div class="row hidden_divs private_managed_div_agency boldItalic" style="text-align: left;">
					Request Managing Property information. Agency name and Agents name, contact number. 
				</div>
				
				<div class="row hidden_divs private_managed_div_private boldItalic" style="text-align: left;">
					Request Tenant contact information. Including Name, Mobile and email (if available.)
				</div>	
			</div>
			
			<div class="row hidden_divs booking_job_div boldItalic" style="text-align: left;">
				Booking job – Well {name}, I can see our Technician {Tech name} is available on {date} at {time}. Are you happy to me to book the service in?
			</div>
			
			</div>
		
			
			<div class="form_div hidden_divs">
		
				<div class="row">
					<input class="addinput" style="width: 552px;" type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" />
				</div>
				
				<div style="clear:both;"></div>
				
				<div class="row google_address_bar_fields">
				
					<div class="row">
						<label>No.<span style="color:red">*</span></label>
						<input class="addinput" style="width: 77px;" type="text" name="address_1" id="address_1" onkeydown="return keypress(event);">
					</div>
					<div class="row">
						<label>Street<span style="color:red">*</span></label>
						<input class="addinput" style="width: 165px;" type="text" name="address_2" id="address_2" onkeydown="return keypress(event);">
					</div>
					<div class="row">
						<label>Suburb <span style="color:red">*</span></label>
						<input class="addinput" style="width: 141px" type="text" name="address_3" id="address_3" onkeydown="return keypress(event);"> 
					</div>
					<?php
					if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
					
						<div class="row">
							<label>State</label>
							<select class="addinput" name="state" id="state" onkeydown="return keypress(event);" style="width: 81px;">
								<option value="">----</option> 
								 <?php
								  $state_sql = getCountryState();
								  while($state = mysql_fetch_array($state_sql)){ ?>
									<option value='<?php echo $state['state']; ?>'><?php echo $state['state']; ?></option>
								  <?php	  
								  }
								  ?>		
							 </select>
						 </div>
					
					<?php			
					}else{ ?>
					
					
						<div class="row">
							<label>Region</label>
							<input type="text" style="width: 81px;" name="state" id="state" class="addinput" />
						</div>
						
					<?php	
					}
					?>
					
					<div class="row">
						<label>Postcode <span style="color:red">*</span></label>
						<input class="addinput" style="width: 71px;" type="text" name="postcode" id="postcode" /> 
					</div>
				
				</div>
				
				
				
				
			<div style="clear:both;"></div>
			
			<div class="row">
				<label>First Name <span class='ll_req' style='color:red; display:none;'>*</span></label>
				<input class='tenantinput addinput' type=text name='landlord_firstname' id='landlord_firstname' value='' onkeydown='return keypress(event);'>
			</div>
			
			<div class="row">
				<label>Last Name</label>
				<input class='tenantinput addinput' type=text name='landlord_lastname' value='' onkeydown='return keypress(event);'>
			</div>	

			<div class="row">
				<label>Mobile <span class='ll_req' style='color:red; display:none;'>*</span></label>
				<input class='tenantinput addinput' type=text name='landlord_mobile' id='landlord_mobile' value='' onkeydown='return keypress(event);'>
			</div>
			
			<div class="row">
				<label>Landline</label>
				<input class='tenantinput addinput' type=text name='landlord_landline' value='' onkeydown='return keypress(event);'>
			</div>						

			<div class="row">
				<label>Email <span class='ll_req' style='color:red; display:none;'>*</span></label>
				<input class='tenantinput addinput' type=text name='landlord_email' id='landlord_email' value='' onkeydown='return keypress(event);'>
			</div>
			

		
		<div class="tenanted_form_details_div hidden_divs">
		<div class="row">
			<div class="row">
				<label>Key Number</label>
				<input class="tenantinput addinput" style="float:left;width: 120px;" type="text" name="key_num" >
			</div>
			
			<div class="row">
				<label>Work Order No.</label>
				<input class="tenantinput addinput" style="float:left;width: 120px;" type="text" name="work_order_num" id="work_order_num">
			</div>
		
			<div class="row">
				<label>Alarm Code</label>
				<input class="tenantinput addinput" style="float:left;width: 120px;" type="text" name="alarm_code" id="alarm_code">
			</div>
			
			<div class="row">
				<label>Short Term Rental</label>
				<input class="tenantinput addinput" style="float:left; width: auto;" type="checkbox" name="holiday_rental" id="holiday_rental" value="1">
			</div>
			
			<div class="row">
				<label>Property Vacant</label>
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

		 
		 
		
	<!-- NEW TENANT START HERE -->
	<div id="main_tenant_div">
            
                <table>
                    <thead>
					<tr style="border:0px;">
					<th colspan="5">Tenants</th>
					</tr>
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
            <!-- NEW TENANT END HERE -->
		

		
		
		
		</div>
		
		
		<div id="sevices_div" style="margin: 20px 0;"></div>
		
		<div class="row" style="text-align: left;">
			(If yes)Finalising – Great, I can confirm that this has been secured. I would just like to let you know that we will send an SMS the night before, as a reminder, with all of the bookings
		</div>

	
		<label for="submit" class="addlabel submitbth">
		
		<input type="hidden" name="require_work_order" id="require_work_order" value="0" />
		<input type="hidden" name="agency_id" id="agency_id" value="" />
		<input type="hidden" name="franchise_groups_id" id="franchise_groups_id" value="" />
		
        <button class="submitbtnImg submitbutton" type="button" <?php if(ifCountryHasState($_SESSION['country_default'])==true){ ?> style="display:none;" <?php } ?> />Add Property</button>
		
        </label>
		
		
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
.hidden_divs{
	display: none;
}

.boldItalic, .boldItalic label{
	font-style: italic !important;
	font-weight: bold !important;
}

.addproperty .row {
    clear: none;
	text-align: left;
}

.form_div .row{
	float: left !important;
}
.form_div input, .form_div select{
	margin-right: 5px;
}

.addproperty {
    width: auto;
	text-align: left;
}

.form_div .row input.addinput {
    width: 200px;
}
.add_prop_frstform {
    width: auto;
</style>

<script type="text/javascript">
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



jQuery(document).ready(function(){


	//added by gherx start
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
		//added by gherx end
	
	
	
	
	jQuery(".service_area_chk").change(function(){
		
		var service_area_chk = jQuery(this).val();
		
		if( service_area_chk==1 ){
			jQuery('.service_area_yes_div').show();
			jQuery('.service_area_no_div').hide();
		}else{
			jQuery('.service_area_yes_div').hide();
			jQuery('.service_area_no_div').show();
		}
		
	});
	
	jQuery(".aggree_chk").change(function(){
		
		var aggree_chk = jQuery(this).val();
		
		if( aggree_chk==1 ){
			jQuery('.prop_ten_div_yes').show();
			jQuery('.prop_ten_div_no').hide();
		}else{
			jQuery('.prop_ten_div_yes').hide();
			jQuery('.prop_ten_div_no').show();
		}
		
	});
	
	jQuery(".property_tenanted_chk").change(function(){
		
		var property_tenanted_chk = jQuery(this).val();
		
		if( property_tenanted_chk==1 ){
			jQuery('.property_tenanted_div_yes').show();
			jQuery('.property_tenanted_div_no').hide();
			jQuery(".form_div").show();
			jQuery(".tenanted_form_details_div").show();
		}else{
			jQuery('.property_tenanted_div_yes').hide();
			jQuery('.property_tenanted_div_no').show();
			jQuery(".form_div").show();
			jQuery(".tenanted_form_details_div").hide();
		}
		
	});
	
	
	jQuery(".private_managed_chk").change(function(){
		
		var private_managed_chk = jQuery(this).val();
		
		if( private_managed_chk==1 ){
			jQuery('.private_managed_div_private').show();
			jQuery('.private_managed_div_agency').hide();
			jQuery('.booking_job_div').show();
			jQuery('.form_div').show();
		}else{
			jQuery('.private_managed_div_private').hide();
			jQuery('.private_managed_div_agency').show();
			jQuery('.booking_job_div').hide();
			jQuery('.form_div').hide();
		}
		
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
		var franchise_groups_id = jQuery("#franchise_groups_id").val();
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
		if( franchise_groups_id==10 ){
			
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
			url: "ajax_get_agency_services_private.php",
			async: false,
			data: {
				agency_id: agency_id
			}
		}).done(function(ret){
			jQuery("#sevices_div").html(ret);
			jQuery("#agency_id").val(agency_id);
			
			// get smoke alarm price
			var sa_price = jQuery(".alarm_job_type_id[value='2']").parents("div.agency_service_row").find(".price").val();
			//alert("SA price: "+sa_price);
			jQuery("#agency_smoke_alarm_price").html(sa_price);
		});
		
		<?php
		if(ifCountryHasState($_SESSION['country_default'])==true){ ?>
			// get agency state
			jQuery.ajax({
				type: "POST",
				url: "/ajax_get_agency_state.php",
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

	jQuery("#agency_name").change(function(){
		
		var agency_id = jQuery(this).val();	
		
		if(agency_id!=""){
			
			
			
			var dha_agencies = ['3043','3036','3046','1902','3044','1906','1927','3045'];
			if( dha_agencies.indexOf(agency_id)>-1 ){
				jQuery("#dha_agencies_fields").show();
			}else{
				jQuery("#dha_agencies_fields").hide();
			}
			
			jQuery("#jprop_div").show();
			get_agency_hidden_details(agency_id);
			
		}else{
			jQuery("#jprop_div").hide();
		}
		
		
		
	});	
	
	// repopulate franchise group
	jQuery("#agency_name").change(function(){
		
		var agency_id = jQuery(this).val();
		var franchise_groups_id;
		console.log("Agency ID: "+agency_id);
		
		// get agency services
		jQuery.ajax({
			type: "POST",
			url: "ajax_get_agency_franchise_group.php",
			data: {
				agency_id: agency_id
			}
		}).done(function(ret){
			
			franchise_groups_id = ret;
			console.log("FG ID: "+franchise_groups_id);
			jQuery("#franchise_groups_id").val(franchise_groups_id);
			// if private
			if( franchise_groups_id==10 ){
				jQuery(".ll_req").show();
			}else{
				jQuery(".ll_req").hide();
			}
			
		});
		
		
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
	
	
	
	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
