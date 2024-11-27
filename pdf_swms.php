<?php

include('inc/init_for_ajax.php');

require_once('inc/fpdf/fpdf.php');
require_once('inc/fpdi-1.4.4/fpdi.php');

$crm = new Sats_Crm_Class;

$job_id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
$swms_type = filter_var($_GET['swms_type'], FILTER_SANITIZE_STRING);

if( $job_id!='' ){
	
	$jparams = array( 'job_id' => $job_id );
	$jsql = $crm->getJobsData($jparams);
	$job = mysql_fetch_array($jsql);


	#instantiate only if required
	if(!isset($pdf)) {
		$pdf=new FPDI();
	}
	
	$x_pos = 69;
	$y_pos = 48;

	switch($swms_type){
		case 'heights':
			$swms_pdf = 'SWMS heights.pdf';
		break;
		case 'uv_protection':
			$swms_pdf = 'SWMS UV protection.pdf';
		break;
		case 'asbestos':
			$swms_pdf = 'SWMS asbestos.pdf';
		break;
		case 'powertools':
			$swms_pdf = 'SWMS powertools.pdf';
		break;
		case 'animals':
			$swms_pdf = 'SWMS Animals.pdf';
		break;
		case 'live_circuits':
			$swms_pdf = 'SWMS Isolating circuits.pdf';
		break;
	}
	
	$pagecount = $pdf->setSourceFile(getcwd() .'/technician_documents/au/'.$swms_pdf);
	$tplidx = $pdf->importPage(1, '/MediaBox');  

	$pdf->addPage(); 
	$pdf->useTemplate($tplidx, 0, 0, 210);


	$pdf->SetFont('Arial','',11); 
	
	
	// Work Location
	$pdf->setXY($x_pos,$y_pos); 
	$pdf->Cell(21,0,"");     
	$pdf->Cell(0,0, $job['p_address_1']." ".$job['p_address_2']." ".$job['p_address_3']); 
	
	// Person responsible for ensuring compliance with SWMS
	$pdf->setXY($x_pos,$y_pos+30);  
	$pdf->Cell(21,0,"");     
	$pdf->Cell(0,0, $job['first_name']." ".$job['last_name']); 	
	 
	// Workers Name
	$pdf->setXY($x_pos,$y_pos+53); 
	$pdf->Cell(21,0,"");     
	$pdf->Cell(0,0, $job['first_name']." ".$job['last_name']); 
	
	// Date Received
	$pdf->setXY($x_pos,$y_pos+58); 
	$pdf->Cell(21,0,"");     
	$pdf->Cell(0,0, date('d/m/Y',strtotime($job['date']))); 

	$pdf->Output($swms_type.'.pdf', 'I');
	
}


?>