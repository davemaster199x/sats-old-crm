<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View Agency Details</title>
<link href="/css/mainsite.css" type="text/css" rel="stylesheet" />

<!--[if IE]>
<link href="/css/mainsiteieonly.css" type="text/css" rel="stylesheet" />
<![endif]-->

<style type="text/css">
<!--
#apDiv2 {
	position:absolute;
	left:242px;
	top:97px;
	width:199px;
	height:183px;
	z-index:2;
}
#apDiv3 {
	position:absolute;
	left:450px;
	top:96px;
	width:186px;
	height:211px;
	z-index:3;
}
#apDiv4 {
	position:absolute;
	left:242px;
	top:332px;
	width:202px;
	height:197px;
	z-index:4;
}
#apDiv5 {
	position:absolute;
	left:450px;
	top:333px;
	width:192px;
	height:207px;
	z-index:5;
}
-->
</style>
</head>

<body class="thrColLiqHdr">



<div id="apDiv1"><img src="../satslogo.gif" width="89" height="54" alt="sats logo" /></div>
<div id="apDiv2"><a href="aview_properties.php"><img src="images/view_properties.gif" alt="View Properties" width="150" height="210" border="0" /></a></div>
<!-- <div id="apDiv3"><a href="aadd_property_static.php"><img src="images/add_properties.gif" alt="Add Properties" width="150" height="210" border="0" /></a></div> -->
<div id="apDiv4"><a href="aview_agents.php"><img src="images/view_agents.gif" alt="View Agents" width="150" height="210" border="0" /></a></div>
<div id="apDiv5"><a href="aadd_agent_static.php"><img src="images/add_agents.gif" alt="Add Agents" width="150" height="210" border="0" /></a></div>
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

  <div id="mainContent">
    <h1 class="style3">
 You have successfully been logged in.<br />
 <?php
session_start();
if ($_SESSION["agency_id"] == "")
	{
	echo "Your agency_id was ".$_SESSION["agency_id"].".<br>\n";
	header("Location: http://sat.cmcc.com.au/agents/invalid_login.php");
	}

/*
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



*/


?>
</h1>

<h5 class="style3">&nbsp;</h5>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3">&nbsp;</p>
<p class="style3"><a href='destroy.php'>Log Out</a><br />
</p>

<h5 class="style3">
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
    <p class="style13">Logged in</p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
