<?php

include('inc/init_for_ajax.php');

$rh_id = $_POST['rh_id'];
$edit_name = $_POST['edit_name'];

foreach($rh_id as $index => $val){
	mysql_query("
		UPDATE `resources_header`
		SET
			`name` = '".mysql_real_escape_string($edit_name[$index])."'		
		WHERE `resources_header_id` = {$val}
	");
}

/*
// is tech?
$is_tech = $_POST['is_tech'];
$page = ($is_tech==1)?'tech_doc_tech.php':'tech_doc.php';
*/

header("Location: resources.php?success=3");

?>