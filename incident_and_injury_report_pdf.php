<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;
$iai_id = mysql_real_escape_string($_REQUEST['id']);

$params = array(
	'iai_id' => $iai_id,
	'output' => 'I'
);

$crm->getIncidentAndReportPdf($params);

?>