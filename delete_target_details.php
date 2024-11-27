<?

$title = "Delete Target Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$arr = getHomeTotals(); 

?>

  <div id="mainContent">
    
	<h5 class="style3">
<?php

// init the variables
$agency_id = $_GET['id'];
$crm_id = $_GET['cid'];
$doaction = $_GET['doaction'];

if ($doaction == "delete")
	{
	// update the details in the database.

   // (1) Open the database connection and use the winestore
   // database
   


   // (2) Run the query on the winestore through the
   //  connection
   $Query = "DELETE FROM `agency_event_log` WHERE (agency_event_log_id=$crm_id);";
   
	mysql_query ($Query, $connection);
	if (mysql_affected_rows($connection) == 1)
		{
		echo "<div class='success'>Entry successfully deleted ";
		echo "(<a href='" . URL . "view_target_details.php?id=$agency_id '>Back to View Activity Log</a>)</div><br>\n";
		}
		else
		{
		echo "<div class='success'>An error has occurred, it looks like the record may have already been deleted! (please check)";
		echo "<a href='" . URL . "view_target_details.php'>Click Here</a> to Return to the CRM page.</div><br>\n";
		}
	
	
	} // if $doaction
	    			
?>

</tr>
</table>
</td></tr></table>

<br />


</form>

</h5>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<!-- end #container --></div>
</body>
</html>
