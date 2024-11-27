<?

$title = "Booking Regions";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$user_type = $_SESSION['USER_DETAILS']['ClassID'];

# delete
if(intval($_GET['delete_id'])> 0 && Regions::deleteRegion($_GET['delete_id']))
{
	$message = "Region Deleted Successfully";
}

$postcode = mysql_real_escape_string($_REQUEST['postcode']);

if($postcode!=""){
	$str = " AND pr.`postcode_region_postcodes` LIKE '%{$postcode}%' ";
}

# get sats users
$regions = mysql_query("
	SELECT * 
	FROM `postcode_regions` AS pr
	LEFT JOIN `countries` AS c ON pr.`country_id` = c.`country_id`
	LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
	WHERE pr.`deleted` = 0 
	AND pr.`country_id` = {$_SESSION['country_default']}
	{$str}
	ORDER BY r.`region_name` ASC
");

?>
  <div id="mainContent">
  <div class="sats-middle-cont">
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Booking Regions" href="/view_regions.php"><strong>View Regions</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php
if($message)
{
	echo "<div class='success'>" . $message . "</div>";	
}
?>




<table cellspacing="0" cellpadding="0">
        <tbody><tr class="tbl-view-prop">
          <td>
          
          <div class="ap-vw-reg aviw_drop-h">

  <div class="fl-left">
  
  <?php
  // hide for admin
  if( $user_type !=3 ){ ?>
  
	<a href="add_main_region.php">
		<button type="button" class="submitbtnImg" style="width: auto;" >
			<img class="inner_icon" src="images/button_icons/add-button.png">
			<span class="inner_icon_txt">New</span>
		</button>
	</a>

	<a href="edit_region.php">
		<button type="button" class="submitbtnImg" style="width: auto;" >
			<img class="inner_icon" src="images/button_icons/add-button.png">
			<span class="inner_icon_txt">New Sub Region</span>
		</button>
	</a>
  
  <?php  
  }
  ?>
  
  </div>
	<form method="post">
		<div class="fl-left">
			<input type="label" style="width: 100px !important; margin-left: 5px;" class="addinput" value="" name="postcode" />
		</div>

		<div style="float:left;" class="fl-left">
			<input type="hidden" value="Search" class="submitbtnImg">
			<button type="submit" class="submitbtnImg" style="width: auto;" >
				<img class="inner_icon" src="images/button_icons/search-button.png">
				<span class="inner_icon_txt">Search</span>
			</button>
		</div>
	</form>

</div>

		  <table border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th style="width:120px;"><b><?php echo getDynamicStateViaCountry($_SESSION['country_default']); ?></b></th>
<th><div style="width: 200px;"><b><?php echo getDynamicRegionViaCountry($_SESSION['country_default']); ?></b></div></th>
<th><div style="width: 200px;"><b>Sub Region</b></div></th>
<th><b>Postcodes</b></th>
<th><b>Edit</b></th>
<th><b>Delete</b></th>
</tr>
 
<?php

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while( $region = mysql_fetch_array($regions) ){

   $odd++;
	if (is_odd($odd)) {
		echo "<tr bgcolor=#FFFFFF>";		
		} else {
		echo "<tr bgcolor=#eeeeee>";
   		}
	?>
	<td><?=$region['region_state'];?></td>
	<td><a href="/edit_main_region.php?id=<?=$region['regions_id'];?>"><?=$region['region_name'];?></a></td>	
    <td><a href="<?=URL;?>edit_region.php?id=<?=$region['postcode_region_id'];?>"><?=$region['postcode_region_name'];?></a></td>
    <td><?=str_replace(",",", ",$region['postcode_region_postcodes']);?></td>
	<td><a href="<?=URL;?>edit_region.php?id=<?=$region['postcode_region_id'];?>">Edit</a></td>
    <td><a href="<?=URL;?>view_regions.php?delete_id=<?=$region['postcode_region_id'];?>" onclick="return confirm('Are you sure you want to delete this region?');">Delete</a></td>

    </tr>
	
   <? } ?>
	
</table>
            
            </td>
        </tr>
      </tbody>
 </table>



  </div>
</div>

<br class="clearfloat" />

</body>
</html>
