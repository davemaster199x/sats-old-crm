<?php

include('inc/init_for_ajax.php');

$suppliers_id = $_POST['suppliers_id'];

mysql_query("
	DELETE 
	FROM `suppliers`
	WHERE `suppliers_id` = {$suppliers_id}
");

?>