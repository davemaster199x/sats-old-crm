<?php

include('inc/init_for_ajax.php');

$tech_doc_id = $_POST['tech_doc_id'];
$del_file = $_POST['del_file'];


// delete db
mysql_query("
	DELETE 
	FROM `technician_documents`
	WHERE `technician_documents_id` = {$tech_doc_id}
");


if($del_file!=""){
	$country_folder = strtolower($_SESSION['country_iso']);
	// delete file
	$file_path = "technician_documents/{$country_folder}/{$del_file}";
	unlink("technician_documents/{$country_folder}/{$del_file}");	
}

?>