<?

$title = "Add an Agent";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


?>

  <div id="mainContent">

<h5 class="style3">
<fieldset>
<legend>Add an Agency to Target</legend>
<p class="addinput">

    <form id="form1" name="form1" method="POST" action="<?=URL;?>add_agency_target.php">
 
        <label class="addlabel" for="agency_name">Agency Name</label>
        <input class="addinput" type="text" name="agency_name" id="agency_name" />

        <label class="addlabel" for="street_number">Street Number</label>
        <input class="addinput" type="text" name="street_number" id="street_number" />

        <label class="addlabel" for="street_name">Street Name</label>
        <input class="addinput" type="text" name="street_name" id="street_name" />

		<label class='addlabel' for='phone'>Phone</label>
        <input class='addinput' type="text" name="phone" id="phone" />

        <label class="addlabel" for="suburb">Suburb</label>
        <input class="addinput" type="text" name="suburb" id="suburb" />

        <label class="addlabel" for="state">State</label>
         <select class="addinput" name="state" id="state" />
             <option value='NSW'>NSW</option>
             <option value='VIC'>VIC</option>
             <option value='QLD'>QLD</option>
             <option value='ACT'>ACT</option>
             <option value='TAS'>TAS</option>
             <option value='SA'>SA</option>
             <option value='WA'>WA</option>
             <option value='NT'>NT</option>
         </select>        

        <label class="addlabel" for="postcode">Postcode</label>
        <input class="addinput" type="text" name="postcode" id="postcode" />
		
		<?php
		//GET THE FULL LIST OF AGENCY REGIONS	
			echo "<label class='addlabel' for='region_id'>Region</label>\n";	
			echo "<select class='addinput' name='region_id'>";
			$regionresults = array();
			$regionquery = mysql_query("SELECT 
							  agency_region_id,
							  agency_region_name 
							FROM
							  agency_regions 
							WHERE deleted = 0 
							ORDER BY agency_region_id;");
			while ($regionresults = mysql_fetch_row($regionquery)) {
				echo "<option value='". $regionresults[0] ."'>";
				echo $regionresults[1];
				echo"</option>";
			}
			echo "</select>";
		?>
		
		<label class="addlabel" for="totalprop">Total Properties</label>
        <input class="addinput" type="text" name="totalprop" id="totalprop" />

        <!-- label class="addlabel" for="mailing_address_as_above">This is also the mailing Address</label>
        <input class="addinput" type="checkbox" name="mailing_address_as_above" id="mailing_address_as_above" />

        <label class="addlabel" for="mailing_address">Mailing Address</label>
        <textarea class="addtextarea" name="mailing_address" id="mailing_address" cols="45" rows="5"></textarea><br / -->

		<label class="addlabel" for="submit">
        <input class="submit" type="submit" name="submit" id="submit" value="Add Target Agency" />
        </label>

		<input type=hidden name="status" value="target" />


    </form>
</p> 
</fieldset>
    
    
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
    <p class="style13">Logged in to SATs CRM</p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
