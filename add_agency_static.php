<?

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);

$title = "Add an Agency";

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
        <li class="other first"><a title="Add Agency" href="/add_agency_static.php"><strong>Add Agency</strong></a></li>
      </ul>
    </div>
    <div id="time"><?php echo date("l jS F Y"); ?></div>
    
    <div class="formholder addagency">
      <form id="form1" name="form1" method="POST" action="agency_controller.php">
        
        <div class="row">
          <label class="addlabel" for="agen_stat">Agency Status</label>
          <select class="addinput" name="agen_stat" id="agen_stat">
            <option value="">----</option>
            <option value='active'>Active</option>
            <option value='target'>Target</option>
          </select>
        </div>
        
        <div id="active_div" style="display:none;">
        
        <div class="row">
          <label class="addlabel" for="agency_name">Agency Name <span style="color:red">*</span></label>
          <input class="addinput" type="text" name="agency_name" id="agency_name">
        </div> 

		<div class="row">
          <label class="addlabel not_included" for="legal_name">Legal Name</label>
          <input class="addinput not_included" type="text" name="legal_name" id="legal_name">
        </div>
		
		<div class="row">
				<label for="franchise_group" class="addlabel">Franchise Group <span style="color:red">*</span></label>
				<select id="franchise_group" name="franchise_group" class="addinput">
					<option value="">----</option>
					<?php
					$fg_sql = mysql_query("
						SELECT *
						FROM `franchise_groups`
						WHERE `country_id` = {$_SESSION['country_default']}
						ORDER BY `name`
					");
					while($fg = mysql_fetch_array($fg_sql)){ ?>
						<option value="<?php echo $fg['franchise_groups_id'] ?>"><?php echo $fg['name'] ?></option>
					<?php
					}
					?>
				</select>
			</div>
			
			<div class="row">
				  <label class="addlabel" for="abn"><?php echo $_SESSION['country_default']==1?'ABN Number':'GST Number'; ?></label>
				  <input class="addinput" type="text" name="abn" id="abn" />
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
          <label class='addlabel' for='totproperties'>Total Properties </label>
          <input class="addinput" type="text" name="totprop" onkeypress="return numbersonly(event)">        
          </div>
		  
		  
		  <div class="row">
          <label class='addlabel' for='agency_hours'>Agency Hours</label>
          <input class="addinput" type="text" name="agency_hours" />        
          </div>
		  
		  
		  <div class="row">
          <label class='addlabel' for='comment'>Comments</label>
          <input class="addinput" type="text" name="comment" />        
          </div>
		  
		  
		   <div class="row">
          <label class='addlabel'>Agency Specific Notes</label>
          <input class="addinput" type="text" name="agency_specific_notes" />        
          </div>
		  
		  <div class="row">
          <label class='addlabel' for='website'>Website</label>
          <input class="addinput" type="text" name="website" />        
          </div>
		  
		   <div class="row target_only" style="display:none;">
          <div>
          <div class="row">
            <label class='addlabel' for='totproperties'>Currently Using</label>
            <select name="agency_using">
			<option value="">----</option>
			<?php 
			$au_sql = getAgencyUsingByCountry();
			while($au = mysql_fetch_array($au_sql)){ ?>
				<option value="<?php echo $au['agency_using_id']; ?>"><?php echo $au['name']; ?></option>
			<?php
			}
			?>
			</select>
            </div>
          </div>
          </div>
		  
		   <div class="row">
   


			
			
			
          </div>



		  <div class="row">
			<label class='addlabel' for='website'>Agency Special Deal</label>
			<textarea class="addtextarea wider" name="agency_special_deal" id="agency_special_deal"></textarea>
          </div>

		  
		  
          
          <h2 class="heading">Agency Contact</h2>
          <div class="row">
          <label class='addlabel' for='totproperties'>First Name</label>
          <input class="addinput" type="text" name="ac_fname">
          </div>
          <div class="row">
          <label class='addlabel' for='totproperties'>Last Name</label>
          <input class="addinput" type="text" name="ac_lname">
          </div>
          <div class="row">
          <label class='addlabel' for='totproperties'>Landline</label>
          <input class="addinput" type="text" name="ac_phone">
          </div>
          <div class="row">
          <label class='addlabel' for='totproperties'>Email</label>
          <input class="addinput" type="text" name="ac_email">
          </div> 
			<div class="row">
          <label class='addlabel' for='totproperties'>Accounts Name</label>
          <input class="addinput" type="text" name="acc_name">
          </div>
          <div class="row">
          <label class='addlabel' for='totproperties'>Accounts Phone</label>
          <input class="addinput" type="text" name="acc_phone">
          </div> 
		  
		  
		    <h2 class="heading">Agency Emails</h2>
            <div class="row">
            <label class='addlabel' for='totproperties'>Agency Emails <strong>(Reports, Key Sheet)</strong> <br />(one per line) <span style="color:red">*</span></label>
            <textarea class="addtextarea wider" name="agency_emails" id="agency_emails"></textarea>
            </div>
           
		  
                
          <div class="not_included" style="display:none;">
		  
		  
			<div class="row">
            <label class='addlabel' for='totproperties'>Accounts Emails <strong>(Invoices, Certificates)</strong> <br />(one per line) <span style="color:red">*</span></label>
            <textarea class="addtextarea wider" name="account_emails" id="account_emails"></textarea>
            </div>
            
			<!-- PREFERENCES START -->
            <h2 class="heading">Preferences</h2>
            
            <div class="row form-add-agency">
          

            
        
            <div class="row form-add-agency">
            <div class="form-title">Individual Property Mangers Receive Certificate & Invoice?</div>
            <div class="float-left">
            <label>
              <input type="radio" value="1" name="allow_indiv_pm_email_cc" />
              Yes
			</label>
            </div>            
            <div class="float-left">
            <label>
              <input type="radio" value="0" name="allow_indiv_pm_email_cc" checked="checked" />
              No
			</label>
            </div>
            </div>

			<div class="row form-add-agency">
				<div class="form-title">Allow Entry Notice?</div>
				<div class="float-left">
					<label>
				  <input type="radio" value="1" name="allow_en" id="allow_en_yes" />
				  Yes</label>
				</div>            
				<div class="float-left">
				 <label>
				  <input type="radio" value="0" name="allow_en" id="allow_en_no" />
				  No</label>
				</div> 
				<div class="float-left">
				 <label>
				  <input type="radio" value="-1" name="allow_en" id="allow_en_nr" checked="checked" />
				  No Response</label>
				</div> 				
            </div>
			
			
			<div class="row form-add-agency">
				<div class="form-title">All New Jobs Emailed to Agency?</div>
				<div class="float-left">
					<label for="allow_dk_yes">
				  <input type="radio" value="1" name="new_job_email_to_agent" id="new_job_email_to_agent_yes" />
				  Yes</label>
				</div>            
				<div class="float-left">
				 <label for="allow_dk_no">
				  <input type="radio" value="0" name="new_job_email_to_agent" id="new_job_email_to_agent_no" checked="checked" />
				  No</label>
				</div>          
            </div>


			<div class="row form-add-agency">
				<div class="form-title">Subscription Billing?</div>
				<div class="float-left">
					<label>
						<input type="radio" value="1" name="allow_upfront_billing" /> Yes					
				  	</label>
				</div>            
				<div class="float-left">
					<label>
						<input type="radio" value="0" name="allow_upfront_billing" checked="checked" /> No						
					</label>
				</div>          
            </div>
			
		<!-- PREFERENCES END -->



		
			
			
		<div style="display:none;" id="pm_div">
		
			<div class="row form-add-agency">
			  <div class="form-title">Property Managers:</div>
			  <div class="float-left">
				<table id="pm_table" style="border:none;">
					<tbody>
						<tr>
							<td>
								<input type="text" name="pm_name[]" class="addinput pm_name"  />
							</td>
							<td>
								<button class="addinput submitbtnImg eagdtbt btn_remove" type="button" style="margin:0;">X</button>
							</td>
						</tr>
					</tbody>	
				</table>	
			  </div>		  	
			</div>
			
			<div class="row form-add-agency">
			  <div class="form-title">&nbsp;</div>
			  <div class="float-left">
			 <button type="button" id="btn_add" class="addinput submitbtnImg eagdtbt">+ Property Manager</button>	    
			  </div>		  
			</div>
			
		</div>
		
		
		
			
		
    
            
            <h2 class="heading">Alarms</h2>
            <table id='custom_price_table'>
              <thead>
                <tr>
                  <th class="bg-red">Type</th>
                  <th class="bg-red">Approved</th>
                  <th class="bg-red">Price</th>
                </tr>
              </thead>
              <tbody>
                <?php
					$alarm_sql = $agency->get_alarms();
					$index = 0;
					while($alarm = mysql_fetch_array($alarm_sql)){ 
					
					$alarm_240v_rf = 10; // 240v RF	
					$is_alarm_240v_rf = ( $alarm['alarm_pwr_id'] == $alarm_240v_rf )?true:false;
						
					?>
                <tr>
                  <td style="display:none;"><input type="hidden" name="alarm_pwr_id[]" value="<?php echo $alarm['alarm_pwr_id']; ?>">
                    <input type="hidden" name="alarm_is_approved[]" class="is_approved" value="0" /></td>
                  <td>
				  	<?php echo $alarm['alarm_pwr']; ?> 
					<?php echo ( $is_alarm_240v_rf == true )?'<strong style="color:red;">(Required for Quotes)</strong>':null; ?>
				  </td>
                  <td><input type="checkbox" name="alarm_approve[]" class="alarm_approve approve <?php echo ( $is_alarm_240v_rf == true )?'alarm_240v_rf_chk':null; ?>" value="<?php echo $index; ?>"></td>
                  <td><div class="price_div">
                  	 <span style="float: left; margin-top: 9px; margin-right: 5px;">$</span>
                      <input type="text" name="alarm_price[]" class="addinput alarm_price price">
                    </div></td>
                </tr>
                <?php 
					$index++;
					}							
					?>
              </tbody>
            </table>
            <div style="clear:both;">&nbsp;</div>
            
            <h2 class="heading">Services</h2>
            <table id="custom_price_table">
              <thead>
                <tr>
                  <th class="bg-red">Type</th>
                  <th class="bg-red">Approved</th>
                  <th class="bg-red">Price</th>
                </tr>
              </thead>
              <tbody>
                <?php
					$services_sql = $agency->get_services();
					$index = 0;
					while($services = mysql_fetch_array($services_sql)){

					$sa_ic = 12; // Smoke Alarms (IC)
					$is_sa_ic = ( $services['id'] == $sa_ic )?true:false;

					?>
                <tr>
                  <td style="display:none;"><input type="hidden" name="service_id[]" value="<?php echo $services['id']; ?>">
                    <input type="hidden" name="service_is_approved[]" class="is_approved" value="0"></td>
                  <td>
				  	<?php echo $services['type']; ?>
					<?php echo ( $is_sa_ic == true )?'<strong style="color:red;">(Required for Quotes)</strong>':null; ?>
				  </td>
                  <td><input type="checkbox" name="service_approve[]" class="service_approve approve <?php echo ( $is_sa_ic == true )?'sa_ic_chk':null; ?>" value="<?php echo $index; ?>"></td>
                  <td><div class="price_div">
                  	  <span style="float: left; margin-top: 9px; margin-right: 5px;">$</span>	
                      <input type="text" name="service_price[]" class="addinput service_price price">
                    </div></td>
                </tr>
                <?php
					$index++;
					}							
					?>
              </tbody>
            </table>
          </div>
          

          
          <div style="clear:both;">&nbsp;</div>
          
        </div>
		
		
		<div class="not_included" style="display:none;">
		  
		  <h2 class="heading">Maintenance Program</h2>
		  
		
       
				<select name="maintenance" class="maintenance" id="maintenance">
					<option value=''>None</option>
					<?php 
					$m_sql = mysql_query("
						SELECT *
						FROM `maintenance`
					");
					while($m = mysql_fetch_array($m_sql)){ ?>
						<option value='<?php echo $m['maintenance_id']; ?>'><?php echo $m['name']; ?></option>
					<?php	
					}
					?>
				</select>
          
			
			<div id="maintenance_program_div" style="display:none;" class="addproperty">
				
				
				<div class="row">
			
					<label style="float: left; margin-right: 57px;">Apply Surcharge to all Invoices?</label> 
					<div class="eagdt-rd-h">
						<div class="eagdt-rd">
							<label for="send_emails_1"><input type="radio" value="1" name="m_surcharge"> Yes</label>
						</div>
						<div class="eagdt-rd">
							<label for="send_emails_0"><input type="radio" value="0" name="m_surcharge"> No</label></div>
						</div>
						
				</div>
				<div style="clear:both;"></div>
				
				<div class="row">
					
					<label style="float: left; margin-right: 10px;">Surcharge $</label> <input type="text" style="width: 54px; float: left; margin-left: 32px;" class="addinput" name="m_price" id="m_price" />
				</div>
				<div style="clear:both;"></div>
				
				<div class="row">
					
					<label style="float: left; margin-right: 52px;">Display Message on all Invoices?</label> 
					<div class="eagdt-rd-h">
						<div class="eagdt-rd">
							<label for="send_emails_1"><input type="radio" value="1" name="m_disp_surcharge"> Yes</label>
						</div>
						<div class="eagdt-rd">
							<label for="send_emails_0"><input type="radio" value="0" name="m_disp_surcharge"> No</label></div>
						</div>
					
					
					
				</div>
				<div style="clear:both;"></div>
				
				
				
				<div class="row">
				
					<label style="float: left; margin-right: 10px;">Invoice Message</label> <input type="text" style="width: 440px; float: left; margin-left: 5px;" class="addinput" name="m_surcharge_msg" id="m_surcharge_msg" />
				</div>
				<div style="clear:both;"></div>
				
			</div>
			
			</div>
		
		
		<h2 class="heading">Sales Rep</h2>
		<select name="salesrep" class="addinput">
		  <option>-- Select a Sales Rep --</option>
		  <?php
				$salesrep_sql = mysql_query("
					SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
					FROM staff_accounts AS sa
					INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
					WHERE sa.deleted =0
					AND sa.active =1			
					AND ca.`country_id` ={$_SESSION['country_default']}
					AND (
						sa.`ClassID` = 2 OR 
						sa.`ClassID` = 5 OR
						sa.`ClassID` = 9
					)
					ORDER BY sa.`FirstName`
				");
				while($salesrep = mysql_fetch_array($salesrep_sql)){ ?>
		  <option value="<?php echo $salesrep['staff_accounts_id'] ?>"><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
		  <?php 
				}
				?>
		</select>
		
		
		<label for="submit">
            <button class="submitbtnImg" type="button" name="add_agency" id="add_agency" style="margin-top: 10px;">Add Agency</button>
          </label>
        
      </form>
    </div>
    
  </div>
  
</div>

</div>

<br class="clearfloat" />



<style>
.price_div{
	display:none;
}
.form-add-agency .form-title {
    width: 314px;
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

 
  var state = jQuery("#state").val();
  if( state == 'QLD' ){

	// state is QLD, auto tick 240v RF alarm and set price as 200  
	var alarm_240v_rf_chk_node = jQuery(".alarm_240v_rf_chk");
	var parent_row = alarm_240v_rf_chk_node.parents("tr:first");

	alarm_240v_rf_chk_node.prop("checked",true); // tick it
	parent_row.find(".is_approved").val(1); // mark as approved
	parent_row.find(".price_div").show(); // show price div
	parent_row.find(".alarm_price").val(200); // set price

	// state is QLD, auto tick SA IC service and set price as 119  
	var sa_ic_chk_node = jQuery(".sa_ic_chk");
	var parent_row = sa_ic_chk_node.parents("tr:first");

	sa_ic_chk_node.prop("checked",true); // tick it
	parent_row.find(".is_approved").val(1); // mark as approved
	parent_row.find(".price_div").show(); // show price div
	parent_row.find(".service_price").val(119); // set price

  }
  
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
	
	// postcode region auto fill script
	jQuery("#postcode").blur(function(){
		
		var postcode = jQuery(this).val();
		
		getRegionViaPostcode(postcode);
		
	});
	
	
	jQuery("#maintenance").change(function(){
		
		if(jQuery(this).val()!=""){
			jQuery("#maintenance_program_div").show();
		}else{
			jQuery("#maintenance_program_div").hide();
		}
		
	});

	function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}

	jQuery("#allow_indiv_pm_yes").click(function(){
		jQuery("#pm_div").show();
	});
	
	jQuery("#allow_indiv_pm_no").click(function(){
		jQuery("#pm_div").hide();
	});
	
	// remove pm field
	jQuery(document).on("click",".btn_remove",function(){
	  jQuery(this).parents("tr:first").remove();
	});

	// add pm field
	jQuery("#btn_add").click(function(){
		var str = '<tr>'+
						'<td>'+
							'<input type="text" name="pm_name[]" class="addinput pm_name"  />'+
						'</td>'+
						'<td>'+
							'<button class="addinput submitbtnImg eagdtbt btn_remove" type="button" style="margin:0;">X</button>'+
						'</td>'+
					'</tr>';
		jQuery("#pm_table tbody").append(str);
	  
	});

	// append username script
	jQuery("#agency_name").blur(function(){
	  var agency_name = jQuery(this).val();
	  if(agency_name!=""){
		 var temp = agency_name.replace(/ /gi,"_");
		jQuery("#user").val(temp.toLowerCase(temp));
	  }	 
	});

	// agency status script
	jQuery("#agen_stat").click(function(){
		if(jQuery(this).val()=='active'){
			jQuery("#active_div").show();
			jQuery(".not_included").show();
			jQuery(".target_only").hide();
			jQuery("#add_agency").html("Add Active Agency");
		}else if(jQuery(this).val()=='target'){
			jQuery("#active_div").show();
			jQuery(".not_included").hide();
			jQuery(".target_only").show();
			jQuery("#add_agency").html("Add Target Agency");
		}else{
			jQuery("#active_div").hide();
		}		
	});

	// require approve price script
	jQuery(".approve").click(function(){
		
		// is approved hidden value
		var state = jQuery(this).prop("checked");
		if(state==true){
			jQuery(this).parents("tr:first").find(".is_approved").val(1);
			jQuery(this).parents("tr:first").find(".price_div").show();
			// add req class for validation
			jQuery(this).parents("tr:first").find(".price").addClass("req");
		}else{
			jQuery(this).parents("tr:first").find(".is_approved").val(0);
			jQuery(this).parents("tr:first").find(".price").val("");
			jQuery(this).parents("tr:first").find(".price_div").hide();
			// add req class for validation
			jQuery(this).parents("tr:first").find(".price").removeClass("req");
		}
	});

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
		}else{

			if(agen_stat=='active'){

				// 240v RF alarm is required on QLD state
				if( state == 'QLD' && jQuery(".alarm_240v_rf_chk").prop("checked") == false ){
					error += "240v RF alarm is required\n";
				}

			}			

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


			// alarm required
			var alarms_ticked_num = jQuery(".alarm_approve:checked").length;
			if( alarms_ticked_num == 0 ){
				error += "Alarm is required\n";
			}

			// alarm price
			jQuery(".alarm_price:visible").each(function(){

				var alarm_price = jQuery(this).val();

				if( alarm_price == 0 || alarm_price == '' ){
					error += "Alarm Price is required\n";
				}

			});
			

			// service required
			var alarms_ticked_num = jQuery(".service_approve:checked").length;
			if( alarms_ticked_num == 0 ){
				error += "Service is required\n";
			}


		}
		
		/*
		jQuery(".req").each(function(){
			if(jQuery(this).val()==""){
				flag = 1;
			}
		});
		if(flag == 1){
			error += "Approved Price is required\n";
		}
		*/

		
		
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