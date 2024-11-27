<?php

include('inc/init.php');

$query = "
SELECT j.id FROM jobs j, property p, agency a
WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Merged Certificates') AND p.deleted = 0 AND a.`country_id` = {$_SESSION['country_default']}";

$jobs = mysqlMultiRows($query);

header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=myob_import_" . date('m-d-Y') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");


function currencyFormatNoComma($job_price){
	return number_format($job_price,2,'.','');
}

if($_SESSION['country_default']==1){
	$invoice_heading = 'Invoice #';
	$inc_tax_price = 'Inc-Tax Price';
	$inc_tax_total = 'Inc-Tax Total';
	$tax_code = 'Tax Code';
}else{
	$invoice_heading = 'Invoice No.';
	$inc_tax_price = 'Inc-GST Price';
	$inc_tax_total = 'Inc-GST Total';
	$tax_code = 'GST Code';
}

echo "Co./Last Name,First Name,Addr 1 - Line 1,           - Line 2,           - Line 3,           - Line 4,Inclusive,{$invoice_heading},Date,Delivery Status,Item Number,Quantity,Description,Price,{$inc_tax_price},Discount,Total,{$inc_tax_total},Journal Memo,Salesperson Last Name,Salesperson First Name,{$tax_code},Non-GST Amount,GST Amount,LCT Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount,Freight GST Amount,Freight LCT Amount,Sale Status,Terms - Payment is Due,           - Discount Days,           - Balance Due Days,           - % Discount,           - % Monthly Charge,Amount Paid\n";








foreach($jobs as $job)
{

	$invoice_total = 0;
	$job_id = $job['id'];
	
	// append checkdigit to job id for new invoice number
	$check_digit = getCheckDigit(trim($job_id));
	$bpay_ref_code = "{$job_id}{$check_digit}";	
	
	if(!is_numeric($job_id)) exit();
	
	# Job Details
	$query = "SELECT j.job_type, j.property_id, DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.job_price, j.price_used, t.description, j.work_order FROM jobs j
	 		  LEFT JOIN job_type t ON t.job_type = j.job_type WHERE j.id = '" . $job_id . "'";
	$job_details = mysqlSingleRow($query);
	
	# Alarm Details
	$query = "SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason 
	FROM alarm a 
	LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
	LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
	LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
	WHERE a.job_id = '" . $job_id . "'";
	$alarm_details = mysqlMultiRows($query);
	$num_alarms = sizeof($alarm_details);
	
	# Property + Agent Details
	$query = "SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.landlord_lastname, p.landlord_firstname, a.agency_name,
	a.address_1 AS a_address_1, a.address_2 AS a_address_2, a.address_3 AS a_address_3, a.state AS a_state, a.postcode  AS a_postcode, p.price, s.FirstName, s.LastName, a.`agency_id`
	FROM property p 
	LEFT JOIN agency a ON p.agency_id = a.agency_id
	LEFT JOIN staff_accounts s ON s.StaffID = a.salesrep
	WHERE p.property_id = '" . $job_details['property_id'] . "'";
	$property_details = mysqlSingleRow($query);
	
	# Sync price if not already
	/*
	if(!$job_details['price_used'])
	{
		$job_details['job_price'] = $property_details['price'];
		syncJobPrice($job_id, $property_details['price']);
		
	}
	*/
	
	
	
	#Company Last Name, First Name
	if($property_details['agency_name']!=""){
		
		$aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
	
		if (in_array($property_details['agency_id'], $aid_search)){
			echo 'Defence Housing Australia - Master,,';
		}else{
			echo $property_details['agency_name'].",,";
		}		
		
		#Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
		echo $property_details['address_1']." " . $property_details['address_2'] . ",";
		echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
		
		#Invoice Number, Date, Customer PO, Ship Via, Delivery Status
		echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
		
		#Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
		switch($_SESSION['country_default']){
			case 1:
				$gst = $job_details['job_price'] / 11;
				$job_price = $job_details['job_price'] / 1.1;
			break;
			case 2:
				$gst = ($job_details['job_price']*3)/23;
				$job_price = ($job_details['job_price']-$gst);
			break;
		}
		echo $job_details['job_type'] . ",1," . $job_details['description'] . ",$" . currencyFormatNoComma($job_price) . ",$" . currencyFormatNoComma($job_details['job_price']) . ",0%,$" . currencyFormatNoComma($job_price) . ",$" . currencyFormatNoComma($job_details['job_price']) . ",";
		
		#Job, Comment
		//echo ",,";
		
		#Journal Memo
		echo $property_details['address_1']." " . $property_details['address_2'] . ",";
		
		#Salesperson Last Name	Salesperson First Name	Shipping Date	Referral Source
		//echo $property_details['LastName'].",". $property_details['FirstName'].",";
		echo ",,";
		
		if($_SESSION['country_default']==1){
			$gst_text = 'GST';
		}else{
			$gst_text = 'S15';
		}
		
		#Tax Code, Non GST Amount, GST Amount, LCT Amount
		echo "{$gst_text},$0.00,$" .currencyFormatNoComma($gst). ",$0.00,";
		
		#Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
		echo ",{$gst_text},$0.00,$0.00,$0.00,";
		
		#Sale Status, Currency Code, Exchange Rate,	Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
		echo "I,5,1,7,0,0,$0.00,\n";
		
		#Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB	Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
		//echo ",,,,,,,,,,,,,,\n";
		
		$invoice_total += $job_price;
		
	}
	
    
    for($x = 0; $x < $num_alarms; $x++)
	{
		if($alarm_details[$x]['new'] == 1)
		{
			if($property_details['agency_name']!=""){
			
				#Company Last Name, First Name
				$aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
				
				if (in_array($property_details['agency_id'], $aid_search)){
					echo 'Defence Housing Australia - Master,,';
				}else{
					echo $property_details['agency_name'].",,";
				}	
				
				#Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
				echo $property_details['address_1']." " . $property_details['address_2'] . ",";
				echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
				
				#Invoice Number, Date, Customer PO, Ship Via, Delivery Status
				echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
				
				#Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
				switch($_SESSION['country_default']){
				case 1:
					$gst = $alarm_details[$x]['alarm_price'] / 11;
					$alarm_price = $alarm_details[$x]['alarm_price'] / 1.1;
				break;
				case 2:
					$gst = ($alarm_details[$x]['alarm_price']*3)/23;
					$alarm_price = ($alarm_details[$x]['alarm_price']-$gst);
				break;
			}
				echo $alarm_details[$x]['alarm_pwr'] . ",1,Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm,$" . currencyFormatNoComma($alarm_price) . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",0%,$" . currencyFormatNoComma($alarm_price) . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",";
				
				#Job, Comment
				//echo ",,";
				
				#Journal Memo
				echo $property_details['address_1']." " . $property_details['address_2'] . ",";
				
				#Salesperson Last Name	Salesperson First Name	Shipping Date	Referral Source
				echo ",,";
				
				if($_SESSION['country_default']==1){
					$gst_text = 'GST';
				}else{
					$gst_text = 'S15';
				}
				#Tax Code, Non GST Amount, GST Amount, LCT Amount
				echo "{$gst_text},$0.00,$" . currencyFormatNoComma($gst) . ",$0.00,";
				
				#Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
				echo ",{$gst_text},$0.00,$0.00,$0.00,";
				
				#Sale Status, Currency Code, Exchange Rate,	Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
				echo "I,5,1,7,0,0,$0.00,\n";
				
				#Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB	Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
				//echo ",,,,,,,,,,,,,,\n";
				
				$invoice_total += $alarm_price;
			
			}
			
			
			if($property_details['agency_name']!=""){
			
				# SECOND ROW - Reason code
			
				if($alarm_details[$x]['alarm_reason'] == "Insufficient") $reasonstring = "New Install - Insufficient";
				else $reasonstring = "Replaced - " . $alarm_details[$x]['alarm_reason'];	
				
				#Company Last Name, First Name
				$aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
				
				if (in_array($property_details['agency_id'], $aid_search)){
					echo 'Defence Housing Australia - Master,,';
				}else{
					echo $property_details['agency_name'].",,";
				}
				
				#Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
				echo $property_details['address_1']." " . $property_details['address_2'] . ",";
				echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
				
				#Invoice Number, Date, Customer PO, Ship Via, Delivery Status
				echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
				
				#Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
				echo $alarm_details[$x]['alarm_reason'] . ",1," . $reasonstring . ",$0.00,$0.00,0%,$0.00,$0.00,";
				
				#Job, Comment
				//echo ",,";
				
				#Journal Memo
				echo $property_details['address_1']." " . $property_details['address_2'] . ",";
				
				#Salesperson Last Name	Salesperson First Name	Shipping Date	Referral Source
				echo ",,";
				
				if($_SESSION['country_default']==1){
					$gst_text = 'GST';
				}else{
					$gst_text = 'S15';
				}
				#Tax Code, Non GST Amount, GST Amount, LCT Amount
				echo "{$gst_text},$0.00,$0.00,$0.00,";
				
				#Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
				echo ",{$gst_text},$0.00,$0.00,$0.00,";
				
				#Sale Status, Currency Code, Exchange Rate,	Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
				echo "I,5,1,7,0,0,$0.00,\n";
				
				#Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB	Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
				//echo ",,,,,,,,,,,,,,\n";
			
			}
			
			
			
		}
	}
	
	
	// Surcharge
	$sc_sql = mysql_query("
		SELECT *, m.`name` AS m_name 
		FROM `agency_maintenance` AS am
		LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
		WHERE am.`agency_id` = {$property_details['agency_id']}
		AND am.`maintenance_id` > 0
	");
	$sc = mysql_fetch_array($sc_sql);
	if( $invoice_total!=0 && $sc['surcharge']==1 ){
			
		#Company Last Name, First Name
		$aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
		
		if (in_array($property_details['agency_id'], $aid_search)){
			echo 'Defence Housing Australia - Master,,';
		}else{
			echo $property_details['agency_name'].",,";
		}	
		
		#Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
		echo $property_details['address_1']." " . $property_details['address_2'] . ",";
		echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
		
		#Invoice Number, Date, Customer PO, Ship Via, Delivery Status
		echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
		
		#Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
		switch($_SESSION['country_default']){
			case 1:
				$gst = $sc['price'] / 11;
				$sc_price = $sc['price'] / 1.1;
			break;
			case 2:
				$gst = ($sc['price']*3)/23;
				$sc_price = ($sc['price']-$gst);
			break;
		}
		$surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
		//echo $alarm_details[$x]['alarm_pwr'] . ",1,Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm,$" . $alarm_price . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",0%,$" . $alarm_price . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",";
		echo $sc['m_name'] . ",1,\"{$surcharge_txt}\",$" . currencyFormatNoComma($sc_price) . ",$" . currencyFormatNoComma($sc['price']) . ",0%,$" . currencyFormatNoComma($sc_price) . ",$" . currencyFormatNoComma($sc['price']) . ",";
		
		#Job, Comment
		//echo ",,";
		
		#Journal Memo
		echo $property_details['address_1']." " . $property_details['address_2'] . ",";
		
		#Salesperson Last Name	Salesperson First Name	Shipping Date	Referral Source
		echo ",,";
		
		if($_SESSION['country_default']==1){
			$gst_text = 'GST';
		}else{
			$gst_text = 'S15';
		}
		#Tax Code, Non GST Amount, GST Amount, LCT Amount
		echo "{$gst_text},$0.00,$" . currencyFormatNoComma($gst) . ",$0.00,";
		
		#Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
		echo ",{$gst_text},$0.00,$0.00,$0.00,";
		
		#Sale Status, Currency Code, Exchange Rate,	Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
		echo "I,5,1,7,0,0,$0.00,\n";
		
		#Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB	Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
		//echo ",,,,,,,,,,,,,,\n";
	
	}
	
       
    echo "\n";
}
	        

?>
