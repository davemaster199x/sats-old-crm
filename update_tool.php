<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$tools_id = mysql_real_escape_string($_POST['tools_id']);
$item = mysql_real_escape_string($_POST['item']);
$item_id = mysql_real_escape_string($_POST['item_id']);
$brand = mysql_real_escape_string($_POST['brand']);
$description = mysql_real_escape_string($_POST['description']);
$purchase_date = mysql_real_escape_string($_POST['purchase_date']);
$purchase_date2 = $crm->formatDate($purchase_date);
$purchase_price = mysql_real_escape_string($_POST['purchase_price']);
$assign_to_vehicle = mysql_real_escape_string($_POST['assign_to_vehicle']);

$sql = "
	UPDATE `tools` 
	SET
		`item_id` = '{$item_id}',
		`brand` = '{$brand}',
		`description` = '{$description}',
		`purchase_date` = '{$purchase_date2}',
		`purchase_price` = '{$purchase_price}',
		`assign_to_vehicle` = '{$assign_to_vehicle}'
	WHERE `tools_id` = {$tools_id}
";
mysql_query($sql);

header("location: /view_tool_details.php?id={$tools_id}&update=1");

?>