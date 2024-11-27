<?

$title = "Edit Region";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$regions_id = mysql_real_escape_string($_GET['id']);


?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Booking Regions" href="<?=URL;?>view_regions.php">Booking Regions</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/edit_main_region.php?id=<?php echo $regions_id; ?>"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php
if($_GET['success']==1)
{
	echo "<div class='success' style='margin-bottom: 15px;'>Update Successful</div>";	
}
?>

<div class="formholder editbooking">

<form action='edit_main_region_process.php' id="jform" method='post'>
	
	<?php
	$reg_sql = mysql_query("
		SELECT *
		FROM `regions`
		WHERE `regions_id` = {$regions_id}
	");
	$reg = mysql_fetch_array($reg_sql);

	
	?>

    <div class="row">
    	<label class='addlabel' for='region_name'><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?> Name</label>
        <input class='addinput' type=text maxlength=40 size=40 name='region_name' id='region_name' value="<?php echo $reg['region_name']; ?>" />
    </div>
	
	 <div class="row">
    	<label class='addlabel' for='state'><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></label>
        <input class='addinput' type=text maxlength=40 size=40 name='state' id='state' value="<?php echo $reg['region_state']; ?>" />
    </div>
	
	<div class="row edt-reg-btn">
		<input type="hidden" name="regions_id" value="<?php echo $regions_id; ?>" />
    	<input type="submit" value="Update" class="submitbtnImg submit">
    </div>
    	
</form>

</div>

  </div>

</div>

<br class="clearfloat" />
<script>
jQuery(document).ready(function(){
	
	jQuery("#jform").submit(function(){
		
		var region_name = jQuery("#region_name").val();
		var state = jQuery("#state").val();
		var error = "";
		
		if( region_name == "" ){
			error += "Region Name is Required\n";
		}
		
		if( state == "" ){
			error += "State is Required\n";
		}
		
		if(error!=""){
			alert(error);
			return false;
		}else{
			return true;
		}
		
	});
	
});
</script>
</body>
</html>
