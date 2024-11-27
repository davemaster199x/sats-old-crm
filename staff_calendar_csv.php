<?php

    // GRAB THE DATES FROM THE URL
    if(isset($_REQUEST['month'])){ $month = $_REQUEST['month']; }
    if(isset($_REQUEST['year'])){ $year = $_REQUEST['year']; }
    // IF THE DATES DONT EXISIT IN URL THEN USE CURRENT
    if(!isset($_REQUEST['month'])) {
        $month = date('m');
    }
    if(!isset($_REQUEST['year'])) {
        $year = date('Y');
    }

	$random_str = rand().'-'.date('YmdHis');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=StaffCalendar-'.$random_str.'-'.$month.'-'.$year.'.csv');
    
    include('inc/init.php');
	
	$crm = new Sats_Crm_Class();

	
    
    //the tables rely on this to form.
    $monthname = mktime(0, 0, 0, $month, 1, $year);
    $monthname = date("F", $monthname);
    
    //get the number of days in the month
    $calendardays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    

    $countday = 0;
    $themonth = array();
	
	
	if( $_REQUEST['payroll_export'] == 1 ){
		
		$payroll_from = $crm->formatDate($_REQUEST['payroll_from']);
		$payroll_to = $crm->formatDate($_REQUEST['payroll_to']);
		$start_date_loop = $payroll_from;
		

		
		// new
		while( $start_date_loop <= $payroll_to  ){
			$current_date = date('Y-m-d',strtotime($start_date_loop));
			$start_date_loop = date('Y-m-d',strtotime("{$start_date_loop} + 1 day"));
			$themonth[$countday]['date'] = $current_date; // this is from old code, this is how they store it so follow
			$countday++;
		}
		
	}else{
		
		
		while($countday < $calendardays) {
					
			$thedate = $countday + 1;
			$whiledate = $year.'-'.$month.'-'.$thedate;
			
			$themonth[$countday]['date'] = $whiledate;
						
			$countday = $countday + 1;
		}			
		
		
	}

	
	
	

	
	
    
		
	
		$date_str = '"Last Name","First Name",Position';
	  foreach($themonth as $theday){
		$thedate = date("d/m/Y", strtotime($theday['date']));
        $date_str .= ',"'.$thedate.'"';
	  }
	  
	
	  
	  echo $date_str;
	  echo "\n";
	  
	  
		$tech_sql = mysql_query("
			SELECT sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`sa_position` 
			FROM `staff_accounts` AS sa
			LEFT JOIN `staff_classes` AS sc ON sa.`ClassID` = sc.`ClassID`
			LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
			WHERE sa.`Deleted` = 0 
			AND sa.`active` = 1 
			AND ca.`country_id` = {$_SESSION['country_default']}
			ORDER BY sa.`LastName` ASC, sa.`FirstName` ASC
		");
	  
	  while($tech = mysql_fetch_array($tech_sql)){
		  
		$cal_fil_sql = mysql_query("
			SELECT *
			FROM cal_filters 
			WHERE StaffId = {$_SESSION['USER_DETAILS']['StaffID']}
			LIMIT 1
		");
		$cal_fil = mysql_fetch_array($cal_fil_sql);
		$staff_filter = explode(",", $cal_fil['StaffFilter']);
		
		if(!in_array($tech['StaffID'], $staff_filter)){

		$staff_name = "{$tech['LastName']}, {$tech['FirstName']}";
		$position = ucfirst($tech['sa_position']);
	  
		echo "\"{$tech['LastName']}\",\"{$tech['FirstName']}\",\"{$position}\"";
		
		
	  
		foreach($themonth as $theday){
				//echo ",Day: {$theday['date']}  Tech: {$tech['StaffID']}";
				
				
				
				// if weekend
				$weekDay = date('w', strtotime($theday['date']));
				$isWeekend = ($weekDay == 0 || $weekDay == 6)?1:0;
				$jday = date("D",strtotime($theday['date']));
				
				// get staff working days
				$sa_sql = mysql_query("
					SELECT `working_days` 
					FROM  `staff_accounts` 
					WHERE  `StaffID` ={$tech['StaffID']}
				");
				$sa = mysql_fetch_array($sa_sql);
				$wd = $sa['working_days'];
				
				// if not working day
				if( strchr($wd,$jday)==false && $isWeekend==0 ){
					echo ",OFF";
				}else{
					
					$sql = mysql_query("
						SELECT c.`calendar_id`, c.`staff_id`, c.`region`, c.`date_start`, c.`date_finish`, s.`FirstName`, s.`LastName`
						FROM `calendar` AS c 
						INNER JOIN `staff_accounts` AS s ON (s.`StaffID` = c.`staff_id`) 
						WHERE s.`Deleted` = 0 
						AND s.`active` = 1 
						AND c.`staff_id` ={$tech['StaffID']}
						AND '{$theday['date']}' BETWEEN c.`date_start` AND c.`date_finish`
					");
					
					if(mysql_num_rows($sql)>0){
					
						$row = mysql_fetch_array($sql);
						
						echo ',"'.$row['region'].'"';
						
					}else{
						echo ",";
					}
					
				}
				
			
				
			
		}	

		echo "\n";
		
		}
	  
	  }
	  
	

?>
