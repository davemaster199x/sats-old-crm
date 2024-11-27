<?php

include('inc/init_for_ajax.php');

// data
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$status = mysql_real_escape_string($_POST['status']);

// check if route already set
$str = "
	SELECT *
	FROM `map_routes`
	WHERE `tech_id` = {$tech_id}
	AND `date` = '{$date}'
";

$mp_sql = mysql_query($str);

$mp = mysql_fetch_array($mp_sql);

// update
$sql = "
	UPDATE `map_routes`
	SET `run_mapped` = {$status}
	WHERE `map_routes_id` = {$mp['map_routes_id']}
	";
mysql_query($sql);

?>