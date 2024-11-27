<?

$title = "Add Calendar Entry";
$onload = 1;
$onload_txt = "zxcSelectSort('agency',1)";

if(isset($_POST['id'])){
	$calendar_id = addslashes($_POST['id']);
}

$popup = stristr($_SERVER['HTTP_REFERER'], "popup") ? true : false;

if($popup)
{
    $bodyclass = "popup";
}

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

?>

  <div id="mainContent">
  
  
  <div class="sats-breadcrumb">
      <ul>
        <li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
		<li class="other first">
			<a title="Add/Edit Calendar Entry" href="/add_calendar_entry_static.php"><strong>Add/Edit Calendar Entry</strong></a>			
		</li>
      </ul>
    </div>
	<div id="time"><?php echo date("l jS F Y"); ?></div>
  
  
<?php

$staff_id = is_array($_POST['staff_id']) ? $_POST['staff_id'] : addslashes($_POST['staff_id']);
$orig_start_date = addslashes($_POST['start_date']);
$orig_finish_date = addslashes($_POST['finish_date']);
$region = addslashes($_POST['region']);
//$booking_target = addslashes($_POST['booking_target']);
$details = addslashes($_POST['details']);
$accomodation = addslashes($_POST['accomodation']);
$accomodation_id = addslashes($_POST['accomodation_id']);
$marked_as_leave = addslashes($_POST['marked_as_leave']);
$booking_staff = mysql_real_escape_string($_POST['booking_staff']);


	//Pre-Process the dates for table insertion
	$start_date_temp = str_replace('/', "-", $orig_start_date);
	$start_date = date('Y-m-d', strtotime($start_date_temp));
	$start_time = date('H:i', strtotime($start_date_temp));
	
	$finish_date_temp = str_replace('/', "-", $orig_finish_date);
	$finish_date = date('Y-m-d', strtotime($finish_date_temp));
	$finish_time = date('H:i', strtotime($finish_date_temp));
	$success_text = '';

	if($calendar_id) 
    {
		$staff_id = mysql_real_escape_string($_POST['staff_id']);
		//$booking_target = mysql_real_escape_string($_POST['booking_target']);
		
		
		$accomodation_str = ($accomodation!="")?", `accomodation`='{$accomodation}'":', `accomodation` = NULL';
		$accomodation_id_str = ($accomodation==1 || $accomodation==2)?", `accomodation_id`='{$accomodation_id}'":'';
		
		$marked_as_leave = ($marked_as_leave==1)?1:0;
	
    	//insert into database IF IS AN UPDATE
    	$insetQuery = "UPDATE calendar SET 
    					staff_id=". $staff_id .",
    					date_start='". $start_date ."',
    					date_finish='". $finish_date ."',
						`date_start_time` = '".$start_time."',
						`date_finish_time` = '".$finish_time."',
    					region='". $region ."',
						`booking_staff` = '{$booking_staff}',
						`marked_as_leave` = {$marked_as_leave},
    					details='". $details ."'
						{$accomodation_str}
						{$accomodation_id_str}
    					WHERE calendar_id=". $calendar_id .";";

        if ((@ mysql_query ($insetQuery, $connection)) )
        {
            $success = true;
			$success_text = 'Calendar Updated';
        }
        else
        {
           $success = false;
        }
		
    }



    else 
    {
	  	//insert into database IF NOT AN UPDATE (no calendar_id exisists)
		$accomodation_str = ($accomodation!="")?", {$accomodation}":", NULL";
		$accomodation_id_str = ($accomodation==1)?", {$accomodation_id}":', NULL';
		$marked_as_leave = ($marked_as_leave==1)?1:0;
      
         if(is_array($staff_id))
         {
            foreach($staff_id as $staff)
            {
                $insertQuery = "INSERT INTO calendar (staff_id,date_start,date_finish,region,`marked_as_leave`, `booking_staff`, details,`accomodation`,`accomodation_id`, `country_id`, `date_start_time`, `date_finish_time` ) VALUES
                            (" .
                            "\"" . $staff . "\", ".
                            "\"" . $start_date . "\", ".
                            "\"" . $finish_date . "\", ".
                            "\"" . $region . "\", ".
							"\"" . $marked_as_leave . "\", ".
							"\"" . $booking_staff . "\", ".
                            "\"" . $details . "\"
							{$accomodation_str}
							{$accomodation_id_str},
							{$_SESSION['country_default']},
							'".$start_time."',
							'".$finish_time."'
							);";

                if ((@ mysql_query ($insertQuery, $connection)) )
                {
                    $success = true;
					$success_text = 'Calendar Entry Successful';
					
					$send_ical = $_POST['send_ical'];
					$cal_start_date = date("Y-m-d H:i:s",strtotime(str_replace("/","-",$orig_start_date)));
					$cal_finish_date = date("Y-m-d H:i:s",strtotime(str_replace("/","-",$orig_finish_date)));
					
					// if send i calendar is ticked
					if($send_ical==1){
						
						// get Staff Name and Email
						$subject = 'iCalendar';
						$sa_sql = mysql_query("
							SELECT *
							FROM `staff_accounts`
							WHERE `StaffID` = {$staff}
						");
						$sa = mysql_fetch_array($sa_sql);
						$to_name = "{$sa['FirstName']} {$sa['LastName']}";
						$to_email = $sa['Email'];
						//$to_email = 'vaultdweller123@gmail.com';
						
						send_ical_to_mail($subject, $to_name, $to_email, $region, $details, $cal_start_date, $cal_finish_date );
					
					}
                }
                else
                {
                   $success = false;
                }
				
            }
         }
		 
		// 
		
		
		 
    }

    if($popup)
    {
        echo "<script type='text/javascript'> " . "\n";

            if($success)
            {
                // if($calendar_id)
                // {
                //     echo " alert('Event updated successfully');";
                // }
                // else
                // {
                //     echo " alert('Event added successfully');";
                // }
                
            }
            else
            {
                echo " alert('There was a technical error, please try again');";
            }

            echo "opener.location.reload(true);" . "\n";
            echo "self.close();";

        echo "</script>";
    }
    else
    {
        if($success)
        {
            echo "<div class='success'>
				{$success_text} <br />
				<a href='/add_calendar_entry_static.php'>Add Calendar Entry</a> | <a href='/view_tech_calendar.php'>View Staff Calendar</a> | <a href='/view_individual_staff_calendar.php'>My Calendar</a>
				</div>";
        }
        else
        {
            echo "<h3>A fatal error occurred</h3><br>" . $insertQuery;   
        }
    }

?>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<!-- end #container --></div>
</body>
</html>
