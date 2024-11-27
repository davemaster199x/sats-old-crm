<?

$title = "Add an Agent";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


?>

  <div id="mainContent">
    <h1 class="style4">Add an Agent</h1>


<?php

$agency_name = $_POST['agency_name'];
$address_1 = $_POST['street_number'];
$address_2 = $_POST['street_name'];
$address_3 = $_POST['suburb'];
$phone = $_POST['phone'];
$mailing_address_as_above = $_POST['mailing_address_as_above'];
$mailing_address = $_POST['mailing_address'];
$state = $_POST['state'];
$postcode = $_POST['postcode'];
$status = $_POST['status'];
$region = $_POST['region_id'];
$total_properties = $_POST['totalprop'];
   


	$insertQuery = "INSERT INTO agency (agency_name, address_1, address_2, address_3, phone, state, postcode, mailing_as_above, status, tot_properties, agency_region_id, mailing_address) VALUES
	 		        (" .
                    "\"" . $agency_name . "\", ".
                    "\"" . $address_1 . "\", ".
                    "\"" . $address_2 . "\", ".
                    "\"" . $address_3 . "\", ".
					"\"" . $phone . "\", ".
                    "\"" . $state . "\", ".
                    "\"" . $postcode . "\", ".
                    "\"" . "1" . "\", ".
                    "\"" . $status . "\", ".
					"\"" . $total_properties . "\", ".
					"\"" . $region . "\", ".
                    "\"" . $mailing_address . "\");";

     if ((@ mysql_query ($insertQuery,$connection)) && @ mysql_affected_rows(  ) == 1)
        echo "<div class='success'>Agency Successfully Added (<a href='view_target_agencies.php'>View Target Agencies</a> or <a href='add_agency_target_static.php'>Add Another Agency</a>)</div>";
     else
        echo "<div class='success'>A fatal error occurred</div>\n<br>" . $insertQuery;

?>
    
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
