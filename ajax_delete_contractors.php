<?php

include('inc/init_for_ajax.php');

$contractors_id = $_POST['contractors_id'];

mysql_query("
	DELETE 
	FROM `contractors`
	WHERE `contractors_id` = {$contractors_id}
");

?>