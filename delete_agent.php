<?

$title = "Delete Agent";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');



?>
  <div id="mainContent">


	<div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Delete Agent" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>Delete Agent</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php

// init the variables

$contact_id = $_GET['id'];

	// update the details in the database.



   // (2) Run the query on the winestore through the
   //  connection
   $Query = "DELETE FROM contacts WHERE (contact_id=$contact_id);";
   
	mysql_query ($Query, $connection);
	if (mysql_affected_rows($connection) == 1)
		{
		echo "<div class='success'>Agent Successfully Deleted. (<a href='" . URL . "view_agents.php'>View Agents</a>)</div>";
		}
		else
		{
		echo "An error has occurred, it looks like the record may have already been deleted! (please check)<br><br>\n";
		echo "<a href='" . URL . "view_agents.php'>Click Here</a> to Return to the Agents page.<br>\n";
		}
	
	
	 			
?>

</tr>




</table>
</td></tr></table>

<br />




</form>



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
