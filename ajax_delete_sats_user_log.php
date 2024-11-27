<?php

include('inc/init_for_ajax.php');

$user_log_id = $_POST['user_log_id'];

mysql_query("
	DELETE 
	FROM `user_log`
	WHERE `user_log_id` = {$user_log_id}
");

?>