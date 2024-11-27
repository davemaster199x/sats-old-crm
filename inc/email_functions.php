<?

# Sats Email Functions
// validate email
function trimEmail($item)
{
	if(is_array($item))
	{
		foreach($item as $key=>$value)
		{
			if(filter_var($item[$key], FILTER_VALIDATE_EMAIL)){
				$item[$key] = trimEmail($item[$key]);
			}			
		} 
	}
	else
	{
		$item = trim($item);
	}
	
	return $item;
}

function batchSendInvoicesCertificates($country_id='')
{
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	global $user;
	$sent_count = 0;
	
	// improved query
	// pls update ajax_do_invoice.php query too when u edit this query
	$sql_str = "SELECT j.id, j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y') AS job_date,
		DATE_FORMAT(j.date, '%d/%m/%Y') AS date,
		j.job_price, j.price_used, 
		j.status, p.address_1, p.address_2, p.address_3, 
		p.state, p.postcode, j.id, p.property_id,
		a.agency_id, a.send_emails, a.account_emails, a.send_combined_invoice,
		DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
		j.ss_location,
		j.ss_quantity,
		sa.FirstName, 
		sa.LastName,
		j.work_order,
		p.`landlord_email`,
		p.`property_managers_id`,
		a.`allow_indiv_pm_email_cc`,
		a.`invoice_pm_only`,
		j.`invoice_amount`,
		p.`pm_id_new`,
		a.`franchise_groups_id`,
		a.`agency_name`,
		p.`landlord_firstname`,
		p.`landlord_lastname`
		FROM jobs AS j
		LEFT JOIN property AS p ON j.property_id = p.property_id
		LEFT JOIN agency AS a ON p.agency_id = a.agency_id
		LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID   
		WHERE j.status = 'Merged Certificates'		
		AND a.account_emails LIKE '%@%'
		AND j.client_emailed IS NULL
		AND a.`country_id` = {$country_id}
		AND p.`deleted` =0
		AND a.`status` = 'active'
		AND j.`del_job` = 0
		";
	
	
	#Get jobs to send
	$query = mysql_query($sql_str);
		
		
	//$jobs_to_email = mysqlMultiRows($query); 


	
		#Send emails
		while($job=mysql_fetch_array($query))
		{
			unset($jemail);
			$jemail = array();
			
			
			// check if agency has maintenance program
			$jemail = processMergedSendToEmails($job['agency_id'],$job['account_emails'],$job);
			
			/*
			echo "<pre>";
			print_r($jemail);
			echo "</pre>";
			*/
			
			
			if(sendInvoiceCertEmail($job, $jemail,$country_id)){
				$sent_count++;
			}
			
		 
		}
	
	
	
	return $sent_count;	
		
}


function sendPendingEmail($client, $emails, $country_id, $email_cc)
{
	
	// country id
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	# Needs to be in array format.
	if(!is_array($emails)) $emails = array($emails);
	
	#Get base template
	$template = getBaseEmailTemplate();	
	
	if($template != FALSE)
	{
	
		// get pending jobs
		$pending_sql_str = "
		SELECT 
				j.`id`,
				j.`start_date`,
				
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`pm_id_new`
			FROM jobs AS j
			LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN agency AS a ON p.agency_id = a.agency_id
			WHERE j.`status` = 'Pending'
			AND a.status = 'active'			
			AND a.agency_emails LIKE '%@%'
			AND p.deleted = '0'
			AND j.`del_job` = 0
			AND a.`agency_id` = {$client['agency_id']}
			AND a.`country_id` = {$country_id}
		";
		$pending_sql = mysql_query($pending_sql_str);
		
		$j_count = mysql_num_rows($pending_sql);
		
		$prop_string = ($j_count>=2)?'Properties':'Property';
		
		#Set template title
		$template = str_replace("#title", "SATS - " . $j_count . " {$prop_string} Due for Service", $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		$login_link = 'https://agency.'.CURRENT_DOMAIN;
		
		$html_content  = "<p>Dear Agent,</p>\n";
		
		$html_content .= "<p>Please <a href='{$login_link}'>log in</a> to our Agency Portal and go to the 'Service Due' page to view properties that are now due for service.</p>"; 

		$html_content .= "<p>If the tenant details are correct and you still manage the property then mark the check box and 'click' CREATE JOB.</p>";
		
		$html_content .= "<p>If you no longer manage the property then mark the check box and 'click' NO LONGER MANAGE. These changes can be done in multiples.</p>"; 	
		
		if( $client['auto_renew'] == 1 ){
			$html_content .= "<p style='color:red;'>Any properties that are still in 'Service Due' by the 1st of the month, will automatically be renewed to fulfil our obligations to your Landlords.</p>"; 	
		}else{
			$html_content .= "You are currently set up to not auto-renew the properties so SATS will not attend the property until instructed to by your agency. SATS will not take any responsibility in fulfilling our obligations to your Landlord because you have asked us to not attend unless instructed.";
		}
		
		$html_content .= "<p>To view our Step by Step video on how to process properties that are due for service please click <a href='https://youtu.be/RlMSzUKL_wQ'>HERE</a>.</p>";
		
		$html_content .= '<p>If you need any help or have questions, please contact our office on '.$cntry['agent_number'].' and speak with one of our friendly staff members.</p>';



		$html_content .= "<br /><br />";
		// listing
		$html_content .= "
		<table width=590>
		<tr>
			<th>Month Due</th>
			<th width=320>Address</th>
			<th>Property Manager</th>
		</tr>";

		// get pending jobs
		$rowcount = 0;
		while( $pending_row = mysql_fetch_array($pending_sql) ){

			// get property managers
			$pm_id = $pending_row['pm_id_new'];
			if( $pm_id > 0 ){

				$pm_sql = mysql_query("
					SELECT `fname`, `lname`
					FROM `agency_user_accounts`
					WHERE `agency_user_account_id` = {$pm_id}
				");
				$pm_row = mysql_fetch_array($pm_sql);
				$pm_name = "{$pm_row['fname']} {$pm_row['lname']}"; 

			}

			$rowcount++;

			$html_content .= "
			<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . ">
				<td>".date('F',strtotime($pending_row['start_date']))."</td>
				<td width=320>{$pending_row['p_address_1']} {$pending_row['p_address_2']}, {$pending_row['p_address_3']}</td>
				<td>{$pm_name}</td>
			</tr>			
			";
		}
		$html_content .= "</table>";
		$html_content .= "<br /><br />";



		$html_content .= "<p>Kind Regards<br />";
		$html_content .= "Smoke Alarm Testing Services.</p>";		
		
		//echo $html_content;
		
		$text_content  = "Dear Agent,\n\n";
		
		$text_content .= "Please <a href='{$login_link}'>log in</a> to our Agency Portal and go to the 'Service Due' page to view properties that are now due for service. \n"; 

		$text_content .= "If the tenant details are correct and you still manage the property then mark the check box and 'click' CREATE JOB. \n";
		
		$text_content .= "If you no longer manage the property then mark the check box and 'click' NO LONGER MANAGE. These changes can be done in multiples. \n"; 	
		
		$text_content .= "Any properties that are still in 'Service Due' by the 1st of the month, will automatically be renewed to fulfil our obligations to your Landlords. \n"; 	
		
		$text_content .= 'If you need any help or have questions, please contact our office on '.$cntry['agent_number'].' and speak with one of our friendly staff members. \n\n';
		
		$text_content .= "Kind Regards\n";
		$text_content .= "Smoke Alarm Testing Services";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);

		
		
		# Test only
		echo $template;
		# return true;
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject('SATS - ' . $j_count . ' '.$prop_string.' Due for Service')
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  ->setBCC(CC_EMAIL)
		  
		  ->setCc($email_cc)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 $result = $mailer->send($email);
		 
		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	
		 	return false;
		 }
	 }
	else
	{
		return false;
	}
}










function sendkeyAccessEmail($data, $date, $country_id, $email_cc)
{
	
	// country id
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	//$spec_agency = array(1448);
	$spec_agency = array(1328);
	$agency_id = $data['agency_id'];
	
	# Needs to be in array format.
	if(!is_array($data['agency_emails'])) $data['agency_emails'] = array($data['agency_emails']);
	
	#print_r($data['agency_emails']);

	#Get base template
	$template = getBaseEmailTemplate();
	
	
	if($template != FALSE)
	{
		
		#Set template title
		$template = str_replace("#title", "Keys to be collected for ".date("l",strtotime($data['jdate']))." " . date("d/m/Y",strtotime($data['jdate'])), $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		//$html_content  = "<p>" . $data['agency_name'] . "</p>";

		$html_content = "<p>Dear {$data['agency_name']},</p>\n";
		$html_content .= "
		<p>Please find the list of properties that require key access for ".date("l",strtotime($data['jdate']))." ".date("d/m/Y",strtotime($data['jdate'])).".</p>
		<p>Should there be any problems please contact us on {$cntry['agent_number']} or {$cntry['outgoing_email']}</p>";
		
		//$html_content .= "Smoke Alarm Testing Services.</p>";
		
		$text_content  = "Dear {$data['agency_name']},\n\n";
		$text_content .= "
		Please find the list of properties that require key access for ".date("l",strtotime($data['jdate']))." ".date("d/m/Y",strtotime($data['jdate']))." \n.
		Should there be any problems please contact us on {$cntry['agent_number']} or {$cntry['outgoing_email']}
		\n\n";
		
		
		# fill out Jobs
		$html_content .= "<h2 style='text-align: center; font-size: 16px; color:red;'>Keys to be collected for ".date("l",strtotime($data['jdate']))." " . date("d/m/Y",strtotime($data['jdate'])) . "</h2>\n";
		$html_content .= "<table width=800>";
		$html_content .= "<tr>";
		$html_content .= "
			<th width=120>Job Date</th>
			<th>Address</th>
			<th>Key Number</th>
			<th>Authorised by</th>
			<th>Technician</th>
			<th>Property Manager</th>";
		$html_content .= "</tr>";

		$text_content .= "Keys to be collected for ".date("l",strtotime($data['jdate']))." " . date("d/m/Y",strtotime($data['jdate'])) . "\n\n";

		$rowcount = 0;

		// tenants email array
		unset($email_cc);
		$email_cc = array();
		$email_cc[] = KEYS_EMAIL;

		foreach($data['properties'] as $property)
		{
			$rowcount++;
			
			
			// get technician
			$tech_sql = mysql_query("
				SELECT *
				FROM `staff_accounts`
				WHERE `StaffID` = {$property['jtech_id']}
			");
			$tech = mysql_fetch_array($tech_sql);
			// add email for CC
			if(in_array($tech['Email'],$email_cc)==false){
				if(filter_var($tech['Email'], FILTER_VALIDATE_EMAIL)){
					//$email_cc[] = $tech['Email'];
				}				
			}	
			
			

			if( in_array($agency_id, $spec_agency) ){
				$key_number = str_repeat("*", strlen($property['key_number']));
			}else{
				$key_number = $property['key_number'];
			}

			// get Property Manager	
			$pm_id = $property['pm_id_new'];
			if( $pm_id > 0 ){

				$pm_sql = mysql_query("
					SELECT `fname`, `lname`
					FROM `agency_user_accounts`
					WHERE `agency_user_account_id` = {$pm_id}
				");
				$pm_row = mysql_fetch_array($pm_sql);
				$pm_name = "{$pm_row['fname']} {$pm_row['lname']}"; 

			}
			
			 
				
			$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . "> \n";
			$html_content .= "
				<td width=120>" . date("d/m/Y",strtotime($property['jdate'])) . "</td>
				<td>" .  $property['address_1'] . " " . $property['address_2'] . ", ".$property['address_3']."</td>
				<td>".$key_number."</td>
				<td>" . $property['key_access_details'] . "</td>
				<td>{$tech['FirstName']}</td>
				<td>{$pm_name}</td>";
			$html_content .= "</tr>\n";

			$text_content .= $property['address_1'] . " " . $property['address_2'] . ", " . $property['address_3'] . "\n";
			$text_content .= "Authorisation: " . $property['time_of_day'] . "\n\n";
		}


		$html_content .= "</table>";
		
		$html_content .= "<br /><p style='text-align: center; color:red;'>PLEASE NOTE: This is an automated email and we do not read any replies. If you need to contact us then please contact us on {$cntry['agent_number']} or {$cntry['outgoing_email']}.</p><br />";
		$html_content .= "<p>Kind Regards, </p><br />";
		$text_content .= "Kind Regards, \n\n";
		//$text_content .= "Smoke Alarm Testing Services\n\n";

		# Populate Template
		$template = str_replace("#content", $html_content, $template);
		
		# echo template since process is run manually - SATS want to be able to see output
		echo $template;
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		

		
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject("Keys to be collected for ".date("l",strtotime($data['jdate']))." " . date("d/m/Y",strtotime($data['jdate'])))
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($data['agency_emails'])
		
		  ->setBCC(CC_EMAIL)
		  
		  ->setCc($email_cc)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 
		 $result = $mailer->send($email);
		 
		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	return false;
		 }
		 
		 
	 }
	else
	{
		return false;
	}
}

function sendInvoiceCertEmail_old($job, $emails, $country_id='',$invoice_only)
{
	# Needs to be in array format.
	if(!is_array($emails)) $emails = array($emails);
	
	// invoice and merge pdf head functions
	/*
	echo "email passed<br />";
	print_r($emails);
	echo "<br />";
	*/
	
	#Get base template
	$template = getBaseEmailTemplate();
	
	if( $job['send_quote']==1 ){
		$header_txt = 'SATS Quote';
		$email_txt = 'Quote';
	}else if( $invoice_only == 1 ){
		$header_txt = 'SATS Invoice';
		$email_txt = 'Invoice';
	}else{
		$header_txt = 'SATS Invoice and Property Certificate';
		$email_txt = 'Invoice/Statement of Compliance';
	}
	
	
	if($template != FALSE)
	{
		#Set template title
		$template = str_replace("#title", $header_txt, $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);

		
		$invoice_url = "https://".CURRENT_DOMAIN."/documents/pdf.php?t=i&i=" . $job['id'] . "&m=" . md5('i' . $job['agency_id'] . $job['id']) . (DEV_SITE == 1 ? "&d=1" : "");
		$certificate_url = "https://".CURRENT_DOMAIN."/documents/pdf.php?t=c&i=" . $job['id'] . "&m=" . md5('c' . $job['agency_id'] . $job['id']) . (DEV_SITE == 1 ? "&d=1" : "");
		
		$html_content  = "<p>Dear Agent,</p>\n";
		$html_content .= "<p>Please find the attached {$email_txt} for the below property. Please contact us with any enquiries you may have.</p>";
		$html_content .= "<p><strong>Property Address</strong><br />";
		$html_content .= $job['address_1'] . " " . $job['address_2'] . "<br />" . $job['address_3'] . " " . $job['state'] . " " . $job['postcode'] . "</p>";
		#$html_content .= "<p><strong>Invoice</strong><br /><a href='" . $invoice_url . "'>download</a></p>";
		#$html_content .= "<p><strong>Statement of Compliance</strong><br /><a href='" . $certificate_url . "'>download</a></p>";
		$html_content .= "<p>Kind Regards,<br />SATS Team</p>";
		
		$text_content  = "Dear Agent,\n\n";
		$text_content .= "Please find the attached {$email_txt} for the below property. Please contact us with any enquiries you may have.\n\n";
		$text_content .= "Property Address\n";
		$text_content .= $job['address_1'] . " " . $job['address_2'] . "<br />" . $job['address_3'] . " " . $job['state'] . " " . $job['postcode'] . "\n\n";
		#$text_content .= "Invoice\n" . $invoice_url . "\n\n";
		#$text_content .= "Certificate\n" . $certificate_url . "\n\n";
		$text_content .= "Kind Regards,\nSATS Team";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);
		
		# Now generate the invoice and cert docs as a file to attach on the fly TODO - make this a lot better :'(
		# Prepare data for the invoice scripts
		$job_details = $job;
		$job_id = $job['id'];	
		
		
		// append checkdigit to job id for new invoice number
		$check_digit = getCheckDigit(trim($job_id));
		$bpay_ref_code = "{$job_id}{$check_digit}";	
		
		
		// Compass Housing QLD 
		if( $job['agency_id'] == 6502 && $job['invoice_amount'] > 0 ){
			$subject_txt = "Payment required ".$bpay_ref_code;
		}else{
			$subject_txt = $header_txt.' - ' . $job['address_1'] . " " . $job['address_2'] . " " . $job['address_3'];
		}
		
		
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);	
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject($subject_txt)
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  ->setBCC(CC_EMAIL)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		
		 
		 
		 
		 # reset $pdf item if set
		 if(isset($pdf)) unset($pdf);
			
			# i know this sucks - but yeah
			if(!defined("EXTERNAL_PDF")) {
				define("EXTERNAL_PDF", 1);
		}
		

		
		

		require_once("fpdf/fpdf.php");
		require_once('fpdf_override.php');
		
		$send_quote = $job_details['send_quote'];
		$mm_need_proc_inv = $job_details['mm_need_proc_inv'];
		if( $send_quote == 1 ) {
			
			include('pdfInvoiceCertComb.php');							
		
			include('pdf_quote_template.php');
			$combined_pdf = $pdf->Output('', 'S');	

			// Attach Quote
			$email->attach(Swift_Attachment::newInstance($combined_pdf, 'quote_' . $bpay_ref_code . '.pdf', 'application/pdf'));
		}else if( $invoice_only == 1 ){
			
			include('pdfInvoiceCertComb.php');			

			include('pdf_invoice_template.php');
			$invoice_pdf = $pdf->Output('', 'S');	
			// Attach Invoice Only
			$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'invoice' . $bpay_ref_code . '.pdf', 'application/pdf'));
		
		}else if($job_details['send_combined_invoice']) {
			
			
			include('pdfInvoiceCertComb.php');							
		
			include('pdf_combined_template.php');
			$combined_pdf = $pdf->Output('', 'S');	

			// Attach Combined
			$email->attach(Swift_Attachment::newInstance($combined_pdf, 'invoice_cert_' . $bpay_ref_code . '.pdf', 'application/pdf'));
		}else {

			
			include('pdfInvoiceCertComb.php');			
		
		
			include('pdf_invoice_template.php');
			$invoice_pdf = $pdf->Output('', 'S');		
			if(isset($pdf)) unset($pdf);
			include('pdf_certificate_template.php');
			$cert_pdf = $pdf->Output('', 'S');
			
			
			// Attach invoice and cert
			$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'invoice' . $bpay_ref_code . '.pdf', 'application/pdf'));
			$email->attach(Swift_Attachment::newInstance($cert_pdf, 'cert' . $bpay_ref_code . '.pdf', 'application/pdf'));	
			
		}
		 
		 $result = $mailer->send($email);
		 
		
		 
		 if($result)
		 {
			 
			$sent_to_imp = implode(", ",$emails);
		 
			if( $send_quote == 1 ){
				$job_log_txt = 'Quote';
			}else if( $invoice_only == 1 ){
				$job_log_txt = 'Invoice';
			}else{
				$job_log_txt = 'Invoice/Cert';
			}
			
			// if not CRON, user logged
			if( $_SESSION['USER_DETAILS']['StaffID'] !='' ){
				$append_jlfield = '`staff_id`';
				$append_jlval = $_SESSION['USER_DETAILS']['StaffID'];
			}else{
				$append_jlfield = '`auto_process`';
				$append_jlval = 1;
			}
		 
			// job log
			mysql_query("
				INSERT INTO 
				`job_log` (
					`contact_type`,
					`eventdate`,
					`comments`,
					`job_id`, 
					`eventtime`,
					{$append_jlfield}
				) 
				VALUES (
					'{$job_log_txt} Email',
					'".date('Y-m-d')."',
					'{$job_log_txt} Email Sent to: <strong>{$sent_to_imp}</strong>',
					{$job['id']}, 
					'".date('H:i')."',
					'{$append_jlval}'
				)
			");		
			
			
			if( $send_quote == 1 ){
				$query = "UPDATE jobs SET `qld_upgrade_quote_emailed` = NOW() WHERE id = '" . $job['id'] . "' LIMIT 1";
			}else if( $mm_need_proc_inv == 1 ){
				$query = "UPDATE jobs SET `mm_need_proc_inv_emailed` = NOW() WHERE id = '" . $job['id'] . "' LIMIT 1";
			}else{
				$query = "UPDATE jobs SET client_emailed = NOW() WHERE id = '" . $job['id'] . "' LIMIT 1";
			}
			
		 	
			if(mysql_query($query))
			{
				return true;
			}
			else 
			{
				return false;
			}
			
		
		 }
		 else
		 {
		 	return false;
		 }
		 
		 
	 }
	else
	{
		return false;
	}
}


function sendInvoiceCertEmail($job, $emails, $country_id='',$invoice_only)
{

	$crm = new Sats_Crm_Class;

	$encrypt_decrypt = new Openssl_Encrypt_Decrypt();

	# Needs to be in array format.
	if(!is_array($emails)) $emails = array($emails);
	
	// invoice and merge pdf head functions
	/*
	echo "email passed<br />";
	print_r($emails);
	echo "<br />";
	*/
	
	#Get base template
	$template = getBaseEmailTemplate();

	

	$p_address = $job['address_1'] . " " . $job['address_2'] . " " . $job['address_3'] . " " . $job['state'] . " " . $job['postcode'];

	$encode_encrypt_id = rawurlencode($encrypt_decrypt->encrypt($job['id']));
	
	if( $job['send_quote']==1 ){

		$email_txt = 'Quote';
		
		//old--$pdf_link_str = '<a href="'.URL.'view_quote_new.php?i='.$job['id'].'&m='.md5($job['agency_id'].$job['id']).'">'.$p_address.'</a>';	
		$pdf_link_str = '<a href="'.$crm->crm_ci_redirect(rawurlencode("/pdf/view_quote/?job_id={$encode_encrypt_id}")).'">'.$p_address.'</a>';	
		
	}else if( $invoice_only == 1 ){

		$email_txt = 'Invoice';

		//old>>>>> $pdf_link_str = '<a href="'.URL.'view_invoice_new.php?i='.$job['id'].'&m='.md5($job['agency_id'].$job['id']).'">'.$p_address.'</a>';	
		$pdf_link_str = '<a href="'.$crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_id}")).'">'.$p_address.'</a>';	

	}else if($job['send_combined_invoice']) {
						
		$email_txt = 'Invoice/Statement of Compliance';

		//old>>>> $pdf_link_str = '<a href="'.URL.'view_combined_new.php?i='.$job['id'].'&m='.md5($job['agency_id'].$job['id']).'">'.$p_address.'</a>';	
		$pdf_link_str = '<a href="'.$crm->crm_ci_redirect(rawurlencode("/pdf/view_combined/?job_id={$encode_encrypt_id}")).'">'.$p_address.'</a>';	
		
	}else{

		$email_txt = 'Invoice/Statement of Compliance';

		/* old-----
		$pdf_link_str = '
		<a href="'.URL.'view_invoice_new.php?i='.$job['id'].'&m='.md5($job['agency_id'].$job['id']).'">'.$p_address.'</a><br />
		<a href="'.URL.'view_certificate_new.php?i='.$job['id'].'&m='.md5($job['agency_id'].$job['id']).'">'.$p_address.'</a>
		';*/

		$pdf_link_str = '
		<a href="'.$crm->crm_ci_redirect(rawurlencode("/pdf/view_invoice/?job_id={$encode_encrypt_id}")).'">'.$p_address.'</a><br />
		<a href="'.$crm->crm_ci_redirect(rawurlencode("/pdf/view_certificate/?job_id={$encode_encrypt_id}")).'">'.$p_address.'</a>
		';

	}
	
	
	
	if($template != FALSE)
	{
		#Set template title
		$template = str_replace("#title", $email_txt, $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);		

		// agency name switch, display landlord instead of FG is private
		if( $crm->getAgencyPrivateFranchiseGroups($job['franchise_groups_id']) == true ){

			if( $job['landlord_firstname'] == '' && $job['landlord_lastname'] == ''  ){
				$agency_name_switch = "Landlord";
			}else{
				$agency_name_switch = "{$job['landlord_firstname']} {$job['landlord_lastname']}";
			}
			
		}else{
		    $agency_name_switch = $job['agency_name'];
		}

		
		// html
		$html_content  = "
		<p>
			Dear {$agency_name_switch},
		</p>
		<p>
			A copy of your {$email_txt} is available on the link below:
			<br /><br />
			{$pdf_link_str}
			<br /><br />
			If you have any questions or we can be of further assistance please feel free to contact us on {$cntry['agent_number']} or {$cntry['outgoing_email']}.<br />
		</p>
		<p>
			Regards,<br />
			Smoke Alarm and Testing Services
		</p>
		";

		// txt
		$text_content  = "
		Dear {$agency_name_switch}\n\n

		A copy of your {$email_txt} is available on the link below
		\n\n
		{$pdf_link_str}
		\n\n
		If you have any questions or we can be of further assistance please feel free to contact us on {$cntry['agent_number']} or {$cntry['outgoing_email']}.\n\n
	
		Regards,\n
		Smoke Alarm and Testing Services
		";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);
		
		# Now generate the invoice and cert docs as a file to attach on the fly TODO - make this a lot better :'(
		# Prepare data for the invoice scripts
		$job_details = $job;		
		$job_id = $job['id'];	
		
		
		// append checkdigit to job id for new invoice number
		$check_digit = getCheckDigit(trim($job_id));
		$bpay_ref_code = "{$job_id}{$check_digit}";	
		
		
		// Compass Housing QLD 
		if( $job['agency_id'] == 6502 && $job['invoice_amount'] > 0 ){
			$subject_txt = "Payment required ".$bpay_ref_code;
		}else{
			$subject_txt = 'SATS '.$email_txt.' - ' . $job['address_1'] . " " . $job['address_2'] . " " . $job['address_3'];
		}
		
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);	
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject($subject_txt)
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  //->setBCC(CC_EMAIL) >> //Stopped sending to cc@sats on 10/9/2020 as per Daniel’s instructions
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		
		 
		 
		 
		 # reset $pdf item if set
		 if(isset($pdf)) unset($pdf);
			
			# i know this sucks - but yeah
			if(!defined("EXTERNAL_PDF")) {
				define("EXTERNAL_PDF", 1);
		}
		

		
		

		require_once("fpdf/fpdf.php");
		require_once('fpdf_override.php');
		
		$send_quote = $job_details['send_quote'];
		$mm_need_proc_inv = $job_details['mm_need_proc_inv'];
		if( $send_quote == 1 ) {
			
			include('pdfInvoiceCertComb.php');							
		
			include('pdf_quote_template.php');
			$combined_pdf = $pdf->Output('', 'S');	

			// Attach Quote
			$email->attach(Swift_Attachment::newInstance($combined_pdf, 'quote_' . $bpay_ref_code . '.pdf', 'application/pdf'));
		}else if( $invoice_only == 1 ){
			
			include('pdfInvoiceCertComb.php');			

			include('pdf_invoice_template.php');
			$invoice_pdf = $pdf->Output('', 'S');	
			// Attach Invoice Only
			$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'invoice' . $bpay_ref_code . '.pdf', 'application/pdf'));
		
		}else if($job_details['send_combined_invoice']) {
			
			
			include('pdfInvoiceCertComb.php');							
		
			include('pdf_combined_template.php');
			$combined_pdf = $pdf->Output('', 'S');	

			// Attach Combined
			$email->attach(Swift_Attachment::newInstance($combined_pdf, 'invoice_cert_' . $bpay_ref_code . '.pdf', 'application/pdf'));
		}else {

			
			include('pdfInvoiceCertComb.php');			
		
		
			include('pdf_invoice_template.php');
			$invoice_pdf = $pdf->Output('', 'S');		
			if(isset($pdf)) unset($pdf);
			include('pdf_certificate_template.php');
			$cert_pdf = $pdf->Output('', 'S');
			
			
			// Attach invoice and cert
			$email->attach(Swift_Attachment::newInstance($invoice_pdf, 'invoice' . $bpay_ref_code . '.pdf', 'application/pdf'));
			$email->attach(Swift_Attachment::newInstance($cert_pdf, 'cert' . $bpay_ref_code . '.pdf', 'application/pdf'));	
			
		}
		 
		 $result = $mailer->send($email);
		 
		
		 
		 if($result)
		 {
			 
			$sent_to_imp = implode(", ",$emails);
		 
			if( $send_quote == 1 ){
				$job_log_txt = 'Quote';
			}else if( $invoice_only == 1 ){
				$job_log_txt = 'Invoice';
			}else{
				$job_log_txt = 'Invoice/Cert';
			}
			
			// if not CRON, user logged
			if( $_SESSION['USER_DETAILS']['StaffID'] !='' ){
				$append_jlfield = '`staff_id`';
				$append_jlval = $_SESSION['USER_DETAILS']['StaffID'];
			}else{
				$append_jlfield = '`auto_process`';
				$append_jlval = 1;
			}
		 
			// job log
			mysql_query("
				INSERT INTO 
				`job_log` (
					`contact_type`,
					`eventdate`,
					`comments`,
					`job_id`, 
					`eventtime`,
					{$append_jlfield}
				) 
				VALUES (
					'{$job_log_txt} Email',
					'".date('Y-m-d')."',
					'{$job_log_txt} Email Sent to: <strong>{$sent_to_imp}</strong>',
					{$job['id']}, 
					'".date('H:i')."',
					'{$append_jlval}'
				)
			");		
			
			
			if( $send_quote == 1 ){ // quotes
				
				mysql_query("
				UPDATE jobs 
				SET `qld_upgrade_quote_emailed` = NOW() 			
				WHERE id = {$job_id}
				");

			}else if( $mm_need_proc_inv == 1 ){ // MM precomp				
				
				mysql_query("
				UPDATE jobs 
				SET 
					`mm_need_proc_inv_emailed` = NOW(),
					`client_emailed` = NOW() 
				WHERE id = {$job_id}
				");

			}else{ // default send invoice

				// check if agency has maintenance program
				//$agency_has_mm = check_agency_has_mm($job['agency_id']);

				//if( $agency_has_mm == false ){ // on merge, only mark `client_emailed` if no maintenance manager
					
					mysql_query("
					UPDATE jobs 
					SET `client_emailed` = NOW() 			
					WHERE id = {$job_id}
					");

				//}				

			}

			
			return true;
			
		
		 }
		 else
		 {
		 	return false;
		 }
		 
		 
	 }
	else
	{
		return false;
	}
}



function sendEntryNoticeEmail($job_details, $emails, $en_bcc_emails=[])
{
	# Needs to be in array format.
	//if(!is_array($emails)) $emails = array($emails);

	// if TO email is empty move all BCC email to it, bec this email system will error if TO: is empty
	if( count($emails) == 0 ){		
		$emails = $en_bcc_emails;
		$en_bcc_emails = []; // clear BCC
		$en_bcc_emails[] = CC_EMAIL;
	}else{		
		$en_bcc_emails[] = CC_EMAIL;
	}

	#Get base template
	$template = getBaseEmailTemplate();
	
	if($template != FALSE)
	{
		#Set template title
		$template = str_replace("#title", COMPANY_ABBREV_NAME . " Entry Notice - " . $job['address_1'] . " " . $job['address_2'] . " " . $job['address_3'], $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId();
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		$tenants_names_arr = [];
		
		// new tenants switch
		//$new_tenants = 0;
		$new_tenants = NEW_TENANTS;

		if( $new_tenants == 1 ){ // NEW TENANTS

			$pt_params = array( 
				'property_id' => $job_details['property_id'],
				'active' => 1
			 );
			$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
			
			while( $pt_row = mysql_fetch_array($pt_sql) ){
				
				$tenants_names_arr[] = $pt_row['tenant_firstname'];
				
			}

		}else{ // OLD TENANTS
		
			$num_tenants = getCurrentMaxTenants();
			for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
				if( $job_details['tenant_email'.$i]!='' && $job_details['tenant_firstname'.$i]!="" ){
					$tenants_names_arr[] = $job_details['tenant_firstname'.$i];
				}
			}
			
		}
		
		
		
		
		$dear_txt = dynamicDearEmailFormat($tenants_names_arr);
		$prop_address = $job_details['address_1'] . " " . $job_details['address_2'] . "<br />" . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode'];
		
		if( $job_details['job_type'] == "IC Upgrade" && $_SESSION['country_default']==1 ){

			$html_content  = "<p>Dear {$dear_txt},</p>\n";
			$html_content .= "<p>Please find the attached entry notice for ".$job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode']." on {$job_details['date']}. We will collect the keys from {$job_details['agency_name']} to complete the service. Please ensure all bedrooms are accessible as we will be installing alarms into them as part of our Queensland upgrade service.</p>";
			$html_content .= "<p>If you would like to be in attendance or require a change to the date or time, please contact our office on {$cntry['tenant_number']}.</p>";
			$html_content .= "<p><strong>Property Address</strong><br />";
			$html_content .= "{$prop_address}</p>";
			$html_content .= "<p>Kind Regards,<br />SATS Team</p>";
			
			$text_content  = "Dear  {$dear_txt},\n\n";
			$text_content .= "Please find the attached entry notice for ".$job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode']." on {$job_details['date']}. We will collect the keys from {$job_details['agency_name']} to complete the service. Please ensure all bedrooms are accessible as we will be installing alarms into them as part of our Queensland upgrade service.\n\n";
			$text_content .= "If you would like to be in attendance or require a change to the date or time, please contact our office on {$cntry['tenant_number']}.";
			$text_content .= "Property Address\n";
			$text_content .= "{$prop_address}\n\n";
			$text_content .= "Kind Regards,\nSATS Team";

		}else{

			$html_content  = "<p>Dear {$dear_txt},</p>\n";
			$html_content .= "<p>Please find the attached entry notice for ".$job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode']." on {$job_details['date']}. We will collect the keys from {$job_details['agency_name']} to complete the service. Please contact us with any enquiries you may have.</p>";			
			$html_content .= "<p><strong>Property Address</strong><br />";
			$html_content .= "{$prop_address}</p>";
			$html_content .= "<p>Kind Regards,<br />SATS Team</p>";
			
			$text_content  = "Dear  {$dear_txt},\n\n";
			$text_content .= "Please find the attached entry notice for ".$job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode']." on {$job_details['date']}. We will collect the keys from {$job_details['agency_name']} to complete the service. Please contact us with any enquiries you may have.\n\n";			
			$text_content .= "Property Address\n";
			$text_content .= "{$prop_address}\n\n";
			$text_content .= "Kind Regards,\nSATS Team";
			
		}

		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);
		
		//echo $template;

		# Now generate the appropriate letter to attach
		$job_id = $job_details['id'];
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject(COMPANY_ABBREV_NAME . " Entry Notice - " . $job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'])
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => COMPANY_FULL_NAME))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  ->setBCC($en_bcc_emails)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 
		 # reset $pdf item if set
		 if(isset($pdf)) unset($pdf);
			
		# i know this sucks - but yeah
		if(!defined("EXTERNAL_PDF")) {
			define("EXTERNAL_PDF", 1);
		}
		
		require_once("fpdf/fpdf.php");
		require_once('fpdi-1.4.4/fpdi.php');

		// Is it a QLD job or Non QLD? QLD sends the Form9 PDF and Non QLD 	

		if( CURRENT_COUNTRY == 2){ //NZ EN PDF

			include('pdf_entry_notice_generic_nz.php');
			$entry_notice = $pdf->Output('', 'S');

		}else{ //AU EN PDFs

			if($job_details['state'] == "QLD") {
				include('pdf_entry_notice_qld.php');
				$entry_notice = $pdf->Output('', 'S');	
			}else if($job_details['state'] == "SA"){
				include('pdf_entry_notice_sa.php');
				$entry_notice = $pdf->Output('', 'S');
			}else if($job_details['state'] == "NSW"){
				include('inc/pdf_entry_notice_nsw.php');
				$entry_notice = $pdf->Output('', 'S');
			}else if($job_details['state'] == "ACT"){
				include('inc/pdf_entry_notice_act.php');
				$entry_notice = $pdf->Output('', 'S');
			}else{
				include('pdf_entry_notice_generic.php');
				$entry_notice = $pdf->Output('', 'S');
			}

		}
		
		// Attach Combined
		$email->attach(Swift_Attachment::newInstance($entry_notice, 'entry_notice_' .  $job_details['address_1'] . "_" . $job_details['address_2'] . "_" . $job_details['address_3'] .  '.pdf', 'application/pdf'));
		 
		 $result = $mailer->send($email);
		 
		 if($result)
		 {
		 	$query = "UPDATE jobs SET entry_notice_emailed = NOW() WHERE id = '" . $job['id'] . "' LIMIT 1";
			if(mysql_query($query))
			{
				return true;
			}
			else 
			{
				return false;
			}
		 }
		 else
		 {
		 	return false;
		 }
		 
		 
	 }
	else
	{
		return false;
	}
}


function sendEntryNoticeEmailBookedWith($job_details, $emails, $booked_with='')
{
	# Needs to be in array format.
	if(!is_array($emails)) $emails = array($emails);
	
	#Get base template
	$template = getBaseEmailTemplate();
	
	if($template != FALSE)
	{
		#Set template title
		$template = str_replace("#title", COMPANY_ABBREV_NAME . " Entry Notice - " . $job['address_1'] . " " . $job['address_2'] . " " . $job['address_3'], $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId();
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		
		// new tenants switch
		//$new_tenants = 0;
		$new_tenants = NEW_TENANTS;

		if( $new_tenants == 1 ){ // NEW TENANTS

			$pt_params = array( 
				'property_id' => $job_details['property_id'],
				'active' => 1
			 );
			$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

			$tenants_name_arr = [];
			while( $pt_row = mysql_fetch_array($pt_sql) ){
				
				$tenants_name_arr[] =  $pt_row['tenant_firstname'];
				
			}
			
			$tenants_name = implode(', ',$tenants_name_arr);

		}else{ // OLD TENANTS

			$tenants_name = $job_details['tenant_firstname1'] . (isset($job_details['tenant_firstname2']) ? ", " . $job_details['tenant_firstname2'] : "");
			
		}
		
		
		$html_content  = "<p>Dear {$tenants_name}</p>\n";
		$html_content .= "<p>Please find the attached entry notice for the below property on " . $job_details['date']. ". Please contact us with any enquiries you may have.</p>";
		$html_content .= "<p>If you would like to be in attendance or require a change the date or time, please contact our office on {$cntry['tenant_number']}.</p>";
		$html_content .= "<p><strong>Property Address</strong><br />";
		$html_content .= $job_details['address_1'] . " " . $job_details['address_2'] . "<br />" . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode'] . "</p>";
		$html_content .= "<p>Kind Regards,<br />SATS Team</p>";
		
		$text_content  = "Dear " . $booked_with. ",\n\n";
		$text_content .= "Please find the attached notice entry for  the below property on " . $job_details['date']. ". Please contact us with any enquiries you may have..\n\n";
		$text_content .= "Property Address\n";
		$text_content .= $job_details['address_1'] . " " . $job_details['address_2'] . "\n" . $job_details['address_3'] . " " . $job_details['state'] . " " . $job_details['postcode'] . "\n\n";
		$text_content .= "Kind Regards,\nSATS Team";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);

		# Now generate the appropriate letter to attach
		$job_id = $job_details['id'];
		
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject(COMPANY_ABBREV_NAME . " Entry Notice - " . $job_details['address_1'] . " " . $job_details['address_2'] . " " . $job_details['address_3'])
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => COMPANY_FULL_NAME))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  ->setBCC(CC_EMAIL)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 
		 # reset $pdf item if set
		 if(isset($pdf)) unset($pdf);
			
		# i know this sucks - but yeah
		if(!defined("EXTERNAL_PDF")) {
			define("EXTERNAL_PDF", 1);
		}
		
		require_once("fpdf/fpdf.php");
		require_once('fpdi-1.4.4/fpdi.php');

		// SA booked with	
		// send entry notice booked with session (need to pass this to the pdf page)
		$booked_with_name = $booked_with;
		include('inc/pdf_entry_notice_booked_with.php');
		$entry_notice = $pdf->Output('', 'S');	

		// Attach Combined
		$email->attach(Swift_Attachment::newInstance($entry_notice, 'entry_notice_' .  $job_details['address_1'] . "_" . $job_details['address_2'] . "_" . $job_details['address_3'] .  '.pdf', 'application/pdf'));
		 
		 $result = $mailer->send($email);
		 
		 if($result)
		 {
		 	$query = "UPDATE jobs SET entry_notice_emailed = NOW() WHERE id = '" . $job['id'] . "' LIMIT 1";
			if(mysql_query($query))
			{
				return true;
			}
			else 
			{
				return false;
			}
		 }
		 else
		 {
		 	return false;
		 }
	 }
	else
	{
		return false;
	}
}


function sendReportEmail($data_node, $emails, $country_id, $email_cc)
{
	
	// country id
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	# Needs to be in array format.
	if(!is_array($emails)) $emails = array($emails);
	
	#Get base template
	$template = getBaseEmailTemplate();
	
	if($template != FALSE)
	{
		$rowcount = 0;
		
		#Set template title
		$template = str_replace("#title", "SATS - Property Report", $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		$html_content  = "<p><strong>Dear Agent,</strong></p>\n";
		$html_content .= "<p>Please find the report below on jobs that are booked or recently completed.</p>";
		$html_content .= "<p>Please email us if you have any enquiries.</p>";
		
		if(sizeof($data_node[1]) > 0)
		{
			# Completed Jobs
			$html_content .= "<h2>Completed Jobs</h2>\n";
			$html_content .= "<table width=590>\n";
			$html_content .= "<tr>\n";
			$html_content .= "<th width=120>Date</th><th width=320>Address</th><th>Suburb</th>\n";
			$html_content .= "</tr>\n";
			
			foreach($data_node[1] as $property)
			{
				$rowcount++;
				
				$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . "> \n";
				$html_content .= "<td>".date("d/m/Y",strtotime($property['Date']))."</td>\n";
				$html_content .= "<td>{$property['Address']}</td>\n";
				$html_content .= "<td>{$property['Suburb']}</td>\n";
				$html_content .= "<tr>\n";
			}
			
			$html_content .= "</tr>\n";
			$html_content .= "</table>\n";
		}
		
		if(sizeof($data_node[2]) > 0)
		{
			# Completed Jobs
			$html_content .= "<h2>Booked Jobs</h2>\n";
			$html_content .= "<table width=590>\n";
			$html_content .= "<tr>\n";
			$html_content .= "<th width=120>Date</th><th width=320>Address</th><th>Suburb</th>\n";
			$html_content .= "</tr>\n";
			
			foreach($data_node[2] as $property)
			{
				$rowcount++;
				
				$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . "> \n";
				$html_content .= "<td>".date("d/m/Y",strtotime($property['Date']))."</td>\n";
				$html_content .= "<td>{$property['Address']}</td>\n";
				$html_content .= "<td>{$property['Suburb']}</td>\n";
				$html_content .= "<tr>\n";
			}
			
			$html_content .= "</tr>\n";
			$html_content .= "</table>\n";
		}
		
		if(sizeof($data_node[3]) > 0)
		{
			# Completed Jobs
			$html_content .= "<h2>Unable to Complete Jobs</h2>\n";
			$html_content .= "<table width=590>\n";
			$html_content .= "<tr>\n";
			$html_content .= "<th width=120>Date</th><th width=320>Address</th><th>Suburb</th><th>Reason</th>\n";
			$html_content .= "</tr>\n";
			
			foreach($data_node[3] as $property)
			{
				$rowcount++;
				
				$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . "> \n";
				$html_content .= "<td>".date("d/m/Y",strtotime($property['jl_date']))."</td>\n";
				$html_content .= "<td>{$property['Address']}</td>\n";
				$html_content .= "<td>{$property['Suburb']}</td>\n";
				$html_content .= "<td>&nbsp;</td>\n";
				$html_content .= "<tr>\n";
			}
			
			$html_content .= "</tr>\n";
			$html_content .= "</table>\n";
		}

		/*
		if(sizeof($data_node[3]) > 0)
		{
			# Completed Jobs
			$html_content .= "<h2>Problematic Jobs</h2>\n";
			$html_content .= "<table width=590>\n";
			$html_content .= "<tr>\n";
			$html_content .= "<th width=120>Date</th><th width=320>Address</th><th>Suburb</th>\n";
			$html_content .= "</tr>\n";
			
			foreach($data_node[3] as $property)
			{
				$rowcount++;
				
				$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . "> \n";
				$html_content .= "<td>".date("d/m/Y",strtotime($property['jl_date']))."</td>\n";
				$html_content .= "<td>{$property['Address']}</td>\n";
				$html_content .= "<td>{$property['Suburb']}</td>\n";
				$html_content .= "<tr>\n";
			}
			
			$html_content .= "</tr>\n";
			$html_content .= "</table>\n";
		}		
		*/
		
		$html_content .= "<p>&nbsp;<br />Kind Regards,<br />";
		$html_content .= "Smoke Alarm Testing Services.</p>";
		
		
		
		$text_content  = "Dear Agent,\n\n";
		$text_content .= "This text is in HTML format, please enable this to view the report.\n\n";
		$text_content .= "If this is not possible, please contact us at {$cntry['agent_number']} for any enquiries you may have\n\n";
		$text_content .= "Kind Regards\n";
		$text_content .= "Smoke Alarm Testing Services";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);

		# Uncomment to  Test only
		#echo $template;
		#return true;

		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject('SATS - Property Report')
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($emails)
		
		  ->setBCC(CC_EMAIL)
		  
		  ->setCc($email_cc)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 //$result = $mailer->send($email);
		 
		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	
		 	return false;
		 }
	 }
	else
	{
		return false;
	}
}




function sendEscalatePropertiesToAgency($agency_id, $country_id)
{
	
	// country id
	$country_id = ($country_id!="")?$country_id:$_SESSION['country_default'];
	
	// get escalate jobs
	$esc_prop_sql = mysql_query("
		SELECT DISTINCT (
			j.`property_id`
		), 

		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,
		p.`property_managers_id`,
		p.`pm_id_new`, 
		
		pm.`name` AS pm_name,
		
		a.`agency_emails`		
		
		FROM `selected_escalate_job_reasons` AS sejr
		LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
		LEFT JOIN `jobs` AS j ON sejr.`job_id` = j.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
		LEFT JOIN `property_managers` AS pm ON p.`property_managers_id` = pm.`property_managers_id`
		WHERE j.status =  'Escalate'
		AND p.`deleted` =0
		AND a.`status` =  'active'
		AND j.`del_job` =0
		AND a.`country_id` ={$country_id}
		AND ejr.`active` = 1		
		AND (
			sejr.`escalate_job_reasons_id` != 3 AND
			sejr.`escalate_job_reasons_id` != 4 AND
			sejr.`escalate_job_reasons_id` != 5
		)
		AND a.`agency_id` = {$agency_id}
	");
	
	$pcount = mysql_num_rows($esc_prop_sql);
	
	// title
	echo $subj = "SATS – We need your help with {$pcount} ".(($pcount==1)?"Property":"Properties");
	echo "<br />";
	
	#Get base template
	$template = getBaseEmailTemplate();
	
	if($template != FALSE)
	{
		$rowcount = 0;
		
		#Set template title
		$template = str_replace("#title", $subj, $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId($country_id);
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		$html_content  = "<p><strong>Dear Agent,</strong></p>";
		$html_content .= "<p>We need help in being able to access the following properties.</p>";
		$html_content .= "<p>Below is a list of properties that we need help with:</p>";
		
		
		
		
		$html_content .= "<table width=590>";
		$html_content .= "<tr>";
		$html_content .= "
			<th width=320>Address</th>	
			<th>Property Manager</th>
		";
		$html_content .= "</tr>";

			
		// list properties
		while( $esc_prop = mysql_fetch_array($esc_prop_sql) ){

			// get Property Manager	
			$pm_id = $esc_prop['pm_id_new'];
			if( $pm_id > 0 ){

				$pm_sql = mysql_query("
					SELECT `fname`, `lname`
					FROM `agency_user_accounts`
					WHERE `agency_user_account_id` = {$pm_id}
				");
				$pm_row = mysql_fetch_array($pm_sql);
				$pm_name = "{$pm_row['fname']} {$pm_row['lname']}"; 

			}
			
			$rowcount++;
			$prop_add = "{$esc_prop['p_address_1']} {$esc_prop['p_address_2']}, {$esc_prop['p_address_3']}";
			
			$html_content .= "<tr " . ($rowcount % 2 == 0 ? "class='odd'" : "") . ">";			
			$html_content .= "<td>{$prop_add}</td>";
			$html_content .= "<td>{$pm_name}</td>";
			$html_content .= "</tr>";
			
		}

		$html_content .= "</table>";
				
		$html_content .= "
		<p>Can you please <a href='https://agency.".CURRENT_DOMAIN."/'>log in</a> to our Agency Portal and go to the 'Help Needed' page to view these properties and follow the instructions.</p>
		<p>If you need any help or have questions, please contact our office on {$cntry['agent_number']} and speak with one of our friendly staff members.</p>	
		<p>
			Kind Regards<br />
			Smoke Alarm Testing Services.
		</p>
		";
		
		//echo $html_content;
		
		$text_content  = "Dear Agent,\n\n";
		$text_content .= "This text is in HTML format, please enable this to view the report.\n\n";
		$text_content .= "If this is not possible, please contact us at {$cntry['agent_number']} for any enquiries you may have\n\n";
		$text_content .= "Kind Regards\n";
		$text_content .= "Smoke Alarm Testing Services";
		
		# Populate Template
		$template = str_replace("#content", $html_content, $template);
		
		// To 
		// get agency email
		$agen_sql = mysql_query("
			SELECT `agency_emails`			
			FROM `agency` 
			WHERE `country_id` = {$country_id}
			AND `agency_id` = {$agency_id}
		");
		$agen = mysql_fetch_array($agen_sql);
		unset($to_email);
		$to_email = array();
		
		$temp = explode("\n",trim($agen['agency_emails']));
		foreach($temp as $val){
			$val2 = preg_replace('/\s+/', '', $val);
			if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
				$to_email[] = $val2;
			}				
		}
		
		print_r($to_email);
		

		# Uncomment to  Test only
		echo $template;
		#return true;

		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject($subj)
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($to_email)
		
		  ->setBCC(CC_EMAIL)
		  
		  ->setCc($email_cc)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 if($pcount>0){
			  $result = $mailer->send($email);
		 }
		
		 
		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	
		 	return false;
		 }
	 }
	else
	{
		return false;
	}
}


/**
 * When an Agency adds a new property through the Agency Login, send them an email confirmation
 * @param  array $property_details Property Details
 * @param  array $agency_details   Agency details, inc email and name
 */
function sendNewPropertyConfirmation($property_details, $agency_details)
{
	# Needs to be in array format.
	if(!is_array($agency_details['agency_emails'])) $agency_details['agency_emails'] = array($agency_details['agency_emails']);
	
	#print_r($data['agency_emails']);

	#Get base template
	$template = getBaseEmailTemplate();
	
	
	if($template != FALSE)
	{
		
		#Set template title
		$template = str_replace("#title", "SATS - New Property Added", $template);
		
		// get country
		$cntry_sql = mysql_query("
			SELECT *
			FROM `agency` AS a
			LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
			WHERE a.`agency_id` = {$agency_details['agency_id']}				
		");
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		//$html_content  = "<p>" . $data['agency_name'] . "</p>";
		
		$html_content = '';

		$html_content .= "<p>Dear {$cntry['agency_name']},</p>\n";
		$html_content .= "<p>You have added a new property through the SATS Agency portal:</p>";

		// Address
		$html_content .= "<p>";
		$html_content .= "<strong>Address:</strong> ";
		$html_content .= $_POST['address_1'] . " " . $_POST['address_2'] . ", " . $_POST['address_3'] . ", " . $_POST['state'] . " " . $_POST['postcode'];
		$html_content .= "</p>";

		// new tenants switch
		//$new_tenants = 0;
		$new_tenants = NEW_TENANTS;

		if( $new_tenants == 1 ){ // NEW TENANTS

				$tnt_fname = $_POST['tenant_firstname'];
				$tenant_counter = 1;
				foreach($tnt_fname as $index => $row_tnt_fname){

						$tenant_lastname = $_POST['tenant_lastname'][$index];
						$tenant_ph = $_POST['tenant_ph'][$index];
						$tenant_mob = $_POST['tenant_mob'][$index];
						$tenant_email = $_POST['tenant_email'][$index];

						if($row_tnt_fname!=""){
							$html_content .= "<p>";
							$html_content .= "<b>Tenant {$tenant_counter}</b><br/>";
							$html_content .= "<b>Name:</b>  $row_tnt_fname $tenant_lastname <br/>";
							$html_content .= "<b>Phone:</b> $tenant_ph <br/>";
							$html_content .= "<b>Mobile:</b> $tenant_mob <br/> ";
							$html_content .= "<b>Email:</b> $tenant_email <br/>";
							$html_content .= "</p>";
						}

						$tenant_counter ++;
				}

		}else{ // OLD TENANTS

			
			$num_tenants = getCurrentMaxTenants();
			for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
				if( $_POST['tenant_firstname'.$pt_i] != '' ){
					
					// Tenants
					$html_content .= "<p>";
					$html_content .= "<strong>Tenant {$pt_i} Name:</strong> ";
					$html_content .= $_POST['tenant_firstname'.$pt_i] . " " . $_POST['tenant_lastname'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Phone:</strong> ";
					$html_content .= $_POST['tenant_ph'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Mobile:</strong> ";
					$html_content .= $_POST['tenant_mob'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Email:</strong> ";
					$html_content .= $_POST['tenant_email'.$pt_i];
					$html_content .= "</p>";
					
				}
			}
		}
		

		// Landlord
		$html_content .= "<p>";
		$html_content .= "<strong>Landlord Name:</strong> ";
		$html_content .= $_POST['landlord_firstname'] . " " . $_POST['landlord_lastname'];
		$html_content .= "<br />";
		$html_content .= "<strong>Mobile:</strong> ";
		$html_content .= $_POST['landlord_mobile'];
		$html_content .= "<br />";
		$html_content .= "<strong>Landline:</strong> ";
		$html_content .= $_POST['landlord_landline'];
		$html_content .= "<br />";
		$html_content .= "<strong>Email:</strong> ";
		$html_content .= $_POST['landlord_email'];
		$html_content .= "</p>";

		// Agent
		$html_content .= "<p>";
		$html_content .= "<strong>Agent Name:</strong> ";
		$html_content .= $_POST['agent_firstname'] . " " . $_POST['agent_lastname'];
		$html_content .= "</p>";

		$s_sql = mysql_query("
			SELECT *
			FROM `property_services` AS ps
			LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`
			WHERE `property_id` = {$agency_details['property_id']}
		");
		// Service
		if(mysql_num_rows($s_sql)>0){
			$html_content .= "<p><strong>Services:</strong></p>"; 									
			$html_content .= "<p><ul>";
			while($s = mysql_fetch_array($s_sql)){
				switch($s['service']){
					case 0:
						$service = 'DIY';
					break;
					case 1:
						$service = 'SATS';
					break;
					case 2:
						$service = 'No Response';
					break;
					case 3:
						$service = 'Other Provider';
					break;
				}
				$html_content .= "<li>{$s['type']} $".$s['price']." - <b style='color:red;'>{$service}</b></li>";					
			}
			$html_content .= "</ul></p>";
		}

		$html_content .= "<p>";
		$html_content .= "<strong>Job Comments:</strong> ";
		$html_content .= $_POST['jcomments'];
		$html_content .= "</p>";
		
		// other information
		$html_content .= "<p>";
		if( is_numeric($_POST['prop_vacant']) ){
			$html_content .= "<strong>Currently Vacant:</strong> ".(($_POST['prop_vacant']==1)?'YES':'NO')."<br />";
		}		
		if($_POST['prop_vacant']==1){
			if($_POST['vacant_from']=="" && $_POST['vacant_to']==""){
				$vacant_from_to = "";
			}else{
				$vacant_from_to = "From ".date("d/m/Y",strtotime(str_replace("/","-",mysql_real_escape_string($_POST['vacant_from']))))." To ".date("d/m/Y",strtotime(str_replace("/","-",mysql_real_escape_string($_POST['vacant_to']))));
			}
			$html_content .= "<strong>Vacant Dates</strong> ".$vacant_from_to."<br />";
		}	
		if( is_numeric($_POST['holiday_rental']) ){
			$html_content .= "<strong>Short Term Rental:</strong> ".(($_POST['holiday_rental']==1)?'YES':'NO')."<br />";
		}		
		$html_content .= "</p>";

		$html_content .= "<p>SATS have been notified of this new property, please do not reply to this email</p>";
		$html_content .= "<p>Kind Regards<br />";
		$html_content .= "Smoke Alarm Testing Services.</p>";
		
		$text_content  = "You must enable HTML to view this email properly";
		$text_content .= "Kind Regards\n";
		$text_content .= "Smoke Alarm Testing Services\n\n";
		
		
		$temp = explode("\n",trim($_SESSION['agency_emails']));
		foreach($temp as $val){
			$val2 = preg_replace('/\s+/', '', $val);
			$jemail[$val2] = '';
		}

		# Populate Template
		$template = str_replace("#content", $html_content, $template);
			
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject("Ready for Booking")
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'SATS - Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array		  
		  ->setTo($jemail)
		
		  ->setBCC(CC_EMAIL)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 $result = $mailer->send($email);

		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	return false;
		 }
	 }
	else
	{	
		return false;
	}
}

/**
 * When an Agency adds a new property through the Agency Login, send them an email confirmation
 * @param  array $property_details Property Details
 * @param  array $agency_details   Agency details, inc email and name
 */
function sendChangeOfTenancyConfirmation($property_details, $agency_details)
{
	# Needs to be in array format.
	if(!is_array($agency_details['agency_emails'])) $agency_details['agency_emails'] = array($agency_details['agency_emails']);
	
	#print_r($data['agency_emails']);

	#Get base template
	$template = getBaseEmailTemplate();
	
	
	if($template != FALSE)
	{
		
		#Set template title
		$template = str_replace("#title", "SATS - Change of Tenancy Created", $template);
		
		// get country
		$cntry_sql = getCountryViaCountryId();
		$cntry = mysql_fetch_array($cntry_sql);
		// replace email signature image
		$template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
		// replace trading name
		$template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);
		
		$html_content  = "<p>" . $data['agency_name'] . "</p>";

		$html_content .= "<p>Dear Agent,</p>\n";
		$html_content .= "<p>You have added a Change of Tenancy Job through the SATS Agency Portal:</p>";

		// Address
		$html_content .= "<p>";
		$html_content .= "<strong>Address:</strong> ";
		$html_content .= $_POST['tenant_address'];
		$html_content .= "</p>";

		// Address
		$html_content .= "<p>";
		$html_content .= "<strong>Vacant From:</strong> ";
		$html_content .= $_POST['vacancydate'];
		$html_content .= "</p>";
		
		
		// new tenants switch
		//$new_tenants = 0;
		$new_tenants = NEW_TENANTS;

		if( $new_tenants == 1 ){ // NEW TENANTS

			

		}else{ // OLD TENANTS

			$num_tenants = getCurrentMaxTenants();
			for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
				if( $_POST['tenant_firstname'.$pt_i] != '' ){
					
					// Tenant 
					$html_content .= "<p>";
					$html_content .= "<strong>New Tenant {$pt_i} Name:</strong> ";
					$html_content .= $_POST['tenant_firstname'.$pt_i] . " " . $_POST['tenant_lastname'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Phone:</strong> ";
					$html_content .= $_POST['tenant_ph'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Mobile:</strong> ";
					$html_content .= $_POST['tenant_mob'.$pt_i];
					$html_content .= "<br />";
					$html_content .= "<strong>Email:</strong> ";
					$html_content .= $_POST['tenant_email'.$pt_i];
					$html_content .= "</p>";
					
				}
			}
			
		}

		$html_content .= "<p>SATS have been notified, please contact us for any further information</p>";
		$html_content .= "<p>Kind Regards<br />";
		$html_content .= "Smoke Alarm Testing Services.</p>";
		
		$text_content  = "You must enable HTML to view this email properly";
		$text_content .= "Kind Regards\n";
		$text_content .= "Smoke Alarm Testing Services\n\n";

		# Populate Template
		$template = str_replace("#content", $html_content, $template);
			
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		
		// Create the message
		$email = Swift_Message::newInstance($transport)
		
		  // Give the message a subject
		  ->setSubject("SATS - Change of Tenancy Created")
		
		  // Set the From address with an associative array
		  ->setFrom(array($cntry['outgoing_email'] => 'Smoke Alarm Testing Services'))
		
		  // Set the To addresses with an associative array
		  ->setTo($agency_details['agency_emails'])
		
		  ->setBCC(CC_EMAIL)
		
		  // Give it a body
		  ->setBody($text_content)
		
		  // And optionally an alternative body
		  ->addPart($template, 'text/html')
		 ;
		 
		 $result = $mailer->send($email);
		 
		 if($result)
		 {
		 	return true;
		 }
		 else
		 {
		 	return false;
		 }
	 }
	else
	{	
		return false;
	}
}


function getBaseEmailTemplate()
{
	if(file_exists(EMAIL_TEMPLATE))
	{
		$template = file_get_contents(EMAIL_TEMPLATE);
		return $template;
	}
	else
	{
		return false;
	}
}	

function processMergedSendToEmails($agency_id,$agency_account_emails,$job){
	
	unset($jemail);
	$jemail = array();
	
	// check if agency has maintenance program
	$to_email = '';
	$agency_has_mm = check_agency_has_mm($agency_id);
	
	if( $agency_has_mm == true ){ // Maintenance Program Found
		$to_email = MM_EMAIL;				
	}else{
		if( $job['invoice_pm_only'] == 1 ){
			// only get sent to PM email
		}else{
			$to_email = $agency_account_emails;
		}		
	}
	
	if( $to_email !='' ){
		
		$temp = explode("\n",trim($to_email));
		foreach($temp as $val){
			$val2 = preg_replace('/\s+/', '', $val);
			if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
				$jemail[] = $val2;
			}				
		}
		
	}
	
	
	if( $job['allow_indiv_pm_email_cc']==1 ){

		// pm id
		$pm_id = $job['pm_id_new'];
		$pm_email_fin = "";
		
		// If property has PM with valid email
		 $pm_sql = mysql_query("
			SELECT `email`
			FROM `agency_user_accounts`
			WHERE `agency_user_account_id` = {$pm_id}
			AND `email` != ''
			AND `email` IS NOT NULL
		 ");
		 if( mysql_num_rows($pm_sql)>0 ){
			
			// email not empty, lets validate it
			$pm = mysql_fetch_array($pm_sql);
			$pm_email2 = trim($pm['email']);
			$pm_email3 = preg_replace('/\s+/', '', $pm_email2);
			if(filter_var($pm_email3, FILTER_VALIDATE_EMAIL)){
				$jemail[] = $pm_email3;
			}
			
		 }

		 
	}
	
	return $jemail;
	
}

function check_agency_has_mm($agency_id){

	if( $agency_id > 0 ){

		// check if agency has maintenance program
		$am_sql = mysql_query("
			SELECT COUNT(`agency_maintenance_id`) AS am_count 
			FROM `agency_maintenance` 
			WHERE `agency_id` = {$agency_id}
			AND `maintenance_id` > 0
		");

		$am_row = mysql_fetch_array($am_sql);
		if( $am_row['am_count'] > 0 ){
			return true;
		}else{
			return false;
		}
		
	}	

}

?>
