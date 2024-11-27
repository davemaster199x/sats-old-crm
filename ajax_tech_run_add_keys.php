<?php 

include('inc/init_for_ajax.php');

// data
$tech_run_id = mysql_real_escape_string($_POST['tech_run_id']);
$keys_agency = mysql_real_escape_string($_POST['keys_agency']);
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$country_id = mysql_real_escape_string($_POST['country_id']);
$agency_addresses_id = mysql_real_escape_string($_POST['agency_addresses_id']);

$params = array(
	'tech_run_id' => $tech_run_id,
	'keys_agency' => $keys_agency,
	'tech_id' => $tech_id,
	'date' => $date,
	'country_id' => $country_id,
	'agency_addresses_id' => $agency_addresses_id
);

techRunAddAgencyKeys($params);	
	


?>