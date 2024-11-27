<?php 

include('inc/init_for_ajax.php');

// data
$tech_run_id = mysql_real_escape_string($_POST['tech_run_id']);
$supplier = mysql_real_escape_string($_POST['supplier']);
$country_id = mysql_real_escape_string($_POST['country_id']);

$params = array(
	'tech_run_id' => $tech_run_id,
	'supplier' => $supplier,
	'country_id' => $country_id
);

techRunAddSuppliers($params);	
	


?>