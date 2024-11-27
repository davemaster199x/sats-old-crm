<?php
$title = "View Deactivated Agency Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');




// init the variables
if ($_GET['id'] == "")
	{ $agency_id = $_POST['id']; }
else
	{ $agency_id = $_GET['id']; }


autoUpdateAgencyRegion($agency_id);
	

?>
<style>
.important_log{
	background-color:#FFCCCB!important; 
	border: 1px solid #b4151b!important; 
	box-shadow: 0 0 2px #b4151b inset!important;
}
</style>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="View Deactivated Agencies" href="/view_deactivated_agencies.php">View Deactivated Agencies</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/view_target_details.php?id=<?php echo $_REQUEST['id']; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  



<form action='view_deactivated_agency_details.php?id=<?php echo $_REQUEST['id']; ?>' method=post name='example' id='example'>
<table border=0 cellspacing=1 cellpadding=5 width="100%" class="all-table">
<tr bgcolor="#b4151b" style="border: 1px solid #B4151B;">
<td class="colorwhite"><b>Name</b></td>
<td class="colorwhite"><b>Address</b></td>
<td class="colorwhite"><b>Phone</b></td>
<td class="colorwhite"><b>State</b></td>
<td class="colorwhite"><b>Postcode</b></td>
<td class="colorwhite"><b>Region</b></td>
<td class="colorwhite"><b>Edit Agency</b></td>
<td class="colorwhite"><b>Make Active</b></td>
<td class="colorwhite"><b>Delete</b></td>
</tr>
<?php



$doaction = '';
if($_POST){	
	$contact_type = mysql_real_escape_string($_POST['contact_type']);
	$eventdate = mysql_real_escape_string($_POST['eventdate']);
	$comments = mysql_real_escape_string($_POST['comments']);
	$doaction = mysql_real_escape_string($_POST['doaction']);
}

//echo "agency_id is $agency_id<br>";
//echo "contact_type is $contact_type<br>";

echo "<input type='hidden' name='id' value='$agency_id'>\n";

if($doaction == "addevent")
{
	// insert the event into the database
	
	$next_contact = ($_POST['next_contact']!="")?date("Y-m-d",strtotime(str_replace("/","-",$_POST['next_contact']))):'';
    $important = $_POST['important']; 
	
	
   $eventdate = convertDate($eventdate);
   // (2) Run the query 
   $insertQuery = "INSERT INTO `agency_event_log` (contact_type,eventdate,comments,agency_id, staff_id, `next_contact`, `important`) VALUES ('$contact_type','$eventdate','$comments','$agency_id', ".$_SESSION['USER_DETAILS']['StaffID'].",'{$next_contact}','{$important}');";

//	echo "insertQuery is <br>$insertQuery<br>\n";

	mysql_query($insertQuery);
    
    if (mysql_affected_rows($connection) == 0)
    	echo "An error occurred creating the event, please report\n";

	
		
} // if doaction
	/* old table
   $insertQuery = "
	SELECT *, a.agency_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.phone 
	FROM agency AS a
	LEFT JOIN `agency_using` AS au ON a.`agency_using_id` = au.`agency_using_id`
	LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id`
	WHERE (a.agency_id='".$agency_id."');
   ";
   */

   	## new table
	$insertQuery = "
	SELECT *, a.agency_name, a.address_1, a.address_2, a.address_3, a.state, a.postcode, a.phone, sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name
	FROM agency AS a
	LEFT JOIN `agency_using` AS au ON a.`agency_using_id` = au.`agency_using_id`
	LEFT JOIN `sub_regions` AS sr ON a.`postcode_region_id` = sr.`sub_region_id`
	LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
	WHERE (a.agency_id='".$agency_id."');
	";

     if (($result = mysql_query ($insertQuery, $connection)) && @ mysql_num_rows() == 0);
     else
        echo "<h3>No Agency Details Returned, that's odd.</h3><br>";

	
   $row = mysql_fetch_array($result);
   
   
	
 
 
		echo "<tr bgcolor=#FFFFFF class=tgt-ag>";		
		
		
		echo "\n";
		echo "<td>";		
		echo $row['agency_name']; //Agency
		echo "</td>";
	 
		echo "<td>";		
		echo $row['address_1'] . " " . $row['address_2'] .  " " . $row['address_3']; //Number Street Suburb
		echo "</td>";	 
		
	 
		echo "<td>";		
		echo $row['phone']; //Phone
		echo "</td>";	 
	 
		echo "<td>";		
		echo $row['state']; //State
		echo "</td>";
		
		echo "<td>";		
		echo $row['postcode']; //Postcode
		echo "</td>";
		
		echo "<td>";		
		echo ( $row['postcode_region_id']!="" )?$row['postcode_region_name']:'No region set up for this postcode';
		echo "</td>";

		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$agency_id}"); 
		echo "<td>
		<a href='" . URL . "edit_agency_details.php?id=$agency_id'>Edit </a> | 
		<a href='" . URL . "$ci_link'>Edit v2</a>
		</td>\n";	
		
		echo "<td>";
		echo "<a href='view_target_agencies.php?act_id=$agency_id&activate=1' onclick='return confirm(\"Are you sure you want to activate {$row['agency_name']}?\")'>Activate</a>";
		//echo "<a href='delete_target_agency.php?id=$row[7]'>delete</a>";
		echo "</td>";
		
		echo "<td>";
		echo "<a href='view_target_agencies.php?del_id=$agency_id' onclick='return confirm(\"Are you sure you want to delete {$row['agency_name']}?\")'>Delete</a>";
		//echo "<a href='delete_target_agency.php?id=$row[7]'>delete</a>";
		echo "</td>";
			
		echo "</tr>";
		echo "\n";

 
?>
<tr bgcolor="#b4151b" style="border: 1px solid #B4151B;">
	<td class="colorwhite"><b>Total Properties</b></td>
	<td class="colorwhite"><b>Currently Using</b></td>
	<td class="colorwhite"><b>Agency Contact</b></td>
	<td class="colorwhite" colspan="100%"><b>Agency Emails</b></td>
</tr>
<tr>
	<td><?php echo $row['tot_properties']; ?></td>
	<td><?php echo $row['name']; ?></td>
	<td><?php echo $row['contact_first_name']." ".$row['contact_last_name']; ?></td>
	<td colspan="100%"><?php echo $row['agency_emails']; ?></td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="1" class="all-table">
			<tr class="tgt-ag-bl" bgcolor="#eee" style="border-top: none !important;">
				<td align="left" width="18%">
                <label class="vpr-adev" for="eventdate">Date</label>
				<input type="text" style="width: 75px; padding-left: 5px;" name="eventdate" value="<?php echo date("d/m/Y"); ?>" class="datepicker" />				
				</td>
				<td axis="centre" align="left" class="cntype" width="10%">
				<label for="contact_type" class="vpr-adev">Contact Type</label>
				<select name="contact_type">
					<option value="Phone Call">Phone Call</option>
					<option value="E-mail">E-mail</option>
					<option value="Cold Call">Cold Call</option>  
					<option value="Cold Call In">Cold Call In</option>  
					<option value="Pop In">Pop In</option>  
					<option value="Follow Up">Follow up</option>  
					<option value="Mailout">Mail-Out</option>
					<option value="Other">Other</option>
					<option value="Pack Sent">Pack Sent</option>   
					<option value="Meeting">Meeting</option> 
					<option value="Conference">Conference</option> 
				</select></td>
				<td axis="centre" width="54%">
				<label for="comments" class="vpr-adev">Comments</label>
				<textarea name="comments" class="addtextarea vpr-adev-txt"></textarea>
				</td>
				
				<td align="left" width="18%">
                <label class="vpr-adev" for="eventdate">Next Contact Date</label>
				<input type="text" name="next_contact" value="" class="datepicker" style="width: 75px; padding-left: 5px;" />				
				</td>
				
				<td align="left" width="18%">
                <label class="vpr-adev" for="eventdate">Important</label>
				<input type="checkbox" name="important" value="1" />				
				</td>
				
				<td  width="12%">
				<input type="hidden" name="doaction" value="addevent">
				<input type="submit" value="Add Event" class="submitbtnImg vpr-adev-btn">
				</td>
			</tr>
		</table>
</form>


<?php
	$type = '';
	$staff = '';
	if($_POST){
		$type = ($_POST['search_type'] != '') ? $_POST['search_type'] : '';
		$staff = ($_POST['search_staff'] != '') ? $_POST['search_staff'] : '';
	}
	
	$Query = "SELECT cr.contact_type, DATE_FORMAT(cr.eventdate,'%d/%m/%Y') as datestamp, cr.comments, cr.`agency_event_log_id`, cr.staff_id, c.FirstName, c.LastName, cr.`next_contact`, cr.`important` FROM `agency_event_log` cr LEFT JOIN staff_accounts c ON cr.staff_id = c.StaffID WHERE (cr.agency_id = $agency_id) ";
	//$Query = "SELECT cr.contact_type, DATE_FORMAT(cr.eventdate,'%d/%m/%Y'), cr.comments, cr.crm_id FROM crm cr WHERE (cr.agency_id = $agency_id) ";
	if($type != '')
		$Query .= "AND cr.contact_type ='".$type."' ";
	
	if($staff != '')
		$Query .= "AND cr.staff_id = ".$staff;
		//$Query .= "AND CONCAT_WS(' ', LOWER(c.first_name), LOWER(c.last_name)) LIKE '%".$staff."%' ";
		
	$Query .= " ORDER BY cr.eventdate DESC";
    //$result = mysql_query ($Query, $connection);
	$result = mysqlMultiRows($Query);
	$contacts = mysqlMultiRows('SELECT DISTINCT(staff_id), FirstName, LastName FROM `agency_event_log`, staff_accounts WHERE staff_id = StaffID AND staff_id IS NOT NULL AND agency_id = '.$agency_id);
	
?>
<h2 class="heading">Activity Log for Target Agency</h2>

<table width="100%" cellspacing="1" cellpadding="5" border="0" class="all-table">
			<form method="POST" action="<?=URL;?>view_deactivated_agency_details.php?id=<?php echo $_REQUEST['id']; ?>" class="searchstyle">
			<tr style="border-bottom: none; background-color: #eee;">
				<td>
					Type : 
					<select name="search_type">
						<option <?php echo ($type == '') ? 'selected="selected"': '';?> value=""></option>
						<option <?php echo ($type == 'Phone Call') ? 'selected="selected"': '';?> value="Phone Call">Phone Call</option>
						<option <?php echo ($type == 'E-mail') ? 'selected="selected"': '';?> value="E-mail">E-mail</option>
						<option <?php echo ($type == 'Cold Call') ? 'selected="selected"': '';?> value="Cold Call">Cold Call</option>  
						<option <?php echo ($type == 'Cold Call In') ? 'selected="selected"': '';?> value="Cold Call In">Cold Call In</option>  
						<option <?php echo ($type == 'Pop In') ? 'selected="selected"': '';?> value="Pop In">Pop In</option>  
						<option <?php echo ($type == 'Follow up') ? 'selected="selected"': '';?> value="Follow Up">Follow up</option>  
						<option <?php echo ($type == 'Mailout') ? 'selected="selected"': '';?> value="Mailout">Mail-Out</option>
						<option <?php echo ($type == 'Other') ? 'selected="selected"': '';?> value="Other">Other</option>
						<option <?php echo ($type == 'Pack Sent') ? 'selected="selected"': '';?> value="Pack Sent">Pack Sent</option>   
						<option <?php echo ($type == 'Meeting') ? 'selected="selected"': '';?> value="Meeting">Meeting</option> 
						<option <?php echo ($type == 'Conference') ? 'selected="selected"': '';?> value="Conference">Conference</option> 
					</select>
				</td>
				<td>
					Staff :
					<select name="search_staff">
						<option <?php echo ($staff == '') ? 'selected="selected"': '';?> value=""></option>
						<?php if($contacts)
								foreach ($contacts as $contact){
									$select = ($staff == $contact['staff_id']) ? 'selected="selected"': '';
									echo '<option '.$select.' value="'.$contact['staff_id'].'" >'.$contact['FirstName'] .' '. $contact['LastName'].'</option>';
								}
						?>
						
					</select>
				</td>
				<td align="right">
					<input type="submit" name="submit_search" value="Search" class="searchstyle submitbtnImg">
				</td>
			</tr>
			</form>
		</table>
<table width="100%" border=0 cellspacing=1 cellpadding=5 class="all-table">
<?php
		
	echo "<tr bgcolor=#b4151b  style='border: 1px solid #B4151B;'>\n";
	echo "<td width='10%' class='colorwhite bold'>Date</td>\n";
	echo "<td width='15%' class='colorwhite bold'>Type</td>\n";
	echo "<td width='15%' class='colorwhite bold'>Staff</td>\n";
	echo "<td width='50%' colspan=2 class='colorwhite bold'>Comments</td>\n";
	echo "<td width='50%' class='colorwhite bold'>Next Contact</td>\n";
	echo "<td width='10%' class='colorwhite bold'>Delete</td>\n";
	echo "</tr>\n";
	
	//if(mysql_num_rows($result) != 0) {
if($result){
	$odd=0;
	
   //while ($row = mysql_fetch_row($result))
   foreach($result as $row)
   {
		$odd++;
		if (is_odd($odd)) {
			echo '<tr bgcolor="#FFFFFF" '.( ($row['important']==1)?"class='important_log'":'' ).'>';		
		} else {
			echo '<tr bgcolor="#eeeeee" '.( ($row['important']==1)?"class='important_log'":'' ).'>';
		}
		
		// date
		echo "\n";
		echo "<td>";		
		echo $row[1];
		echo "</td>\n";
	 
		// type
		echo "<td>";		
		echo $row[0];
		echo "</td>\n";
	 
		// staff 
		echo "<td colspan=1>";		
		echo $row[5] ." ". $row[6];
		echo "</td>\n";
		
		// comments
		echo "<td colspan=2>";		
		echo $row[2];
		echo "</td>\n";
		
		// next contact
		echo "<td>";		
		echo ($row[7]!='0000-00-00')?date("d/m/Y",strtotime($row[7])):'';
		echo "</td>\n";
		
		echo "<td>";		
		echo "<a href='delete_target_details.php?id=$agency_id&cid=$row[3]&doaction=delete'>Delete</a>";
		echo "</td>\n";

		echo "</tr>\n";
	}
} // if
   
?>
</tr>
</table>


<h2 class="heading">Agency Files</h2>

<?php
		
 # Get Property uploaded Files
$property_files = getPropertyFiles2($agency_id); 

if(sizeof($property_files) == 0)
{
	echo "<div style='text-align: left; font-size: 13px;'>This Agency Has No Uploaded Files. Upload One Below</div>";
}
else {

	// path
	//$upload_path = $_SERVER['DOCUMENT_ROOT'].'agency_files/';

	echo "<ul class='vad-btmfld'>";
	foreach($property_files as $file)
	{
		echo "<li><a href='/agency_files/". $agency_id . "/" . $file . "' target='_blank'>" . $file . "</a> - <a href='?id=" . $agency_id . "&delfile=" . urlencode($file) . "#uploads' class='delfile'>Delete</a></li>";
	}
	echo "</ul>";
}

?>



<div style="text-align:left; font-size: 13px;">
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
<?php

# Upload property file
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
	
	
	if(move_uploaded_file($files_arr['fileupload']['tmp_name'], $upload_path . $agency_id . "/" . $files_arr['fileupload']['name']))
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
		echo "<script>window.location='/view_target_details.php?id={$_REQUEST['id']}&upload_success=1'</script>";
		//echo "<div class='success'>File Uploaded Successfully</div>";
	}
	else
	{
		//echo "<div class='error'>Technical Problem. Please Try Again</div>";
		echo "<script>window.location='/view_target_details.php?id={$_REQUEST['id']}&upload_success=0'</script>";
	}
}

# Process Delete
if(isset($_GET['delfile']))
{
	
	$delfile = urldecode($_GET['delfile']);
	if(deleteFile2($delfile, $agency_id))
	{
		echo "<script>window.location='/view_target_details.php?id={$_REQUEST['id']}&upload_success=2'</script>";
		//echo "<div class='success'>File Deleted Successfully</div>";
	}
	else
	{
		echo "<script>window.location='/view_target_details.php?id={$_REQUEST['id']}&upload_success=0'</script>";
		//echo "<div class='error'>Technical Problem. Please Try Again</div>";
	}
}



if($_GET['upload_success']==1){
	echo "<div class='success'>File Uploaded Successfully</div>";
}else if($_GET['upload_success']==2){
	echo "<div class='success'>File Deleted Successfully</div>";
}else if($_GET['upload_success']===0){
	echo "<div class='error'>Technical Problem. Please Try Again</div>";
}		

?>

<form action="<?php echo "/view_deactivated_agency_details.php?id={$_REQUEST['id']}" ?>#uploads" enctype="multipart/form-data" method="post">
<p><input type="file" name="fileupload" class="submitbtnImg"> <input type="submit" value="Upload Now" class="submitbtnImg"></p>
</form>
</div>


</div>
</div>
<br class="clearfloat" />

<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<script>
jQuery(document).ready(function(){
	jQuery(".datepicker").datepicker({ dateFormat: "dd/mm/yy" });
});
</script>
</body>
</html>
