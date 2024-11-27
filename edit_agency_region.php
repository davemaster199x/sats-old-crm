<?

$title = "Edit Agency Region";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# get region
$region_id = intval($_GET['id']);
$name = mysql_real_escape_string($_POST['agency_region_name']);
$country_id = mysql_real_escape_string($_POST['country_id']);
$postcodes = mysql_real_escape_string($_POST['agency_region_postcodes']);


//UPDATE The database with new information or updated information
if($region_id >= 1 && isset($_POST['agency_region_name']))
{	
	$update = mysql_query("UPDATE agency_regions SET agency_region_name='". $name ."', agency_region_postcodes='". $postcodes ."' WHERE(agency_region_id='". $region_id ."');", $connection);
	if (mysql_affected_rows() > 0){
		$message = "Update Successful!";
	}
	else{
		$message = "Update Failed!";
	}
} elseif(!$_POST['id'] && isset($_POST['agency_region_name'])) {	
	$sql = "INSERT INTO agency_regions(agency_region_name, agency_region_postcodes,`country_id`) VALUES ('". $name ."', '". $postcodes."', {$_SESSION['country_default']})";
	$new = mysql_query($sql, $connection);
	
	if (mysql_affected_rows() > 0){
		$message = "New Region Added!";
	}
	else{
		$message = "Update Failed!";
	}
}

if($region_id > 0)
{
	$region = array();	
	$query = "SELECT * FROM agency_regions WHERE agency_region_id = ". $region_id .";";
	$query = mysql_query($query, $connection);
	$region = mysql_fetch_array($query);
	
}

?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Agency Regions" href="<?=URL;?>view_agency_regions.php">Agency Regions</a></li>
        <li class="other second"><a title="Edit Agency Region" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Edit Agency Region</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<div class="addproperty edit-ag-reg-form">

<?php
if($message)
{
	echo "<div class='success' style='margin-bottom: 15px;'>" . $message . "</div>";	
}
?>


<form action='edit_agency_region.php?id=<?php echo $region_id; ?>' method='post'>

<div class="row">
		<label class='addlabel' for='agency_region_name'>Name</label><input class='addinput' type=text maxlength=40 size=40 name='agency_region_name' value='<?php echo $region['agency_region_name'];?>'>
        </div>
		
		
		
        <div class="row">
		<label class='addlabel' for='agency_region_postcodes'>Postcodes (seperate with comma)</label>
        <textarea class='addtextarea' type='text' name='agency_region_postcodes'><?php echo $region['agency_region_postcodes'];?></textarea>
        </div>
		<div class="row edt-reg-btn">
			<label class='addlabel' for='agency_region_postcodes'>&nbsp;</label>
			<input type="submit" value="Save Region" class="submitbtnImg submit" style="padding: 11px;" /> 
		</div>
</form>

</div>

  </div>
  
</div>

<br class="clearfloat" />

</body>
</html>
