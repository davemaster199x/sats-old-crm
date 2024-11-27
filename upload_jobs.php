<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Imported Properties</title>

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

   include 'dbconnect.php';

   
   $insertQuery = "select count(id) FROM jobs where (status='To Be Booked');";
   $result = mysql_query ($insertQuery, $connection);
   $row = mysql_fetch_row($result);
   echo "<a href='http://sat.cmcc.com.au/view_jobs.php?status=tobebooked'>There are currently $row[0] Jobs To Be Booked in the System</a><br><br>\n";

   $insertQuery = "select count(id) FROM jobs where (status='Send Letters' AND letter_sent=0);";
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
    <h1 class="style4">Imported Properties</h1>


<?php
if ($_FILES["csvfile"]["error"] > 0)
  {
  echo "Error: " . $_FILES["csvfile"]["error"] . "<br />";
  }
else
  {
  echo "Upload: " . $_FILES["csvfile"]["name"] . "<br />";
  echo "Type: " . $_FILES["csvfile"]["type"] . "<br />";
  echo "Size: " . ($_FILES["csvfile"]["size"] / 1024) . " Kb<br />";
  echo "Stored in: " . $_FILES["csvfile"]["tmp_name"];
  }


$csvfile = $_FILES["csvfile"]["error"];

$target_path = "uploads/";

$target_path = $target_path . basename( $_FILES["csvfile"]["name"]); 

if(move_uploaded_file($_FILES['csvfile']['tmp_name'], $target_path)) {
    echo "The file ".  basename( $_FILES['uploadedfile']['name']). " has been uploaded";
} else{
    echo "There was an error uploading the file, please try again!";
	echo "target_path is: $target_path.";
}

if (file_exists($target_path))
	{
	echo "<br><br>The File $target_path exists!<br><br>";
	}
	else
	{
	echo "<br><br>The File $target_path does not exist!<br><br>\n";
	exit;
	}

$agencycount = 0;
$propertycount = 0;
	
	  $connection2 = mysql_connect("localhost","satsuser","dell123");
      mysql_select_db("sats", $connection2);
	  	  
echo "Processing File.... please wait...<br><br>\n";


   // open for inserting the jobs...
	  $connection3 = mysql_connect("localhost","satsuser","dell123");
      mysql_select_db("sats", $connection3);
	  
   $fh = fopen($target_path, "r");
   while (list($landlord_name, $address1, $street, $suffix, $suburb, $state, $postcode, $res_firstname1, $res_lastname1, $amp, $res_firstname2, $res_lastname2, $res_ph1, $res_ph2, $res_ph3, $agency_name, $agent_name, $option, $price_struct, $price, $alarm_price,
   $a1_pwr, $a1_ion, $a1_type, $a1_exp, $a1_text, $a2_pwr, $a2_ion, $a2_type, $a2_exp, $a2_text, $a3_pwr, $a3_ion, $a3_type, $a3_exp, $a3_text, 
   $a4_pwr, $a4_ion, $a4_type, $a4_exp, $a4_text, $job_type, $recd, $ltr_sent, $ltr_booking_date, $phone_booking, $test_date, $inv_num, $gen_comments, $booking_comments, $testing_comments, $new_alarms_installed, $retest_date, $certs_sent) = fgetcsv($fh, 5000000, "	")) {

	$address2 = $street . " " . $suffix;

    $landlord_name = addslashes($landlord_name);
    $address_1 = addslashes($address_1);
    $street = addslashes($street);
    $suffix = addslashes($suffix);
    $res_firstname1 = addslashes($res_firstname1);
    $res_lastname1 = addslashes($res_lastname1);
    $res_firstname2 = addslashes($res_firstname2);
    $res_lastname2 = addslashes($res_lastname2);
    $gen_comments = addslashes($gen_comments);
    $booking_comments = addslashes($booking_comments);
    $testing_comments = addslashes($testing_comments); 
    
	  echo "<p>\n";
      echo "Landlord Name: $landlord_name<br>\n";
	  echo "Address: $address<br>\n";

	// check to see if the agency exists first.
	  $checkquery = "SELECT property_id FROM property WHERE (address_1 = '$address1' AND address_2 = '$address2' AND postcode = '$postcode');";
		

	  $connection4 = mysql_connect("localhost","satsuser","dell123");
      mysql_select_db("sats", $connection4);

		  $result2 = mysql_query ($checkquery,$connection4);

		  if (mysql_affected_rows($connection4) == 1)
		  {
		  echo "<h3>Property $address1, $address2, $address3 found.<br></h3>";
		  $row = mysql_fetch_row($result2);
		  $property_id = $row[0];
		  // insert the a job linked to the property id. 
	 	  $insertQuery = "INSERT INTO JOBS (status, job_type, property_id) VALUES ('To Be Booked', 'Yearly Maintenance', '$property_id');";
	  
		  if ((@ mysql_query ($insertQuery,$connection4)) && @ mysql_affected_rows(  ) == 1)
    	    echo "<h3>Job $property_id successfully added</h3>";
		  #repopulate alarms
	      populateAlarms(mysql_insert_id());
	     else
    	    echo "<h3>A fatal error occurred</h3>\n<br>" . $insertQuery . "<br><br>\n\n";
		  }
     else
	 		{
        echo "<h3>The property could not be found\n<br>" . $checkquery . "</h3><br><br>\n\n";	  
			}	  
	  
	  	$propertycount++;
	  
   } // while
   fclose($fh);

      mysql_close($connection2);
      mysql_close($connection3);
	  mysql_close($connection4);
	  
echo "********************************************************";
echo "********** E N D ***************************************";
echo "********************************************************";


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
