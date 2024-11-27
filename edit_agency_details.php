<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);




$title = "Edit Agency Details";

include ('inc/init.php');
include ('inc/header_html.php');
include ('inc/menu.php');

// init the variables

$agency_id = $_GET['id'];
$success = ($_GET['success'] == 1 ? 1 : 0);
//$update_alarm = ($_GET['updatealarm'] == 1 ? 1 : 0);
// added
$add_agency_ty_msg = $_GET['add_agency_ty_msg'];


$alarm_prices = Agency::getAlarmPrices($agency_id);

include('inc/agency_class.php');
$agency = new Agency_Class();

?>
<style>
.pm_name{
	display:inline;
	float:left !important;
	width: 250px !important;
}
.btn_remove, .btn_delete{
	display:inline;
	float:left !important;
	margin-left: 10px;
	margin-top:0px;
}
#pm_main_div{
	display:none;
}
#pm_table input{
	width: 172px !important;
}
#load-screen {
	width: 100%;
	height: 100%;
	background: url("/images/loading.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	display:none;
	z-index: 9999999999;
}
.tdc_name{
	float: left !important;
    margin-right: 12px !important;
    width: 300px !important;
}
.eagdt-rd-fld {
    width: 314px;
}
</style>
<div id="load-screen"></div>
<div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Agencies" href="<?=URL;?>view_agencies.php">View Agencies</a></li>
        <li class="other second"><a title="Edit Agency Details" href="/edit_agency_details.php?id=<?php echo $_REQUEST['id']; ?>"><strong>Edit Agency Details</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
	
	<div class="addproperty">
	<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$agency_id}"); ?>
	<?php
		if($success){ ?>
			<div class="success">Agency Details Updated. (<a href="/view_agencies.php" >View Agencies</a>)  (<a href="/view_target_agencies.php" >View Target Agencies</a>) (<a href="<?php echo $ci_link; ?>" >View Agency Details</a>) (<a href="/add_agency_static.php" >Add Agency</a>)</div>
		<?php }else if($add_agency_ty_msg==1){ ?>
			<div class="success">New Agency Created. (<a href="/view_agencies.php" >View Agencies</a>)  (<a href="/view_target_agencies.php" >View Target Agencies</a>) (<a href="<?php echo $ci_link; ?>" >View Agency Details</a>) (<a href="/add_agency_static.php" >Add Agency</a>)</div>
		<?php
		}
		?>
	<form action='update_agency.php?id=<?php echo $agency_id; ?>&doaction=<?php echo $doaction;?>' method='post' id="jform">

		<?php

		$doaction = "";

		$i = 0;

		$a = array();

		$a[0] = "";

		$a[1] = "";

		$a[2] = "";

		$a[3] = "";

		// (1) Open the database connection and use the winestore

		// (2) Run the query

		$insertQuery = "SELECT 					 
						  a.agency_name,
						  a.address_1,
						  a.address_2,
						  a.address_3,
						  a.state,
						  a.postcode,						
						  a.login_id,
						  a.password,
						  a.tot_properties,
						  a.phone,					
						  a.send_emails,
						  a.account_emails,
						  a.agency_emails,
						  a.custom_alarm_pricing,
						  a.send_combined_invoice,
						  ar.agency_region_name,
						  a.agency_region_id,
						  a.salesrep,
                          s.FirstName,
                          s.LastName,
                          a.status,
						  a.send_entry_notice,
						  a.agency_id,
						  a.contact_first_name ,
						  a.contact_last_name,
						  a.contact_phone ,
						  a.contact_email,
						  a.pass_timestamp,
						  a.require_work_order,
						  a.tot_prop_timestamp,
						  a.`allow_indiv_pm`,
						  a.`franchise_groups_id`,
						  a.`agency_using_id`,
						  a.`legal_name`,
						  a.`country_id`,
						  a.`comment`,
						  a.`auto_renew`,
						  a.`agency_hours`,
						  a.`key_allowed`,
						  a.`key_email_req`,
						  a.`phone_call_req`,
						  pr.`postcode_region_id`,
						  a.`abn`,
						  a.`accounts_name`,
						  a.`accounts_phone`,
						  a.`allow_dk`,
						  pr.`postcode_region_name`,
						  a.`website`,
						  a.`allow_en`,
						  a.`agency_specific_notes`,
						  a.`team_meeting`,
						  a.`new_job_email_to_agent`,
						  a.`tenant_details_contact_name`,
						  a.`tenant_details_contact_phone`,
						  a.`display_bpay`
						FROM
						  agency a
						  LEFT JOIN agency_regions ar USING (agency_region_id)
						  LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
						  LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID)
						WHERE (agency_id='" . $agency_id . "');";
		

		if (($result = mysql_query($insertQuery, $connection)) && @ mysql_num_rows() == 0)
			;
		
else

			echo "<h3>No Agency Details Returned, that's odd.</h3><br>";

		$odd = 0;

		// (3) While there are still rows in the result set,

		// fetch the current row into the array $row

		while ($row = mysql_fetch_array($result)) {

			$odd++;

			if (is_odd($odd)) {

				echo "<tr bgcolor=#FFFF99>";

			} else {

				echo "<tr bgcolor=lightblue>";

			}

			
			
			$active = '';
			$target = '';
			if($row['status'] == 'active')  $active = 'selected="selected"';
			if($row['status'] == 'target')  $target = 'selected="selected"';

			echo "\n";

			// (4) Print out each element in $row, that is,

			// print the values of the attributes

			
			echo "<div class='row'>";
			echo "<label class='addlabel' for='agency_name'>Agency Name</label>\n<input class='addinput' type=text name='agency_name' id='agency_name' title='Agency Name' value=\"".$row[0]."\"><input type='hidden' name='agency_name_edited' id='agency_name_edited' value='0' />";
			//echo "<div class='eagd-fl'><span style='color:red'>*</span>If you edit the Agency Name please click <a href='mailto:accounts@sats.com.au?subject=Please update Agency Name in MYOB for $row[0]'>Here</a> to notify the Accounts Department</div>";
			echo "</div>";
			

			if($row['status']=='active'){ 
			
			?>
			
				<div class="row">
					<label class="addlabel" for="postcode">Legal Name</label>			
					<input class="addinput" type="text" name="legal_name" id="legal_name" title="Legal Name" value="<?php echo $row['legal_name']; ?>" />
				</div>
			
			<?php
			}		
			?>
            
            
			<div class="row">
				<label for="franchise_group" class="addlabel">Franchise Group</label>
				<select id="franchise_group" name="franchise_group" class="addinput" title="Franchise Group">
					<option value="">----</option>
					<?php
					$fg_sql = mysql_query("
						SELECT *
						FROM `franchise_groups`	
						WHERE `country_id` = {$_SESSION['country_default']}
						ORDER BY `name`
					");
					while($fg = mysql_fetch_array($fg_sql)){ ?>
						<option value="<?php echo $fg['franchise_groups_id'] ?>" <?php echo ($row['franchise_groups_id']==$fg['franchise_groups_id'])?'selected="selected"':''; ?>><?php echo $fg['name'] ?></option>
					<?php
					}
					?>
				</select>
			</div>
			
			<div class="row">
				  <label class="addlabel" for="abn"><?php echo $_SESSION['country_default']==1?'ABN Number':'GST Number'; ?></label>
				  <input class="addinput" type="text" name="abn" id="abn" value="<?php echo $row['abn']; ?>" />
				 </div>
			
			<div class="row">
			  <label class="addlabel" for="fullAdd">Address</label>
			  <input class="addinput" type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" value="<?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>" />
			 </div>
			
			<?php
	
			echo "<div class='row'>";
			echo "<label class='addlabel' for='street_number'>Street Number</label>\n<input class='addinput' type=text name='street_number' id='street_number' title='Street Number' value='{$row['address_1']}'>";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel' for='street_name'>Street Name</label>\n<input class='addinput' type=text name='street_name' id='street_name' title='Street Name' value='{$row['address_2']}'>";
			echo "</div>";
			
			echo "<div class='row'>";	
			echo "<label class='addlabel' for='suburb'>Suburb</label>\n<input class='addinput' type=text name='suburb' id='suburb' title='Suburb' value='{$row['address_3']}'>";
			echo "</div>";

			if(ifCountryHasState($_SESSION['country_default'])==true){					 
			
			echo "<div class='row'>";
			echo "<label class='addlabel' for='state'>State</label>\n";
			?>
			
			<select title="state" class="addinput" id="state" name="state" title="State">
			  <option value="">----</option>
			   <?php
			  $state_sql = getCountryState();
			  while($state = mysql_fetch_array($state_sql)){ ?>
				<option value='<?php echo $state['state']; ?>' <?php echo ($state['state']==$row['state'])?'selected="selected"':''; ?>><?php echo $state['state_full_name']; ?></option>
			  <?php	  
			  }
			  ?>
			</select>
							
			<?php	
			echo "</div>";
			
			 }else{ ?>
			 
			 <div class='row'>
				<label for='state' class='addlabel'>Region</label>
				<input type='text' name='state' id='state' value='<?php echo $row['state']; ?>' class='addinput' />
				</div>
			 
			<?php	 
			 }
			 
			 ?>
			 
			 <div class="row"> 
			  <label class="addlabel" for="postcode">Postcode <span style="color:red">*</span></label>
			  <input class="addinput" type="text" name="postcode" id="postcode" title="Postcode" value="<?php echo $row['postcode']; ?>" />
			</div>
			 
			 <?php
			 
			 echo "<div class='row'>";
			echo "<label class='addlabel' for='phone'>Landline</label>\n<input class='addinput' title='Phone' type=text name='phone' value='$row[phone]'>";
			echo "</div>";
			 
			 ?>
			 
			 <!--
			 <div class="row">
			  <label class='addlabel' for='region_id'><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?> <span style="color:red">*</span></label>
			  <input class="addinput" type="text" name="postcode_region_name" id="postcode_region_name" value="<?php echo $row['postcode_region_name']; ?>" readonly="readonly" />
				<input class="addinput" type="hidden" name="region_id" id="region_id" value="<?php echo $row['postcode_region_id']; ?>" />
			  
          </div>-->
			 
			 <?php
			
			/*
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='postcode'>Postcode</label>\n<input class='addinput' type=text name='postcode' value='$row[5]'>";
			echo "</div>";
			*/
			
			echo "<div class='row'>";
			echo "<div class='eagd-fl'>
			<label class='addlabel' for='totproperties'>Total Properties </label>\n <input class='addinput' type=text name='tot_properties' id='tot_properties' title='Total Properties' value={$row['tot_properties']} onkeypress='return numbersonly(event)'>
			</div>";
			echo '<input type="hidden" name="tot_prop_change" id="tot_prop_change" value="0">';
			
			
	
			
			$timestamp = ($row['tot_prop_timestamp']!='0000-00-00 00:00:00')?'Last Updated '.date('d/m/Y',strtotime($row['tot_prop_timestamp'])):'';
			echo "<div class='eagd-fl' style='margin-top: -23px; color: #00D1E5;'><span>$timestamp</span></div>";
			echo "</div>";
			
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='agency_hours'>Agency Hours</label>\n<input class='addinput' type=text name='agency_hours' title='Agency Hours' value='{$row['agency_hours']}'>";
			echo "</div>";
			
			
			
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='comment'>Comments</label>\n<input class='addinput' type=text name='comment' title='Comments' value='{$row['comment']}'>";
			echo "</div>";
			
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='agency_specific_notes'>Agency Specific Notes</label>\n<input class='addinput' type=text name='agency_specific_notes' title='agency_specific_notes' value='{$row['agency_specific_notes']}'>";
			echo "</div>";
			
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='team_meeting'>Team Meeting</label>\n<input class='addinput' type=text name='team_meeting' title='team_meeting' value='{$row['team_meeting']}'>";
			echo "</div>";
			
			echo "<div class='row'>";			
			echo "<label class='addlabel' for='website'>Website</label>\n<input class='addinput' type=text name='website' title='Website' value='{$row['website']}'>";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel' for='postcode'>Username</label>\n<input class='addinput' type=text name='user' title='Username' value='".$row['login_id']."' />";
			echo "</div>";
			
			$encrypt = new cast128();
			$encrypt->setkey(SALT);			
			
			echo "<div class='row'>";
			echo "<div class='eagd-fl'>";
			echo "<label class='addlabel' for='postcode'>Password</label>\n<input class='addinput' type=text name='pass' id='pass' title='Password' value='".(($row['password']!="")?addslashes($encrypt->decrypt(utf8_decode($row['password']))):'')."' />";
			echo "</div>";
			echo '<input type="hidden" name="pass_change" id="pass_change" value="0" />';
			
			
			$timestamp = ($row['pass_timestamp']!='0000-00-00 00:00:00')?'Last Updated '.date('d/m/Y',strtotime($row['pass_timestamp'])):'';
			echo "<div class='eagd-fl' style='margin-top: -23px; color: #00D1E5;'><span>$timestamp</span></div>";
			
			echo "</div>";	
			?>
			
			
			
			<?php
			if( $row['status']=='target' || $row['status']=='deactivated' ){ ?>
			
				<div class='row'>
					<label class='addlabel' for='postcode'>Currently Using</label> 
					<select name="agency_using" class="agency_using" title="Currently Using">
						<option value="">----</option>
						<?php 
						$au_sql = getAgencyUsingByCountry();
						while($au = mysql_fetch_array($au_sql)){ ?>
							<option value="<?php echo $au['agency_using_id']; ?>" <?php echo($row['agency_using_id']==$au['agency_using_id'])?'selected="selected"':''; ?>><?php echo $au['name']; ?></option>
						<?php
						}
						?>
					</select>
				</div>
			
			<?php
			}

			echo "<div class='row'>";
			echo "<label class='addlabel' for='status'>Agency Status</label> 
			
			<select class='addinput2' id='status' name='status' title='Agency Status'>
				<option ".(($row['status']=='active')?'selected="selected"':'')." value='active'>Active</option>
				<option ".(($row['status']=='target')?'selected="selected"':'')." value='target'>Target</option>
				<option ".(($row['status']=='deactivated')?'selected="selected"':'')." value='deactivated'>Deactivated</option>
			</select>";
			
			echo "</div>";
			
			
			// added
			echo '<h2 class="heading">Agency Contact</h2>';
			echo "<div class='row'>";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>First Name</label>\n<input name='ac_fname' class='addinput' title='Agency Contact First Name' type=text value='".$row['contact_first_name']."' />";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Last Name</label>\n<input name='ac_lname' class='addinput' title='Agency Contact Last Name' type=text value='".$row['contact_last_name']."' />";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Landline</label>\n<input name='ac_phone' class='addinput' title='Agency Contact Phone' type=text value='".$row['contact_phone']."' />";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Email</label>\n<input name='ac_email'  class='addinput' title='Agency Contact Email' type=text value='".$row['contact_email']."' />";
			echo "</div>";
			
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Accounts Name</label>\n<input name='acc_name' class='addinput' title='Accounts Name' type=text value='".$row['accounts_name']."' />";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Accounts Phone</label>\n<input name='acc_phone'  class='addinput' title='Accounts Phone' type=text value='".$row['accounts_phone']."' />";
			echo "</div>";
			
			
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Tenant Details Contact Name</label>\n<input name='tdc_name' class='addinput' title='Accounts Name' type=text value='".$row['tenant_details_contact_name']."' />";
			echo "</div>";
			
			echo "<div class='row'>";
			echo "<label class='addlabel'>Tenant Details Contact Phone</label>\n<input name='tdc_phone'  class='addinput' title='Accounts Phone' type=text value='".$row['tenant_details_contact_phone']."' />";
			echo "</div>";
			
			
			/*
			echo "<p style='margin: 0 0 0 10px; clear: both;'>Landlord letters </p>";

			echo "<div style='height: 100px; width: 350px; margin-left: 10px; margin-bottom: 15px; border: 1px solid #a0a0a0'><br>";

			echo "<label class='addlabel2' for='checkbox2'>Letter 1</label>\n<input class='' type=checkbox name='letter1' value='1' {$a[0]}><br>";

			echo "<label class='addlabel2' for='checkbox3'>Letter 2 </label>\n<input class='' type=checkbox name='letter2' value='2' {$a[1]}><br>";

			echo "<label class='addlabel2' for='checkbox4'>Letter 3</label>\n<input class='' type=checkbox name='letter3' value='3' {$a[2]}><br>";

			echo "<label class='addlabel2' for='checkbox5'>No Letter</label>\n<input class='' type=checkbox name='noletter' value='4' {$a[3]}></div>";
			*/

			// write "checked" to above if database value is 1 otherwise empty string

			// Agency emails
			
			echo '<h2 class="heading">Agency Emails</h2>';
			echo "<div class='row'>";
			echo "<label class='addlabel' for='agency_emails'>
			Agency Emails <strong>(Reports, Key Sheet)</strong>
			<br />(one per line) <span style='color:red'>*</span>
			</label>
			<textarea name='agency_emails' id='agency_emails' class='addtextarea formtextarea' title='Agency Emails'>" . $row['agency_emails'] . "</textarea>
			<input type='hidden' name='agency_emails_edited' id='agency_emails_edited' value='0' />
			<div style='clear:both;'>&nbsp;</div>";
			echo "</div>";
			
			if($row['status']=='active'){			
			?>
			<div class="active_div">			
			<?php	
			// Accounts emails
			echo "<div class='row' style='margin-bottom: -15px;'>";
			echo "<label class='addlabel' for='account_emails'>
			Accounts Emails <strong>(Invoices, Certificates)</strong> <br />
			(one per line) <span style='color:red'>*</span>
			</label>
			<textarea name='account_emails' id='account_emails' class='addtextarea formtextarea' title='Accounts Emails'>" . $row['account_emails'] . "</textarea>
			<input type='hidden' name='account_emails_edited' id='account_emails_edited' value='0' />
			<div style='clear:both;'>&nbsp;</div>";
			echo "</div>";
			
			// Accounts email toggle
			
			echo '<h2 class="heading">Preferences</h2>';
			
			echo "<div class='row edadt'>";
			echo "<div class='float-left eagdt-rd-fld'>Email Invoices and Certificates?</div>";
			
			echo "<div class='eagdt-rd-h'>";
			echo "<div class='eagdt-rd'><label for='send_emails_1'><input type='radio' class='addinput' title='Send Account Emails to Agency' name='send_emails' value='1' " . ($row['send_emails'] == 1 ? 'checked' : '') . "> Yes</label></div>\n";
			echo "<div class='eagdt-rd'><label for='send_emails_0'><input type='radio' class='addinput' title='Send Account Emails to Agency' name='send_emails' value='0' " . ($row['send_emails'] == 0 ? 'checked' : '') . "> No</label></div>\n";
			echo "</div>";
			echo "</div>";
			
			
			// Send Combined or Seperate invoices
			echo "<div class='row edadt'>";
			echo "<div class='float-left eagdt-rd-fld'>Send Combined Certificate and Invoice</div>";
			echo "<div class='eagdt-rd-h'>";
			echo "<div class='eagdt-rd'><label for='send_emails_1'><input type='radio' class='addinput' title='Combined Invoice / Cert PDF' name='send_combined_invoice' value='1' " . ($row['send_combined_invoice'] == 1 ? 'checked' : '') . "> Yes</label></div>\n";
			echo "<div class='eagdt-rd'><label for='send_emails_0'><input type='radio' class='addinput' title='Combined Invoice / Cert PDF' name='send_combined_invoice' value='0' " . ($row['send_combined_invoice'] == 0 ? 'checked' : '') . "> No</label></div>\n";
			echo "</div>";
			echo "</div>";
			
			
			// Send Entry Notice
			echo "<div class='row edadt'>";
			echo "<div class='float-left eagdt-rd-fld'>Entry Notice issued by Email</div>";
			echo "<div class='eagdt-rd-h'>";
			echo "<div class='eagdt-rd'><label for='send_emails_1'><input type='radio' class='addinput' title='Send Entry Notice Email' name='send_entry_notice' value='1' " . ($row['send_entry_notice'] == 1 ? 'checked' : '') . "> Yes</label></div>\n";
			echo "<div class='eagdt-rd'><label for='send_emails_0'><input type='radio' class='addinput' title='Send Entry Notice Email' name='send_entry_notice' value='0' " . ($row['send_entry_notice'] == 0 ? 'checked' : '') . "> No</label></div>\n";
			echo "</div>";
			echo "</div>";

			// added
			
			echo "<div class='row edadt'>";
			echo "<div class='float-left eagdt-rd-fld'>Work Order Required For All Jobs?</div>";
			echo "<div class='eagdt-rd-h'>";
			echo "<div class='eagdt-rd'><label for='send_emails_1'><input type='radio' class='addinput' title='Work Order Required For All Jobs' name='work_order_required' value='1' ".(($row['require_work_order'] == 1) ? 'checked="checked"' : '')." /> Yes</label></div>\n";
			echo "<div class='eagdt-rd'><label for='send_emails_0'><input type='radio' class='addinput' title='Work Order Required For All Jobs' name='work_order_required' value='0' ".(($row['require_work_order'] == 0) ? 'checked="checked"' : '')." /> No</label></div>\n";
			echo "</div>";
			echo "</div>";
			
			?>
			
            
            <div class="row edadt">
				<div class="float-left eagdt-rd-fld">Allow Individual Property Managers?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="send_emails_1"><input type="radio" class='addinput' title='Allow Individual Property Managers' value="1" id="pm_check_y" name="allow_indiv_pm" <?php echo ($row['allow_indiv_pm']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="send_emails_0"><input type="radio" class='addinput' title='Allow Individual Property Managers' value="0" id="pm_check_n" name="allow_indiv_pm" <?php echo ($row['allow_indiv_pm']==0)?'checked="checked"':''; ?>> No</label></div>
				</div>
            </div>
			
			
			 <div class="row edadt">
				<div class="float-left eagdt-rd-fld">Auto Renew Yearly Maintenance Properties</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="auto_renew_1"><input type="radio" class='addinput' title='Auto Renew' value="1" id="auto_renew_yes" name="auto_renew" <?php echo ($row['auto_renew']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="auto_renew_0"><input type="radio" class='addinput' title='Auto Renew' value="0" id="auto_renew_no" name="auto_renew" <?php echo ($row['auto_renew']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Key Access Allowed?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="auto_renew_1"><input type="radio" class='addinput' title='Key Access Allowed' value="1" id="key_allowed_yes" name="key_allowed" <?php echo ($row['key_allowed']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="auto_renew_0"><input type="radio" class='addinput' title='Key Access Allowed' value="0" id="key_allowed_no" name="key_allowed" <?php echo ($row['key_allowed']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Tenant Key Email Required?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="auto_renew_1"><input type="radio" class='addinput' title='Tenant Key Email Required' value="1" id="key_email_req_yes" name="key_email_req" <?php echo ($row['key_email_req']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="auto_renew_0"><input type="radio" class='addinput' title='Tenant Key Email Required' value="0" id="key_email_req_no" name="key_email_req" <?php echo ($row['key_email_req']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			<!--
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Phone Call to confirm Keys Required?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="auto_renew_1"><input type="radio" class='addinput' title='Phone Call to confirm Keys Required?' value="1" id="phone_call_req_yes" name="phone_call_req" <?php echo ($row['phone_call_req']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="auto_renew_0"><input type="radio" class='addinput' title='Phone Call to confirm Keys Required?' value="0" id="phone_call_req_no" name="phone_call_req" <?php echo ($row['phone_call_req']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			-->
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Allow Doorknocks?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="allow_dk_yes"><input type="radio" class='addinput' title='Allow Doorknocks?' value="1" id="allow_dk_yes" name="allow_dk" <?php echo ($row['allow_dk']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="allow_dk_no"><input type="radio" class='addinput' title='Allow Doorknocks?' value="0" id="allow_dk_no" name="allow_dk" <?php echo ($row['allow_dk']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Allow Entry Notice?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label><input type="radio" class='addinput' title='Allow Entry Notice?' value="1" id="allow_dk_yes" name="allow_en" <?php echo ($row['allow_en']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label><input type="radio" class='addinput' title='Allow Entry Notice?' value="0" id="allow_dk_no" name="allow_en" <?php echo ($row['allow_en']==0)?'checked="checked"':''; ?>> No</label></div>
					<div class="eagdt-rd"><label><input type="radio" class='addinput' title='Allow Entry Notice?' value="-1" id="allow_dk_nr" name="allow_en" <?php echo ($row['allow_en']==-1)?'checked="checked"':''; ?>> No Response</label></div>
				</div>
            </div>
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">All New Jobs Emailed to Agency?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="new_job_email_to_agent_yes"><input type="radio" class='addinput' title='New Job Email to Agent?' value="1" id="new_job_email_to_agent_yes" name="new_job_email_to_agent" <?php echo ($row['new_job_email_to_agent']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="new_job_email_to_agent_no"><input type="radio" class='addinput' title='New Job Email to Agent?' value="0" id="new_job_email_to_agent_no" name="new_job_email_to_agent" <?php echo ($row['new_job_email_to_agent']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			
			<div class="row edadt">
				<div class="float-left eagdt-rd-fld">Display BPAY on Invoices?</div>
				<div class="eagdt-rd-h">
					<div class="eagdt-rd"><label for="display_bpay_yes"><input type="radio" class='addinput' title='Display BPAY?' value="1" id="display_bpay_yes" name="display_bpay" <?php echo ($row['display_bpay']==1)?'checked="checked"':''; ?>> Yes</label></div>
					<div class="eagdt-rd"><label for="display_bpay_no"><input type="radio" class='addinput' title='Display BPAY?' value="0" id="display_bpay_no" name="display_bpay" <?php echo ($row['display_bpay']==0)?'checked="checked"':''; ?>> No</label></div>					
				</div>
            </div>
			
			
			<?php
			$pm_sql = mysql_query("
				SELECT *
				FROM `property_managers`
				WHERE `agency_id` ={$agency_id}
			");
			$i = 0;
			 ?>
			
			<div id="pm_main_div" <?php echo ($row['allow_indiv_pm']==1)?'style="display:block"':''; ?>>
			
				<div class="row edadt">
					<div class="eagdt-rd-fld"><?php echo ($i==0)?'<h2 class="heading">Property Managers</h2>':'&nbsp;'; ?></div>
					<div class="eagdt-rd-h">
                    <style>
                    	#pm_table tr{border: none !important;}
                    </style>
						<table id="pm_table" style="margin-top: 8px;">
							<thead>
								<th>Name</th>
								<th>Email</th>
							</thead>
							<tbody>
								<?php
								while($pm = mysql_fetch_array($pm_sql)){ ?>
									<tr>
										<td>
											<input type="hidden" name="hid_pm_id[]" class="hid_pm_id" value="<?php echo $pm['property_managers_id']; ?>" />
											<input type="text" name="update_pm_name[]" class="addinput update_pm_name" value="<?php echo $pm['name']; ?>" />
										</td>
										<td>
											<input type="text" name="update_pm_email[]" style="width: 250px !important;" class="addinput update_pm_email" value="<?php echo $pm['pm_email']; ?>" />
										</td>
										<td>
											<button class="addinput submitbtnImg eagdtbt btn_delete" type="button">X</button>
										</td>
									</tr>
								<?php
									$i++;
								}
								?>
							</tbody>	
						</table>				
					</div>			
				</div>
				
				<div>				
					<div class="row edadt">
					<div class="float-left eagdt-rd-fld">&nbsp;</div>
					<div class="eagdt-rd-h">
						<button type="button" id="btn_add" class="addinput submitbtnImg eagdtbt">+ Property Manager</button>
					</div>
					</div>
				</div>
				
			</div>
			
			
			
			
			
			<?php

			
			
			
			$agency_name = $row[0];

			// Print a carriage return to neaten the output

			echo "\n";

		// (5) Close the database connection
	?>
	
	<!--
	<h2 class="style4">Alarms</h2>
		
		
		<table id='custom_price_table'>
			<thead>	
				<tr>
					<th>Type</th>
					<th>Approved</th>
					<th>Price</th>
				</tr>
			</thead>
			<tbody>
				<?php
				
				foreach($alarm_prices as $alarm)
				{
					echo "<tr>";
					echo "<td>" . $alarm['alarm_pwr'] . "&nbsp;&nbsp;&nbsp;</td><td><input type='checkbox' /></td><td>$<input type='text' name='alarm_pwr_id" . $alarm['alarm_pwr_id'] . "' id='alarm_pwr_id_" . $alarm['alarm_pwr_id'] . "' value='" . $alarm['alarm_price'] . "' /></td>";
					echo "</tr>";
				}
				
				?>
			</tbody>
		</table>
	-->
	
	
	<h2 class="heading">Alarms</h2>
		<table id='custom_price_table' class="table-center tbl-fr-red">
			<thead>	
				<tr bgcolor="b4151b">
					<th>Type</th>
					<th>Approved</th>
					<th>Price</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$alarm_sql = $agency->get_alarms();
				while($alarm = mysql_fetch_array($alarm_sql)){ 
				
				$sel_alarm_sql = $agency->get_approved_agency_alarms($agency_id,$alarm['alarm_pwr_id']);
				$num_rows = mysql_num_rows($sel_alarm_sql);
				if($num_rows>0){
					$sel_alarm = mysql_fetch_array($sel_alarm_sql);
				}
				?>
					<tr>
						<td style="display:none;">
							
							<?php
							if($num_rows>0){ ?>
								<input type="hidden" name="agency_alarm_id[]" class="agency_alarm_id" value="<?php echo $sel_alarm['agency_alarm_id']; ?>" />
								<input type="hidden" name="edit_alarm_is_sel[]" class="edit_alarm_is_sel is_sel" value="1" />
							<?php
								}else{ ?>
								<input type="hidden" name="alarm_pwr_id[]" value="<?php echo $alarm['alarm_pwr_id']; ?>" />
								<input type="hidden" name="alarm_is_sel[]" class="alarm_is_sel is_sel" value="0" />
							<?php	}
							?>
							
						</td>
						<td><?php echo $alarm['alarm_pwr']; ?></td>
						<td>
							<?php
							if($num_rows>0){ ?>
								<input type="checkbox" name="edit_alarm_approve[]" class="edit_alarm_approve approve" checked="checked" value="1" />
							<?php
							}else{ ?>
								<input type="checkbox" name="alarm_approve[]" class="alarm_approve approve" value="0" />
							<?php
							}
							?>
							
						</td>
						<td>
							<?php
								if($num_rows>0){ ?>
								<div class="price_div" style="display:block;">$<input type="text" name="edit_alarm_price[]" class="edit_alarm_price price req addinput" title="<?php echo $alarm['alarm_pwr']; ?> price" value="<?php echo $sel_alarm['price']; ?>" /></div>
							<?php
								}else{
							?>
								<div class="price_div" style="display:none;">$<input type="text" name="alarm_price[]" class="alarm_price price addinput"></div>
							<?php
								}
							?>
							
						</td>
					</tr>
				<?php 
				}							
				?>
			</tbody>
		</table>		
		
		<h2 class="heading">Services</h2>
		<table id="custom_price_table" class="table-center tbl-fr-red">
			<thead>	
				<tr bgcolor="b4151b">
					<th>Type</th>
					<th>Approved</th>
					<th>Price</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$service_sql = $agency->get_services();
				while($service = mysql_fetch_array($service_sql)){ 
				
				$sel_service_sql = $agency->get_approved_agency_services($agency_id,$service['id']);
				$num_rows = mysql_num_rows($sel_service_sql);
				if($num_rows>0){
					$sel_service = mysql_fetch_array($sel_service_sql);
				}
				?>
					<tr>
						<td style="display:none;">
							
							<?php
							if($num_rows>0){ ?>
								<input type="hidden" name="agency_services_id[]" class="agency_services_id" value="<?php echo $sel_service['agency_services_id']; ?>" />
								<input type="hidden" name="edit_service_is_sel[]" class="edit_service_is_sel is_sel" value="1" />
							<?php
								}else{ ?>
								<input type="hidden" name="service_id[]" value="<?php echo $service['id']; ?>" />
								<input type="hidden" name="service_is_sel[]" class="service is_sel" value="0" />
							<?php	}
							?>
							
						</td>
						<td><?php echo $service['type']; ?></td>
						<td>
							<?php
							if($num_rows>0){ ?>
								<input type="checkbox" name="edit_service_approve[]" class="edit_service_approve approve" checked="checked" value="1" />
							<?php
							}else{ ?>
								<input type="checkbox" name="service_approve[]" class="service_approve approve" value="0" />
							<?php
							}
							?>
							
						</td>
						<td>
							<?php
								if($num_rows>0){ ?>
								<div class="price_div" style="display:block;">$<input type="text" name="edit_service_price[]" class="edit_service_price price req addinput" title="<?php echo $service['type']; ?> price" value="<?php echo $sel_service['price']; ?>" /></div>
							<?php
								}else{
							?>
								<div class="price_div" style="display:none;">$<input type="text" name="service_price[]" class="service_price price addinput"></div>
							<?php
								}
							?>
							
						</td>
					</tr>
				<?php 
				}							
				?>
			</tbody>
		</table>
		
	<!--<h2 class="style4">Services</h2>
	<table>
		<thead>
			<tr>
				<th>Type</th>
				<th>Approved</th>
				<th>Price</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Annual Maintenance</td><td><input type="checkbox" /></td><td>$<input type="text" value="99" /></td>
			</tr>
			<tr>
				<td>Corded Windows</td><td><input type="checkbox" /></td><td>$<input type="text" value="99" /></td>
			</tr>
			<tr>
				<td>Pool Maintenance</td><td><input type="checkbox" /></td><td>$<input type="text" value="99" /></td>
			</tr>
			<tr>
				<td>Safety Switch & Mechanical test</td><td><input type="checkbox" /></td><td>$<input type="text" value="99" /></td>
			</tr>
		</tbody>
	</table>
	-->
	

		</div>
		
		
		<?php
		}
		?>
		
		
		
		<h2 class="heading">Maintenance Program</h2>
			
			
			
				<select name="maintenance" class="maintenance" id="maintenance" title="Maintenance Program" style="margin-bottom: 14px;">
					<option value=''>None</option>
					<?php 
					// get all maintenance
					$m_sql = mysql_query("
						SELECT *
						FROM `maintenance`
					");
					
					// get selected maintenance
					$sel_m_sql = mysql_query("
						SELECT *
						FROM `agency_maintenance`
						WHERE `agency_id` = {$_GET['id']}
					");
					$sel_m = mysql_fetch_array($sel_m_sql);
					
					
					while($m = mysql_fetch_array($m_sql)){ ?>
						<option value='<?php echo $m['maintenance_id']; ?>' <?php echo ($m['maintenance_id']==$sel_m['maintenance_id'])?'selected="selected"':''; ?>><?php echo $m['name']; ?></option>
					<?php	
					}
					?>
				</select>
			
			
			<div id="maintenance_program_div" <?php echo (mysql_num_rows($sel_m_sql)==0)?'style="display:none"':''; ?>>
				
				
				<div class="row">
			
					<label style="float: left; margin-right: 57px;">Apply Surcharge to all Invoices?</label> 
					<div class="eagdt-rd-h">
						<div class="eagdt-rd">
							<label for="send_emails_1"><input type="radio" class="addinput" title="Apply Surcharge to all Invoices" value="1" name="m_surcharge" <?php echo ($sel_m['surcharge']==1)?'checked="checked"':''; ?>> Yes</label>
						</div>
						<div class="eagdt-rd">
							<label for="send_emails_0"><input type="radio" class="addinput" title="Apply Surcharge to all Invoices" value="0" name="m_surcharge" <?php echo ($sel_m['surcharge']==0)?'checked="checked"':''; ?>> No</label></div>
						</div>
						
				</div>
				
				<div class="row">
					
					<label style="float: left; margin-right: 10px;">Surcharge $</label> <input type="text" style="width: 54px; float: left; margin-left: 32px;" class="addinput" name="m_price" id="m_price" title="Surcharge" value="<?php echo $sel_m['price']; ?>" />
				</div>
				
				<div class="row">
					
					<label style="float: left; margin-right: 52px;">Display Message on all Invoices?</label> 
					<div class="eagdt-rd-h">
						<div class="eagdt-rd">
							<label for="send_emails_1"><input type="radio" class="addinput" title="Display Message on all Invoices" value="1" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==1)?'checked="checked"':''; ?>> Yes</label>
						</div>
						<div class="eagdt-rd">
							<label for="send_emails_0"><input type="radio" class="addinput" title="Display Message on all Invoices" value="0" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==0)?'checked="checked"':''; ?>> No</label></div>
						</div>
					
					
					
				</div>
				
				
				
				<div class="row">
				
					<label style="float: left; margin-right: 10px;">Invoice Message</label> <input type="text" style="width: 440px; float: left; margin-left: 5px;" class="addinput" name="m_surcharge_msg" id="m_surcharge_msg" title="Invoice Message" value="<?php echo $sel_m['surcharge_msg']; ?>" />
				</div>
			</div>
		
		
		<h2 class="heading">Sales Rep</h2>

		<select class='addinput' name='salesrep' id='salesrep' title="Sales Rep">
			<option>-- Select a Sales Rep --</option>
		<?php
				$salesrep_sql = getStaffByCountry();
				while($salesrep = mysql_fetch_array($salesrep_sql)){ ?>
		  <option value="<?php echo $salesrep['staff_accounts_id'] ?>" <?php echo ($salesrep['staff_accounts_id']==$row['salesrep'])?'selected="selected"':''; ?>><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
		  <?php 
				}
				?>
		</select> 
		<input type='hidden' name='salesrep_edited' id='salesrep_edited' value='0' />
		
	
	<script type="text/javascript">
	$(document).ready(function() {
	
		/*
		
		var val = $('input[name=custom_alarm_pricing]:checked').val();
		val = parseInt(val);
		
		console.log(val);
		
		if(val == 1)
    	{
    		$("table#custom_price_table").show(200);
    	}
    	else
    	{
    		$("table#custom_price_table").hide(200);
    	}
		
	    $('input[name=custom_alarm_pricing]').live('change', function() { 
	    	val = $(this).val();
	    	console.log(val);
	    	if(val == 1)
	    	{
	    		$("table#custom_price_table").show(200);
	    	}
	    	else
	    	{
	    		$("table#custom_price_table").hide(200);
	    	}
	    	
	    	
	    });
		*/
		
	});
	</script>
	

	<input type='hidden' name='num_alarms' value='<?php echo sizeof($alarm_prices); ?>'>
	
        <?php //$user = $_SESSION['USER_DETAILS']['StaffID'];
        //if(in_array($user, array(2,10,2025,37))) { ?>
     
        <?php /*} else {
            echo "<input type='hidden' name='salesrep' value='". $row['salesrep'] ."'>";
        } */?>
    
	<input type="hidden" name="agency_id" value="<?php echo $row['agency_id']; ?>">
	<input type="hidden" id="fields_edited" name="fields_edited" />	
	<button class='addinput submitbtnImg eagdtbt' id="btn-update" type="button">Update Details</button>
		</form>
	</div>
    
</div>
<?php

}

?>

</div> 

<br class="clearfloat" />

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

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

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
}
// end google autocomplete





jQuery("#load-screen").show();

jQuery(document).ready(function(){
	
	jQuery("#load-screen").hide();
	
	// agency name edited
	jQuery("#agency_name").change(function(){
		
		jQuery("#agency_name_edited").val(1);
		
	});
	
	// agency emails edited
	jQuery("#agency_emails").change(function(){
		
		jQuery("#agency_emails_edited").val(1);
		
	});
	
	// agency emails edited
	jQuery("#account_emails").change(function(){
		
		jQuery("#account_emails_edited").val(1);
		
	});
	
	
	// salesrep edited
	jQuery("#salesrep").change(function(){
		
		jQuery("#salesrep_edited").val(1);
		
	});
	
	// postcode region auto fill script
	jQuery("#postcode").blur(function(){
		
		var postcode = jQuery(this).val();
		
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
				jQuery("#region_id").val(ret.postcode_region_id);
			});	
		}
		
	});
	
	
	// field edited script
	jQuery(".addinput").change(function(){

		var fields_edited = jQuery("#fields_edited").val();
		var field = jQuery(this).attr("title");
		if(fields_edited.search(field)==-1){
			console.log('already exist');
			var comb = fields_edited+","+field;
			jQuery("#fields_edited").val(comb);
		}
		
	  
	});
	
	jQuery("#maintenance").change(function(){
		
		if(jQuery(this).val()!=""){
			jQuery("#maintenance_program_div").show();
		}else{
			jQuery("#maintenance_program_div").hide();
		}
		
	});

	// change status
	jQuery("#status").change(function(){
		if(jQuery(this).val()=="target"){
			var prompt = confirm("If you change status to Target, all Jobs will be cancelled and all properties will be deactivated. Are you sure you want to continue?");
			if(!prompt){				
				jQuery("#status option:first").prop("selected",true);
			}
		}
	});


	// delete pm
	jQuery(".btn_delete").click(function(){	
		var obj = jQuery(this);
		var hid_pm_id = obj.parents("tr:first").find(".hid_pm_id").val();		
	
		if(confirm("Are you sure you want to delete?")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_property_manager.php",
				data: { 
					hid_pm_id: hid_pm_id,
				}
			}).done(function( ret ){
				if(parseInt(ret)==1){
					alert("There are properties attached to this PM");
				}else{
					obj.parents("tr:first").remove();
				}
				
			});	
		}
	});


	jQuery("#pm_check_y").click(function(){
		jQuery("#pm_main_div").show();	  
	});
	
	jQuery("#pm_check_n").click(function(){
		jQuery("#pm_main_div").hide();	  
	});

	// add property manager
	jQuery("#btn_add").click(function(){
	  var str = '<tr>'+
					'<td>'+
						'<input type="text" class="addinput pm_name" name="pm_name[]">'+
					'</td>'+
					'<td>'+
						'<input type="text" class="addinput pm_email" name="pm_email[]">'+
					'</td>'+
					'<td>'+
						'<button type="button" id="btn_remove" class="addinput submitbtnImg eagdtbt btn_remove">X</button>'+
					'</td>'+
				'</tr>';	  
	  jQuery("#pm_table tbody").append(str);  
	});
	
	// remove
	jQuery(document).on("click",".btn_remove",function(){
		jQuery(this).parents("tr:first").remove();
	});

	// submission
	jQuery("#btn-update").click(function(){
	
		var agency_name = jQuery("#agency_name").val();
		var region = jQuery("#region_id").val();
		var agency_emails = jQuery("#agency_emails").val();
		var account_emails = jQuery("#account_emails").val();
		var error = "";
		var flag = 0;
		
		if(agency_name==""){
			error += "Agency Name is required\n";
		}
		/*
		if(region==""){
			error += "<?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?> is required\n";
		}
		*/
		if(agency_emails==""){
			error += "Agency emails are required\n";
		}
		if(account_emails==""){
			error += "Account emails is required\n";
		}
		jQuery(".req").each(function(){
			if(jQuery(this).val()==""){
				flag = 1;
			}
		});
		if(flag == 1){
			error += "Approved Price is required\n";
		}
		
		var invalid_email = 0;
		
		jQuery(".update_pm_email").each(function(){
			
			var email = jQuery(this).val();			
			
			if( email!="" && validateEmail(email)==false ){
				invalid_email++;
			}				
			
		});
		
		jQuery(".pm_email").each(function(){
			
			var email = jQuery(this).val();			
			
			if( email!="" && validateEmail(email)==false ){
				invalid_email++;
			}				
			
		});
		
		if(invalid_email>0){
			error += 'Some property manager email is invalid format\n';
		}
		
		if(error!=""){
			alert(error);
		}else{
			jQuery("#jform").submit();
		}
		
	});

	// mark pass as changed
	jQuery("#pass").change(function(){
		jQuery("#pass_change").val(1);
	});

	// mark total properties as changed
	jQuery("#tot_properties").change(function(){
		jQuery("#tot_prop_change").val(1);
	});
	
	// require approve price script
	jQuery(document).on("click",".approve",function(){		
		// is approved hidden value
		var state = jQuery(this).prop("checked");
		//is_edit = jQuery(this).parents("tr:first").find(".agency_alarm_id").length;
		if(state==true){
			jQuery(this).parents("tr:first").find(".is_sel").val(1);					
			jQuery(this).parents("tr:first").find(".price_div").show();	
			jQuery(this).parents("tr:first").find(".price").addClass("req");		
		}else{	
			jQuery(this).parents("tr:first").find(".is_sel").val(0);
			jQuery(this).parents("tr:first").find(".price_div").hide();
			jQuery(this).parents("tr:first").find(".price").removeClass("req");			
		}
	});
	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
