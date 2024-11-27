<?php

include('inc/init_for_ajax.php');

$make = mysql_real_escape_string($_POST['make']);
$model = mysql_real_escape_string($_POST['model']);
$amount_replaced = mysql_real_escape_string($_POST['amount_replaced']);
$amount_discarded = mysql_real_escape_string($_POST['amount_discarded']);

$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql = "
	INSERT INTO 
	`warranties`(
		`tech_staff_id`,
		`make`,
		`model`,
		`amount_replaced`,
		`amount_discarded`,
		`date_created`
	)
	VALUES(
		{$sa_id},
		'{$make}',
		'{$model}',
		'{$amount_replaced}',
		'{$amount_discarded}',
		'".date('Y-m-d')."'
	)
";

mysql_query($sql);

header("location: warranty_report.php?success=1");

?>