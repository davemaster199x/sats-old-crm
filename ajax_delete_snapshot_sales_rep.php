<?php

include('inc/init_for_ajax.php');

$ss_sr_id = $_POST['ss_sr_id'];

mysql_query("
	DELETE 
	FROM `sales_snapshot_sales_rep`
	WHERE `sales_snapshot_sales_rep_id` = {$ss_sr_id}
");

?>