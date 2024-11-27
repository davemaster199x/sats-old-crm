<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_debtors.php');

$agency_id = $_REQUEST['id'];
$order_by = ($_REQUEST['order_by']!="")?$_REQUEST['order_by']:'a.`agency_name`';
$sort = ($_REQUEST['sort']!="")?$_REQUEST['sort']:'ASC';
	
// instantiate class
$crm = new Sats_Crm_Class;

//$date = date('Y-m-d');

// get agency details
$country_id = $_SESSION['country_default'];



// start fpdf
$pdf=new jPDF('P','mm','A4');
$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
$pdf->setCountryData($country_id);

$pdf->SetTopMargin(10); // top margin
$pdf->SetAutoPageBreak(true,30); // bottom margin
$pdf->AliasNbPages();
$pdf->AddPage();





$cell_height = 5;
$font_size = 8;

$col1 = 80;
$col2 = 22; 






$pdf->SetFont('Arial','',$font_size);


$custom_select = "
	SUM(j.`invoice_balance`) AS invoice_balance_tot, a.`agency_name`, a.`agency_id`
";

// get unpaid jobs and exclude 0 job price
$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)
";

$jparams = array(
	'custom_select' => $custom_select,
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),	
	'group_by' => 'a.`agency_id`',
	'sort_list' => array(
		array(
			'order_by' => $order_by,
			'sort' => $sort
		)
	),
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$credits_sql = $crm->getUnpaidJobs($jparams);



$not_overdue_tot = 0;
$overdue_31_to_60_tot = 0;
$overdue_61_to_90_tot = 0;
$overdue_91_more_tot = 0;
$invoice_balance_tot = 0;
$invoice_balance_tot = 0;

while($row = mysql_fetch_array($credits_sql)){
	
	$not_overdue = $crm->getOverdueTotal($row['agency_id'],'DateDiff <= 30');
	$overdue_31_to_60 = $crm->getOverdueTotal($row['agency_id'],'DateDiff BETWEEN 31 AND 60');
	$overdue_61_to_90 = $crm->getOverdueTotal($row['agency_id'],'DateDiff BETWEEN 61 AND 90');
	$overdue_91_more = $crm->getOverdueTotal($row['agency_id'],'DateDiff >= 91');
	$invoice_balance = $row['invoice_balance_tot'];
	
	$pdf->Cell($col1,$cell_height,$row['agency_name'],1);
	$pdf->Cell($col2,$cell_height,'$'.number_format($not_overdue,2),1);	
	$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_31_to_60,2),1);	
	$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_61_to_90,2),1);		
	$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_91_more,2),1);
	$pdf->Cell($col2,$cell_height,'$'.number_format($invoice_balance,2),1);
	$pdf->Ln();
	
	// sum
	$not_overdue_tot += $not_overdue;
	$overdue_31_to_60_tot += $overdue_31_to_60;
	$overdue_61_to_90_tot += $overdue_61_to_90;
	$overdue_91_more_tot += $overdue_91_more;
	$invoice_balance_tot += $invoice_balance;
		
	
}

/*
// test rows
for( $i=0; $i<50; $i++ ){
	$pdf->Cell($col1,$cell_height,'Test Rows',1);
	$pdf->Cell($col2,$cell_height,'$0',1);	
	$pdf->Cell($col2,$cell_height,'$0',1);	
	$pdf->Cell($col2,$cell_height,'$0',1);		
	$pdf->Cell($col2,$cell_height,'$0',1);
	$pdf->Cell($col2,$cell_height,'$0',1);
	$pdf->Ln();
}
*/

$x = $pdf->GetX();
$y = $pdf->GetY();


$pdf->setX(10);

$cell_width = 38;
//$cell_height = 7;
$cell_border = 1;
$cell_new_line = 0;
$cell_align = 'R';
$cell_change_txt_color = true;

//$cell_height = 10;

// grey
$pdf->SetFillColor(238,238,238);
$pdf->SetFont('Arial','B',$font_size);
$pdf->Cell($col1,$cell_height,'Total',1,null,null,true);
$pdf->Cell($col2,$cell_height,'$'.number_format($not_overdue_tot,2),1,null,null,true);
$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_31_to_60_tot,2),1,null,null,true);
$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_61_to_90_tot,2),1,null,null,true);
$pdf->Cell($col2,$cell_height,'$'.number_format($overdue_91_more_tot,2),1,null,null,true);
$pdf->Cell($col2,$cell_height,'$'.number_format($invoice_balance_tot,2),1,null,null,true);
$pdf->Ln();


$not_overdue_perc = ($not_overdue_tot / $invoice_balance_tot) * 100;
$overdue_31_to_60_perc = ($overdue_31_to_60_tot / $invoice_balance_tot) * 100;
$overdue_61_to_90_perc = ($overdue_61_to_90_tot / $invoice_balance_tot) * 100;
$overdue_91_more_perc = ($overdue_91_more_tot / $invoice_balance_tot) * 100;

$pdf->SetFont('Arial','B',$font_size);
$pdf->Cell($col1,$cell_height,'Ageing Percentage',1,null,null,true);
$pdf->Cell($col2,$cell_height,number_format($not_overdue_perc).'%',1,null,null,true);
$pdf->Cell($col2,$cell_height,number_format($overdue_31_to_60_perc).'%',1,null,null,true);
$pdf->Cell($col2,$cell_height,number_format($overdue_61_to_90_perc).'%',1,null,null,true);
$pdf->Cell($col2,$cell_height,number_format($overdue_91_more_perc).'%',1,null,null,true);
$pdf->Cell($col2,$cell_height,'100%',1,null,null,true);
$pdf->Ln();


$pdf_filename = 'statements_'.date('dmYHis').'.pdf';
$pdf->Output($pdf_filename, 'I');
		

?>

