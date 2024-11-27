<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$mob = mysql_real_escape_string($_POST['mob']);
$tenant = mysql_real_escape_string($_POST['tenant']);

// update job
mysql_query("
	UPDATE `property`
	SET 
		`{$tenant}` = '{$mob}'
	WHERE `property_id` = {$prop_id}
");


?>