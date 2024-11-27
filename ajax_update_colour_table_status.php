<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$colour_id = mysql_real_escape_string($_POST['colour_id']);
$booking_status = mysql_real_escape_string($_POST['booking_status']);

mysql_query("
	UPDATE `colour_table`
	SET 
		`booking_status` = '{$booking_status}'
	WHERE `tech_run_id` = {$tr_id}
	AND `colour_id` = {$colour_id}
");

?>