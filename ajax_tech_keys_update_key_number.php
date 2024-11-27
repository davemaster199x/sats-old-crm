<?php

include('inc/init_for_ajax.php');

// data
$property_id = mysql_real_escape_string($_POST['property_id']);
$key_number = mysql_real_escape_string($_POST['key_number']);


mysql_query("
	UPDATE `property`
	SET 
		`key_number` = '{$key_number}'
	WHERE `property_id` = {$property_id}
");

?>