<?php

include('inc/init_for_ajax.php');

$icon_id = mysql_real_escape_string($_POST['icon_id']);
$description = mysql_real_escape_string($_POST['description']);
$page = mysql_real_escape_string($_POST['page']);

// vehicles
mysql_query("
	UPDATE `icons`
	SET
		`description` = '{$description}',
		`page` = '{$page}'
	WHERE `icon_id` = {$icon_id}
");

?>