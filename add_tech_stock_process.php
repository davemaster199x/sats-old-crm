<?php

include('inc/init.php');

$code = $_POST['code'];
$item = $_POST['item'];
$display_name = $_POST['display_name'];
$price = $_POST['price'];
$display = ($_POST['display'])?$_POST['display']:0;
$supplier = $_POST['supplier'];
$show_on_stocktake = ($_POST['show_on_stocktake'])?$_POST['show_on_stocktake']:0;

mysql_query("
	INSERT INTO 
	`stocks`(
		`code`,
		`item`,
		`display_name`,
		`price`,
		`display`,
		`created`,
		`country_id`,
		`suppliers_id`,
		`show_on_stocktake`
	)
	VALUES(
		'".mysql_real_escape_string($code)."',
		'".mysql_real_escape_string($item)."',
		'".mysql_real_escape_string($display_name)."',
		'".mysql_real_escape_string($price)."',
		'".mysql_real_escape_string($display)."',
		'".date("Y-m-d H:i:s")."',
		{$_SESSION['country_default']},
		'".mysql_real_escape_string($supplier)."',
		'".mysql_real_escape_string($show_on_stocktake)."'
	)
");

header("Location: /add_tech_stock.php?success=1");

?>
