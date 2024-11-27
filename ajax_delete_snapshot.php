<?php

include('inc/init_for_ajax.php');

$sales_snapshot_id = $_POST['sales_snapshot_id'];

mysql_query("
	DELETE 
	FROM `sales_snapshot`
	WHERE `sales_snapshot_id` = {$sales_snapshot_id}
");

?>