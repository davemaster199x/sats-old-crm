<?php

include('inc/init_for_ajax.php');

$tech_run_id = $_POST['tech_run_id'];

// delete tech run
mysql_query("
	DELETE 
	FROM `tech_run`
	WHERE `tech_run_id` = {$tech_run_id}
");

// delete tech run rows
mysql_query("
	DELETE 
	FROM `tech_run_rows`
	WHERE `tech_run_id` = {$tech_run_id}
");

// delete sub regions
mysql_query("
	DELETE 
	FROM `tech_run_sub_regions`
	WHERE `tech_run_id` = {$tech_run_id}
");

?>