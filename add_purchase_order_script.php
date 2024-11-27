<?php

include('inc/init.php');

// instantiate class
$crm = new Sats_Crm_Class();

// data
$country_id = $_SESSION['country_default'];
$purchase_order_num = mysql_real_escape_string($_POST['purchase_order_num']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);

$suppliers_id = mysql_real_escape_string($_POST['supplier']);
$supplier_name = mysql_real_escape_string($_POST['supplier_name']);
$supplier_address = mysql_real_escape_string($_POST['supplier_address']);
$supplier_email = mysql_real_escape_string($_POST['supplier_email']);

$code_arr = $_POST['code'];
$item_arr = $_POST['item'];
$price_arr = $_POST['price'];
$qty_arr = $_POST['qty'];
$total_arr = $_POST['total'];
$item_note = mysql_real_escape_string($_POST['item_note']);

$deliver_to = mysql_real_escape_string($_POST['deliver_to']);
$deliver_to_name = mysql_real_escape_string($_POST['deliver_to_name']);
$delivery_address = mysql_real_escape_string($_POST['delivery_address']);
$reciever_email = mysql_real_escape_string($_POST['reciever_email']);

$comments = mysql_real_escape_string($_POST['comments']);

$ordered_by = mysql_real_escape_string($_POST['ordered_by']);
$ordered_by_name = mysql_real_escape_string($_POST['ordered_by_name']);
$ordered_by_full_name = mysql_real_escape_string($_POST['ordered_by_full_name']);
$order_by_email = mysql_real_escape_string($_POST['order_by_email']);

$agency_id = mysql_real_escape_string($_POST['agency']);
$invoice_total = mysql_real_escape_string($_POST['invoice_total']);


// purchase order
$sql = "
INSERT INTO 
`purchase_order` (
	`purchase_order_num`,
	`date`,
	`suppliers_id`,
	`item_note`,
	`deliver_to`,
	`comments`,
	`ordered_by`,
	`agency_id`,
	`invoice_total`,
	`active`,
	`deleted`,
	`date_created`,
	`country_id`
) 
VALUES(
	'{$purchase_order_num}',
	'{$date2}',
	'{$suppliers_id}',
	'{$item_note}',
	'{$deliver_to}',
	'{$comments}',
	'{$ordered_by}',
	'{$agency_id}',
	'{$invoice_total}',
	'1',
	'0',
	'".date('Y-m-d H:i:s')."',
	'".$_SESSION['country_default']."'
)
";

mysql_query($sql);

$purchase_order_id = mysql_insert_id();

$stocks_id_arr = $_POST['stocks_id'];
$qty_arr = $_POST['qty'];
$total_arr = $_POST['total'];

// purchase order items
foreach( $stocks_id_arr as $index=>$stocks_id ){
	$sql2 = "
		INSERT INTO
		`purchase_order_item`(
			`purchase_order_id`,
			`stocks_id`,
			`quantity`,
			`total`,
			`active`,
			`deleted`,
			`date_created`
		)
		VALUES(
			'{$purchase_order_id}',
			'".mysql_real_escape_string($stocks_id)."',
			'".mysql_real_escape_string($qty_arr[$index])."',
			'".mysql_real_escape_string($total_arr[$index])."',
			'1',
			'0',
			'".date('Y-m-d H:i:s')."'
		)
	";
	mysql_query($sql2);
}



// if email purchase order is ticked
$epo = mysql_real_escape_string($_POST['email_purchase_order']);
if($epo==1){

	$params = array(
		'post_data'=>$_POST,
		'country_id'=>$_SESSION['country_default'],
		'subject'=>'Purchase Order'
	);
	$crm->sendPurchaseOrderEmail($params);

	
}

// redirect
header("location: /purchase_order.php?success=1");


?>