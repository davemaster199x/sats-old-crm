<?

    defined('SATS_INC') or die("Insufficient Permissions");
	
	
	// get country id
	$c_sql = mysql_query("
		SELECT *
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
		WHERE j.`id` = {$job_details['id']}
	");
	$c = mysql_fetch_array($c_sql);
	
	$tenant_numer = $c['tenant_number'];
	

    #instantiate only if required
    if(!isset($pdf)) {
        
        $pdf=new FPDI();
    }


    // Use base PDF as a template, and make sure it is the full width of our PDF doc
    $pagecount = $pdf->setSourceFile(getcwd() .'/inc/templates/NSW_EN_Final_template.pdf');
    $tplidx = $pdf->importPage(1, '/MediaBox');  

    $pdf->addPage(); 
    $pdf->useTemplate($tplidx, 0, 0, 210); 

    $pdf->SetFont('Arial','',11); 

    // Tenant details
    $pdf->setY(44); 

	$pt_params = array( 
		'property_id' => $job_details['property_id'],
		'active' => 1,
		'paginate' => array(
			'offset' => 0,
			'limit' => 2
		),
		'echo_query' => 0
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	
	$pt_num_row = mysql_num_rows($pt_sql);
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		 // Tenant 
		$pdf->Cell(23,0,"");     
		//$pdf->Cell(0,0, $pt_row['tenant_firstname'] . " " . substr($pt_row['tenant_lastname'], 0, 1).'.');
		$pdf->Cell(0,0, $pt_row['tenant_firstname'] . " " .$pt_row['tenant_lastname']);
		$pdf->Ln(5.3);
		
	}
	
	// if only 1 tenant, but a blank space to preserve the spacing
	if( $pt_num_row == 1 ){
		 // Tenant 
		$pdf->Cell(23,0,"");     
		$pdf->Cell(0,0, "");
		$pdf->Ln(5.3);
	}elseif($pt_num_row <= 0){ //add another cell if no tenants to fixed alignement:GHERX
        $pdf->Cell(23,0,"");     
        $pdf->Cell(0,0, "");
        $pdf->Ln(5.3);
        $pdf->Cell(23,0,"");     
        $pdf->Cell(0,0, "");
		$pdf->Ln(5.3);
    }
	
    //macron fix
    setlocale(LC_CTYPE, 'en_US');
    $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_details['address_1']." ".$job_details['address_2']);
    $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_details['address_3']." ".$job_details['state']);

    // Address L1
    $pdf->Cell(23,0,"");     
    $pdf->Cell(0,0, $incov_val1);

    // Suburb and Postcode
    $pdf->Ln(5.3);
    $pdf->Cell(23,0,"");     
    $pdf->Cell(90,0, $incov_val2);
    $pdf->Cell(60,0, $job_details['postcode']);

	
	// 1 Address of the rental property
	//$pdf->Ln(12);

    // 2 Notice issued by
    $pdf->Ln(13.5);
    //$pdf->Cell(94,0,""); 
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+112.8,$y_pos-0.4);
    $pdf->Cell(8,0, "X");

	
    $pdf->Ln(9.1);
    //$pdf->Cell(6,0,""); 
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+16,$y_pos+0.8);
    $pdf->Cell(119,0, COMPANY_FULL_NAME);
    $pdf->Cell(30,0, $tenant_numer);


    // 3 Details of all people entering
    $pdf->Ln(19.3);
    //$pdf->Cell(9,0,"");     
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+16,$y_pos+0.8);
    $pdf->Cell(119,0, $job_details['FirstName'] . " " . substr($job_details['LastName'], 0, 1).'.');
    $pdf->Cell(30,0, $tenant_numer);
	

	 

    // 4 Notice issued on
    $pdf->Ln(25.3);
    //$pdf->Cell(7,0,"");    
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+16,$y_pos);
    $pdf->Cell(43,0, ( $job_details['en_date_issued'] != '' )?date('l',strtotime($job_details['en_date_issued'])):null ); //eg Sunday
    $pdf->Cell(9,0, ( $job_details['en_date_issued'] != '' )?date('d',strtotime($job_details['en_date_issued'])):null ); // dd
    $pdf->Cell(9.2,0, ( $job_details['en_date_issued'] != '' )?date('m',strtotime($job_details['en_date_issued'])):null ); // mm
    $pdf->Cell(28,0, ( $job_details['en_date_issued'] != '' )?date('Y',strtotime($job_details['en_date_issued'])):null ); // yy

	$pdf->Cell(15,0, "Email/SMS"); // Method of Issue
    
    /*
    // 5 Entry is sought under the following grounds 
    //$pdf->SetY(197.2);
    //$pdf->Cell(4.6,0,"");
	 $pdf->Ln(30.5);
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+4.5,$y_pos+2);
    $pdf->Cell(10,0, "X"); // Fire & Rescue Service Act
	
	$pdf->Ln();

    //$pdf->SetY(202.1);
    //$pdf->Cell(4.6,0,"");
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+4.5,$y_pos+8.5);
    $pdf->Cell(10,0, "X"); // Smoke alarm act
	*/
	$pdf->Ln(60.6);
    
    // 6 Entry to the property by the property owner/manager or other authorised person
    //$pdf->SetY(245);
    //$pdf->Cell(7,0,"");   
	$x_pos = $pdf->GetX();
	$y_pos = $pdf->GetY();
	$pdf->setXY($x_pos+16,$y_pos+11.5);
    $pdf->Cell(43,0, $job_details['booking_date_name']); // Smoke alarm act
    $pdf->Cell(9,0, $job_details['booking_date_day']); // dd
    $pdf->Cell(9.2,0, $job_details['booking_date_month']); // mm
    $pdf->Cell(28,0, $job_details['booking_date_year']); // yy

    # Prepare Time of Day (as best can)
    $tod_fixed = trim(preg_replace("/[^:.\-0-9\s]/", "", $job_details['time_of_day']));
    $tod_fixed = str_replace("-", " ", $tod_fixed);
    $tod_fixed = preg_replace("/\s{2,}/", " ", $tod_fixed);
    $tod_fixed = str_replace(":", ".", $tod_fixed);


    $tmp = explode(" ", $tod_fixed);
    $tod_start = number_format($tmp[0], 2);
    $tod_end = number_format($tmp[1], 2);
	
	// replace . with :
	$from_str = str_replace(".", ":", $tod_start);
	$to_str = str_replace(".", ":", $tod_end);
	
	// 12-hour time to 24-hour time 
	$time_in_24_hour_format  = date("H:i", strtotime("1:30 PM"));
	
	// From AM or PM
	if($tod_start < 12 && $tod_start >= 6){
        $from_ampm = 'AM';
    }else if($tod_start < 8){
		$from_ampm = 'PM';        
    }
	// To AM or PM
    if($tod_end < 12 && $tod_end >= 8){
        $to_ampm = 'AM';
    }else if($tod_end < 8 || $tod_end >= 12){
        $to_ampm = 'PM'; 
    }
	
	
    //$pdf->Cell(50,0, "{$from_str} {$from_ampm}"); // Time of Entry from
	
	$from_24_hour  = date("H:i", strtotime( "{$from_str} {$from_ampm}"));
	$to_24_hour  = date("H:i", strtotime( "{$to_str} {$to_ampm}"));

	
	// Two hour period
	$pdf->Cell(11,0, $from_24_hour);
	$pdf->Cell(3,0, "-");
	$pdf->Cell(11,0, $to_24_hour);
	
    

    // 6 Signature of the lessor, agent or secondary agent
    //$pdf->Ln(25.5);
    $pdf->setXY($x_pos+10,$y_pos+31);
    $pdf->Cell(6,0,"");     
    $pdf->Cell(89,0, "Daniel Kramarzewski");

    $pdf->Cell(9,0, date('d',strtotime($job_details['en_date_issued']))); // dd
    $pdf->Cell(8.0,0, date('m',strtotime($job_details['en_date_issued']))); // mm
    $pdf->Cell(17,0, date('Y',strtotime($job_details['en_date_issued']))); // yy


    $pdf->Cell(30,0, ""); // Padding behind signature image

    //$pdf->Image(__DIR__ . "/../images/signature-entrynotice.png", 38, 228, 50); // Manually position image on PDF
    $pdf->Image(__DIR__ . "/../images/DK_signature.png", 33, 221, 70); // Manually position image on PDF

