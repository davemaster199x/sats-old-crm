<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
//require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;
$vehicle_id = mysql_real_escape_string($_REQUEST['vehicle_id']);

$params = array(
	'vehicle_id' => $vehicle_id,
	'output' => 'I'
);

$crm->getVehicleDetailsPdf($params);

?>