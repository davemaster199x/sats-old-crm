<?php

$title = "Incident and Injury Report Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;
$iai_id = mysql_real_escape_string($_REQUEST['id']);

$params = array(
	'iai_id' => $iai_id
);
$iai_sql = $crm->getIncidentAndReport($params);
$iai = mysql_fetch_array($iai_sql);

?>
<style>
.addproperty input, .addproperty select, .addproperty textarea {
    width: 20%;
}
.addproperty label {
   width: 230px;
}
.tbl_chkbox td{
	text-align: left;
}

.tbl_chkbox tr{
	border: none !important;
}

.tbl_chkbox tr.tr_last_child{
	border-bottom: medium none !important;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Incident Summary" href="incident_and_injury_report_list.php">Incident Summary</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="incident_and_injury_report_details.php?id=<?php echo $iai_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success" style="margin-bottom: 12px;">Update Successful</div>
	<?php
	}
	?>
      	
	<form action="/incident_and_injury_report_update.php" method="post" id="jform" style="font-size: 14px;" enctype="multipart/form-data">
	<div class="addproperty" style="width: 100%;">	
		
		
		<div class="row">
			<h2 class="heading">The Incident</h2>
		</div>
		<div class="row">
			<label class="addlabel">Date of incident</label>
			<input type="text"  class="addinput datepicker date_of_incident" name="date_of_incident" id="date_of_incident" value="<?php echo date('d/m/Y',strtotime($iai['datetime_of_incident'])); ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Time of incident</label>
			<input type="text"  class="addinput timepicker time_of_incident" name="time_of_incident" id="time_of_incident" value="<?php echo date('H:i',strtotime($iai['datetime_of_incident'])); ?>" />
		</div>	
		<div class="row">
			<label class="addlabel">Nature of incident</label>
			<select name="nature_of_incident" id="nature_of_incident">
				<option value="">----</option>
				<option value="1" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==1 )?'selected="selected"':''; ?>>Near Miss</option>	
				<option value="2" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==2 )?'selected="selected"':''; ?>>First Aid</option>
				<option value="3" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==3 )?'selected="selected"':''; ?>>Medical Treatment</option>
				<option value="4" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==4 )?'selected="selected"':''; ?>>Car accident</option>	
				<option value="5" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==5 )?'selected="selected"':''; ?>>Property damage</option>
				<option value="6" <?php echo ( is_numeric($iai['nature_of_incident']) && $iai['nature_of_incident']==6 )?'selected="selected"':''; ?>>Incident report</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Location of incident</label>
			<input type="text"  class="addinput" name="loc_of_inci" id="loc_of_inci" value="<?php echo $iai['location_of_incident'] ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Describe the incident</label>
			<textarea name="desc_inci" class="addtextarea desc_inci" style="height: 84px; margin:0px;"><?php echo $iai['describe_incident'] ?></textarea>
		</div>
		
		
		<?php
		
		$poi_sql = $crm->getIncidentPhotos($iai_id);
		while( $poi = mysql_fetch_array($poi_sql) ){ 
			$img = explode("/",$poi['image_name']);
		?>	
			
			
			<div class="row photo_row_div">
				<label class="addlabel">Photo of Incident</label>
				<div style="float: left;">
				<input capture="camera" accept="image/*" name="photo_of_incident[]" id="photo_of_incident" class="addinput photo_of_incident" style="margin-top: 2px; width: 241px; margin-right: 20px;" type="file" />
				</div>
				<div class="photo_details_div" style="float: left;">
					<div style="float: left;">
					<img src="/images/camera_blue.png"/>
					</div>
					<div style="float: left;">
					<a href="<?php echo $poi['image_name']; ?>" class="fancybox" style="left: 5px; position: relative; top: 4px;" target="_blank"><?php echo $img[3]; ?></a>	
					</div>
					<div style="float: left; margin-left: 15px; margin-top: 5px;">
						<a href="javascript: void(0);" class="link_remove_photo"><img src="/images/cross_red.png" style="width: 11px;" /></a>
					</div>
					<input type="hidden" name="incident_photos_id[]" class="incident_photos_id" value="<?php echo $poi['incident_photos_id']; ?>" />
				</div>
			</div>	
		<?php	
		}
		?>			
		
		<div class="row" style="margin-top: 34px;">
			<label class="addlabel">&nbsp;</label>
			<button type="button" style="float: left;" id="btn_add_photo" class="blue-btn submitbtnImg btn_add_photo">
				<img class="inner_icon" src="images/button_icons/add-button.png">
				Photo
			</button>
		</div>	
		
		
		<div class="row">
			<h2 class="heading">Injured Person Details</h2>
		</div>
		<div class="row">
			<label class="addlabel">Name</label>
			<input type="text"  class="addinput" name="ip_name" id="ip_name" value="<?php echo $iai['ip_name']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Address</label>
			<input type="text"  class="addinput" name="ip_address" id="ip_address" value="<?php echo $iai['ip_address']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Occupation</label>
			<input type="text"  class="addinput" name="ip_occu" id="ip_occu" value="<?php echo $iai['ip_occupation']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Date of birth</label>
			<input type="text"  class="addinput dob_dp" name="ip_dob" id="ip_dob" value="<?php echo date('d/m/Y',strtotime($iai['ip_dob'])); ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Telephone number</label>
			<input type="text"  class="addinput" name="ip_tel_num" id="ip_tel_num" value="<?php echo $iai['ip_tel_num']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Employer</label>
			<input type="text"  class="addinput" name="ip_employer" id="ip_employer" value="<?php echo $iai['ip_employer']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Nature of Injury</label>
			<input type="text"  class="addinput" name="ip_noi" id="ip_noi" value="<?php echo $iai['ip_noi']; ?>" />
		</div>	
		<div class="row">
			<label class="addlabel">Location of Injury</label>
			<input type="text"  class="addinput" name="ip_loi" id="ip_loi" value="<?php echo $iai['ip_loi']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Onsite treatment</label>
			<select name="ip_onsite_treatment" id="ip_onsite_treatment">
				<option value="">----</option>
				<option value="1" <?php echo ( is_numeric($iai['ip_onsite_treatment']) && $iai['ip_onsite_treatment']==1 )?'selected="selected"':''; ?>>Yes</option>	
				<option value="0" <?php echo ( is_numeric($iai['ip_onsite_treatment']) && $iai['ip_onsite_treatment']==0 )?'selected="selected"':''; ?>>No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Further treatment required?</label>
			<select name="ip_fur_treat" id="ip_fur_treat">
				<option value="">----</option>
				<option value="1" <?php echo ( is_numeric($iai['ip_further_treatment']) && $iai['ip_further_treatment']==1 )?'selected="selected"':''; ?>>Yes</option>	
				<option value="0" <?php echo ( is_numeric($iai['ip_further_treatment']) && $iai['ip_further_treatment']==0 )?'selected="selected"':''; ?>>No</option>
			</select>
		</div>
		
		<div class="row">
			<h2 class="heading">Witness Details</h2>
		</div>
		<div class="row">
			<label class="addlabel">Name</label>
			<input type="text"  class="addinput" name="witness_name" id="witness_name" value="<?php echo $iai['witness_name']; ?>" />
		</div>
		<div class="row">
			<label class="addlabel">Contact Number</label>
			<input type="text"  class="addinput" name="witness_contact" id="witness_contact" value="<?php echo $iai['witness_contact']; ?>" />
		</div>
		
		<div class="row">
			<h2 class="heading">Outcome</h2>
		</div>
		
		<div class="row">
			<label class="addlabel">Time lost due to injury</label>
			<select name="loss_time_injury" id="loss_time_injury">
				<option value="">----</option>
				<option value="1" <?php echo ( is_numeric($iai['loss_time_injury']) && $iai['loss_time_injury']==1 )?'selected="selected"':''; ?>>Yes</option>	
				<option value="0" <?php echo ( is_numeric($iai['loss_time_injury']) && $iai['loss_time_injury']==0 )?'selected="selected"':''; ?>>No</option>
			</select>
		</div>
		<div class="row">
			<label class="addlabel">Who was the incident reported to?</label>
			<select name="reported_to" id="loss_time_injury" style="display:block; float:left;">
				<option value="">----</option>
				<?php
				// for global and full access
				// sarah gutherie - 2226
				$staff_sql = mysql_query("
				SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
				FROM staff_accounts AS sa
				INNER JOIN `country_access` AS ca ON (
					sa.`StaffID` = ca.`staff_accounts_id` 
					AND ca.`country_id` ={$_SESSION['country_default']}
				)
				WHERE sa.deleted =0
				AND sa.active =1											
				AND (
					sa.`ClassID` = 2 OR 
					sa.`ClassID` = 9 OR
					sa.`StaffID` = 2226
				)
				ORDER BY sa.`FirstName`
				");
				while($staff = mysql_fetch_array($staff_sql)){ ?>
					<option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ($staff['staff_accounts_id']==$iai['reported_to'])?'selected="selected"':''; ?>><?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?></option>
				<?php 
				}
				?>
			</select>
			<div style="float: left; margin: 9px 0 0 6px; color: #00D1E5; font-size: 13px;">
				<?php echo date('d/m/Y H:i',strtotime($iai['created_date'])); ?>
			</div>
		</div>
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<div>
				<input type="checkbox" class="addinput" name="confirm_chk" id="confirm_chk" style="width:auto; float: left;" <?php echo ($iai['confirm_chk']==1)?'checked="checked"':''; ?> value="1" />
				<span style=" float: left; margin: 5px 0 0 10px; text-align: left; width: 18%;">
					I confirm that the information I have entered is true and correct to the best of my knowledge
				</span>
			</div>
		</div>
		
		<div class="row">
			<label class="addlabel">&nbsp;</label>
			<input type="hidden" name="iai_id" value="<?php echo $iai_id; ?>" />
			<input type="hidden" name="delete_existing_photos" id="delete_existing_photos"  value="" />
        	<button class="submitbtnImg" id="btn_add_vehicle" type="submit" style="float: left; margin-top: 30px;">
				<img class="inner_icon" src="images/button_icons/save-button.png">
				Update
			</button>
        </div>
	</div>
	</form>


    
  </div>

<br class="clearfloat" />



<script>

function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.
  
  var options = {
	  types: ['geocode'],
	  componentRestrictions: {country: 'au'}
	};
  
  autocomplete = new google.maps.places.Autocomplete(
	 (document.getElementById('ip_address')),
	 options
	  );

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  //autocomplete.addListener('place_changed', fillInAddress);
}

var deleted_photos_arr = [];

jQuery(document).ready(function(){
	
	
	
	// Update preferred time
	jQuery(".link_remove_photo").click(function(){
		
		var incident_photos_id = jQuery(this).parents(".photo_row_div:first").find(".incident_photos_id").val();
		
		if( confirm("Are you sure you want to delete?") ){
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_incident_photo.php",
				data: { 
					iai_id: <?php echo $iai_id; ?>,
					incident_photos_id: incident_photos_id
				}
			}).done(function( ret ){
				window.location="/incident_and_injury_report_details.php?id=<?php echo $iai_id; ?>"
			});
			
		}
		
		
	});
	
	
	jQuery(".photo_of_incident").change(function(){
		
		var deleted_photos = jQuery(this).parents(".photo_row_div:first").find(".incident_photos_id").val();		
		deleted_photos_arr.push(deleted_photos);

		console.log(deleted_photos_arr);
		jQuery("#delete_existing_photos").val(deleted_photos_arr);
		
	});
	
	
	// add photo 
	jQuery("#btn_add_photo").click(function(){
		
		var last_photo_elem = jQuery(".photo_row_div:last");		
		var photo_elem = last_photo_elem.clone();
		photo_elem.find(".photo_details_div").remove();
		photo_elem.find(".photo_of_incident").val("");
		last_photo_elem.after(photo_elem);
		
	});
	
	
	// invoke fancybox
	jQuery('.fancybox').fancybox();

	
	// time picker
	jQuery(".timepicker").timepicker();
	
	// dob
	jQuery(".dob_dp").datepicker({ 
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: "1940:2000", // year range
		dateFormat: "dd/mm/yy"	// date format					
	});
	
	
	// form validation
	jQuery("#jform").submit(function(){
		
		var error = "";
		
		// The Incident
		var date_of_incident = jQuery("#date_of_incident").val();
		var time_of_incident = jQuery("#time_of_incident").val();
		var nature_of_incident = jQuery("#nature_of_incident").val();
		var loc_of_inci = jQuery("#loc_of_inci").val();
		var desc_inci = jQuery("#desc_inci").val();
		
		// Injured Person Details
		var ip_name = jQuery("#ip_name").val();
		var ip_address = jQuery("#ip_address").val();
		var ip_occu = jQuery("#ip_occu").val();
		var ip_dob = jQuery("#ip_dob").val();
		var ip_tel_num = jQuery("#ip_tel_num").val();
		var ip_employer = jQuery("#ip_employer").val();
		var ip_noi = jQuery("#ip_noi").val();
		var ip_loi = jQuery("#ip_loi").val();
		var ip_onsite_treatment = jQuery("#ip_onsite_treatment").val();
		var ip_fur_treat = jQuery("#ip_fur_treat").val();
		
		// Witness Details
		var witness_name = jQuery("#witness_name").val();
		var witness_contact = jQuery("#witness_contact").val();
		// Outcome
		var loss_time_injury = jQuery("#loss_time_injury").val();
		var loss_time_injury = jQuery("#loss_time_injury").val();
	
		
		
		// The Incident
		if( date_of_incident=="" ){
			error += "Date of Incident is required\n";
		}
		if( time_of_incident=="" ){
			error += "Time of Incident is required\n";
		}
		if( nature_of_incident=="" ){
			error += "Nature of Incident is required\n";
		}
		if( loc_of_inci=="" ){
			error += "Location of Incident is required\n";
		}
		if( desc_inci=="" ){
			error += "Describe of Incident is required\n";
		}
		
		
		
		// Injured Person Details
		if( ip_name=="" ){
			error += "Injured Person Name is required\n";
		}
		if( ip_address=="" ){
			error += "Injured Person Address is required\n";
		}
		if( ip_occu=="" ){
			error += "Injured Person Occupation is required\n";
		}
		if( ip_dob=="" ){
			error += "Injured Person Date of birth is required\n";
		}
		if( ip_tel_num=="" ){
			error += "Injured Person Telephone number is required\n";
		}
		if( ip_employer=="" ){
			error += "Injured Person Employer is required\n";
		}
		if( ip_noi=="" ){
			error += "Injured Person Nature of Injury is required\n";
		}
		if( ip_loi=="" ){
			error += "Injured Person Location of Injury is required\n";
		}
		if( ip_onsite_treatment=="" ){
			error += "Injured Person Onsite treatment is required\n";
		}
		if( ip_onsite_treatment=="" ){
			error += "Injured Person Further treatment required is required\n";
		}
		
		
		
		// Witness Details
		if( witness_name=="" ){
			error += "Witness Details Name is required\n";
		}
		if( witness_contact=="" ){
			error += "Witness Details Contact Number is required\n";
		}
		
		
		
		// Outcome
		if( loss_time_injury=="" ){
			error += "Outcome Time lost due to injury is required\n";
		}
		if( loss_time_injury=="" ){
			error += "Outcome Who was the incident reported to? is required\n";
		}
		
		if(  jQuery("#confirm_chk").prop("checked")==false ){
			error += "Please tick the confirm box to proceed\n";
		}
		
		if( error!="" ){			
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});
	
	
});
</script>
<script src="/js/datetimepicker/datetimepicker_addon.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAa9QRZRQ3eucZ6OE18rSSi8a7VGJjoXQE&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
