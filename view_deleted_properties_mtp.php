<?

$title = "View Deleted Properties";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>



  <div id="mainContentCalendar">

    <h1 class="style4">View Deleted Properties</h1>



<h5 class="style3">



<hr noshade="noshade" size=1 width=100% />



<form method=GET action="<?=URL;?>view_deleted_properties.php" class="searchstyle">



<table border=1 cellpadding=0 cellspacing=0 width=100%>

<tr><td>



<table border=0 cellspacing=0 cellpadding=5 width=100%>

<tr bgcolor="#DDDDDD">

<td colspan2><b>Address</b></td>

<td><b>Suburb</b></td>

<td><b>State</b></td>

<td><b>Postcode</b></td>

<td>Click to Restore</td>

</tr>



<tr>

<td>&nbsp;</td>

<td><input class="searchstyle" type=text name="searchsuburb" size=10/></td>

<td><input class="searchstyle" type="submit" value="search" /></td>

<td colspan=2 align=right>



        <select name="agency" id="agency">

        <option id="Any" selected>Any</option>



<?php

   // (1) Open the database connection

   





   // (2) Run the query on the winestore through the

   //  connection

   $result = mysql_query ("SELECT agency_id, agency_name, address_3 FROM agency", $connection);



	$odd=0;



   // (3) While there are still rows in the result set,

   // fetch the current row into the array $row

   while ($row = mysql_fetch_row($result))

   {



     // (4) Print out each element in $row, that is,

     // print the values of the attributes



		echo "<option value='" . $row[0] . "'>";		

		echo $row[1];

		echo "</option>\n";



      // Print a carriage return to neaten the output

      echo "\n";

   }

   // (5) Close the database connection

   





echo "</select></td></tr>\n";



$searchsuburb = $_GET['searchsuburb'];

$agency = $_GET['agency'];



   // (1) Open the database connection and use the winestore

   // database

   





   // (2) Run the query 

if ($agency == "Any" || $agency == "")

{

   

   if ($searchsuburb == "")

   {   

   $selectQuery = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, a.agency_id, a.agency_name, a.address_3 FROM property p, agency a

 WHERE (p.agency_id = a.agency_id AND p.deleted=1) " . $user->prepareStateString('AND', 'p.') . ";";

	}

	else

	{

	$selectQuery = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, a.agency_id, a.agency_name, a.address_3 FROM property p, agency a

 WHERE (p.agency_id = a.agency_id AND p.address_3 LIKE '%$searchsuburb%' AND p.deleted=1) " . $user->prepareStateString('AND', 'p.') . ";";

	}



}

else

{

   if ($searchsuburb == "")

   {   

   $selectQuery = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, a.agency_id, a.agency_name, a.address_3 FROM property p, agency a

 WHERE (p.agency_id = a.agency_id AND p.agency_id = '$agency' AND p.deleted=1);";

	}

	else

	{

	$selectQuery = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.comments, p.property_id, a.agency_id, a.agency_name, a.address_3 FROM property p, agency a

 WHERE (p.agency_id = a.agency_id AND p.address_3 LIKE '%$searchsuburb%' AND p.agency_id = '$agency' AND p.deleted=1);";

	}

} // main

	

	$result = mysql_query ($selectQuery, $connection);

//	echo "Query is $selectQuery<br>\n";

//	echo "SearchSuburb is $searchsuburb.<Br>\n";

//	echo "Agency is $agency.<br>\n";



	$odd=0;



   // (3) While there are still rows in the result set,

   // fetch the current row into the array $row

   while ($row = mysql_fetch_row($result))

   {

   $odd++;

	if (is_odd($odd)) {

		echo "<tr bgcolor=#efebef>";		

		} else {

		echo "<tr bgcolor=#ffffff>";

   		}

		

      echo "\n";

     // (4) Print out each element in $row, that is,

     // print the values of the attributes



		echo "<td>";		

		echo "<a href='view_property_details.php?id=" . $row[6] . "'>" . $row[0] . " $row[1] </a>";

		echo "</td>";





		echo "<td>";		

		echo $row[2] . " ";

		echo "</td>";



		echo "<td>";		

		echo $row[3] . " ";

		echo "</td>";

				

		echo "<td>";		

		echo $row[4] . " ";

		echo "</td>";



//       echo "<td><a href='" . URL . "view_property_details.php?id=" . $row[6] . "'>details</a></td></tr>";



	   echo "<td><a href='" . URL . "undelete_property.php?id=" . $row[6] . "'>Restore this Property</a></td></tr>";



      // Print a carriage return to neaten the output

      echo "\n";

   }

   // (5) Close the database connection

   







?>



</table>



<hr noshade="noshade" size=1 />



</td></tr></table>



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

