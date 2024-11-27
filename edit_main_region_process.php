<?php

include('inc/init.php');

$regions_id = mysql_real_escape_string($_POST['regions_id']);
$region_name = mysql_real_escape_string($_POST['region_name']);
$state = mysql_real_escape_string($_POST['state']);

mysql_query("
	UPDATE `regions`
	SET
		`region_name` = '{$region_name}',
		`region_state` = '{$state}',
		`country_id` = {$_SESSION['country_default']},
		`status` = 1
	WHERE `regions_id` = {$regions_id}
");

header("location:/edit_main_region.php?id={$regions_id}&success=1");

?>