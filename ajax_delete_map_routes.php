<?php

include('inc/init_for_ajax.php');

$map_routes_id = $_POST['map_routes_id'];

mysql_query("
	DELETE 
	FROM `map_routes`
	WHERE `map_routes_id` = {$map_routes_id}
");

?>