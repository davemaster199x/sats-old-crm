<?php

include('inc/init_for_ajax.php');

$calendar_id = $_POST['calendar_id'];

mysql_query("
	DELETE 
	FROM `calendar`
	WHERE `calendar_id` = {$calendar_id}
");

?>