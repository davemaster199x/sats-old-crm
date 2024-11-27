<?php

include('inc/init.php');

$user_type = $_SESSION['USER_DETAILS']['ClassID'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

$crm = new Sats_Crm_Class;


$from_date = ($_REQUEST['from_date']!='')?mysql_real_escape_string($_REQUEST['from_date']):'';
if($from_date!=''){
	$from_date2 = $crm->formatDate($from_date);
}
$to_date = ($_REQUEST['to_date']!='')?mysql_real_escape_string($_REQUEST['to_date']):'';
if($to_date!=''){
	$to_date2 = $crm->formatDate($to_date);
}


// file name
$filename = "expense_summary_".date("d/m/Y").".csv";

// send headers for download
header("Content-Type: text/csv");
header("Content-Disposition: Attachment; filename={$filename}");
header("Pragma: no-cache");

// headers
echo "Date of Purchase,Name,Card Used,Supplier,Description,Account,Entered By,Amount,Net Amt,GST,Gross Amt\n";

// get expense
if( $user_type==2 || $staff_id==2097 || $staff_id==2158 ){
	
	$jparams = array(
		'sort_list2' => array(
			array(
				'order_by' => 'exp.`date`',
				'sort' => 'DESC'
			),
			array(
				'order_by' => 'emp.`FirstName`',
				'sort' => 'ASC'
			),
			array(
				'order_by' => 'emp.`LastName`',
				'sort' => 'ASC'
			)
		),
		'filterDate' => array(
			'from' => $from_date2,
			'to' => $to_date2
		),
		'country_id' => $country_id
	);
	


}else{
	
	$jparams = array(
		'sort_list2' => array(
			array(
				'order_by' => 'exp.`date`',
				'sort' => 'DESC'
			),
			array(
				'order_by' => 'emp.`FirstName`',
				'sort' => 'ASC'
			),
			array(
				'order_by' => 'emp.`LastName`',
				'sort' => 'ASC'
			)
		),
		'filterDate' => array(
			'from' => $from_date2,
			'to' => $to_date2
		),
		'employee' => $loggedin_staff_id,
		'country_id' => $country_id
	);
	
}

$exp_sql = $crm->getExpenses($jparams);

// body
while($exp=mysql_fetch_array($exp_sql)){
	
	$dop = date('d/m/Y',strtotime($exp['date']));
	$emp_full = "{$exp['emp_fname']} {$exp['emp_lname']}";
	$card_used = $crm->getExpenseCards($exp['card']);
	$supplier =  $exp['supplier'];
	$desc = $exp['description'];
	$acc_name = $exp['account_name'];
	$eb_full = "{$exp['eb_fname']} {$exp['eb_lname']}";
	
	
	// get dynamic GST based on country
	$gst = $crm->getDynamicGST($exp['amount'],$country_id);
	$net_amount = $exp['amount']-$gst;
	
	$amount = "\$".$exp['amount'];
	$net_amount2 = "\$".number_format($net_amount,2);
	$gst2 = "\$".number_format($gst,2);
	$gross_amt = "\$".$exp['amount'];
	
	echo "\"{$dop}\",\"{$emp_full}\",\"{$card_used}\",\"{$supplier}\",\"{$desc}\",\"{$acc_name}\",\"{$eb_full}\",\"{$amount}\",\"{$net_amount2}\",\"{$gst2}\",\"{$gross_amt}\"\n";
}

?>