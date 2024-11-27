<?php

include('inc/init_for_ajax.php');
include('inc/agency_class.php');

$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];
$country_id = $_SESSION['country_default'];

$job_id = $_POST['job_number'];
$alreadyExist = 0;



// check if credit request already exist (job id)
$jsql = mysql_query("
	SELECT *
	FROM `credit_requests`
	WHERE `job_id` = {$job_id}
	AND `deleted` = 0
	AND `active` = 1
	AND `country_id` = {$country_id}
");

if( mysql_num_rows($jsql)>0 ){
	
	$alreadyExist = 1;

}else{
	
	$alreadyExist = 0;
	
	$sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE j.`id` = {$job_id}
		AND a.`country_id` = {$country_id}
	");

	$row = mysql_fetch_array($sql);

	// get invoice number
	if(isset($row['tmh_id'])){
		$invoice_num = $row['tmh_id'];
	}else{
		$invoice_num = $row['id'];
	}

	// get amount
	$grand_total = getJobAmountGrandTotal($job_id,$country_id);
	
	
}





$arr = array( 
	"alreadyExist" => $alreadyExist,
	"invoice_num" => $invoice_num,
	"amount" => number_format($grand_total,2),
	"agency" => $row['agency_name']
	);
echo json_encode($arr);

?>

