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

    
	// tenants
	$tenants_names_arr = [];
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
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		$tenants_names_arr[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
		
	}

	if( count( $tenants_names_arr ) > 1 ){
			
		$tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
		$last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
		$tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)		
		$pdf->Cell(0,0, $tenant_str);
		
	}else{
				
		$pdf->Cell(0,0, $tenants_names_arr[0]);
		
	}

	
    //macron fix
    setlocale(LC_CTYPE, 'en_US');
	$incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_details['address_1']." ".$job_details['address_2']." ".$job_details['address_3']." ".$job_details['state']);

    // Address L1
	$pdf->SetXY($x_pos, 71);
    //$pdf->Cell(0,0, $job_details['address_1'] . " " . $job_details['address_2']." ".$job_details['address_3'] . " " . $job_details['state']);
    $pdf->Cell(0,0, $incov_val1);
	
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
	//$pdf->Image(__DIR__ . "/../images/signature-entrynotice.png",null, null, 50); // Manually position image on PDF
	$pdf->Image(__DIR__ . "/../images/DK_signature.png",66, 175, 70); // Manually position image on PDF
	
	
	// date
	$pdf->SetXY(162, 196.5); 
	$pdf->Cell(0,0, date("d    m   Y",strtotime($job_details['en_date_issued'])) );
	
	
	$cntry =  mysql_query("
		SELECT * 
		FROM  `countries` 
		WHERE `country_id` = ".CURRENT_COUNTRY."
	");
	$c = mysql_fetch_array($cntry);
	$pdf->SetXY($x_pos, 208); 
	$pdf->Cell(0,0, "Smoke Alarm Testing Services ({$c['tenant_number']}) on behalf of" );
	
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
	
	
?>