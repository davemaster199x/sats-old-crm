<?php

include('inc/init_for_ajax.php');

$admin_doc_id = $_POST['admin_doc_id'];
$del_file = $_POST['del_file'];

// delete db
mysql_query("
	DELETE 
	FROM `admin_documents`
	WHERE `admin_documents_id` = {$admin_doc_id}
");

if($del_file!=""){
	// delete file
	unlink($del_file);	
	//echo $del_file;
}

?>