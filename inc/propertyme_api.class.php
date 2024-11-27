<?php 
session_start();

class Propertyme_api {

	private static $username = 'propertyme@sats.com.au';
	private static $password = "fB4y%'_2^ZN?tEuQ";
	private static $api_url = 'https://app.propertyme.com/api';

	public function __construct()
	{
		// if(self::authenticate() === TRUE) {
		// 	$_SESSION['isAuth'] = TRUE;
		// } else {
			self::authenticate();
		// 	$_SESSION['isAuth'] = FALSE;
		// 	header("Location: index.php");
		// }
	}

	public function auth()
	{
		self::authenticate();
	}

	public function checkSmsforToday($job_id){
	 return mysql_query("
	  SELECT * 
	  FROM  `job_log` 
	  WHERE  `contact_type` =  'SMS sent'
	  AND  `job_id` ={$job_id}
	  AND  `eventdate` =  '".date('Y-m-d')."'
	 ");
	}

	public function getAgencyDetails($agency_id)
	{
		$url = '/sec/user/portfolios?format=json';
		$postfields = array('CustomerId' => $agency_id);
		$result = self::oAuth($url, 'POST', FALSE, $postfields);
		return $result;
	}

	public function getTenancyDetails($id)
	{
		$url = '/entity/tenancies/' . $id;
		$result = self::oAuth($url, 'GET', FALSE);
		return $result;
	}
	
	public function getAgencies()
	{
		$url = '/sec/user/portfolios?format=json';
		$result = self::oAuth($url, 'GET', FALSE, []);
		return $result;
	}

	public function getAllProperties($getActive = TRUE)
	{
		$includeArchived = ($getActive === FALSE) ? "/?IncludeArchived=1" : "";
		$url = '/reporting/Properties/PropertyDetailsReport'.$includeArchived;
		$result = self::oAuth($url, 'GET', FALSE);
		return $result;
	}

	public function getPropertyDetails($property_id)
	{
		$url = '/entity/propertyfolders/'.$property_id;
		$result = self::oAuth($url, 'GET', FALSE);
		return $result;
	}

	public function getContactDetails($contact_id)
	{
		$url = '/entity/contacts/'.$contact_id;
		$result = self::oAuth($url, 'GET', FALSE);
		return $result;
	}
	
	private function authenticate()
	{
		$url = '/auth/credentials';
		$result = self::oAuth($url, 'GET', TRUE);
		if(!empty($result['ResponseStatus']['ErrorCode'])){
			echo "<h3>".$result['ResponseStatus']['ErrorCode']."</h3><p>".$result['ResponseStatus']['Message']."</p>";
			die();
		} else {
			return TRUE;
		}
		
	}

	public function getDistance($coordinates)
	{
		$crm = $coordinates['crm']['latitude'].",".$coordinates['crm']['longtitude'];
		$pm = $coordinates['pm']['latitude'].",".$coordinates['pm']['longtitude'];
		$url = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$crm."&destinations=".$pm."&mode=driving&language=en-EN&sensor=false";
		$api = file_get_contents($url);
		return json_decode($api);
	}

	public static function debug($arr)
	{
		return '<pre>'.print_r($arr, TRUE).'</pre>';
	}

	private function oAuth($url, $method, $oAuth = FALSE, $postfields = [])
	{
	    $ch = curl_init(); 
	    if($oAuth === TRUE)
	    	$url = $url.'/?username='.self::$username.'&password='.self::$password;
	    curl_setopt($ch, CURLOPT_URL, self::$api_url.$url);
	  	switch ($method) 
		{
			case 'GET':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				break;

			case 'PUT':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
				break;

			case 'POST':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
				break;

			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postfields));
				break;
			
			default:
				die('INVALID REQUEST METHOD');
				break;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));	
	    curl_setopt($ch, CURLOPT_HEADER, 0); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		/*
	    if($oAuth === TRUE){
	    	curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '\propertyme_cookies.txt'); 
	    } else {
	    	curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . '\propertyme_cookies.txt');
	    }
		*/
		if($oAuth === TRUE){
	    	curl_setopt($ch, CURLOPT_COOKIEJAR, 'pme_api_cookie/cookie_pme.txt'); 
	    } else {
	    	curl_setopt($ch, CURLOPT_COOKIEFILE, 'pme_api_cookie/cookie_pme.txt');
	    }
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    $response = curl_exec($ch); 
	    curl_close($ch); 
	    return json_decode($response, TRUE);
	}
	
	
	public function getAgencyName($pm_agency_id){

		$a_sql_str =  "
			SELECT `agency_name`
			FROM `agency`
			WHERE `propertyme_agency_id` = '{$pm_agency_id}'
		";
		$a_sql = mysql_query($a_sql_str);
		$a = mysql_fetch_array($a_sql);
		return $a['agency_name'];
		
	}
	
	function matchPmToCrmProp($pm_agency_id,$pm_prop_id){
	
		$p_sql_str = "
			SELECT 
				p.`property_id`,
				apd.`api_prop_id`,
				apd.`api`,
				p.`propertyme_prop_id`,
				p.`address_1`,
				p.`address_2`,
				p.`address_3`,
				p.`state`,
				p.`postcode`,
				a.`agency_id`,
				a.`franchise_groups_id`,
				a.`allow_indiv_pm`
			FROM `property` AS p 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `api_property_data` AS apd ON p.`property_id` = apd.`crm_prop_id`
			WHERE a.`propertyme_agency_id` = '{$pm_agency_id}'
			AND apd.`api_prop_id`='{$pm_prop_id}'
			AND apd.`api`= 1
			AND p.`deleted` = 0
		";
		return mysql_query($p_sql_str);
	
	}


}