<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_statements.php');

$agency_id = $_REQUEST['id'];
	
// instantiate class
$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];
$pdf_filename = 'statements_'.date('dmYHis').'.pdf';

$params = array(
	'agency_id' => $agency_id,
	'country_id' => $country_id,
	'output' => 'I',
	'file_name' => $pdf_filename
);
$crm->getStatementsPdf($params);




		

?>

