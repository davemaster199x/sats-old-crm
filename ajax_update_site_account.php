<?php

include('inc/init_for_ajax.php');

$site_accounts_id = $_POST['site_accounts_id'];
$website = $_POST['website'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$notes = $_POST['notes'];
$expiry_date = $_POST['expiry_date'];
$expiry_date2 = date("Y-m-d",strtotime(str_replace("/","-",$expiry_date)));


// vehicles
mysql_query("
	UPDATE `site_accounts`
	SET
		`website` = '".mysql_real_escape_string($website)."',
		`email` = '".mysql_real_escape_string($email)."',
		`username` = '".mysql_real_escape_string($username)."',		
		`password` = '".mysql_real_escape_string($password)."',
		`notes` = '".mysql_real_escape_string($notes)."',
		`expiry_date` = '".mysql_real_escape_string($expiry_date2)."',
		`last_updated` = '".date("Y-m-d")."'
	WHERE `site_accounts_id` = '".mysql_real_escape_string($site_accounts_id)."'
");


?>