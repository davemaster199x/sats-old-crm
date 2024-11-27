<?php

include('inc/init.php');

$staff = mysql_real_escape_string($_POST['staff']);
$date = jFormatDateToBeDbReady($_POST['date']);
$shift_from = mysql_real_escape_string($_POST['shift_from']);
$shift_to = mysql_real_escape_string($_POST['shift_to']);
$first_call = mysql_real_escape_string($_POST['first_call']);
$last_call = mysql_real_escape_string($_POST['last_call']);

$sql = "
	INSERT INTO
	`call_centre_data` (
		`date`,
		`staff_id`,
		`shift_from`,
		`shift_to`,
		`first_call`,
		`last_call`,
		`country_id`,
		`date_created`,
		`deleted`,
		`active`
	)
	VALUES(
		'{$date}',
		{$staff},
		'{$shift_from}',
		'{$shift_to}',
		'{$first_call}',
		'{$last_call}',
		'{$_SESSION['country_default']}',
		'".date('Y-m-d H:i:s')."',
		0,
		1
	)
";
mysql_query($sql);

header("location: /call_centre_report.php?success=1&date={$_POST['date']}");

?>