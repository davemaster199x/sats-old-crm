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
	
	$tenant_number = $c['tenant_number'];
	//$agent_number = $c['agent_number'];
	$letterhead_footer = $c['letterhead_footer'];

    #instantiate only if required
    if(!isset($pdf)) {
		
		$pdf=new FPDI();
        
        //$pdf=new jPDI();
		//$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
		//$pdf->setCountryData($_SESSION['country_default']);
    }

    $pdf->setMargins(35, 35, 35); // Left margin 3.5mm
    $pdf->addPage(); 

    $pdf->SetFont('Arial','',11); 

    // Tenant details
    $pdf->setY(50); 

    
	
	
	


	$pt_params = array( 
		'property_id' => $job_details['property_id'],
		'active' => 1
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		 // Tenant 
		$pdf->Cell(0,5, ucwords(strtolower($pt_row['tenant_firstname'])) . " " . ucwords(strtolower($pt_row['tenant_lastname'])),0,1);
		
	}
	
	
	
	//$pdf->Ln(7);
	
    //$pdf->Ln(1);
	
	//$pdf->setY(46); 

    // Property Address

	//macron fix
    setlocale(LC_CTYPE, 'en_US');
    $incov_val1 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_details['address_1']." ".$job_details['address_2']);
    $incov_val2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $job_details['address_3']." ".$job_details['state']);

    //$pdf->Ln(15.4);
    $pdf->Cell(0,5, $incov_val1,0,1);
    $pdf->Cell(180,5, $incov_val2 . ", " . $job_details['postcode'],0,1);

    // Greeting Line
    $pdf->Ln(15.4);
    


	$tenants_names_arr = [];

	$pt_params = array( 
		'property_id' => $job_details['property_id'],
		'active' => 1
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		$tenants_names_arr[] = $pt_row['tenant_firstname'];
		
	}
	
	if( count( $tenants_names_arr ) > 1 ){
		
		// Tenant 
		$tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
		$last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
		$tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)
		$pdf->Cell(0,0, "Dear ".$tenant_str);

		
	}else{
		
		$pdf->Cell(0,0, "Dear ".$tenants_names_arr[0]);
		
	}

	$tech_initial = substr($job_details['LastName'], 0, 1).'.';

	//$tech_name = "{$job_details['FirstName']} {$job_details['LastName']}";
	$tech_name = $job_details['FirstName'] . ' ' . $tech_initial;
	
	
    





	
	// Email Body
	if( CURRENT_COUNTRY == 1 ){ // AU

		// Immediate Access Required
		$pdf->Ln(13.4);
		$pdf->SetFont('', 'BU', 13);
		$pdf->Cell(0,0, "IMMEDIATE ACCESS REQUIRED", 0, 0, 'C');
		$pdf->SetFont('Arial','',11); 
		
		$pdf->Ln(15.4);
		$pdf->MultiCell(0,5, "Recently your Landlord and Property Manager have engaged the services of " . COMPANY_FULL_NAME . " to undertake Smoke Alarm Maintenance and Testing Services on the property you occupy.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, COMPANY_FULL_NAME . " needs to attend your property to undertake works on the installed smoke alarms to ensure the smoke alarms within the property are correctly working and compliant with legislation.");
		$pdf->Ln(5.4);
	
	
		$pdf->MultiCell(0,5, "Adhering to the Residential Tenancy Agreement 23.7 to carry out, or assess the need for, work relating to statutory health and safety obligations relating to the residential premises, if the tenant is given at least 2 days notice each time. ");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "SATS will be attending your property on ".(($job_details['date']!="")?$job_details['date']:'')." between {$job_details['time_of_day']}. We will be obtaining the keys from {$job_details['agency_name']} to carry out the service. Please call SATS on ".$tenant_number." if there are any issues.");
		
	}else if( CURRENT_COUNTRY == 2 ){ // NZ

		// Immediate Access Required
		$pdf->Ln(13.4);
		$pdf->SetFont('', 'BU', 13);
		$pdf->Cell(0,0, "NOTICE TO ENTER PREMISES - SMOKE ALARM INSPECTION", 0, 0, 'C');
	
		$pdf->SetFont('Arial','',10); 
		$pdf->Ln(10);
		$pdf->MultiCell(0,5, "I hereby give you notice that SATS will enter the above premises on {$job_details['date']} between {$job_details['time_of_day']}.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "Purpose of visit: To inspect/service/install smoke alarms as per The Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "As part of the Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016, landlords are required to ensure that there are correctly installed, maintained and fully operational smoke alarms in all residential rental properties.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "As per the request of {$job_details['agency_name']}, this notice is issued by Smoke Alarm Testing Services who, as the industry leader in smoke alarm servicing and maintenance, have been authorised to act as a Secondary Agent on behalf of the Landlord.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "The date and time frame of our attendance is detailed above. Our technician, {$tech_name} will collect the keys from your agency the morning of the inspection therefore there is no need to for you to be home when we attend the property. Our technicians are company employees who wear photo identification, drive sign written vehicles and have been extensively trained in customer service.");
		$pdf->Ln(5.4);
		$pdf->MultiCell(0,5, "Please call SATS on {$tenant_number} if there are any issues.");
		$pdf->Ln(5.4);	
		
	}
	

    

    // Yours Faithfully
    $pdf->Ln(10);
    $pdf->Cell(0,0, "Yours Faithfully,");

    // Signature (manually placed with padding ln())
    //$pdf->Image(__DIR__ . "/../images/signature-entrynotice.png", 34, $pdf->GetY()+3, 35); // Manually position image on PDF
    // $pdf->Image(__DIR__ . "/../images/DK_signature.png", 24, 185, 70); // Manually position image on PDF
    $pdf->Image(__DIR__ . "/../images/DK_signature.png", 24, $pdf->GetY()-5, 70); // Manually position image on PDF
    

    // SATS, number and agent name
	$pdf->Ln(30);
    $pdf->Cell(0,0, COMPANY_FULL_NAME.' ('.$tenant_number.')');
    //$pdf->Ln(7);
	//$pdf->Cell(0,0, $tenant_number);
    $pdf->Ln(7);
    $pdf->Cell(157,0, "Technician Attending: " . $tech_name);


	
    # If external PDF (linked from email) - add header and footer images
	$footer_img_path = $_SERVER['DOCUMENT_ROOT'].'/documents/';
    if(defined('EXTERNAL_PDF'))
    {

        if(COMPANY_ABBREV_NAME == "SATS")
        {
            //$pdf->Image($footer_img_path . 'cert_corner_img.png',110,0,100);
            //$pdf->Image($footer_img_path . $letterhead_footer,0,263,210); 

			$pdf->Image($_SERVER['DOCUMENT_ROOT']."documents/inv_cert_pdf_header.png",150,10,50);
			if( CURRENT_COUNTRY == 1 ){ // AU
				$image = 'documents/inv_cert_pdf_footer_au.png';
			}else if( CURRENT_COUNTRY == 2 ){ // NZ
				$image = 'documents/inv_cert_pdf_footer_nz.png';
			}
			$pdf->Image($_SERVER['DOCUMENT_ROOT'].$image,0,273,210);

			
        }
        else
        {
            //$pdf->Image($footer_img_path . 'cert_corner_img.png',0,0,210);
            //$pdf->Image($footer_img_path . $letterhead_footer,0,271.5,210);    
        }
    }
	