<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

$expense_id = mysql_real_escape_string($_GET['id']);

// delete old image first
$c_sql = mysql_query("
	SELECT `receipt_image`
	FROM `expenses`
	WHERE `expense_id` = {$expense_id}
");
$c = mysql_fetch_array($c_sql);

if( $c['receipt_image']!='' ){
	$file_to_delete = $c['receipt_image'];
	if( $file_to_delete!="" ){
		$crm->deleteExpenseFile($file_to_delete);
	}
}

// delete db
$sql_str = "
	DELETE
	FROM expenses
	WHERE `expense_id` = {$expense_id}
";
mysql_query($sql_str);



header("location: /expense.php?del_success=1");

?>