<?php

include('inc/init_for_ajax.php');

$ccd_id = mysql_real_escape_string($_POST['ccd_id']);

// update
mysql_query("
	DELETE 
	FROM `call_centre_data`
	WHERE `call_centre_data_id` = {$ccd_id}
");

?>