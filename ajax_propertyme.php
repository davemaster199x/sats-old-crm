<?php 
include('inc/init_for_ajax.php');

$propertyme = new Propertyme_api;

function getGoogleMapAddress($address){      
	$ch = curl_init();
	$API_key = GOOGLE_DEV_API;
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".rawurlencode($address)."&key={$API_key}";
	$optArray = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false
	);
	// apply those options
	curl_setopt_array($ch, $optArray);
	// execute request and get response
	$result = curl_exec($ch);
	$result_json = json_decode($result);
	curl_close($ch);
	return $result_json;
}

if(isset($_GET['getPropertyDetails'])){
	$pid = filter_input(INPUT_POST, 'pid');
	$rsDetails = $propertyme->getPropertyDetails($pid);
	
	/*
	echo "<pre>";
	print_r($rsDetails);
	echo "</pre>";
	*/
	
	if(!empty($rsDetails)){

		$details['Id'] = $rsDetails['Id'];
		$details['pm_full_address'] = (!empty($rsDetails['AddressText'])) ? $rsDetails['AddressText'] : "";
		$details['AddressText'] = (!empty($rsDetails['AddressText'])) ? $rsDetails['AddressText'] : "";
		$details['AddressNumber'] = (!empty($rsDetails['Address'])) ? $rsDetails['Address']['Unit'].' '.$rsDetails['Address']['Number'] : "";
		$details['AddressStreet'] = (!empty($rsDetails['Address'])) ? $rsDetails['Address']['Street'] : "";
		$details['AddressSuburb'] = (!empty($rsDetails['Address'])) ? $rsDetails['Address']['Suburb'] : "";
		$details['AddressState'] = (!empty($rsDetails['Address'])) ? $rsDetails['Address']['State'] : "";
		$details['AddressCode'] = (!empty($rsDetails['Address'])) ? $rsDetails['Address']['PostalCode'] : "";
		$details['LLName'] = (!empty($rsDetails['Ownership'])) ? $rsDetails['Ownership']['ContactReference'] : "";
		$details['LLEmail'] = (!empty($rsDetails['Ownership'])) ? $rsDetails['Ownership']['ContactEmail'] : "";
		$details['LLLandline'] = (!empty($rsDetails['Ownership'])) ? $rsDetails['Ownership']['HomePhone'] : "";
		$details['LLMobile'] = (!empty($rsDetails['Ownership'])) ? $rsDetails['Ownership']['CellPhone'] : "";
		$details['PropertyManager'] = (!empty($rsDetails['PropertyManager'])) ? $rsDetails['PropertyManager']: "";
		$details['rsDetails'] = $rsDetails;

		$address_str = "{$details['AddressNumber']} {$details['AddressStreet']} {$details['AddressSuburb']} {$details['AddressState']} {$details['AddressCode']}";
		$gmap = getGoogleMapAddress($address_str);

		/*
		$address1 = $gmap->results[0]->address_components[0]->long_name;
		$address2 = $gmap->results[0]->address_components[1]->long_name;
		$address3 = $gmap->results[0]->address_components[2]->long_name;
		$state = $gmap->results[0]->address_components[4]->short_name;
		$pocode = $gmap->results[0]->address_components[6]->long_name;
		// $address = $address1." ".$address2." ".$address3." ".$state." ".$pocode;
		*/
		
		
		$full_address = $gmap->results[0]->formatted_address;
		$add_comp = $gmap->results[0]->address_components;
		
		foreach( $add_comp as $add_comp_obj) {
			
			
			$address_type = $add_comp_obj->types[0];
			$address_type_val = $add_comp_obj;
	
			
			switch($address_type){
				case 'subpremise':
					$address1_1 = $address_type_val->short_name;
				break;
				case 'street_number':
					$address1_2 = $address_type_val->short_name;
				break;
				case 'route':
					$address2 = $address_type_val->long_name;
				break;
				case 'locality':
					$address3 = $address_type_val->long_name;
				break;
				case 'administrative_area_level_1':
					$state = $address_type_val->short_name;
				break;
				case 'postal_code':
					$pocode = $address_type_val->short_name;
				break;
			}
			
		}
		
		// street number 
		$full_address_exp = explode(" ",$full_address);
		$address1 = $full_address_exp[0];
		
		
		//print_r($gmap);

		$details['AddressText'] = $full_address;
		$details['AddressNumber'] = $address1;
		$details['AddressStreet'] = $address2;
		$details['AddressSuburb'] = $address3;
		$details['AddressState'] = $state;
		$details['AddressCode'] = $pocode;

	} else {
		$details['Id'] = "";
		$details['AddressText'] = "";
		$details['AddressNumber'] = "";
		$details['AddressStreet'] = "";
		$details['AddressSuburb'] = "";
		$details['AddressState'] = "";
		$details['AddressCode'] = "";
		$details['LLName'] = "";
		$details['LLEmail'] = "";
		$details['LLLandline'] = "";
		$details['LLMobile'] = "";
		$details['rsDetails'] = "";
	}
	die(json_encode($details));
}
?>