<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_expense.php');

$crm = new Sats_Crm_Class;
$exp_sum_id = $_REQUEST['id'];
$country_id = $_SESSION['country_default'];

$jparams = array(
	'exp_sum_id' => $exp_sum_id,
	'country_id' => $country_id,
	'output' => 'I'
);
$crm->getExpenseSummaryPdf($jparams);

?>