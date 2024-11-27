<?php 

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$email_body = nl2br($_POST['maito_textarea']);
$log_txt = str_replace("\n\n","<br />",$_POST['maito_textarea']);
$cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
$cntry = mysql_fetch_array($cntry_sql);


// new tenants switch
//$new_tenants = 0;
$new_tenants = NEW_TENANTS;
$tenant_emails = [];

if( $new_tenants == 1 ){ // NEW TENANTS

	$tenants_arr = $_POST['tenants_arr'];
	foreach( $tenants_arr as $tenant_email_post){
		$tenant_email = trim($tenant_email_post);
		if( $tenant_email != '' && filter_var( $tenant_email, FILTER_VALIDATE_EMAIL) ){
			$tenant_emails[] = $tenant_email;
		}
	}

}else{ // OLD TENANTS

	$num_tenants = getCurrentMaxTenants();
	for( $pt_i=1; $pt_i<=$num_tenants; $pt_i++ ){ 
		
		$tenant_email = trim($_POST['tenant_email'.$pt_i]);
		if( $tenant_email != '' && filter_var( $tenant_email, FILTER_VALIDATE_EMAIL) ){
			$tenant_emails[] = $tenant_email;
		}
		
	}
	
}


$to_email = implode(",",$tenant_emails);
$from_email = $cntry['outgoing_email'];
$subject = 'Your Agent requires your Approval';


// message
$message = '
<html>
<head>
  <title>'.$subject.'</title>
</head>
<body>
  '.$email_body.'
</body>
</html>
';

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Additional headers
//$headers .= 'To: '.$to."\r\n";
$headers .= 'From: Smoke Alarm Testing Services <'.$from_email.'>' . "\r\n";

// get agency email   
$agency_sql = mysql_query("
	SELECT a.`agency_emails`
	FROM `jobs` AS j
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
	WHERE j.`id` = {$job_id}
");
$agency_data = mysql_fetch_array($agency_sql);

unset($agency_emails);
$agency_emails = array();

$agency_arr = explode("\n",trim($agency_data['agency_emails']));
foreach($agency_arr as $val){
	$val2 = preg_replace('/\s+/', '', $val);
	if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
		$agency_emails[] = $val2;
	}				
}

$cc_email = implode(",",$agency_emails);
$headers .= "Cc: {$cc_email}" . "\r\n";
//$headers .= 'Cc: vaultdweller123@gmail' . "\r\n";
//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";


//echo $message;


mail($to_email, $subject, $message, $headers);


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
		'Tenants Emailed',
		'".date('Y-m-d')."',
		'Email sent to: <strong>{$to_email}</strong> Click <a class=\'sent_email_alink\' href=\'javascript:void(0);\'><strong>HERE</strong></a> to view email content',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
");	
$job_log_id = mysql_insert_id();

// capture email sent
mysql_query("
	INSERT INTO
	`email_templates_sent` (
		`job_log_id`,
		`from_email`,
		`to_email`,
		`cc_email`,
		`subject`,
		`email_body`,
		`date_created`
	)
	VALUES (
		{$job_log_id},
		'".mysql_real_escape_string($from_email)."',
		'".mysql_real_escape_string($to_email)."',
		'".mysql_real_escape_string($cc_email)."',
		'".mysql_real_escape_string($subject)."',
		'".mysql_real_escape_string($email_body)."',
		'".date('Y-m-d H:i:s')."'
	)
");


?>
