<?
$title = "Add SATS User";

$custom_datepicker = 1;

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# process
if($_POST){ 

	
	//  staff save
	function addUser2($post_array)
	{
		# don't slash password before encryption
		# $post_array['Password'] = stripSlashesData($post_array['Password']);
			
		# encrypt password
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		
		if(UTF8_USED)
		{
			$password_encrypted = addslashes(utf8_encode($encrypt->encrypt($post_array['Password'])));
		}
		else
		{
			$password_encrypted = addslashes($encrypt->encrypt($post_array['Password']));
		}
		
		
		# geenrate MD5
		$md5 = md5(rand(1,10000));
		
		$working_days = implode(",",$post_array['working_days']);
		
		$query = "
		INSERT INTO 
		staff_accounts ( 
			ClassID, 
			Email, 
			FirstName, 
			LastName, 
			dob, 
			Password, 
			Hash, 
			ContactNumber, 
			`debit_card_num`, 
			`working_days`, 
			`sa_position`, 
			`address` 
		)
		VALUES ( 
			'" . $post_array['user_class'] . "', 
			'" . addSlashesData(stripSlashesData($post_array['Email'])) . "',
			'" . addSlashesData(stripSlashesData($post_array['FirstName'])) . "',
			'" . addSlashesData(stripSlashesData($post_array['LastName'])) . "',
			'".date("Y-m-d",strtotime(str_replace("/","-",$post_array['dob'])))."',
			'" . $password_encrypted . "',
			'" . $md5 . "',
			'" . addSlashesData(stripSlashesData($post_array['ContactNumber'])) . "',
			'{$post_array['debit_card_num']}',
			'{$working_days}',
			'".mysql_real_escape_string($post_array['sa_position'])."',
			'".mysql_real_escape_string($post_array['address'])."'
		)";
		
		if(mysql_query($query)) 
		{
			
			$StaffID = mysql_insert_id();
	
			if( $post_array['user_class'] == 6 ){ // technician
				
				// create its accomodation
				$address = $post_array['address'];
				$tech_full_name = "{$post_array['FirstName']} {$post_array['LastName']}"; 
				
				mysql_query("
					INSERT INTO 
					`accomodation`(
						`name`,
						`area`,
						`address`,
						`phone`,
						`email`,
						`rate`,
						`comment`,
						`country_id`
					)
					VALUES(
						'".mysql_real_escape_string($tech_full_name)."',
						'1 Staff',
						'".mysql_real_escape_string($address)."',
						'".mysql_real_escape_string($post_array['ContactNumber'])."',
						'".mysql_real_escape_string($post_array['Email'])."',
						'',
						'STAFF',
						".CURRENT_COUNTRY."
					)
				");
				$acco_id = mysql_insert_id();
				
				// update accomodation id on staff account
				mysql_query("
					UPDATE `staff_accounts`
					SET `accomodation_id` = {$acco_id}
					WHERE `StaffID` = {$StaffID}
				");
				
			}
			
			
			// country
			mysql_query("
				INSERT INTO
				`country_access` (
					`staff_accounts_id`,
					`country_id`,
					`default`,
					`status`
				)
				VALUES(
					{$StaffID},
					".CURRENT_COUNTRY.",
					1,
					1
				)
			");
			
			if( CURRENT_COUNTRY == 1 ){ // AU
				
				// state
				$state = $_POST["state_access"];
				foreach($state as $val){
					$query = "
						INSERT INTO 
						staff_states(
							`StaffID`, 
							`country_id`,
							`StateID`
						) 
						VALUES (
							{$StaffID},
							".CURRENT_COUNTRY.",
							{$val}
						)";
						mysql_query($query);
				}
				
			}			
			
			return 1;
			
			
		}	
		else return 0;
		
		
		
		
		 
	}
	$user_details = $_POST;
		
	# validate password
	$error_message = $user->validatePassword($_POST);
	
	#put states as same format that comes from db
	/*
	if(is_array($_POST['States']))
	{
		$tmp = array();
		for($x = 0; $x < sizeof($_POST['States']); $x++) $tmp[$x]['StateID'] = $_POST['States'][$x];
		$user_details['States'] = $tmp;
		unset($tmp);
		
	}
	else $error_message = "Must include at least one state";
	*/
	
	
	
	# validate email address
	if(!validEmail($_POST['Email'])) $error_message = "Please enter a valid email address";
	if(!$user->isEmailAvailable($_POST['Email'])) $error_message = "Email address already in use, please choose another";
	
	if(!isset($error_message))
	{
		
		if(addUser2($user_details))
		{
			$success = 1;
		}
		else
		{
			$error_message = "There was a technical problem, please try again";
		}
		//else $error_message = "There was a technical problem, please try again";
	}
	
	
	
	
}

# get allowable classes
$classes = $user->getAllowableClasses();

# get all states
$states = $user->getAllStates();
?>
  <div id="mainContent">
  <style>
	.ui-datepicker-year{
		display:none;   
	}
  </style>
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
		<?php 
			$view_user_ci_page = $crm->crm_ci_redirect("/users")
		?>
      <ul>
        <li><a title="Home" href="main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="SATS User" href="<?php echo $view_user_ci_page; ?>">SATS User</a></li>
        <li class="other first"><a title="Add SATS User" href="<?php echo $_SERVER['PHP_SELF'] ?>"><strong>Add SATS User</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

	<?php if($success){ ?>

		<div class="success">New user added successfully, <a href="<?=URL;?>view_sats_users.php">click here</a> to return to user list</div>

	<?php }else{ ?>
   
		<?php if($error_message){ ?>
		<div class="error"><?=$error_message;?></div>
		<?php } ?>


		<div class="addproperty formholder" id="div_staff">
		
			<form action="" method="post" id="form_staff">
			
				<div class="row">
				<label for="FirstName" class="addlabel">First Name <span style="color:red">*</span></label>
				<input type="text" name="FirstName" value="<?=$user_details['FirstName'];?>" class="fname" />
				</div>
				<div class="row">
				<label for="LastName" class="addlabel">Last Name <span style="color:red">*</span></label>
				<input type="text" name="LastName" value="<?=$user_details['LastName'];?>" class="lname" />
				</div>
				<div class="row">
				<label for="address" class="addlabel">Address</label>
				<input type="text" name="address" id="address" value="<?=$user_details['address'];?>" class="address" />
				</div>

				<div style="display:none;">
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
				<div class="row">
				<label class="addlabel" for="postcode">Postcode <span style="color:red">*</span></label>
				<input class="addinput" type="text" name="postcode" id="postcode" /> 
				</div>
				</div>


				<div class="row">
				<label for="dob" class="addlabel">Birthday</label>
				<input type="text" name="dob" value="<?=$user_details['dob'];?>" class="dob datepicker" />
				</div>
				<div class="row">
				<label for="debit_card_num" class="addlabel">Debit Card No.</label>
				<input type="text" name="debit_card_num" value="<?=$user_details['debit_card_num'];?>" class="debit_card_num" />
				</div>
				<div class="row">
				<label for="Email" class="addlabel">Email <span style="color:red">*</span></label>
				<input type="text" name="Email" value="<?=$user_details['Email'];?>" class="email" />
				</div>
				<div class="row">
				<label for="Password" class="addlabel">Password <span style="color:red">*</span></label>
				<input type="password" name="Password" value="<?=$user_details['Password'];?>" class="pass" />
				</div>
				<div class="row">
				<label for="Password2" class="addlabel">Retype Password <span style="color:red">*</span></label>
				<input type="password" name="Password2" value="<?=$user_details['Password2'];?>" class="pass2" />
				</div>
				<div class="row">
				<label for="ContactNumber" class="addlabel">Phone</label>
				<input type="text" name="ContactNumber" value="<?=$user_details['ContactNumber'];?>" class="ContactNumber" />
				</div>

				<div class="row">
				<label class="addlabel">Job Title</label>
				<input type="text" name="sa_position" value="" class="job_title" />
				</div>
				
				
				<div class="row">
				<label for="UserType" class="addlabel">User Class <span style="color:red">*</span></label>
				<select name="user_class" class="user_class">
				<option	value="">----</option>
				<? foreach($classes as $index=>$data): ?>		
				<option value="<?=$data['ClassID'];?>" <? if($user_details['ClassID'] == $data['ClassID']):?>selected <? endif;?> class="sats_user"><?=$data['ClassName'];?></option>
				<? endforeach;?>
				</select>
				</div>
				
				<?php				
				if( CURRENT_COUNTRY == 1 ){ // AU ?>
				
				<div class="row" id="state_access_div">
				
					<label class="addlabel state_access_lbl">State Access <span style="color:red">*</span></label>
					<div class="vsud-inner">

						<div id="state_access" class="state">
						<? 
						$au_states_sql = getStateViaCountry(CURRENT_COUNTRY);
						while( $au_states = mysql_fetch_array($au_states_sql) ){ ?>
						<input type="checkbox" id="<?=$au_states['StateID'];?>" name="state_access[]" class="states" value="<?=$au_states['StateID'];?>" <? if(stateIsChecked($user_details['States'],$au_states['StateID'])):?> checked <? endif;?>>
						<label for="<?=$au_states['StateID'];?>" class="statelabel"><?=$au_states['state'];?></label>             
						<? } ?>
						<input type="checkbox" id="staff_check_all"><label for="<?=$data['StateID'];?>" class="statelabel">All</label>
						</div>

					</div>
					
				</div>
				
				
				<?php
				}				
				?>

				<div class="row">

					<label for="country" class="addlabel">Working Days</label>
					
					<p>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Mon" />
						<span class="working_days_span">Monday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Tue" />
						<span class="working_days_span">Tuesday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Wed" />
						<span class="working_days_span">Wednesday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Thu" />
						<span class="working_days_span">Thursday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Fri" />
						<span class="working_days_span">Friday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Sat" />
						<span class="working_days_span">Saturday</span>

						<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Sun" />
						<span class="working_days_span">Sunday</span>

					</p>

				</div>

				<div class="row" style="margin-top: 30px; text-align:left;">
					<label for="country" class="addlabel">&nbsp;</label>
					<button type="button" class="submitbtnImg" id="btn_add_staff">Add Staff</button>
				</div>
				
				
			
			</form>
		
		</div>
	
	
	<?php
	}
	?>

  </div>
  
</div>

<br class="clearfloat" />

<style>
.addlabel{
	width: 133px;
}
.working_days_span{
	float: left; 
	margin: -2px 5px 0 4px;
}
</style>
<script>
// google map autocomplete
var autocomplete, autocomplete2;

// address autocomplete
function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.
  
  var options = {
	  types: ['geocode'],
	  componentRestrictions: {country: '<?php echo CountryISOName(CURRENT_COUNTRY); ?>'}
	};
  
  // there are 2 address field, bad mark up design
  autocomplete = new google.maps.places.Autocomplete(
	 (document.getElementById('address')),
	 options
	  );
	  
  autocomplete2 = new google.maps.places.Autocomplete(
	 (document.getElementById('address_tech')),
	 options
	  );

  
  
}



jQuery(document).ready(function(){	
	
	
	// check all staff state
	jQuery("#staff_check_all").click(function(){
	
		if(jQuery(this).prop("checked")==true){
			jQuery(".states").prop("checked",true);
			jQuery(this).html("cancel");
		}else{
			jQuery(".states").prop("checked",false);
			jQuery(this).html("all");
		}
	
	  
	});

	// datepicker
	jQuery(".datepicker").datepicker({ 
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: "1940:2000", // year range
		dateFormat: "dd/mm"	// date format					
	});


	// staff accounts
	jQuery("#btn_add_staff").click(function(){
		
		var fname = jQuery(this).parents("#form_staff").find(".fname").val();
		var lname = jQuery(this).parents("#form_staff").find(".lname").val();
		var email = jQuery(this).parents("#form_staff").find(".email").val();
		var pass = jQuery(this).parents("#form_staff").find(".pass").val();
		var pass2 = jQuery(this).parents("#form_staff").find(".pass2").val();
		var phone_num = jQuery(this).parents("#form_staff").find(".phone_num").val();
		var job_title = jQuery(this).parents("#form_staff").find(".job_title").val();
		var user_class = jQuery(this).parents("#form_staff").find(".user_class").val();			
		
		var states = [];
		jQuery(this).parents("#form_staff").find(".states:checked").each(function(){
			states.push(jQuery(this).val());
		});
		
		var error = "";
		
		if(fname==""){
			error += "First Name is required\n";
		}
		
		if(lname==""){
			error += "Last Name is required\n";
		}
				
		if(pass==""){
			error += "Password is required\n";
		}else{
			// pass must be minimum of 6 characters
			if(pass.length<6){
				error += "Password must be at least 6 characters\n";
			}
			// match password
			if(pass!=pass2){
				error += "Password does not match\n";
			}
		}

		/*
		if(job_title==""){
			error += "Job Title is required\n";
		}
		*/
		
		if(user_class==""){
			error += "User Class is required\n";
		}
		
		<?php
		if( CURRENT_COUNTRY == 1 ){ // AU ?>
			var sel_c_state = jQuery(".states:checked").length;
			if(states.length==0){
				error += "State Access is required\n";
			}
		<?php
		}
		?>
		
				
		
		// if email is empty
		if(email==""){
			error += "Email is required\n";
			if(error!=""){
				alert(error);
			}
		// if not empty
		}else{		
			var atpos=email.indexOf("@");
			var dotpos=email.lastIndexOf(".");
			// email is invalid
			if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length){
				error += "Email is invalid\n";
				if(error!=""){
					alert(error);
				}
			// valid email
			}else{			
				jQuery.ajax({
					type: "POST",
					url: "ajax_check_existing_email.php",
					data: { 
						email: email				
					}
				}).done(function( ret ) {	
					// email already exist
					if(ret=="1"){
						error += "Email already exist\n";
					}	
					if(error!=""){
						alert(error);
					}else{
						jQuery("form#form_staff").submit();
					}
				});					
			}				
		}		
		
	});
	
	

	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
