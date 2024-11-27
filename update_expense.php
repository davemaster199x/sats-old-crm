<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

$expense_id = mysql_real_escape_string($_POST['expense_id']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$card = mysql_real_escape_string($_POST['card']);
$supplier = mysql_real_escape_string($_POST['supplier']);
$description = mysql_real_escape_string($_POST['description']);
$account = mysql_real_escape_string($_POST['account']);
$amount = mysql_real_escape_string($_POST['amount']);
$receipt_image = $_FILES['receipt_image'];
$country_id = $_SESSION['country_default'];

//print_r($receipt_image);

/*
if( $receipt_image['name']!='' ){
	//echo "file not empty";
	$upload = $crm->uploadExpenseRecieptImage($receipt_image);
	$append_set = ",`receipt_image` = '{$upload['receipt_image']}'";
}else{
	//echo "file empty";
}
*/

// update ss image
if( $_POST['image_touched']==1 ){

	$receipt_image = $_FILES['receipt_image'];
		
	// dont upload if empty
	if($receipt_image['name']!=''){
		
		
		// delete old image
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
		
		
		
		// UPLOAD
		$file_type = ($receipt_image['type']=='application/pdf')?'pdf':'image';
		$uparams = array(
			'files' => $receipt_image,
			'id' => $expense_id,
			'upload_folder' => 'expenses',
			'file_type' => $file_type,
			'image_size' => 760
		);
		$upload_ret = $crm->ExpensesFileUpload($uparams);
		
		
		// store image path
		mysql_query("
			UPDATE `expenses`
			SET `receipt_image` = '{$upload_ret['path_to_file']}'
			WHERE `expense_id` = {$expense_id}
		");
		
	}
	
	
}


$sql_str = "
	UPDATE `expenses` 
	SET
		`date` = '{$date2}',
		`card` = '{$card}',
		`supplier` = '{$supplier}',
		`description` = '{$description}',
		`account` = '{$account}',
		`amount` = '{$amount}'
		{$append_set}
	WHERE `expense_id` = {$expense_id}
";

mysql_query($sql_str);


header("location: /expense_details.php?id={$expense_id}&success=1");

?>