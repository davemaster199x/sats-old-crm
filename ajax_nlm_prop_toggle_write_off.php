<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$write_off = mysql_real_escape_string($_POST['write_off']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

mysql_query("
	UPDATE `property`
	SET `write_off` = {$write_off}
	WHERE `property_id` = {$prop_id}
");

if( $write_off==1 ){
	
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
			'Marked Write Off', 
			'".date('Y-m-d H:i:s')."'
		)
	");
	
}


?>