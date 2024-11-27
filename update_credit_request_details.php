<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$cr_id = mysql_real_escape_string($_POST['cr_id']);
$reason = mysql_real_escape_string($_POST['reason']);
$result = ( is_numeric($_POST['result']) )?mysql_real_escape_string($_POST['result']):'NULL';
$date_processed = ($_POST['date_processed']!='')?"'".$crm->formatDate($_POST['date_processed'])."'":'NULL';
$comments = mysql_real_escape_string($_POST['comments']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$requested_by_name = mysql_real_escape_string($_POST['requested_by_name']);
$job_id = mysql_real_escape_string($_POST['job_id']);
$requested_by_id = mysql_real_escape_string($_POST['requested_by_id']);
$amount_credited = mysql_real_escape_string($_POST['amount_credited']);

$sql_str = "
	UPDATE `credit_requests` 
	SET
		`reason` = '{$reason}',
		`result` = {$result},
		`date_processed` = {$date_processed},
		`comments` = '{$comments}',
		`who` = {$staff_id},
		`amount_credited` = '{$amount_credited}'
	WHERE `credit_request_id` = {$cr_id}
";
mysql_query($sql_str);







// EMAIL
if( is_numeric($result) ){
	
	// get job property address
	$j_sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		WHERE j.`id` = {$job_id} 
	");
	$j = mysql_fetch_array($j_sql);
	$property_address = "{$j['address_1']} {$j['address_2']}, {$j['address_3']} {$j['state']} {$j['postcode']}";
	
	// get requested user email
	$sa_sql = mysql_query("
		SELECT * 
		FROM `staff_accounts`
		WHERE `StaffID` = {$requested_by_id} 
	");
	$sa = mysql_fetch_array($sa_sql);
	$rb_email = $sa['Email'];
	
	// result
	if( $result==1 ){
		$result_txt = "<b style='color:green'>Accepted</b>";
	}else if( is_numeric($result) && $result==0 ){
		$result_txt = "<b style='color:red'>Declined</b>";
	}else if( is_numeric($result) && $result==2 ){
		$result_txt = "<b style='color:#f37b53'>More info needed</b>";
	}
	
	$amount_credited_txt = '$'.number_format($amount_credited,2);

	// Multiple recipients
	//$to = 'vaultdweller123@gmail.com, danielk@sats.com.au'; // note the comma
	//$to = 'danielk@sats.com.au'; 
	//$to = 'vaultdweller123@gmail.com';
	$to = $rb_email;
	//$to = ACCOUNTS_EMAIL; 
	$from_email = INFO_EMAIL;

	// Subject
	$subject = 'Adjustment Request #'.$job_id;

	// Message
	$message = "
	<html>
	<head>
	  <title>{$subject}</title>
	</head>
	<body>
	
		<p>Dear {$requested_by_name}</p><br />
		<p>The Adjustment request {$amount_credited_txt} for {$property_address} has been {$result_txt}</p><br />
		<p>{$comments}</p><br />
		<p>Please contact accounts for any further information</p>
	
	</body>
	</html>
	";

	// To send HTML mail, the Content-type header must be set
	$headers[] = 'MIME-Version: 1.0';
	$headers[] = 'Content-type: text/html; charset=iso-8859-1';

	// Additional headers
	$headers[] = 'From: Smoke Alarm Testing Services <'.$from_email.'>';
	$headers[] = 'Cc: '.$from_email. "\r\n";


	// Mail it
	mail($to, $subject, $message, implode("\r\n", $headers));
	
}


header("location: credit_requests.php?id={$cr_id}&update_success=1");

?>