<?

$title = "Mark Letter Sent";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');


$job_ids = $_GET['job_ids'];

//$arr = getHomeTotals(); 

?>

  <div id="mainContent">

<?php


	
	// added
	// get all jobs that have status 'Send Letters', so we can get the property to be notified
	
	$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
	$j_sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Send Letters'
		AND p.`deleted` =0
		AND j.`id` IN({$job_ids})
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	if(mysql_num_rows($j_sql)>0){	
		while($j = mysql_fetch_array($j_sql)){
			$property_id = $j['property_id'];
			mysql_query("
			INSERT INTO 
				`property_event_log` (
				 `property_id`, 
				 `staff_id`, 
				 `event_type`, 
				 `event_details`, 
				 `log_date`
				) 
				VALUES (
				 ".$property_id.",
				 ".$staff_id.",
				 'No Tenant Letter Sent',
				 'No Tenant Details Available on ".date('d-m-Y')."',
				 '".date('Y-m-d H:i:s')."'
				)
			");
		}	
	}
	
	
	// send email script
	$dstnct_agency_sql = mysql_query("
		SELECT p.`agency_id` , a.`agency_emails`, a.`agency_name`, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`, j.`id`, p.`property_id`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`status` = 'Send Letters'
		AND p.`deleted` =0
		AND j.`id` IN({$job_ids})
		AND a.`country_id` = {$_SESSION['country_default']}
	");
	
	
	// get country
	$country_id = $_SESSION['country_default'];
	$cntry_sql = getCountryViaCountryId($country_id);
	$cntry = mysql_fetch_array($cntry_sql);
	
	// loop through sent letter jobs and sent it's agency sent letter emails
	if(mysql_num_rows($dstnct_agency_sql)>0){	
		while($dstnct_agency = mysql_fetch_array($dstnct_agency_sql)){
			unset($jemail);
			$jemail = array();
			$temp = explode("\n",trim($dstnct_agency['agency_emails']));
			foreach($temp as $val){
				
				$val2 = preg_replace('/\s+/', '', $val);
				if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
					$jemail[] = $val2;
				}
				
			}
			
			/*
			echo $to = implode(",",$jemail);
			echo "<br />";
			*/
			
			// send email
			$to = implode(",",$jemail);
			
			$agency_name = $dstnct_agency['agency_name'];
			$prop_address = "{$dstnct_agency['address_1']} {$dstnct_agency['address_2']} {$dstnct_agency['address_3']} {$dstnct_agency['state']} {$dstnct_agency['postcode']}";
			
			// subject
			$subject = "Ready for Booking {$dstnct_agency['address_1']} {$dstnct_agency['address_2']} {$dstnct_agency['address_3']}";

			$template = file_get_contents(EMAIL_TEMPLATE);

			#Set template title
			$template = str_replace("#title", "Letter Sent", $template);
			// replace trading name
			$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
			
			$html_content = "<p>Dear {$agency_name},</p>
		   <p>{$prop_address} is now in our system ready for booking.</p>
		   <p>Any questions please feel free to contact us on {$cntry['agent_number']}</p>
		   <br/>
		   ";

			# Populate Template
			$template = str_replace("#content", $html_content, $template);

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			//echo $template;

			//echo $template;
			
			// Mail it
			// Additional headers
			$headers .= "To: {$to}" . "\r\n";
			$headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";
			mail($to, $subject, $template, $headers);
			
			// update
			mysql_query("
				UPDATE property
				SET `tenant_ltr_sent` = '".date("Y-m-d")."'
				WHERE `property_id` = {$dstnct_agency['property_id']}
			");
			
			mysql_query("
				UPDATE jobs
				SET `status` = 'To Be Booked'
				WHERE `status` = 'Send Letters'
				AND `id` = {$dstnct_agency['id']}
			");
			
		}
	}
	
	

	

   
   // (2) Run the query 
	/*
    $todaydate = date("Y", time())."-".date("m",time())."-".date("d", time());

	// Set the Letter sent date
    $Query = "UPDATE jobs j, property p SET p.tenant_ltr_sent='$todaydate' WHERE (j.property_id = p.property_id AND j.status='Send Letters');";
	mysql_query ($Query, $connection);

    $Query = "UPDATE jobs SET status='To Be Booked' WHERE (status='Send Letters');";
	mysql_query ($Query, $connection);
	*/
	

	
	

    
	//echo "All <b>Send Letter</b> Jobs have been moved to <b>To Be Booked</b>\n";
	
	//echo '<div  class="success">All jobs changed to <a href="/view_jobs.php?status=tobebooked&agency=Any">To Be Booked</a></div>';
	
	echo "<script>window.location='/send_letter_jobs.php'</script>";
	
?>

    <p>
      <!-- end #mainContent -->
    </p>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats --><br class="clearfloat" />

<!-- end #container --></div>
</body>
</html>
