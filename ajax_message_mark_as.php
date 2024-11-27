<?php

include('inc/init_for_ajax.php');

$msg_h_id = mysql_real_escape_string($_POST['msg_h_id']);
$mark_as = mysql_real_escape_string($_POST['mark_as']);

// update job
mysql_query("
	UPDATE `message_header`
	SET 
		`read` = {$mark_as}
	WHERE `message_header_id` = {$msg_h_id}
");


?>