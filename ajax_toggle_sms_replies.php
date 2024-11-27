<?php

include('inc/init_for_ajax.php');

$sar_id = mysql_real_escape_string($_POST['sar_id']);
$unread = mysql_real_escape_string($_POST['unread']);

$unread2 = ($unread==1)?1:'NULL';

mysql_query("
	UPDATE `sms_api_replies`
	SET `unread` = {$unread2}
	WHERE `sms_api_replies_id` = {$sar_id}
");

?>

