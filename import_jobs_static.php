<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Import Properties</title>

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

   $connection = mysql_connect("localhost","satsuser","dell123");
   mysql_select_db("sats", $connection);
   
   $insertQuery = "select count(id) from jobs where (status='To Be Booked');";
   $result = mysql_query ($insertQuery, $connection);
   $row = mysql_fetch_row($result);
   echo "<a href='http://sat.cmcc.com.au/view_jobs.php?status=tobebooked'>There are currently $row[0] Jobs To Be Booked in the System</a><br><br>\n";

   $insertQuery = "select count(id) from jobs where (status='Send Letters' AND letter_sent=0);";
   $result = mysql_query ($insertQuery, $connection);
   $row = mysql_fetch_row($result);
   echo "<a href='http://sat.cmcc.com.au/view_jobs.php?status=sendletters'>There is currently $row[0] Jobs waiting for letters to be sent</a><br><br>\n";
   
   mysql_close($connection); 
   ?>
   
    </span></p>
      <!-- end #sidebar2 -->
    </p>
    </div>
  <div id="mainContent">
    <h1 class="style4">Import Jobs</h1>

    <form action="/upload_jobs.php" method="POST" enctype="multipart/form-data" name="form1" id="form1">
      <p>
        <label>File (CSV) to Upload</label> 
        <label>
        <input type="file" name="csvfile" id="csvfile" />
        </label>
        <label></label>
      </p>

      <label>
        <input type="submit" name="submit" id="submit" value="submit" /> (click ONCE then WAIT - up to 2 minutes).
      </label>
    </form>
    <p>&nbsp;</p>
	
<h6>
<p>
$landlord_name, $address1, $address2, $suburb, $state, $postcode, $res_firstname1, $res_lastname1, $res_firstname2, $res_lastname2, $res_ph1, $res_ph2, $res_ph3, $agency_name<br><br><br>

$landlord_name, $address1, $address2, $suburb, $state, $postcode, $res_firstname1, $res_lastname1, $res_firstname2,<br>
$res_lastname2, $res_ph1, $res_ph2, $landlord_ph, $agency_name, $agent_name, $option, $price_struct, $price, <br>
$a1_type, $a1_exp, $a1_text, $a2_type, $a2_exp, $a2_text, $a3_type, $a3_exp, $a3_text,<br>
  $a4_type, $a4_exp, $a4_text, $a5_type, $a5_exp, $a5_text, $a6_type, $a6_exp, $a6_text, <br>
  $job_type, $recd, $ltr_sent, $ltr_booking_date, $phone_booking, $test_date, $inv_num, $gen_comments,<br>
   $booking_comments, $testing_comments, $new_alarms_installed, $retest_date, $certs_sent
<br>
</p>
</h6>

  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />
  <div id="footer">
    <p class="style13">Logged in to SATs CRM</p>
    <!-- end #footer --></div>
<!-- end #container --></div>
</body>
</html>
