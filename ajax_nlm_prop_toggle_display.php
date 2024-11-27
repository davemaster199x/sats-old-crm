<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$nlm_display = mysql_real_escape_string($_POST['nlm_display']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

mysql_query("
	UPDATE `property`
	SET `nlm_display` = {$nlm_display}
	WHERE `property_id` = {$prop_id}
");

// add property log if someone verified payment on NLM properties
mysql_query("
	INSERT INTO 
	property_event_log (
		property_id, 
		staff_id, 
		event_type, 
		event_details, 
		log_date
	) 
	VALUES (
		{$prop_id}, 
		{$staff_id}, 
		'NLM Property', 
		'Payments verified', 
		'".date('Y-m-d H:i:s')."'
	)
");

?>