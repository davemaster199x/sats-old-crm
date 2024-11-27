<?php

require_once('inc/init.php');
require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdf_override_statements.php');

$agency_id = $_REQUEST['id'];
	
// instantiate class
$crm = new Sats_Crm_Class;

$date = date('Y-m-d');

// get agency
$jparams = array(
	'agency_id' => $agency_id,
	'join_table' => 'country'
);
$agency_sql = $crm->getAgency($jparams);
$agency = mysql_fetch_array($agency_sql);
$a_name = $agency['agency_name'];
$a_address1 = "{$agency['address_1']} {$agency['address_2']}";
$a_address2 = "{$agency['address_3']} {$agency['state']} {$agency['postcode']}";
$a_country_name = $agency['country'];



// start fpdf
$pdf=new jPDF('P','mm','A4');
$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
$pdf->setCountryData($cntry['country_id']);

$pdf->SetTopMargin(18);
$pdf->SetAutoPageBreak(true,10);
$pdf->AddPage();

// set default values
$header_width = 80;
$header_height = 6;
$header_border = 0;
$header_new_line = 1;
$header_align = 'T';

// agency name and address
$pdf->SetFont('Arial',null,10);
$x = $pdf->GetX();
$y = $pdf->GetY();
$agency_text = "{$a_name}
{$a_address1}
{$a_address2}
{$a_country_name}
";
$pdf->MultiCell($header_width, $header_height, $agency_text, $header_border,'L');


// Statement
$x = $header_width+25;
$pdf->SetXY($x,$y);
$pdf->SetFont('Arial','B',15);
$pdf->Cell($header_width, 7, 'STATEMENT', $header_border, 1, 'R');
$y = $pdf->GetY();



// Current as of 
$pdf->SetFont('Arial',null,12);
$pdf->SetXY($x,$y);
$pdf->Cell($header_width, $header_height, 'Current as of '.date('d/m/Y',strtotime($date)), $header_border, 1, 'R');
$y = $pdf->GetY();


$pdf->Ln(20);



$cell_height = 5;
$font_size = 8;

$col1 = 17;
$col1_ins = 22; 
$col2 = 38; 
$col3 = 100; 
$col4 = 33; 
$col5 = 15; 


// grey
$pdf->SetFillColor(211,211,211);
$pdf->SetFont('Arial','B',$font_size);
$pdf->Cell($col1,$cell_height,'Date',1,null,null,true);
$pdf->Cell($col1,$cell_height,'Invoice',1,null,null,true);
$pdf->Cell($col3,$cell_height,'Property',1,null,null,true);
$pdf->Cell($col5,$cell_height,'Charges',1,null,null,true);
$pdf->Cell($col5,$cell_height,'Payments',1,null,null,true);
$pdf->Cell($col5,$cell_height,'Credits',1,null,null,true);
$pdf->Cell($col5,$cell_height,'Balance',1,null,null,true);
$pdf->Ln();

$pdf->SetFont('Arial','',$font_size);


function getCredits($agency_id){
	$sql = "
		SELECT 
			j.`id` AS jid, 
			j.`date` AS jdate, 
			j.`invoice_amount`,
			j.`invoice_payments`,
			j.`invoice_credits`,
			j.`invoice_balance`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3
		FROM `jobs` AS j 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 		
		WHERE j.`id` > 0 
		AND p.`deleted` = 0 
		AND a.`status` = 'active' 
		AND j.`del_job` =0 
		AND p.`agency_id` = {$agency_id}
		AND j.`job_price` > 0 
		AND j.`invoice_balance` > 0
	";
	return mysql_query($sql);
}


// credits
$credits_sql = getCredits($agency_id);




while($row = mysql_fetch_array($credits_sql)){
	
	$jdate = ( $crm->isDateNotEmpty($row['jdate']) )?date('d/m/Y',strtotime($row['jdate'])):'';
	
	// append checkdigit to job id for new invoice number
	$check_digit = getCheckDigit(trim($row['jid']));
	$bpay_ref_code = "{$row['jid']}{$check_digit}";	
	
	$p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
	
	$invoice_amount = number_format($row['invoice_amount'],2);
	$invoice_payments = number_format($row['invoice_payments'],2);
	$invoice_credits = number_format($row['invoice_credits'],2);
	$invoice_balance = number_format($row['invoice_balance'],2);
	
	//$invoice_balance = $invoice_amount - $invoice_credits;
	
	if( $invoice_payments > 0 ){
		$invoice_payments_str = '$'.$invoice_payments;
	}else{
		$invoice_payments_str = '';
	}
	
	if( $invoice_credits > 0 ){
		$invoice_credits_str = '$'.$invoice_credits;
	}else{
		$invoice_credits_str = '';
	}
	
	$pdf->Cell($col1,$cell_height,$jdate,1);
	$pdf->Cell($col1,$cell_height,$bpay_ref_code,1);
	$pdf->Cell($col3,$cell_height,$p_address,1);
	$pdf->Cell($col5,$cell_height,'$'.$invoice_amount,1);		
	$pdf->Cell($col5,$cell_height,$invoice_payments_str,1);
	$pdf->SetTextColor(255,0,0);
	$pdf->Cell($col5,$cell_height,$invoice_credits_str,1);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell($col5,$cell_height,'$'.$invoice_balance,1);
	$pdf->Ln();
		
	
}


$pdf_filename = 'statements_'.date('dmYHis').'.pdf';
$pdf->Output($pdf_filename, 'I');
		

?>

