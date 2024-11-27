<?php

include('inc/init_for_ajax.php');

$tdh_id = $_POST['tdh_id'];
$edit_name = $_POST['edit_name'];

foreach($tdh_id as $index => $val){
	mysql_query("
		UPDATE `tech_doc_header`
		SET
			`name` = '".mysql_real_escape_string($edit_name[$index])."'		
		WHERE `tech_doc_header_id` = {$val}
	");
}

// is tech?
$is_tech = $_POST['is_tech'];
$page = ($is_tech==1)?'tech_doc_tech.php':'tech_doc.php';

header("Location: {$page}?success=3");

?>