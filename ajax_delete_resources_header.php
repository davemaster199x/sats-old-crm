<?php

include('inc/init_for_ajax.php');

$rh_id = $_POST['rh_id'];

// delete db
mysql_query("
	DELETE 
	FROM `resources_header`
	WHERE `resources_header_id` = {$rh_id}
");

?>