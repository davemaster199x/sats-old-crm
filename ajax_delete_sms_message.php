<?php

include('inc/init_for_ajax.php');

$sms_msg_id = $_POST['sms_msg_id'];

mysql_query("
	DELETE 
	FROM `sms_messages`
	WHERE `sms_messages_id` = {$sms_msg_id}
");

?>