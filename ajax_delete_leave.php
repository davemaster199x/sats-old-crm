<?php

include('inc/init_for_ajax.php');

$leave_id = mysql_real_escape_string($_POST['leave_id']);

mysql_query("
	UPDATE `leave`
	SET `deleted` = 1
	WHERE `leave_id` = {$leave_id}
");

?>