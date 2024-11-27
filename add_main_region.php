<?

$title = "Add Region";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>
<div id="mainContentCalendar">

 <div class="sats-middle-cont">
    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first"><a title="Booking Regions" href="<?=URL;?>view_regions.php">Booking Regions</a></li>
        <li class="other first"><a title="<?php echo $title; ?>" href="/add_main_region.php"><strong><?php echo $title; ?></strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php
if($_GET['success']==1)
{
	echo "<div class='success' style='margin-bottom: 15px;'>New Region has been created</div>";	
}
?>

<div class="formholder editbooking">

<form action='add_main_region_process.php' id="jform" method='post'>
	

    <div class="row">
    	<label class='addlabel' for='region_name'><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?> Name</label>
        <input class='addinput' type="text" name='region_name' id='region_name' />
    </div>
	
	<div class="row">
    	<label class='addlabel' for='state'><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></label>
         <input class='addinput' type="text" name='state' id='state' />
    </div>
	
	<div class="row edt-reg-btn">
		<label class='addlabel' for='state'>&nbsp;</label>
    	<input type="hidden" value="Submit" class="submitbtnImg submit">
		<button type="submit" class="submitbtnImg" style="width: auto; float: left;" >
			<img class="inner_icon" src="images/button_icons/save-button.png">
			<span class="inner_icon_txt">Submit</span>
		</button>
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
