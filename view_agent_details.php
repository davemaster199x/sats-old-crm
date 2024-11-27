<?

$title = "View Agent Details";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

<div id="mainContent">

<div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="http://crmdev.sats.com.au/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Agent" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>View Agent</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
<?php
// init the variables

$contact_id = $_GET['id'];

//echo "<a href='delete_agent.php?id=$contact_id'>Delete this Agent</a>";
?>

<table border=1 cellpadding=5 cellspacing=1>
<tr>
<td>
	<a href='<?php echo "delete_agent.php?id=".$contact_id;?>'>Delete this Agent</a>
</td>
	
</tr>
<tr>
<td colspan=2>
<table border=0 cellspacing=1 cellpadding=5 width=100%>
<tr bgcolor="#DDDDDD">
<td><b>First Name</b></td>
<td><b>Last Name</b></td>
<td><b>Mobile</b></td>
<td><b>Office</b></td>
<td><b>Email</b></td>
<td><b>Fax</b></td>
<!-- <td><b>Address</b></td>
<td><b>State</b></td>
<td colspan=1><b>Postcode</b></td> -->
<td><b>Agency</b></td>
<td><b>Edit</b></td>
<td><b>Delete</b></td>

<?php

//$insertQuery = "SELECT c.first_name, c.last_name, c.phone_home, c.phone_mob, c.phone_work, c.fax, c.address_1, c.address_2, c.address_3, c.state, c.postcode, a.agency_name, a.address_3, c.agency_id, a.agency_id FROM contacts c, agency a WHERE (c.contact_id='".$contact_id."') AND (c.agency_id = a.agency_id);";
$insertQuery = "SELECT c.first_name, c.last_name, c.phone_home, c.phone_mob, c.phone_work, c.fax, a.agency_name, a.address_3, c.agency_id, a.agency_id FROM contacts c, agency a WHERE (c.contact_id='".$contact_id."') AND (c.agency_id = a.agency_id);";

     if (($result = mysql_query ($insertQuery,  $connection)) && @mysql_affected_rows(  ) == 1)
      //  echo "<h3>View an Agent</h3>";
		echo "";
     else
        echo "<h3>No further details available</h3><br>";

	$odd=0;

   while ($row = mysql_fetch_row($result))
   {
   $odd++;
	if (is_odd($odd)) {
		echo "<tr bgcolor=#FFFFFF>";		
		} else {
		echo "<tr bgcolor=#efebef>\n";
   		}
		
      echo "\n";
		
		
		echo "<td>";		
		echo $row[0];
		echo "</td>\n";

		echo "<td>";		
		echo $row[1];
		echo "</td>\n";

		echo "<td>";		
		echo $row[3];
		echo "</td>\n";

		echo "<td>";		
		echo $row[4];
		echo "</td>\n";

		echo "<td>";		
		echo $row[2];
		echo "</td>\n";

		echo "<td>";		
		echo $row[5];
		echo "</td>";

		// echo "<td>";		
		// echo $row[6] . " " . $row[7] .  " " . $row[8];
		// echo "</td>";

		// echo "<td>";		
		// echo $row[9];
		// echo "</td>";

		// echo "<td>";		
		// echo $row[10];
		// echo "</td>";

		echo "<td>";		
		$ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row[9]}"); 
		echo "<a href='". $ci_link . "'>" . $row[6] . ", " . $row[7] . "</a>";
		echo "</td>";
		echo "<td>";
		echo "<a href='#'>Edit</a>";
		echo "</td>";
		echo "<td>";
		echo "<a href='delete_agent.php?id=$contact_id'>Delete this Agent</a>";
		echo "</td>";
		echo "</tr>\n";


      echo "\n";
   }
   // (5) Close the database connection
   

?>
                                                                          
</table>
</td></tr></table>

  </div>
</div>

<br class="clearfloat" />


</body>
</html>
