<?php

include('inc/init_for_ajax.php');

$vehicles_id = $_POST['vehicles_id'];

mysql_query("
	DELETE 
	FROM `vehicles`
	WHERE `vehicles_id` = {$vehicles_id}
");

?>