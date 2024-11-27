<?php

include('inc/init_for_ajax.php');

$staff_id = $_POST['staff_id'];
$bs_num = $_POST['bs_num'];

// vehicles
mysql_query("
	UPDATE `staff_accounts`
	SET
		`booking_schedule_num` = '".mysql_real_escape_string($bs_num)."'
	WHERE `StaffID` = {$staff_id}
");


?>