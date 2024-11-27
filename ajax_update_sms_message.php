<?php

include('inc/init_for_ajax.php');

$sms_msg_id = $_POST['sms_msg_id'];
$title = $_POST['title'];
$msg = $_POST['msg'];

mysql_query("
	UPDATE `sms_messages`
	SET
		`title` = '".mysql_real_escape_string($title)."',
		`message` = '".mysql_real_escape_string($msg)."'
	WHERE `sms_messages_id` = {$sms_msg_id}
");



?>