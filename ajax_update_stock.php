<?php

include('inc/init_for_ajax.php');

$stocks_id = $_POST['stocks_id'];
$code = $_POST['code'];
$item = $_POST['item'];
$display_name = $_POST['display_name'];
$price = $_POST['price'];
$display = $_POST['display'];
$status = $_POST['status'];
$supplier = $_POST['supplier'];
$show_on_stocktake = $_POST['show_on_stocktake'];

// vehicles
mysql_query("
	UPDATE `stocks`
	SET
		`code` = '".mysql_real_escape_string($code)."',
		`item` = '".mysql_real_escape_string($item)."',	
		`display_name` = '".mysql_real_escape_string($display_name)."',
		`price` = '".mysql_real_escape_string($price)."',
		`display` = '".mysql_real_escape_string($display)."',		
		`status` = '".mysql_real_escape_string($status)."',
		`suppliers_id` = '".mysql_real_escape_string($supplier)."',
		`show_on_stocktake` = '".mysql_real_escape_string($show_on_stocktake)."'
	WHERE `stocks_id` = {$stocks_id}
");


?>