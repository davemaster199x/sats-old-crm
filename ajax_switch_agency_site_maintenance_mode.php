<?php

include('inc/init_for_ajax.php');

$mode = $_POST['mode'];

$switch_mode = ($mode==1)?0:1;

// kms
mysql_query("
	UPDATE `agency_site_maintenance_mode`
	SET `mode` = {$switch_mode}
");

?>