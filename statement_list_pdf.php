<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_statements.php');

$agency_id = $_REQUEST['id'];
	
// instantiate class
$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];

$from = mysql_real_escape_string($_REQUEST['from']);
$from2 = ( $from != '' )?$crm->formatDate($from):'';
$to = mysql_real_escape_string($_REQUEST['to']);
$to2 = ( $to != '' )?$crm->formatDate($to):'';
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$phrase = mysql_real_escape_string($_REQUEST['phrase']);
$search_flag = mysql_real_escape_string($_REQUEST['search_flag']);

// static financial year 
$financial_year = '2019-07-01'; 
// unpaid marker
$unpaid_marker_str = '
OR(
	j.`unpaid` = 1 AND
	j.`invoice_balance` > 0
)
';


// get unpaid jobs and exclude 0 job price
$custom_filter = "
	AND j.`job_price` > 0 
	AND j.`invoice_balance` > 0
	AND j.`status` = 'Completed'
	AND (
		a.`status` = 'Active' OR
		a.`status` = 'Deactivated'
	)

	AND j.`date` >= '{$financial_year}'
	{$unpaid_marker_str}
";

$jparams = array(
	'custom_filter' => $custom_filter,
	
	'agency_id' => $agency_id,
	'phrase' => $phrase,
	
	'filterDate' => array(
		'from' => $from2,
		'to' => $to2
	),	
	'paginate' => array(
		'offset' => $offset,
		'limit' => $limit
	),
	'echo_query' => 0
);
$plist = $crm->getUnpaidJobs($jparams);





// start fpdf
$pdf=new jPDF('P','mm','A4');

$pdf->agency_id = $agency_id;
$pdf->to_date = $to;

$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
$pdf->setCountryData($country_id);

$pdf->SetTopMargin(10); // top margin
$pdf->SetAutoPageBreak(true,30); // bottom margin
$pdf->AliasNbPages();
$pdf->AddPage();





$cell_height = 5;
$font_size = 8;

$col1 = 17;
$col3 = 96; 
$col5 = 15; 




$pdf->SetFont('Arial','',$font_size);




$balance_tot = 0;
$not_overdue = 0;
$overdue_31_to_60 = 0;
$overdue_61_to_90 = 0;
$overdue_91_more = 0;

while($row = mysql_fetch_array($plist)){
	
	$jdate = ( $crm->isDateNotEmpty($row['jdate']) )?date('d/m/Y',strtotime($row['jdate'])):'';
	
	// append checkdigit to job id for new invoice number
	$check_digit = getCheckDigit(trim($row['jid']));
	$bpay_ref_code = "{$row['jid']}{$check_digit}";	
	
	$p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	
	$invoice_amount = number_format($row['invoice_amount'],2);
	$invoice_payments = number_format($row['invoice_payments'],2);
	$invoice_credits = number_format($row['invoice_credits'],2);
	
	$balance_tot += $row['invoice_balance'];
	$invoice_balance = number_format($row['invoice_balance'],2);
	
	if( $invoice_payments > 0 ){
		$invoice_payments_str = '$'.$invoice_payments;
	}else{
		$invoice_payments_str = '';
	}
	
	if( $invoice_credits > 0 ){
		$invoice_credits_str = '-$'.$invoice_credits;
	}else{
		$invoice_credits_str = '';
	}
	
	
	// Age
	$date1=date_create(date('Y-m-d',strtotime($row['jdate'])));
	$date2=date_create(date('Y-m-d'));
	$diff=date_diff($date1,$date2);
	$age = $diff->format("%r%a");
	$age_val = (((int)$age)!=0)?$age:0;
	
	
	if( $age_val <= 30 ){ // not overdue, within 30 days
		$not_overdue += $row['invoice_balance'];
	}else if( $age_val >= 31 && $age_val <= 60 ){ // overdue, within 31 - 60 days
		$overdue_31_to_60 += $row['invoice_balance'];
	}else if( $age_val >= 61 && $age_val <= 90 ){ // overdue, within 61 - 90 days
		$overdue_61_to_90 += $row['invoice_balance'];
	}else if( $age_val >= 91 ){ // overdue over 91 days or more
		$overdue_91_more += $row['invoice_balance'];
	}

	$url = $_SERVER['SERVER_NAME'];
	if($_SESSION['country_default']==1){ // AU
	
		if( strpos($url,"crmdev")===false ){ // live 
			$compass_fg_id = 39;
		}else{ // dev 
			$compass_fg_id = 34;
		}
		
	}
	
	$fg_id = $row['franchise_groups_id'];
	
	$pdf->Cell($col1,$cell_height,$jdate,1);
	$pdf->Cell($col1,$cell_height,$bpay_ref_code,1);
	if( $fg_id == $compass_fg_id ){ // compass only
		$pdf->Cell($col1,$cell_height,$row['compass_index_num'],1);
		$pdf->Cell($col3-18,$cell_height,$p_address,1);
		
	}else{
		$pdf->Cell($col3,$cell_height,$p_address,1);
	}	
	$pdf->Cell($col5,$cell_height,'$'.$invoice_amount,1);		
	$pdf->Cell($col5,$cell_height,$invoice_payments_str,1);
	$pdf->SetTextColor(255,0,0);
	$pdf->Cell($col5,$cell_height,$invoice_credits_str,1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($col5,$cell_height,'$'.$invoice_balance,1);
	$pdf->Ln();
		
	
}

/*
// test rows
for( $i=0; $i<50; $i++ ){
	$pdf->Cell($col1,$cell_height,'Test Rows',1);
	$pdf->Cell($col1,$cell_height,'$0',1);
	$pdf->Cell($col3,$cell_height,'$0',1);
	$pdf->Cell($col5,$cell_height,'$0',1);		
	$pdf->Cell($col5,$cell_height,'$0',1);
	$pdf->SetTextColor(255,0,0);
	$pdf->Cell($col5,$cell_height,'$0',1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($col5,$cell_height,'$0',1);
	$pdf->Ln();
}
*/







$x = $pdf->GetX();
$y = $pdf->GetY();


$pdf->setX(10);
$pdf->setY($y+3);


$cell_width = 38;
$cell_height = 7;
$cell_border = 1;
$cell_new_line = 0;
$cell_align = 'R';
$cell_change_txt_color = true;

$cell_height = 10;

// grey
$pdf->SetFillColor(238,238,238);
$pdf->SetFont('Arial','B',$font_size);

$cell_height = 5;
$pdf->Cell($cell_width, $cell_height, '0-30 days (Not Overdue)', $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '31-60 days OVERDUE', $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '61-90 days OVERDUE', $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '91+ days OVERDUE', $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, 'Total Amount Due', $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);

$pdf->Ln();


$pdf->SetFillColor(255,255,255);
$pdf->SetFont('Arial','',$font_size);
$pdf->Cell($cell_width, $cell_height, '$'.number_format($not_overdue,2), $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '$'.number_format($overdue_31_to_60,2), $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '$'.number_format($overdue_61_to_90,2), $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->Cell($cell_width, $cell_height, '$'.number_format($overdue_91_more,2), $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
// grey
$pdf->SetFillColor(238,238,238);
$pdf->SetFont('Arial','B',$font_size);
$pdf->Cell($cell_width, $cell_height, '$'.number_format($balance_tot,2), $cell_border, $cell_new_line, $cell_align,$cell_change_txt_color);
$pdf->SetFillColor(255,255,255);





$pdf_filename = 'statements_'.date('dmYHis').'.pdf';
$pdf->Output($pdf_filename, 'I');
		

?>

