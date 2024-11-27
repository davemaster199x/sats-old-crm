<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$key_access = $_POST['key_access'];

mysql_query("
	UPDATE `jobs` 
	SET `key_access_required` = {$key_access}
	WHERE `id` = {$job_id}
");


?>