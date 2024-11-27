<?php

include('inc/init.php');

$stocks = $_POST['stocks'];
$staff_id = $_POST['staff_id'];
$quantity = $_POST['quantity'];
$vehicle = $_POST['vehicle'];

mysql_query("
	INSERT INTO
	`tech_stock`(
		`staff_id`,
		`date`,
		`status`,
		`country_id`,
		`vehicle`
	)
	VALUES(
		'{$staff_id}',
		'".date("Y-m-d H:i:s")."',
		1,
		{$_SESSION['country_default']},
		".mysql_real_escape_string($vehicle)."
	)
");
$ts_id = mysql_insert_id();

// stocks
foreach($stocks as $index=>$stock_id){
	
	mysql_query("
		INSERT INTO 
		`tech_stock_items`(
			`tech_stock_id`,
			`stocks_id`,
			`quantity`,
			`status`
		)
		VALUES(
			{$ts_id},
			'".mysql_real_escape_string($stock_id)."',
			'".mysql_real_escape_string($quantity[$index])."',
			1
		)
	");

}

header("Location: /tech_stock.php?ts_sub=1");

?>
