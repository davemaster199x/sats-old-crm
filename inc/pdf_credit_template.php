<?

	defined('SATS_INC') or die("Insufficient Permissions");
	
	// AUTO - UPDATE INVOICE DETAILS
	$crm_pdf = new Sats_Crm_Class;
	$crm_pdf->updateInvoiceDetails($job_id);
	

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
	

	
	if( $job_details['show_as_paid']==1 || ( is_numeric($job_details['invoice_balance']) && $job_details['invoice_balance'] <= 0 && $job_details['invoice_payments'] > 0 ) ){
		$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/paid.png',55,180);
	}

	if( $is_copy == true ){
		$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/copy.png',10,10,30);
	}		
	
	// space needed to fit envelope
	//$pdf->Cell(20,10,'');
	
	// append checkdigit to job id for new invoice number
	$check_digit = getCheckDigit(trim($job_id));
	$bpay_ref_code = "{$job_id}";

	// CREDITS
	$credit_sql = mysql_query("
		SELECT *
		FROM `invoice_credits` AS ic 
		WHERE ic.`job_id` = {$job_id}
	");

	$credit_rows = mysql_num_rows($credit_sql);
	
	

    $pdf->SetFont('Arial','',11); 
   
    // $pdf->Cell(0,15,'',0,1,'C');
    // $pdf->Cell(0,5,'A.B.N.    48 160 538 741',0,1,'');
   
    // $pdf->Cell(0,10,'',0,1,'C');
  	//$pdf->Ln(18);

	
	$pdf->SetX(30);
    
	$pdf->Cell(70,5,'Credit Date:   ' . $job_details['date']); 
    
    $pdf->SetFont('Arial','B',14);
    
    if(isset($job_details['tmh_id']))
    {
    	$pdf->Cell(100,5,'Credit Note    #' . 'CR'.str_pad($job_details['tmh_id'] . ' TMH', 6, "0", STR_PAD_LEFT) . $credit_rows,0,1,'C');
    }
    else
    {
    	$pdf->Cell(100,5,'Credit Note    #' . 'CR'.$bpay_ref_code . $credit_rows,0,1,'C');
    }

    $pdf->SetFont('Arial','',11);
    // $pdf->Cell(40,5,'Invoice #' . str_pad($job_id, 6, "0", STR_PAD_LEFT)); 
    
    $pdf->Ln(5);
   
    # Agent Details
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	
	// space needed to fit envelope
	$pdf->Cell(20,10,'');
	
	# Hack for LJ Hooker Tamworth - display Landlord in different spot for them
	if( $property_details['landlord_firstname']!="" || $property_details['landlord_lastname']!='' ){
		$landlord_txt = "{$property_details['landlord_firstname']} {$property_details['landlord_lastname']}";
		$landlord_txt2 = "\n\nLANDLORD: {$landlord_txt}";
	}else{
		$landlord_txt = "LANDLORD";
		$landlord_txt2 = "";
	}
	
	
	// compass index number
	if( $property_details['compass_index_num'] != '' ){
		$compass_index_num = "\nINDEX NO.: {$property_details['compass_index_num']}";	
	}else{
		$compass_index_num = "\n";
	}
	

	$agency_address_txt = htmlspecialchars_decode("{$property_details['a_address_1']} {$property_details['a_address_2']}\n{$property_details['a_address_3']} {$property_details['a_state']} {$property_details['a_postcode']}");
	$property_address_txt = htmlspecialchars_decode("{$property_details['address_1']} {$property_details['address_2']}\n{$property_details['address_3']} {$property_details['state']} {$property_details['postcode']}");
	$workorder_txt = ($job_details['work_order'])?"\nWORK ORDER: {$job_details['work_order']}":"";
	
	$date_of_visit = ( $job_details['assigned_tech'] > 0 && $job_details['assigned_tech'] != 1 )?$job_details['date']:'N/A';

	# Hack for LJ Hooker Tamworth - display Landlord in different spot for them

	$append_str = null;
	// if agency "Agency Allows up front billing" to yes and job type is YM
	$is_upfront_billing = ( $job_details['allow_upfront_billing'] == 1 && $job_details['job_type'] == "Yearly Maintenance" )?true:false;

	if( $is_upfront_billing == true ){

		//4644 - Ray White Whitsunday
		//4637 - Vision Real Estate Mackay
		//6782 - Vision Real Estate Dysart 
		//4318 - first national mackay
		$spec_agen_arr = array(4644,4637,6782,4318);

		if( in_array($property_details['agency_id'], $spec_agen_arr) ){

			// month format
			$sub_start_period = date("F Y",strtotime($job_details['jdate']));;
			$sub_end_period = date("F Y",strtotime($job_details['jdate']."+ 11 months"));

		}else{

			// d/m/y format
			$sub_start_period = date("1/m/Y",strtotime($job_details['jdate']));;
			$sub_end_period = date("t/m/Y",strtotime($job_details['jdate']."+ 11 months"));		

		}

		$append_str = "Subscription Period {$sub_start_period} - {$sub_end_period}";
		
	}else{
		$append_str = "DATE OF VISIT: {$date_of_visit}";
	}
	
	

	if($property_details['agency_id'] == 1348){
		$pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
		$box1_h = $pdf->GetY();
		$pdf->SetY($curry);
		$pdf->SetX(124);
		$pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}",0,'L' );
		$box2_h = $pdf->GetY();	
		$pdf->Ln(6);
	}else if ($property_details['agency_id'] == 3079){	
		$pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n" . "\n\n{$append_str}");
		$box1_h = $pdf->GetY();
		$pdf->SetY($curry);
		$pdf->SetX(124);
		$pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
		$box2_h = $pdf->GetY();	
	}else{	
		$pdf->MultiCell(90, 5, "ATTN: {$landlord_txt}\n{$agency_address_txt}\n\n{$append_str}");
		$box1_h = $pdf->GetY();
		$pdf->SetY($curry);
		$pdf->SetX(124);
		$pdf->MultiCell(70, 5, "PROPERTY SERVICED:" . "\n{$property_address_txt}{$landlord_txt2}{$compass_index_num}{$workorder_txt}" ,0,'L');
		$box2_h = $pdf->GetY();	
	}
	
	#$pdf->SetX(0);
	
	// $pdf->setXY($curry,75);
	
	if($box1_h>$box2_h){
		$pdf->SetY($box1_h);
	}else{
		$pdf->SetY($box2_h);
	}
	
    $pdf->Ln();
	
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	$pdf->SetLineWidth(0.4);
	$pdf->Line($currx, $curry, $currx + 190, $curry);
	$pdf->Ln(1.5);
	
    $pdf->Cell(15,5,"Qty");
	$pdf->Cell(40,5,"Item");
	$pdf->Cell(85,5,"Description");
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
	// $pdf->Cell(15,5,"1", 0, 0, 'C');
	// $pdf->Cell(40,5,$job_details['job_type']);
	// $pdf->Cell(85,5,$os['type']);
	// $pdf->Cell(19,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
	// $pdf->Cell(31,5,"$".number_format($job_details['job_price'], 2), 0, 0, 'R');
	// $pdf->Ln();
	
	// $grand_total = $job_details['job_price'];
	
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
			
			// $pdf->SetFont('Arial','',11);
			// $pdf->Cell(15,5,"1", 0, 0, 'C');
			// $pdf->Cell(40,5,$alarm_details[$x]['alarm_pwr']);
			// $pdf->Cell(85,5,"Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm");
			// $pdf->Cell(19,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
			// $pdf->Cell(31,5,"$" . $alarm_details[$x]['alarm_price'], 0, 0, 'R');
			// $pdf->Ln();
			
			// $pdf->SetFont('Arial','I',11);
			// $pdf->Cell(15,5,"", 0, 0, 'C');
			// $pdf->Cell(40,5,"");
			// $pdf->SetTextColor(255, 0, 0); // red
			// $pdf->Cell(85,5,"Reason: " . $alarm_details[$x]['alarm_reason']);
			// $pdf->SetTextColor(0, 0, 0);
			// $pdf->Cell(19,5,"", 0, 0, 'R');
			// $pdf->Cell(31,5,"", 0, 0, 'R');
			// $pdf->Ln();
			
			// $grand_total += $alarm_details[$x]['alarm_price'];
		}
	}
	
	// surcharge
	$sc_sql = mysql_query("
		SELECT *, m.`name` AS m_name 
		FROM `agency_maintenance` AS am
		LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
		WHERE am.`agency_id` = {$property_details['agency_id']}
		AND am.`maintenance_id` > 0
	");
	$sc = mysql_fetch_array($sc_sql);
	if( $grand_total!=0 && $sc['surcharge']==1 ){
		
		// $pdf->SetFont('Arial','',11);
		// $pdf->Cell(15,5,"1", 0, 0, 'C');
		// $pdf->Cell(45,5,$sc['m_name']);
		// $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
		// $pdf->Cell(80,5,$surcharge_txt);
		// $pdf->Cell(19,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
		// $pdf->Cell(31,5,"$".number_format($sc['price'], 2), 0, 0, 'R');
		// $pdf->Ln();
		
		// $grand_total += $sc['price'];
		
	}
	
	
	// CREDITS
	$credit_sql = mysql_query("
		SELECT *
		FROM `invoice_credits` AS ic 
		WHERE ic.`job_id` = {$job_id}
	");
	$grand_total = 0;
	while( $credit = mysql_fetch_array($credit_sql) ){

		$item_credit_text = ($credit['credit_paid']<0) ? 'Credit - Reversal' : 'Credit' ;
		$credit_paid = ( $credit['credit_paid']<0 ) ? '$'.number_format(abs($credit['credit_paid']),2) : "$".number_format($credit['credit_paid'], 2) ;
		
		$pdf->SetFont('Arial','',11);
		$pdf->Cell(15,5,"1", 0, 0, 'C');
		$pdf->Cell(45,5,$item_credit_text);
		$pdf->SetFont('Arial','I',11);
		$pdf->SetTextColor(255, 0, 0); // red
		$pdf->Cell(80,5,'Reason: '.$crm_pdf->getInvoiceCreditReason($credit['credit_reason']));
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Arial','',11);
		/*
		$pdf->Cell(19,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
		$pdf->Cell(31,5,"-$".number_format($credit['credit_paid'], 2), 0, 0, 'R');
		*/
		$pdf->Cell(19,5,'('.$credit_paid.')', 0, 0, 'R');
		$pdf->Cell(31,5,'('.$credit_paid.')', 0, 0, 'R');


		$pdf->Ln();
		$grand_total += abs(floatval($credit['credit_paid']));
		
	}
	
	
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
	 
	$pdf->Ln(5);
	
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
	
	$curry = $pdf->GetY();
	$currx = $pdf->GetX();
	$pdf->SetLineWidth(0.4);
	$pdf->Line($currx, $curry, $currx + 190, $curry);
	$pdf->Ln(5);
	
	// get cursor position
	$cursor_y = $pdf->GetY();
	
	//SUB TOTAL
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(140,5,"", 0, 0, 'C');
	$text = 'Sub Total';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($grand_total - ($gst), 2), 0, 0, 'R');
	$pdf->Ln();

	//GST
	$pdf->Cell(140,5,"", 0, 0, 'C');
	$text = 'GST';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($gst, 2), 0, 0, 'R');	
	$pdf->Ln();

	//Total
	$pdf->Cell(140,5,"", 0, 0, 'C');
	$text = 'Total';
	$pdf->Cell(19,5,$text, 0, 0, 'R');
	$pdf->Cell(31,5,"$" . number_format($grand_total, 2), 'B', 0, 'R');
	$pdf->Ln();

	// Payments/Credits
	$pdf->Cell(140,10,"", 0, 0, 'C');
	$text = 'Payments';
	//$pdf->SetTextColor(255, 0, 0); // red
	$pdf->Cell(25,10,$text, 0, 0, 'R');
	$pdf->SetFont('Arial','B',12);
	// $inv_payments = $grand_total - $job_details['invoice_balance'];
	$pdf->Cell(25,10,'($'.number_format($grand_total, 2).')', 0, 0, 'R');
	//$pdf->SetTextColor(0, 0, 0); // clear red
	$pdf->Ln();
	
	
	// balance
	$pdf->SetFont('Arial','I',10);
	$pdf->Cell(140,10,"", 0, 0, 'C');
	$text = 'Amount Owing';
	$pdf->Cell(25,10,$text, 0, 0, 'R');
	$pdf->SetFont('Arial','B',12);
	// $inv_balance = ( is_numeric($job_details['invoice_balance']) )?$job_details['invoice_balance']:$grand_total;
	$pdf->Cell(25,10,'$'.number_format($grand_total, 2), 0, 0, 'R');
	$pdf->Ln();
	
		
	
	$x_pos = 10;
	$pdf->SetXY($x_pos,(($cursor_y)-1.3));	
	
	
	// BPAY AU only
	if( $c['country_id']==1 && $job_details['display_bpay']==1 ){
		
		// BPAY logo	
		$pdf->Image($_SERVER['DOCUMENT_ROOT'] . 'images/bpay/bpay_does_not_accept_credit_card.jpg',null,null,60);
		
		// set font 
		$pdf->SetFont('Helvetica','',11);
		$pdf->SetTextColor(24, 49, 104); // blue
		
		// Biller Code
		$bpay_x = $x_pos+38;
		$bpay_y = $cursor_y+4;
		$pdf->SetXY($bpay_x,$bpay_y);	
		$biller_code = '264291';
		$pdf->Cell(15,5,$biller_code, 0, 0, 'R');
		
		// Ref Code
		$pdf->SetXY($bpay_x-15,$bpay_y+4);
		//$ref_code = str_pad($job_id, 12, "0", STR_PAD_LEFT);	
		//$check_digit = getCheckDigit($job_id);
		//$bpay_ref_code = "{$job_id}{$check_digit}";
		$pdf->Cell(30,5,$bpay_ref_code, 0, 0, 'R');
		
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->SetXY($x_pos+62,$cursor_y);
		
		//$x_pos += 62;
		
	}
	
	
	
	
	
	
	 
	
	
	$x_pos = $pdf->getX();
	
	
	
	// Direct Deposit Details
	$pdf->SetXY($x_pos,($cursor_y+1));
	$pdf->SetFont('Arial','',10);
	
	
	$c_bank = $c['bank'];	
	$c_ac_name = $c['ac_name'];
	$c_ac_number = $c['ac_number'];
	
if($c['country_id']!=2){
	
$c_bsb = $c['bsb'];	
$pdf->MultiCell(55,5,"Direct Deposit Details:
Name: {$c_ac_name}
Bank: {$c_bank} 
BSB: {$c_bsb}
Account #: {$c_ac_number}
",0,'L');

}else{
	
$pdf->MultiCell(55,5,"Direct Deposit Details:
Name: {$c_ac_name}
Bank: {$c_bank} 
Account #: {$c_ac_number}
",0,'L');

}	

// Reference No.
$pdf->SetX($x_pos);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(22,5,'Reference #: ');
$pdf->SetTextColor(255, 0, 0); // red
$pdf->Cell(11,5,$bpay_ref_code,0,1);
$pdf->SetTextColor(0, 0, 0); // clear red
$pdf->SetFont('Arial','',10);

$pdf->SetX($x_pos);
$pdf->Cell(11,5,'Term:');
$pdf->SetFont('Arial','U',10);
$pdf->Cell(30,5,'NET 30 Days');	


	    
	
	
	
	$pdf->Ln(10);
	
	// if agency "Agency Allows up front billing" to yes and job type is YM
	if( $is_upfront_billing == true ){

		$pdf->SetFont('Arial','',10);
		$pdf->cell(190, 5, '* SATS will attend the property for Change of Tenancies and Lease Renewals as instructed by your agency to ensure');
		$pdf->Ln();
		$pdf->cell(190, 5, '  compliance is maintained throughout the subscription period.');
		$pdf->Ln();

	}	

	$pdf->SetFont('Arial','',11);
	$pdf->Ln(5);
	$pdf->Cell(160, 5, 'All SATS visits include:');	
	
	// if bundle, get bundle services id
	$ajt_serv_sql = getService($job_details['jservice']);
	$ajt_serv = mysql_fetch_array($ajt_serv_sql);
	
	// if bundle
	if($ajt_serv['bundle']==1){
	
		$bs_sql = mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `id` IN({$ajt_serv['bundle_ids']})
			AND `active` = 1
		");
		while($bs = mysql_fetch_array($bs_sql)){
			$pdf->Ln(10);
			$pdf->Cell(160, 5, $bs['type'].': ');
			getServiceIncludesDesc($pdf,$job_details['job_type'],$bs['id'],$country_id,$is_upfront_billing);
		}

	// not bundle
	}else{
		getServiceIncludesDesc($pdf,$job_details['job_type'],$job_details['jservice'],$country_id,$is_upfront_billing);
	}
	

$curr_y = $pdf->GetY();	
$pdf->SetY($curr_y+10);
$pdf->SetFont('Arial','',10);

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

/*
// smoke alarms
if( $job_details['jservice'] == 2 || $job_details['jservice'] == 12 ){
	
	if( $job_details['country_id']==1 ){ // AU
		$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above have been cleaned and tested as per manufacturers instructions and been installed in accordance with '.$country_text.' Standard AS 3786 (2014) Smoke Alarms, Building Code of '.$c['country'].', Volume 2 Part 3.7.2 of the National Construction code series (BCA) and AS 3000-2007 Electrical installations.'); 
	}else if( $job_details['country_id']==2 ){ // NZ
		$pdf->MultiCell(185,5,'All Smoke Alarms Located within the Property as detailed above have been cleaned and tested as per manufacturers instructions and in accordance with Australian Standard AS/NZ 3786 (2014) Smoke Alarms, and installed in accordance with NZS 4514, Building Code of New Zealand clause F7 Emergency Warning Systems 3.0, 3.3 and AS/NZ 3000-2007 Electrical installations.'); 
	}  
	$pdf->Ln(2);
	
}
*/

// if service type is IC dont show, only show for non-IC services
$ic_service = getICService();

if(in_array($job_details['jservice'], $ic_service)){
	$ic_check = 1;
}else{
	$ic_check = 0;
}

if( $ic_check == 0 && $job_details['state'] == 'QLD' && $job_details['qld_new_leg_alarm_num']>0 && $job_details['prop_upgraded_to_ic_sa'] != 1 ){

	if( $job_details['assigned_tech']!=NULL &&  $job_details['assigned_tech']!=1 && $job_details['assigned_tech']!=2){

		$pdf->SetTextColor(0, 0, 204);				
		// QUOTE
		$quote_qty = $job_details['qld_new_leg_alarm_num'];
		$price_240vrf = $crm_pdf->get240vRfAgencyAlarm($property_details['agency_id']);
		$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
		$quote_total = $quote_price*$quote_qty;
		$pdf->MultiCell(185,5,'We have provided a quote for $'.$quote_total.' to upgrade this property to meet the NEW QLD legislation. This quote is valid until '.date('d/m/Y',strtotime(str_replace('/','-',$job_details['date']).'+90 days')).' and available on the agency portal. To go ahead with this quote please contact SATS on '.$c['agent_number'].' or '.$c['outgoing_email']); 
		$pdf->SetTextColor(0, 0, 0);
	
	}

}
