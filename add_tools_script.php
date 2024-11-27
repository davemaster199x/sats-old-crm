<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$item = mysql_real_escape_string($_POST['item']);
$item_id = mysql_real_escape_string($_POST['item_id']);
$brand = ($item==1)?mysql_real_escape_string($_POST['brand_dp']):mysql_real_escape_string($_POST['brand_input']);
$description = ($item==1)?mysql_real_escape_string($_POST['description_dp']):mysql_real_escape_string($_POST['description_input']);
$purchase_date = mysql_real_escape_string($_POST['purchase_date']);
$purchase_date2 = $crm->formatDate($purchase_date);
$purchase_price = mysql_real_escape_string($_POST['purchase_price']);
$assign_to_vehicle = mysql_real_escape_string($_POST['assign_to_vehicle']);
$country_id = $_SESSION['country_default'];

$sql = "
	INSERT INTO
	`tools` (
		`item`,
		`item_id`,
		`brand`,
		`description`,
		`purchase_date`,
		`purchase_price`,
		`assign_to_vehicle`,
		`active`,
		`deleted`,
		`date_created`,
		`country_id`
	)
	VALUE (
		'{$item}',
		'{$item_id}',
		'{$brand}',
		'{$description}',
		'{$purchase_date2}',
		'{$purchase_price}',
		'{$assign_to_vehicle}',
		1,
		0,
		'".date('Y-m-d H:i:s')."',
		'{$country_id}'
	)
";
mysql_query($sql);

header("location: /view_tools.php?success=1");

?>