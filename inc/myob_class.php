<?php

class MYOB_Class{
	
	
	// developer key
	public $client_key;
	public $secret_key;

	// tokens
	public $access_token;
	public $refresh_token;
	public $cf_token;

	// admin - sandbox
	public $company_user;
	public $company_pass;
	
	// company file
	public $cf_guid;
	
	// if use sandbox
	public $isSandBox;
	
	// curl header
	public $curl_header;
	
	// csv path
	public $file_path;
	
	// constructor 
	public function __construct($client_key,$secret_key,$refresh_token,$cf_guid,$company_user="Administrator",$company_pass="",$file_path,$isSandBox=true){
		
		// developer key
		$this->client_key = $client_key;
		$this->secret_key = $secret_key;

		// tokens
		$this->refresh_token = $refresh_token;
		
		// refresh token
		$this->access_token = $this->refreshAccessToken();

		// company file credentials
		$this->company_user = $company_user;
		$this->company_pass = $company_pass;
		
		$this->cf_token = $this->getCfToken();
		
		$this->isSandBox = $isSandBox;
		
		$this->curl_header = $this->getCurlHeaders();
		
		$this->cf_guid = $cf_guid;
		
		$this->file_path = $file_path;
	  
	}
	
	// this function gets called if it's object is directly echoed
	public function __toString(){
		
		$prop = array(
			'client_key' => $this->client_key,
			'secret_key' => $this->secret_key,
			'refresh_token' => $this->refresh_token,
			'access_token' => $this->access_token,
			'cf_token' => $this->cf_token,
			'company_user' => $this->company_user,
			'company_pass' => $this->company_pass,
			'isSandBox' => $this->isSandBox,
			'cf_guid' => $this->cf_guid,
			'curl_header' => $this->curl_header,
			'file_path' => $this->file_path
		);
		
		ob_start( );
		echo "<pre>";
		print_r($prop);
		echo "</pre>";
		
		return $output = ob_get_clean( );
		
	}
	
	
	// refresh access token
	public function refreshAccessToken(){
		
		// set execution time limit to 60 secondes, default is 30
		set_time_limit(120);
		
		// init curl object        
		$ch = curl_init();
		
		$url = "https://secure.myob.com/oauth2/v1/authorize";
		
		// define options
		$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_CONNECTTIMEOUT => 1,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_POSTFIELDS => "client_id={$this->client_key}&client_secret={$this->secret_key}&refresh_token={$this->refresh_token}&grant_type=refresh_token",
			CURLOPT_RETURNTRANSFER => true
		);

		// apply those options
		curl_setopt_array($ch, $optArray);

		// Execute
		$output = curl_exec($ch);

		$result_json = json_decode($output);

		$access_token = $result_json->access_token;

		curl_close($ch); // Close curl handle
		
		// delay
		//sleep(3);
		
		return $access_token;
		
		
	}
	
	// get cf_token
	public function getCfToken(){
		
		return $cf_token = base64_encode("{$this->company_user}:{$this->company_pass}");
		
	}
	
	
	// get header details needed for MYOB API
	public function getCurlHeaders(){
	
		// headers
		if($this->isSandBox==true){
			$headers = array(
				"Authorization: Bearer {$this->access_token}",
				"x-myobapi-cftoken: {$this->cf_token}",
				"x-myobapi-key: {$this->client_key}",
				"x-myobapi-version: v2",
				"Content-Type: application/json"
			);
		}else{
			$headers = array(
				"Authorization: Bearer {$this->access_token}",
				"x-myobapi-key: {$this->client_key}",
				"x-myobapi-version: v2",
				'Content-Type: application/json'
			);
		}
		
		return $headers;
		
		
	}
	
	// extract invoices from myob csv
	public function extractMYOBCsvFile(){
		
		// csv file path
		$file_path = $this->file_path;
		
		
		$file = fopen($file_path,"r");
		$csv_array = array();

		// extracts csv data and store it on array 
		while(! feof($file)){
			$csv_array[] = fgetcsv($file);	
		}
		
		$i = 0;
		foreach($csv_array as $index=>$csv){
			if( $index!=0 && $csv[0]!="" ){
				// required fields
				$import_list[$i]['date'] = $csv[8];
				$import_list[$i]['agency'] = $csv[0];
				$import_list[$i]['quantity'] = 1;
				$import_list[$i]['total'] = 1;
				$import_list[$i]['item'] = $csv[10];
				$import_list[$i]['tax_code'] = $csv[21];
				// non required fields
				$import_list[$i]['delivery_status'] = $csv[9];
				$import_list[$i]['IsTaxInclusive'] = $csv[6];
				$import_list[$i]['Number'] = $csv[7];
				// items
				$import_list[$i]['ship_to_address'] = "{$csv[2]}\r\n{$csv[3]}";
				$import_list[$i]['description'] = $csv[12];
				$import_list[$i]['price'] = $csv[13];
				$import_list[$i]['price_inc_tax'] = $csv[14];
				$import_list[$i]['discount_percent'] = $csv[15];
				$import_list[$i]['total'] = $csv[16];
				$import_list[$i]['total_inc_tax'] = $csv[17];
				$import_list[$i]['journal_memo'] = $csv[18];
				$import_list[$i]['status'] = $csv[30];
				$import_list[$i]['payment_is_due'] = $csv[31];
				$import_list[$i]['dicount_date'] = $csv[32];
				$import_list[$i]['balance_due_date'] = $csv[33];
				$import_list[$i]['discount'] = $csv[34];
				$import_list[$i]['monthly_charge'] = $csv[35];
				// salesperson
				$import_list[$i]['salesperson_fn'] = $csv[19];
				$import_list[$i]['salesperson_ln'] = $csv[20];
				$i++;
			}
		}
		
		return $import_list;
		
		
	}
	
	
	// get MYOB UID
	public function getMyobUid($headers,$cf_guid,$ep_uri){
		
		// set execution time limit to 60 secondes, default is 30
		set_time_limit(120);
		
		$ch = curl_init();
		
		$url = 'https://api.myob.com/accountright/'.$cf_guid.$ep_uri;
		// define options
		$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 1,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_HTTPHEADER => $headers
		);

		// apply those options
		curl_setopt_array($ch, $optArray);

		// Execute
		$output = curl_exec($ch);

		$result_json = json_decode($output);

		curl_close($ch); // Close curl handle
		
		/*
		echo "<pre>";
		print_r($result_json); // Show output
		echo "</pre>";
		*/
		
		// delay
		//sleep(3);

		return $result_json->Items[0]->UID;
		
	}


	// get Items 
	public function getLineItems($params_arr_lines,$imp,$item,$tax_code){
		
		$price = ($imp['IsTaxInclusive']=='X')?$imp['price_inc_tax']:$imp['price'];
		$total = ($imp['IsTaxInclusive']=='X')?$imp['total_inc_tax']:$imp['total'];
		
		return array(
				'Description' => $imp['description'],
				'ShipQuantity' => $imp['quantity'],
				'UnitPrice' => str_replace('$','',$price),
				'DiscountPercent' => str_replace('%','',$imp['discount_percent']),
				'Total' => str_replace('$','',$total),
				'Item' => $item,
				'TaxCode' => $tax_code
			);
	}
	
	// Get Delivery Status
	public function getDeliveryStatus($ds){
		$ds_val = 'Print';
		switch($ds){
			case 'P':
				$ds_val = 'Print';
			break;
			case 'E':
				$ds_val = 'Email';
			break;
			case 'B':
				$ds_val = 'PrintAndEmail';
			break;
			case 'A':
				$ds_val = 'Nothing';
			break;
		}
		return $ds_val;
	}
	
	// run checks
	public function runChecks(){
		
		$import_list = $this->extractMYOBCsvFile();
		
		$error=0;
		$agency_missing = "";
		$post_param = array();

		//echo $invoice_num_group = $import_list[1]['Number'];
		$orig_invoice = '';
		foreach( $import_list as $index => $imp ){
	
			// invoice number
			$find = array('"','=');
			$invoice_num = str_replace($find,"",str_pad($imp['Number'], 8, "0", STR_PAD_LEFT));
			
			// Item ID
			$filter_attr = 'Number';
			$filter_val = urlencode($imp['item']);
			$end_point = '/Inventory/Item';
			$ep_uri = $end_point.'/?$filter='.$filter_attr.'+eq+\''.$filter_val.'\'';
			//echo "<br />";
			$item_id = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
			if($item_id==""){
				$item = null;
				if( !in_array($imp['item'], $item_missing) ){
					$item_missing[] = $imp['item'];
				}
			}else{
				$item = array(
					'UID' => $item_id
				);
			}
			
			// Tax Code ID
			$filter_attr = 'Code';
			$filter_val = urlencode($imp['tax_code']);
			$end_point = '/GeneralLedger/TaxCode';
			$ep_uri = $end_point.'/?$filter='.$filter_attr.'+eq+\''.$filter_val.'\'';
			$tax_code_id = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
			if($tax_code_id==""){
				$tax_code = null;
				if( !in_array($imp['tax_code'], $tax_code_missing) ){
					$tax_code_missing[] = $imp['tax_code'];
				}
			}else{
				$tax_code = array(
					'UID' => $tax_code_id
				);
			}
			
			// same invoice
			if($invoice_num==$orig_invoice){
				
				$params_arr_lines[] = $this->getLineItems($params_arr_lines,$imp,$item,$tax_code);
				
			}else{
				
				$orig_invoice = $invoice_num;
				
				$date = date("Y-m-d",strtotime(str_replace("/","-",$imp['date'])));
				$IsTaxInclusive = ($imp['IsTaxInclusive']=='X')?true:false;
				//echo "Is Tax inclusive: ({$imp['IsTaxInclusive']})";
			
				// get myob UID
				
				$filter_attr = 'Number';
				$filter_val = urlencode($invoice_num);
				$end_point = '/Sale/Invoice/Item';
				$ep_uri = $end_point.'/?$filter='.$filter_attr.'+eq+\''.$filter_val.'\'';
				//echo "<br />";
				$invoice_id_via_num = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
				if($invoice_id_via_num!=""){
					if( !in_array($invoice_num, $invoice_already_exist) ){
						$invoice_already_exist[] = $invoice_num;
					}
				}
				
				// customer ID
				$filter_attr = 'CompanyName';
				$filter_val =  urlencode($imp['agency']);
				$end_point = '/Contact/Customer';
				$ep_uri = $end_point.'/?$filter='.$filter_attr.'+eq+\''.$filter_val.'\'';
				$customer_id = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
				if($customer_id==""){
					$customer = null;
					if( !in_array($imp['agency'], $customer_missing) ){
						$customer_missing[] = $imp['agency'];
					}
				}else{
					$customer = array(
						'UID' => $customer_id
					);
				}


				// Sales Person ID
				$filter_attr_fn = 'FirstName';
				$salesperson_fn = urlencode($imp['salesperson_fn']);
				$filter_attr_ln = 'LastName';
				$salesperson_ln = urlencode($imp['salesperson_ln']);
				$end_point = '/Contact/Employee';
				$ep_uri = $end_point.'/?$filter='.$filter_attr_fn.'+eq+\''.$salesperson_fn.'\'and+'.$filter_attr_ln.'+eq+\''.$salesperson_ln.'\'';
				$salesperson_id = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
				if($salesperson_id==""){
					$salesperson = null;
					$salesperson_fullname = "{$imp['salesperson_fn']} {$imp['salesperson_ln']}";
					if( !in_array($salesperson_fullname, $salesperson_missing) ){
						$salesperson_missing[] = $salesperson_fullname;
					}
				}else{
					$salesperson = array(
						'UID' => $salesperson_id
					);
				}
				
				//echo "<br />";
				
				unset($params_arr_lines);
				
				$params_arr_lines[] = $this->getLineItems($params_arr_lines,$imp,$item,$tax_code);
				
			}
			
			


			$next_invoice_temp = str_replace("=","",$import_list[$index+1]['Number']);
			$next_invoice_num = str_replace('"',"",$next_invoice_temp);
			if( $invoice_num!=$next_invoice_num ){
				
				// data
				$params_arr = array(
					'Number' => $invoice_num,
					'Date' => $date,
					'InvoiceDeliveryStatus' => $this->getDeliveryStatus($imp['delivery_status']),
					'ShipToAddress' => $imp['ship_to_address'],
					'Customer' => $customer,
					'IsTaxInclusive' => $IsTaxInclusive,
					'JournalMemo' => $imp['journal_memo'],
					'FreightTaxCode' => $tax_code,
					'Terms' => array(
						'PaymentIsDue' => $imp['payment_is_due'],
						'DiscountDate' => $imp['dicount_date'],
						'BalanceDueDate' => $imp['balance_due_date'],
						'Discount' => $imp['discount'],
						'MonthlyChargeForLatePayment' => $imp['monthly_charge']
					),
					'Salesperson' => $salesperson,
					'Lines' => $params_arr_lines
				);
				
				//echo $post_param = json_encode($params_arr);
				$post_param[] = $params_arr;
				
				
				//echo "<br />";

				
				
			}
			
			
			

			
		}
		
		
		$ret = array(
			'invoice_already_exist' => $invoice_already_exist,
			'customer_missing' => $customer_missing,
			'item_missing' => $item_missing,
			'tax_code_missing' => $tax_code_missing,
			'salesperson_missing' => $salesperson_missing,
			'post_param' => $post_param
		);

		return $ret;
		
	}
	
	public function checkMyobForMarks(){
		
		$import_list = $this->extractMYOBCsvFile();
		
		
		
		// loop through csv
		$orig_invoice = '';
		$unique_invoice = array();
		foreach( $import_list as $index => $imp ){
			//echo $imp['Number']."<br />";
			if( !in_array($imp['Number'], $unique_invoice) ){
				$unique_invoice[] = $imp['Number'];
			}
		}
		
		/*
		// unique invoice
		echo "<pre>";
		print_r($unique_invoice);
		echo "</pre>";
		*/
		
		// loop through invoice
		foreach( $unique_invoice as $invoice ){
			
			$find = array('"','=');
			$invoice_num = str_replace($find,"",str_pad($invoice, 8, "0", STR_PAD_LEFT));
			
			
			// find if invoice already exist on myob
			$filter_attr = 'Number';
			$filter_val = urlencode($invoice_num);
			$end_point = '/Sale/Invoice/Item';
			$ep_uri = $end_point.'/?$filter='.$filter_attr.'+eq+\''.$filter_val.'\'';
			//echo "<br />";
			$invoice_id_via_num = $this->getMyobUid($this->curl_header,$this->cf_guid,$ep_uri);
			// invoice exist
			if($invoice_id_via_num!=""){
				
				$invoice_num_arr = str_split($invoice_num);
				//$temp = array(1,2,3,4,5,6,7,8,9);
				$found = 0;
				foreach( $invoice_num_arr as $index=>$val ){
					$val2 = (int)$val;
					if( $val2>0 && $found==0 ){
						$pos = $index;
						$found = 1;
					}
				}
				//echo "found invoice number {$invoice_num}, position is: {$pos}<br />";
				$job_id = substr($invoice_num,$pos);
				//echo "<br />";
				
				$jsql = mysql_query("
					SELECT *
					FROM `jobs`
					WHERE `id` = {$job_id}
					AND `at_myob` = 0
				");
				if( mysql_num_rows($jsql)>0 ){
					
					//echo "Job {$job_id} found<br />";
					
					mysql_query("
						UPDATE `jobs`
						SET `at_myob` = 1
						WHERE `id` = {$job_id}
					");
					//echo "Job {$job_id} marked as already imported on myob<br />";
					
				}
				
			}
			
				
				
				
			
		}
		
		/*
		echo "<pre>";
		print_r($invoice_already_exist);
		echo "</pre>";
		*/
		

	}
	
	// import to MYOB
	public function import(){
		
		// run checks
		$checks = $this->runChecks();
		
		$invoice_already_exist = $checks['invoice_already_exist'];
		$item_missing = $checks['item_missing'];
		$tax_code_missing = $checks['tax_code_missing'];
		$customer_missing = $checks['customer_missing'];
		$salesperson_missing = $checks['salesperson_missing'];
		$post_param = $checks['post_param'];
		
		
		
		if( count($invoice_already_exist)>0 || count($customer_missing)>0 || count($tax_code_missing)>0 || count($item_missing)>0 ){
			echo "<br />ERROR: <br />";
		}




		if( count($invoice_already_exist)>0 ){
			echo "Invoice Number already exist on myob database: <br />";
			echo "<ul>";
			foreach( $invoice_already_exist as $val ){
				echo "<li>{$val}</li>";
			}
			echo "</ul>";
			$error = 1;
			echo "<br />";
		}


		if( count($customer_missing)>0 ){
			echo "Agency didn't exist on myob database: <br />";
			echo "<ul>";
			foreach( $customer_missing as $val ){
				echo "<li>{$val}</li>";
			}
			echo "</ul>";
			$error = 1;
			echo "<br />";
		}



		if( count($tax_code_missing)>0 ){
			echo "Tax Code didn't exist on myob database: <br />";
			echo "<ul>";
			foreach( $tax_code_missing as $val ){
				echo "<li>{$val}</li>";
			}
			echo "</ul>";
			$error = 1;
			echo "<br />";
		}



		if( count($item_missing)>0 ){
			echo "Item didn't exist on myob database: <br />";
			echo "<ul>";
			foreach( $item_missing as $val ){
				echo "<li>{$val}</li>";
			}
			echo "</ul>";
			$error = 1;
			echo "<br />";
		}



		if(count($salesperson_missing)>0){
			
			echo "<br />WARNING: <br />";
			
			echo "Sales Person didn't exist on myob database: <br />";
			echo "<ul>";
			foreach( $salesperson_missing as $val ){
				echo "<li>{$val}</li>";
			}
			echo "</ul>";
			echo "<br />";
		}
		
		echo $url = "https://api.myob.com/accountright/{$this->cf_guid}/Sale/Invoice/Item";
		
		if($error==0){
	
			/*
			echo "Invoice json data: <br />";
			echo "<pre>";
			print_r($post_param); // Show output
			echo "</pre>";
			*/
			
			//echo $url = "https://api.myob.com/accountright/";	
			//echo $url = "https://api.myob.com/accountright/{$cf_guid}";
			//echo $url = "https://api.myob.com/accountright/{$cf_guid}/Contact";	
			//$url = "https://api.myob.com/accountright/Info";
			//echo $url = "https://api.myob.com/accountright/{$cf_guid}/Inventory/Item";
			//echo $url = "https://api.myob.com/accountright/{$this->cf_guid}/Sale/Invoice/Item";
			
			echo "<br />";
			
			echo $headers = $this->curl_header;
			
			echo "<br />";
			echo "<br />";
			
			/*
			foreach( $post_param as $param ){
				
				echo $param_json = json_encode($param);
				echo "<br />";
				echo "<br />";
				
				
				$ch = curl_init();
				
				// TEST INSERT item
				// define options
				$optArray = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_HTTPHEADER => $headers,
					CURLOPT_POSTFIELDS => $param_json
				);

				// apply those options
				curl_setopt_array($ch, $optArray);

				// Execute
				$output = curl_exec($ch);

				$result_json = json_decode($output);

				curl_close($ch); // Close curl handle

				echo "<pre>";
				print_r($result_json); // Show output
				echo "</pre>";
				
				// delay
				//sleep(2);
				
				
				
			}
			*/
			
		}

		
	}
	

}

?>