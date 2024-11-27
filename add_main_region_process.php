<?php

include('inc/init.php');

$region_name = mysql_real_escape_string($_POST['region_name']);
$state = mysql_real_escape_string($_POST['state']);

mysql_query("
	INSERT INTO
	`regions`(
		`region_name`,
		`region_state`,
		`country_id`,
		`status`
	)
	VALUES(
		'{$region_name}',
		'{$state}',
		{$_SESSION['country_default']},
		1
	)
");

header("location:/add_main_region.php?success=1");

?>