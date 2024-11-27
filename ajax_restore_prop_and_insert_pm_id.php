<?php 
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$property_id = mysql_real_escape_string($_POST['property_id']);
$pm_prop_id = mysql_real_escape_string($_POST['pm_prop_id']);

$params = array(
	'property_id' => $property_id,
	'pm_prop_id' => $pm_prop_id
);
$crm->restoreProperty($params);

?>