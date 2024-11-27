<?

	defined('SATS_INC') or die("Insufficient Permissions");
	
	$crm = new Sats_Crm_Class;
	

	#instantiate only if required
	if(!isset($pdf)) {
		
		//$pdf=new FPDF('P','mm','A4');
		//include('fpdf_override.php');
		$pdf=new jPDF('P','mm','A4');
		$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
		$pdf->setCountryData($job_details['country_id']);
	}

	$pdf->SetTopMargin(40);
	$pdf->SetAutoPageBreak(true,50);
	$pdf->AddPage();
	
	/*
     # If external PDF (linked from email) - add header and footer images
	if(defined('EXTERNAL_PDF'))
	{

		if(COMPANY_ABBREV_NAME == "SATS")
		{
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);  
		}
		else
		{
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',0,0,210);
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,271.5,210);  
		}
	}else{
		if($print!=true){
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);
		}		 
	}
	*/
	
	
	
	
	// space needed to fit envelope
	//$pdf->Cell(20,10,'');
	
	// append checkdigit to job id for new invoice number
	$check_digit = getCheckDigit(trim($job_id));
	$bpay_ref_code = "{$job_id}{$check_digit}";
	
	

    $pdf->SetFont('Arial','',11); 
   
    // $pdf->Cell(0,15,'',0,1,'C');
    // $pdf->Cell(0,5,'A.B.N.    48 160 538 741',0,1,'');
   
    // $pdf->Cell(0,10,'',0,1,'C');
  	//$pdf->Ln(18);

	
	$pdf->SetX(30);
    
	$pdf->Cell(65.5,5,'Quote Date:   ' . $job_details['date']); 
    
    $pdf->SetFont('Arial','B',14);
    
    if(isset($job_details['tmh_id']))
    {
    	$pdf->Cell(100,5,'Quote    #' . str_pad($job_details['tmh_id'] . ' TMH-Q', 6, "0", STR_PAD_LEFT),0,1,'C');
    }
    else
    {
    	$pdf->Cell(100,5,'Quote    #' . $bpay_ref_code.'Q',0,1,'C');
    }

    $pdf->SetFont('Arial','',11);
    // $pdf->Cell(40,5,'Quote #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 
    
    $pdf->Ln(5);
   
    # Agent Details
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	
	// space needed to fit envelope
	$pdf->Cell(20,10,'');
	
	# Hack for LJ Hooker Tamworth - display Landlord in different spot for them
	
	#NZ macron char fixes
	setlocale(LC_CTYPE, 'en_US');
	$incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_details['address_1']." ".$property_details['address_2']);
	$incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $property_details['address_3']." ".$property_details['state']." ".$property_details['postcode']);


	if($property_details['agency_id'] == 1348) {
		$pdf->MultiCell(100, 5, "ATTN: " . ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'LANDLORD' ) . "\n" . $property_details['landlord_firstname'] . " " . $property_details['landlord_lastname'] . ( ( $property_details['landlord_firstname']=='' && $property_details['landlord_lastname']=='' )?"\nC/- {$property_details['agency_name']}":"" ) . "\n" . trim($property_details['a_address_1']). " " . trim($property_details['a_address_2']) . "\n" . trim($property_details['a_address_3']) . " " . $property_details['a_state'] . " " . $property_details['a_postcode'] . "\n\n\n");
		$pdf->SetY($curry);
		$pdf->SetX(124);
		$pdf->MultiCell(100, 5, "PROPERTY ADDRESS:" . "\n"  . $incov_val1 . "\n" . $incov_val2 . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
		$pdf->Ln(6);
	}
	else
	{
		$pdf->MultiCell(100, 5, "ATTN: ". ( ( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' )?"{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}":'LANDLORD' ) . ( ( $property_details['landlord_firstname']=='' && $property_details['landlord_lastname']=='' )?"\nC/- {$property_details['agency_name']}":"" ) . "\n" . $property_details['a_address_1']. " " . htmlspecialchars_decode($property_details['a_address_2']) . "\n" . $property_details['a_address_3'] . " " . $property_details['a_state'] . " " . $property_details['a_postcode']);
		$pdf->SetY($curry);
		$pdf->SetX(124);
		$pdf->MultiCell(100, 5, "PROPERTY ADDRESS:" . "\n"  . $incov_val1 . "\n" . $incov_val2 . "\n\n" . ( ($property_details['landlord_firstname']!='')?"LANDLORD: {$property_details['landlord_firstname']} {$property_details['landlord_lastname']}\n\n":"" )  );
			
	}
	
	$pdf->Ln(10);
	
	$pdf->SetFont('Arial','',10);

	$pdf->Cell(190, 5, 'This quote is to upgrade the above property to meet the new QLD Legislation (effective 1/1/2022).',0,1);
	
	
	$pdf->SetFont('Arial','',11);
	#$pdf->SetX(0);
	
    $pdf->Ln(6);
	
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	$pdf->SetLineWidth(0.4);
	$pdf->Line($currx, $curry, $currx + 190, $curry);
	$pdf->Ln(1.5);
	
    $pdf->Cell(15,5,"Qty");
	$pdf->Cell(45,5,"Item");
	$pdf->Cell(80,5,"Description");
	$pdf->Cell(25,5,"Unit Price");
	$pdf->Cell(25,5,"Total Amount");
	$pdf->Ln();
	
	$pdf->Cell(141,5,"");
	$pdf->Cell(27.5,5,"Inc. GST");
	$pdf->Cell(25,5,"Inc. GST");
	$pdf->Ln(6);
	
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	$pdf->SetLineWidth(0.4);
	$pdf->Line($currx, $curry, $currx + 190, $curry);
	$pdf->Ln(5);
	
	
	
	// get service
	$os_sql = getService($job_details['jservice']);	
	$os = mysql_fetch_array($os_sql);
	
	# Add Job Type
	$quote_qty = $job_details['qld_new_leg_alarm_num'];
	$price_240vrf = $crm->get240vRfAgencyAlarm($property_details['agency_id']);
	$ic_alarm_price = $crm->getIcAlarmAgencyService($property_details['agency_id']);
	
	$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
	$service_price = ( $ic_alarm_price > 0 )?$ic_alarm_price:119;
	
	$quote_total = $quote_price*$quote_qty;
	$pdf->Cell(15,5,$quote_qty, 0, 0, 'C');
	$pdf->Cell(45,5,'Photo-Electric');
	$pdf->Cell(80,5,'Interconnected Smoke Alarms');
	$pdf->Cell(19,5,"$".number_format($quote_total, 2), 0, 0, 'R');
	$pdf->Cell(31,5,"$".number_format($quote_total, 2), 0, 0, 'R');
	$pdf->Ln();
	
	$grand_total = $quote_total;
	
	/*
	// installed alarm
	for($x = 0; $x < $num_alarms; $x++)
	{
		if($alarm_details[$x]['new'] == 1)
		{
			#$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
			#$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
			#$pdf->Cell(25,5,"Expiry");
			#$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
			#$pdf->Ln();
			
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(15,5,"1", 0, 0, 'C');
			$pdf->Cell(45,5,$alarm_details[$x]['alarm_pwr']);
			$pdf->Cell(80,5,"Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm");
			$pdf->Cell(19,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Cell(31,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Ln();
			
			$pdf->SetFont('Arial','I',11);
			$pdf->Cell(15,5,"", 0, 0, 'C');
			$pdf->Cell(45,5,"");
			$pdf->SetTextColor(255, 0, 0); // red
			$pdf->Cell(80,5,"Reason: " . $alarm_details[$x]['alarm_reason']);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->Cell(19,5,"", 0, 0, 'R');
			$pdf->Cell(31,5,"", 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total += $alarm_details[$x]['alarm_price'];
		}
	}
	
	
	// removed alarm
	for($x = 0; $x < $numDelAlarm; $x++)
	{
			#$pdf->Cell(25,5,$alarm_details[$x]['alarm_pwr']);
			#$pdf->Cell(35,5,$alarm_details[$x]['alarm_type']);
			#$pdf->Cell(25,5,"Expiry");
			#$pdf->Cell(35,5,$alarm_details[$x]['expiry']);
			#$pdf->Ln();
			
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(15,5,"1", 0, 0, 'C');
			$pdf->Cell(45,5,$delAlarm[$x]['alarm_pwr']);
			$pdf->Cell(80,5,"Remove ".$delAlarm[$x]['alarm_type'] . " Smoke Alarm");
			$pdf->Cell(19,5,"$" . $delAlarm[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Cell(31,5,"$" . $delAlarm[$x]['alarm_price'], 0, 0, 'R');
			$pdf->Ln();
			
			$pdf->SetFont('Arial','I',11);
			$pdf->Cell(15,5,"", 0, 0, 'C');
			$pdf->Cell(45,5,"");
			$pdf->Cell(80,5,"Reason: " . $delAlarm[$x]['reason']);
			$pdf->Cell(19,5,"", 0, 0, 'R');
			$pdf->Cell(31,5,"", 0, 0, 'R');
			$pdf->Ln();
			
			$grand_total += $delAlarm[$x]['alarm_price'];
		
	}
	
	// surcharge
	$sc_sql = mysql_query("
		SELECT *, m.`name` AS m_name 
		FROM `agency_maintenance` AS am
		LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
		WHERE am.`agency_id` = {$property_details['agency_id']}
	");
	$sc = mysql_fetch_array($sc_sql);
	if( $grand_total!=0 && $sc['surcharge']==1 ){
		
		$pdf->SetFont('Arial','',11);
		$pdf->Cell(15,5,"1", 0, 0, 'C');
		$pdf->Cell(45,5,$sc['m_name']);
		$surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
		$pdf->Cell(80,5,$surcharge_txt);
		$pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
		$pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
		$pdf->Ln();
		
		$grand_total += $sc['price'];
		
	}
	*/
	
	
	//getServiceIncludesDesc($pdf,$job_details['job_type'],$job_details['jservice']);

    
	
	
	/*
	if($num_alarms > 0)
	{	
		$pdf->Cell(160, 5, $job_details['job_type'].' Includes:');
		$pdf->SetFont('Arial','',10);
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Surveying the quantity and location of smoke alarms');
		$pdf->Cell(160, 5, '* Inspecting alarms for secure fitting');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Replacing batteries in all alarms with replaceable batteries');
		$pdf->Cell(160, 5, '* Cleaning alarms with an anti-static wipe');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Testing alarms with the manual test button');
		$pdf->Cell(160, 5, '* Verifying expiry dates on all alarms');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Checking alarms for audible notification');
		$pdf->Cell(160, 5, '* Checking alarms for visual indicators');
		$pdf->Ln();
		$pdf->Cell(108, 5, '* Checking alarms meet Australian Standards');
		$pdf->Cell(160, 5, '* The recording of all details in SATS database');
		$pdf->SetFont('Arial','',11);
	}
	*/


	# Old Format
    /*
    $pdf->MultiCell(185,5,'Annual Maintenance Includes:
* Surveying the quantity and location of smoke alarms
* Inspecting alarms for secure fitting
* Cleaning alarms with an anti-static wipe
* Replacing batteries in all alarms with replaceable batteries
* Testing alarms with the manual test button
* Verifying expiry dates on all alarms
* Checking alarms for audible notification
* Checking alarms for visual indicators
* Checking alarms meet Australian Standards
* The recording of all details in SATS database');
	*/
	 
	$pdf->Ln(15);
	
	// get country
	$c_sql = mysql_query("
		SELECT *
		FROM `agency` AS a
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE a.`agency_id` = {$property_details['agency_id']}
	");
	$c = mysql_fetch_array($c_sql);
	
	// gst
	if($c['country_id']==1){
		$gst = $grand_total / 11;
	}else if($c['country_id']==2){
		$gst = ($grand_total*3) / 23;
	}	
	
	$pdf->MultiCell(190, 5, 'All current smoke alarms at the above address will be removed and replaced as part of this quote. This is to ensure all alarms will interconnect and no warranties will be void due to manufacturers recommendations and/or specifications.',0,1);
	$pdf->Ln(2);
	$pdf->MultiCell(190, 5, 'All alarms installed by SATS carry a manufacturers warranty of 5 Years and SATS offer an Additional 5 years warranty whilst part of a SATS service agreement.',0,1);
	$pdf->Ln(2);
	
	$pdf->Ln(2);
	$pdf->SetFont('Arial','I',10);
	$pdf->Cell(190, 5, '*After the upgrade has been carried out the Annual Maintenance fee will be $'.$service_price.' incl GST.',0,1,'C');
	$pdf->Ln(2);
	
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	$pdf->SetLineWidth(0.4);
	$pdf->Line($currx, $curry, $currx + 190, $curry);
	$pdf->Ln(5);
	
	// get cursor position
	$cursor_y = $pdf->GetY();
	
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(140,5,"", 0, 0, 'C');
	//$text = 'Sale Amount';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($grand_total - ($gst), 2), 0, 0, 'R');
	$pdf->Ln();

	$pdf->Cell(140,5,"", 0, 0, 'C');
	//$text = 'GST';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');	
	$pdf->Ln();

	
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(140,5,"", 0, 0, 'C');
	//$text = 'Quote Total';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($grand_total, 2), 0, 0, 'R');
	$pdf->Ln();
	
	
	
	$x_pos = 10;
	$pdf->SetXY($x_pos,$cursor_y);	
	    
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(190, 5, 'Quote valid until '.date("d/m/Y",strtotime(str_replace('/','-',$job_details['date']).'+90 days')),0,1);
	$pdf->Ln(30);
	$pdf->SetFont('Arial','',14);


	if( $job_details['prop_upgraded_to_ic_sa'] == 1 ){ // upgraded = yes

		$pdf->Cell(190, 5, 'No Upgrade required. Property meets NEW QLD Legislation',0,1,'C');

	}else{ //  upgraded = no

		$pdf->Cell(190, 5, 'To go ahead with the above quote please issue a work order to SATS',0,1,'C');

	}	

	
	
	
	
	
	
	
	
	
