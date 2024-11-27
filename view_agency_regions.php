<?

$title = "Agency Regions";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

# delete
if($_GET['delete_id']) {
	$delete_id = $_GET['delete_id'];
	$deletequery = mysql_query("UPDATE agency_regions SET deleted='1' WHERE agency_region_id='". $delete_id ."'  ", $connection);
	$message = "Agency Region Deleted Successfully";
}



?>
  <div id="mainContent">
  
  <div class="sats-middle-cont">
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Agency Regions" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Agency Regions</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  
  
    <?php
	    // get sats agency regions
	    $regions = array();
		$insertQuery = "
		SELECT * 
		FROM `agency_regions` AS ar
		LEFT JOIN `countries` AS c ON ar.`country_id` = c.`country_id`
		WHERE ar.`deleted` = 0 
		AND ar.`country_id` = {$_SESSION['country_default']}
		ORDER BY ar.`agency_region_id`";
		$query = mysql_query($insertQuery, $connection);
		
		/*
		while ($row = mysql_fetch_row($query)) {
			$regions[] = $row;
		}
		*/

		
	?>


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
  <a class="submitbtnImg export vwregn" href="edit_agency_region.php">+ Add New Region</a>
  </div>
  
  <div class="fl-left">&nbsp;</div>

</div>

		  <table border=0 cellspacing=1 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b">
<th>Region Name</th>
<th>Postcodes</th>
<th>Edit</th>
<th>Delete</th>
</tr>
 
<?php

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while($region = mysql_fetch_array($query)){

   $odd++;
	if (is_odd($odd)) {
		echo "<tr class='bg-white'>";		
		} else {
		echo "<tr class='bg-grey-light'>";
   		}
	?>
    
    <td class="view-agency-title"><a href="<?=URL;?>edit_agency_region.php?id=<?=$region['agency_region_id'];?>"><?=$region['agency_region_name'];?></a></td>
	<td class="view-agency-postcode"><?=$region['agency_region_postcodes'];?></td>
	<td><a href="<?=URL;?>edit_agency_region.php?id=<?=$region['agency_region_id'];?>">Edit</a></td>
    <td class="view-agency-delete" style="text-align: left;"><a href="<?=URL;?>view_agency_regions.php?delete_id=<?=$region['agency_region_id'];?>" onclick="return confirm('Are you sure you want to Delete this region?');">Delete</a></td>

    </tr>
   <?php } ?>
	


</table>
            
            </td>
        </tr>
      </tbody>
 </table>



   
  </div>
  
  </div>
  
<br class="clearfloat">

</body>
</html>
