<?php

$custom_datepicker = 1;

$title = "User Details";
include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class;

$id = mysql_real_escape_string($_GET['id']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

function check_state($staff_id,$state,$country_id){
	$sql = "
		SELECT *
		FROM `staff_states`
		WHERE `StaffID` = {$staff_id}
		AND `StateID` = {$state}
		AND `country_id` = {$country_id}
	";
	$ss_sql = mysql_query($sql);
	if(mysql_num_rows($ss_sql)>0){
		return true;
	}else{
		return false;
	}
}

function checkCountry($staff_id,$country_id){
	$sql = "
		SELECT *
		FROM `country_access`
		WHERE `staff_accounts_id` = {$staff_id}
		AND `country_id` = {$country_id}
	";
	$ss_sql = mysql_query($sql);
	if(mysql_num_rows($ss_sql)>0){
		return true;
	}else{
		return false;
	}
}

function getStaffStates($staff_id,$country_id){
	return mysql_query("
		SELECT *
		FROM `staff_states`
		WHERE `StaffID` = {$staff_id}
		AND `country_id` = {$country_id}
	");
}

?>
<style>
.addproperty input, .addproperty select {
    width: 30%;
}
.addproperty label {
   width: 230px;
}
</style>
    
    <div id="mainContent">
      
    <div class="sats-breadcrumb">
	<?php 
		$view_user_ci_page = $crm->crm_ci_redirect("/users")
	?>
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View User" href="<?php echo $view_user_ci_page; ?>">View User</a></li>
        <li class="other second"><a title="User Details" href="/sats_users_details.php?id=<?php echo $id; ?>"><strong>User Details</strong></a></li>
      </ul>
    </div>
    
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
    <?php
	if($_GET['success']==1){ ?>
		<div class="success">New Vehicle Added</div>
	<?php
	}else if($_GET['success']==2){ ?>
		<div class="success">Update Successful</div>
	<?php
	}else if($_GET['success']==3){ ?>
		<div class="success">User log Added</div>
	<?php
	}
	?>
	
	<?php
	if($_GET['error']!=""){ 
		echo $_GET['error'];
	}
	?>
    
	<?php
	$sa_sql = mysql_query("
		SELECT *
		FROM `staff_accounts`
		WHERE `StaffID` ={$id}
	");
	$sa = mysql_fetch_array($sa_sql);
	?>
	<form action="/update_sats_user_details.php" method="post" id="frm_vehicle" style="font-size: 14px;" enctype="multipart/form-data">
    
<div class="vvd-holder addproperty">
		
       <div class="vvd-wd">       
			<div class="vvd-row left-flow">
				<h2 class="heading">Name</h2>          
				<div class="float-left">
					<label class="addlabel" for="fname">First Name</label>
					<input type="text"  class="addinput" name="fname" id="fname" value="<?php echo $sa['FirstName']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="lname">Last Name</label>
					<input type="text"  class="addinput" name="lname" id="lname" value="<?php echo $sa['LastName']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="email">Email</label>
					<input type="text"  class="addinput" name="email" id="email" value="<?php echo $sa['Email']; ?>" style="width:160px;" />
				</div>
			</div>  
		
			<?php
			$v_sql = mysql_query("
				SELECT *
				FROM `vehicles`
				WHERE `StaffID` = {$id}
				LIMIT 0,1
			");
			$v = mysql_fetch_array($v_sql);
			?>
			<div class="vvd-row">		
				<h2 class="heading"><span>Vehicle</span><span style="margin-left: 85px;">Driver's License</span></h2>          
				<div class="float-left" style="width: 145px;">					
					<label class="addlabel" for="other_idn">
						<?php
						if(mysql_num_rows($v_sql)>0){ ?>
							<a href="/view_vehicle_details.php?id=<?php echo $v['vehicles_id'] ?>"><?php echo $v['number_plate']; ?></a>
						<?php
						}else{
							echo "No vehicles assigned yet";
						}
						?>						
					</label>
				</div>
				<div class="float-left">
					<label class="addlabel" for="license_num">License No.</label>
					<input type="text"  class="addinput" name="license_num" id="license_num" value="<?php echo $sa['license_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="licence_expiry">License Expiry</label>
					<input type="text"  class="addinput datepicker" name="licence_expiry" id="licence_expiry" value="<?php echo ( $crm->isDateNotEmpty($sa['licence_expiry']) )?$crm->formatDate($sa['licence_expiry'],'d/m/Y'):''; ?>" />
				</div>
                
                
                
                
                
			</div>  
					       
        </div> 

		<div class="vvd-wd">        
			<div class="vvd-row left-flow">
				<h2 class="heading">Phone</h2> 
				<div class="float-left">
					<label class="addlabel" for="number">Number</label>
					<input type="text"  class="addinput" name="number" id="number" value="<?php echo $sa['ContactNumber']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="phone_mn">Model No.</label>
					<input type="text"  class="addinput" name="phone_mn" id="phone_mn" value="<?php echo $sa['phone_model_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="phone_sn">Serial No.</label>
					<input type="text"  class="addinput" name="phone_sn" id="phone_sn" value="<?php echo $sa['phone_serial_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="phone_mn">IMEI</label>
					<input type="text"  class="addinput" name="phone_imei" id="phone_imei" value="<?php echo $sa['phone_imei']; ?>" />
				</div>
			</div>
            
            <div class="vvd-row">
                <!-- Electricians License number and expiry -->
                <h2 class="heading">Electrical License</h2>    
                
				<div class="float-left">
					<label class="addlabel" for="license_num">Electrician</label>
					<select name="is_electrician">
						<option value="0" <?php echo ($sa['is_electrician']==0)?'selected="selected"':''; ?>>No</option>
						<option value="1" <?php echo ($sa['is_electrician']==1)?'selected="selected"':''; ?>>Yes</option>						
					</select>
				</div>
				<div class="float-left">
					<label class="addlabel" for="license_num">License No.</label>
					<input type="text"  class="addinput" name="electrician_license_num" id="electrician_license_num" value="<?php echo $sa['elec_license_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="licence_expiry">License Expiry</label>
					<input type="text"  class="addinput datepicker" name="electrician_license_expiry" id="electrician_license_expiry" value="<?php echo ( $crm->isDateNotEmpty($sa['elec_licence_expiry']) )?$crm->formatDate($sa['elec_licence_expiry'],'d/m/Y'):''; ?>" />
				</div>
            </div>
			
		</div>
		
      
		<div class="vvd-wd">        
			<div class="vvd-row left-flow">		
				<h2 class="heading">Other</h2>          
				<div class="float-left">
					<label class="addlabel" for="other_idn">ID No.</label>
					<input type="text"  class="addinput" name="other_idn" id="other_idn" value="<?php echo $id; ?>" readonly="readonly" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="other_kn">Key No.</label>
					<input type="text"  class="addinput" name="other_kn" id="other_kn" value="<?php echo $sa['other_key_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="other_pid">Plant ID</label>
					<input type="text"  class="addinput" name="other_pid" id="other_pid" value="<?php echo $sa['other_plant_id']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="other_sz">Shirt Size</label>
					<input type="text"  class="addinput" name="other_sz" id="other_sz" value="<?php echo $sa['other_shirt_size']; ?>" />
				</div>
								
			</div>
            
            <div class="vvd-row">
				<h2 class="heading">Blue Card</h2>          
				<div class="float-left">
					<label class="addlabel" for="blue_card_num">Blue Card No</label>
					<input type="text"  class="addinput" name="blue_card_num" id="blue_card_num" value="<?php echo $sa['blue_card_num']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="blue_card_expiry">Blue Card Expiry</label>
					<input type="text"  class="addinput datepicker" name="blue_card_expiry" id="blue_card_expiry" value="<?php echo ( $crm->isDateNotEmpty($sa['blue_card_expiry']) )?$crm->formatDate($sa['blue_card_expiry'],'d/m/Y'):''; ?>" />
				</div>				
			</div>    
	
			
        </div>


		<div class="vvd-wd"> 
		
			<div class="vvd-row left-flow">
				<div class="float-left">
					<h2 class="heading">Laptop</h2>          
					<div class="float-left">
						<label class="addlabel" for="laptop_make">Make</label>
						<input type="text"  class="addinput" name="laptop_make" id="laptop_make" value="<?php echo $sa['laptop_make']; ?>" />
					</div>
					<div class="float-left">
						<label class="addlabel" for="laptop_sn">Serial No.</label>
						<input type="text"  class="addinput" name="laptop_sn" id="laptop_sn" value="<?php echo $sa['laptop_serial_num']; ?>" />
					</div>
				</div>
			</div>
			
			<div class="vvd-row">
				<h2 class="heading">Dates</h2>          
				<div class="float-left">
					<label class="addlabel" for="start_date">Start Date</label>
					<input type="text"  class="addinput datepicker" name="start_date" id="start_date" value="<?php echo ($sa['start_date']!="0000-00-00")?date("d/m/Y",strtotime($sa['start_date'])):''; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="dob">Date of Birth</label>
					<input type="text"  class="addinput dob" name="dob" id="dob" value="<?php echo ($sa['dob']!="0000-00-00")?date("d/m",strtotime($sa['dob'])):''; ?>" />
					<input type="hidden"  class="addinput hid_dob" name="hid_dob" id="hid_dob" value="<?php echo ($sa['dob']!="0000-00-00")?date("d/m/Y",strtotime($sa['dob'])):''; ?>" />
				</div>
			</div>  
			
		</div>
		
		
		<div class="vvd-wd">        
			
			<div class="vvd-row left-flow">
				<h2 class="heading">iPad</h2>          
				<div class="float-left">
					<label class="addlabel" for="ipod_mn">Model No.</label>
					<input type="text"  class="addinput" name="ipad_mn" id="ipod_mn" value="<?php echo $sa['ipad_model_num']; ?>" />
				</div>			
				<div class="float-left">
					<label class="addlabel" for="ipod_sn">Serial No.</label>
					 <input type="text"  class="addinput" name="ipad_sn" id="ipod_sn" value="<?php echo $sa['ipad_serial_num']; ?>" />
				</div>			
				<div class="float-left">
					<label class="addlabel" for="ipod_imei">IMEI</label>
					 <input type="text"  class="addinput" name="ipad_imei" id="ipod_imei" value="<?php echo $sa['ipad_imei']; ?>" />
				</div>			
				<div class="float-left">
					<label class="addlabel" for="ipod_ppsn">Pre Paid Service No.</label>
					<input type="text"  class="addinput" name="ipad_ppsn" id="ipod_ppsn" value="<?php echo $sa['ipad_prepaid_serv_num']; ?>" />
				</div>  
				<div class="float-left">
					<label class="addlabel" for="ipad_expiry_date">Data Expiry Date</label>
					<input type="text"  class="addinput datepicker" name="ipad_expiry_date" id="ipad_expiry_date" value="<?php echo ( $sa['ipad_expiry_date']!="" && $sa['ipad_expiry_date']!="0000-00-00" && $sa['ipad_expiry_date']!="1970-01-01" )?date("d/m/Y",strtotime($sa['ipad_expiry_date'])):''; ?>" />
				</div>
			</div>
            
            <div class="vvd-row">
				<h2 class="heading">ICE</h2>          
				<div class="float-left">
					<label class="addlabel" for="start_date">Name</label>
					<input type="text"  class="addinput" name="ice_name" id="ice_name" value="<?php echo $sa['ice_name']; ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="dob">Phone</label>
					<input type="text"  class="addinput" name="ice_phone" id="ice_phone" value="<?php echo $sa['ice_phone']; ?>" />
				</div>
			</div> 
			
        </div> 
		
		<div class="vvd-wd">        
			
			<div class="vvd-row left-flow">
				<h2 class="heading"><span>Debit Card</span><span style="margin-left: 208px;">CRM</span></h2>          
				<div class="float-left">
					<label class="addlabel" for="debit_card_num">Debit Card No.</label>
					<input type="text"  class="addinput" name="debit_card_num" id="debit_card_num" value="<?php echo $sa['debit_card_num']; ?>" />
				</div>		
				<div class="float-left">
					<label class="addlabel" for="debit_expiry_date">Expiry Date</label>
					<input type="text"  class="addinput datepicker" name="debit_expiry_date" id="debit_expiry_date" value="<?php echo ( $sa['debit_expiry_date']!="" && $sa['debit_expiry_date']!="0000-00-00" && $sa['debit_expiry_date']!="1970-01-01" )?date("d/m/Y",strtotime($sa['debit_expiry_date'])):''; ?>" />
				</div>
				<?php
				$encrypt = new cast128();
				$encrypt->setkey(SALT);
				?>
				<div class="float-left">
					<label class="addlabel" for="pass">Password</label>
					<input type="text"  class="addinput" name="pass" id="pass" value="<?php echo $encrypt->decrypt(utf8_decode($sa['Password'])) ?>" />
				</div>
				<div class="float-left">
					<label class="addlabel" for="status">Status</label>
					<select name="status">
						<option value="1" <?php echo ($sa['active']==1)?'selected="selected"':''; ?>>Active</option>
						<option value="0" <?php echo ($sa['active']==0)?'selected="selected"':''; ?>>Inactive</option>
					</select>
				</div>
				<?php
				if( $_SESSION['USER_DETAILS']['ClassID']==2 ){ 
					$sc_sql = mysql_query("
						SELECT *
						FROM `staff_classes`
					");
				?>
					<div class="float-left">
						<label class="addlabel" for="class">Class</label>
						<select name="class" style="width: 146px;">
						<?php
						while( $sc = mysql_fetch_array($sc_sql) ){ ?>
							<option value="<?php echo $sc['ClassID']; ?>" <?php echo ($sc['ClassID']==$sa['ClassID'])?'selected="selected"':''; ?>><?php echo $sc['ClassName']; ?></option>
						<?php	
						}
						?>
						</select>
					</div>
				<?php	
				}
				?>
				
				
				<div class="float-left">
					<label class="addlabel" for="sa_position">Position</label>
					<input type="text"  class="addinput" name="sa_position" id="sa_position" value="<?php echo $sa['sa_position']; ?>" />
				</div>				
				
				<div class="float-left">
					<label class="addlabel" for="other_cc">Call Centre</label>
					<select name="other_cc" style="width: 146px;">
						<option value="">---Select---</option>
						<?php
						$jparams = array(
							'sort_list' => array(
								'order_by' => '`FirstName`',
								'sort' => 'ASC'
							),
							'custom_query' => "AND ( sa.`ClassID` = 7 OR sa.`ClassID` = 8 )"
						);
						$cc_sql = $crm->getStaffAccount($jparams);
						while($cc = mysql_fetch_array($cc_sql)){ ?>
							<option value="<?php echo $cc['StaffID'] ?>" <?php echo ($cc['StaffID']==$sa['other_call_centre'])?'selected="selected"':''; ?>><?php echo $crm->formatStaffName($cc['FirstName'],$cc['LastName']); ?></option>
						<?php	
						}
						?>
					</select>
				</div>
				
			</div>
			
			<div class="vvd-row">
			<h2 class="heading">Address</h2>          
				<div class="float-left">
					<label class="addlabel" for="address">Full Address</label>
					<input type="text" style="width:315px;" class="addinput" name="address" id="address" value="<?php echo $sa['address'] ?>" />
					<input type="hidden" name="accomodation_id" id="accomodation_id" value="<?php echo $sa['accomodation_id'] ?>" />
				</div>				
			</div>
			
			

		
			
        </div>

		
		<div class="vvd-wd">        
			
			<div class="vvd-row left-flow">
				<h2 class="heading"><span>Country Access</span></h2>          
				<div class="float-left">
				
				
					<p>
						<?php
							$c_sql = mysql_query("
								SELECT *
								FROM `countries`
							");
							while($c = mysql_fetch_array($c_sql)){ ?>
								<input style="width: auto; float: left;"  type="checkbox" name="countries[]" id="country<?php echo $c['iso']; ?>"  class="country" value="<?php echo $c['country_id']; ?>" title="<?php echo $c['country']; ?>" <?php echo (checkCountry($sa['StaffID'],$c['country_id'])==true)?'checked="checked"':''; ?> />
								<label style="float: left; margin: 0 14px 0 6px;" class="statelabel"><?php echo $c['iso']; ?></label>
							<?php	
							}
						?>
					</p>
					
					<div style="clear:both;">&nbsp;</div>
			
					<p id="c_default_div">
						<label>Default: </label>
							<select name="c_default" class="c_default" id="c_default" style="height:auto;">
							<?php
							$c_sql = getStaffCountries($sa['StaffID']);
							while( $c = mysql_fetch_array($c_sql) ){?>
								<option value="<?php echo $c['country_id']; ?>" <?php echo ( countrySelectedDefault($sa['StaffID'],$c['country_id'])==true )?'selected="selected"':''; ?>><?php echo $c['country']; ?></option>
							<?php	
							}
							?>
							</select>
					</p>
				
					<h2 class="heading" style="margin-top: 45px;"><span>State</span></h2>    
							
							<?php
							$sd_sql = getStaffStates($sa['StaffID'],1);
							?>
							<p id="state_1" <?php echo ( mysql_num_rows($sd_sql)>0 )?'':'style="display:none"'; ?>>
							<?php
								# get AU states
								$states = mysql_query("
									SELECT *
									FROM `states_def`
									WHERE `country_id` = 1
								");
								if( mysql_num_rows($states)>0 ){ ?>
								<label>AU: </label>
								<?php
								while($s = mysql_fetch_array($states) ){ ?>	
					
								<input style="width: auto; float: left;" type="checkbox" id="<?=$s['StateID'];?>" name="state_1[]" class="states" value="<?=$s['StateID'];?>" <?php echo (check_state($sa['StaffID'],$s['StateID'],1)==true)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;"><?=$s['state'];?></span>
											
								<? }
								}
								?>	
							</p>
						
					
						
						<div style="clear:both;">&nbsp;</div>
											
							<?php
							$sd_sql = getStaffStates($sa['StaffID'],3);
							?>
							<p id="state_3" <?php echo ( mysql_num_rows($sd_sql)>0 )?'':'style="display:none"'; ?>>	
							<?php
								# get CA states
								$states = mysql_query("
									SELECT *
									FROM `states_def`
									WHERE `country_id` = 3
								");
								if( mysql_num_rows($states)>0 ){ ?>
								<label>CA: </label>
								<select name="state_3[]" class="states" style="height:auto;" multiple>
								<?php
								while($s = mysql_fetch_array($states) ){ ?>			 
									<option value="<?php echo $s['StateID']; ?>" <?php echo (check_state($sa['StaffID'],$s['StateID'],3)==true)?'selected="selected"':''; ?>><?php echo $s['state_full_name']; ?></option>       
								<? }
								}
								?>
								</select>
							</p>
						
					

					
							<?php
							$sd_sql = getStaffStates($sa['StaffID'],5);
							?>
							<p id="state_5" <?php echo ( mysql_num_rows($sd_sql)>0 )?'':'style="display:none"'; ?>>						
							<?php
								# get US states
								$states = mysql_query("
									SELECT *
									FROM `states_def`
									WHERE `country_id` = 5
								");
								if( mysql_num_rows($states)>0 ){ ?>
								<label>US: </label>
								<select name="state_5[]" class="states" style="height:auto;" multiple>
								<?php
								while($s = mysql_fetch_array($states) ){ ?>			 
									<option value="<?php echo $s['StateID']; ?>" <?php echo (check_state($sa['StaffID'],$s['StateID'],5)==true)?'selected="selected"':''; ?>><?php echo $s['state_full_name']; ?></option>       
								<? }
								}
								?>
								</select>
							</p>
							
							
							<h2 class="heading" style="margin-top: 45px;"><span>Working Days</span></h2>
							<p>
							
					
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Mon" <?php echo (strchr($sa['working_days'],'Mon')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Monday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Tue" <?php echo (strchr($sa['working_days'],'Tue')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Tuesday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Wed" <?php echo (strchr($sa['working_days'],'Wed')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Wednesday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Thu" <?php echo (strchr($sa['working_days'],'Thu')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Thursday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Fri" <?php echo (strchr($sa['working_days'],'Fri')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Friday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Sat" <?php echo (strchr($sa['working_days'],'Sat')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Saturday</span>
								
								<input style="width: auto; float: left;" type="checkbox" name="working_days[]" class="working_days" value="Sun" <?php echo (strchr($sa['working_days'],'Sun')!=false)?'checked="checked"':''; ?> />
								<span style="float: left; margin: 0 14px 0 6px;">Sunday</span>
								
							</p>
						
					
				</div>					
			</div>
			
			
			
			<div class="vvd-row">
			<h2 class="heading">Photo</h2>          
				<div class="float-left">
					<label class="addlabel" for="address">&nbsp;</label>
					<?php 
					if($sa['profile_pic']!=''){ ?>
						<a id="profilePic" class="single_fancybox" href="/images/staff_profile/<?php echo $sa['profile_pic']; ?>" > <img style="max-height:185px;" src="/images/staff_profile/<?php echo $sa['profile_pic']; ?>" /></a>
					<?php	
					} 
					?>					
					<input type="file" name="profile_pic" style="width: 200px;" />
				</div>				
			</div>
			
			
			
			<div class="vvd-row">
			<h2 class="heading">Electrical License</h2>          
				<div class="float-left">
					<label class="addlabel" for="address">&nbsp;</label>
					<?php 
                     
                    $pathInfo =  pathinfo($sa['electrical_license']);
                    $pathExtension = $pathInfo['extension'];
                    
					if($sa['electrical_license']!=''){ 
                        if($pathExtension=="pdf"){
                    ?>
				             <a style="background:#b4151b;padding:5px 10px;display:inline-block;margin-bottom:5px;" href="/images/electrical_license/<?php echo $sa['electrical_license']; ?>" class="single_fancybox_iframe"><img style="height:35px;" src="/images/button_icons/pdf_white.png"></a>
                       <?php  }else{ ?>
                            <a  href="/images/electrical_license/<?php echo $sa['electrical_license']; ?>" class="single_fancybox"><img style="max-height:185px;" src="/images/electrical_license/<?php echo $sa['electrical_license']; ?>" /></a>
                        
					<?php
                        }
					} 
					?>					
					<input type="file" name="electrical_license" style="width: 200px;" />
				</div>				
			</div>

			
			<div class="vvd-row">    
				<h2 class="heading"></h2>    
				<div class="float-left">
					<input style="width: auto; float: left;" type="checkbox" name="display_on_wsr" class="display_on_wsr" value="1" <?php echo  ( $sa['display_on_wsr'] == 1 )?'checked="checked"':''; ?> />
					<span style="float: left; margin: 0 14px 0 6px;">Display on Weekly Sales Report</span>
				</div>				
			</div>

			<div class="vvd-row">    
				<h2 class="heading"></h2>    
				<div class="float-left">
					<input style="width: auto; float: left;" type="checkbox" name="recieve_wsr" class="recieve_wsr" value="1" <?php echo  ( $sa['recieve_wsr'] == 1 )?'checked="checked"':''; ?> />
					<span style="float: left; margin: 0 14px 0 6px;">Receive Weekly Sales Report</span>
				</div>				
			</div>
			
		</div>
		


          
		 
		<div class="vvd-wd">

			<div class="vvd-row">        
				<div class="float-left">
					<input type="hidden" name="user_id" value="<?php echo $id; ?>">
					<button class="submitbtnImg" id="btn_update_vehicle" type="button" style="float: left; margin-top: 15px;">
						<img class="inner_icon" src="images/save-button.png">
						Update
					</button>
				</div>
			</div> 
			         
        </div>             
          
      </div>
      
	</form>




	<form action="add_sats_user_log.php" id="frm_vehicle_log" name="frm_vehicle_log" method="post">
		<table cellpadding="5" border="0" class="all-table tbl-fr-red view-property-table-inner">
			<tbody>
				<tr class="padding-none border-none" bgcolor="#ECECEC" style="border: 1px solid #CCCCCC !important;">
					<td style="width: 100px;">
						<label class="vpr-adev" for="eventdate">Date</label>
						<input type="text" style="width: 80px;" class="addinput vpr-adev-in datepicker" id="user_log_date" name="user_log_date" value="<?php echo date("d/m/Y"); ?>" />
					</td>
					<td>
						<label class="vpr-adev">Details</label>
						<input class="addinput vpr-adev-txt" style="width: 97%;" id="user_log_details" name="user_log_details"></input>
					</td>
					
					<td width="25%">
						<input type="hidden" value="Add Event" name="add_event">
						<button class="submitbtnImg vpr-adev-btn" id="btn_vehiclelog" type="button">Add Log</button>
						<input type="hidden" name="user_id" value="<?php echo $id; ?>" />
						<input type="hidden" name="logged_user" value="<?php echo $logged_user; ?>" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<table cellpadding="5" border="0" class="vvd-odd-cl all-table tbl-fr-red view-property-table-inner">
			<tbody>
				<tr bgcolor="#b4151b" class="redrow-v padding-none" style="border: 1px solid #b4151b !important;">
					<td class="colorwhite bold">Date</td>
					<td class="colorwhite bold">Who</td>
					<td class="colorwhite bold">Details</td>
					<td class="colorwhite bold">Delete</td>
				</tr>
				<?php
				
				
				$logs_sql = mysql_query("
					SELECT *, who.`FirstName` AS who_fname, who.`LastName` AS who_lname
					FROM `user_log` AS ul
					LEFT JOIN `staff_accounts` AS who ON ul.`added_by` = who.`StaffID`
					WHERE ul.`staff_id` = {$id}
					ORDER BY ul.`date` DESC
				");
				
				
				
				if(mysql_num_rows($logs_sql)>0){ 
					while($logs=mysql_fetch_array($logs_sql)){ ?>
					
					
						<tr>
							<td><?php echo date("d/m/Y",strtotime($logs['date'])) ?></td>
							<td><?php echo $crm->formatStaffName($logs['who_fname'],$logs['who_lname']); ?></td>
							<td><?php echo $logs['details'] ?></td>
							<td>
								<a class="btn_del_logs" href="javascript:void(0);">Delete</a>
								<input type="hidden" class="user_log_id" value="<?php echo $logs['user_log_id'] ?>" />
							</td>
						</tr>
					<?php
					}
				}else{ ?>
					<tr><td colspan="5" style="text-align:left">No Entries</td></tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</form>
			
<script>

$(document) .ready(function() {
     $("table.vvd-odd-cl tr:even").addClass("vvtbl-odd");
});

</script>
			
		
		


    
  </div>

<br class="clearfloat" />


<script>


// google map autocomplete
var autocomplete;

// address autocomplete
function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.
  
  var options = {
	  types: ['geocode'],
	  componentRestrictions: {country: '<?php echo CountryISOName($_SESSION['country_default']); ?>'}
	};
  
  // there are 2 address field, bad mark up design
  autocomplete = new google.maps.places.Autocomplete(
	 (document.getElementById('address')),
	 options
	  );
  
  
}


jQuery(document).ready(function(){
	
	jQuery("#dob").change(function(){
		
		jQuery("#hid_dob").val(jQuery(this).val());
		
	});
	
	jQuery(".country").click(function(){

		var obj = jQuery(this);
		var country_id = obj.val();
		var iso = obj.attr("title");
	
		if(obj.prop("checked")==true){
			jQuery(".state_access_lbl").show();
			jQuery("#state_"+country_id).show();
			jQuery("#c_default_div").show();
			jQuery("#c_default").append('<option value="'+country_id+'">'+iso+'</option>');
		}else{
			var num_country = jQuery(".country:checked").length;
			if(num_country==0){
				jQuery("#c_default_div").hide();
				jQuery("#state_access_div").hide();
			}
			jQuery("#state_"+country_id).hide();
		}	
		
	});


	jQuery("#btn_add_vf").toggle(function(){
		jQuery(this).html("Cancel");
		jQuery("#upload_vf_div").show();
	},function(){
		jQuery(this).html("Add Vehicle Files");
		jQuery("#upload_vf_div").hide();
	});

	// delete logs
	jQuery(".btn_del_vf").click(function(){
	
		var vehicle_files_id = jQuery(this).parents("tr:first").find(".vehicle_files_id").val();
		var vf_path = jQuery(this).parents("tr:first").find(".vf_path").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_vehicle_files.php",
				data: { 
					vehicle_files_id: vehicle_files_id,
					vf_path: vf_path
				}
			}).done(function( ret ){
				window.location = "/view_vehicle_details.php?id=<?php echo $v['vehicles_id']; ?>";
			});	
		}
	});
	

	// delete logs
	jQuery(".btn_del_logs").click(function(){
	
		var user_log_id = jQuery(this).parents("tr:first").find(".user_log_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_sats_user_log.php",
				data: { 
					user_log_id: user_log_id
				}
			}).done(function( ret ){
				window.location = "/sats_users_details.php?id=<?php echo $id; ?>";
			});	
		}
	});

	// add vehicle logs
	jQuery("#btn_vehiclelog").click(function(){
	
		var log_date = jQuery("#user_log_date").val();
		var log_details = jQuery("#user_log_details").val();
		var error = "";
		
		if(log_date==""){
			error += "User Log date date is required\n";
		}
		if(log_details==""){
			error += "User Log details is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_vehicle_log").submit();
		}
	
	});


	// update vehicle
	jQuery("#btn_update_vehicle").click(function(){
	
		var rego_expires = jQuery("#rego_expires").val();
		var staff_id = jQuery("#staff_id").val();
		var error = "";
		
		if(rego_expires==""){
			error += "Rego expiry date is required\n";
		}
		if(staff_id==""){
			error += "SATS user is required\n";
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#frm_vehicle").submit();
		}
		
	});
	
	

	var date = new Date();
	var start_range = date.getFullYear()-70; 
	var date = new Date();
	var end_range = date.getFullYear()-15;
	
	var year_range = start_range+':'+end_range;
	console.log("YEAR RANGE: "+year_range);


	var hid_dob = jQuery("#hid_dob").val();
	jQuery(".dob").datepicker({ 
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: year_range, // year range
		dateFormat: "dd/mm/yy",	// date format
		defaultDate: hid_dob
	});	

	var hid_dob = jQuery("#start_date").val();
	jQuery("#start_date").datepicker({ 
		changeMonth: true, // enable month selection
		changeYear: true, // enable year selection
		yearRange: year_range, // year range
		dateFormat: "dd/mm/yy",	// date format
		defaultDate: hid_dob
	});	
		

	// invoke datepicker
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
    
    
    // Profile Pic fancybox
    jQuery('.single_fancybox').fancybox();
    
    // For PDF fancybox
    jQuery('.single_fancybox_iframe').fancybox({
        'type': 'iframe',
         width  : 800,
         height : 1000
    });
    
	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
