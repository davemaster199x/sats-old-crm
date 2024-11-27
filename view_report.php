<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>View Report</title>
<link href="/css/mainsite.css" type="text/css" rel="stylesheet" />

<!--[if IE]>
<link href="/css/mainsiteieonly.css" type="text/css" rel="stylesheet" />
<![endif]-->
</head>

<body class="thrColLiqHdr">

<div id="apDiv1"><img src="satslogo.gif" width="89" height="54" alt="sats logo" /></div>
<div id="container">
 <div id="header">
    <h1><span class="style1">Smoke Alarm Testing.</span>
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

  <div id="mainContent">

<h5 class="style3">
<hr noshade="noshade" size=1 width=700 />
<h3>Reports</h3>
		<?php
			include 'dbconnect.php';
			
			  //declare variable
			    $i=0;
			    $rowcnt = 0;
			    $str = "";
				
				/*$Query = " SELECT 
								DISTINCT j.property_id
							FROM 
								jobs j
							
							WHERE (
								j.job_type = 'Yearly Maintenance'
								AND j.status = 'pending'
							)
							ORDER BY
								j.property_id
						  ";*/
			    
			    $Query = " SELECT 
								j.property_id
							FROM 
								jobs j
							WHERE (
								j.status = 'pending'
							)
							ORDER BY
								j.property_id
						  ";
				
				$result = mysql_query($Query, $connection);
				$arr;
				
				while ($row = mysql_fetch_row($result))
   				{
					$arr[] = $row[0];
					$str .= "{$row[0]}, ";
					$rowcnt++;
   				}
				$str = substr($str, 0, -2);
				
			echo "Query: find pending jobs with property id.<br>";	
   			echo "Result found $rowcnt records: <br><br>$str";
		  	//print_r($arr);
		  	// close the database connection
		  	mysql_close($connection);
		?>

<hr noshade="noshade" size=1 width=700 />
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