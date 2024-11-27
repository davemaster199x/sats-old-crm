<?

$title = "View Tech Schedule Day";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

    
  <div id="mainContent">
  
  <div class="sats-middle-cont">

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Overall Schedule" href="<?php echo $_SERVER['REQUEST_URI']; ?>"><strong>Overall Schedule</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>
  
<?php

$useday = $_GET['day'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];

	$jobdate = $useyear."-".$usemonth."-".$useday;

   // (1) Open the database connection and use the winestore database
   

    //re-format the date into prefer readable format
	$datebooked = $useday."-".$usemonth."-".$useyear;
	$today = date("d-m-Y");			//format today's date to string
	//echo "today: $today";
		
	$dc = date_create($datebooked);		//craate date object from string
	$dateformat = date_format($dc, 'd-m-Y');
	//echo " create: ".$dateformat;
	
	$datebooked = ($dateformat == $today) ? "today" : date_format($dc, 'D d-M-Y');
		
	//$selectQuery = "select count(status) FROM jobs;";
	//$selectQuery = "select count(id) FROM jobs WHERE status='Booked' AND date='$jobdate';";
	
	$selectQuery = "
		SELECT count(j.`id`) 
		FROM jobs AS j
		LEFT JOIN property AS p ON j.property_id = p.property_id
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status`='Booked' 
		AND j.`date`='$jobdate'
		AND j.`del_job`= 0
		AND a.`country_id` ={$_SESSION['country_default']}
	";
	
    $result = mysql_query ($selectQuery, $connection);
	$row = mysql_fetch_row($result);
	$row[0] = $row[0];
	echo "
		<div class='vosch-tp aviw_drop-h'>

  <div class='fl-left'>
    Total Jobs for $datebooked is: $row[0]
  </div>

  <div class='fl-left pull-left'>
  <a href='export_overall_schedule_day.php?day={$useday}&amp;month={$usemonth}&amp;year={$useyear}' class='submitbtnImg colorwhite'>Export</a>
  </div>
  
</div>
	\n";


	
	
	$selectQuery = "
	SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, sa.FirstName, sa.LastName, p.property_id, j.id, j.service
	FROM jobs AS j
	LEFT JOIN property AS p ON j.property_id = p.property_id
	LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
	WHERE j.status = 'Booked'
	AND j.date = '{$jobdate}'
	AND p.deleted =0
	AND j.del_job =0
	AND a.`country_id` ={$_SESSION['country_default']}
	ORDER BY sa.FirstName
	";
	
	//echo $selectQuery;
   // (2) Run the query on the winestore through the
   //  connection
   $result = mysql_query ($selectQuery, $connection);
?>

<table border=0 cellspacing=0 cellpadding=5 width=100% class="table-left tbl-fr-red">
<tr bgcolor="#b4151b" style="border-bottom: 1px solid #B4151B !important;">
<th>#</th>
<th>Service</th>
<th>Status</th>
<th>Address</th>
<th>Key</th>
<th>Notes</th>
<th>Time</th>
<th>Name</th>




<?php

	if (mysql_num_rows($result) == 0)
	{
	echo "No Bookings for Today!<br>\n";
	}
	
	$count=0;

	$odd=0;

   // (3) While there are still rows in the result set,
   // fetch the current row into the array $row
   while ($row = mysql_fetch_row($result))
   {
   $odd++;
	if (is_odd($odd)) {
		echo "<tr bgcolor=#FFFFFF>";		
		} else {
		echo "<tr bgcolor=#F0F0F0>";
   		}
		$count++;
      echo "\n";
     // (4) Print out each element in $row, that is,
     // print the values of the attributes

		echo "<td>$count</td>";
		
		/*
		switch($row[10]){
			case 2:
				$serv = "Smoke Alarms";
			break;
			case 5:
				$serv = "Safety Switch";
			break;				
			case 6:
				$serv = "Corded Windows";
			break;
			case 7:
				$serv = "Pool Barriers";
			break;
		}
		*/
		
		//service
		$ajt_sql = mysql_query("
			SELECT `type`
			FROM `alarm_job_type`
			WHERE `id` = {$row[10]}
		");
		$ajt = mysql_fetch_array($ajt_sql);
		
		echo "<td>{$ajt['type']}</td>";

		echo "<td>";		
		echo "<a href='" . URL . "view_job_details.php?id=$row[9]'>$row[0]</a>";
		echo "</td>";


		echo "<td>";		
		echo "<a href='" . URL . "view_property_details.php?id=$row[8]'>".$row[1]." ".$row[2].", ".$row[3]."</a>";
		echo "</td>";

		// key
		$key_sql = mysql_query("
			SELECT `key_number`
			FROM `jobs`
			WHERE `id` = {$row[9]}
		");
		$key = mysql_fetch_array($key_sql);
		echo "<td>";		
		echo $key['key_number'];
		echo "</td>";
		
		// notes
		$tn_sql = mysql_query("
			SELECT `tech_notes`
			FROM `jobs`
			WHERE `id` = {$row[9]}
		");
		$tn = mysql_fetch_array($tn_sql);
		echo "<td>";		
		echo $tn['tech_notes'];
		echo "</td>";
				
		echo "<td>";		
		echo $row[5];
		echo "</td>";

		echo "<td>";		
		echo $row[6];
		echo "</td>";

      // Print a carriage return to neaten the output
      echo "\n";
   }

?>




</table>


  </div>
</div>

<br class="clearfloat" />

</body>
</html>
