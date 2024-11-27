<?php

include('inc/init_for_ajax.php');

$sales_documents_id = $_POST['sales_documents_id'];
$del_path = $_POST['del_path'];

// delete db
mysql_query("
	DELETE 
	FROM `sales_documents`
	WHERE `sales_documents_id` = {$sales_documents_id}
");

if($del_path!=""){
	// delete file
	unlink($del_path);
	//echo $del_path;
}


?>