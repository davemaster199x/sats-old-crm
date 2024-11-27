<?

$title = "View Technician Schedule";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

function getJobSched($jobdate,$country_id){
	return mysql_query("
		SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, j.id, sa.FirstName, sa.LastName
		FROM jobs AS j				
		LEFT JOIN `property` AS p ON j.property_id = p.property_id
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		WHERE j.status = 'Booked'
		AND j.date = '{$jobdate}'
		AND p.deleted = 0
		AND j.`del_job` = 0
		AND a.`country_id` ={$_SESSION['country_default']}
		ORDER BY sa.`StaffID`
	");
}

?>

  <div id="mainContentCalendar">
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Schedule" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>View Schedule</strong></a></li>
      </ul>
    </div>
   <div id="time"><?php echo date("l jS F Y"); ?></div>

	

<?php

// init the variables
$techid = $_GET['id'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];


    // Get key day informations. 
    // We need the first and last day of the month and the actual day

	// echo "Time is: ".time()."<br>\n";
	if ($usemonth == "") { $usemonth = date("m",time()); }
	if ($useyear == "") { $useyear = date("Y", time()); }

    $firstDay = getdate(mktime(0,0,0,$usemonth,1,$useyear));
	// fixup Asians shit code.
	if ($firstDay['wday'] == 0) { $firstDay['wday'] = 7; }
    $lastDay  = getdate(mktime(0,0,0,$usemonth+1,0,$useyear));
	$monthname = date("F", mktime(0,0,0,$usemonth,1,$useyear));

	$mydate = getdate(date("U"));
	
	// do the stuff for nextmonth.
	if ($usemonth == 12)
		{
		$nextmonth = 1;
		$nextyear = $useyear+1;
		}
	else
		{
		$nextmonth = $usemonth+1;
		$nextyear = $useyear;
		}
	
	// do the stuff for prevyear.
	if ($usemonth == 1)
		{
		$prevmonth = 12;
		$prevyear = $useyear-1;
		}
	else
		{
		$prevmonth = $usemonth-1;
		$prevyear = $useyear;

		}
		
    // Create a table with the necessary header informations
	
	
    echo "<table width=100% height=600px border=1 cellpadding=2 cellspacing=2 class='vos-table'>\n";
    echo " 
			<div class='aviw_drop-h' style='border: 1px solid #ccc; padding: 5px;'>
			
  <div class='fl-left' style='float: none;'>
    <a href='" . URL . "view_overall_schedule.php?month=$prevmonth&year=$prevyear'><span class='arw-lft'>&nbsp;</span></a>\n ".$monthname." ".$useyear."<a href='" . URL . "view_overall_schedule.php?month=$nextmonth&year=$nextyear'>&nbsp;<span class='arw-rgt'>&nbsp;</span></a>
  </div>
  
</div>
	";
    echo "<tr class='days rowred' valign=middle align=center height=30>\n";
    echo "  <td width=14%>Monday</td><td width=14%>Tuesday</td><td width=14%>Wednesday</td><td width=14%>Thursday</td>\n";
    echo "  <td width=14%>Friday</td><td width=14%>Saturday</td><td width=14% style='display:none;'>Sunday</td></tr>\n\n";
    
  
    // display the whitespace if applicable.
	    echo "<tr height=110>\n";
    for($i=1;$i<$firstDay['wday'];$i++){
        echo "<td>&nbsp;</td>\n";
    }
    $actday = 1;
	
	
	// display the first line of numbers.
    for($i=$firstDay['wday'];$i<7;$i++){
        
		if($i%7!=0){
            echo "<td valign=top align=left><a class=mystyle href='" . URL . "view_overall_schedule_day.php?id=$techid&day=$actday&month=$usemonth&year=$useyear'>$actday &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><br>\n";
			// insert query here

			   // set the jobdate.
			   $jobdate = $useyear."-".$usemonth."-".$actday;
				// echo "The Jobdate is $jobdate.";
	   

			   $result = getJobSched($jobdate);
			   // run through all the jobs of the day and print them.
			   while ($row = mysql_fetch_row($result))
				{
				// get postcode region
				$pr_sql = mysql_query("
					SELECT r.`region_name`, pr.`postcode_region_name`
					FROM `postcode_regions` AS pr
					LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
					WHERE pr.`postcode_region_postcodes` LIKE '%{$row[4]}%'
					AND pr.`country_id` = {$_SESSION['country_default']}
					AND pr.`deleted` = 0
				");
				$pr = mysql_fetch_array($pr_sql);
				echo "<a href='" . URL . "view_job_details.php?id=$row[6]'>$row[7] ".(($row[8]!='')?substr(strtoupper($row[8]),0,1).'.':'')." ({$pr['postcode_region_name']})</a><br>\n";
				}	
			 // close the Database Connection.
			 
				// end insert.
			echo "</td>\n";
		}
		$actday++;
		
    }
    echo "</tr>\n\n";
    
    //Get how many complete weeks are in the actual month
    $fullWeeks = floor(($lastDay['mday']-$actday)/7);
    
    for ($i=0;$i<$fullWeeks;$i++){
        echo "<tr height=110>\n";
        for ($j=0;$j<7;$j++){
            
			if($j%7!=0){
					echo "<td valign=top align=left><a class=mystyle href='/view_overall_schedule_day.php?id=$techid&day=$actday&month=$usemonth&year=$useyear'>$actday &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><br>\n";
					// insert query here

				   // set the jobdate.
				   $jobdate = $useyear."-".$usemonth."-".$actday;
					// echo "The Jobdate is $jobdate.";
		   
				   $result = getJobSched($jobdate);
				   // run through all the jobs of the day and print them.
				   while ($row = mysql_fetch_row($result))
					{
						// get postcode region
						$pr_sql = mysql_query("
							SELECT r.`region_name`, pr.`postcode_region_name`
							FROM `postcode_regions` AS pr
							LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
							WHERE pr.`postcode_region_postcodes` LIKE '%{$row[4]}%'
							AND pr.`country_id` = {$_SESSION['country_default']}
							AND pr.`deleted` = 0
						");
						$pr = mysql_fetch_array($pr_sql);
					echo "<a href='" . URL . "view_job_details.php?id=$row[6]'>$row[7] ".(($row[8]!='')?substr(strtoupper($row[8]),0,1).'.':'')." ({$pr['postcode_region_name']})</a><br>\n";
					}	
				 // close the Database Connection.
				 
					// end insert.
					
				echo "</td>\n";	
			}
			
			$actday++;
			
        }
        echo "</tr>\n\n";
    }
    
    //Now display the rest of the month
    if ($actday < $lastDay['mday']){
        echo '<tr height=110>';
        
        for ($i=0; $i<7;$i++){
		 
			if($i%7!=0){
			
				
				if ($actday <= $lastDay['mday']){
				
					echo "<td valign=top align=left><a class=mystyle href='/view_overall_schedule_day.php?id=$techid&day=$actday&month=$usemonth&year=$useyear'>$actday &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a><br>\n";
						// insert query here

				   // set the jobdate.
				   $jobdate = $useyear."-".$usemonth."-".$actday;
					// echo "The Jobdate is $jobdate.";
		   
					
				   $result = getJobSched($jobdate);
				   // run through all the jobs of the day and print them.
				   while ($row = mysql_fetch_row($result))
					{
					// get postcode region
					$pr_sql = mysql_query("
						SELECT r.`region_name`, pr.`postcode_region_name`
						FROM `postcode_regions` AS pr
						LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
						WHERE pr.`postcode_region_postcodes` LIKE '%{$row[4]}%'
						AND pr.`country_id` = {$_SESSION['country_default']}
						AND pr.`deleted` = 0
					");
					$pr = mysql_fetch_array($pr_sql);
					echo "<a href='" . URL . "view_job_details.php?id=$row[6]'>$row[7] ".(($row[8]!='')?substr(strtoupper($row[8]),0,1).'.':'')." ({$pr['postcode_region_name']})</a><br>\n";
					}	
				 // close the Database Connection.
				 
					// end insert.
					echo "</td>\n";

				}else {
					echo '<td>&nbsp;</td>';
				}
			
			}
			
			$actday++;
           
        }
        
        
        echo '</tr>';
    }
    
    echo '</table>';

?>

  </div>
  
  </div>

<br class="clearfloat" />

</body>
</html>
