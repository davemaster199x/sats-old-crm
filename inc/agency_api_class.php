<?php

class Agency_api{

    private $clientId = '5ff326e1-18f3-4c9e-9092-607ad116c81e';
    private $clientSecret = 'e8383f6a-8340-4905-8f5c-21fca8702a9a';
    private $clientScope = 'contact:read%20property:read%20activity:read%20communication:read%20transaction:write%20offline_access';
    private $urlCallBack = 'api/callback_pme';
    private $accessTokenUrl = 'https://login.propertyme.com/connect/token';
    private $authorizeUrl = 'https://login.propertyme.com/connect/authorize'; 

    // property tree
    private $pt_api_gateway;
    private $pt_application_key;
    private $pt_subscription_key;
    private $pt_request_limit;
    private $pt_sleep_interval_sec;
    
    public function __construct(){        

        // AU and NZ used the same live/production keys
        if( IS_PRODUCTION ==  1 ){ // LIVE

            $this->pt_api_gateway = 'https://api.propertytree.io';
            $this->pt_application_key = '3941b249-5113-4d49-9c38-f1bf6386cc35';
            $this->pt_subscription_key = '8e5d8cb6af5f41bf8408c40de41b8d82';

        }else{ // DEV

            $this->pt_api_gateway = 'https://uatapi.propertytree.io';
            $this->pt_application_key = '246f503a-7487-4d09-8ef8-2f1bd8c69fd4';
            $this->pt_subscription_key = '3e9b29d41df3414cad93b9b043e52ec6';

        }
        
        $this->pt_request_limit = 240; //  PropertyTree API request limit
        $this->pt_sleep_interval_sec = 60; // delay 1 minute         

    }

    public function call_end_points($params)
    {

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Bearer {$params['access_token']}",
            "Content-Type: application/json"
        );

        // curl options
        $curl_opt = array(
            CURLOPT_URL => $params['end_points'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );     
        
        // parameters
        if( count($params['param_data']) > 0 ){  

            $curl_opt[CURLOPT_POST] = true;                                                        
		    $data_string = json_encode($params['param_data']);  
            $curl_opt[CURLOPT_POSTFIELDS] = $data_string;
            
        }  
              

        // display - debug
        if( $params['display'] == 1 ){
            print_r($curl_opt);
        }

        curl_setopt_array($curl, $curl_opt);

        $response = curl_exec($curl);
        curl_close($curl);

        //$response_decode = json_decode($response);

        return $response;
        
		
    }

    public function getAccessToken($params){

        $agency_id = $params['agency_id'];
        $api_id = ( $params['api_id'] != '' )?$params['api_id']:1; // default is Pme

        if( $agency_id > 0 ){
            
            // get Pme tokens
            $agency_api_tokens_str = "
                SELECT 
                    `access_token`,
                    `expiry`,
                    `refresh_token`
                FROM `agency_api_tokens`
                WHERE `agency_id` = {$agency_id}
                AND `api_id` = {$api_id}
            ";
            $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
            $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);

            $token_expiry = $a_api_tok_row['expiry'];
            $current_datetime = date('Y-m-d H:i:s');

            if( $current_datetime >= date('Y-m-d H:i:s',strtotime("{$token_expiry} -10 minutes"))   ){

                // get new access token from refresh token request
                $refresh_token = $a_api_tok_row['refresh_token'];
                $refresh_token_json = $this->refreshToken($refresh_token);

                //echo "<br />";
                //echo "orig refresh token: {$refresh_token}<br />";
  

                $access_token = json_decode($refresh_token_json)->access_token;
                $refresh_token = json_decode($refresh_token_json)->refresh_token;
                $token_expiry = date('Y-m-d H:i:s',strtotime('+3600 seconds'));

               
                //echo "refresh token json: <br />";
                //print_r($refresh_token_json);

                if( $access_token != '' &&  $refresh_token != '' ){

                    $update_token_str = "
                        UPDATE `agency_api_tokens`
                        SET 
                            `access_token` = '{$access_token}',
                            `expiry` = '{$token_expiry}',
                            `refresh_token` = '{$refresh_token}'
                        WHERE `agency_id` = {$agency_id}
                        AND `api_id` = {$api_id}
                    ";
                    mysql_query($update_token_str) or die(mysql_error());

                }               

            }else{
                $access_token = $a_api_tok_row['access_token'];
            }

            return $access_token;

        }        

    }

    public function refreshToken($refresh_token = "") {

        $token_url = $this->accessTokenUrl;
        $client_id = $this->clientId;
        $client_secret = $this->clientSecret;
        $callback_uri = $this->urlCallBack;

        $authorization = base64_encode("$client_id:$client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=refresh_token&refresh_token=$refresh_token&redirect_uri=$callback_uri";

        $curl_opt = array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        );

        $curl = curl_init();
        
        curl_setopt_array($curl, $curl_opt);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }


    // PME
    public function get_property_pme($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        if( $prop_id != '' && $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}";
            $api_id = 1; // PMe    
    
            // get access token
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->getAccessToken($pme_params);
    
            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            
            return $this->call_end_points($pme_params);

        }       

    }

    // get all PM from PMe members
    public function get_all_property_managers_pme($params){

        $agency_id = $params['agency_id'];

        if( $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/members";
            $api_id = 1; // PMe    
    
            // get access token
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->getAccessToken($pme_params);
    
            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            
            return $this->call_end_points($pme_params);

        }       

    }

    // get PM from lot
    public function get_pme_prop_pm($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        if( $prop_id != '' && $agency_id > 0 ){

            $end_points = "https://app.propertyme.com/api/v1/lots/{$prop_id}/members";
            $api_id = 1; // PMe    
    
            // get access token
            $pme_params = array(
                'agency_id' => $agency_id,
                'api_id' => $api_id
            );
            $access_token = $this->getAccessToken($pme_params);
    
            $pme_params = array(
                'access_token' => $access_token,
                'end_points' => $end_points
            );
            
            return $this->call_end_points($pme_params);

        }       

    }

    public function get_tenants($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $end_points = "https://app.propertyme.com/api/v1/tenancies?LotId={$prop_id}";

        $api_id = 1; // PMe        

        // get access token        
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);
        //echo "access_token: {$access_token}";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        return $this->call_end_points($pme_params);

    }

    public function get_ourtradie_tenants($params){
        //include('inc/ourtradie_api_class.php'); 

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $unixtime 	= time();
        $now 		= date("Y-m-d H:i:s",$unixtime);

        $api_id = 6; // Ourtradie   
        
        $api = new OurtradieApi();
        $access_token = "224af86018ea62a909092bbf7a48acfd62f7c7f2";
        $agency_name = "Chops TV";

        $token = array('access_token' => $access_token);

        //GetAgencies
        $params = array(
            'Skip' 	 		=> 'No',
            'Count'     => 'No'
        );
        $agency = $api->query('GetAgencies', $params, '', $token, true);

        $data_agency = array();
        $data_agency = json_decode($agency, true);

        $data['agency_list'] = array_filter($data_agency, function ($v) {
        return $v !== 'OK';
        });

        $response = $data['agency_list'];

        return $response;
        exit();

        //$api = new OurtradieApi();

        //$access_token = "1ef46ddcfec7c1e00d0e80017d2dd52b9ddf53c5";
        //$agency_name = "Chops TV";

        //$resArr = array('access_token' => $access_token);

        // get Pme tokens
        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);

        //$resArr = $a_api_tok_row['expiry'];

        $expiry          = $a_api_tok_row['expiry'];
        $refresh_token   = $a_api_tok_row['refresh_token'];
        $expired         = strtotime($now) - strtotime($expiry);

        //$resArr = $expired;
        //print_r($token);

        if($expired > 0){

            $options = array(
                'grant_type'      => 'refresh_token',
                'refresh_token'   =>  $refresh_token,
                'client_id'		  => 'br6ucKvcPRqDNA1V2s7x',
                'client_secret'	  => 'd5YOJHb6EYRw5oypl73CJFWGLob5KB9A',
                'redirect_uri'	  => 'https://crmdevci.sats.com.au/ourtradie/checkToken/'
                );

            $api = new OurtradieApi($options, $_REQUEST);
            $token = $refresh_token;

            $response = $api->refreshToken($token);
            
            return $response;
            exit();
            //print_r();
                
            /*
            if(!empty($response)){
                $access_token   = $response->access_token;
                $refresh_token  = $response->refresh_token;
                $expiry         = date('Y-m-d H:i:s',strtotime('+3600 seconds'));
                $created        = $now;

                $update_token_str = "
                        UPDATE `agency_api_tokens`
                        SET 
                            `access_token` = '{$access_token}',
                            `expiry` = '{$expiry}',
                            `refresh_token` = '{$refresh_token}'
                        WHERE `agency_id` = {$agency_id}
                        AND `api_id` = {$api_id}
                    ";
                    mysql_query($update_token_str) or die(mysql_error());
            }*/
        }
        /*
        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }
        $end_points = $palace_api_base."/Service.svc/RestService/ViewAllDetailedTenancy";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $tenantList = $this->call_palace_end_points($pme_params);
        $tenantList = isset($tenantList['ViewAllDetailedTenancy']) ? $tenantList['ViewAllDetailedTenancy'] : array();
        
        $resArr = array();
        foreach ($tenantList as $key => $value) {
            if ($value->PropertyCode == $params['prop_id']) {
                if ($value->TenancyArchived == 'false') {
                    array_push($resArr, $tenantList[$key]);
                }
            }
        }
        */
        //return $response;

    }

    public function get_palace_tenants($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace        

        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }
        $end_points = $palace_api_base."/Service.svc/RestService/ViewAllDetailedTenancy";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $tenantList = $this->call_palace_end_points($pme_params);
        $tenantList = isset($tenantList['ViewAllDetailedTenancy']) ? $tenantList['ViewAllDetailedTenancy'] : array();
        
        $resArr = array();
        foreach ($tenantList as $key => $value) {
            if ($value->PropertyCode == $params['prop_id']) {
                if ($value->TenancyArchived == 'false') {
                    array_push($resArr, $tenantList[$key]);
                }
            }
        }
        return $resArr;

    }

    public function get_palace_landlord($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $propertyDet = $this->get_palace_prop_by_id($params);
        $ownerCode = $propertyDet[0]->PropertyOwnerCode;

        $api_id = 4; // Palace        

        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }
        $end_points = $palace_api_base."/Service.svc/RestService/ViewAllDetailedOwner";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $ownerList = $this->call_palace_end_points($pme_params);
        $ownerList = isset($ownerList['ViewAllDetailedOwner']) ? $ownerList['ViewAllDetailedOwner'] : array();

        $resArr = array();
        foreach ($ownerList as $key => $value) {
            if ($value->OwnerCode == $ownerCode) {
                if ($value->OwnerArchived == 'false') {
                    array_push($resArr, $ownerList[$key]);
                }
            }
        }
        return $resArr;

    }

    public function get_palace_prop_by_id($params) {

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace        

        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }
        $end_points = $palace_api_base."/Service.svc/RestService/ViewAllDetailedProperty";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $propList = $this->call_palace_end_points($pme_params);
        $propList = isset($propList['ViewAllDetailedProperty']) ? $propList['ViewAllDetailedProperty'] : array();
        
        $resArr = array();
        foreach ($propList as $key => $value) {
            if ($value->PropertyCode == $params['prop_id']) {
                if ($value->PropertyArchived == 'false') {
                    array_push($resArr, $propList[$key]);
                }
            }
        }
        return $resArr;

    }

    public function get_palace_diary_by_id($params) {

        $code = $params['code'];
        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace
        
        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }

        $end_points = $palace_api_base."/Service.svc/RestService/v2ViewAllDetailedDiaryGroup";
  
        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $diaryList = $this->call_palace_end_points($pme_params);    


        $diaryList = isset($diaryList['v2ViewAllDetailedDiaryGroup']) ? $diaryList['v2ViewAllDetailedDiaryGroup'] : array();

        $diary_ret = null;
        foreach ($diaryList as $key => $diary_obj) {
            if ($diary_obj->DiaryGroupCode == $code) {                
                array_push($resArr, $diaryList[$key]);
                $diary_ret = $diaryList[$key];
            }
        }   

        return $diary_ret;
  
      }

    public function call_palace_end_points($params)
    {
        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Basic {$params['access_token']}",
            "Content-Type: application/xml"
        );

        curl_setopt_array($curl, array(
          CURLOPT_URL => $params['end_points'],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => $http_header,
          CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $xml_snippet = simplexml_load_string( $response );
        $json_convert = json_encode( $xml_snippet );
        $json = json_decode( $json_convert );
        return (array)($json);

    }

    public function get_contact($params){

        $contact_id = $params['contact_id'];
        $agency_id = $params['agency_id'];

        $end_points = "https://app.propertyme.com/api/v1/contacts/{$contact_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        return $this->call_end_points($pme_params);

    }


    public function get_palace_tenants_v2($params){

        $prop_id = $params['prop_id'];
        $agency_id = $params['agency_id'];

        $api_id = 4; // Palace        

        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }

        $end_points = "{$palace_api_base}/Service.svc/RestService/v2ViewAllDetailedTenancyByProperty/JSON/{$prop_id}";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        $tenant_json = $this->get_palace_end_points($pme_params);
        return json_decode($tenant_json);        

    }


    public function get_property_tree_tenancy($params){

        $tenancy_id = $params['tenancy_id'];
        $agency_id = !empty($params['agency_id']) ? $params['agency_id'] : 0;

        $api_id = 3; // Property Tree    
        
        // API request limit solution
        $req_limit_params = array(
            'api_id' => $api_id,
            'request_limit' => $this->pt_request_limit,
            'sleep_interval_sec' => $this->pt_sleep_interval_sec,
            'agency_id' => $agency_id
        );
        $this->api_request_limit_counter_and_delay($req_limit_params);

        // get access token
        $agency_api_tokens_str = "
            SELECT `access_token`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";
        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_object($agency_api_tokens_sql);
        $access_token = $a_api_tok_row->access_token;       

        $end_points = "{$this->pt_api_gateway}/residentialproperty/v1/Tenancies/{$tenancy_id}";

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );

        // API call
        $curl_opt = array(
            CURLOPT_URL => $end_points,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );

        curl_setopt_array( $curl, $curl_opt );

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);  

    }


    public function get_property_tree_property($params) {

        $property_id = $params['property_id'];
        
        $api_id = 3; // Property Tree 

        if( $property_id > 0 ){            

            // get agency ID from property 
            $prop_sql = mysql_query("
            SELECT `agency_id`
            FROM `property`
            WHERE `property_id` = {$property_id}
            ");
            $prop_row = mysql_fetch_object($prop_sql);
            $agency_id = !empty($prop_row->agency_id) ? $prop_row->agency_id : 0;

            // API request limit solution
            $req_limit_params = array(
                'api_id' => $api_id,
                'request_limit' => $this->pt_request_limit,
                'sleep_interval_sec' => $this->pt_sleep_interval_sec,
                'agency_id' => $agency_id
            );
            $this->api_request_limit_counter_and_delay($req_limit_params);

            // get access token
            $agency_api_tokens_str = "
            SELECT `access_token`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
            ";
            $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
            $a_api_tok_row = mysql_fetch_object($agency_api_tokens_sql);
            $access_token = $a_api_tok_row->access_token;    
            
            // get API property ID
            $crm_connected_prop_sql_str = "
            SELECT `api_prop_id`
            FROM `api_property_data`
            WHERE `crm_prop_id` = {$property_id}
            AND `api` = {$api_id}
            ";
            $crm_connected_prop_sql = mysql_query($crm_connected_prop_sql_str);
            $crm_connected_prop_row = mysql_fetch_object($crm_connected_prop_sql);
            $api_prop_id = $crm_connected_prop_row->api_prop_id;
            
            //echo "api_prop_json: <br />";
            $end_points = "{$this->pt_api_gateway}/residentialproperty/v1/Properties/{$api_prop_id}";
            //echo "<br />";

            $curl = curl_init();

            // HTTP headers
            $http_header = array(
                "Authorization: Bearer {$access_token}",
                "Content-Type: application/json"
            );

            // API call
            $curl_opt = array(
                CURLOPT_URL => $end_points,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $http_header
            );

            curl_setopt_array( $curl, $curl_opt );

            $response = curl_exec($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            /*
            echo "response: <br />";
            echo "<pre>";
            print_r($response);
            echo "</pre>";
            */
            curl_close($curl);

            return array(
                'raw_response' => $response,
                'json_decoded_response' => json_decode($response),
                'http_status_code' => $responseCode
            );

        }        

    }


    public function get_property_palace($params){

        $prop_id = $params['prop_id'];
        $agency_id = !empty($params['agency_id']) ? $params['agency_id'] : 0;

        $api_id = 4; // Palace   

        $agency_api_tokens_str = "
            SELECT 
                `access_token`,
                `expiry`,
                `refresh_token`,
                `system_use`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
        ";

        // var_dump($agency_api_tokens_str);
        // exit;

        $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
        $a_api_tok_row = mysql_fetch_array($agency_api_tokens_sql);
        $access_token = $a_api_tok_row['access_token'];
        $system = $a_api_tok_row['system_use'];

        if ($_SESSION['country_default'] == 1) { // AU
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapia.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($system == "Legacy" || is_null($system)) {
                $palace_api_base = 'https://serviceapi.realbaselive.com';
            }else {
                $palace_api_base = 'https://api.getpalace.com';
            }
        }

        $end_points = "{$palace_api_base}/Service.svc/RestService/v2DetailedProperty/JSON/{$prop_id}";

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        return $this->get_palace_end_points($pme_params);

    }


    public function get_palace_end_points($params)
    {

        $curl = curl_init();

        // HTTP headers
        $http_header = array(
            "Authorization: Basic {$params['access_token']}",
            "Content-Type: application/json"
        );

        // curl options
        $curl_opt = array(
            CURLOPT_URL => $params['end_points'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $http_header
        );     
        
        // parameters
        if( count($params['param_data']) > 0 ){  

            $curl_opt[CURLOPT_POST] = true;                                                        
		    $data_string = json_encode($params['param_data']);  
            $curl_opt[CURLOPT_POSTFIELDS] = $data_string;
            
        }  
              

        // display - debug
        if( $params['display'] == 1 ){
            print_r($curl_opt);
        }

        curl_setopt_array($curl, $curl_opt);

        $response = curl_exec($curl);
        curl_close($curl);

        //$response_decode = json_decode($response);

        return $response;
        
		
    }

    public function api_request_limit_counter_and_delay($params){

        $agency_id = $params['agency_id'];
        $api_id = $params['api_id'];
        $request_limit = $params['request_limit'];
        $sleep_interval_sec = $params['sleep_interval_sec'];        

        // get count
        $sql = mysql_query("
        SELECT `count`
        FROM `agency_api_request_count`
        WHERE `api_id` = {$api_id}
        AND `agency_id` = {$agency_id}
        ");
        $row = mysql_fetch_object($sql);

        if( $row->count >= $request_limit ){ // request limit

            // sleep interval
            sleep($sleep_interval_sec); // 1 minute

            // reset to 1
            $count = 1;

        }else{

            // increment count
            $count = ($row->count+1);

        }

        if( mysql_num_rows($sql) > 0 ){ // exist, update

            mysql_query("
            UPDATE `agency_api_request_count`
            SET `count` = {$count}
            WHERE `api_id` = {$api_id}
            AND `agency_id` = {$agency_id}
            ");

        }else{ //  new, insert

            mysql_query("
            INSERT INTO 
            `agency_api_request_count`(
                `api_id`,
                `count`,
                `agency_id`
            )
            VALUES(
                {$api_id},
                {$count},
                {$agency_id}
            )
            ");

        }                

    }

    public function get_property_tree_agent_by_id($params) {

        $property_id = $params['property_id'];
        $agent_id = $params['agent_id'];
        
        $api_id = 3; // Property Tree 

        if( $property_id > 0 ){            

            // get agency ID from property 
            $prop_sql = mysql_query("
            SELECT `agency_id`
            FROM `property`
            WHERE `property_id` = {$property_id}
            ");
            $prop_row = mysql_fetch_object($prop_sql);
            $agency_id = !empty($prop_row->agency_id) ? $prop_row->agency_id : 0;

            // API request limit solution
            $req_limit_params = array(
                'api_id' => $api_id,
                'request_limit' => $this->pt_request_limit,
                'sleep_interval_sec' => $this->pt_sleep_interval_sec,
                'agency_id' => $agency_id
            );
            $this->api_request_limit_counter_and_delay($req_limit_params);

            // get access token
            $agency_api_tokens_str = "
            SELECT `access_token`
            FROM `agency_api_tokens`
            WHERE `agency_id` = {$agency_id}
            AND `api_id` = {$api_id}
            ";
            $agency_api_tokens_sql =  mysql_query($agency_api_tokens_str) or die(mysql_error());
            $a_api_tok_row = mysql_fetch_object($agency_api_tokens_sql);
            $access_token = $a_api_tok_row->access_token;    
            
            //echo "api_prop_json: <br />";
            $end_points = "{$this->pt_api_gateway}/residentialproperty/v1/Agents/{$agent_id}";
            //echo "<br />";

            $curl = curl_init();

            // HTTP headers
            $http_header = array(
                "Authorization: Bearer {$access_token}",
                "Content-Type: application/json"
            );

            // API call
            $curl_opt = array(
                CURLOPT_URL => $end_points,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $http_header
            );

            curl_setopt_array( $curl, $curl_opt );

            $response = curl_exec($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            /*
            echo "response: <br />";
            echo "<pre>";
            print_r($response);
            echo "</pre>";
            */
            curl_close($curl);

            return array(
                'raw_response' => $response,
                'json_decoded_response' => json_decode($response),
                'http_status_code' => $responseCode
            );

        }        

    }

    /**
     * @param $params
     * @return int
     * 1 means linked
     * 0 means unlink
     */
    public function get_crm_property_unlinked_property($params = null)
    {
        $id = $params['crm_prop_id'];
        $api_id = $params['api_id'];

        // get data from api_property_data
        $query = mysqli_query("
            SELECT * FROM `api_property_data`
            WHERE `api_id` = {$api_id}
            AND `crm_prop_id` = {$id}
        ");

        $num_rows = mysqli_num_rows($query);

        return isset($num_rows) && !empty($num_rows) ? $num_rows : 0;
    }
}

?>