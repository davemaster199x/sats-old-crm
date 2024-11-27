<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override.php');

$crm = new Sats_Crm_Class;
$leave_id = mysql_real_escape_string($_REQUEST['id']);

$params = array(
	'leave_id' => $leave_id,
	'output' => 'I'
);

$crm->getLeavePdf($params);

?>