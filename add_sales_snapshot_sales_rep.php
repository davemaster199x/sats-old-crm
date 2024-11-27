<?php

include('inc/init.php');

$fname = $_POST['fname'];
$lname = $_POST['lname'];

mysql_query("
	INSERT INTO 
	`sales_snapshot_sales_rep`(
		`first_name`,
		`last_name`,
		`country_id`
	)
	VALUES(
		'".mysql_real_escape_string($fname)."',
		'".mysql_real_escape_string($lname)."',
		{$_SESSION['country_default']}
	)
");

header("Location: sales_snapshot.php?success=2");

?>
