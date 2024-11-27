<?php

include('inc/init.php');

// init the variables
$property_id = $_GET['id'];
$jobtype = $_GET['jobtype'];

   // (1) Open the database connection 
   

   // (2) Run the query
   // Create a job with the property_id
   
	switch ($jobtype)
	{
		case "oo": $ijobtype = "Once-off";  break;
		case "ct": $ijobtype = "Change of Tenancy"; break;
		case "ym": $ijobtype = "Yearly Maintenance"; break;
        // case "yt": $ijobtype = "Yearly Test"; break;
        case '240v': $ijobtype = "240v Rebook"; break;
        case 'for': $ijobtype = "Fix or Replace"; break;
		case "":   $ijobtype = "Change of Tenancy"; break;
	}

   $insertQuery = "INSERT INTO jobs (job_type, property_id, status) VALUES ('$ijobtype', '$property_id', 'To Be Booked');";

     if ((@ mysql_query ($insertQuery,$connection)) && @ mysql_affected_rows() == 1){
	 	//header("Location: http://localhost/view_jobs.php?status=notbooked");
	 	#repopulate alarms
	 populateAlarms(mysql_insert_id());
	 	
	 	
		header("Location: " . URL . "view_jobs.php?status=notbooked");
	 }
     else
        echo "An Error occurred, could not Create a job, how odd..." . $insertQuery;		

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
      // Print a carriage return to neaten the output

      echo "\n";

   // (5) Close the database connection
   
?>
