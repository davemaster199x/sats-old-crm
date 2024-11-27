<?php

include('inc/init.php');

$sucess = "";
$error = "";

$url = mysql_real_escape_string($_POST['url']);
$title = mysql_real_escape_string($_POST['title']);
$header = $_POST['header'];


mysql_query("
	INSERT INTO
	`technician_documents`(
		`type`,
		`url`,
		`title`,
		`date`,
		`tech_doc_header_id`
	)
	VALUES(
		2,
		'{$url}',
		'{$title}',
		'".date("Y-m-d H:i:s")."',
		'{$header}'
	)
");	


header("Location: tech_doc.php?success=1");

?>