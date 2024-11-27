<?php

include('inc/init_for_ajax.php');

$sales_snapshot_id = $_POST['sales_snapshot_id'];
$sales_rep = $_POST['sales_rep'];
$agency_id = $_POST['agency_id'];
$properties = $_POST['properties'];
$status = $_POST['status'];
$details = $_POST['details'];


// vehicles
mysql_query("
	UPDATE `sales_snapshot`
	SET
		`sales_snapshot_sales_rep_id` = '".mysql_real_escape_string($sales_rep)."',
		`agency_id` = '".mysql_real_escape_string($agency_id)."',
		`properties` = '".mysql_real_escape_string($properties)."',		
		`sales_snapshot_status_id` = '".mysql_real_escape_string($status)."',
		`details` = '".mysql_real_escape_string($details)."',
		`date` = '".date('Y-m-d H:i:s')."'
	WHERE `sales_snapshot_id` = {$sales_snapshot_id}
");


?>