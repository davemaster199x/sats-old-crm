<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View Agency Details</title>
<link href="/css/mainsite.css" type="text/css" rel="stylesheet" />

<!--[if IE]>
<link href="/css/mainsiteieonly.css" type="text/css" rel="stylesheet" />
<![endif]-->

</head>

<body class="thrColLiqHdr">

<div id="apDiv1"><img src="../satslogo.gif" width="89" height="54" alt="sats logo" /></div>
<div id="container">
 <div id="header">
    <h1><span class="style1">Smoke Alarm Testing</span>
      <!-- end #header -->
    </h1>
  </div>
  <div id="sidebar1">
  <h3 class="fltrt">&nbsp;</h3>

<?php

$myFile = "menubar.html";
$fh = fopen($myFile, 'r');
$theData = fread($fh, 2000);
fclose($fh);
echo $theData;


?>


  <p class="style2">&nbsp;</p>
  <p class="style2">&nbsp;</p>
  <p class="style2">&nbsp;</p>
  <p class="style2">.</p>
  <!-- end #sidebar1 --></div>
  <div id="sidebar2">
    <h3 class="style11">Current Statistics:</h3>
    <p><span class="style10"><span class="style11"><span class="style12"><a href="../asdfw">21 UNACTIONED properties</a></span></span></span></p>
    <p class="style12"><a href="../asdfw">8 requests for change of tenancy</a></p>
    <p><span class="style12"><a href="../asdfw">39 UNACTIONED jobs</a></span></p>
    <p>
      <!-- end #sidebar2 -->
    </p>
    </div>
  <div id="mainContent">
    <h1 class="style4">View Agency Details</h1>

<h5 class="style3">

<hr noshade="noshade" size=1 />

You have successfully been logged out.<br />
<br />
<br />

You can:<br />

<?php
session_start();
session_destroy();

echo ".".$_SESSION["agency_id"].". session";
echo "<br />\n";
echo "<a href='aadd_property_static.php'>Add Properties</a><br />\n";
echo "<br />\n<br />\n";
echo "<a href='aview_properties.php'>View Properties<br />\n";
echo "(including Update and Create a Change of Tenancy)</a><br />\n";
echo "<br />\n<br />\n";
echo "<a href='aadd_agents.php'>Add Agents (Employees)</a><br />\n";
echo "<br />\n<br />\n";
echo "<a href='aview_agents.php'>View and Update Agents (Employees)</a><br />\n";
echo "<br /><br />\n";

echo "<a href='destroy.php'>Log Out</a><br />\n";
echo "<br /><br />\n";




?>


<hr noshade="noshade" size=1 />

</h5>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
    <p class="style13">Logged in as: <strong>Damien Mason (<em>Admin</em>)</strong></p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
