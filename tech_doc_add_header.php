<?php

include('inc/init.php');

$name = $_POST['name'];

mysql_query("
	INSERT INTO 
	`tech_doc_header`(
		`name`,
		`country_id`
	)
	VALUES(
		'".mysql_real_escape_string($name)."',
		{$_SESSION['country_default']}
	)
");

// is tech?
$is_tech = $_POST['is_tech'];
$page = ($is_tech==1)?'tech_doc_tech.php':'tech_doc.php';

header("Location: /{$page}?success=2");

?>