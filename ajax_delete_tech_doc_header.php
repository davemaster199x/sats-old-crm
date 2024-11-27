<?php

include('inc/init_for_ajax.php');

$tdh_id = $_POST['tdh_id'];

// delete db
mysql_query("
	DELETE 
	FROM `tech_doc_header`
	WHERE `tech_doc_header_id` = {$tdh_id}
");

?>