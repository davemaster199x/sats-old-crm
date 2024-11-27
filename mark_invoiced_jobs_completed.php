<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Main - Smoke Alarm Testing Services</title>

<link href="/css/mainsite.css" type="text/css" rel="stylesheet" />

</head>

<body class="thrColLiqHdr">

<div id="apDiv1"><img src="satslogo.gif" width="89" height="54" alt="sats logo" /></div>
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
$theData = fread($fh, 10000);
fclose($fh);
echo $theData;


?>


  <p class="style2">&nbsp;</p>
  <p class="style2">&nbsp;</p>
  <p class="style2">&nbsp;</p>
  <p class="style2">.</p>
  <!-- end #sidebar1 --></div>
  <div id="sidebar2">
  <p><span class="style11">
  <?php

   

   
   $insertQuery = "select count(id) FROM jobs where (status='To Be Booked');";
   $result = mysql_query ($insertQuery, $connection);
   $row = mysql_fetch_row($result);
   echo "<a href='http://sat.cmcc.com.au/view_jobs.php?status=tobebooked'>There are currently $row[0] Jobs To Be Booked in the System</a><br><br>\n";

   $insertQuery = "select count(id) FROM jobs where (status='Send Letters' AND letter_sent=0);";
   $result = mysql_query ($insertQuery, $connection);
   $row = mysql_fetch_row($result);
   echo "<a href='http://sat.cmcc.com.au/view_jobs.php?status=sendletters'>There is currently $row[0] Jobs waiting for letters to be sent</a><br><br>\n";
   
    
   ?>
   
    </span></p>
      <!-- end #sidebar2 -->
    </p>
    </div>
  <div id="mainContent">
    <h1 class="style4">Welcome to the Smoke Alarm Testing Database!</h1>

<?php

   


   // (2) Run the query 
   $Query = "UPDATE jobs SET status='Completed' WHERE (status='To Be Invoiced');";

	mysql_query ($Query, $connection);

        

	echo "All <b>Not Invoiced Jobs</b> Marked As <b>Complete</b>!\n";
	
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
