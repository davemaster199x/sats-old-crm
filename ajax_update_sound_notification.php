<?php

include('inc/init_for_ajax.php');

$staff_id = mysql_real_escape_string($_POST['staff_id']);

mysql_query("
	UPDATE `staff_accounts`
	SET `sound_notification` = 0
	WHERE `StaffID` = {$staff_id}
");

?>