<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$key_num = mysql_real_escape_string($_POST['key_num']);

mysql_query("
	UPDATE `property`
	SET `key_number` = '{$key_num}'
	WHERE `property_id` = {$prop_id}
");

?>