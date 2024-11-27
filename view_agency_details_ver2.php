<?

$title = "Agency Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');
include('inc/agency_services_class.php');
include('inc/agency_alarms_class.php');
include('inc/agency_class.php');

// invoke class
$aa_class = new Agency_Alarms($agency_id);
$as_class = new Agency_Services($agency_id);
$agency = new Agency_Class();
$crm = new Sats_Crm_Class;

$encrypt = new cast128();
$encrypt->setkey(SALT);
 


$agency_id = $_REQUEST['id'];
$tab = $_REQUEST['tab'];
$search = $_REQUEST['search'];
$status = $_REQUEST['status'];

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];



//autoUpdateAgencyRegion($agency_id);





# Agency Details

$query = "SELECT 
          a.agency_name,
          a.address_1,
          a.address_2,
          a.address_3,
          a.state,
          a.postcode,
          a.send_emails,
          a.account_emails,
          ar.agency_region_name,
          a.salesrep,
          s.FirstName,
          s.LastName
        FROM
          agency a 
          LEFT JOIN agency_regions ar USING (agency_region_id)
          LEFT JOIN staff_accounts s ON (a.salesrep = s.StaffID) 
		where (agency_id='".$agency_id."')";
$agency_details = mysqlSingleRow($query);

$alarm_prices = Agency::getAlarmPrices($agency_id);


// agency logs
if($_POST['doaction'] == "addevent"){
	
	$_POST = addSlashesData($_POST);
	$_POST['eventdate'] = convertDate($_POST['eventdate']);
	$next_contact = ($_POST['next_contact']!="")?date("Y-m-d",strtotime(str_replace("/","-",$_POST['next_contact']))):'';
	// insert the event into the database
	   
	$important = ($_POST['important']!="")?1:0;

   $eventdate = convertDate($eventdate);
   // (2) Run the query 
   $insertQuery = "INSERT INTO `agency_event_log` (contact_type,eventdate,comments,agency_id,`staff_id`,`next_contact`, `important` ) VALUES 
   ('" . $_POST['contact_type'] . "','" . $_POST['eventdate'] . "','" . $_POST['comments'] . "','{$_GET['id']}','{$staff_id}','{$next_contact}', '".$important."' );";
	
	// echo "insertQuery is <br>$insertQuery<br>\n";

	mysql_query($insertQuery);
	
	if (mysql_affected_rows($connection) == 0)
		echo "An error occurred creating the event, please report\n";
		
	   
		
}


## Delete CRM Entry
if($_GET['doaction'] == 'delete' && intval($_GET['cid']) > 0)
{
	$query = "DELETE FROM `agency_event_log` WHERE `agency_event_log_id` = '" . $_GET['cid'] . "' AND agency_id = '" . $agency_id ."' LIMIT 1";
	mysql_query($query) or die(mysql_error());
	//echo "Event Deleted";
}


// file uploads
function uploadfile2($files_arr, $agency_id)
{
	// path
	$upload_path = $_SERVER['DOCUMENT_ROOT'].'agency_files/';
	
	#ensure property id set
	if(intval($agency_id) == 0) return false;	
	
	#security measure, don't allow ..
	if(stristr($files_arr['fileupload']['name'], "..")) return false; 
	
	
	# if subdir doesn't exist then create it first
	if(!is_dir($upload_path . $agency_id))
	{
		@mkdir($upload_path . $agency_id, 0777);
	}
	
	// append this text to prevent from overwritting file with same name
	$append_text = 'af'.rand().date('YmdHis');
	
	if(move_uploaded_file($files_arr['fileupload']['tmp_name'], $upload_path . $agency_id . "/{$append_text}" . $files_arr['fileupload']['name']))
	{
		return true;
	}
	else {
		return false;
	}
	
}

# Get Property Files - will eventually move these into a class / similar
function getPropertyFiles2($agency_id)
{
	// path
	$upload_path = $_SERVER['DOCUMENT_ROOT'].'agency_files/';

	# if subdir doesn't exist then return null
	if(!is_dir($upload_path . $agency_id))
	{
		//echo $upload_path;
		return null;
	}
	else 
	{
		if ($handle = opendir($upload_path . $agency_id)) 
		{
			$files = array();
			
			while (false !== ($entry = readdir($handle))) 
			{
				if($entry != "." && $entry != "..")
				{	
					$files[] = $entry;
				}
			}
		
			closedir($handle);
		
			return $files;
		}
		else
		{
			return null;
		}
	}
}

# Delete property file
function deletefile2($file, $agency_id)
{
	// path
	$upload_path = $_SERVER['DOCUMENT_ROOT'].'agency_files/';

	if(intval($agency_id) == 0) return false;
	if(strlen($file) == 0) return false;
	
	#non allowed chars
	$notallowed = array("/", "\\", "..");
	$file = str_replace($notallowed, "", $file);
	
	if(file_exists($upload_path . $agency_id . "/" . $file))
	{
		@unlink($upload_path . $agency_id . "/" . $file);
		return true;	
	}
	else
	{
		return false;
	}

}

# Process Upload
if($_FILES['fileupload']['error'] == 0 && $_FILES['fileupload']['size'] > 0)
{

	if(uploadfile2($_FILES, $agency_id))
	{
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=1'</script>";
		//echo "<div class='success'>File Uploaded Successfully</div>";
	}
	else
	{
		//echo "<div class='error'>Technical Problem. Please Try Again</div>";
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=0'</script>";
	}
}

# Process Delete
if(isset($_GET['delfile']))
{
	
	$delfile = rawurldecode($_GET['delfile']);
	
	
	if(deleteFile2($delfile, $agency_id))
	{
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=2'</script>";
		//echo "<div class='success'>File Deleted Successfully</div>";
	}
	else
	{
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=0'</script>";
		//echo "<div class='error'>Technical Problem. Please Try Again</div>";
	}
	
	
}

?>
<style>
@media only screen 
and (min-width : 1824px) {

	.priceAlarmCol{
		width: 360px !important;
	}

}
.caf_table, .caf_table tr, .caf_table tr:last-child{
	border: medium none !important;
}
.caf_table td{
	text-align: left;
}

#tabs table{
	text-align: left;
	font-size: 13px;
}


.grey_bg_color{
	background-color: #eeeeee;
}

.white_bg_color{
	background-color: #ffffff;
}

.important_bg_color{
	background-color:#FFCCCB!important; 
	border: 1px solid #b4151b!important; 
	box-shadow: 0 0 2px #b4151b inset!important;
}

.agency_logs_input{
	margin-left: 0px; 
	width: 75px; 
	display: block; 
	float: none;
}

.tab_agency_logs table tr {
    /*border: medium none !important;*/
}
.agency_details_tab_cont label{
	float: left !important; 
	margin-top: 8px !important; 
}

.agency_details_tab_cont .col1{
	width: 150px;
}
.agency_details_tab_cont .col2{
	width: 340px;
}
.agency_details_tab_cont .addinput{
	margin: 0;
}


.agency_pref_tab_content label{
	float: left !important; 
	margin-top: 8px !important; 
}

.agency_pref_tab_content .col1{
	width: 300px;
}

.agency_pref_tab_content .col2, .agency_pref_tab_content .col3{
	width: 300px;
}

.timestamp_txt{
	color: #00D1E5; 
	position: relative; 
	top: 7px;
}
h5{
	text-align: left;
}
.agency_contact_inner_div input.addinput {
    width: 150px;	
    margin-right: 5px;
}

.c-tab__content .table tr{
	border:none !important;
}
.agency_contact_inner_div textarea.addtextarea{
	height: 90px !important;
}

.c-tab__content button{
	margin: 0;
}

.colorItGreen{
	color: green;
	display:none;
}
.colorItRed{
	color: red;
	
}
.jshowIt{
	display:block;
}
.jhideIt{
	display:none;
}

.contact_details_tab_cont table tr td, table.agency_address tr td{
    padding: 0;
}

table.agency_address .addinput{
	margin-right: 5px !important;
}



.login_details_div input.addinput{
    width: 219px;
    margin-right: 5px;
}

.login_details_div select.addinput{
    width: 229px;
    margin: 0 5px 0 0;
}

.agency_comments{
	margin: 0 !important; 
	width: 558px !important; 
	height: 60px !important; 
	font-size: 13.3333px !important; 
	padding-left: 10px !important;
}

.pricing_tab_content_table table tr th, .pricing_tab_content_table table tr td {
    padding: 0 13px;
	margin: 0px;
}

.pricing_tab_content_table table{
    width: auto;
}

.pricing_tab_content_table input.addinput {
    width: 60px;
}

.fadedText{
	opacity: 0.5;
}

.login_icon{
	position: relative;
    left: 5px;
    top: 2px;
}
</style>
    <div id="mainContent">	
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first">
			<a title="Agencies" href="/view_agencies.php">View Agencies</a>			
		</li>
		<li class="other first">
			<?php $tab_bc = ($_GET['tab']=='crm')?'Agency Properties':'Agency Details'; ?>
			<a title="Agency Details" href="/view_agency_details.php?id=<?php echo $_REQUEST['id']; ?>"><strong><?php echo $tab_bc; ?></strong></a>			
		</li>
      </ul>
    </div>
	<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	<?php
	if($_GET['success']==1){ ?>
	
		<div class="success">Update Successfull</div>
	
	<?php	
	}
	?>
	
	

	<?php	
	$insertQuery = "
	SELECT *,
		a.`password` AS a_pass,
		sa.FirstName AS sr_fname,
		sa.LastName AS sr_lname,
		fg.`name` AS fg_name,
		sr.sub_region_id AS postcode_region_id,
		sr.subregion_name as postcode_region_name
	FROM `agency` AS a
	LEFT JOIN `sub_regions` AS sr ON a.`postcode_region_id` = sr.`sub_region_id`
	LEFT JOIN `staff_accounts` AS sa ON a.`salesrep` = sa.`StaffID`
	LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
	LEFT JOIN `franchise_groups` AS fg ON a.`franchise_groups_id` = fg.`franchise_groups_id`
	WHERE a.`agency_id` = '".$agency_id."'
	";

	$result = mysql_query ($insertQuery, $connection);
	$row = mysql_fetch_array($result);
	?>



	<div class='vw-pro-dtl-tn-hld vpr-left clear' style='float: none;  margin: 0;'>	

		<div id="tabs" class="c-tabs no-js">
		
			<div class="c-tabs-nav">
				<a href="#" data-tab_index="0" data-tab_name="agency_details" class="c-tabs-nav__link is-active">Agency Details</a>
				<a href="#" data-tab_index="1" data-tab_name="contact_details" class="c-tabs-nav__link">Contact Details</a>
				<a href="#" data-tab_index="2" data-tab_name="property_managers" class="c-tabs-nav__link">Property Managers</a>
				<a href="#" data-tab_index="3" data-tab_name="prices" class="c-tabs-nav__link">Pricing</a>
				<a href="#" data-tab_index="4" data-tab_name="preferences" class="c-tabs-nav__link">Preferences</a>
				<a href="#" data-tab_index="5" data-tab_name="agency_logs" class="c-tabs-nav__link">Logs</a>
				<a href="#" data-tab_index="6" data-tab_name="agency_files" class="c-tabs-nav__link">Files</a>				
				<a href="#" data-tab_index="7" data-tab_name="properties" class="c-tabs-nav__link">Properties</a>
			</div>
			
			<form action='update_agency.php' method='post' id="jform">
			
			<!-- AGENCY DETAILS -->
			<div class="c-tab is-active" data-tab_cont_name="agency_details">
				<div class="c-tab__content">
				
					<div class="agency_details_tab_cont">	

					
					
					<div style="float:left">
					
						<h5>Address</h5>
					
						<div class='row'>
							<label>Google Address Bar</label><br />							
							<input class="addinput" style="width:558px;" name="fullAdd" id="fullAdd" type="text" value="<?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; ?>" />
						</div>
						
						<div style="clear:both;"></div>
					
					
						<div class='row'>					
							<label class='col2'>
								<table class='table agency_address'>
									<tr>
										<td>No.</td>
										<td>Street</td>
										<td>Suburb</td>
										<td><?php echo ifCountryHasState($_SESSION['country_default'])?'State':'Region'; ?></td>
										<td>Postcode</td>
									</tr>
									<tr>
										<td><input class="addinput" style="width: 77px;" name='street_number' id='street_number' type="text" value="<?php echo $row['address_1']; ?>"></td>
										<td><input class="addinput" style="width: 165px;" name='street_name' id='street_name' type="text" value="<?php echo $row['address_2']; ?>"></td>
										<td><input class="addinput" style="width: 141px;" name='suburb' id='suburb' type="text" value="<?php echo $row['address_3']; ?>"></td>
										<td>
											<?php 											
											if( ifCountryHasState($_SESSION['country_default'])==true ){ ?>
												<select class="addinput" style="width: 81px;" id="state" name="state">
												  <option value="">----</option>
												   <?php
												  $state_sql = getCountryState();
												  while($state = mysql_fetch_array($state_sql)){ ?>
													<option value='<?php echo $state['state']; ?>' <?php echo ($state['state']==$row['state'])?'selected="selected"':''; ?>><?php echo $state['state']; ?></option>
												  <?php	  
												  }
												  ?>
												</select>
											<?php	
											}else{ ?>
												<input type='text' name='state' id='state' value='<?php echo $row['state']; ?>' class='addinput' />
											<?php	
											}
											?>											
										</td>
										<td><input class="addinput" style="width: 44px;" name='postcode' id='postcode' type="text" value="<?php echo $row['postcode']; ?>"></td>
									</tr>
								</table>
							</label>
						</div>
						
						<div style="clear:both;"></div>
						
						
						
						<div class='row'>
							<table class='table agency_address' style="width:auto; margin-top: 10px;">
								<tr>
									<td><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></td>
									<td>Franchise Group</td>
									<td>Landline</td>
								</tr>
								<tr>
									<td>
										<?php 
										if( $row['postcode_region_id']!="" ){ ?>
											<input class="addinput" style="width: 165px;" readonly="readonly" name='postcode_region_name' id='postcode_region_name' type="text" value="<?php echo $row['postcode_region_name']; ?>">
										<?php	
										}else{
											echo "NO region set up for this postcode";
										}
										?>
									</td>
									<td>
										<select id="franchise_group" style="width: 170px;" name="franchise_group" class="addinput" title="Franchise Group">
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
									</td>
									<td>
										<input class="addinput" style="width: 98px;" title="Landline" name='phone' id='phone' type="text" value="<?php echo $row['phone']; ?>">
									</td>
								</tr>
							</table>	
						</div>		
							
						<div style="clear:both;"></div>
						
						<div class='row'>
							<label>Agency Comments</label><br />							
							<textarea class="addinput addtextarea formtextarea agency_comments" title="Agency Comments" name='comment' id='comment'><?php echo $row['comment']; ?></textarea>
						</div>
						
						<div style="clear:both;"></div>
						
						<?php
						# Decode password
						if(UTF8_USED){
							$pass = $encrypt->decrypt(utf8_decode($row['a_pass']));
						}else{
							$pass = $encrypt->decrypt($row['a_pass']);
						}
						$decr_pass = ($row['a_pass']!="")?addslashes($pass):'';
						$url = $_SERVER['SERVER_NAME'];
						$url_params = "?user={$row['login_id']}&pass={$pass}";
						
						
						if($_SESSION['country_default']==1){ // AU
		
							if( strpos($url,"crmdev")===false ){ // live
								$agency_site = "//agency.sats.com.au{$url_params}";
							}else{ // dev
								$agency_site = "//agencydev.sats.com.au{$url_params}";
							}
							
						}else if($_SESSION['country_default']==2){ // NZ
							
							if( strpos($url,"crmdev")===false ){ // live
								$agency_site = "//agency.sats.co.nz{$url_params}";
							}else{ // dev
								$agency_site = "//agencydev.sats.co.nz{$url_params}";
							}
							
							
						}
						?>
						<div class="login_details_div">
						
						<h5>Login Details <a href='<?php echo $agency_site; ?>'><img src='/images/agency_login.png' class="login_icon" /></a></h5> 
						
						<div class='row'>
							<label>Username</label><br />
							<input class="addinput" title="Username" name='user' id='user' type="text" value="<?php echo $row['login_id']; ?>">
						</div>

						
						<div class='row'>
							<label>Password</label><br />
							<input class="addinput" title="Password" name='pass' id='pass' type="text" value="<?php echo $decr_pass; ?>">
							<?php $timestamp = ($row['pass_timestamp']!='0000-00-00 00:00:00')?'Last Updated '.date('d/m/Y',strtotime($row['pass_timestamp'])):''; ?>
							<!--<label class='col3 timestamp_txt'><span><?php echo $timestamp; ?></label>-->
						</div>
						
						<div style="clear:both;"></div>
												
						<div class='row'>
							<h5>Agency Status</h5>
							<select id="status" class="addinput status" name="status" title="Agency Status">						
								<option <?php echo ($row['status']=='active')?'selected="selected"':''; ?> value='active'>Active</option>
								<option <?php echo ($row['status']=='target')?'selected="selected"':''; ?> value='target'>Target</option>
								<option <?php echo ($row['status']=='deactivated')?'selected="selected"':''; ?> value='deactivated'>Deactivated</option>
							</select>							
						</div>
						
						
						<div class='row'>
							<h5>Sales Rep</h5>
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
						</div>
						
						<div style="clear:both;"></div>
						
						<div class='row' style="width: 463px;">
							<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin-top:20px; float: right;">Update Details</button>
						</div>
						
						<div style="clear:both;"></div>
						
						</div>
						
						
						
						
						
					</div>
						 
					</div>
					
					
					
					<div class="agency_details_tab_cont" style="float:left; margin-left: 40px;">
					
					<h5>Agency Details</h5>
			
					<div class='row'>
						<label class='col1'>Agency Name</label>							
						<label class='col2'><input class="addinput" title="Agency Name" name="agency_name" type="text" value="<?php echo $row['agency_name']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>
					
					
					<div class='row'>
						<label class='col1'>Legal Name</label>						
						<label class='col2'><input class="addinput" title="Legal Name" name="legal_name" type="text" title="Legal Name" value="<?php echo $row['legal_name']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>
					
					
					<?php $abn_lbl = $_SESSION['country_default']==1?'ABN Number':'GST Number'; ?>
					<div class='row'>
						<label class='col1'><?php echo $abn_lbl; ?></label>						
						<label class='col2'><input class="addinput" name="abn" title="<?php echo $abn_lbl; ?>" type="text" value="<?php echo $row['abn']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>		
						
					<div class='row'>
						<label class='col1'>Total Properties</label>
						<label class='col2'><input class="addinput" title="Total Properties" name='tot_properties' id='tot_properties' type="text" value="<?php echo $row['tot_properties']; ?>"></label>
						<?php $timestamp = ($row['tot_prop_timestamp']!='0000-00-00 00:00:00')?'Last Updated '.date('d/m/Y',strtotime($row['tot_prop_timestamp'])):''; ?>
						<!--<label class='col3 timestamp_txt'><span><?php echo $timestamp; ?></label>-->
					</div>
					
					<div style="clear:both;"></div>						
					
					<div class='row'>
						<label class='col1'>Agency Hours</label>
						<label class='col2'><input class="addinput" title="Agency Hours" name='agency_hours' id='agency_hours' type="text" value="<?php echo $row['agency_hours']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>										
					
					<div class='row'>
						<label class='col1'>Agency Specific Notes</label>
						<label class='col2'><input class="addinput" title="Agency Specific Notes" name='agency_specific_notes' id='agency_specific_notes' type="text" value="<?php echo $row['agency_specific_notes']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>
					
					<div class='row'>
						<label class='col1'>Team Meeting</label>
						<label class='col2'><input class="addinput" title="Team Meeting" name='team_meeting' id='team_meeting' type="text" value="<?php echo $row['team_meeting']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>
					
					<div class='row'>
						<label class='col1'>Website</label>
						<label class='col2'><input class="addinput" title="Website" name='website' id='website' type="text" value="<?php echo $row['website']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>

					<?php
					if( $row['status']=='target' || $row['status']=='deactivated' ){ ?>
					
						<div class='row'>
							<label class='col1'>Currently Using</label>
							<label class='col2'>
								<select name="agency_using" title="Currently Using" class="addinput agency_using">
									<option value="">----</option>
									<?php 
									$au_sql = getAgencyUsingByCountry();
									while($au = mysql_fetch_array($au_sql)){ ?>
										<option value="<?php echo $au['agency_using_id']; ?>" <?php echo($row['agency_using_id']==$au['agency_using_id'])?'selected="selected"':''; ?>><?php echo $au['name']; ?></option>
									<?php
									}
									?>
								</select>
							</label>
						</div>
						
						<div style="clear:both;"></div>
					
					<?php
					}
					?>						
					</div>
					
					
				</div>
			</div>
			
			<!-- CONTACT DETAILS -->
			<div class="c-tab" data-tab_cont_name="contact_details">
				<div class="c-tab__content contact_details_tab_cont">
			
					<!-- AGENCY CONTACT -->
					<h5>Agency Contact</h5>
					<div class='row agency_contact_inner_div'>					
						
						<table class="table">
							<tr>
								<td>First Name</td>	
								<td>Last Name</td>
								<td>Phone</td>
								<td>Email</td>
							</tr>
							<tr>
								<td><input class="addinput" title="Agency Contact First Name" name="ac_fname" type="text" value="<?php echo $row['contact_first_name']; ?>"></td>
								<td><input class="addinput" title="Agency Contact Last Name" name="ac_lname" style="width: 95px;" type="text" value="<?php echo $row['contact_last_name']; ?>"></td>
								<td><input class="addinput" title="Agency Contact Phone" name="ac_phone" style="width:93px;" type="text" value="<?php echo $row['contact_phone']; ?>"></td>
								<td><input class="addinput" title="Agency Contact Email" name="ac_email" style="width: 240px;;" type="text" value="<?php echo $row['contact_email']; ?>"></td>
							</tr>
						</table>
						
					</div>
					
					<div style="clear:both;"></div>				
					
					<h5>Accounts Contact</h5>
					<div class='row agency_contact_inner_div'>
											
						<table class="table">
							<tr>
								<td>Name</td>	
								<td>Phone</td>
							</tr>
							<tr>
								<td><input class="addinput" title="Accounts Contact Name" name="acc_name" type="text" value="<?php echo $row['accounts_name']; ?>"></td>
								<td><input class="addinput" title="Accounts Contact Phone" style="width: 95px;" name="acc_phone" type="text" value="<?php echo $row['accounts_phone']; ?>"></td>							
							</tr>
						</table>
						
					</div>
					
					<div style="clear:both;"></div>

					
					<h5>Tenant Details Contact</h5>
					<div class='row agency_contact_inner_div'>
						
						<table class="table">
							<tr>
								<td>Name</td>	
								<td>Phone</td>
							</tr>
							<tr>
								<td><input class="addinput" title="Tenant Details Contact Name" name="tdc_name" type="text" value="<?php echo $row['tenant_details_contact_name']; ?>"></td>
								<td><input class="addinput" title="Tenant Details Contact Phone" style="width: 95px;" name="tdc_phone" type="text" value="<?php echo $row['tenant_details_contact_phone']; ?>"></td>							
							</tr>
						</table>
						
						
						
					</div>
					
					<div style="clear:both;"></div>
					
					
					
					<!-- ACCOUNT EMAILS -->							
					<div class='row agency_contact_inner_div' style="margin:20px 0;">
						
						<table class="table">
							<tr>
								<th>Agency Emails</th>
								<th>Accounts Emails</th>
							</tr>
							<tr>
								<td>								
									<textarea title="Agency Emails" name='agency_emails' style="margin-right: 10px;" id='agency_emails' class='addtextarea formtextarea'><?php echo $row['account_emails']; ?></textarea>
								</td>
								<td>								
									<textarea title="Accounts Emails" name='account_emails' style="margin-right: 10px;" id='account_emails' class='addtextarea formtextarea' title='Accounts Emails'><?php echo $row['agency_emails']; ?></textarea>
								</td>								
							</tr>
							<tr>								
								<td>
									<strong>(Reports, Key Sheet)</strong> <em>(one per line)</em> <span style='color:red'>*</span>
								</td>
								<td>
									<strong>(Invoices, Certificates)</strong> <em>(one per line)</em> <span style='color:red'>*</span>	
								</td>								
							</tr>	
							<tr>
								<td colspan="100%" style="text-align:right;"><button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="float: none; margin-top: 15px;">Update Details</button></td>
							</tr>
						</table>
						
					</div>

			
					
	
					
					
					
					
			
				</div>
			</div>
			
			<!-- PROPERTY MANAGERS -->
			<div class="c-tab" data-tab_cont_name="property_managers">
				<div class="c-tab__content">
			
					<?php
					if( $row['allow_indiv_pm']==1 ){
												
						$pm_sql = mysql_query("
							SELECT *
							FROM `property_managers`
							WHERE `agency_id` ={$agency_id}
						");
						$i = 0;
						if( mysql_num_rows($pm_sql)>0 ){ ?>
						
							<div id="pm_main_div">							
								<div class="row edadt">			
									<div>
										<table class="table" id="pm_table" style="margin-top: 8px;">
											<thead>
												<th>Name</th>
												<th>Email</th>
											</thead>
											<tbody>
												<?php
												while($pm = mysql_fetch_array($pm_sql)){ ?>
													<tr>
														<td>
															<input type="hidden" name="pm_id[]" class="pm_id" value="<?php echo $pm['property_managers_id']; ?>" />
															<input type="text" name="pm_name[]" class="addinput pm_name" value="<?php echo $pm['name']; ?>" />
														</td>
														<td>
															<input type="text" style="width: 250px !important;" name="pm_email[]" class="addinput pm_email" value="<?php echo $pm['pm_email']; ?>" />
														</td>
														<td>
															<button class="addinput submitbtnImg eagdtbt btn_delete" type="button">Delete</button>
														</td>
													</tr>
												<?php
													$i++;
												}
												?>												
											</tbody>	
										</table>	
										
										<div style="margin-top: 20px;">
											<button type="button" id="btn_add" class="addinput submitbtnImg eagdtbt blue-btn">+ Property Manager</button>
											<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="float: right;">Update Details</button>
										</div>
									
									</div>			
								</div>		
							</div>
						
						<?php	
						}				
					}
					?>
										
					<div style="clear:both;"></div>					
				
				</div>
			</div>
			
			<!-- PRICING -->
			<div class="c-tab" data-tab_cont_name="prices">
				<div class="c-tab__content pricing_tab_content_table">
				
					<?php
					$service_sql = $agency->get_services();
					?>
					<table class="table" id='tbl_servicing' style="margin-bottom:30px;">
						<tr>
							<th>Services</th>
							<th>Price</th>
							<th>Approved</th>								
						</tr>
						<?php
						$index = 0;
						while($service = mysql_fetch_array($service_sql)){
							
							$sel_service_sql = $agency->get_approved_agency_services($agency_id,$service['id']);
							$agencySelected = (mysql_num_rows($sel_service_sql)>0)?true:false;
							$sel_service = mysql_fetch_array($sel_service_sql);
							
						?>
							<tr <?php echo ($agencySelected==true)?'':'class="fadedText"'; ?>>
								<td class="priceAlarmCol">
									<?php 
									echo $service['type']; 
									if( is_numeric($sel_service['price']) && $sel_service['price']==0 ){ ?>
										<span style="color:red; margin-left: 20px;">FREE</span>
									<?php	
									}
									?>
								</td>
								<td class="price_div">
									<label>$</label>
									<input type="text" name="service_price[]" class="addinput service_price" value="<?php echo $sel_service['price']; ?>" />
								</td>
								<td>								
									<input type="checkbox" name="agency_service_approve[]" class="approve agency_service_approve" <?php echo ($agencySelected==true)?'checked="checked"':''; ?> value="<?php echo $index; ?>" />		
									<input type="hidden" name="service_id[]" class="service_id" value="<?php echo $service['id']; ?>" />
								</td>								
							</tr>
						<?php
						$index++;
						}
						?>	

						<tr><td colspan="100%"><div style="visibility: hidden; margin: 10px 0;">----Separator----</div></td></tr>
						
						<?php
						$alarm_sql = $agency->get_alarms();
						?>				
						<tr>
							<th>Alarms</th>
							<th>&nbsp;</th>
							<th>&nbsp;</th>								
						</tr>
						<?php
						$index = 0;
						while($alarm = mysql_fetch_array($alarm_sql)){ 
						
							$sel_alarm_sql = $agency->get_approved_agency_alarms($agency_id,$alarm['alarm_pwr_id']);
							$agencySelected = (mysql_num_rows($sel_alarm_sql)>0)?true:false;
							$sel_alarm = mysql_fetch_array($sel_alarm_sql);
							
						 ?>
							<tr <?php echo ($agencySelected==true)?'':'class="fadedText"'; ?>>
								<td class="priceAlarmCol">
									<?php 
									echo $alarm['alarm_pwr']; 
									if( is_numeric($sel_alarm['price']) && $sel_alarm['price']==0 ){ ?>
										<span style="color:red; margin-left: 20px;">FREE</span>
									<?php	
									}
									?>
								</td>	
								<td class="price_div">
									<label>$</label>
									<input type="text" name="alarm_price[]" class="addinput" value="<?php echo $sel_alarm['price']; ?>" />									
								</td>
								<td>
									<input type="checkbox" name="agency_alarm_approve[]" class="agency_alarm_approve approve" <?php echo ($agencySelected==true)?'checked="checked"':''; ?> value="<?php echo $index; ?>" />
									<input type="hidden" name="alarm_id[]" class="alarm_id" value="<?php echo $alarm['alarm_pwr_id']; ?>" />
								</td>								
							</tr>
						<?php
						$index++;
						} 
						?>	
						<tr>
							<td>&nbsp;</td>
							<td colspan="2"><button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin-top:10px; float: right;">Update Details</button></td>
						</tr>
					</table>
				
					<div style="clear:both;"></div>

				</div>
			</div>
			
			<!-- PREFERENCES -->
			<div class="c-tab" data-tab_cont_name="preferences">
				<div class="c-tab__content agency_pref_tab_content">
				
				
					<div style="float:left; margin-right: 40px;">
				
					<h5>Agency Preferences</h5>
				
					<table class="table agency_pref_tbl" style="width:auto;">
						<tr>
							<td>Email Invoices and Certificates?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_emails' value='1' <?php echo $row['send_emails'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_emails' value='0' <?php echo $row['send_emails'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_emails']==1)?'jshowIt':'jhideIt'; ?>">Invoices and Certificates are emailed to Agency</div>
								<div class="colorItRed <?php echo ($row['send_emails']==0)?'jshowIt':'jhideIt'; ?>">Invoices and Certificates are posted to Agency</div>
							</td>
						</tr>
						<tr>
							<td>Send Combined Certificate and Invoice</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_combined_invoice' value='1' <?php echo $row['send_combined_invoice'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_combined_invoice' value='0' <?php echo  $row['send_combined_invoice'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_combined_invoice']==1)?'jshowIt':'jhideIt'; ?>">Agency Receives a Combined Invoice and Certificate</div>
								<div class="colorItRed <?php echo ($row['send_combined_invoice']==0)?'jshowIt':'jhideIt'; ?>">Agency Receives a separate Invoice and Certificate</div>
							</td>
						</tr>
						<tr>
							<td>Entry Notice issued by Email</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_entry_notice' value='1' <?php echo $row['send_entry_notice'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_entry_notice' value='0' <?php echo $row['send_entry_notice'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_entry_notice']==1)?'jshowIt':'jhideIt'; ?>">Entry Notices by Email allowed</div>
								<div class="colorItRed <?php echo ($row['send_entry_notice']==0)?'jshowIt':'jhideIt'; ?>">Entry Notices MUST be Posted</div>
							</td>
						</tr>
						<tr>
							<td>Work Order Required For All Jobs?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='work_order_required' value='1' <?php echo $row['require_work_order'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='work_order_required' value='0' <?php echo $row['require_work_order'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['require_work_order']==1)?'jshowIt':'jhideIt'; ?>">Work order number required for all jobs</div>
								<div class="colorItRed <?php echo ($row['require_work_order']==0)?'jshowIt':'jhideIt'; ?>">NO work order number required</div>
							</td>
						</tr>
						<tr>
							<td>Allow Individual Property Managers?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_indiv_pm' value='1' <?php echo $row['allow_indiv_pm'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_indiv_pm' value='0' <?php echo $row['allow_indiv_pm'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['allow_indiv_pm']==1)?'jshowIt':'jhideIt'; ?>">Agency can assign Property Managers to properties</div>
								<div class="colorItRed <?php echo ($row['allow_indiv_pm']==0)?'jshowIt':'jhideIt'; ?>">Agency CAN'T assign Property Managers to properties</div>
							</td>
						</tr>
						<tr>
							<td>Auto Renew Yearly Maintenance Properties</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='auto_renew' value='1' <?php echo $row['auto_renew'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='auto_renew' value='0' <?php echo $row['auto_renew'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['auto_renew']==1)?'jshowIt':'jhideIt'; ?>">Agency Allows Auto Renew</div>
								<div class="colorItRed <?php echo ($row['auto_renew']==0)?'jshowIt':'jhideIt'; ?>">Agency DOESN'T allow Auto Renew</div>
							</td>
						</tr>
						<tr>
							<td>Key Access Allowed?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='key_allowed' value='1' <?php echo $row['key_allowed'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='key_allowed' value='0' <?php echo $row['key_allowed'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['key_allowed']==1)?'jshowIt':'jhideIt'; ?>">Key access allowed</div>
								<div class="colorItRed <?php echo ($row['key_allowed']==0)?'jshowIt':'jhideIt'; ?>">Key access NOT allowed</div>
							</td>
						</tr>
						<tr>
							<td>Tenant Key Email Required?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='key_email_req' value='1' <?php echo $row['key_email_req'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='key_email_req' value='0' <?php echo $row['key_email_req'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['key_email_req']==1)?'jshowIt':'jhideIt'; ?>">Agency wants email from Tenant to approve keys</div>
								<div class="colorItRed <?php echo ($row['key_email_req']==0)?'jshowIt':'jhideIt'; ?>">No email from Tenant required</div>
							</td>
						</tr>						
						
						<tr>
							<td>Allow Doorknocks?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_dk' value='1' <?php echo $row['allow_dk'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_dk' value='0' <?php echo $row['allow_dk'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['allow_dk']==1)?'jshowIt':'jhideIt'; ?>">Door Knocks allowed</div>
								<div class="colorItRed <?php echo ($row['allow_dk']==0)?'jshowIt':'jhideIt'; ?>">NO Door Knocks allowed</div>
							</td>
						</tr>
						<tr>
							<td>Allow Entry Notice?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_en' value='1' <?php echo $row['allow_en'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_en' value='0' <?php echo $row['allow_en'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_en' value='-1' <?php echo $row['allow_en'] == -1 ? 'checked' : ''; ?>> NR</div>
							</td>
							<td>								
								<div class="colorItGreen <?php echo ($row['allow_en']==1)?'jshowIt':'jhideIt'; ?>">Entry Notices are Allowed</div>
								<div class="colorItRed <?php echo ($row['allow_en']==0)?'jshowIt':'jhideIt'; ?>">NO Entry Notices are Allowed</div>
								<div class="colorItRed pref_nr <?php echo ($row['allow_en']==-1)?'jshowIt':'jhideIt'; ?>">No Response in regards to Entry Notice</div>
							</td>
						</tr>
						<tr>
							<td>All New Jobs Emailed to Agency?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='new_job_email_to_agent' value='1' <?php echo $row['new_job_email_to_agent'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='new_job_email_to_agent' value='0' <?php echo $row['new_job_email_to_agent'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['new_job_email_to_agent']==1)?'jshowIt':'jhideIt'; ?>">Agency Receives email for ALL new properties</div>
								<div class="colorItRed <?php echo ($row['new_job_email_to_agent']==0)?'jshowIt':'jhideIt'; ?>">Agency DOESN'T Receive email for ALL new properties</div>
							</td>
						</tr>
						<tr>
							<td>Display BPAY on Invoices?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='display_bpay' value='1' <?php echo $row['display_bpay'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='display_bpay' value='0' <?php echo $row['display_bpay'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['display_bpay']==1)?'jshowIt':'jhideIt'; ?>">BPAY displayed on invoices</div>
								<div class="colorItRed <?php echo ($row['display_bpay']==0)?'jshowIt':'jhideIt'; ?>">BPAY not displayed on invoices</div>
							</td>
						</tr>
					</table>
				
				
					
					
					</div>
					
					
					<div style="float:left;">
					<h5>Maintenance Program</h5>
					
					<table class="table">
					
						<tr>
							<td>Maintenance Provider</td>
							<td>
								<select style="width: 210px;" name="maintenance" class="maintenance" id="maintenance">
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
							</td>
						</tr>
						
						<tr class="maintenance_prog_row">
							<td>Apply Surcharge to all Invoices?</td>
							<td>
								<div class="eagdt-rd">
									<label for="send_emails_1"><input type="radio" class="addinput" title="Apply Surcharge to all Invoices" value="1" name="m_surcharge" <?php echo ($sel_m['surcharge']==1)?'checked="checked"':''; ?>> YES</label>
								</div>
								<div class="eagdt-rd">
									<label for="send_emails_0"><input type="radio" class="addinput" title="Apply Surcharge to all Invoices" value="0" name="m_surcharge" <?php echo ($sel_m['surcharge']==0)?'checked="checked"':''; ?>> NO</label>
								</div>
							</td>
						</tr>
						
						<tr class="maintenance_prog_row">
							<td>Display Message on all Invoices?</td>
							<td>
								<div class="eagdt-rd">
									<label for="send_emails_1"><input type="radio" class="addinput" title="Display Message on all Invoices" value="1" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==1)?'checked="checked"':''; ?>> YES</label>
								</div>
								<div class="eagdt-rd">
									<label for="send_emails_0"><input type="radio" class="addinput" title="Display Message on all Invoices" value="0" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==0)?'checked="checked"':''; ?>> NO</label>
								</div>
							</td>
						</tr>
						
						<tr class="maintenance_prog_row">
							<td>Surcharge $</td>
							<td>
								<input type="text" style="width: 200px; margin: 0;" class="addinput" name="m_price" id="m_price" title="Surcharge" value="<?php echo $sel_m['price']; ?>" />
							</td>
						</tr>
						
						<tr class="maintenance_prog_row">
							<td>Invoice Message</td>
							<td>								
								<textarea style="width: 193px !important;" class="addtextarea formtextarea agency_comments" name='m_surcharge_msg' id='m_surcharge_msg'><?php echo $sel_m['surcharge_msg']; ?></textarea>
							</td>
						</tr>
						
					</table>
				
			
					<div style="clear:both;"></div>
					
					
					</div>
					
					
					<div style="clear:both;"></div>
					<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin-top:10px;">Update Details</button>
					
				
				</div>
			</div>
			
			<input type='hidden' name='num_alarms' value='<?php echo sizeof($alarm_prices); ?>'>						
			<input type="hidden" id="fields_edited" name="fields_edited" />	
			<input type="hidden" name="pass_change" id="pass_change" value="0" />
			<input type="hidden" name="tot_prop_change" id="tot_prop_change" value="0">
			<input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>">
			<input type="hidden" name="doaction" value="<?php echo $doaction; ?>">
			
			<input type='hidden' name='agency_name_edited' id='agency_name_edited' value='0' />
			<input type='hidden' name='agency_emails_edited' id='agency_emails_edited' value='0' />
			<input type='hidden' name='account_emails_edited' id='account_emails_edited' value='0' />
			
			</form>
			
			<!-- LOGS -->
			<div class="c-tab tab_agency_logs" data-tab_cont_name="agency_logs">
				<div class="c-tab__content">
				
				<?php
				$vip = array(11,58,2032,17,12,2025,2085,2097,2123,2056,2156,2178);
				if (in_array($staff_id, $vip)){
					$temp_str = '';
				}else{
					$temp_str = "
						AND c.`contact_type` != 'Phone Call - Accounts'
						AND c.`contact_type` != 'Email - Accounts'
						AND c.`contact_type` != 'Other - Accounts'
					";						
				}
				
				 $Query = "
				SELECT c.`contact_type`, 
					   c.`eventdate`, 
					   c.`comments`,
					   c.`agency_event_log_id`,
					   sa.`FirstName`,
					   sa.`LastName`,
					   c.`next_contact`,
					   c.`important`
				FROM `agency_event_log` AS c
				LEFT JOIN `staff_accounts` AS sa ON c.`staff_id` = sa.`StaffID`
				WHERE (agency_id = $agency_id) 
				{$temp_str}
				ORDER BY c.`agency_event_log_id` DESC";

				// (2) Run the query 
				  

				//	echo "Query is <br>$Query\n";
				$result = mysql_query ($Query, $connection);
				?>
				
				<form method="post" name="form_agency_logs" id="form_agency_logs">
				<table border=0 cellspacing=1 cellpadding=5 style="width: 100%;">
				

				<tr valign=middle bgcolor="white" style="border: medium none;">

					<td align=left>
					<label for="eventdate" style="display: block; float: none;">Date</label>
					<input type="text" name="eventdate" class="datepicker addinput agency_logs_input" value="<?php echo date("d/m/Y"); ?>">
					</td>

					<td>
					<label for="contact_type" style="display: block; float: none;">Contact Type</label>
					<select name="contact_type" class="fselect agency_logs_input" style="width:157px;">
						<option value="Phone Call">Phone Call</option>
						<option value="E-mail">E-mail</option>
						<option value="Mailout">Mail-Out</option>
						<option value="Other">Other</option>
						<option value="Pack Sent">Pack Sent</option>   
						<option value="Meeting">Meeting</option> 
						<option value="Follow up">Follow Up</option> 
						<?php
						//$vip = array(11,58,2032,17,12,2025,2059);
						if (in_array($staff_id, $vip)){ ?>
							<option value="Phone Call - Accounts">Phone Call - Accounts</option>   
							<option value="Email - Accounts">Email - Accounts</option> 
							<option value="Other - Accounts">Other - Accounts</option> 
						<?php	
						}
						?>						  					
					</select>
					</td>

					<td style="width: 50%;">				
						<textarea name="comments" lengthcut="true" class="addtextarea vpr-adev-txt" style="width: 95%;"></textarea>
					</td>
					
					<td>
						<label for="next_contact" style="display: block; float: none;">Next Contact</label>
						<input type="text" name="next_contact" class="datepicker addinput agency_logs_input" />	
					</td>
					
					<td>
						<label for="next_contact" style="display: block; float: none;">Important</label>
						<input type="checkbox" value="1" style="display: block; margin-top: 8px;" name="important" id="important">
					</td>
				
					<td align=left>
						<input type="hidden" name="doaction" value="addevent" class="submitbtnImg">
						<input type="submit" value="Add Event" class="submitbtnImg">
					</td>

				</tr>

				</table>
				</form>	
			
				
			<?php
			if( mysql_num_rows($result) != 0 ) { 
			$odd=0;
			?>

			<table>

				<tr bgcolor=#b4151b style='border-top: 1px solid #cccccc!important; border-bottom: 1px solid #cccccc!important;'>
					<td class='colorwhite bold'>Date</td>
					<td class='colorwhite bold'>Type</td>
					<td class='colorwhite bold'>Staff Member</td>
					<td class='colorwhite bold' style='width: 54%;'>Comments</td>
					<td class='colorwhite bold'>Next Contact</td>
					<!--<td class='colorwhite bold'>Delete</td>-->
				</tr>
				

				<?php
				// (3) While there are still rows in the result set,
				// fetch the current row into the array $row
				while ($row = mysql_fetch_array($result)){
				$odd++;			  
				if (is_odd($odd)) {
					$bg_color = 'white_bg_color';
				} else {
					$bg_color = 'grey_bg_color';
				}				
				// red highlight for important
				if( $row['important']==1 ){
					$bg_color = 'important_bg_color';
				}	
				?>			   
				<tr class="<?php echo $bg_color; ?>">

					<td><?php echo date('d/m/Y',strtotime($row['eventdate'])); ?></td>
					<td><?php echo $row['contact_type']; ?></td>					
					<td><?php echo "{$row['FirstName']} {$row['LastName']}" ?></td>					
					<td><?php echo $row['comments']; ?></td>					
					<td><?php echo ($row['next_contact']!='0000-00-00')?date("d/m/Y",strtotime($row['next_contact'])):''; ?></td>
					<!--<td>	
						<a href="?id=<?php echo $agency_id; ?>&cid=<?php echo $row['agency_event_log_id']; ?>&doaction=delete" onclick="return confirm('Are you sure you want to Delete this event?')">Delete</a>
					</td>
					-->
				</tr>
				<?php
				}
				?>

			</table>
				
			<?php			
			} 
			?>





				
			
				
				</div>
			</div>
			
			<!-- FILES -->
			<div class="c-tab" data-tab_cont_name="agency_files">
				<div class="c-tab__content">
				
				
				<div style="float: left;">
					<h2 class="heading">Agency Files</h2>
					<form action="#uploads" enctype="multipart/form-data" method="post">
					<p style="float: left;">
						<input type="file" name="fileupload" class="submitbtnImg"> 
						<input type="submit" value="Upload NOw" class="submitbtnImg">
					</p>
					</form>
					
					<?php
			
					 # Get Property uploaded Files
					$property_files = getPropertyFiles2($agency_id); 

					if(sizeof($property_files) == 0)
					{
						echo "<div style='text-align: left; font-size: 13px;'>This Agency Has NO Uploaded Files. Upload One Below</div>";
					}
					else {
					
						// path
						//$upload_path = $_SERVER['DOCUMENT_ROOT'].'agency_files/';
					
						echo "<ul class='vad-btmfld'>";
						foreach($property_files as $file)
						{
							echo "<li><a href='/agency_files/". $agency_id . "/" . $file . "' target='_blank'>" . $file . "</a> - <a href='?id=" . $agency_id . "&delfile=" . rawurlencode($file) . "#uploads' class='delfile'>Delete</a></li>";				
						}
						echo "</ul>";
					}
					
					?>
					<script type="text/javascript">

					$('a.delfile').live('click', function() {
							
									var d_confirm = confirm("Are you sure you want to Delete this File?");
									if(d_confirm) {
										return true;
									}
									else
									{
										return false;
									}
							
								});		

					</script>
				</div>
				
				
				
				<div style="float: right;">
		
					<h2 class="heading">Contractor Appointment Form</h2>

					<?php if( $_REQUEST['ca_upload_success']==1 ){ ?>
						<div class='success'>File Uploaded Successfully</div>
					<?php	
					} ?>
					
					<?php if( $_REQUEST['ca_del_success']==1 ){ ?>
						<div class='success'>File Deleted Successfully</div>
					<?php	
					} ?>
					
					
					
					<form action="<?php echo "/upload_contractor_appointment.php" ?>#uploads" enctype="multipart/form-data" method="post">
					<p style="float: left;">
						<input type="file" capture="camera" name="upload_cont_app_frm" class="submitbtnImg"> <input type="submit" value="Upload NOw" class="submitbtnImg">			
						<input type="hidden" name="hid_agency_id" value="<?php echo $agency_id; ?>" />
					</p>
					</form>
					
					
					<div>
					<?php
					$jparams = array(
						'agency_id' => $agency_id,
						'country_id' => $country_id
					);
					$ca_sql = $crm->getContractorAppointment($jparams);
					if( mysql_num_rows($ca_sql)>0 ){						
					?>
					<table class="caf_table">
						<?php
						while( $ca = mysql_fetch_array($ca_sql) ){ ?>
							<tr>
								<td><a href="<?php echo $ca['file_path']; ?>" target="_blank"><?php echo $ca['file_name']; ?></a></td>
								<td>-</td>
								<td><a class="caf_delete" data-ca_id="<?php echo $ca['contractor_appointment_id']; ?>" href="javascript:void(0);" >Delete</a></td>
							</tr>
						<?php	
						}
						?>			
					</table>
					<?php
					}
					?>
					</div>
				
				</div>
				
				
				
				
				</div>
			</div>
			
			<!-- PROPERTIES -->
			<div class="c-tab" data-tab_cont_name="properties">
				<div class="c-tab__content">
				
				
					<form method="POST">    
						
						<div class="aviw_drop-h" style="background-color:white;">
						
						  <div class="fl-left">
							<label>Search Phrase:</label>
							<input class="addinput searchstyle" type="text" name="search" size=10 value="<?php echo $search; ?>">
						  </div>
						  
						  <div class="fl-left">
							<label>Property Status:</label>
							<select name="status" id="status">
							<option value="">--</option>
							<option value="0" <?php echo($status!=""&&$status==0)?'selected="selected"':''; ?>>Active</option>
							 <option value="1" <?php echo($status!=""&&$status==1)?'selected="selected"':''; ?>>Deleted</option>
						  </select>
						  </div>
						  
						  <div class="fl-left">
							<input type="hidden" name="id" value="<?php echo $agency_id; ?>">
							<input type="hidden" name="tab" value="crm">
							<input type="submit" name="btn_search" value="Search" class="submitbtnImg">			
						  </div>
						  
						  <div class="fl-left">
							<a href='/export_agency_properties.php?agency_id=<?php echo $agency_id; ?>'>
								<button type="button" class="submitbtnImg">Export</button>
							</a>
						  </div>						  					
						  
						</div>
						
					</form>
		

				
				<table border=1 cellpadding=0 cellspacing=0 width=100% class="table-left vad-outer-table">
				<tr>
				<td class="padding-none">

				<? 

				// get property list
				function get_property_list($agency_id,$get_all=0,$offset,$limit,$search,$sort='address_2',$order_by='ASC',$status){

				$str = "";

				if($search!=""){
				//$str .= "AND CONCAT( `address_1` , `address_2` , `address_3` ) LIKE '%{$search}%' ";
				$str .= " AND ( CONCAT_WS( ' ', LOWER(`address_1`), LOWER(`address_2`), LOWER(`address_3`) ) LIKE LOWER('%{$search}%') )";
				}

				if($status!=""){
				$str .= "AND `deleted` = {$status}";
				}

				if($get_all==1){
				$str .= "";
				}else{
				$str .= "
				ORDER BY {$sort} {$order_by}
				LIMIT {$offset}, {$limit}";
				}

				$sql = "
				SELECT *
				FROM `property`
				WHERE `agency_id` = {$agency_id}
				{$str}
				";
				//echo $sql;
				return mysql_query($sql);

				}

				// get services
				function get_services($property_id,$alarm_job_type_id){
				$service = '';
				$ps_sql = mysql_query("
				SELECT *
				FROM `property_services` 
				WHERE `property_id` = {$property_id}
				AND `alarm_job_type_id` = {$alarm_job_type_id}
				");
				if(mysql_num_rows($ps_sql)>0){
				$s = mysql_fetch_array($ps_sql);
				$service = $s['service'];
				switch ($service) {
				case 0:
					$service = 'DIY';
					break;
				case 1:
					$service = 'SATS';
					break;
				case 2:
					$service = 'NO Response';
					break;
				case 3:
					$service = 'Other Provider';
					break;
				}		
				}else{
				$service = "N/A";
				}
				return $service;
				}

				// header sort parameters
				$sort = $_REQUEST['sort'];
				$order_by = $_REQUEST['order_by'];

				$sort = ($sort)?$sort:'address_2';
				$order_by = ($order_by)?$order_by:'ASC';


				// pagination script
				$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
				$limit = 1;
				$this_page = $_SERVER['PHP_SELF'];
				$params = "&id={$agency_id}&tab=crm&sort={$sort}&order_by={$order_by}&search={$search}&status={$status}";

				$next_link = "{$this_page}?offset=".($offset+$limit).$params;
				$prev_link = "{$this_page}?offset=".($offset-$limit).$params;



				$result = get_property_list($agency_id,0,$offset,$limit,$search,$sort,$order_by,$status);
				$ptotal = mysql_num_rows(get_property_list($agency_id,1,'','',$search,'','',$status));	





				// sort
				if($_GET['order_by']){
				if($_GET['order_by']=='ASC'){
				$ob = 'DESC';
				$sort_arrow = '<div class="arw-std-up arrow-top-active"></div>';
				}else{
				$ob = 'ASC';
				$sort_arrow = '<div class="arw-std-dwn arrow-dwn-active"></div>';
				}
				}else{
				$sort_arrow = '<div class="arw-std-up"></div>';
				$ob = 'ASC';
				}

				echo "<table border=0 cellspacing=1 cellpadding=5 width=100%>";
				?>

				<tr class="rowred">
				<?php 
				// default active
				$active = ($_GET['sort']=="")?'arrow-top-active':''; 
				?>
				<td width="350"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $agency_id; ?>&tab=crm&sort=address_2&order_by=<?php echo ($_GET['sort']=='address_2')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Address</div> <?php echo ($_GET['sort']=='address_2')?$sort_arrow:'<div class="arw-std-up  '.$active.'"></div>'; ?></a></td>
				<td width="150"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $agency_id; ?>&tab=crm&sort=address_3&order_by=<?php echo ($_GET['sort']=='address_3')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Suburb</div> <?php echo ($_GET['sort']=='address_3')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></td>
				<td width="135"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $agency_id; ?>&tab=crm&sort=state&order_by=<?php echo ($_GET['sort']=='state')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold"><?php echo getDynamicStateViaCountry($_SESSION['country_default']) ?></div> <?php echo ($_GET['sort']=='state')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></td>
				<?php
					// get agency services
					$as_sql = mysql_query("
						SELECT *, ajt.`id` AS ajt_id
						FROM `agency_services` AS as2
						LEFT JOIN `alarm_job_type` AS ajt ON as2.`service_id` = ajt.`id` 
						WHERE as2.`agency_id` ={$agency_id}
					");
					while($as = mysql_fetch_array($as_sql)){ 
						
						switch($as['ajt_id']){
							case 2:
								$serv_color = 'b4151b';
								$serv_icon = 'smoke_white.png';
							break;
							case 5:
								$serv_color = 'f15a22';
								$serv_icon = 'safety_white.png';
							break;
							case 6:
								$serv_color = '00ae4d';
								$serv_icon = 'corded_white.png';
							break;
							case 7:
								$serv_color = '00aeef';
								$serv_icon = 'pool_white.png';
							break;
							case 8:
								$serv_color = '9b30ff';
								$serv_icon = 'sa_ss_white.png';
							break;
							case 9:
								$serv_color = '9b30ff';
								$serv_icon = 'sa_cw_ss_white.png';
							break;
						}
						
					?>
						<td width="100"><img src="images/serv_img/<?php echo $serv_icon; ?>" /></td>
					<?php
					}
				?>				
				<td width="100"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $agency_id; ?>&tab=crm&sort=deleted&order_by=<?php echo ($_GET['sort']=='deleted')?$ob:'ASC'; ?>"><div class="tbl-tp-name colorwhite bold">Status</div> <?php echo ($_GET['sort']=='deleted')?$sort_arrow:'<div class="arw-std-up"></div>'; ?></a></td>
				<td style="width: 22px;"><input type="checkbox" id="prop_check_all" /></td>
				</tr>

				<?php 



				// (2) Run the query



				$odd=0;

				// (3) While there are still rows in the result set,
				// fetch the current row into the array $row
				// list all properties from database for this agency
				while ($row = mysql_fetch_array($result)){ 
				if(is_odd($odd)){
				$bg_color = '#cccccc'; 
				}else{
				$bg_color = '#ffffff'; 
				}
				?>
				<tr class="<?php echo $bg_color; ?>">



				<td style='text-align: left;!important'>		
					<a href='view_property_details.php?id=<?php echo $row['property_id'] ?>'><?php echo "{$row['address_1']} {$row['address_2']}"; ?></a>
				</td>

				<td style='text-align: left;!important'><?php echo $row['address_3']; ?></td>

				<td style='text-align: left;!important'><?php echo $row['state']; ?></td>

				<?php
				// get agency services
				$as_sql = mysql_query("
					SELECT *
					FROM `agency_services` AS as2
					LEFT JOIN `alarm_job_type` AS ajt ON as2.`service_id` = ajt.`id` 
					WHERE as2.`agency_id` ={$agency_id}
				");
				while($as = mysql_fetch_array($as_sql)){ ?>
					<td style='text-align: left;!important'><?php echo get_services($row['property_id'],$as['service_id']); ?></td>
				<?php
				}		
				?>

				<td style='text-align: left;!important'><?php echo  ($row['deleted']==1)?'Deleted':'Active'; ?></td>				

				<td><input type="checkbox" class="prop_chk" name="prop_chk[]" value="<?php echo $row['property_id']; ?>" /></td>
							
				</tr>


				<?php

				}


				echo "</table>";
				// (5) Close the database connection


				?>
				
				
				
				<div style="text-align: center;">
				<?php

				// Initiate pagination class
				$jp = new jPagination();
				
				$per_page = $limit;
				$page = ($_GET['page']!="")?$_GET['page']:1;
				$offset = ($_GET['offset']!="")?$_GET['offset']:0;	
				
				echo $jp->display($page,$ptotal,$per_page,$offset,$params);
				
				?>
				</div>
				
				
				<div id="change_agency_div" style="display:none;">
	
					<div style="float:right">
						<button type="button" class="submitbtnImg" id="btn_change_agency">Change Agency</button>
					</div>
					<div style="float:right; margin-right: 20px;">
						<select id="sel_agency" style="width: 150px;">
							<option value="">-- Select --</option>
							<?php
							$a_sql = mysql_query("
								SELECT `agency_id`, `agency_name`
								FROM `agency`
								WHERE `status` = 'active'
								AND `country_id` = {$_SESSION['country_default']}
								AND `agency_id` != {$_GET['id']}
								ORDER BY `agency_name`
							");
							while( $a = mysql_fetch_array($a_sql) ){ ?>
								<option value="<?php echo $a['agency_id']; ?>"><?php echo $a['agency_name']; ?></option>
							<?php	
							}
							?>
							
						</select>
					</div>
					
				</div>
				
				
				

				
				
				</div>
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
		
		
	</div>
	

  </div>
	


<script type="text/javascript" >
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
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}








jQuery(document).ready(function(){
	
	
	
	// submission
	jQuery(".btn-update").click(function(){
		
		//alert('test trigger');
	
		
		var agency_name = jQuery("#agency_name").val();
		var region = jQuery("#region_id").val();
		var agency_emails = jQuery("#agency_emails").val();
		var account_emails = jQuery("#account_emails").val();
		var error = "";
		var flag = 0;
		
		if(agency_name==""){
			error += "Agency Name is required\n";
		}
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
			//alert('submit form');
			jQuery("#jform").submit();
		}
		
		
	});
	
	
	// require approve price script
	jQuery(document).on("click",".approve",function(){
		
		// is approved hidden value
		var state = jQuery(this).prop("checked");
		if(state==true){				
			jQuery(this).parents("tr:first").removeClass("fadedText");
		}else{	
			jQuery(this).parents("tr:first").addClass("fadedText");			
		}
		
	});
	
	
	
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
	
	
	// salesrep edited marker
	jQuery("#salesrep").change(function(){		
		jQuery("#salesrep_edited").val(1);		
	});
	
	
	
	// field edited script, to know what field is edited to be included in the logs, WIP try and get all fields
	jQuery(".addinput").change(function(){

		var fields_edited = jQuery("#fields_edited").val();
		var field = jQuery(this).attr("title");
		if(fields_edited.search(field)==-1){
			console.log('already exist');
			var comb = fields_edited+","+field;
			jQuery("#fields_edited").val(comb);
		}
		
	  
	});
	
	
	
	// maintenance hide/show toggle
	jQuery("#maintenance").change(function(){
		
		if(jQuery(this).val()!=""){
			jQuery(".maintenance_prog_row").show();
		}else{
			jQuery(".maintenance_prog_row").hide();
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
	
	
	
	// delete PM
	jQuery(document).on("click",".btn_delete",function(){
		
		var parent_row = jQuery(this).parents("tr:first");
		var pm_id = parent_row.find(".pm_id").val();
		
		if( pm_id!='' ){
			
			//alert('PM id present');
			if( confirm("Are you sure you want to delete?") ){
				jQuery.ajax({
					type: "POST",
					url: "ajax_delete_property_manager.php",
					data: { 
						pm_id: pm_id,
					}
				}).done(function( ret ){
					if(parseInt(ret)==1){
						alert("There are properties attached to this PM");
					}else{
						parent_row.remove();
					}
					
				});	
			}
			
		}else{
			//alert('PM id not found');
			parent_row.remove();
		}
		
	});
	
	
	
	// add property manager
	jQuery("#btn_add").click(function(){
		// clone last PM row
		var pm_last = jQuery("#pm_table tr:last").clone();
		// clear  values
		pm_last.find(".pm_id").val('');
		pm_last.find(".pm_name").val('');
		pm_last.find(".pm_email").val('');
		jQuery("#pm_table tbody").append(pm_last);
	});
	
	
	
	// preferences text script
	jQuery(".agency_pref_tab_content input[type='radio']").click(function(){

		var obj = jQuery(this);
		var pref_radio = obj.val();
		//console.log(pref_radio);
		if( pref_radio == 1 ){
			obj.parents("tr:first").find(".colorItGreen").show();
			obj.parents("tr:first").find(".colorItRed").hide();
			obj.parents("tr:first").find(".pref_nr").hide();
		}else if( pref_radio == 0 ){
			obj.parents("tr:first").find(".colorItGreen").hide();
			obj.parents("tr:first").find(".colorItRed").show();
			obj.parents("tr:first").find(".pref_nr").hide();
		}else if( pref_radio == -1 ){
			obj.parents("tr:first").find(".colorItGreen").hide();
			obj.parents("tr:first").find(".colorItRed").hide();
			obj.parents("tr:first").find(".pref_nr").show();
		}

	});
	
	
	
	
	// selects the previous tab on load
	var curr_tab = $.cookie('vad_tab_index');
	if( curr_tab!='' ){
		
		if(curr_tab!=''){
			myTabs.goToTab(curr_tab);
		}else{
			myTabs.goToTab(0);
		}
		
	}
	
	
	// keep tab script
	jQuery(".c-tabs-nav__link").click(function(){
		
		var tab_index = jQuery(this).attr('data-tab_index');
		console.log(tab_index);
		$.cookie('vad_tab_index', tab_index);
		
	});
	
	
	
	
	jQuery(".caf_delete").click(function(){

		if( confirm("Are you sure you want to delete?") ){
			
			var ca_id = jQuery(this).attr("data-ca_id");
			
			// invoke ajax
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_caf.php",
				data: { 
					ca_id: ca_id
				}
			}).done(function( ret ){
				window.location = "/view_agency_details.php?id=<?php echo $agency_id; ?>&ca_del_success=1";
			});	

		}
		
	});
	
	
	
	// check all toggle
	jQuery("#prop_check_all").click(function(){
  
	  if(jQuery(this).prop("checked")==true){
		jQuery(".prop_chk:visible").prop("checked",true);
		jQuery("#change_agency_div").show();
	  }else{
		jQuery(".prop_chk:visible").prop("checked",false);
		jQuery("#change_agency_div").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	jQuery(".prop_chk").click(function(){

	  var chked = jQuery(".prop_chk:checked").length;
	  
	  if(chked>0){
		jQuery("#change_agency_div").show();
	  }else{
		jQuery("#change_agency_div").hide();
	  }

	});
	
	// change agency script
	jQuery("#btn_change_agency").click(function(){
		
		if(confirm("Are you sure you want to change agency?")){
			
			var new_agency = jQuery("#sel_agency").val();
			var props = [];
			jQuery(".prop_chk:checked").each(function(){
				props.push(jQuery(this).val());	
			});
			
			// invoke ajax
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_property_agency.php",
				data: { 
					current_agency: <?php echo $_GET['id']; ?>,
					new_agency: new_agency,
					props: props
				}
			}).done(function( ret ){
				window.location = "/view_agency_details.php?id=<?php echo $_GET['id']; ?>&tab=crm";
			});	
			
		}
		
	});
	
});
parseCharCounts();
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
