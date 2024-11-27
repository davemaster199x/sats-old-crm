<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$agency_id = $_POST['agency_id'];


// vehicles
mysql_query("
	UPDATE `property`
	SET
		`agency_id` = '".mysql_real_escape_string($agency_id)."'		
	WHERE `property_id` = {$property_id}
");


?>