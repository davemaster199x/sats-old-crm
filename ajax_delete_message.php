<?php

include('inc/init_for_ajax.php');

$msg_h_id = $_POST['msg_h_id'];

mysql_query("
	UPDATE `message_header`
	SET `deleted` = 1
	WHERE `message_header_id` = {$msg_h_id}
");

?>