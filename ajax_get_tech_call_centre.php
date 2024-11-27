<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$tech_id = mysql_real_escape_string($_REQUEST['tech']);

$params = array(
	'staff_id' => $tech_id
);
$cc_sql = $crm->getStaffAccount($params);
$cc = mysql_fetch_array($cc_sql);

$json_arrr = array(
	'other_call_centre' => $cc['other_call_centre'],
	'accomodation_id' => $cc['accomodation_id']
);

echo json_encode($json_arrr);

?>