<?php

include('inc/init.php');

$website = mysql_real_escape_string($_POST['website']);
$email = mysql_real_escape_string($_POST['email']);
$user = mysql_real_escape_string($_POST['user']);
$pass = mysql_real_escape_string($_POST['pass']);
$notes = mysql_real_escape_string($_POST['notes']);
$expiry_date = mysql_real_escape_string($_POST['expiry_date']);
$expiry_date2 = date("Y-m-d",strtotime(str_replace("/","-",$expiry_date)));

mysql_query("
	INSERT INTO 
	`site_accounts`(
		`website`,
		`email`,
		`username`,
		`password`,
		`notes`,
		`expiry_date`,
		`last_updated`,
		`status`,
		`country_id`
	)
	VALUES(
		'{$website}',
		'{$email}',
		'{$user}',
		'{$pass}',
		'{$notes}',
		'{$expiry_date2}',
		'".date("Y-m-d")."',
		1,
		{$_SESSION['country_default']}
	)
");

header("Location: /passwords.php?success=1");

?>
