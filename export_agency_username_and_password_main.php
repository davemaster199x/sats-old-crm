<?php
include('inc/init.php');
$country_id = $_SESSION['country_default'];
$crm = new Sats_Crm_Class;
?>
<h1>Split Agency Export</h1>


<a href="export_agency_username_and_password1.php">Split LIMIT 0, 200</a><br /><br />
<a href="export_agency_username_and_password2.php">Split LIMIT 200, 200</a><br /><br />
<a href="export_agency_username_and_password3.php">Split LIMIT 400, 200</a><br /><br />
<a href="export_agency_username_and_password4.php">Split LIMIT 600, 200</a><br /><br />
<a href="export_agency_username_and_password5.php">Split LIMIT 800, 200</a><br /><br />


