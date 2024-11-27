<?php

include('inc/init_for_ajax.php');

$job_id_arr = $_POST['job_id'];
$country_id = $_SESSION['country_default'];

foreach( $job_id_arr as $job_id ){

	$today = date('Y-m-d');
	$todaydt = date('Y-m-d H:i:s');
	$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
	$pme_billable = false;
	$palace_billable = false;

	// clear email array
	unset($jemail);
	$jemail = array();

	// update job
	$tech_id = 2; // Upfront Bill

	$sql_str = "
		UPDATE `jobs` 
		SET
			`job_type` = 'Yearly Maintenance',
			`status` = 'Merged Certificates',
			`date` = '{$today}',
			`booked_with` = 'Agent',
			`booked_by` = '{$logged_user}',			
			`assigned_tech` = {$tech_id}			
		WHERE `id` = ".mysql_real_escape_string($job_id)."
	";
	mysql_query($sql_str);

	// job log
	mysql_query("
		INSERT INTO 
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`, 
			`staff_id`,
			`eventtime`
		) 
		VALUES (
			'Job Update',
			'" . date('Y-m-d') . "',
			'Job status changed from <b>To Be Invoiced</b> to <b>Merged Certificates</b>.',
			{$job_id}, 
			'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
			'" . date('H:i') . "'
		)
	");
	
	// get updated job
	// copied from email_functions.php, batchSendInvoicesCertificates function 
	$sql_str2 = "SELECT j.id, j.job_type, DATE_FORMAT(j.date,'%d/%m/%Y') AS job_date,
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
		p.`pm_id_new`,
		a.`franchise_groups_id`,
		a.`agency_name`,
		p.`landlord_firstname`,
		p.`landlord_lastname`,
		p.`propertyme_prop_id`,
		a.`pme_supplier_id`,
		p.`palace_prop_id`,
		a.`palace_diary_id`
		FROM (jobs j, property p, agency a)
		LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID   
		WHERE j.property_id = p.property_id 
		AND p.agency_id = a.agency_id
		AND j.`id` = {$job_id}
		";
	$query = mysql_query($sql_str2);
	$job = mysql_fetch_array($query);

	// Pme property ID exist and agency supplier ID exist
	if( $job['propertyme_prop_id'] != '' && $job['pme_supplier_id'] != '' ){
		$pme_billable = true;
	}
	
	// Palace property ID exist and palace diary ID exist
	if( $job['palace_prop_id'] != '' && $job['palace_diary_id'] != '' ){
		$palace_billable = true;
	}
	
	if( $pme_billable == true ){ // skip email

		// job log
		mysql_query("
			INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`comments`,
				`job_id`, 
				`staff_id`,
				`eventtime`
			) 
			VALUES (
				'Upfront Job moved',
				'" . date('Y-m-d') . "',
				'PMe connected job moved from <b>To Be Invoiced</b> to <b>Merged Jobs</b> for invoicing',
				{$job_id}, 
				'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
				'" . date('H:i') . "'
			)
		");
		
	}else if( $palace_billable == true ){ // skip email

		// job log
		mysql_query("
			INSERT INTO 
			`job_log` (
				`contact_type`,
				`eventdate`,
				`comments`,
				`job_id`, 
				`staff_id`,
				`eventtime`
			) 
			VALUES (
				'Upfront Job moved',
				'" . date('Y-m-d') . "',
				'Palace connected job moved from <b>To Be Invoiced</b> to <b>Merged Jobs</b> for invoicing',
				{$job_id}, 
				'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
				'" . date('H:i') . "'
			)
		");
		
	}else{ // send email

		/*
		// check if agency has maintenance program
		$jemail = processMergedSendToEmails($job['agency_id'],$job['account_emails'],$job);
		
		// email invoice
		$invoice_only = 1;
		sendInvoiceCertEmail($job, $jemail,$country_id,$invoice_only);

		// update email and SMS marker		
		$sql_str = "
			UPDATE `jobs` 
			SET
				`client_emailed` = '{$todaydt}',
				`sms_sent_merge` = '{$todaydt}'
			WHERE `id` = ".mysql_real_escape_string($job_id)."
		";
		mysql_query($sql_str);
		*/

	}
	
	
	
	
}



?>