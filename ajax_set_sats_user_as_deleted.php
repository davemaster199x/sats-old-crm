<?php

include('inc/init_for_ajax.php');

$staff_id = $_POST['staff_id'];

mysql_query("
	UPDATE `staff_accounts`
	SET `Deleted` = 1
	WHERE `StaffID` = {$staff_id}
")

?>