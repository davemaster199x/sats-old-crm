<?

$title = "Mark Jobs Completed";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$arr = getHomeTotals(); 

?>



  <div id="mainContent">


	 <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Merged Jobs" href="merged_jobs.php"><strong>Merged Jobs</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>


<?php



   



//$todaydate = date(Y, time())."-".date(m,time())."-".date(d, time());



 // (2) Run the query 

 //$Query = "Select j.date, j.property_id from jobs j, property p WHERE (j.property_id = p.property_id AND j.status='Merged Certificates');";
 $Query = "
 SELECT j.`date`, j.`property_id`, j.`id` 
 FROM jobs AS j
 LEFT JOIN property AS p ON j.`property_id` = p.`property_id` 
 LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
 WHERE j.`status` = 'Merged Certificates'
 AND a.`country_id` = {$_SESSION['country_default']}
 ";

 $result = mysql_query ($Query, $connection);

 

   while ($row = mysql_fetch_array($result))

   {

   		$jobdate = $row['date'];

		$property_id = $row['property_id'];
		
		$job_id = $row['id'];

		//print_r($row);

		

		$Query = "UPDATE property p, jobs j SET p.test_date='$jobdate', p.retest_date=(DATE_ADD('$jobdate', INTERVAL 1 YEAR)) WHERE (p.property_id = $property_id);";

    	mysql_query ($Query, $connection);



    	$Query = "UPDATE jobs SET status='Completed' WHERE status='Merged Certificates' AND `id`={$job_id}";

		mysql_query ($Query, $connection);
		
		
		
		
		// insert job logs
		mysql_query("
			INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`eventtime`,
				`comments`,
				`job_id`,
				`staff_id`
			) 
			VALUES (
				'Merged Certificates',
				'" . date('Y-m-d') . "',
				'" . date('H:i') . "',
				'Job status updated from <strong>Merged Certificates</strong> to <strong>Completed</strong>', 
				'{$job_id}',
				'{$_SESSION['USER_DETAILS']['StaffID']}'
			)
		");
		

   }



	// Set the Letter sent date

    // $Query = "UPDATE jobs j, property p SET p.tenant_ltr_sent='$todaydate' WHERE (j.property_id = p.property_id AND j.status='Merged Certificates');";

	

/*  $Query = "UPDATE property p, JOBS j set p.test_date='$todaydate', p.retest_date=(DATE_ADD('$todaydate', INTERVAL 1 YEAR)) WHERE (p.property_id=j.property_id);";

    mysql_query ($Query, $connection);





    $Query = "UPDATE jobs SET status='Completed' WHERE (status='Merged Certificates');";

	mysql_query ($Query, $connection);

*/

    

	echo "<div class='success'>All Jobs Marked Completed</div>";

?>



    <p>

      <!-- end #mainContent -->

    </p>

  </div>

	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />





</body>

</html>