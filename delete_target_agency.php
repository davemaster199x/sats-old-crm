<?

$title = "Delete Target Agency";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <div id="mainContent">
    <h1 class="style4">Delete target agency</h1>
	<h5 class="style3">


<?php

// init the variables

$agencyid = $_POST['id'];

	// update the details in the database.

   // (1) Open the database connection and use the winestore
   // database
   


   // (2) Run the query on the winestore through the
   //  connection
   $Query = "DELETE FROM agency WHERE (agency_id = $agencyid);";
 	//echo $Query;  
   
	mysql_query ($Query, $connection);
	if (mysql_affected_rows($connection) == 1)
		{
		echo "Agency $agencyid has been successfully deleted<br><br>\n";
		echo "<a href='" . URL . "view_target_agencies.php'>Click Here</a> to Return to the Agents page.<br>\n";
		}
		else
		{
		echo "An error has occurred, it looks like the record may have already been deleted! (please check)<br><br>\n";
		echo "<a href='" . URL . "view_target_agencies.php'>Click Here</a> to Return to the Agency page.<br>\n";
		}

	 			
?>


<br />
<hr noshade="noshade" size=1 />
</form>

</h5>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
    <p class="style13">Logged in to SATs CRM</p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
