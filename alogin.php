<?php

session_start();
if ($_SESSION["agency_id"] == "")
    {
    /* header("Location: http://satagents.cmcc.com.au/invalid_login.php"); */
	header("Location: /invalid_login.php");
    }

    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Agency Login</title>
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
  <div id="mainContent">
    <h1 class="style4">Agency Login</h1>

<h5 class="style3">

<hr noshade="noshade" size=1 />


<?php

// init the variables
$login_id = $_POST['login_id'];
$password = $_POST['password'];

   // (1) Open the database connection and use the winestore
   // database
   include 'dbconnect.php';


   // (2) Run the query on the winestore through the
   //  connection
   $Query = "SELECT agency_id FROM agency WHERE (login_id = '$login_id' AND password = '$password');";
	$result = mysql_query ($Query, $connection);
     if (mysql_num_rows($result) == 0)
	 	{
		sleep(1);
        echo "Invalid Login, Please try again.<br>";
	    mysql_close($connection);
		}
	else
	{

		echo "Logging you in... please wait or (click here) to be redirected...<br>\n\n";
		session_start();

		$row = mysql_fetch_row($result);
		$_SESSION["agency_id"] = $row[0];

		/* header("Location: http://satagents.cmcc.com.au/home.php?agency_id=".$_SESSION["agency_id"]); */
		header("Location: /home.php?agency_id=".$_SESSION["agency_id"]);
		session_register($_SESSION);
		
   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
	
	} // main.
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
    <p class="style13"></strong></p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
