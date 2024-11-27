<?php

include('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;

// data
$employee = mysql_real_escape_string($_POST['employee']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$card = mysql_real_escape_string($_POST['card']);
$supplier = mysql_real_escape_string($_POST['supplier']);
$description = mysql_real_escape_string($_POST['description']);
$account = mysql_real_escape_string($_POST['account']);
$amount = mysql_real_escape_string($_POST['amount']);
$receipt_image = $_FILES['receipt_image'];
$country_id = $_SESSION['country_default'];

$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];


//$upload = $crm->uploadExpenseRecieptImage($receipt_image);

// UPLOAD
$file_type = ($receipt_image['type']=='application/pdf')?'pdf':'image';
$uparams = array(
	'files' => $receipt_image,
	'id' => rand(),
	'upload_folder' => 'expenses',
	'file_type' => $file_type,
	'image_size' => 760
);
$upload_ret = $crm->ExpensesFileUpload($uparams);


$sql_str = "
	INSERT INTO
	`expenses` (
		`employee`,
		`date`,
		`card`,
		`supplier`,
		`description`,
		`account`,
		`amount`,
		`receipt_image`,
		`file_type`,
		`country_id`,
		`entered_by`
	)
	VALUES (
		'{$employee}',
		'{$date2}',
		'{$card}',
		'{$supplier}',
		'{$description}',
		'{$account}',	
		'{$amount}',	
		'{$upload_ret['path_to_file']}',
		'{$file_type}',
		'{$country_id}',
		{$loggedin_staff_id}
	)
";

mysql_query($sql_str);


header("location: /expense.php?success=1");

?>