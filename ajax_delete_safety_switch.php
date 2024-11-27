<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$ss_id = $_POST['ss_id'];

// kms
$sql = mysql_query("
	DELETE 
	FROM `safety_switch`
	WHERE `safety_switch_id` = {$ss_id}
	AND `job_id` = {$job_id}
");

echo mysql_affected_rows($sql);

?>