<?php

include('inc/init.php');

$useday = $_GET['day'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];

// send headers for download
$filename = "Overall_Day_".$useday."-".$usemonth ."-".$useyear.".csv";

header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");

	$jobdate = $useyear."-".$usemonth."-".$useday;

	// improved query
	$selectQuery = "SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, sa.FirstName, sa.LastName, p.property_id, 
    p.testing_comments, j.comments, j.tech_comments, p.tenant_firstname1, p.tenant_lastname1, p.tenant_firstname2, p.tenant_lastname2, a.agency_name, 
    a.address_3, p.state, p.tenant_ph1, p.tenant_ph2, p.tenant_mob1, p.tenant_mob2, p.tenant_email1, p.tenant_email2, j.`service` 
	FROM jobs AS j 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`	
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
	WHERE j.date = '{$jobdate}' 
	ORDER BY sa.FirstName, j.sort_order ASC";

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

	echo "Agency,Street Address,Suburb,Sevice,Time Of Day,Tech Name,Tenant 1 Name,Tenant 1 Ph,Tenant 1 Mob,Tenant 1 Email,Tenant 2 Name,Tenant 2 Ph,Tenant 2 Mob,Tenant 2 Email,Comments,Tech Comments\n";
	#echo "Agency,Street Address,Suburb,Time Of Day,Tech Name,Tenant 1 Name,Tenant 1 Ph,Tenant 2 Name,Tenant 2 Ph,Comments,Tech Comments\n";

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_assoc($result))
   {
		$comment = str_replace("\r\n", ", ", $row[10]);
		switch($row['service']){
			case 2:
				$serv = 'Smoke Alarms';
			break;
			case 5:
				$serv = 'Safety Switch';
			break;
			case 6:
				$serv = 'Corded Windows';
			break;
			case 7:
				$serv = 'Pool Barriers';
			break;	
		}
		echo "$row[agency_name],$row[address_1] $row[address_2],$row[address_3],$serv,$row[time_of_day],$row[first_name] $row[last_name],$row[tenant_firstname1] $row[tenant_lastname1],$row[tenant_ph1],$row[tenant_mob1],$row[tenant_email1],$row[tenant_firstname2] $row[tenant_lastname2],$row[tenant_ph2],$row[tenant_mob2],$row[tenant_email2],$row[testing_comments],$comment\n"; 		
   }

   // (5) Close the database connection
   
?>
