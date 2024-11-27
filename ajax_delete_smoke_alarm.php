<?php

include('inc/init_for_ajax.php');

//$crm = new Sats_Crm_Class;

$sa_id = mysql_real_escape_string($_REQUEST['sa_id']);
$country_id = $_SESSION['country_default'];

mysql_query("
	DELETE
	FROM `smoke_alarms`
	WHERE `smoke_alarm_id` = {$sa_id}
	AND `country_id` = {$country_id}
");

?>