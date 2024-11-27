<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];
//$date = date('Y-m-d');
$date = mysql_real_escape_string($_POST['date']);


// get Number of Techs Today
$sales = $crm->jGetSales($country_id,$date);
$techs = $crm->jGetNumOfTechToday($country_id,$date);
$jobs = $crm->jGetNumJobsCompleted($country_id,$date);

// needs to send via json
$arr = array(
	"sales"=>$sales,
	"techs"=>$techs,
	"jobs"=>$jobs
);
echo json_encode($arr);
?>