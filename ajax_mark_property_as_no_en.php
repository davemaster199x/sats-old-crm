<?php

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['prop_id']);
$no_en = mysql_real_escape_string($_POST['no_en']);


// vehicles
mysql_query("
	UPDATE `property`
	SET
		`no_en` = {$no_en}
	WHERE `property_id` = {$property_id}
");


?>