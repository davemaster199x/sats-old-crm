<?php

include('inc/init.php');

$crm = new Sats_Crm_Class();

$purchase_order_id = mysql_real_escape_string($_POST['purchase_order_id']);

$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$suppliers_id = mysql_real_escape_string($_POST['supplier']);
$item_note = mysql_real_escape_string($_POST['item_note']);
$deliver_to = mysql_real_escape_string($_POST['deliver_to']);
$comments = mysql_real_escape_string($_POST['comments']);
$ordered_by = mysql_real_escape_string($_POST['ordered_by']);

$agency_id = mysql_real_escape_string($_POST['agency']);
$invoice_total = mysql_real_escape_string($_POST['invoice_total']);

if( $suppliers_id == $crm->getDynamicHandyManID() ){ // if supplier is handyman
	$update_str = "
		`agency_id` = '{$agency_id}',
		`invoice_total` = '{$invoice_total}'
	";
}else{
	$update_str = "
		`item_note` = '{$item_note}',
		`deliver_to` = '{$deliver_to}',
		`comments` = '{$comments}',
		`ordered_by` = '{$ordered_by}'
	";
}


// update purchase order
$sql = "
	UPDATE `purchase_order`
	SET 
		`date` = '{$date2}',
		`suppliers_id` = '{$suppliers_id}',
		`item_note` = '{$item_note}',
		`deliver_to` = '{$deliver_to}',
		`comments` = '{$comments}',
		`ordered_by` = '{$ordered_by}',		
		{$update_str}
	WHERE `purchase_order_id` = {$purchase_order_id}
";
mysql_query($sql);


if( $suppliers_id != $crm->getDynamicHandyManID() ){ // if supplier is non-handyman

	// update purchase order item
	// clear purchase order item
	mysql_query("
		DELETE 
		FROM `purchase_order_item`
		WHERE `purchase_order_id` = {$purchase_order_id}
	");

	// purchase order items
	$stocks_id_arr = $_POST['stocks_id'];
	$qty_arr = $_POST['qty'];
	$total_arr = $_POST['total'];

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

}



// if email purchase order is ticked
$epo = mysql_real_escape_string($_POST['email_purchase_order']);
if($epo==1){
	$params = array(
		'post_data'=>$_POST,
		'country_id'=>$_SESSION['country_default'],
		'subject'=>'Purchase Order Updated'
	);
	$crm->sendPurchaseOrderEmail($params);
}


header("location:/purchase_order_details.php?id={$purchase_order_id}&update=1");

?>