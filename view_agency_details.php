<?

include('inc/init.php');

//Redirect to CI > BY GHERX
	$domain = $_SERVER['SERVER_NAME'];
	$au_country_pattern = "/au/";
	$country_match =  preg_match($au_country_pattern, $domain);

	if ($country_match) { // AU
		// go to NZ
		$country_iso_txt = 'NZ';

		if (strpos($domain, "crmdev") !== false) { // DEV
			$crm_ci_link = 'https://crmdevci.sats.com.au';
		} else { // LIVE
			$crm_ci_link = 'https://crmci.sats.com.au';
		}
	} else { // NZ
		// go to AU
		$country_iso_txt = 'AU';

		if (strpos($domain, "crmdev") !== false) { // DEV
			$crm_ci_link = 'https://crmdevci.sats.co.nz';
		} else { // LIVE
			$crm_ci_link = 'https://crmci.sats.co.nz';
		}
	}

	$page_params = "";
	$page = "/agency/view_agency_details/{$_REQUEST['id']}";
	$ci_link = "{$crm_ci_link}/login/authenticate/?staff_id={$_SESSION['USER_DETAILS']['StaffID']}&page=" . rawurlencode($page) . "&page_params=" . rawurlencode($page_params);
	
	//header("Location: ".$ci_link); #disabled header location > update all links instead as per Ben's request
	//exit();
//Redirect to CI end

$title = "Agency Details";

include('inc/header_html.php');
include('inc/menu.php');
include('inc/agency_services_class.php');
include('inc/agency_alarms_class.php');
include('inc/agency_class.php');

// 2189 - NZ staff
$vip = array(11,12,58,2025,2056,2070,2124,2156,2178,2189,2190,2239,2259);

// invoke class
$agency = new Agency_Class();
$crm = new Sats_Crm_Class;
$agency_api = new Agency_api;

$encrypt = new cast128();
$encrypt->setkey(SALT);
 

$agency_id = $_REQUEST['id'];
$tab = $_REQUEST['tab'];
$search = $_REQUEST['search'];
$status = $_REQUEST['status'];
$p_deleted = $_REQUEST['status']; // added for a better variable name

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$user_type = $_SESSION['USER_DETAILS']['ClassID'];

// Vanessa Halfpenny , Developer Testing, Daniel Kramarzewski, Jacinta Wawatai
if ($_SESSION['country_default'] == 1){ // AU
	// 11 - Vanessa Halfpenny
	// 2070 - Developer Testing
	// 2025 - Daniel Kramarzewski
	// 2175 - Thalia Paki
	// 2312 - Jacinta Wawatai
	$allowed_people_to_pme_unlink = array(11,2070,2025,2175,2312);
}else if ($_SESSION['country_default'] == 2){ // NZ
	// 11 - Vanessa Halfpenny
	// 2070 - Developer Testing
	// 2025 - Daniel Kramarzewski
	// 2193 - Thalia Paki
	// 2245 - Jacinta Wawatai
	$allowed_people_to_pme_unlink = array(11,2070,2025,2193,2245);
}	

autoUpdateAgencyRegion($agency_id);


function getSatsToServicePropertyServices($agency_id){

	$sql = mysql_query("
		SELECT count(ps.`property_services_id`) AS jcount
		FROM `property_services` AS ps 
		LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
		WHERE ps.`service` = 1 
		AND p.`agency_id` = {$agency_id} 
		AND p.deleted = 0
	");
	$row = mysql_fetch_array($sql);
	return $row['jcount'];

}



function getActivePropCount($agency_id){
	
	$sql = mysql_query("
		SELECT count(p.`property_id`) AS jcount
		FROM `property` AS p 
		WHERE p.`agency_id` = {$agency_id} 
		AND p.`deleted` = 0
	");
	$row = mysql_fetch_array($sql);
	return $row['jcount'];

}


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
	
	$log_comments =  mysql_real_escape_string($_POST['comments']);
	$_POST = addSlashesData($_POST);
	$_POST['eventdate'] = convertDate($_POST['eventdate']);
	$next_contact = ($_POST['next_contact']!="")?date("Y-m-d",strtotime(str_replace("/","-",$_POST['next_contact']))):'';
	// insert the event into the database
	   
	$important = ($_POST['important']!="")?1:0;

   $eventdate = convertDate($eventdate);
   // (2) Run the query 
   $insertQuery = "INSERT INTO `agency_event_log` (contact_type,eventdate,comments,agency_id,`staff_id`,`next_contact`, `important` ) VALUES 
   ('" . $_POST['contact_type'] . "','" . $_POST['eventdate'] . "','{$log_comments}','{$_GET['id']}','{$staff_id}','{$next_contact}', '".$important."' );";
	
	// echo "insertQuery is <br>$insertQuery<br>\n";

	mysql_query($insertQuery);
	
	if (mysql_affected_rows($connection) == 0){
		echo "An error occurred creating the event, please report\n";
	}
	
	// sales snapshot
	$add_to_snapshot = mysql_real_escape_string($_POST['add_to_snapshot']);
	$ss_status = mysql_real_escape_string($_POST['ss_status']);
	$total_prop = mysql_real_escape_string($_POST['total_prop']);
	if( $add_to_snapshot == 1 && $ss_status !='' ){

		$ss_str = "
			INSERT INTO 
			`sales_snapshot`(
				`agency_id`,
				`properties`,
				`sales_snapshot_status_id`,
				`details`,
				`date`,
				`sales_snapshot_sales_rep_id`,
				`country_id`
			)
			VALUES(
				".mysql_real_escape_string($agency_id).",
				'".mysql_real_escape_string($total_prop)."',
				'".mysql_real_escape_string($ss_status)."',
				'".mysql_real_escape_string($log_comments)."',
				'".date('Y-m-d H:i:s')."',
				".mysql_real_escape_string($staff_id).",
				{$_SESSION['country_default']}
			)
		";
		mysql_query($ss_str);
		
	}	
		
	   
		
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
	$append_text2 = "{$append_text}" . $files_arr['fileupload']['name'];
	
	if(move_uploaded_file($files_arr['fileupload']['tmp_name'], $upload_path . $agency_id ."/".$append_text2 ))
	{
		// appended - insert logs
		mysql_query("
			INSERT INTO 
			`agency_event_log` 
			(
				`contact_type`,
				`eventdate`,
				`comments`,
				`agency_id`,
				`staff_id`,
				`date_created`,
				`hide_delete`
			) 
			VALUES (
				'File Upload',
				'".date('Y-m-d')."',
				'".mysql_real_escape_string($append_text2)." Uploaded',
				'{$agency_id}',
				'".$_SESSION['USER_DETAILS']['StaffID']."',
				'".date('Y-m-d H:i:s')."',
				1
			);
		");
		
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
		
		// appended - insert logs
		mysql_query("
			INSERT INTO 
			`agency_event_log` 
			(
				`contact_type`,
				`eventdate`,
				`comments`,
				`agency_id`,
				`staff_id`,
				`date_created`,
				`hide_delete`
			) 
			VALUES (
				'File Upload',
				'".date('Y-m-d')."',
				'".mysql_real_escape_string($_GET['delfile'])." Deleted',
				'{$agency_id}',
				'".$_SESSION['USER_DETAILS']['StaffID']."',
				'".date('Y-m-d H:i:s')."',
				1
			);
		");
		
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=2'</script>";
		//echo "<div class='success'>File Deleted Successfully</div>";
	}
	else
	{
		echo "<script>window.location='/view_agency_details.php?id={$_GET['id']}&upload_success=0'</script>";
		//echo "<div class='error'>Technical Problem. Please Try Again</div>";
	}
	
	
}


# Process Activation
if( $agency_id!='' && $_GET['activate']==1 ){
	
	// get agency
	$agen_sql = mysql_query("
		SELECT *, 
			fg.`name` AS fg_name, 
			ar.`agency_region_name` AS ar_name,
			c.`country` AS c_name,
			a.`abn` AS a_abn
		FROM `agency` AS a
		LEFT JOIN `franchise_groups` AS fg ON a.`franchise_groups_id` = fg.`franchise_groups_id`
		LEFT JOIN `agency_regions` AS ar ON a.`agency_region_id` = ar.`agency_region_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE a.`agency_id` = {$agency_id}
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	$agen = mysql_fetch_array($agen_sql);	
	
	$agency_name = $agen['agency_name'];
	$street_number = $agen['address_1'];
	$street_name = $agen['address_2'];
	$suburb = $agen['address_3'];
	$phone = $agen['phone'];
	$state = $agen['state'];
	$postcode = $agen['postcode'];
	$region = $agen['postcode_region_id'];
	$totprop = $agen['tot_properties'];
	$ac_fname = $agen['contact_first_name'];
	$ac_lname = $agen['contact_last_name'];
	$ac_phone = $agen['contact_phone'];
	$ac_email = $agen['contact_email'];
	$agency_emails = $agen['agency_emails'];
	$account_emails = $agen['account_emails'];
	$send_emails = $agen['send_emails'];
	$combined_invoice = $agen['send_combined_invoice'];
	$send_entry = $agen['send_entry_notice'];
	$workorder_required = $agen['require_work_order'];
	$allow_indiv_pm = $agen['allow_indiv_pm'];
	$auto_renew = $agen['agency_name'];
	$key_allowed = $agen['street_number'];
	$key_email_req = $agen['agency_name'];
	$salesrep = $agen['salesrep'];
	$phone_call_req = $agen['phone_call_req'];
	$legal_name = $agen['legal_name'];
	$abn = $agen['a_abn'];
	$acc_name = $agen['accounts_name'];
	$acc_phone = $agen['accounts_phone'];
	$allow_dk = $agen['allow_dk'];
	$allow_en = $agen['allow_en'];

	// send email
	$agency->send_mail(
		$agency_name,
		$street_number,
		$street_name,
		$suburb,
		$phone,
		$state,
		$postcode,
		$region,
		$totprop,
		$ac_fname,
		$ac_lname,
		$ac_phone,
		$ac_email,
		$agency_emails,
		$account_emails,
		$send_emails,
		$combined_invoice,
		$send_entry,
		$workorder_required,
		$allow_indiv_pm,
		$auto_renew,
		$key_allowed,
		$key_email_req,
		$salesrep,
		$phone_call_req,
		$legal_name,
		$abn,
		$acc_name,
		$acc_phone,
		$allow_dk,
		$allow_en
	);
	
	// update agency
	$query = "
	UPDATE agency 
	SET 
		status = 'active', 
		`send_emails` = 1,
		`send_combined_invoice` = 1,
		`send_entry_notice` = 0,
		`require_work_order` = 0,
		`allow_indiv_pm` = 1,
		`auto_renew` = 1,
		`key_allowed` = 1,
		`key_email_req` = 0,
		`phone_call_req` = 1,
		`allow_dk` = 1,
		`allow_en` = -1,
		`new_job_email_to_agent` = 0
	WHERE agency_id = {$agency_id }";
	mysql_query($query) or die(mysql_error());
	
	
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
	width: 364px;
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

.addinput .addtextarea .formtextarea .agency_comments{
	width: 552px
}

.tbl_servicing th{
	padding-top: 10px!important;
    padding-bottom: 10px!important;
}

.bigSign{
	z-index: 9999999!important;
    position: absolute;
    left: 40%;
    top: 20%;
}

.greyBgColor{
	background-color: #ECECEC;
}
.inner_icon{
	position: relative;
	top: 2px;
	margin-right: 3px;
}
.green-btn {
    background-color: #00ae4d !important;
}
.vad_tbl_row{
	border-top: 1px solid #cccccc!important; 
	border-bottom: 1px solid #cccccc!important;
	background: #b4151b;
}
.upload_add_div{
	text-align: left;
	margin-top: 15px;
}
.upload_form_div{
	float: left; 
	display:none;
}
.addinput.mm_prog {
    margin-right: 3px;
}
#add_to_snapshot{
	display: block; 
	margin-top: 8px; 
	float: left;
}
#ss_status{
	width: 108px;
	float: left;
	display: none;
}
.statement_note_txt{
	margin-left: 77px;
	font-size: 12px; 
	color: red;
}
#pm_table .addinput{
	margin: 0;
}
table.vjc-log tr.border-none td {
    border-bottom: 1px solid #CCCCCC !important;
    border-top: 1px solid #CCCCCC !important;
}
.login_icon.mail_icon {
    width: 20px;
}   
.inactive_agency_portal_user td{
	opacity: 0.35;
}
#api_integration_form{
	display: none;
}
.txt_hid, .action_div{
	display:none;
}
.deactivate_agency_reason_div{
	border: 2px solid #b4151b;
	padding: 10px;
	background:#fff;
}

.api_integ_div tr th, .api_integ_div tr td {
    padding: 5px 33px 5px 0;
}
.statement_agency_comment_div{
	float:left;
}
.statement_agency_comment_div .left_box{
	float:left;
	width:455px!important;
}
.statement_agency_comment_div .right_box{
	float: right;
	width: 100px;
	margin-top: 53px;
	margin-left:5px;
}
.statement_agency_comment_div .statements_agency_comments{
	display: block!important;
	width: 100%!important;
}
.statements_agency_comments_ts_span{
	color: #00d1e5;
	font-style: italic;
}

</style>

<?php	
$insertQuery = "
SELECT *,
	a.`password` AS a_pass,
	sa.FirstName AS sr_fname,
	sa.LastName AS sr_lname,
	fg.`name` AS fg_name,
	a.`abn` AS a_abn,
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
$agency_row = $row; // can use this better name for agency data
?>

    <div id="mainContent">	
	
	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first">
			<?php
			$agency_bc_title = ucfirst($row['status']);
			if( $row['status']=='active' ){
				$agency_bc_link = "view_agencies.php";
			}else{				
				$agency_bc_link = "view_{$row['status']}_agencies.php";
			}
			
			?>
			<a title="<?php echo $agency_bc_title; ?> Agencies" href="<?php echo $agency_bc_link; ?>">View <?php echo $agency_bc_title; ?> Agencies</a>			
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
	if($_GET['activate']==1){ ?>
	
		<div class="success">Agency is now Active</div>
	
	<?php	
	}
	?>
	
	<?php
	if($_GET['booking_notes']==1){ ?>
	
		<div class="success">New Booking Notes Added</div>
	
	<?php	
	}
	?>
	
	
	
	<?php
	if($_GET['booking_notes_updated']==1){ ?>
	
		<div class="success">Booking Notes Updated</div>
	
	<?php	
	}
	?>
	
	<?php
	if($_GET['bn_del_success']==1){ ?>
	
		<div class="success">Booking Notes Deleted</div>
	
	<?php	
	}
	?>


	<?php
	if($_GET['api_integ_add']==1){ ?>
	
		<div class="success">New API Integration Added</div>
	
	<?php	
	}
	?>


	<?php
	if($_GET['api_integ_update']==1){ ?>
	
		<div class="success">API Integration Updated</div>
	
	<?php	
	}
	?>

	<?php
	if($_GET['api_integ_deleted']==1){ ?>
	
		<div class="success">API Integration Deleted</div>
	
	<?php	
	}
	?>


	<?php
	if($_GET['api_token_deleted']==1){ ?>
	
		<div class="success">API Token Deleted</div>
	
	<?php	
	}
	?>


<?php
	if($_GET['unlink_pme_prop']==1){ ?>
	
		<div class="success">Connected PropertyMe Properties has been unlinked</div>
	
	<?php	
	}
	?>
	
	
	<?php
	if( $row['status']=='target' ){ 
		echo "<div id='permission_error'>This Agency is a Target Agency</div>";
	}else if( $row['status']=='deactivated' ){ 
		echo "<div id='permission_error'>This Agency is a Deactivated Agency</div>";
	}
	?>


	<?php
	// property has active jobs and cannot be deactivated
	$property_with_active_jobs = $_GET['property_with_active_jobs'];
	if( count( $property_with_active_jobs ) > 0 ){ 

		$property_with_active_jobs_imp = implode(",",array_filter($property_with_active_jobs));

		$property_with_active_jobs_str = "
		SELECT 
			`property_id`,
			`address_1`,
			`address_2`,
			`address_3`,
			`state`,
			`postcode`
		FROM `property`
		WHERE `property_id` IN({$property_with_active_jobs_imp})
		";
		$property_with_active_jobs_sql = mysql_query($property_with_active_jobs_str);

		if( mysql_num_rows($property_with_active_jobs_sql) > 0 ){

			echo "
			<div>
				<p>These Properties has an active jobs, cant be deactivated</p>
				<ul>";
				while( $pwaj = mysql_fetch_array($property_with_active_jobs_sql) ){
					echo "
					<li>
						<a href='/view_property_details.php?id={$pwaj['property_id']}' target='_blank'>
							{$pwaj['address_1']} {$pwaj['address_2']}, {$pwaj['address_3']} {$pwaj['state']} {$pwaj['postcode']}
						</a>
					</li>";
				}			
				echo "
				</ul>
			</div>";

		}
		
	}
	?>
	



	<div class='vw-pro-dtl-tn-hld vpr-left clear <?php echo ( $row['status']=='target' || $row['status']=='deactivated' )?'greyBgColor':''; ?>' style='float: none;  margin: 0;'>	

		<div id="tabs" class="c-tabs no-js">
		
			<div class="c-tabs-nav">
				<a href="#" data-tab_index="0" data-tab_name="agency_details" class="c-tabs-nav__link is-active">Agency Details</a>
				<a href="#" data-tab_index="1" data-tab_name="contact_details" class="c-tabs-nav__link">Contact Details</a>
				<a href="#" data-tab_index="2" data-tab_name="property_managers" class="c-tabs-nav__link">Portal Users</a>
				<a href="#" data-tab_index="3" data-tab_name="prices" class="c-tabs-nav__link">Pricing</a>			
				<a href="#" data-tab_index="4" data-tab_name="preferences" class="c-tabs-nav__link">Preferences</a>		
				<a href="#" data-tab_index="5" data-tab_name="agency_logs" class="c-tabs-nav__link">Logs</a>
				<a href="#" data-tab_index="6" data-tab_name="agency_files" class="c-tabs-nav__link">Files</a>				
				<a href="#" data-tab_index="7" data-tab_name="properties" class="c-tabs-nav__link">Properties</a>
				<a href="#" data-tab_index="8" data-tab_name="accounts" class="c-tabs-nav__link">Accounts</a>
				<a href="#" data-tab_index="9" data-tab_name="api" class="c-tabs-nav__link">API</a>
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
												<input type='text' name='state' style="width: 81px;" id='state' value='<?php echo $row['state']; ?>' class='addinput' />
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
											<input class="addinput" style="width: 270px;" readonly="readonly" name='postcode_region_name' id='postcode_region_name' type="text" value="<?php echo $row['postcode_region_name']; ?>">
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
						//$url_params = "?user={$row['login_id']}&pass={$pass}";
						
						
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
						
						<div style="clear:both;"></div>
												
						<div class='row'>
							<h5>Agency Status</h5>
							<select id="status" class="addinput status" name="status" title="Agency Status">
								<?php
								if( $row['status']=='active' ){ ?>
									<option <?php echo ($row['status']=='active')?'selected="selected"':''; ?> value='active'>Active</option>
								<?php	
								}
								?>								
								<option <?php echo ($row['status']=='target')?'selected="selected"':''; ?> value='target'>Target</option>
								<option <?php echo ($row['status']=='deactivated')?'selected="selected"':''; ?> value='deactivated'>Deactivated</option>
							</select>	
								
							

						</div>
						
						<?php
						$salesrep_sql = mysql_query("
							SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
							FROM staff_accounts AS sa
							INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
							WHERE ca.`country_id` ={$_SESSION['country_default']}
							ORDER BY sa.`FirstName`
						");
						?>
						<div class='row'>
							<h5>Sales Rep</h5>
							<?php 
							// edited only for GLOBAL users, target and deactivated agencies and some selected user
							if( $_SESSION['country_default'] == 1 ){ // AU
								$allowed_user = array(2191); // Ashlee Ryan
							}else if( $_SESSION['country_default'] == 2 ){ // NZ
								$allowed_user = array(2124); // Ashley Orchard
							}
							
							
							if( $user_type == 2 || $row['status']=='target' || $row['status']=='deactivated' || in_array($staff_id, $allowed_user) ){ ?>
								<select class='addinput' name='salesrep' id='salesrep' title="Sales Rep">
									<option>-- Select a Sales Rep --</option>
									<?php
									//$salesrep_sql = getStaffByCountry();
									while($salesrep = mysql_fetch_array($salesrep_sql)){ ?>
										<option value="<?php echo $salesrep['staff_accounts_id'] ?>" <?php echo ($salesrep['staff_accounts_id']==$row['salesrep'])?'selected="selected"':''; ?>><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
									<?php 
									}
									?>
								</select> 
								<input type='hidden' name='salesrep_edited' id='salesrep_edited' value='0' />
							<?php	
							}else{ ?>
								<select class='addinput' disabled="disabled">
									<option>-- Select a Sales Rep --</option>
									<?php
									//$salesrep_sql = getStaffByCountry();
									while($salesrep = mysql_fetch_array($salesrep_sql)){ ?>
										<option value="<?php echo $salesrep['staff_accounts_id'] ?>" <?php echo ($salesrep['staff_accounts_id']==$row['salesrep'])?'selected="selected"':''; ?>><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
									<?php 
									}
									?>
								</select> 
								<input type='hidden' name='salesrep' id='salesrep' value='<?php echo $row['salesrep']; ?>' />
								<input type='hidden' name='salesrep_edited' id='salesrep_edited' value='0' />
							<?php	
							}
							?>
							
							
						</div>


						<!-- Gherx addeed START -->

						<div style="clear:both;">&nbsp;</div>
							
						<div class="deactivate_agency_reason_div" style="display:<?php echo ($row['status']=='deactivated')?'block':'none' ?>;">
							<h5>Deactivated Details</h5>
							
							<div class="row">
								<label>Active Properties with SATS</label>
								<br/>
								<input class="addinput" type="text" id="active_prop_with_sats" name="active_prop_with_sats" value="<?php echo $row['active_prop_with_sats'] ?>">
							</div>


							<div class='row'>
								<label class='col1_a'>Changing To</label>
								<br/>
							
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
								
							</div>

							<div style="clear:both;"></div>

							<div class="row">
								<label>Reason they Left</label>
								<br/>
								<textarea style="width:542px;font-size:13.3333px;" class="addinput addtextarea" id="deactivate_reason" name="deactivate_reason"><?php echo $row['deactivated_reason'] ?></textarea>
							</div>

							<div style="clear:both;">&nbsp;</div>
						</div>
						
						<!-- Gherx addeed END -->

						<div style="clear:both;"></div>
						<!-- NEW ADDED BY GHERX NOV 27 2019 START -->					
						<div class="statement_agency_comment_div">
							<div class="row">
								<div class="left_box">
									<label>Statement Message (Appears on Agency Accounts Statement)</label>
										<textarea class="addinput addtextarea formtextarea statements_agency_comments" title="Statement Agency Comments" name="statements_agency_comments" id="statements_agency_comments"><?php echo $row['statements_agency_comments'] ?></textarea>
										<input type="hidden" class="statements_agency_comments_is_changed" name="statements_agency_comments_is_changed" value="0">
										<input type="hidden" class="statements_agency_comments_orig_val" value="<?php echo $row['statements_agency_comments']; ?>">
									</div>
								<div class="right_box">
									<?php 
									$statements_agency_comments_ts =  ( $crm->isDateNotEmpty($row['statements_agency_comments_ts']) ) ? date('d/m/Y H:i', strtotime($row['statements_agency_comments_ts'])) : '';
									?>
									<span class="statements_agency_comments_ts_span"><?php echo $statements_agency_comments_ts ?></span>
									<input type="hidden" name="statements_agency_comments_ts" value="<?php echo $row['statements_agency_comments_ts'] ?>" >
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>
						<!-- NEW ADDED BY GHERX NOV 27 2019 END -->	

						
						<div style="clear:both;"></div>
						
						<div class='row' style='width: 464px;'>
						
							<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin-top:20px; float: right;">
								<img class="inner_icon" src="images/save-button.png">
								Update Details
							</button>

							<?php 
								$crm_ci_page_statement_pdf = "/accounts/statement_pdf/{$agency_id}";
							?>
							<a href="<?php echo $crm->crm_ci_redirect($crm_ci_page_statement_pdf); ?>" target="__blank">
								<button type='button' class='submitbtnImg blue-btn' id="btn_bulk_payment" style="margin: 20px 10px 0 0; float: right;">
									<img class="inner_icon" src="images/button_icons/pdf_white.png">
									Current Statement
								</button>
							</a>
						
						
						</div>
						
						<div>
							<label class="statement_note_txt">Statement shows all invoices after 1/12/19</label>
						</div>
						
						<div style="clear:both;"></div>
						
						</div>
	
						
						
					</div>
						 
					</div>
					
					
					
					<div class="agency_details_tab_cont" style="float:left; margin-left: 40px;">
					
					<h5>Agency Details</h5>
			
					<div class='row'>
						<label class='col1'>Agency Name</label>							
						<label class='col2'><input class="addinput" name="agency_name" id="agency_name" title="Agency Name" type="text" value="<?php echo $row['agency_name']; ?>"></label>
						<label class="col1" style="padding-top:6px;font-size:14px;"><a href='<?php echo $crm->crm_ci_redirect("agency/view_agency_details/{$agency_id}") ?>'>CI VAD</a></label>
					</div>
					
					<div style="clear:both;"></div>
					
					
					<div class='row'>
						<label class='col1'>Legal Name</label>						
						<label class='col2'><input class="addinput" name="legal_name" id="legal_name" title="Legal Name" type="text" value="<?php echo $row['legal_name']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>
					
					
					<?php $abn_lbl = $_SESSION['country_default']==1?'ABN Number':'GST Number'; ?>
					<div class='row'>
						<label class='col1'><?php echo $abn_lbl; ?></label>						
						<label class='col2'><input class="addinput" name="abn" id="abn" title="<?php echo $abn_lbl; ?>" type="text" value="<?php echo $row['a_abn']; ?>"></label>
					</div>
					
					<div style="clear:both;"></div>							
					
					<div class='row'>
						<label class='col1'>Agency ID</label>						
						<label class='col2'><input class="addinput" title="Legal Name" type="text" title="Agency ID" value="<?php echo $agency_id; ?>" readonly="readonly"></label>
					</div>
					
					<div style="clear:both;"></div>
						
					<div class='row'>
						<label class='col1'>Total Properties</label>
						<label class='col2'>
							<input class="addinput" style="width: 37px; margin-right: 5px;" title="Total Properties" name='tot_properties' id='tot_properties' type="text" value="<?php echo $row['tot_properties']; ?>">
							<span>Total Active</span>
							<input class="addinput" style="width: 37px; float: none; display:inline-block;" title="Total Active" type="text" value="<?php echo getSatsToServicePropertyServices($agency_id); ?>" readonly="readonly">
							<span>Joined SATS</span>
							<input class="addinput datepicker" style="width: 75px; float: none; display:inline-block;" title="Joined SATS" type="text" name="joined_sats" value="<?php echo ( $row['joined_sats']!='' )?date('d/m/Y',strtotime($row['joined_sats'])):''; ?>" />
						</label>
					</div>
					
					<div style="clear:both;"></div>
					
					<div class='row'>
						
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
					// trust account software
					$tas_sql = mysql_query("
					SELECT *
					FROM `trust_account_software`
					WHERE `active` = 1
					");					
					?>
					<div class='row'>
						<label class='col1'>Trust Acct. Software</label>
						<label class='col2'>
							<select style="width: 93%;" name="trust_acc_soft" id="trust_acc_soft" title="Trust Acct. Software" class="addinput trust_acc_soft">
								<option value="">----</option>
								<?php
								while( $tsa_row = mysql_fetch_array($tas_sql) ){ ?>
									<option value="<?php echo $tsa_row['trust_account_software_id'] ?>" <?php echo ( $tsa_row['trust_account_software_id'] == $row['trust_account_software'] )?'selected="selected"':''; ?>><?php echo $tsa_row['tsa_name']; ?></option>
								<?php
								}
								?>								
							</select>
						</label>
					</div>
					
					<div style="clear:both;"></div>
					
					<!--
					<div class='row' id="tas_connected_div" style="<?php echo ( $row['trust_account_software'] > 0 )?'display: block;':'display: none;' ?>">
						<label class='col1'>Trust Acct. Connected?</label>
						<label class='col2'>
							<select style="width: 93%;" name="tas_connected" id="tas_connected" class="addinput tas_connected">
								<option value="0" <?php echo ($row['tas_connected']==0)?'selected="selected"':''; ?>>NO</option>
								<option value="1" <?php echo ($row['tas_connected']==1)?'selected="selected"':''; ?>>YES</option>								
							</select>
						</label>
					</div>
					-->
					
					
					<div class='row' id="tas_connected_div" style="<?php echo ( $row['trust_account_software'] > 0 )?'display: block;':'display: none;' ?>">
						<label class='col1'>Trust Account Agency ID</label>
						<label class='col2'>
							<input class="addinput" name='propertyme_agency_id' id='propertyme_agency_id' type="text" value="<?php echo $row['propertyme_agency_id']; ?>">
						</label>
					</div>
					
					<div style="clear:both;"></div>
					
					
					<div style="float:left; width: 111%;">
					
					<h5>Maintenance Program</h5>
					
					<table class="table" id="mm_prog_table">
					
						<tr>
							<td>Maintenance Provider</td>
							<td>
								<select style="width: 210px;" name="maintenance" class="maintenance mm_prog" id="maintenance">
									<option value=''>None</option>
									<?php 
									// get all maintenance
									$m_sql = mysql_query("
										SELECT *
										FROM `maintenance`
										ORDER BY `name`
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
						
						<tr class="maintenance_prog_row" <?php echo ($sel_m['maintenance_id']=='' || $sel_m['maintenance_id']==0)?'style="display:none;"':''; ?>>
							<td>Apply Surcharge to all Invoices?</td>
							<td>
								<div class="eagdt-rd">
									<label for="send_emails_1"><input type="radio" class="addinput mm_prog" title="Apply Surcharge to all Invoices" value="1" name="m_surcharge" <?php echo ($sel_m['surcharge']==1)?'checked="checked"':''; ?>> YES</label>
								</div>
								<div class="eagdt-rd">
									<label for="send_emails_0"><input type="radio" class="addinput mm_prog" title="Apply Surcharge to all Invoices" value="0" name="m_surcharge" <?php echo ($sel_m['surcharge']==0)?'checked="checked"':''; ?>> NO</label>
								</div>
							</td>
						</tr>
						
						<tr class="maintenance_prog_row" <?php echo ($sel_m['maintenance_id']=='' || $sel_m['maintenance_id']==0)?'style="display:none;"':''; ?>>
							<td>Display Message on all Invoices?</td>
							<td>
								<div class="eagdt-rd">
									<label for="send_emails_1"><input type="radio" class="addinput mm_prog" title="Display Message on all Invoices" value="1" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==1)?'checked="checked"':''; ?>> YES</label>
								</div>
								<div class="eagdt-rd">
									<label for="send_emails_0"><input type="radio" class="addinput mm_prog" title="Display Message on all Invoices" value="0" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==0)?'checked="checked"':''; ?>> NO</label>
								</div>
							</td>
						</tr>
						
						<tr class="maintenance_prog_row" <?php echo ($sel_m['maintenance_id']=='' || $sel_m['maintenance_id']==0)?'style="display:none;"':''; ?>>
							<td>Surcharge $</td>
							<td>
								<input type="text" style="width: 200px; margin: 0;" class="addinput mm_prog" name="m_price" id="m_price" title="Surcharge" value="<?php echo $sel_m['price']; ?>" />
							</td>
						</tr>
						
						<tr class="maintenance_prog_row" <?php echo ($sel_m['maintenance_id']=='' || $sel_m['maintenance_id']==0)?'style="display:none;"':''; ?>>
							<td>Invoice Message</td>
							<td>								
								<textarea style="width: 193px !important;" class="addtextarea formtextarea agency_comments mm_prog" name='m_surcharge_msg' id='m_surcharge_msg'><?php echo $sel_m['surcharge_msg']; ?></textarea>
							</td>
						</tr>
						
					</table>
					
					</div>
				
					<div style="clear:both;"></div>
					
					<div class='row'>
						<label class='col1'>&nbsp;</label>
						<label class='col2'>
							<!--
							<button type="button" id="btn_hard_delete" class="submitbtnImg" style="float: right; margin-right: 28px;">
								<img class="inner_icon" src="images/cancel-button.png">DELETE
							</button>
							-->
						</label>
					</div>
					
					<div style="clear:both;"></div>

					<?php
					//if( $row['status']=='target' || $row['status']=='deactivated' ){ 
					?>
					
						
						
						<div style="clear:both;"></div>
					
					<?php
					//}
					?>	

					<?php
					if( $row['status'] != 'active' ){ ?>
						<a href='view_agency_details.php?id=<?php echo $agency_id ?>&activate=1' onclick='return confirm("Are you sure you want to activate <?php echo $row['agency_name']; ?>?")'>
							<button class="addinput submitbtnImg eagdtbt blue-btn btn-activate" id="btn-activate" type="button" style="margin-top:90px; float: right;">Change Agency to Active</button>
						</a>
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
									<textarea title="Agency Emails" name='agency_emails' style="margin-right: 10px;" id='agency_emails' class='addtextarea formtextarea' title='Agency Emails'><?php echo $row['agency_emails']; ?></textarea>
								</td>
								<td>								
									<textarea title="Accounts Emails" name='account_emails' style="margin-right: 10px;" id='account_emails' class='addtextarea formtextarea' title='Accounts Emails'><?php echo $row['account_emails']; ?></textarea>
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
								<td>
									<a target="_blank" id="custom_email_link" href="send_sales_emails.php?agency_id=<?php echo $agency_id ?>">
										<button type="button" id="btn_custom_email" class="submitbtnImg green-btn" style="margin-top: 10px;">
											<img class="inner_icon" src="images/email.png">
											Send Sales Emails
										</button>
									</a>
								</td>
								<td style="text-align:right;">
									<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="float: none; margin-top: 15px;">
										<img class="inner_icon" src="images/save-button.png">
										Update Details
									</button>
								</td>
							</tr>
						</table>
						
					</div>

			
					
	
					
					
					
					
			
				</div>
			</div>
			
			
	
			
			<!-- PROPERTY MANAGERS -->
			<div class="c-tab" data-tab_cont_name="property_managers">
				<div class="c-tab__content">
			
					<?php
					//if( $row['allow_indiv_pm']==1 ){

						$custom_select = "
							aua.`agency_user_account_id`,
							aua.`fname`,
							aua.`lname`,
							aua.`phone`,
							aua.`job_title`,
							aua.`email`,
							aua.`password`,
							aua.`user_type`,
							aua.`active`,
							
							auat.`user_type_name`
						";

						$pm_params = array( 
							'custom_select' => $custom_select,
							'agency_id' => $agency_id,
							'sort_list' => array(
								array(
									'order_by' => 'aua.`user_type`',
									'sort' => 'ASC'
								)
							),
							'echo_query' => 0
						 );
						 
						if( $_GET['show_all_portal_users'] !=1 ){
							$pm_params['active'] = 1;
						} 
						 
						$pm_sql = Sats_Crm_Class::getNewPropertyManagers($pm_params);	
						
						
										
						
						?>
						
						<table class="table" id="pm_table" style="margin-top: 8px;">
							<thead>
								<th>User Type</th>
								<th>First Name</th>
								<th>Last Name</th>
								<th>Position</th>
								<th>Phone</th>
								<th>Email</th>
								<th>login</th>
								<th>Invite</th>
								<th>Reset Password</th>
								<th></th>
							</thead>
							<tbody>
								<?php
								// IMPORTANT: changes on the html markup, needs to update js add PM too
								while($pm = mysql_fetch_array($pm_sql)){ ?>
									<tr class="<?php echo ($pm['active']==0)?'inactive_agency_portal_user':''; ?>">
										<td>									
											<select name="pm_user_type[]">											
												<option value="">---</option> 	
												<?php
												// get user type
												$aua_t_sql = mysql_query("
													SELECT *
													FROM `agency_user_account_types`
													WHERE `active` = 1
												");		
												while( $aua_t = mysql_fetch_array($aua_t_sql) ){ ?>
													<option value="<?php echo $aua_t['agency_user_account_type_id']; ?>" <?php echo ($aua_t['agency_user_account_type_id'] == $pm['user_type'])?'selected="selected"':'' ?>><?php echo $aua_t['user_type_name']; ?></option> 	
												<?php	
												}
												?>
											</select>
										</td>
										<td>														
											<input type="text" name="pm_fname[]" class="addinput pm_fname" value="<?php echo $pm['fname']; ?>" />
										</td>
										<td>
											<input type="text" name="pm_lname[]" class="addinput pm_lname" value="<?php echo $pm['lname']; ?>" />
										</td>											
										<td>
											<input type="text" name="pm_job_title[]" class="addinput pm_job_title" value="<?php echo $pm['job_title']; ?>" />
										</td>
										<td>
											<input type="text" name="pm_phone[]" class="addinput pm_phone" value="<?php echo $pm['phone']; ?>"  />
										</td>
										<td>
											<input type="text" name="pm_email[]" class="addinput pm_email" value="<?php echo $pm['email']; ?>" />
										</td>					
										<td>
											<?php
											// login
											if( $pm['active'] == 1 ){
												if( $pm['password'] != '' ){ ?>
													<a href="<?php echo $agency_site; ?>?user=<?php echo $pm['email']; ?>&agency_id=<?php echo $agency_id; ?>&pass=<?php echo $pm['password'] ?>&crm_login=1" target="__blank">
														<img src='/images/agency_login.png' class="login_icon" />
													</a>
												<?php	
												}else{
													echo "Password not set";
												}
											}
											?>												
										</td>
										<td>
											<?php
											// invite
											if( $pm['active'] == 1 ){
												if(  $pm['password'] == '' ){ ?>
													<a class="invite_email_link" href="<?php echo $agency_site; ?>/sys/send_invite_email?aua_id=<?php echo $pm['agency_user_account_id']; ?>" target="__blank">
														<img src='/images/button_icons/mail-tenant.png' class="login_icon mail_icon" />
													</a>
												<?php	
												}else{
													echo "Password already set";
												}
											}
											?>												
										</td>
										<td>
											<?php
											// reset password
											if( $pm['active'] == 1 ){ ?>
												<a class="reset_pass_email_link" href="<?php echo $agency_site; ?>/sys/send_reset_password_email?aua_id=<?php echo $pm['agency_user_account_id']; ?>" target="__blank">
													<img src='/images/button_icons/mail-tenant.png' class="login_icon mail_icon" />
												</a>	
											<?php
											}
											?>																					
										</td>
										<td style="opacity:1 !important;">
											
											<?php
											if( $pm['active'] == 1 ){ ?>												
												<button class="addinput submitbtnImg eagdtbt status_toggle_btn" type="button" data-status="0">
													<img class="inner_icon" src="images/button_icons/cancel-button.png">
													Deactivate
												</button>
											<?php	
											}else{ ?>
												<button class="addinput submitbtnImg green-btn eagdtbt status_toggle_btn" type="button" data-status="1">
													<img class="inner_icon" src="/images/button_icons/rebook.png">
													Restore												
												</button>
											<?php	
											}
											?>
											<input type="hidden" name="pm_id[]" class="pm_id" value="<?php echo $pm['agency_user_account_id']; ?>" />
											
										</td>
									</tr>
								<?php
									$i++;
								}
								?>												
							</tbody>	
						</table>
						<div id="pm_main_div">							
							<div class="row edadt">			
								<div>
									
									
									<div style="margin-top: 20px;">
										
										<button type="button" id="btn_add_pm" class="addinput submitbtnImg eagdtbt blue-btn" style="margin-right: 10px;"> 
											<img class="inner_icon" src="images/add-button.png">
											User
										</button>
										
										<?php
										if( $_GET['show_all_portal_users'] == 1 ){ ?>
										
											<a href="view_agency_details.php?id=<?php echo $agency_id; ?>">
												<button type="button" id="btn_show_all_portal_users" class="addinput submitbtnImg eagdtbt blue-btn" style="margin-right: 10px;"> 
													<img class="inner_icon" src="images/show-button.png">
													Show Ony Active
												</button>
											</a>
										
										<?php	
										}else{ ?>
										
											<a href="view_agency_details.php?id=<?php echo $agency_id; ?>&show_all_portal_users=1">
												<button type="button" id="btn_show_all_portal_users" class="addinput submitbtnImg eagdtbt blue-btn" style="margin-right: 10px;"> 
													<img class="inner_icon" src="images/show-button.png">
													Show All Users
												</button>
											</a>
											
										<?php	
										}
										?>										
										
										
										<?php
										//if( $pm_num > 0 ){ ?>
											<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="float: right;">
												<img class="inner_icon" src="images/save-button.png">
												Update Details
											</button>
										<?php
										//}
										?>
										
									</div>
								
								</div>			
							</div>		
						</div>
					<?php				
					//}
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
					<table class="table tbl_servicing" style="float:left; margin-right:30px">
						<tr>
							<th style="width: 250px;">Services</th>
							<th>Price</th>
							<th>Approved</th>								
						</tr>
						<?php
						$index = 0;
						while($service = mysql_fetch_array($service_sql)){
							
							$sel_service_sql = $agency->get_approved_agency_services($agency_id,$service['id']);
							$agencySelected = (mysql_num_rows($sel_service_sql)>0)?true:false;
							$sel_service = mysql_fetch_array($sel_service_sql);

							$sa_ic = 12; // Smoke Alarms (IC)
							$is_sa_ic = ( $service['id'] == $sa_ic )?true:false;
							
						?>
							<tr class="<?php echo ($agencySelected==true)?null:'fadedText'; ?> <?php echo ( $is_sa_ic == true )?'sa_ic_row':null; ?>" value="<?php echo $index; ?>">
								<td class="priceAlarmCol">
									<?php 
									echo $service['type'];
									if( $is_sa_ic == true ){  ?>
										<strong style="color:red;">(Required for Quotes)</strong>
									<?php	
									}									
									if( $is_sa_ic == true && is_numeric($sel_service['price']) && $sel_service['price']==0 ){ ?>
										<span style="color:red; margin-left: 20px;">$119</span>
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
									<input type="hidden" name="agency_service_changed[]" class="agency_service_changed" value="0" />
									<input type="hidden" name="agency_service_orig_price[]" class="agency_service_orig_price" value="<?php echo $sel_service['price']; ?>" />
								</td>								
							</tr>
						<?php
						$index++;
						}
						?>	
					</table>
					
					<table class="table tbl_servicing" style="float:left;">
						<?php
						$alarm_sql = $agency->get_alarms();
						?>				
						<tr>
							<th style="width: 210px;">Alarms</th>
							<th>Price</th>
							<th>Approved</th>							
						</tr>
						<?php
						$index = 0;
						while($alarm = mysql_fetch_array($alarm_sql)){ 
						
							$sel_alarm_sql = $agency->get_approved_agency_alarms($agency_id,$alarm['alarm_pwr_id']);
							$agencySelected = (mysql_num_rows($sel_alarm_sql)>0)?true:false;
							$sel_alarm = mysql_fetch_array($sel_alarm_sql);
							
							$alarm_240v_rf_brooks = 10; // 240v RF
							$alarm_240v_rf_cav = 14; // 240vRF(cav)

							$is_alarm_req_for_quotes = ( $alarm['alarm_pwr_id'] == $alarm_240v_rf_brooks || $alarm['alarm_pwr_id'] == $alarm_240v_rf_cav )?true:false;
							
						 ?>
							<tr class="<?php echo ($agencySelected==true)?null:'fadedText'; ?> <?php echo ( $is_alarm_req_for_quotes == true )?'is_alarm_req_for_quotes_row':null; ?>" value="<?php echo $index; ?>">
								<td class="priceAlarmCol">
									<?php 
									$display_alarm_make = array(10,12);
									if( in_array($alarm['alarm_pwr_id'],$display_alarm_make) ){
										echo "{$alarm['alarm_pwr']}".( ( $alarm['alarm_make'] != '' )?" ({$alarm['alarm_make']})":null ); 
									}else{
										echo $alarm['alarm_pwr']; 
									}
									
									if( $is_alarm_req_for_quotes == true ){  ?>
										<strong style="color:red;">(Required for Quotes)</strong>
									<?php	
									}
									if( $is_alarm_req_for_quotes == true && is_numeric($sel_alarm['price']) && $sel_alarm['price']==0 ){ ?>
										<span style="color:red; margin-left: 20px;">$200</span>
									<?php	
									}
									?>
								</td>	
								<td class="price_div">
									<label>$</label>
									<input type="text" name="alarm_price[]" class="addinput alarm_price" value="<?php echo $sel_alarm['price']; ?>" />																		
									
								</td>		
								<td>
									<input type="checkbox" name="agency_alarm_approve[]" class="agency_alarm_approve approve" <?php echo ($agencySelected==true)?'checked="checked"':''; ?> value="<?php echo $index; ?>" />
									<input type="hidden" name="alarm_id[]" class="alarm_id" value="<?php echo $alarm['alarm_pwr_id']; ?>" />
									<input type="hidden" name="agency_alarms_changed[]" class="agency_alarms_changed" value="0" />
									<input type="hidden" name="agency_alarms_orig_price[]" class="agency_alarms_orig_price" value="<?php echo $sel_alarm['price']; ?>" />
									<input type="hidden" name="alarm_name[]" class="alarm_name" value="<?php echo $alarm['alarm_pwr']; ?>" />
									<input type="hidden" name="alarm_checked[]" class="alarm_checked" value="<?php echo ($agencySelected==true)?1:0; ?>" />
									<input type="hidden" name="alarm_orig[]" class="alarm_orig" value="<?php echo ($agencySelected==true)?1:0; ?>" />
								</td>								
							</tr>
						<?php
						$index++;
						} 
						?>	
					</table>
				
					<div style="clear:both;"></div>
					
					<?php
					//if( $staff_id==2025 || $staff_id==2070 ){ ?>
						<div class='row'>
							<label><strong>Agency Special Deal</strong></label><br />
							<textarea title="Agency Special Deal" name='agency_special_deal' id='agency_special_deal' class='addtextarea formtextarea'><?php echo $row['agency_special_deal']; ?></textarea>
						</div>
						<div style="clear:both;"></div>
					<?php					
					//}
					?>	

					<div class='row' style="margin-top: 25px;">
							<label><strong>Multi-owner Discount</strong></label><br />
							<label class="multi_owner_discount_lbl">$</label> <input type="text" name="multi_owner_discount" style="width: 100px;" class="addinput" value="<?php echo $row['multi_owner_discount']; ?>" />																		
					</div>
					<div style="clear:both;"></div>				
					
					<div class='row'>
						<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin: 20px 0; float: right;">
							<img class="inner_icon" src="images/save-button.png">
							Update Details
						</button>
					</div>
					
				</div>
			</div>
			
		
			
			
			<!-- PREFERENCES -->
			<div class="c-tab" data-tab_cont_name="preferences">
				<div class="c-tab__content agency_pref_tab_content">
			
				<div style="float:left; margin-right: 40px;">
		
					<h5>Agency Preferences</h5>
				
					<table class="table agency_pref_tbl" style="width:auto;">
						<tr>
							<td>Attach invoices to emails?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_emails' value='1' <?php echo $row['send_emails'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_emails' value='0' <?php echo $row['send_emails'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_emails']==1)?'jshowIt':'jhideIt'; ?>">Agency emails will include an attached PDF invoice and a link</div>
								<div class="colorItRed <?php echo ($row['send_emails']==0)?'jshowIt':'jhideIt'; ?>">Agency emails will only include a hyperlink to the invoice</div>
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
							<td>Individual Property Managers Receive Certificate & Invoice?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_indiv_pm_email_cc' value='1' <?php echo $row['allow_indiv_pm_email_cc'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_indiv_pm_email_cc' value='0' <?php echo $row['allow_indiv_pm_email_cc'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['allow_indiv_pm_email_cc']==1)?'jshowIt':'jhideIt'; ?>">PMs get additional copy of Invoice and Certificate</div>
								<div class="colorItRed <?php echo ($row['allow_indiv_pm_email_cc']==0)?'jshowIt':'jhideIt'; ?>">NO additional Certificate and Invoice sent</div>
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
						<tr>
							<td>Subscription Billing?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_upfront_billing' value='1' <?php echo $row['allow_upfront_billing'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='allow_upfront_billing' value='0' <?php echo $row['allow_upfront_billing'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['allow_upfront_billing']==1)?'jshowIt':'jhideIt'; ?>">Agency Allows up front billing</div>
								<div class="colorItRed <?php echo ($row['allow_upfront_billing']==0)?'jshowIt':'jhideIt'; ?>">Agency Does not Allow up front billing</div>
							</td>
						</tr>
						<tr>
							<td>Invoice PM'S Only?</td>
							<td>
								<div class='eagdt-rd'><input disabled="true" type='radio' class='addinput' name='invoice_pm_only' value='1' <?php echo $row['invoice_pm_only'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='invoice_pm_only' value='0' <?php echo $row['invoice_pm_only'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['invoice_pm_only']==1)?'jshowIt':'jhideIt'; ?>">Invoice issued only to individual PM not to agency accounts email</div>
								<div class="colorItRed <?php echo ($row['invoice_pm_only']==0)?'jshowIt':'jhideIt'; ?>">Agency Does not Allow Invoice PM'S Only</div>
							</td>
						</tr>
						<tr>
							<td>Electrician Only</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='electrician_only' value='1' <?php echo $row['electrician_only'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='electrician_only' value='0' <?php echo $row['electrician_only'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['electrician_only']==1)?'jshowIt':'jhideIt'; ?>">Electricians ONLY to attend</div>
								<div class="colorItRed <?php echo ($row['electrician_only']==0)?'jshowIt':'jhideIt'; ?>">Both Techs and Electricians can attend</div>
							</td>
						</tr>
						<tr>
							<td>Send copy of EN to Agency</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_en_to_agency' value='1' <?php echo $row['send_en_to_agency'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_en_to_agency' value='0' <?php echo $row['send_en_to_agency'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_en_to_agency']==1)?'jshowIt':'jhideIt'; ?>">Agency receives a copy of Entry Notices</div>
								<div class="colorItRed <?php echo ($row['send_en_to_agency']==0)?'jshowIt':'jhideIt'; ?>">Agency does not receive a copy of Entry Notices</div>
							</td>
						</tr>

						<tr>
							<td>Individual Property Managers Receive EN?</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='en_to_pm' value='1' <?php echo $row['en_to_pm'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='en_to_pm' value='0' <?php echo $row['en_to_pm'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td></td>
							<td>
								<div class="colorItGreen <?php echo ($row['en_to_pm']==1)?'jshowIt':'jhideIt'; ?>">EN sent to PM only</div>
								<div class="colorItRed <?php echo ($row['en_to_pm']==0)?'jshowIt':'jhideIt'; ?>">EN sent to Agency only</div>
							</td>
						</tr>

						<tr>
							<td>Show accounts reports?</td>
							<td>
								<div class='eagdt-rd'><input disabled="disabled" type='radio' class='addinput' name='accounts_reports' value='1' <?php echo $row['accounts_reports'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='accounts_reports' value='0' <?php echo $row['accounts_reports'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td>&nbsp;</td>
							<td>
								<div class="colorItGreen <?php echo ($row['accounts_reports']==1)?'jshowIt':'jhideIt'; ?>">Accounts reports, including statements will be visible</div>
								<div class="colorItRed <?php echo ($row['accounts_reports']==0)?'jshowIt':'jhideIt'; ?>">No accounting information will be visible to the agency</div>
							</td>
						</tr>

						<tr>
							<td>Exclude $0 invoices</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='exclude_free_invoices' value='1' <?php echo $row['exclude_free_invoices'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='exclude_free_invoices' value='0' <?php echo $row['exclude_free_invoices'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td>&nbsp;</td>
							<td>
								<div class="colorItGreen <?php echo ($row['exclude_free_invoices']==1)?'jshowIt':'jhideIt'; ?>">This agency will only receive invoices with a positive balance.</div>
								<div class="colorItRed <?php echo ($row['exclude_free_invoices']==0)?'jshowIt':'jhideIt'; ?>">This agency will receive all invoices.</div>
							</td>
						</tr>

						<tr>
							<td>Send 48 hour key email</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_48_hr_key' value='1' <?php echo $row['send_48_hr_key'] == 1 ? 'checked' : ''; ?>> YES</div>
							</td>
							<td>
								<div class='eagdt-rd'><input type='radio' class='addinput' name='send_48_hr_key' value='0' <?php echo $row['send_48_hr_key'] == 0 ? 'checked' : ''; ?>> NO</div>
							</td>
							<td>&nbsp;</td>
							<td>
								<div class="colorItGreen <?php echo ($row['send_48_hr_key']==1)?'jshowIt':'jhideIt'; ?>">This agency will be notified both 24 and 48 hours in advance of any keys required.</div>
								<div class="colorItRed <?php echo ($row['send_48_hr_key']==0)?'jshowIt':'jhideIt'; ?>">This agency will only be notified 24 hours in advance of any keys required.</div>
							</td>
						</tr>

					</table>

				</div>
					
			
				
					
					
					
				
				
				
				<div style="clear:both;"></div>
				<button class='addinput submitbtnImg eagdtbt btn-update' id="btn-update" type="button" style="margin-top:10px;">
					<img class="inner_icon" src="images/save-button.png">
					Update Details
				</button>
					
				
				</div>
			</div>
			
			<input type='hidden' name='num_alarms' value='<?php echo sizeof($alarm_prices); ?>'>						
			<input type="hidden" id="fields_edited" name="fields_edited" />	
			<input type="hidden" name="pass_change" id="pass_change" value="0" />
			<input type="hidden" name="tot_prop_change" id="tot_prop_change" value="0">
			<input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>">
			<input type="hidden" name="doaction" value="<?php echo $doaction; ?>">
			
			<input type='hidden' name='agency_name_edited' id='agency_name_edited' value='0' />
			<input type='hidden' name='legal_name_edited' id='legal_name_edited' value='0' />
			<input type='hidden' name='abn_edited' id='abn_edited' value='0' />

			<input type='hidden' name='agency_emails_edited' id='agency_emails_edited' value='0' />
			<input type='hidden' name='account_emails_edited' id='account_emails_edited' value='0' />
			
			<input type='hidden' name='mm_program_edited' id='mm_program_edited' value='0' />
			<input type='hidden' name='agency_services_edited' id='agency_services_edited' value='0' />
			<input type='hidden' name='agency_alarms_edited' id='agency_alarms_edited' value='0' />
			
			</form>
			
			
			
			<!-- LOGS -->
			<div class="c-tab tab_agency_logs" data-tab_cont_name="agency_logs">
				<div class="c-tab__content">




				<table class="table" id="onboading_tbl" style="width:auto;">
						<thead>
						<tr>
							<td>&nbsp;</td>
							<td><strong>Info</strong></th>
							<td class="done_col"><strong>Done</strong></td>
							<td><strong>Timestamp</strong></td>							
						</tr>
						</thead>
						<tbody>
						<?php
						$on_boarding_sql = mysql_query("
							SELECT `onboarding_id`, `name`
							FROM `agency_onboarding`						
							WHERE `active` = 1
						");

						while( $on_brdng_row = mysql_fetch_array($on_boarding_sql) ){ 
							
							// check if selected
							$aob_sel_sql = mysql_query("
								SELECT aob_sel.`onboarding_selected_id`, aob_sel.`updated_date`, sa.`FirstName`, sa.`LastName` 
								FROM `agency_onboarding_selected` AS aob_sel
								LEFT JOIN `staff_accounts` AS sa ON aob_sel.`updated_by` = sa.`StaffID`
								WHERE aob_sel.`agency_id` = {$agency_id}
								AND aob_sel.`onboarding_id` = {$on_brdng_row['onboarding_id']}
							");

							$aob_sel_num_row = mysql_num_rows($aob_sel_sql);
							
							if( $aob_sel_num_row > 0 ){
								$aob_sel_row = mysql_fetch_array($aob_sel_sql);
								$updated_by = $crm->formatStaffName($aob_sel_row['FirstName'],$aob_sel_row['LastName']);		
								$updated_date = date('d/m/Y H:i',strtotime($aob_sel_row['updated_date']));					
							}else{
								$updated_by = null;		
								$updated_date = null;
							}
							
							 

							?>
							<tr>
								<td>
									<input type="checkbox" class="onboarding_id" value="<?php echo $on_brdng_row['onboarding_id']; ?>" <?php echo ( $aob_sel_num_row > 0 )?'checked="checked"':null; ?> />
								</td>
								<td>
									<?php echo $on_brdng_row['name']; ?>
								</td>
								<td class="done_col">
									<img src="/images/check_icon2.png" class="ob_check_icon" style="width: 18px; <?php echo ( $aob_sel_num_row == 0 )?'display:none;':null; ?>" />								
								</td>
								<td class="timestamp_style">
									<span class="ob_updated_by">
										<?php echo $updated_by; ?>
									</span>
									<span class="ob_updated_date">
										<?php echo $updated_date; ?>
									</span>
								</td>
							</tr>
						<?php
						}
						?>												
						</tbody>
				</table>


				
					<?php			
					/*
					if (in_array($staff_id, $vip)){
						$temp_str = '';
					}else{
						$temp_str = "
							AND c.`contact_type` != 'Phone Call - Accounts'
							AND c.`contact_type` != 'Email - Accounts'
							AND c.`contact_type` != 'Other - Accounts'
						";						
					}
					*/
					
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
					ORDER BY c.`agency_event_log_id` DESC
					";

					$result = mysql_query ($Query, $connection);
					?>
					
					<form method="post" name="form_agency_logs" id="form_agency_logs">
						<table class="table" border=0 cellspacing=1 cellpadding=5 style="width: 100%;">
							<tr valign="middle" bgcolor="white" style="border: medium none;">

								<td align="left">
									<label for="eventdate" style="display: block; float: none;">Date</label>
									<input type="text" name="eventdate" class="datepicker addinput agency_logs_input" value="<?php echo date("d/m/Y"); ?>">
								</td>

								<td>
									<label for="contact_type" style="display: block; float: none;">Contact Type</label>
									<?php
									if( $row['status']=='active' ){ // for active agencies
									?>									
										<select name="contact_type" class="fselect agency_logs_input" style="width:157px;">
											<option value="Cold Call">Cold Call</option>  
											<option value="Cold Call In">Cold Call In</option>
											<option value="Conference">Conference</option>
											<option value="E-mail">E-mail</option>
											<?php
											//if (in_array($staff_id, $vip)){ ?>
												<option value="Email - Accounts">Email - Accounts</option> 
											<?php	
											//}
											?>	
											<option value="Follow up">Follow Up</option> 
											<option value="Happy Call">Happy Call</option>
											<option value="Mailout">Mail-Out</option>
											<option value="Meeting">Meeting</option> 
											<option value="Other">Other</option>
											<?php
											//if (in_array($staff_id, $vip)){ ?>					
												<option value="Other - Accounts">Other - Accounts</option> 
											<?php	
											//}
											?>
											<option value="Pack Sent">Pack Sent</option> 
											<option value="Phone Call">Phone Call</option>
											<?php
											//if (in_array($staff_id, $vip)){ ?>
												<option value="Phone Call - Accounts">Phone Call - Accounts</option>   				
											<?php	
											//}
											?>	
											<option value="Pop In">Pop In</option>
										</select>
									
									<?php	
									}else{ // for target and deactivated agencies
									?>
										<select name="contact_type">											
											<option value="Cold Call">Cold Call</option> 
											<option value="Cold Call In">Cold Call In</option> 
											<option value="Conference">Conference</option> 
											<option value="E-mail">E-mail</option>
											<option value="Follow Up">Follow up</option>
											<option value="Mailout">Mail-Out</option>
											<option value="Meeting">Meeting</option>											
											<option value="Other">Other</option>
											<option value="Pack Sent">Pack Sent</option> 
											<option value="Phone Call">Phone Call</option>
											<option value="Pop In">Pop In</option> 
										</select>
									<?php	
									}
									?>
									
								</td>

								<td style="width: 405px;">				
									<textarea name="comments" lengthcut="true" class="addtextarea vpr-adev-txt" style="width: 95%;"></textarea>
								</td>
								
								<td>
									<label for="next_contact" style="display: block; float: none;">Next Contact</label>
									<input type="text" name="next_contact" class="datepicker addinput agency_logs_input" />	
								</td>
								
								<td>
									<label for="next_contact" style="display: block; float: none;">Important</label>
									<input type="checkbox" value="1" style="display: block; margin-top: 8px;" name="important" id="important" />
								</td>
								
								<td>
									<label for="next_contact" style="display: block; float: none;">Add to Snapshot</label>
									<input type="checkbox" value="1" name="add_to_snapshot" id="add_to_snapshot" />
									<select name="ss_status" id="ss_status" class="fselect agency_logs_input">
										<option value="">----</option>
										<?php
										$ss_s_sql = mysql_query("
											SELECT *
											FROM `sales_snapshot_status`								
										");
										while($ss_s = mysql_fetch_array($ss_s_sql)){ ?>
											<option value="<?php echo $ss_s['sales_snapshot_status_id']; ?>"><?php echo $ss_s['name']; ?></option>
										<?php
										}
										?>						
									</select>
									<input type="hidden" name="total_prop" value="<?php echo $row['tot_properties']; ?>" />
								</td>
							
								<td align="left">
									<input type="hidden" name="doaction" value="addevent" class="submitbtnImg" />									
									<button style="margin-top: 22px;" class='addinput submitbtnImg eagdtbt btn-add_log' id="btn-add_log" type="submit">
										<img class="inner_icon" src="images/add-button.png">
										Event
									</button>
								</td>

							</tr>
						</table>
					</form>	
					
					
					<?php
					if( mysql_num_rows($result) != 0 ) { 
					$odd=0;
					?>
					<table>

						<tr class="vad_tbl_row">
							<td class='colorwhite bold'>Date</td>
							<td class='colorwhite bold'>Type</td>
							<td class='colorwhite bold'>Staff Member</td>
							<td class='colorwhite bold' style='width: 54%;'>Comments</td>
							<td class='colorwhite bold'>Next Contact</td>
							<!--<td class='colorwhite bold'>Delete</td>-->
						</tr>						
						<?php
						while ($a_log = mysql_fetch_array($result)){
						$odd++;			  
						if (is_odd($odd)) {
							$bg_color = 'white_bg_color';
						} else {
							$bg_color = 'grey_bg_color';
						}				
						// red highlight for important
						if( $a_log['important']==1 ){
							$bg_color = 'important_bg_color';
						}	
						?>			   
						<tr class="<?php echo $bg_color; ?>">

							<td><?php echo date('d/m/Y',strtotime($a_log['eventdate'])); ?></td>
							<td><?php echo $a_log['contact_type']; ?></td>					
							<td><?php echo "{$a_log['FirstName']} {$a_log['LastName']}"; ?></td>					
							<td><?php echo $a_log['comments']; ?></td>					
							<td><?php echo ($a_log['next_contact']!='0000-00-00')?date("d/m/Y",strtotime($a_log['next_contact'])):''; ?></td>
							<!--
							<td>	
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
					
				
					
					<?php
					//if( strpos($_SERVER['SERVER_NAME'],"crmdev") !== false ){

					// pagination
					$pagi_offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
					$pagi_limit = 50;

					$this_page = $_SERVER['PHP_SELF'];
					$pagi_params = "&{$_SERVER['QUERY_STRING']}";

					$next_link = "{$this_page}?offset=".($pagi_offset+$pagi_limit).$pagi_params;
					$prev_link = "{$this_page}?offset=".($pagi_offset-$pagi_limit).$pagi_params;
				
					// NEW LOGS TABLE
					// paginate
					// exclude log title, payments(43) and agency payments(47)
					$custom_filter = "
					AND l.`title` NOT IN(43,47)
					";
					$params = array(
						'custom_select' => '
							l.`log_id`,
							l.`created_date`,
							l.`title`,
							l.`details`,				
							
							ltit.`title_name`,
							
							aua.`fname`,
							aua.`lname`,
							aua.`photo`,

							sa.StaffID,
							sa.FirstName,
							sa.LastName
						',
						'custom_filter' => $custom_filter,
						'agency_id' => $agency_id,
						'display_in_vad' => 1,
						'deleted' => 0,
						'paginate' => array(
							'offset' => $pagi_offset,
							'limit' => $pagi_limit
						),
						'sort_list' => array(
							array(
								'order_by' => 'l.`created_date`',
								'sort' => 'DESC'
							)
						),
						'echo_query' => 0
					);
					$result = $crm->getNewLogs($params);

					// all row
					$params = array(
						'custom_select' => 'l.`log_id`',
						'custom_filter' => $custom_filter,
						'agency_id' => $agency_id,
						'display_in_vad' => 1,
						'deleted' => 0
					);
					$ptotal = mysql_num_rows($crm->getNewLogs($params));
					
					
					?>
					<h2 class="heading">New logs</h2>
					<table style="border:1px solid #cccccc !important;" border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">	
						<tr bgcolor="#b4151b">
						<td class='colorwhite bold'>Date</td>
						<td class='colorwhite bold'>Time</td>
						<td class='colorwhite bold'>Title</td>
						<td class='colorwhite bold'>Who</td>
						<td class='colorwhite bold' style="width: 53%;">Details</td>
						</tr>
						<?php
						// (3) While there are still rows in the result set,
						// fetch the current row into the array $row
						while ($row = mysql_fetch_array($result))
						{
						?>
							<tr class="border-none">
								<td>
									<?php echo date('d/m/Y',strtotime($row['created_date'])); ?>
								</td>
								<td>
									<?php echo date('H:i',strtotime($row['created_date'])); ?>
								</td>
								<td>
									<?php echo $row['title_name']; ?>
								</td>
								<td>
									<?php
									if( $row['StaffID'] != '' ){ // sats staff
										echo "{$row['FirstName']} {$row['LastName']}";
									}else{ // agency portal users
										echo "{$row['fname']} {$row['lname']}";
									}
									?>
								</td>
								<td>
									<?php 
									$params = array(
										'log_details' => $row['details'],
										'log_id' => $row['log_id']
									);								
									echo $crm->parseDynamicLink($params);
									?>
								</td>
							</tr>			
						<?php	
						} ?>
					</table>
					<?
					//}
					?>
				
				
					<?php
									
					// Initiate pagination class
					$jp = new jPagination();

					$per_page = $pagi_limit;
					$page = ($_GET['page']!="")?$_GET['page']:1;
					$pagi_offset = ($_GET['offset']!="")?$_GET['offset']:0;	

					echo $jp->display($page,$ptotal,$per_page,$pagi_offset,$pagi_params);
					
					?>
				</div>
			</div>
			
		
			
				
				
			<!-- FILES -->
			<div class="c-tab" data-tab_cont_name="agency_files">
				<div class="c-tab__content">
				
				
				<div class="agency_files_div">
				
					<h2 class="heading">Agency Files</h2>
					
					<?php if( $_REQUEST['upload_success']==1 ){ ?>
						<div class='success'>File Uploaded Successfully</div>
					<?php	
					} ?>
					
					<?php if( $_REQUEST['upload_success']==2 ){ ?>
						<div class='success'>File Deleted Successfully</div>
					<?php	
					} ?>
					
					<table>
						<tr class="vad_tbl_row">
							<th class="colorwhite bold" style="width: 94%;">File Name</th>
							<th class="colorwhite bold">Delete</th>
						</tr>
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
				
							foreach($property_files as $file)
							{
								echo "<tr>
								<td>
									<a href='/agency_files/". $agency_id . "/" . $file . "' target='_blank'>" . $file . "</a>
								</td>
								<td>
									<a href='?id=" . $agency_id . "&delfile=" . rawurlencode($file) . "#uploads' class='delfile'>Delete</a>
								</td>";				
							}
					
						}
						
						?>
					</table>
					
					<div class="upload_add_div">
					
						<div style="float: left; margin-right: 26px;">
							<button type="button" class="submitbtnImg btn_add_upload">
								<img class="inner_icon" src="images/add-button.png">
								File
							</button>
						</div>
						
						<div class="upload_form_div">
							<form action="#uploads" enctype="multipart/form-data" method="post">
							
								<label style="margin-right: 10px;">File:</label>
								<input type="file" style="float: left; margin-right: 10px;" id="fileupload" name="fileupload" class="submitbtnImg"> 						
								<button style="display:none; float: left; margin-top: 5px;" class='addinput submitbtnImg eagdtbt btn_upload_now' id="btn_upload_now" type="submit">
									<img class="inner_icon" src="images/add-button.png">
									Upload Now
								</button>
							
							</form>
						</div>
					</div>
					
					
					
				</div>
				
				<div style="clear:both;"></div>
				
				<div>
		
					<h2 class="heading">Contractor Appointment Form</h2>

					<?php if( $_REQUEST['ca_upload_success']==1 ){ ?>
						<div class='success'>File Uploaded Successfully</div>
					<?php	
					} ?>
					
					<?php if( $_REQUEST['ca_del_success']==1 ){ ?>
						<div class='success'>File Deleted Successfully</div>
					<?php	
					} ?>
					
					
					<table>
					<tr class="vad_tbl_row">
						<th class="colorwhite bold" style="width: 94%;">File Name</th>
						<th class="colorwhite bold">Delete</th>
					</tr>
					
					<?php
					$jparams = array(
						'agency_id' => $agency_id,
						'country_id' => $country_id
					);
					$ca_sql = $crm->getContractorAppointment($jparams);
					if( mysql_num_rows($ca_sql)>0 ){						
						while( $ca = mysql_fetch_array($ca_sql) ){ ?>
							<tr>
								<td><a href="<?php echo $ca['file_path']; ?>" target="_blank"><?php echo $ca['file_name']; ?></a></td>					
								<td><a class="caf_delete" data-ca_id="<?php echo $ca['contractor_appointment_id']; ?>" href="javascript:void(0);" >Delete</a></td>
							</tr>
						<?php	
						}
					}
					?>
				
					</table>
					
					
					<div class="upload_add_div">
					
						<div style="float: left; margin-right: 26px;">
							<button type="button" class="submitbtnImg btn_add_upload">
								<img class="inner_icon" src="images/add-button.png">
								Form
							</button>
						</div>
						
						<div class="upload_form_div">
							<form action="<?php echo "/upload_contractor_appointment.php" ?>#uploads" enctype="multipart/form-data" method="post">
						
									
									<label style="margin-right: 10px;">File:</label>
									<input type="file" capture="camera" style="float: left; margin-right: 10px;" id="upload_cont_app_frm" name="upload_cont_app_frm" class="submitbtnImg"> 			
									<button style="display:none; float: left; margin-top: 5px;" id="btn_add_caf" class='addinput submitbtnImg eagdtbt btn_add_caf' type="submit">
										<img class="inner_icon" src="images/add-button.png">
										Upload Now
									</button>
									<input type="hidden" name="hid_agency_id" value="<?php echo $agency_id; ?>" />
							
							</form>
						</div>
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
							<input type="hidden" name="btn_search" value="Search" class="submitbtnImg">
							<button type="submit" class="submitbtnImg">
								<img class="inner_icon" src="images/button_icons/search-button.png">
								Search
							</button>
						  </div>
						  
						  <div class="fl-left">
							<a href='export_agency_properties.php?agency_id=<?php echo $agency_id; ?>&p_deleted=<?php echo $p_deleted; ?>'>
								<button type="button" class="submitbtnImg">
									<img class="inner_icon" src="images/button_icons/export.png">
									Export
								</button>
							</a>
						  </div>						  					
						  
						</div>
						
					</form>
		

			

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
				$limit = 100;
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

		
				?>
				
				<table border=0 cellspacing=1 cellpadding=5 width=100%>

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
					?>
						<td width="100"><img src="images/serv_img/<?php echo getServiceIcons($as['ajt_id'],1); ?>" /></td>
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
				while ($p_row = mysql_fetch_array($result)){ 
				
				
				if($p_row['deleted']==1){
					$bg_color = '#eeeeee'; 
				}else{
					
					if(is_odd($odd)){
					$bg_color = '#cccccc'; 
					}else{
					$bg_color = '#ffffff'; 
					}
					
				}
				?>
				<tr style="background-color:<?php echo $bg_color; ?>">



				<td style='text-align: left;!important'>		
					<a href='view_property_details.php?id=<?php echo $p_row['property_id'] ?>'><?php echo "{$p_row['address_1']} {$p_row['address_2']}"; ?></a>
				</td>

				<td style='text-align: left;!important'><?php echo $p_row['address_3']; ?></td>

				<td style='text-align: left;!important'><?php echo $p_row['state']; ?></td>

				<?php
				// get agency services
				$as_sql = mysql_query("
					SELECT *
					FROM `agency_services` AS as2
					LEFT JOIN `alarm_job_type` AS ajt ON as2.`service_id` = ajt.`id` 
					WHERE as2.`agency_id` ={$agency_id}
				");
				while($as = mysql_fetch_array($as_sql)){ ?>
					<td style='text-align: left;!important'><?php echo get_services($p_row['property_id'],$as['service_id']); ?></td>
				<?php
				}		
				?>

				<td style='text-align: left;!important'><?php echo  ($p_row['deleted']==1)?'Deleted':'Active'; ?></td>				

				<td><input type="checkbox" class="prop_chk" name="prop_chk[]" value="<?php echo $p_row['property_id']; ?>" /></td>
							
				</tr>


				<?php

				}
				?>
				</table>
				
				
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

			<!-- Accounts TAB -->
			<div class="c-tab" data-tab_cont_name="accounts">
				<div class="c-tab__content">

					<?php
					//if( strpos($_SERVER['SERVER_NAME'],"crmdev") !== false ){

					// pagination
					$pagi_offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
					$pagi_limit = 50;

					$this_page = $_SERVER['PHP_SELF'];
					$pagi_params = "&{$_SERVER['QUERY_STRING']}";

					$next_link = "{$this_page}?offset=".($pagi_offset+$pagi_limit).$pagi_params;
					$prev_link = "{$this_page}?offset=".($pagi_offset-$pagi_limit).$pagi_params;
				
					// NEW LOGS TABLE
					// paginate
					// show only log title, payments(43) and agency payments(47)
					$custom_filter = "
					AND l.`title` IN(43,47)
					";
					$params = array(
						'custom_select' => '
							l.`log_id`,
							l.`created_date`,
							l.`title`,
							l.`details`,				
							
							ltit.`title_name`,
							
							aua.`fname`,
							aua.`lname`,
							aua.`photo`,

							sa.StaffID,
							sa.FirstName,
							sa.LastName
						',
						'custom_filter' => $custom_filter,
						'agency_id' => $agency_id,
						'display_in_vad' => 1,
						'deleted' => 0,
						'paginate' => array(
							'offset' => $pagi_offset,
							'limit' => $pagi_limit
						),
						'sort_list' => array(
							array(
								'order_by' => 'l.`created_date`',
								'sort' => 'DESC'
							)
						),
						'echo_query' => 0
					);
					$result = $crm->getNewLogs($params);

					// all row
					$params = array(
						'custom_select' => 'l.`log_id`',
						'custom_filter' => $custom_filter,
						'agency_id' => $agency_id,
						'display_in_vad' => 1,
						'deleted' => 0
					);
					$ptotal = mysql_num_rows($crm->getNewLogs($params));
					
					
					?>
					<h2 class="heading">New logs</h2>
					<table style="border:1px solid #cccccc !important;" border="0" cellpadding="5" cellspacing="1" class="table-left jb-cnt-lg vjc-log">	
						<tr bgcolor="#b4151b">
						<td class='colorwhite bold'>Date</td>
						<td class='colorwhite bold'>Time</td>
						<td class='colorwhite bold'>Title</td>
						<td class='colorwhite bold'>Who</td>
						<td class='colorwhite bold' style="width: 53%;">Details</td>
						</tr>
						<?php
						// (3) While there are still rows in the result set,
						// fetch the current row into the array $row
						while ($row = mysql_fetch_array($result))
						{
						?>
							<tr class="border-none">
								<td>
									<?php echo date('d/m/Y',strtotime($row['created_date'])); ?>
								</td>
								<td>
									<?php echo date('H:i',strtotime($row['created_date'])); ?>
								</td>
								<td>
									<?php echo $row['title_name']; ?>
								</td>
								<td>
									<?php
									if( $row['StaffID'] != '' ){ // sats staff
										echo "{$row['FirstName']} {$row['LastName']}";
									}else{ // agency portal users
										echo "{$row['fname']} {$row['lname']}";
									}
									?>
								</td>
								<td>
									<?php 
									$params = array(
										'log_details' => $row['details'],
										'log_id' => $row['log_id']
									);								
									echo $crm->parseDynamicLink($params);
									?>
								</td>
							</tr>			
						<?php	
						} ?>
					</table>
					<?
					//}
					?>
				
				
					<?php
									
					// Initiate pagination class
					$jp = new jPagination();

					$per_page = $pagi_limit;
					$page = ($_GET['page']!="")?$_GET['page']:1;
					$pagi_offset = ($_GET['offset']!="")?$_GET['offset']:0;	

					echo $jp->display($page,$ptotal,$per_page,$pagi_offset,$pagi_params);
					
					?>

				</div>
			</div>
			
		
			<!-- API TAB -->
			<div class="c-tab" data-tab_cont_name="api">
				<div class="c-tab__content">
				
							<table class="table api_integ_div" style="width:auto;">

								<thead>
									<tr>									
										<th>Software</th>
										<th>Available to Connect</th>
										<th>API Active</th>
										<th>Marker Name</th>
										<th>Marker ID</th>
										<th>Edit</th>
									</tr>
								</thead>

							<?php 
							/*
							$api_sql = mysql_query("
								SELECT ``
								FROM `agency_api_integration` AS agen_api_int
								LEFT JOIN `agency_api` AS agen_api ON agen_api_int.`connected_service` = agen_api.`agency_api_id`
								WHERE agen_api_int.`agency_id` = {$agency_id}
							");
							*/
							$custom_select = "
								agen_api_int.`api_integration_id`,		
								agen_api_int.`connected_service`,
								agen_api_int.`active`,
								agen_api_int.`date_activated`,

								agen_api.`api_name`,
								agen_api.`agency_api_id`
							";
							$agency_api_integ_params = array(
								'custom_select' => $custom_select,
								'agency_id' => $agency_id,
								'sort_query' => 'agen_api.`api_name` ASC',
								'display_echo' => 0
							);
							$agency_api_integ_sql = $crm->get_agency_api_integration($agency_api_integ_params);

							if( mysql_num_rows($agency_api_integ_sql) > 0 ){ ?>
								<tbody>
									<?php										
									while( $api_row = mysql_fetch_array($agency_api_integ_sql) ){ ?>

									<tr>									
										<td>

											<span class="txt_lbl">
												<?php 
													echo $api_row['api_name'];
												?>
											</span>

											<span class="txt_hid">
												<select name="edit_api_connected_service" title="Connected Service" class="edit_api_connected_service">
													<option value="">----</option>
													<?php
													$custom_select = "
														agency_api_id,
														`api_name`
													";
													$agency_api_params = array(
														'custom_select' => $custom_select,
														'active' => 1,
														'sort_query' => '`api_name` ASC'
													);
													$agency_api_sql = $crm->get_agency_api($agency_api_params);
													while( $agency_api_row = mysql_fetch_array($agency_api_sql)  ){ ?>
														<option value="<?php echo $agency_api_row['agency_api_id']; ?>" <?php echo ( $agency_api_row['agency_api_id'] == $api_row['connected_service'] )?'selected="selected"':''; ?>>
															<?php echo $agency_api_row['api_name']; ?>
														</option>
													<?php
													}
													?>													
												</select>
											</span>

										</td>

										<td>

											<span class="txt_lbl">
												<?php echo ( $api_row['active'] == 1 )?'<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>'; ?>
											</span>

											<span class="txt_hid">
												<select name="edit_api_status" title="Connected Service" class="edit_api_status">								
													<option value="1" <?php echo ( $api_row['active'] == 1 )?'selected="selected"':''; ?>>Yes</option>
													<option value="0" <?php echo ( $api_row['active'] == 0 )?'selected="selected"':''; ?>>No</option>																					
												</select>
											</span>

										</td>
				
										<td>
											<?php
											
											// check if connected to API
											$custom_select = "`agency_api_token_id`";
											$api_tokens_params = array(
												'custom_select' => $custom_select,
												'active' => 1,
												'agency_id' => $agency_id,
												'api_id' => $api_row['connected_service'],
											);
											$api_tokens_sql = $crm->get_agency_api_tokens($api_tokens_params);
											$api_tokens_row = mysql_fetch_array($api_tokens_sql);

											echo ( $api_tokens_row['agency_api_token_id'] > 0 )?'<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
											
											?>
											<input type="hidden" class="agency_api_token_id" value="<?php echo $api_tokens_row['agency_api_token_id']; ?>" />
										</td>

										<td>
											<?php 
												$params = array(
													'status' => 'active',
													'country_id' => $_SESSION['country_default'],
													'agency_id' => $agency_id
												);
												$agen_sql = $crm->getAgency($params);
												$agen = mysql_fetch_array($agen_sql);

												if ($api_row['agency_api_id'] == 1) { // PME

													$agency_api_params = array(
														'contact_id' => $agen['pme_supplier_id'],
														'agency_id' => $agency_id
													);
													$contact_json = $agency_api->get_contact($agency_api_params);
													$contact_json_enc = json_decode($contact_json);
													echo $contact_json_enc->Contact->Reference;
													
												}else if($api_row['agency_api_id'] == 4){ // Palace
													
													$agency_api_params = array(
														'code' => $agen['palace_diary_id'],
														'agency_id' => $agency_id
													);
													$palace_diary_json = $agency_api->get_palace_diary_by_id($agency_api_params);																								
													echo $palace_diary_json->DiaryGroupDescription;

												}

											?>
										</td>

										<td>
											<?php 
												if ($api_row['agency_api_id'] == 1) { // PMe
													echo $agen['pme_supplier_id'];
												} else if ($api_row['agency_api_id'] == 4){ // Palace
													echo $agen['palace_diary_id'];
												}
											?>
										</td>

										<td>
											<button type="button" class="addinput submitbtnImg eagdtbt blue-btn btn_edit_api_integ">Edit</button>
											<div class="action_div">
												<button class="blue-btn submitbtnImg btn_update_api_integ">
												<img class="inner_icon" src="images/button_icons/save-button.png">
												Update
												</button>		
												<button class="submitbtnImg btn_cancel_api_integ">
													<img class="inner_icon" src="images/button_icons/back-to-tech.png">
													Cancel
												</button>
												<input type="hidden" class="api_integration_id" value="<?php echo $api_row['api_integration_id']; ?>" />
												<input type="hidden" class="api_id" value="<?php echo $api_row['connected_service']; ?>" />
											</div>
											
											<?php
											if( in_array($staff_id,$allowed_people_to_pme_unlink) ){

												if( $api_tokens_row['agency_api_token_id'] > 0 ){ ?>
													<button type="button" class="addinput submitbtnImg eagdtbt remove_agency_token_btn" style="margin-left: 8px;">Remove API Token</button>
												<?php
												}else{ // only allow delete if token is not present												
												?>
													<button type="button" class="addinput submitbtnImg eagdtbt btn_delete_api_integ" style="margin-left: 8px;">Remove API</button>
												<?php
												}

											}											
											?>										
											
										</td>
									</tr>

									<?php
									}
									?>																				
								</tbody>								
							<?php
							}							
							?>	
														
							</table>


							<button type="button" id="btn_show_add_api_form" class="addinput submitbtnImg eagdtbt blue-btn" style="margin-top: 10px;"> 
									<img class="inner_icon" src="images/add-button.png">
									Add API integration
							</button>

							<div style="clear:both">&nbsp;</div>


							<form id="api_integration_form" action="add_agency_api_integration.php" method="post">

									<table class="table" style="width:auto;">

									<!-- hard coded as sir dan instruction -->
									<tr>
										<td><strong>Software</strong></td>
										<td>
												<select name="connected_service" id="api_connected_service" title="Connected Service" class="addinput connected_service">
													<option value="">----</option>
													<?php
													$custom_select = "
														agency_api_id,
														`api_name`
													";
													$agency_api_params = array(
														'custom_select' => $custom_select,
														'active' => 1,
														'sort_query' => '`api_name` ASC'
													);
													$agency_api_sql = $crm->get_agency_api($agency_api_params);
													while( $agency_api_row = mysql_fetch_array($agency_api_sql)  ){ ?>
														<option value="<?php echo $agency_api_row['agency_api_id']; ?>">
															<?php echo $agency_api_row['api_name']; ?>
														</option>
													<?php
													}
													?>															
												</select>
										</td>
									</tr>									

									<tr>
										<td></td>
										<td>
											<button class="addinput submitbtnImg eagdtbt btn-save_api_integ" id="btn-save_api_integ" type="submit" style="float: none; margin-top: 15px;">
												<img class="inner_icon" src="images/save-button.png">
												Save
											</button>	
											<input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>" />
										</td>
									</tr>
									</table>
															


													
											
							</form>


							<?php													
							if( in_array($staff_id,$allowed_people_to_pme_unlink) ){ ?>
								<button id="btn_unlink_connected_api_prop" class="addinput submitbtnImg" type="button" style="width: auto;">								
									Unlink Connected API Properties
								</button>	
							<?php
							}
							?>

							<div style="clear:both;"></div>

							<div>
							
								<table class="table" style="margin-top: 15px;">
									<tr>
										<td style="width: 88px;">API Billable?</td>
										<td style="width: 37px;">		
											<?php 
												//tweak for api_billable > only selected user can edit api_billable checkbox
												if($_SESSION['country_default']==1){ //AU
													$sel_can_edit_user = array(2025,11,2175,2070); //Daniel,Ness,Thalia id in AU
													if (!in_array($_SESSION['USER_DETAILS']['StaffID'], $sel_can_edit_user)){
														$read_only_api_billable = "onclick='return false;'";
													}else{
														$read_only_api_billable = NULL;
													}
												}else{//NZ
													$sel_can_edit_user = array(2025,2193,11,2191); ////Daniel,Ness,Thalia,Tayler in in NZ
													if (!in_array($_SESSION['USER_DETAILS']['StaffID'], $sel_can_edit_user)){
														$read_only_api_billable = "onclick='return false;'";
													}else{
														$read_only_api_billable = NULL;
													}
												}
											?>				
											<input <?php echo $read_only_api_billable; ?> type="checkbox" value="1" id="api_billable" <?php echo ( $agency_row['api_billable'] == 1 )?'checked':null; ?> />
										</td>
										<td>
											<img style="width: 15px; display:none;" id="api_billable_green_tick" class="green_check" src="/images/check_icon2.png" />
										</td>
									</tr>
								</table>								
								
							</div>
							
							<!-- No Bulk Match > Gherx Start-->
							<div class="no_bulk_match_div"> 
							<table class="table" style="margin-top: 15px;">
									<tr>
										<td style="width: 88px;">Generate warning on bulk match?</td>
										<td style="width: 37px;">		
											<?php 
												//tweak for api_billable > only selected user can edit api_billable checkbox
												if($_SESSION['country_default']==1){ //AU
													if (!in_array($_SESSION['USER_DETAILS']['StaffID'], $sel_can_edit_user)){
														$read_only_no_bulk_match = "onclick='return false;'";
													}else{
														$read_only_no_bulk_match = NULL;
													}
												}else{//NZ

													if (!in_array($_SESSION['USER_DETAILS']['StaffID'], $sel_can_edit_user)){
														$read_only_no_bulk_match = "onclick='return false;'";
													}else{
														$read_only_no_bulk_match = NULL;
													}
												}
											?>				
											<input <?php echo $read_only_no_bulk_match; ?> type="checkbox" value="1" id="no_bulk_match" <?php echo ( $agency_row['no_bulk_match'] == 1 )?'checked':null; ?> />
										</td>
										<td>
											<img style="width: 15px; display:none;" id="no_bulk_match_green_tick" class="green_check" src="/images/check_icon2.png" />
										</td>
									</tr>
								</table>	
							</div>
							<!-- No Bulk Match > Gherx End-->
							

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
	

<style>
.timestamp_style{
	color: #00D1E5;
	font-style: italic;
}    

#onboading_tbl table tr td {
    padding: 5px;
}
.done_col{
	padding: 1px 25px !important;
	text-align: center;
}
.multi_owner_discount_lbl{
	float: left !important; 
	margin-right: 8px !important; 
	position: relative !important; 
	top: 8px !important;
}
</style>
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


		// API billable script
		jQuery("#api_billable").change(function(){

			var node = jQuery(this);
			var api_billable = ( node.prop("checked") == true )?1:0;

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "ajax_api_billable_toggle.php",
				data: { 
					agency_id: <?php echo $agency_id; ?>,
					api_billable: api_billable
				}
			}).done(function( ret ) {
				jQuery("#load-screen").hide();
				jQuery("#api_billable_green_tick").show();
			});	

		});


		// clear Pme Prop ID field(`propertyme_prop_id`) in ALL properties, under this agency
		jQuery("#btn_unlink_connected_api_prop").click(function(){

			if( confirm("This will unlink ALL property that are connected to API under this agency. Are you sure you want to proceed?") ){

				jQuery.ajax({
					type: "POST",
					url: "ajax_unlink_connected_api_prop.php",
					data: { 
						agency_id: <?php echo $agency_id; ?>

					}
				}).done(function( ret ) {
					window.location="/view_agency_details.php?id=<?php echo $agency_id; ?>&unlink_pme_prop=1";
				});	
				
			}

		});


		// linline update api integration
		jQuery(".btn_update_api_integ").click(function(){
		
		var api_integration_id = jQuery(this).parents("tr:first").find(".api_integration_id").val();
		var agency_api_token_id = jQuery(this).parents("tr:first").find(".agency_api_token_id").val();
		var connected_service = jQuery(this).parents("tr:first").find(".edit_api_connected_service").val();
		var status = jQuery(this).parents("tr:first").find(".edit_api_status").val();
		var date_activated = jQuery(this).parents("tr:first").find(".edit_api_date_activated").val();
		var error = "";
		
		if( connected_service == "" ){
			error += "Connected Service is required";
		}

		if(  status == 0 && agency_api_token_id != '' ){
			error += "Cannot Update Available to Connect to NO if agency access token exist. remove it first";
		}
		
		if(error != ""){
			alert(error);
		}else{			
			
			
			jQuery.ajax({
				type: "POST",
				url: "ajax_update_agency_api_integration.php",
				data: { 
					api_integration_id: api_integration_id,
					connected_service: connected_service,
					status: status,
					date_activated: date_activated,
					agency_id: <?php echo $agency_id; ?>

				}
			}).done(function( ret ) {
				window.location="/view_agency_details.php?id=<?php echo $agency_id; ?>&api_integ_update=1";
			});	
			
						
		}		
		
	});


	// inline edit toggle api integration
	jQuery(".btn_edit_api_integ").click(function(){
		
		var btn_txt = jQuery(this).html();
		
		jQuery(this).hide();
		
		if( btn_txt == 'Edit' ){			
			jQuery(this).parents("tr:first").find(".action_div").show();
			jQuery(this).parents("tr:first").find(".txt_hid").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
		}else{
			jQuery(this).parents("tr:first").find(".action_div").hide();
		}
				
	});


	// delete api integration
	jQuery(".btn_delete_api_integ").click(function(){
		
		var api_integration_id = jQuery(this).parents("tr:first").find(".api_integration_id").val();	
		var api_id = jQuery(this).parents("tr:first").find(".api_id").val();

		if( confirm('This will delete this API integration. Proceed?') ){

			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_agency_api_integration.php",
				data: { 
					api_integration_id: api_integration_id,
					api_id: api_id,
					agency_id: <?php echo $agency_id; ?>
				}
			}).done(function( ret ) {
				window.location="/view_agency_details.php?id=<?php echo $agency_id; ?>&api_integ_deleted=1";
			});	
			
		}	
				
				
	});


	// delete api token
	jQuery(".remove_agency_token_btn").click(function(){
		
		var agency_api_token_id = jQuery(this).parents("tr:first").find(".agency_api_token_id").val();
		var api_integration_id = jQuery(this).parents("tr:first").find(".api_integration_id").val();
		var api_id = jQuery(this).parents("tr:first").find(".api_id").val();

		if( confirm('This will delete this API token. Proceed?') ){

			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_agency_api_token.php",
				data: { 
					agency_api_token_id: agency_api_token_id,
					api_id: api_id,
					agency_id: <?php echo $agency_id; ?>
				}
			}).done(function( ret ) {
				window.location="/view_agency_details.php?id=<?php echo $agency_id; ?>&api_token_deleted=1";
			});	
			
		}	
				
				
	});


	// cancel api integration inline edit
	jQuery(".btn_cancel_api_integ").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".btn_edit_api_integ").show();		
	});


	// don't allow same API serviced to be added
	jQuery("#api_connected_service").change(function(){

	var obj = jQuery(this);
	var connected_service = obj.val();
	var agency_id = <?php echo $agency_id  ?>;

	jQuery("#load-screen").show();
	jQuery.ajax({
			type: "POST",
			url: "ajax_check_agency_api_integration_selected.php",
			data: { 
				agency_id: agency_id,
				connected_service: connected_service
			}
		}).done(function( ret ){
			
			jQuery("#load-screen").hide();
			if( parseInt(ret) > 0 ){
				alert('API service already selected');
				obj.find("option:eq(0)").prop("selected",true); // unselect
			}

		

		});	


	});


	// show API integration form
	jQuery("#btn_show_add_api_form").click(function(){

		jQuery("#api_integration_form").show();

	});



	// agency info marker update
	jQuery(".onboarding_id").click(function(){

		var obj = jQuery(this);
		var onboarding_id = obj.val();
		var agency_id = <?php echo $agency_id  ?>;
		var is_ticked = ( obj.prop("checked") == true )?1:0;

		jQuery("#load-screen").show();
		jQuery.ajax({
				type: "POST",
				url: "ajax_update_agency_onboarding_selection.php",
				data: { 
					agency_id: agency_id,
					onboarding_id: onboarding_id,
					is_ticked: is_ticked
				},
				dataType: 'json'
			}).done(function( ret ){
				
				if( is_ticked == 1 ){
					obj.parents("tr:first").find('.ob_check_icon').show();
					obj.parents("tr:first").find('.ob_updated_by').html(ret.updated_by);
					obj.parents("tr:first").find('.ob_updated_date').html(ret.updated_date);
				}else{
					obj.parents("tr:first").find('.ob_check_icon').hide();
					obj.parents("tr:first").find('.ob_updated_by').html('');
					obj.parents("tr:first").find('.ob_updated_date').html('');
				}				
				jQuery("#load-screen").hide();

			});	
		

	});

	
	
	
	// reset password email
	jQuery(".reset_pass_email_link").click(function(e){
		
		if( confirm('This will send reset password email. Proceed?') ){
			
		}else{
			e.preventDefault();
		}		
		
	});
	
	
	// invite email
	jQuery(".invite_email_link").click(function(e){
		
		if( confirm('This will send invite email. Proceed?') ){
			
		}else{
			e.preventDefault();
		}		
		
	});
	
	
	
	
	// TAS script
	jQuery("#trust_acc_soft").change(function(){
		
		var option_val = jQuery(this).val();
		
		console.log("tas: "+option_val);
		
		if( option_val > 0 ){
			jQuery("#tas_connected_div").show();
		}else{
			jQuery("#tas_connected_div").hide();
		}
		
	});
	
	
	
	// sales snapshot log script
	jQuery("#add_to_snapshot").change(function(){
		
		var checked = jQuery(this).prop("checked");
		if( checked == true ){
			jQuery("#ss_status").show();
		}else{
			jQuery("#ss_status").hide();
		}	
		
	});
	
	
	
	jQuery(".btn_add_upload").click(function(){
		jQuery(this).parents(".upload_add_div:first").find(".upload_form_div").show();
	})
	
	
	
	jQuery("#btn_hard_delete").click(function(){
		
		if( confirm("WARNING!!!! You are about to delete this agency! once deleted it cannot be undone. Are you sure you want to continue?") ){
			
			// hard delete
			jQuery.ajax({
				type: "POST",
				url: "ajax_hard_delete_agency.php",
				data: { 
					agency_id: <?php echo $agency_id; ?>,
				}
			}).done(function( ret ){
				var num_prop = parseInt(ret);
				if(num_prop>0){
					alert("ABORTED!!! cannot delete this agency. properties under this agency still exist");
				}else{
				<?php
				if( $row['status'] == 'target' ){ ?>
					window.location="view_target_agencies.php?agency_deleted=1";
				<?php
				}else if( $row['status'] == 'deactivated' ){ ?>
					window.location="view_deactivated_agencies.php?agency_deleted=1";
				<?php	
				}else{ ?>
					window.location="view_agencies.php?agency_deleted=1";
				<?php
				}
				?>					
				}
			});	
			
		}
		
	});
	
	
	// show upload button script
	jQuery("#fileupload").change(function(){
		
		var file = jQuery(this).val();
		if( file != '' ){
			jQuery("#btn_upload_now").show();	
		}		
		
	});
	
	// show upload button script
	jQuery("#upload_cont_app_frm").change(function(){
		
		var file = jQuery(this).val();
		if( file != '' ){
			jQuery("#btn_add_caf").show();	
		}		
		
	});
	
	
	// Maintenance Program flag
	jQuery(".mm_prog").change(function(){
		jQuery("#mm_program_edited").val(1);
	});
	
	
	// agency alarms edited flag
	jQuery(".alarm_price").change(function(){
		jQuery("#agency_alarms_edited").val(1);
		jQuery(this).parents("tr:first").find(".agency_alarms_changed").val(1);
	});
	// agency alarms edited flag
	jQuery(".upgrade_alarm_price").change(function(){
		jQuery("#agency_alarms_edited").val(1);
	});
	jQuery(".agency_alarm_approve").change(function(){
		jQuery("#agency_alarms_edited").val(1);

		if($(this).is(":checked")){
			jQuery(this).parents("tr:first").find(".alarm_checked").val(1);
		}else{
			jQuery(this).parents("tr:first").find(".alarm_checked").val(0);
		}
		
	})
	
	// agency services edited flag
	jQuery(".service_price").change(function(){
		jQuery("#agency_services_edited").val(1);
		jQuery(this).parents("tr:first").find(".agency_service_changed").val(1);
	});
	jQuery(".agency_service_approve").change(function(){
		jQuery("#agency_services_edited").val(1);
	});
	
	
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
	
	
	
	// submission
	jQuery(".btn-update").click(function(){
		
		//alert('test trigger');
	
		
		var agency_name = jQuery("#agency_name").val();
		var region = jQuery("#region_id").val();
		var agency_emails = jQuery("#agency_emails").val();
		var account_emails = jQuery("#account_emails").val();
		var active_prop_with_sats = jQuery("#active_prop_with_sats").val();
		var agency_status = jQuery("#status").val();
		var error = "";
		var flag = 0;
		
		
		
		<?php
		if( $row['state'] == 'QLD' ){ ?>

			if( agency_status =='active' ){

				jQuery(".tbl_servicing .agency_service_approve").each(function(){
				
					var isChecked = jQuery(this).prop("checked");
					var id = jQuery(this).parents("td:first").find(".service_id").val();
					
					if( id == 12 && isChecked == false ){ //  Smoke Alarms (IC) 
						error += "Smoke Alarms (IC) is required\n";
					}
					
				});
				
				jQuery(".tbl_servicing .agency_alarm_approve").each(function(){
					
					var isChecked = jQuery(this).prop("checked");
					var id = jQuery(this).parents("td:first").find(".alarm_id").val();
					
					if( id == 10 && isChecked == false ){ //  240v RF
						error += "240v RF is required\n";
					}
					
				});

			}
		
			
			
		<?php
		}
		?>
		
		
		if(agency_name==""){
			error += "Agency Name is required\n";
		}
		
		<?php
		// required only on active
		if( $row['status']=='active' ){ ?>
		
			if(agency_emails==""){
				error += "Agency emails are required\n";
			}
			if(account_emails==""){
				error += "Account emails is required\n";
			}
		
		<?php	
		}
		?>
		
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
			//error += 'Some property manager email is invalid format\n';
		}
		
		if(agency_status=='deactivated'){
			if(active_prop_with_sats==""){
				error += "Active Properties with SATS is required\n";
			}
		}

		if( agency_status == 'active' ){

			// alarm required
			var alarms_ticked_num = jQuery(".agency_alarm_approve:checked").length;
			if( alarms_ticked_num == 0 ){
				error += "Alarm is required\n";
			}

			// service required
			var alarms_ticked_num = jQuery(".agency_service_approve:checked").length;
			if( alarms_ticked_num == 0 ){
				error += "Service is required\n";
			}


			// 240v RF alarm is required on QLD state
			var state = jQuery("#state").val();

			if( state == 'QLD' ){

				// SA IC
				var sa_ic_row_node = jQuery(".sa_ic_row");
				var agency_service_approve_node = sa_ic_row_node.find('.agency_service_approve');
				var service_price_node = sa_ic_row_node.find('.service_price');
				var has_req_for_quotes_alarm = false;

				if( agency_service_approve_node.prop("checked") == false  ){ // not ticked
					error += "Smoke Alarms (IC) is required\n";
				}else{ // ticked

					if(  !( parseFloat(service_price_node.val()) > 0 ) ){
						error += "Smoke Alarms (IC) (Required for Quotes) service price must be greater than $0\n";
					}

				}

				// 'required for quotes' required validation
				var is_alarm_req_for_quotes_row_node = jQuery(".is_alarm_req_for_quotes_row"); // 'required for quotes' alarms
				var agency_alarm_approve_node = is_alarm_req_for_quotes_row_node.find('.agency_alarm_approve:checked'); // approved checkbox
				var alarm_price_dom = is_alarm_req_for_quotes_row_node.find('.alarm_price'); // alarm price
				
				var agency_price_is_empty = false;
				var agency_price_is_zero = false;
				is_alarm_req_for_quotes_row_node.each(function(){

					var alarm_req_for_quotes_row_node = jQuery(this);
					var agen_price = alarm_req_for_quotes_row_node.find('.alarm_price').val(); // alarm price					
					
					if( agen_price != '' ){ // not empty
						
						if( !(agen_price > 0) ){
							agency_price_is_zero = true;
						}

					}else{ // empty
						agency_price_is_empty = true;
					}					

				});

				if( agency_price_is_empty == true ){
					error += "'Required for Quotes' alarms price is required\n";
				}

				if( agency_price_is_zero == true ){
					error += "'Required for Quotes' alarms price must be greater than $0\n";
				}
								
				if( agency_alarm_approve_node.length == 0  ){ 
					error += "At least one 'Required for Quotes' alarms must be approved\n";
				}
				

			}

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
	// legal name edited
	jQuery("#legal_name").change(function(){		
		jQuery("#legal_name_edited").val(1);		
	});
	// abn
	jQuery("#abn").change(function(){		
		jQuery("#abn_edited").val(1);		
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
		
		var prompt = '';
		
		if(jQuery(this).val()=="target"){
			var prompt = confirm("If you change status to Target, all Jobs will be cancelled and all properties will be deactivated. Are you sure you want to continue?");
		
			//by gherx > hide deactivate reason field
			$('.deactivate_agency_reason_div').hide();
		}else if(jQuery(this).val()=="deactivated"){
			var prompt = confirm("If you change status to Deactivated, all Jobs will be cancelled and all properties will be deactivated. Are you sure you want to continue?");
			
			//by gherx > show deactivate reason field
			$('.deactivate_agency_reason_div').show();
		}
		
		if( !prompt ){
			jQuery("#status option:first").prop("selected",true);

			//by gherx > hide deactivate reason field
			$('.deactivate_agency_reason_div').hide();
		}
		
	});
	
	
	
	// agency portal status deactivate/restore
	jQuery(document).on("click",".status_toggle_btn",function(){
		
		
		var parent_row = jQuery(this).parents("tr:first");
		var pm_id = parseInt(parent_row.find(".pm_id").val());
		var status = jQuery(this).attr("data-status");
		
		console.log("pm_id: "+pm_id);
		console.log("status: "+status);
		
		
		if( pm_id > 0 ){
			
			var confirm_txt;
			
			if( status == 1 ){ // activate
				confirm_txt = 'Are you sure you want to restore this user?';
			}else{ // deactivate
				confirm_txt = 'Are you sure you want to deactivate this user?';
			}
			
			//alert('PM id present');
			if( confirm(confirm_txt) ){
				jQuery.ajax({
					type: "POST",
					url: "ajax_activate_deactivate_portal_users.php",
					data: { 
						pm_id: pm_id,
						status: status
					}
				}).done(function( ret ){
					
					if(parseInt(ret)==1){
						alert("There are properties attached to this PM");
					}else{
						location.reload();
					}
					
					
					
				});	
			}
			
		}
		
		
		
	});
	
	
	jQuery(document).on("click",".btn_remove_user_row",function(){
		
		jQuery(this).parents("tr:first").remove();
		
	});
	
	
	
	// add property manager
	jQuery("#btn_add_pm").click(function(){
		
		var pm_row = '<tr>'+
			'<td>'+									
				'<select name="pm_user_type[]">'+											
					'<option value="">---</option>'+ 	
					<?php
					// get user type
					$aua_t_sql = mysql_query("
						SELECT *
						FROM `agency_user_account_types`
						WHERE `active` = 1
					");		
					while( $aua_t = mysql_fetch_array($aua_t_sql) ){ ?>
						'<option value="<?php echo $aua_t['agency_user_account_type_id']; ?>" <?php echo ($aua_t['agency_user_account_type_id'] == $pm['user_type'])?'selected="selected"':'' ?>><?php echo $aua_t['user_type_name']; ?></option>'+ 	
					<?php	
					}
					?>
				'</select>'+
			'<td>'+														
				'<input type="text" name="pm_fname[]" class="addinput pm_fname" />'+
			'</td>'+
			'<td>'+
				'<input type="text" name="pm_lname[]" class="addinput pm_lname" />'+
			'</td>'+
			'<td>'+														
				'<input type="text" name="pm_job_title[]" class="addinput pm_job_title" />'+
			'</td>'+
			'<td>'+
				'<input type="text" name="pm_phone[]" class="addinput pm_phone" />'+
			'</td>'+
			'<td>'+
				'<input type="text" name="pm_email[]" class="addinput pm_email"  />'+
			'</td>'+
			'<td>&nbsp;</td>'+
			'<td>&nbsp;</td>'+
			'<td>&nbsp;</td>'+
			'<td>'+
				'<input type="hidden" name="pm_id[]" class="pm_id" />'+
				'<button class="addinput submitbtnImg eagdtbt btn_remove_user_row" type="button">'+
					'<img class="inner_icon" src="images/cancel-button.png">'+
					'Remove'+
				'</button>'+
			'</td>'+
		'</tr>';
		
		jQuery("#pm_table tbody").append(pm_row);
		
		
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

	//statements_agency_comments changed
	var previousValue = $(".statements_agency_comments_orig_val").val();
	$('.statements_agency_comments').keyup(function(e){
		var currentValue = $(this).val();
		if(currentValue != previousValue) {
			$('.statements_agency_comments_is_changed').val(1);
		}else{
			$('.statements_agency_comments_is_changed').val(0);
		}
	})
	//statements_agency_comments changed end


	jQuery("#no_bulk_match").change(function(){

		var node = jQuery(this);
		var no_bulk_match = ( node.prop("checked") == true )?1:0;

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "ajax_no_bulk_match_toggle.php",
			data: { 
				agency_id: <?php echo $agency_id; ?>,
				no_bulk_match: no_bulk_match
			}
		}).done(function( ret ) {
			jQuery("#load-screen").hide();
			jQuery("#no_bulk_match_green_tick").show();
			setTimeout(function(){ 
				jQuery("#no_bulk_match_green_tick").hide();
			 }, 2000);
		});	

	});

	var ci_link = "<?php echo $crm->crm_ci_redirect("/agency/view_agency_details/{$agency_id}"); ?>";
	//window.location.replace(ci_link);
	
});
parseCharCounts();
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_DEV_API; ?>&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
</body>
</html>
