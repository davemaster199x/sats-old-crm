<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$nlm_owing = mysql_real_escape_string($_POST['nlm_owing']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

mysql_query("
	UPDATE `property`
	SET `nlm_owing` = {$nlm_owing}
	WHERE `property_id` = {$prop_id}
");

// add property for money Owing
if( $nlm_owing==1 ){
	
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
			'Marked Money Owing', 
			'".date('Y-m-d H:i:s')."'
		)
	");
	
}


?>