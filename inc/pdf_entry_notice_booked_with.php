<?

    defined('SATS_INC') or die("Insufficient Permissions");

    #instantiate only if required
    if(!isset($pdf)) {
        
        $pdf=new FPDI();
    }


    // Use base PDF as a template, and make sure it is the full width of our PDF doc
    $pagecount = $pdf->setSourceFile(getcwd() .'/inc/templates/notice_to_enter_premises_ver2.pdf');
    $tplidx = $pdf->importPage(1, '/MediaBox');  

	$size = $pdf->getTemplateSize($tplidx);
	$pdf->AddPage('P', array(210, $size['h']+30));
    //$pdf->addPage(); 
    $pdf->useTemplate($tplidx, 0, 0, 210); 

    $pdf->SetFont('Arial','',11); 

	$x_pos = 28;
	
    // Tenant details
	$pdf->SetXY($x_pos, 49);

    // Tenant 1  
    $pdf->Cell(0,0, $booked_with_name);

    // Address L1
	$pdf->SetXY($x_pos, 71);
    $pdf->Cell(0,0, $job_details['address_1'] . " " . $job_details['address_2']." ".$job_details['address_3'] . " " . $job_details['state']);
	
	$y_pos = 98;
	// date
	$pdf->SetXY(32, $y_pos); 
	$pdf->Cell(0,0, date("d      m      Y",strtotime($job_details['jdate'])) );
	
	// time
	$pdf->SetXY(116, $y_pos); 
	$pdf->Cell(0,0, $job_details['time_of_day'] );
	
	// mark genuine purpose
	$pdf->SetXY(19.5, 161); 
	$pdf->Cell(0,0, 'X' );
	
	// purpose 
	$pdf->SetXY(32, 174); 
	$pdf->Cell(0,0, 'To test and service the Smoke Alarms located at this address' );

	$x_pos = 75;
	
	// signature
	$pdf->SetXY($x_pos, 183); 
	$pdf->Image(__DIR__ . "/../images/signature-entrynotice.png",null, null, 50); // Manually position image on PDF
	
	
	// date
	$pdf->SetXY(162, 196.5); 
	$pdf->Cell(0,0, date("d    m   Y",strtotime($job_details['jdate'])) );
	
	
	$cntry =  mysql_query("
		SELECT * 
		FROM  `countries` 
		WHERE `country_id` = {$_SESSION['country_default']}
	");
	$c = mysql_fetch_array($cntry);
	$pdf->SetXY($x_pos, 208); 
	$pdf->Cell(0,0, "Smoke Alarm Testing Services ({$c['agent_number']}) on behalf of" );
	
	// Agency
	$pdf->SetXY($x_pos, 220); 
	$pdf->Cell(0,0, "{$job_details['agency_name']} - {$job_details['agent_address_1']} {$job_details['agent_address_2']} {$job_details['agent_address_3']} {$job_details['agent_state']} {$job_details['agent_postcode']}" );
	
	// end 1ST pge
	//$pdf->endPage();
	
	// 2ND page
	$tplidx2 = $pdf->importPage(2, '/MediaBox');
	$pdf->addPage(); 
    $pdf->useTemplate($tplidx2, 0, 0, 210);
	//$pdf->endPage();
	
	
	/*
	
	// address of rental property
	$pdf->Ln(13.8);

    // 1 Notice Issued by
    $pdf->Ln(13.8);
    $pdf->Cell(94,0,"");     
    $pdf->Cell(10,0, "X");

    $pdf->Ln(9.8);
    $pdf->Cell(6,0,"");     
    $pdf->Cell(140,0, COMPANY_FULL_NAME);
    $pdf->Cell(30,0, COMPANY_TENANT_NUMBER);


    // 2 Details of all people entering (tech names) - Assuming only one tech
    $pdf->Ln(17);
    $pdf->Cell(9,0,"");     
    $pdf->Cell(137,0, $job_details['first_name'] . " " . $job_details['last_name']);
    $pdf->Cell(30,0, '1300 55 21 99');

    // 3 Notice Issued On - assume today
    $pdf->Ln(31);
    $pdf->Cell(7,0,"");     
    $pdf->Cell(49,0, date('l')); //eg Sunday
    $pdf->Cell(9,0, date('d')); // dd
    $pdf->Cell(9.2,0, date('m')); // mm
    $pdf->Cell(17,0, date('Y')); // yy

    if(defined("TYPE_POST"))
    {
        $pdf->Cell(15,0, "Post"); // Method of Issue
    }
    else
    {
        $pdf->Cell(15,0, "Email"); // Method of Issue
    }
    

    // 4 Entry is sought under the following grounds
    $pdf->SetY(187.2);
    $pdf->Cell(4.6,0,"");     
    $pdf->Cell(10,0, "X"); // Fire & Rescue Service Act

    $pdf->SetY(192.6);
    $pdf->Cell(4.6,0,"");     
    $pdf->Cell(10,0, "X"); // Smoke alarm act
    
    // 5 Entry to the premises by the lessor, agent or secondary agent - this is the booking date
    $pdf->SetY(245);
    $pdf->Cell(7,0,"");     
    $pdf->Cell(49,0, $job_details['booking_date_name']); // Smoke alarm act
    $pdf->Cell(9,0, $job_details['booking_date_day']); // dd
    $pdf->Cell(9.2,0, $job_details['booking_date_month']); // mm
    $pdf->Cell(17,0, $job_details['booking_date_year']); // yy

    # Prepare Time of Day (as best can)
    $tod_fixed = trim(preg_replace("/[^:.\-0-9\s]/", "", $job_details['time_of_day']));
    $tod_fixed = str_replace("-", " ", $tod_fixed);
    $tod_fixed = preg_replace("/\s{2,}/", " ", $tod_fixed);
    $tod_fixed = str_replace(":", ".", $tod_fixed);


    $tmp = explode(" ", $tod_fixed);
    $tod_start = number_format($tmp[0], 2);
    $tod_end = number_format($tmp[1], 2);

    $pdf->Cell(17,0, str_replace(".", ":", $tod_start)); // Time of Entry from
    if($tod_start < 12 && $tod_start >= 6)
    {
        $pdf->Cell(10,0, "X"); // AM
        $pdf->Cell(11,0, "");
    }
    elseif($tod_start < 8)
    {
        $pdf->Cell(11,0, "");
        $pdf->Cell(10,0, "X"); // PM
    }
    else
    {
        $pdf->Cell(21,0, ""); // None so skip
    }

    $pdf->Cell(8, 0, ""); // Padding

    $pdf->Cell(17,0, str_replace(".", ":", $tod_end)); // Time of Entry to
    if($tod_end < 12 && $tod_end >= 8)
    {
        $pdf->Cell(10,0, "X"); // AM
        $pdf->Cell(11,0, "");
    }
    elseif($tod_end < 8 || $tod_end >= 12)
    {
        $pdf->Cell(11,0, "");
        $pdf->Cell(10,0, "X"); // PM
    }
    else
    {
        $pdf->Cell(21,0, ""); // None so skip
    }

    // 6 Signature of the lessor, agent or secondary agent
    $pdf->Ln(27.8);
    $pdf->Cell(6,0,"");     
    $pdf->Cell(90,0, "Jeremy Batten");

    $pdf->Cell(67,0, ""); // Padding behind signature image

    $pdf->Image(__DIR__ . "/../images/signature-entrynotice.png", 110, 255, 50); // Manually position image on PDF

    $pdf->Cell(9,0, date('d')); // dd
    $pdf->Cell(8.0,0, date('m')); // mm
    $pdf->Cell(17,0, date('Y')); // yy
	*/

