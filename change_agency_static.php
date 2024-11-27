<?

$title = "Change property to new agency";

$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Properties" href="<?=URL;?>view_properties.php">View Properties</a></li>
        <li class="other second"><a title="View Property Details" href="<?=URL;?>view_property_details.php">View Property Details</a></li>
        <li class="other third"><a title="Change property to new agency" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Change Agency</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

<div class="addproperty">

<form id="form1" name="form1" method="POST" action="change_agency.php">
    
    <div class="row">
        <label class="addlabel" for="agency">Select Agency</label>
        <select class="addinput" name="agency" id="agency">
          <option value="">----</option>

<?php

// init the variables

$id = $_GET['id'];
$a_sql = mysql_query("
	SELECT `agency_id`
	FROM `property`
	WHERE `property_id` = {$id}
");
$a = mysql_fetch_array($a_sql);

  $agency_sql = "
  SELECT `agency_id`, `agency_name`, `address_3` 
  FROM `agency` 
  WHERE `status` = 'active' 
  AND `deleted` = 0
  AND `country_id` = {$_SESSION['country_default']} 
  ORDER BY `agency_name` ASC
  ";

	$result1 = mysql_query($agency_sql, $connection);
	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_array($result1))
   {

     // (4) Print out each element in $row, that is,
     // print the values of the attributes

		echo "<option value='" . $row['agency_id'] . "'>";		
		echo $row['agency_name']; 	// . ", " . $row[2];
		echo "</option>\n";

      // Print a carriage return to neaten the output
      echo "\n";
   }
   //send property id as well
   echo "<input type='hidden' name='property_id' value='$id'/>";
	   
   			
?>

	</select>
        </label>
    </div>    
    <div class="row">
      <div id="pm_ajax_div"></div>
    </div>
     <div class="row">    
    <label class="addlabel">
	<input type="hidden" name="orig_agency_id" value="<?php echo $a['agency_id']; ?>" />
    <input class="submit submitbtnImg color-white" type="submit" name="submit" id="submit" value="Change Agency">
    </label>
	</div>

</form>

</div>

  </div>

  
</div>

<br class="clearfloat" />

<script type="text/javascript">

   $('#agency').change(function(){
     var agency_id = $(this).val();
     var harris_agencies = ["1961","6203","6974"];
     jQuery("#load-screen").show();
     $('#pm_ajax_div').load('ajax_change_agency_static_get_pm.php',{agency_id:agency_id}, function(response, status, xhr){
        jQuery("#load-screen").hide();
        if( harris_agencies.indexOf(agency_id) > -1 ){
          alert("If you have not been instructed to move this property and are only doing so as you think it is in the wrong portfolio, please check the KEY number first: \nKey Number 0 to 2999-  Harris Adelaide Portfolio\nKey Number 3000 to 3999- Harris Glenelg Portfolio\nKey Number 7000 to 7999-  Harris Stirling Portfolio");
        }
     });
   });

</script>

</body>
</html>