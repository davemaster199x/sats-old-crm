<?php

include('inc/init_for_ajax.php');

$vehicle_files_id = $_POST['vehicle_files_id'];
$vf_path = $_POST['vf_path'];

mysql_query("
	DELETE 
	FROM `vehicle_files`
	WHERE `vehicle_files_id` = {$vehicle_files_id}
");

if($vf_path!=''){
	unlink($vf_path);
}


?>