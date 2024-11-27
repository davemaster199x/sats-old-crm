<?php

include('inc/init_for_ajax.php');

$warranty_id = mysql_real_escape_string($_POST['warranty_id']);
$make = mysql_real_escape_string($_POST['make']);
$model = mysql_real_escape_string($_POST['model']);
$amount_replaced = mysql_real_escape_string($_POST['amount_replaced']);
$amount_discarded = mysql_real_escape_string($_POST['amount_discarded']);

$sc_id = $_SESSION['USER_DETAILS']['ClassID'];
$sa_id = $_SESSION['USER_DETAILS']['StaffID'];


$sql = "
	UPDATE `warranties`
	SET
		`make` = '{$make}',
		`model` = '{$model}',
		`amount_replaced` = '{$amount_replaced}',
		`amount_discarded` = '{$amount_discarded}'
	WHERE `warranty_id` = {$warranty_id}
";

mysql_query($sql);

//header("location: warranty_report.php?success=1");

?>