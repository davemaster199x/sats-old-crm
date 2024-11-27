<?php

include('inc/init_for_ajax.php');
include('inc/agency_class.php');

$agency_id = $_POST['agency_id'];
//$agency_id = 1490;

# invoke class
$agency_class = new Agency_Class();

$agency_sql = $agency_class->get_agency($agency_id);

$agency = mysql_fetch_array($agency_sql);

$arr = array( 
	"state"=>$agency['state'] ,
	"require_work_order"=>$agency['require_work_order']
	);
echo json_encode($arr);

?>

