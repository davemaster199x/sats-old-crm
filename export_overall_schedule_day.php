<?php

include('inc/init.php');

$useday = $_GET['day'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];

// send headers for download
$filename = "Overall_Schedule_Day_".$useday."-".$usemonth ."-".$useyear.".csv";

header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename=$filename");
header("Pragma: no-cache");

	$jobdate = $useyear."-".$usemonth."-".$useday;

	// improved query
	$selectQuery = "SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, sa.FirstName, sa.LastName, p.property_id, j.id, j.service 
	FROM jobs AS j
	LEFT JOIN property AS p ON j.property_id = p.property_id 
	LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
	WHERE j.status = 'Booked' 		
	AND j.date = '$jobdate' 
	AND p.deleted = 0
	ORDER BY sa.FirstName;";

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

	echo "Service,Status,Address,Postcode,Time,Name\n";
	

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_array($result))
   {
		$comment = str_replace("\r\n", ", ", $row[10]);
		
		//service
		$ajt_sql = mysql_query("
			SELECT `type`
			FROM `alarm_job_type`
			WHERE `id` = {$row['service']}
		");
		$ajt = mysql_fetch_array($ajt_sql);
				
		echo "\"{$ajt['type']}\",{$row['status']},\"$row[address_1] $row[address_2]\",$row[address_3],$row[time_of_day],\"$row[FirstName] $row[LastName]\"\n"; 		
   }

   // (5) Close the database connection
   
?>
