<?

$title = "View Tech Calendars";
$onload = 1;
//$onload_txt = "zxcSelectSort('agency',1)";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$currentUser = $_SESSION['USER_DETAILS']['StaffID'];


// GRAB THE DATES FROM THE URL
$month = $_GET['month'];
$year = $_GET['year'];
// IF THE DATES DONT EXISIT IN URL THEN USE CURRENT
if(!isset($_GET['month'])) {
	$month = date(m);
}
if(!isset($_GET['year'])) {
	$year = date(Y);
}


?>


<?php
  if($_SESSION['USER_DETAILS']['ClassID']==6){ ?>  
	<div style="clear:both;"></div>
  <?php
  }  
  ?>

<div id="mainContent">

<div class="sats-middle-cont">

	 <?php
	  if($_SESSION['USER_DETAILS']['ClassID']==6){ 
	  
	  $tech_id = $_SESSION['USER_DETAILS']['StaffID'];
	  
	  $day2 = date("d");
	  $month2 = date("m");
	  $year2 = date("y");
	  
	  include('inc/tech_breadcrumb.php');
	   
	  }else{ ?>

    <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
        <li class="other first"><a title="Individual Staff Calendar" href="<?php echo $_SERVER['PHP_SELF']; ?>"><strong>My Calendar</strong></a></li>
      </ul>
    </div>
	
	<?php
	}
	?> 
	
	
	
   <div id="time"><?php echo date("l jS F Y"); ?></div>
	
    
    <div class="visc-top">
	<?php
	if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
		<div style=" float: left;margin-left: 7px;"><a href="/add_calendar_entry_static.php"><button type="button" class="submitbtnImg">Add Entry</button></a></div>
	<?php	
	}
	?>	
	
	<?php

		// fetch all the calendar entries for the loged in user
		$rows = array();
		$sql = "SELECT 
                  c.calendar_id,
                  c.staff_id,
                  c.region,
                  c.date_start,
                  c.date_finish,
                  s.FirstName,
                  s.LastName ,
				  c.`booking_target`,
				  c.`details`
                FROM
                  calendar c 
                  INNER JOIN staff_accounts s 
                    ON (s.StaffID = c.staff_id) 
					LEFT JOIN `country_access` AS ca ON s.`StaffID` = ca.`staff_accounts_id`
                WHERE StaffID = '". $currentUser ."'
				AND ca.`country_id` = {$_SESSION['country_default']}
                ORDER BY date_start ;
                ";
		$query = mysql_query($sql, $connection);
		while($result = mysql_fetch_assoc($query)) {
			$rows[] = $result;
		}		
		
		//the tables rely on this to form.
		$monthname = mktime(0, 0, 0, $month, 1, $year);
		$monthname = date("F", $monthname);
		//echo '<br/>';
		
		//get the number of days in the month
		$calendardays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		

		$countday = 0;
		$themonth = array();
		
		//echo '<h2>'. $monthname .', '. $year .'</h2>';
		//echo '<br/>';
		
		//ENABLE TIME TRAVEL
		//GO BACKWARDS ONE MONTH
		if($month == 1){
			$backyear = $year - 1;
			$backmonth = 12;			
		} else {
			$backmonth = $month - 1;
			$backyear = $year;
		}
		
		echo date("F",mktime(0,0,0,$backmonth,1,$backyear)).'<a href="view_individual_staff_calendar.php?month='. $backmonth .'&year='. $backyear .'" style="margin-left: 5px;"><span class="arw-lft3">&nbsp;</span> <span class="r-bck-mnt"></span> </a>'.$monthname .' '. $year;
		
		//GO FORWARDS ONE MONTH
		if($month == 12){
			$forwardyear = $year + 1;
			$forwardmonth = 1;			
		} else {
			$forwardmonth = $month + 1;
			$forwardyear = $year;
		}
		
		echo '<a href="view_individual_staff_calendar.php?month='. $forwardmonth .'&year='. $forwardyear .'" style="margin-right: 5px;"><span class="l-bck-mnt"></span> <span class="arw-rgt3">&nbsp;</span></a>'.date("F",mktime(0,0,0,$forwardmonth,1,$forwardyear));
		
		while($countday < $calendardays) {
				
			$thedate = $countday + 1;
			$whiledate = $year.'-'.$month.'-'.$thedate;
			
			// check if date is weekend
			$weekDay = date('w', strtotime($whiledate));
			if($weekDay == 0 || $weekDay == 6) {
				$isWeekend = TRUE;
			} else {
				$isWeekend = FALSE;
			}
			
			$themonth[$countday]['date'] = $whiledate;
			$themonth[$countday]['weekend'] = $isWeekend;
						
			$countday = $countday + 1;
		}
		
		//Have the date running down the side outside of the scrolling div
		?>
        
        </div>
        
		<div class="calendardates">
			<table class="techcalendar individual align-left" cellpadding="0" cellspacing="0" border="0" style="width:auto;">

				<tr>
					<th><div class="jdate" style="width:100px;">Date</div></th>
					<?php
					if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
						<th style="width:16%;">Calendar Item</th>
					<?php	
					}else{ ?>
						<th style="width:16%;">Area</th>
					<?php	
					}
					?>					
					<?php
					if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
						<th style="width:7%;">Target</th>
					<?php	
					}
					?>					
					<th style="width:7%;">Accommodation</th>
					<?php
					//if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
						<th style="width:25%;">Address</th>
						<th style="width:8%;">Phone</th>
					<?php	
					//}
					?>					
					<th style="width:50%;">Details</th>
				</tr>
					<?php
					
					foreach($themonth as $calday) {
						
						$acco = '';
						$acc_address = '';
						$acc_phone = '';
					    
                        //Section for spiting out the date
												
						if($calday[weekend] == 1):							
							echo '<tr class="weekend date">';
							echo '<td class="weekend date">';
						else:
							echo '<tr class="date">'; 
							echo '<td class="date">';
						endif;
						
						$thedate = date("d-m-Y", strtotime($calday[date]));
						
						echo "{$thedate}</div>";
						echo '</td><td>';
                        
                        //Section for spiting out the Calendar Entries for that person
                        $bt = "";
						$d = "";
						$acco = "";
                        foreach($rows as $row) {
							$caldate = strtotime($calday['date']);
							$startdate = strtotime($row['date_start']);
							$finishdate = strtotime($row['date_finish']);
							
							
							if($caldate >= $startdate && $caldate <= $finishdate) {
								echo '<a style="padding-left: 5px;" href="add_calendar_entry_static.php?id='. $row[calendar_id] .'">'. $row[region] .'</a>';
								
								// get booking target and details
								$c_sql = mysql_query("
									SELECT *
									FROM `calendar` AS c
									LEFT JOIN `accomodation` AS a ON c.`accomodation_id` = a.`accomodation_id`
									WHERE c.`calendar_id` ={$row[calendar_id]}
								");
								$c = mysql_fetch_array($c_sql);
								
								$bt = ($c['booking_target']!=0)?$c['booking_target']:'';
								$d = $c['details'];
								if($c['accomodation']==1){
									$alink = ( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" )?'/accomodation.php':'javascript:void(0);';
									$acco = '<a href="'.$alink.'">'.$c['name'].'</a>';									
								}
								$acc_address = $c['address'];
								$acc_phone = $c['phone'];
								
                            }
                        }
                        echo '</td>';
						
						
						
						
						?>
						
						<?php
						if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
							<td><?php echo $bt; ?></td>
						<?php	
						}
						?>	
						
						
						<td><?php echo $acco; ?></td>
						<?php
						//if( $_SESSION['USER_DETAILS']['ClassName'] != "TECHNICIAN" ){ ?>
							<td><?php echo $acc_address; ?></td>
							<td><?php echo $acc_phone; ?></td>
						<?php	
						//}
						?>						
						<td><?php echo $d; ?></td>
						
						<?
						
						echo '</tr>';
					}
					?>
					
				
				
			</table>
		</div>

  </div>
  
</div>

<br class="clearfloat" />

</body>
</html>
