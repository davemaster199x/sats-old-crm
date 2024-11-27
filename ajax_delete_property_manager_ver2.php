<?php

include('inc/init_for_ajax.php');

$pm_id = $_POST['pm_id'];

// check properties assign to this PM
$p_sql = mysql_query("
	SELECT *
	FROM `property`
	WHERE `property_managers_id` ={$pm_id}
");

if(mysql_num_rows($p_sql)==0){ // delete if PM is not assigneed to any properties
	mysql_query("
		DELETE 
		FROM `property_managers`
		WHERE `property_managers_id` = {$pm_id}
	");
	$ret = 0;
}else{
	$ret = 1;
}

echo $ret;

?>