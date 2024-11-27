<?php

include('inc/init.php');

$useday = $_GET['day'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];
$techid = $_GET['id'];

// send headers for download
$filename = "Jobs_.Tech_".$techid."_".date("d")."-".date("m")."-".date("y").".txt";

header("Content-Type: text/plain");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");


	$jobdate = $useyear."-".$usemonth."-".$useday;

   // (1) Open the database connection
   

	$selectQuery = "select count(status) FROM jobs;";
    $result = mysql_query ($selectQuery, $connection);
	$row = mysql_fetch_row($result);
	$row[0] = $row[0];

	// improved query
	$selectQuery = "SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, sa.FirstName, sa.LastName, p.property_id, p.testing_comments, j.comments, j.tech_comments, p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, a.agency_name, a.address_3, p.state, p.tenant_ph1, p.tenant_ph2 
	FROM jobs AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`	
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
	WHERE sa.`StaffID` = $techid 
	AND j.date = '$jobdate' 
	ORDER BY j.sort_order ASC;
	";
	
	//echo $selectQuery;
   // (2) Run the query
   $result = mysql_query ($selectQuery, $connection);

	// echo $selectQuery;

	if (mysql_num_rows($result) == 0)
	{
	echo "<Br><br>No Bookings for Today!<br><br>\n";
	}
	
	$count = 0;

	$odd=0;

	echo "Agency\tStreet Address\tSuburb\tTime Of Day\tTech Name\tTenant 1 Name\tTenant 1 Ph\tTenant 2 Name\tTenant 2 Ph\tComments\tTech Comments\n";
	#echo "Agency,Street Address,Suburb,Time Of Day,Tech Name,Tenant 1 Name,Tenant 1 Ph,Tenant 2 Name,Tenant 2 Ph,Comments,Tech Comments\n";

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_row($result))
   {
		$comment = str_replace("\r\n", ", ", $row[10]);
		//echo "$row[16], $row[17]\t$row[1] $row[2]\t$row[3]\t$row[5]\t$row[6] $row[7]\t$row[12] $row[13]\t$row[19]\t$row[14] $row[15]\t$row[20]\t$row[9]\t$row[10]\n";
		echo "$row[16]\t$row[1] $row[2]\t$row[3]\t$row[5]\t$row[6] $row[7]\t$row[12] $row[13]\t$row[19]\t$row[14] $row[15]\t$row[20]\t$row[9]\t$comment\n"; 
		#echo "$row[16],$row[1] $row[2],$row[3],$row[5],$row[6] $row[7],$row[12] $row[13],$row[19],$row[14] $row[15],$row[20],$row[9],$comment\n";  
   }

   // (5) Close the database connection
   
?>
