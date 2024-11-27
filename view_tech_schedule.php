<?

$title = "View Technician Schedule";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
  <?php
  }  
  ?>
	

  <div id="mainContentCalendar">
  
  
   <div class="sats-middle-cont">
   
   
    <?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
  
  $tech_id = $_SESSION['USER_DETAILS']['StaffID'];
  
  $day = date("d");
  $month = date("m");
  $year = date("y");
  
  include('inc/tech_breadcrumb.php');
   
  }else{ ?>
  
   <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="View Schedule" href="<?php echo $_SERVER['REQUEST_URI']; ?>"><strong>View Schedule</strong></a></li>
      </ul>
    </div>
  
  <?php
  }
  ?>  
   
   
	
	
	
   <div id="time"><?php echo date("l jS F Y"); ?></div>
   
   
   
   
  
  
	
   


<?php

// init the variables
$techid = $_GET['id'];
$usemonth = $_GET['month'];
$useyear = $_GET['year'];



?>

<h2><?php echo date('F, Y',strtotime("{$useyear}-{$usemonth}-01")) ?></h2>

<?

		// get the name of the technician.
   

		   // (2) Run the query
		   $selectQuery = "SELECT FirstName, LastName FROM staff_accounts WHERE (StaffID = $techid);";
		   $result = mysql_query($selectQuery, $connection);
		   // run through all the jobs of the day and print them.
		   while ($row = mysql_fetch_row($result))
		    {
			$tech_name = $row[0] . " " . $row[1];
			}	
	  	 // close the Database Connection.
	  	 
			// end insert.


    // Get key day informations. 
    // We need the first and last day of the month and the actual day

	// echo "Time is: ".time()."<br>\n";
	
//	$usemonth = 8;
//	$useyear = 2009;

//    $today    = getdate();
//	$firstDay['wday'] = 1;
    $firstDay = getdate(mktime(0,0,0,$usemonth,1,$useyear));
	// fixup Asians shit code.
	if ($firstDay['wday'] == 0) { $firstDay['wday'] = 7; }
    $lastDay  = getdate(mktime(0,0,0,$usemonth+1,0,$useyear));
	$monthname = date("F", mktime(0,0,0,$usemonth,1,$useyear));
//	echo "Firstday is: ".$firstDay['wday'].".";
   // echo "Usemonth: $usemonth.\n";
//	echo "Today is $today[mday]<br>\n";
	$mydate = getdate(date("U"));
//    echo "The Year is: $mydate[year]<br>\n";
//	echo "The Month is: $mydate[month]<br>\n";
//	echo "The Day is: $mydate[mday]<br>\n";
	
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
	

	/*
	echo "
	<div class='ap-vw-reg-sch aviw_drop-h' style='height: 28px; padding-top: 11px; text-align: center; border-bottom: 1px solid #CCCCCC;'>
		<a href='" . URL . "view_tech_schedule.php?id=$techid&month=$prevmonth&year=$prevyear'>
			<span class='arw-lft'>&nbsp;</span></a>
			<div class='vtc-mid mn-vtc'>".$monthname." ".$useyear." ". $tech_name." </div>
			<span class='vtc-mid mn-vtc'>".$monthname." ".$useyear." ". $tech_name." </span>
		<a href='" . URL . "view_tech_schedule.php?id=$techid&month=$nextmonth&year=$nextyear'>
			<span class='arw-rgt'>&nbsp;</span>
		</a>
	</div>";
	*/
	

	
		
    // Create a table with the necessary header informations
    echo "<table width=100% height=600px border=1 cellpadding=0 cellspacing=0  class='table-left'>\n";
	
	 $actday = 0;
	
	// display the first line of numbers.
    for($i=1;$i<=31;$i++){
		
		
		
		
		
        $actday++;
		
		
		$hiderow = 0;
		
		$jdate = "{$useyear}-{$usemonth}-{$actday}";
		$jdate2 = date('Y-m-d',strtotime($jdate));
		
		if( $jdate2 < date('Y-m-d') ){
			$hiderow = 1;
		}
		
		
		echo "<tr height=60 style='border: 1px solid #ccc; border-top: 1px solid transparent; ".( ( $hiderow==1 && $_SESSION['USER_DETAILS']['ClassName'] == "TECHNICIAN" )?'display:none;':'' )."'>";
		
		// check tech run
		$tr_sql = mysql_query("
			SELECT *
			FROM `tech_run`
			WHERE `assigned_tech` = {$techid}
			AND `date` = '{$jdate2}'
		");
		
		if( mysql_num_rows($tr_sql)>0 ){
			$hasTechRun = 1;
			$tr = mysql_fetch_array($tr_sql);
			$tr_id = $tr['tech_run_id'];
			$date = $tr['date'];
			$sub_regions = $tr['sub_regions'];
			//$jurl = "/view_tech_schedule_day2.php?tr_id={$tr_id}";
			$jurl = "/tech_day_schedule.php?tr_id={$tr_id}";
		}else{
			$hasTechRun = 0;
			//$jurl = "/view_tech_schedule_day.php?id=$techid&day=$actday&month=$usemonth&year=$useyear";
			$jurl = "/tech_day_schedule.php?tr_id={$tr_id}";
		}
		
            echo "<td valign=top align=left style='padding: 0px;'><a class=mystyle href='{$jurl}'>$actday &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>";
		// insert query here
		
			if($_SESSION['USER_DETAILS']['ClassID']!=6){

			$domain = $_SERVER['SERVER_NAME'];
			$dev_str = (strpos($domain,"crmdev")===false)?'':'_dev';
			//echo '<a href="http://smokealarmregistrar.com.au/public_map'.$dev_str.'.php?api_key=sats123&tech_id='.$tech_id.'&day='.$day.'&month='.$month.'&year='.$year.'&country_id='.$_SESSION['country_default'].'"><img src="/images/google_map/main_pin_icon.png"></a></div>';

				if( $hasTechRun == 1 ){
			?>

				<a href="<?php echo PUBlIC_MAP_DOMAIN; ?>/tech_run<?php echo $dev_str; ?>.php?api_key=sats123&tr_id=<?php echo $tr_id; ?>&country_id=<?php echo $_SESSION['country_default']; ?>">
					<img src="/images/google_map/main_pin_icon.png">
				</a>

			<?php	
				}

			}
		
			?>
			
			
			<br />
			<table>
			
					<tr style="border: 1px solid #CCCCCC; border-left: none; border-right: none; background-color: #B4151B; color: #fff;">
						<td class="bold">Status</td>
						<td class="bold">Address</td>
						<td class="bold">Key #</td>
						<td class="bold">Notes</td>
						<td class="bold">Time</td>
						<td class="bold">Agency</td>
						
						<?php
						if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
							<td class="bold">Completed</td>
						<?php	
						}
						?>
					</tr>
			
			
			<?php
			
			
			
			
			// has tech run use VTS2
			if( $hasTechRun==1 ){
				
				$job_ctr = 0;
				$jr_list2 = getTechRunRows($tr_id,$_SESSION['country_default']);
				$comp_count = 0;
				$jobs_count = 0;
				while( $row = mysql_fetch_array($jr_list2) ){ 

					$hiddenText = "";
					$showRow = 1;
					$isUnavailable = 0;
					$isHidden = 0;
					$isPriority = 0;

					// JOBS
					if( $row['row_id_type'] == 'job_id' ){
						
						$jr_sql = getJobRowData($row['row_id'],$_SESSION['country_default']);
						$row2 = mysql_fetch_array($jr_sql);
						
						// if job type is 240v Rebook and status is to be booked and the tech is not electricianthen hide it
						if( $row2['job_type']=='240v Rebook' && $row2['j_status']=='To Be Booked' && $isElectrician==false ){
							$hiddenText .= '240v<br />';
							$showRow = 0;
						}else{
							$showRow = 1;
						}
						
						if( $row['hidden']==1 ){
							$hiddenText .= 'User<br />';
						}
						
						if( $row2['unavailable']==1 && $row2['unavailable_date']==$date ){
							$isUnavailable = 1;
							$hiddenText .= 'Unavailable<br />';
						}
						
						$startDate = date('Y-m-d',strtotime($row2['start_date']));
						
						if( $row2['job_type'] == 'Lease Renewal' && ( $row2['start_date']!="" && $date < $startDate ) ){
							$hiddenText .= 'LR<br />';
						}
						
						if( $row2['job_type'] == 'Change of Tenancy' && ( $row2['start_date']!="" && $date < $startDate  ) ){
							$hiddenText .= 'COT<br />';
						}
						
						if( $row2['j_status'] == 'DHA' && ( $row2['start_date']!="" && $date < $startDate ) ){
							$hiddenText .= 'DHA<br />';
						}
						
						if( $row2['j_status'] == 'On Hold' && ( $row2['start_date']!="" && $date < $startDate ) ){
							$hiddenText .= 'On Hold<br />';
						}			
						
						if( $row2['j_status'] == 'On Hold' && $row['allow_upfront_billing']==1 ){
							$hiddenText .= 'Up Front Billing<br />';
						}
						
						// this job is for electrician only
						if( $row2['electrician_only'] == 1 && $isElectrician == false ){
							$hiddenText .= 'Electrician Only<br />';
						}
						
						
						/*
						if( $row2['j_status'] == 'Allocate' && ( $row2['start_date']!="" && $date < $startDate ) ){
							$hiddenText .= 'Allocate<br />';
						}
						*/
						
						if( $show_hidden==0 && $hiddenText!="" && $row2['j_status']!='Booked' ){
							$showRow = 0;
						}else{
							$showRow = 1;
						}
						
						
						
						
						$bgcolor = "#FFFFFF";
						if($row2['job_reason_id']>0){
							//$bgcolor = "#fffca3";
						}else if($row2['ts_completed']==1){
							//$bgcolor = "#c2ffa7";
						}
						
						
						
						
						$j_created = date("Y-m-d",strtotime($row2['created']));
						$last_60_days = date("Y-m-d",strtotime("-60 days"));

						
						if( $row['dnd_sorted']==0 ){
							$bgcolor = '#FFFF00';
						}
						
						if( $hiddenText!="" ){
							$hiddenRowsCount++;
							//$bgcolor = "#ADD8E6";
							$isHidden = 1;
						}
						
						if( $show_hidden==1 && ( $row['hidden']==1 || $isUnavailable==1 ) ){
							$hideChk = 0;
						}else if( $show_hidden==1 ){
							$hideChk = 1;
						}else{
							$hideChk = 0;
						}
						
						
						if( $row['highlight_color']!="" ){
							//$bgcolor = $row['highlight_color'];
						}
						
						
						// priority jobs
						if( 
							$row2['job_type'] == "Change of Tenancy" || 
							$row2['job_type'] == "Lease Renewal" || 
							$row2['job_type'] == "Fix or Replace" || 
							$row2['job_type'] == "240v Rebook" || 
							$row2['j_status'] == 'DHA' ||
							$row2['j_status'] == 'On Hold' ||
							$row2['urgent_job'] == 1 
						){
							$isPriority = 1;
						}else{
							$isPriority = 0;
						}

						if( $showRow==1 ){

						
					?>
						<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo $bgcolor; ?>">
							<?php
							if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
								<td class="jstatus">
									<a href="view_job_details.php?id=<?php echo $row2['jid']; ?>">
										<?php echo $row2['j_status']; ?>
									</a>
									<a href="view_job_details_tech.php?id=<?php echo $row2['jid']; ?>">[ts]</a>
								</td>
							<?php	
							}else{ ?>
								<td class="jstatus">
									<a href="view_job_details_tech.php?id=<?php echo $row2['jid']; ?>"><?php echo $row2['j_status']; ?></a>
								</td>
							<?php	
							}
							?>							

							<?php
							$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
							if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
								<td><a href="view_property_details.php?id=<?php echo $row2['property_id']; ?>"><?php echo $paddress; ?></a></td>						
							<?php				
							}else{ ?>
								<td><a href="http://maps.google.com/?q=<?php echo $paddress; ?>" target="_blank"><?php echo $paddress; ?></a></td>						
							<?php	
							}
							?>													
							<td><?php echo $row2['key_number']; ?></td>					
							<td><?php echo $row2['tech_notes']; ?></td>
							<td><?php echo $row2['time_of_day']; ?></td>								
							<?php 
							if( $_SESSION['USER_DETAILS']['ClassName'] != 'TECHNICIAN' ){ ?>
							
								<td>
									<?php $ci_link = $crm->crm_ci_redirect("/agency/view_agency_details/{$row2['agency_id']}"); ?>
									<a href="<?php echo $ci_link; ?>" class="agency_td">
										<?php echo str_replace('*do not use*','',$row2['agency_name']); ?>
									</a>
									<br /><?php echo $row2['a_phone']; ?>
								</td>							
							<?php	
							}else{ ?>
								<td>
									<?php echo str_replace('*do not use*','',$row2['agency_name']); ?>
									<br /><?php echo $row2['a_phone']; ?>
								</td>
							<?php	
							}						
							if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){
								echo "<td>".(($row2['completed_timestamp']!="")?date("H:i",strtotime($row2['completed_timestamp'])):'')."</td>";
							}
							
							?>
						</tr>
					<?php	

						}
							
							
					}else{ 
					
						// KEYS
						$k_sql = getTechRunKeys($row['row_id'],$_SESSION['country_default']);
						$kr = mysql_fetch_array($k_sql);
						

						

						?>
							<tr id="<?php echo $row['tech_run_rows_id']; ?>" style="background-color:<?php echo ($kr['completed']==1)?'#c2ffa7':'#ffffff'; ?>;">
								<td>
									<?php 
										if($kr['completed']==1){
											$kr_act = explode(" ",$kr['action']);
											$temp2 = ($kr['action']=="Drop Off")?'p':'';
											$temp = "{$kr_act[0]}{$temp2}ed";
											$action = "{$temp} {$kr_act[1]}";
										}else{
											$action = $kr['action'];
										}
										echo $action;
									?>
								</td>															
								<td><?php echo "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}"; ?></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td><?php echo $kr['agency_hours']; ?></td>						
								<td>
									<?php $ci_link2 = $crm->crm_ci_redirect("/agency/view_agency_details/{$kr['agency_id']}"); ?>
									<a href="<?php echo $ci_link2; ?>">
										<?php echo str_replace('*do not use*','',$kr['agency_name']); ?>
									</a>
									<br />
									<?php echo $kr['phone']; ?>
								</td>																						
								<?php
								if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
									<td colspan="2">&nbsp;</td>
								<?php	
								}
								?>								
							</tr>
						<?php
					
						
					}
						
						
				}
				
								
			}else{
				
				
				// set the jobdate.
			   $jobdate = $useyear."-".$usemonth."-".$actday;
				// echo "The Jobdate is $jobdate.";
	   

			   // (2) Run the query
			   //$selectQuery = "SELECT j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, j.id FROM jobs j, property p, techs t WHERE (j.tech_id = t.id AND t.id = $techid AND j.property_id = p.property_id AND j.date = '$jobdate' AND p.deleted = 0) ORDER BY sort_order ASC;";
			   
			   $selectQuery = "
			   SELECT *, 
					j.`id` AS jid,
					j.`status` AS jstatus,
					p.`address_1` AS paddress1,
					p.`address_2` AS paddress2,
					p.`address_3` AS paddress3,
					a.`address_1` AS aaddress1,
					a.`address_2` AS aaddress2,
					a.`address_3` AS aaddress3,
					a.`postcode` AS apostcode,
					a.`phone` AS aphone
			   FROM jobs j
			   LEFT JOIN property p ON j.`property_id` = p.`property_id` 
			   LEFT JOIN agency AS a ON a.`agency_id` = p.`agency_id`
			   LEFT JOIN staff_accounts AS sa ON j.`assigned_tech` = sa.`StaffID`
			   WHERE j.`assigned_tech` = {$techid}
			   AND j.`date` = '{$jobdate}'
			   AND p.`deleted` = 0
			   AND j.`del_job` = 0
			   AND a.`country_id` ={$_SESSION['country_default']}
			   ORDER BY sort_order ASC
			   ";
			   
			   $result = mysql_query($selectQuery, $connection);
			   // run through all the jobs of the day and print them.
			   if(mysql_num_rows($result)>0){
					while ($row = mysql_fetch_array($result))
					{				
						$page = ($_SESSION['USER_DETAILS']['ClassName'] <> "TECHNICIAN")?'view_job_details':'view_job_details_tech'; 
						?>
						
								
								<tr>								
									<td><a href="/<?php echo $page; ?>.php?id=<?php echo $row['jid'] ?>"><?php echo $row['jstatus']; ?></a></td>
									<td><?php echo $row['paddress1']." ".$row['paddress2'].", ".$row['paddress3']; ?></td>
									<td><?php echo $row['key_number']; ?></td>
									<td><?php echo $row['tech_notes']; ?></td>
									<td><?php echo $row['time_of_day']; ?></td>
									<td>
										<?php echo str_replace('*do not use*','',$row['agency_name']); ?><br />
										<?php echo $row['aphone']; ?>
									</td>
									<td><?php echo $row['aaddress1'] . " " . $row['aaddress2']."<br />".$row['aaddress3'] . " " . $row['apostcode']; ?></td>
									<?php
									if($_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN"){ ?>
										<td><?php echo (($row['completed_timestamp']!="")?date("H:i:s",strtotime($row['completed_timestamp'])):'') ?></td>
									<?php	
									}
									?>								
								</tr>
							
						<?php
							
					}
				}else{?>
					<tr style="border-bottom: none !important; border-right: none !important; border-left: none !important;"><td colspan="6">No Data</td></tr>
				<?php	
				}
	
			}
			
			?>
		   
			
				</table>
			<?php
		echo "</td>\n";
	echo "</tr>";

    }
	


	  	 // close the Database Connection.
	  	 
			// end insert.
    
    //Get how many complete weeks are in the actual month
    $fullWeeks = floor(($lastDay['mday']-$actday)/7);
 
    
    echo '</table>';
	
	$prev_month_link = date('m',strtotime("{$useyear}-{$usemonth}-01 -1 month"));
	$prev_month_year_link = date('Y',strtotime("{$useyear}-{$usemonth}-01 -1 month"));
	$prev_month_name = date('F',strtotime("-1 month"));
	
	$next_month_link = date('m',strtotime("{$useyear}-{$usemonth}-01 +1 month"));
	$next_month_year_link = date('Y',strtotime("{$useyear}-{$usemonth}-01 +1 month"));
	$next_month_name = date('F',strtotime("+1 month"));
	
	echo "
	<p> 
		<a href='view_tech_schedule.php?id={$techid}&month={$prev_month_link}&year={$prev_month_year_link}'>
			<-
			{$prev_month_name}
		</a>
		| 
		<a href='view_tech_schedule.php?id={$techid}&month={$next_month_link}&year={$next_month_year_link}'>
			{$next_month_name}
			->			
		</a>
	<p>";

?>


  </div>
  
    </div>

<br class="clearfloat" />

</body>
</html>
