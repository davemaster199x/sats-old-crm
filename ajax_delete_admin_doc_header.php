<?php

include('inc/init_for_ajax.php');

$tdh_id = $_POST['tdh_id'];

// delete db
mysql_query("
	DELETE 
	FROM `admin_doc_header`
	WHERE `admin_doc_header_id` = {$tdh_id}
");

?>