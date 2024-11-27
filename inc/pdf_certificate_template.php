<?php

defined('SATS_INC') or die("Insufficient Permissions");

// AUTO - UPDATE INVOICE DETAILS
$crm_pdf = new Sats_Crm_Class;
$crm_pdf->updateInvoiceDetails($job_id);

// get service
$os_sql = getService($job_details['jservice']);	
$os = mysql_fetch_array($os_sql);
$property_job_types = getTechSheetAlarmTypesJob($job_details['property_id'], true);

#instantiate only if required
if(!isset($pdf)) {
	
	//$pdf=new FPDF('P','mm','A4');
	//include('fpdf_override.php');
	$pdf=new jPDF('P','mm','A4');
	$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
	$pdf->setCountryData($job_details['country_id']);
}

$pdf->SetTopMargin(50);
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
            $pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);  
        }
    }else{
		if($print!=true){
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_corner_img.png',110,0,100);
			$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'documents/cert_footer.png',0,263,210);
		}		
	}
*/

$pdf->SetFont('Arial','B',18); 
   
   // $pdf->Cell(0,36,'',0,1,'C');
   
    $pdf->Cell(0,5,'STATEMENT OF COMPLIANCE',0,1,'C');
$pdf->Cell(0,15,'',0,1,'C');
   
    $pdf->SetFont('Arial','B',11);

$pdf->Cell(45,5,"Real Estate Agent:");

$pdf->SetFont('Arial','',11);
$pdf->Cell(30,5,$property_details['agency_name']);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,5,"Property:");

$pdf->SetFont('Arial','',11);
$pdf->Cell(30,5,$property_details['address_1'] . " " . $property_details['address_2']);
$pdf->Ln();
$pdf->Cell(45,5,"");
$pdf->Cell(30,5,$property_details['address_3'] . " " . $property_details['state'] . ", " .$property_details['postcode'] );
$pdf->Ln(10);

// compass index number
if( $property_details['compass_index_num'] != '' ){
	
	$pdf->SetFont('Arial','B',11);
	$pdf->Cell(45,5,"Index No.");
	
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(45,5,$property_details['compass_index_num']);
	
	$pdf->Ln(10);
	
}

$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,5,"Type of Visit:");

$pdf->SetFont('Arial','',11);
$pdf->Cell(30,5,$job_details['job_type'].' '.$os['type']);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,5,"Date of Visit:");

$pdf->SetFont('Arial','',11);
$pdf->Cell(30,5,$job_details['date']);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(45,5,"Tested by:");

$pdf->SetFont('Arial','',11);
$pdf->Cell(55,5,$job_details['FirstName']);

// $pdf->SetFont('Arial','B',11);
// $pdf->Cell(38,5,"License Number:");

// $pdf->SetFont('Arial','',11);
// $pdf->Cell(30,5,$job_details['license_number']);

$pdf->Ln(15);


if($num_appliances > 0)
{
    $pdf->SetFont('Arial','B',11);

    $pdf->Cell(45,5,"Appliance Summary:");
    $pdf->Ln(10);


    $pdf->Cell(8, 5, "#");
    $pdf->Cell(20, 5, "Type");
    $pdf->Cell(36, 5, "Appliance");
    $pdf->Cell(36, 5, "Location");
    $pdf->Cell(22, 5, "Pass/Fail");
    $pdf->Cell(40, 5, "Reason");
    $pdf->Cell(65, 5, "Comments");
    $pdf->Ln(9);

    $pdf->SetFont('Arial','',10);

    for($x = 0; $x < $num_appliances; $x++)
    {

        $pdf->Cell(8, 2, $x + 1);
        $pdf->Cell(20, 2, $appliance_details[$x]['alarm_type']);
        $pdf->Cell(36, 2, $appliance_details[$x]['make']);
        $pdf->Cell(36, 2, $appliance_details[$x]['ts_location']);
        $pdf->Cell(22, 2, ($appliance_details[$x]['pass'] ? "Pass" : "Fail"));
        $pdf->Cell(40, 2, $appliance_details[$x]['alarm_reason']);
        $pdf->Cell(65, 2, $appliance_details[$x]['ts_comments']);
        $pdf->Ln(6);

    }

    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(25, 5, "Retest Date:");
    $pdf->Cell(15, 5, $job_details['retest_date']);

    $pdf->Ln(15);

    $pdf->SetFont('Arial','',9);
    $pdf->MultiCell(185,5,'All Appliances located within the property as detailed above are compliant with current legislation and Australian Standards. Appliances and leads are tested as per Manufacturers recommendations & the NSW Test and Tag requirements.');
    $pdf->Ln(10);
}


// if bundle, get bundle services id
$ajt_serv_sql = getService($job_details['jservice']);
$ajt_serv = mysql_fetch_array($ajt_serv_sql);

// bundle
if($ajt_serv['bundle']==1){	
	$bs_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` IN({$ajt_serv['bundle_ids']})
	");
// not bundle
}else{
	$bs_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$job_details['jservice']}
	");
}

/*
$bs_sql = mysql_query("
		SELECT *
		FROM `alarm_job_type`
		WHERE `id` = {$job_details['jservice']}
		AND `active` = 1
	");
*/

while($bs = mysql_fetch_array($bs_sql)){

	// smoke alarms
	if( $bs['id'] == 2 || $bs['id'] == 12 ){
		$pdf->Ln(2);
			$pdf->SetDrawColor(190,190,190);
			$pdf->SetLineWidth(0.05);
			$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

			$pdf->Ln(6);

		$pdf->SetFont('Arial','B',11);

		$pdf->Cell(45,5,"{$bs['type']} Summary:");
		$pdf->Ln(10);

		$ast_pos = 3;
		$hw_Position = 35;
		$hw_Power = 18;
		$hw_Type = 30;
		$hw_Make = 27;
		$hw_Model = 25;
		$hw_Expiry = 14;
		$hw_dB = 25;
		
		$pdf->Cell($ast_pos,5,"");
		$pdf->Cell($hw_Position,5,"Position");
		$pdf->Cell($hw_Power,5,"Power");
		$pdf->Cell($hw_Type,5,"Type");
		$pdf->Cell($hw_Make,5,"Make");
		$pdf->Cell($hw_Model,5,"Model");
		$pdf->Cell($hw_Expiry,5,"Expiry");
		$pdf->Cell($hw_dB,5,"dB");
		$pdf->Ln(9);

		$sa_font_size = 9;
		$pdf->SetFont('Arial','',$sa_font_size);

		$jalarms_sql = mysql_query("
			SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason  
			FROM alarm a 
				LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
				LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
				LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
			WHERE a.job_id = '" . $job_id . "'
			ORDER BY a.`ts_discarded` ASC, a.alarm_id ASC
		");
		$temp_alarm_flag = 0;
		while($jalarms = mysql_fetch_array($jalarms_sql))
		{
			// if reason: temporary alarm
			if( $jalarms['alarm_reason_id']==31 ){
				$temp_alarm_flag = 1;
			}

			// if discarded
			if($jalarms['ts_discarded']==1){
				$pdf->SetTextColor(255, 0, 0);
				$pdf->SetFont('Arial','I',$sa_font_size);
			}

			// if techsheet "Required for Compliance" = 0/No
			$append_asterisk = '';
			if( $jalarms['ts_required_compliance'] == 0 ){
				$append_asterisk = '*';
			}	

			$pdf->SetTextColor(255, 0, 0); // red
			$pdf->Cell($ast_pos,5,$append_asterisk);
			$pdf->SetTextColor(0, 0, 0); // clear red

			$pdf->Cell($hw_Position,5,$jalarms['ts_position']);
			$pdf->Cell($hw_Power,5,$jalarms['alarm_pwr']);
			$pdf->Cell($hw_Type,5,$jalarms['alarm_type']);
			$pdf->Cell($hw_Make,5,$jalarms['make']);
			$pdf->Cell($hw_Model,5,$jalarms['model']);
			$pdf->Cell($hw_Expiry,5,$jalarms['expiry']);		
			
			if($jalarms['ts_discarded']==1){
				$adr_sql = mysql_query("
					SELECT * 
					FROM `alarm_discarded_reason`
					WHERE `active` = 1
					AND `id` = {$jalarms['ts_discarded_reason']}
				");
				$adr = mysql_fetch_array($adr_sql);
				$pdf->Cell($hw_dB,5,'Removed - '.$adr['reason']);
			}else{
				$pdf->Cell($hw_dB,5,$jalarms['ts_db_rating']);
			}
			if($jalarms['ts_discarded']==1){
				$pdf->SetFont('Arial','',$sa_font_size);
				$pdf->SetTextColor(0, 0, 0);
			}
			$pdf->Ln();
		}

		$pdf->Ln(4);
			
		$c_sql = mysql_query("
			SELECT *
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
			WHERE j.`id` = {$job_details['id']}
		");	
		$c = mysql_fetch_array($c_sql);
		switch($c['country_id']){
			case 1:
				$country_text = 'Australian';
			break;
			case 2:
				$country_text = "New Zealand";
			break;
			case 3:
				$country_text = "Canadian";
			break;
			case 4:
				$country_text = "British";
			break;
			case 5:
				$country_text = "American";
			break;
			default:
				$country_text = 'Australian';
		}
			
		$pdf->SetFont('Arial','',10);
		if( $job_details['state'] == 'QLD' && $temp_alarm_flag==1 ){ // if QLD and temporary alarm
			$pdf->SetTextColor(255, 0, 0);
			$pdf->SetFont('Arial','I',10);
			$pdf->MultiCell(185,5,'Smoke alarms at the above property are NOT compliant with AS3786 (2014) and will need to be replaced when compliant smoke alarms become available. The property has working smoke alarms and the property is safe however they are not compliant, and SATS will revisit the property to make it compliant as soon as compliant alarms become available.'); 
			$pdf->SetFont('Arial','',10);
			$pdf->SetTextColor(0, 0, 0);
		}else{			
			
			if( $job_details['country_id']==1 ){ // AU
				$pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
			}else if( $job_details['country_id']==2 ){ // NZ
				$pdf->MultiCell(185,5,'All smoke alarms located within the property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations (where smoke alarms are hard-wired) and Residential Tenancies (Smoke Alarms and Insulation) Regulations 2016.'); 
			}  
			
		}
		

		$pdf->Ln(3);
		//$pdf->SetFont('Arial','',8);
		
		$pdf->SetTextColor(255, 0, 0); // red
		$pdf->Cell($ast_pos,5,'*');
		$pdf->SetTextColor(0, 0, 0); // clear red
		$pdf->MultiCell(185,5,'Not required for compliance'); 
		
	// safety switch
	}else if($bs['id'] == 5){
	
		$ssp_sql = mysql_query("
			SELECT `ts_safety_switch`, `ts_safety_switch_reason`, `ss_quantity`
			FROM `jobs`
			WHERE `id` = {$job_details['id']}
		");
		$ssp = mysql_fetch_array($ssp_sql);
	
		// if Safety Switch Present is no
		//if($ssp['ts_safety_switch']==1){
					
		
			$pdf->Ln(2);
			$pdf->SetDrawColor(190,190,190);
			$pdf->SetLineWidth(0.05);
			$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

			$pdf->Ln(6);

			$pdf->SetFont('Arial','B',11);

			$pdf->Cell(45,5,"{$bs['type']} Summary:");
			$pdf->Ln(10);
			
			//$pdf->Cell(30,5,"{$service} Headings");
			
			$pdf->Cell(30,5,"Make");
			$pdf->Cell(30,5,"Model");
			
			$pdf->Cell(30,5,"Test Date");
			$pdf->Cell(30,5,"Test Result");

			
			$pdf->Ln(9);
			
			
			
			$pdf->SetFont('Arial','',10);

			 //$pdf->Cell(30,5,"{$service} Data");
			
			$ss_sql = mysql_query("
				SELECT *
				FROM `safety_switch`
				WHERE `job_id` ={$job_details['id']}
				ORDER BY `make`
			");
			while($ss = mysql_fetch_array($ss_sql))
			{
				
				

				$pdf->Cell(30,5,$ss['make']);
				$pdf->Cell(30,5,$ss['model']);
				$pdf->Cell(30,5,$job_details['date']);
				if($ss['test']==1){ // pass
					$pdf->Cell(30,5,'Pass');
				}else if( is_numeric($ss['test']) && $ss['test']==0 ){ // fail
					$pdf->SetTextColor(255, 0, 0); // red
					$pdf->Cell(30,5,'Fail');
					$pdf->SetTextColor(0, 0, 0); 
				}else if($ss['test']==2){ // no power
					$pdf->Cell(30,5,'No Power to Property at time of testing');
				}else if($ss['test']==3){ // not tested
					$pdf->Cell(30,5,'Not Tested');
				}else if($ss['test']==''){
					$pdf->Cell(30,5,'Not Tested');
				}
				
				$pdf->Ln();
			}
			
			
			// Fusebox Viewed
			$pdf->Ln(4);
			$pdf->SetFont('Arial','B',11);			
			$pdf->Cell(40,5,"Fusebox Viewed:");
			$pdf->SetFont('Arial','',10);
			$pdf->Cell(15,5,($ssp['ts_safety_switch']==2)?'Yes':'No');
			
			// Fusebox Viewed - Yes
			if($ssp['ts_safety_switch']==2){
			
				// Safety Switch Quantity						
				$pdf->SetFont('Arial','B',11);			
				$pdf->Cell(60,5,"Number of Safety Switches:");
				$pdf->SetFont('Arial','',10);
				$pdf->Cell(30,5,$ssp['ss_quantity']);
			
			// Fusebox Viewed - No
			}else if($ssp['ts_safety_switch']==1){
			
			
				// reason				
				$pdf->SetFont('Arial','B',11);			
				$pdf->Cell(18,5,"Reason:");
				$pdf->SetFont('Arial','',10);
				switch($ssp['ts_safety_switch_reason']){
					case 0:
						$ssp_reason = 'Circuit Breaker Only';
					break;
					case 1:
						$ssp_reason = 'Unable to Locate';
					break;
					case 2:
						$ssp_reason = 'Unable to Access';
					break;
				}
				$pdf->Cell(30,5,$ssp_reason);
			
			}
			
			
			
			
			

			$pdf->Ln(11);
				
			$pdf->SetFont('Arial','',10);
			
			
			//$pdf->MultiCell(185,5,"{$service} Compliance Statement");
			//$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above are Compliant with Current Legislation and Australian Standards. Smoke Alarms are installed as per Manufacturers Recommendations & the Building Code of Australia.'); 
			
			if($ssp['ss_quantity']==0){
				$pdf->SetTextColor(255,0,0);
				$pdf->MultiCell(185,5,'No Safety Switches Present. We strongly recommend a Safety Switch be installed to protect the occupants.'); 
				$pdf->SetTextColor(0,0,0);
			}else{				
				//$pdf->MultiCell(185,5,'All Safety Switches listed above have been Mechanically Tested and pass a basic mechanical test, to assess they are in working order. No test has been performed to determine the speed at which the device activated.'); 				
				$pdf->MultiCell(185,5,'All Safety Switches listed above have been Mechanically Tested to assess they are in working order. No test has been performed to determine the speed at which the device activated or whether all circuits in the home are connected to a Safety Switch.'); 				
			}
			
			
			
		
		//}
	
		
		
	// corded windows
	}else if($bs['id'] == 6){
		$pdf->Ln(2);
			$pdf->SetDrawColor(190,190,190);
			$pdf->SetLineWidth(0.05);
			$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

			$pdf->Ln(6);

		$pdf->SetFont('Arial','B',11);

		$pdf->Cell(45,5,"{$bs['type']} Summary:");
		$pdf->Ln(10);

		$pdf->SetFont('Arial','',10);
		while( $cw = mysql_fetch_array($cw_sql) ){
			$num_windows_total += $cw['num_of_windows'];
			$pdf->Cell(30,5,$cw['location']);
			$pdf->Cell(30,5,$cw['num_of_windows'],0,1);
		}

		$pdf->Ln(5);
		$pdf->MultiCell(185,5,'All Corded Windows within the Property as detailed above are Compliant with Current Legislation and '.$country_text.' Standards. The Required Clips and Tags have been installed to ensure proper compliance with Current Legislation. Further data is available on the agency portal'); 
		$pdf->Ln(3);
		$pdf->SetFont('Arial','',8);
		
		
	// water meter
	}else if($bs['id'] == 7){
		$pdf->Ln(2);
			$pdf->SetDrawColor(190,190,190);
			$pdf->SetLineWidth(0.05);
			$pdf->Line(10, $pdf->getY(), 200, $pdf->getY());

			$pdf->Ln(6);

		$pdf->SetFont('Arial','B',11);

		$pdf->Cell(45,5,"{$bs['type']} Summary:");
		$pdf->Ln(10);

		$pdf->Cell(30,5,"Reading");
		$pdf->Cell(30,5,"Location");
		

		$pdf->Ln(9);
		

	
		$pdf->SetFont('Arial','',10);
		$wm_sql = getWaterMeter($job_details['id']);
		while($wm = mysql_fetch_array($wm_sql))
		{
			$pdf->Cell(30,5,$wm['reading']);
			$pdf->Cell(30,5,$wm['location']);			
			$pdf->Ln();
		}
		

		$pdf->Ln(4);
			
		$pdf->SetFont('Arial','',10);
		//$pdf->MultiCell(185,5,"{$service} Compliance Statement");
		//$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above are Compliant with Current Legislation and Australian Standards. Smoke Alarms are installed as per Manufacturers Recommendations & the Building Code of Australia.'); 
		//$pdf->Ln(3);
		//$pdf->SetFont('Arial','',8);
		
	}
	
}


$pdf->Ln(2);
$pdf->SetFont('Arial','',10);


// if service type is IC dont show, only show for non-IC services
$ic_service = getICService();

if(in_array($job_details['jservice'], $ic_service)){
	$ic_check = 1;
}else{
	$ic_check = 0;
}

if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

	$pdf->SetTextColor(0, 0, 204);				
	// QUOTE
	$quote_qty = $job_details['qld_new_leg_alarm_num'];
	$price_240vrf = $crm_pdf->get240vRfAgencyAlarm($property_details['agency_id']);
	$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
	$quote_total = $quote_price*$quote_qty;
	$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
	$pdf->SetTextColor(0, 0, 0);
	

}
