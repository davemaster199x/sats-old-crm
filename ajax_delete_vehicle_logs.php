<?php

include('inc/init_for_ajax.php');

$vehicles_log_id = $_POST['vehicles_log_id'];

mysql_query("
	DELETE 
	FROM `vehicles_log`
	WHERE `vehicles_log_id` = {$vehicles_log_id}
");

?>