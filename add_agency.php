<?

$title = "Add an Agent";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <div id="mainContent">
    <h1 class="style4">Add an Agent</h1>

<?php

$encrypt = new cast128();
$encrypt->setkey(SALT);

$agency_name = addslashes($_POST['agency_name']);
$address_1 = addslashes($_POST['street_number']);
$address_2 = addslashes($_POST['street_name']);
$address_3 = addslashes($_POST['suburb']);
$mailing_address_as_above = $_POST['mailing_address_as_above'];
$mailing_address = addslashes($_POST['mailing_address']);
$phone = addslashes($_POST['phone']);
$state = addslashes($_POST['state']);
$postcode = addslashes($_POST['postcode']);
$totprop = addslashes($_POST['totprop']);
$password = addslashes(utf8_encode($encrypt->encrypt('password')));
$region_id = intval($_POST['region_id']);

	$insertQuery = "INSERT INTO agency (agency_name, address_1, address_2, address_3, phone, state, postcode, mailing_as_above, mailing_address, tot_properties, password, agency_region_id) VALUES
	 		        (" .
                    "\"" . $agency_name . "\", ".
                    "\"" . $address_1 . "\", ".
                    "\"" . $address_2 . "\", ".
                    "\"" . $address_3 . "\", ".
					"\"" . $phone . "\", ".
                    "\"" . $state . "\", ".
                    "\"" . $postcode . "\", ".
                    "\"" . "1" . "\", ".
                    "\"" . $mailing_address . "\", ".
					"\"" . $totprop . "\", ".
					"\"" . $password . "\", ".
					"\"" . $region_id . "\");";

     if ((@ mysql_query ($insertQuery,$connection)) && @ mysql_affected_rows() == 1){
        echo "<h3>Agency successfully added</h3>";
		echo "<a href='" . URL . "add_agency_static.php'>Click here</a> to add more agency.<br>\n";
	 }
     else{
        echo "<h3>A fatal error occurred</h3>\n<br>" . $insertQuery;
		echo "<br>Contact site admin for remendy action.";
	 }

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
