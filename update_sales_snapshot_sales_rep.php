<?php

include('inc/init_for_ajax.php');

$ss_sr_id = $_POST['ss_sr_id'];
$edit_fname = $_POST['edit_fname'];
$edit_lname = $_POST['edit_lname'];

foreach($ss_sr_id as $index => $val){
	mysql_query("
		UPDATE `sales_snapshot_sales_rep`
		SET
			`first_name` = '".mysql_real_escape_string($edit_fname[$index])."',
			`last_name` = '".mysql_real_escape_string($edit_lname[$index])."'
		WHERE `sales_snapshot_sales_rep_id` = {$val}
	");
}

header("Location: sales_snapshot.php?success=3");

?>