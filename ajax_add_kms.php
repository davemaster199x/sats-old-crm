<?php

include('inc/init_for_ajax.php');

$vehicles_id = $_POST['vehicles_id'];
$kms = $_POST['kms'];

// kms
mysql_query("
	INSERT INTO 
	`kms`(
		`vehicles_id`,
		`kms`,
		`kms_updated`
	)
	VALUES(
		{$vehicles_id},
		'".mysql_real_escape_string($kms)."',
		'".date("Y-m-d H:i:s")."'
	)
");

?>