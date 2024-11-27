<?php

include('inc/init_for_ajax.php');

$figures_id = mysql_real_escape_string($_POST['figures_id']);

mysql_query("
	DELETE 
	FROM `figures`
	WHERE `figures_id` = {$figures_id}
");

?>