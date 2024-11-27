<?php

include('inc/init.php');

$name = $_POST['name'];

mysql_query("
	INSERT INTO 
	`resources_header`(
		`name`,
		`country_id`,
		`status`
	)
	VALUES(
		'".mysql_real_escape_string($name)."',
		{$_SESSION['country_default']},
		1
	)
");

/*
// is tech?
$is_tech = $_POST['is_tech'];
$page = ($is_tech==1)?'tech_doc_tech.php':'tech_doc.php';
*/

header("Location: /resources.php?success=2");

?>