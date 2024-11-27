<?php

include('inc/init_for_ajax.php');

$resources_id = $_POST['resources_id'];
$type = $_POST['type'];
$del_path = $_POST['del_path'];

// delete db
mysql_query("
	DELETE 
	FROM `resources`
	WHERE `resources_id` = {$resources_id}
");

if($type==1){
	if($del_path!=""){
		// delete file
		unlink($del_path);
		//echo $del_path;
	}
}

?>