<?

$title = "Edit Booking Regions";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# get region
$region_id = intval($_GET['id']);



if($_POST)
{
	$_POST['postcode_region_postcodes'] = Regions::cleanPostcodeString($_POST['postcode_region_postcodes']);
	
	# Update Database
	$region_id = Regions::updateRegion($_POST, $region_id);
	//$region = $_POST;
	
	if(is_numeric($region_id))
	{
		$message = "Region Details Saved Successfully";
	}
}

if($region_id > 0)
{
	$region = Regions::getRegionData($region_id);
}

?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Booking Regions" href="<?=URL;?>view_regions.php">Booking Regions</a></li>
        <li class="other first"><a title="Edit Booking Region" href="/edit_region.php?id=<?php echo $region_id; ?><?php echo ($_GET['popup_window']==1)?'&popup_window=1':''; ?>"><strong>Edit Booking Region</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php
if($message)
{
	echo "<div class='success' style='margin-bottom: 15px;'>" . $message . "</div>";	
}
?>

<div class="formholder editbooking">

<form action='edit_region.php?id=<?php echo $region_id; ?><?php echo ($_GET['popup_window']==1)?'&popup_window=1':''; ?>' method='post'>
	
	 <div class="row">
    	<label class='addlabel' for='region'><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></label>
		<select name="region">
		<option value="">--Select--</option>
		<?php
		$reg_sql = mysql_query("
			SELECT *
			FROM `regions`
			WHERE `status` = 1
			AND country_id = {$_SESSION['country_default']}
			ORDER BY `region_name`
		");
		while($reg = mysql_fetch_array($reg_sql)){ ?>
			<option value="<?php echo $reg['regions_id']; ?>" <?php echo ($reg['regions_id']==$region['region'])?'selected="selected"':''; ?>><?php echo $reg['region_name']; ?></option>
		<?php	
		}
		?>
		</select>
    </div>
    <div class="row">
    	<label class='addlabel' for='postcode_region_name'>Sub Region</label>
        <input class='addinput' type=text maxlength=40 size=40 name='postcode_region_name' value='<?php echo $region['postcode_region_name'];?>'>
    </div>
    <div class="row">
    	<label class='addlabel' for='postcode_region_postcodes'>Postcodes (Seperate with commas)</label>
        <textarea class='addtextarea' type='text' style="height: 150px;" name='postcode_region_postcodes'><?php echo $region['postcode_region_postcodes'];?></textarea>
    </div>
		
	<div class="row edt-reg-btn">
		<label class='addlabel' for='state'>&nbsp;</label>
    	<input type="hidden" value="Save Region" class="submitbtnImg submit">
		<button type="submit" class="submitbtnImg" style="width: auto; float: left;" >
			<img class="inner_icon" src="images/button_icons/save-button.png">
			<span class="inner_icon_txt">Save Region</span>
		</button>
    </div>
    	
</form>

</div>

  </div>

</div>

<br class="clearfloat" />
<?php
if($_GET['popup_window']==1){ ?>
	<style>
	#staff_box, #sidebar1{
		display:none;
	}
	</style>
<?php	
}
?>
</body>
</html>
