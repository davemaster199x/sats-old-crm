<?php

class WS_SMS{
	
	public $api_key;
	public $api_secret;
    public $msg;
	public $to;
	public $sent_by;
	public $reply_url;
	public $dlr_url;
	public $yabbr_switch;
	public $yabbr_sms_api_key;
	public $yabbr_virtual_num;
	
	public function __construct($country_id,$msg,$to){
		
		$this->yabbr_switch = 1; ## 1 = AU only and using yabbr sms api | 0 = default using old sms api key
		if( $country_id == 1 ){ // AU
			$this->api_key = '57300872d42a549a242e9a4886bf10da';
			$this->api_secret = '666f8e3d50e3ca340c7fade14dd82442';	

			##new yabbr api > Note: when updating please update CI yabbr_sms_api_key also
			if( strpos(URL,"crmdev")===false ){ ## LIVE
				$this->yabbr_sms_api_key = 'YXBpOmI0MDJmMTU1NWZkMDFjNmMyZDRjOTliYTI0NmQ0MmRjYjM1OGY3ZDg5MDQxZDRmOTJlMGRmYjUyNDM5MWFmODQ=';	
				$this->yabbr_virtual_num = '61485817467';
			}else{ ## DEV
				$this->yabbr_sms_api_key = '';	
				$this->yabbr_virtual_num = '';
			}
			
		}else if( $country_id == 2 ){ // NZ
			$this->api_key = 'a294fdcf898af6af131f825f30dec91c';
			$this->api_secret = '9f4caf7c4f53eba3de7ffe77bbd8c0c4';

			##new yabbr api > Note: when updating please update CI yabbr_sms_api_key also
			if( strpos(URL,"crmdev")===false ){ ## LIVE
				$this->yabbr_sms_api_key = 'YXBpOmI0MDJmMTU1NWZkMDFjNmMyZDRjOTliYTI0NmQ0MmRjYjM1OGY3ZDg5MDQxZDRmOTJlMGRmYjUyNDM5MWFmODQ=';
				$this->yabbr_virtual_num = '61485817467';
			}else{ ## DEV
				$this->yabbr_sms_api_key = '';	
				$this->yabbr_virtual_num = '';
			}
		}
		
		$this->msg = $msg;
		$this->to = $to; 
		$this->reply_url = URL.'sms_replies_catch.php';
		$this->dlr_url = URL.'sms_delivered_catch.php';
		
	}
	
	public function __toString(){
		$str = "
		SMS API setup:<br /><br />
		Domain: ".CURRENT_DOMAIN."<br />
		User: ".($_SESSION['country_default']==1 && $this->yabbr_switch==1)?$this->yabbr_sms_api_key:$this->api_key."<br />
		Pass: {$this->api_secret}<br />
		SMS Message: {$this->msg}<br />
		Send To: {$this->to}<br />
		Reply Url: {$this->reply_url}<br />
		Delivered Url: {$this->dlr_url}<br />
		";
		return $str;
	}
	
	public function sendSMS(){
		
		// init curl object  
		$ch = curl_init();
		
		// parameters
		$sms_msg = trim($this->msg);
		$to_phone = trim($this->to); 

		if(  $_SESSION['country_default']==1 && $this->yabbr_switch==1 ){ ##use sms yabbr API fo AU only

			$api_key = trim($this->yabbr_sms_api_key);
            
            $header = array("x-api-key:$api_key","content-type: application/json");
            $api_endpoint = "https://api.yabbr.io/2019-01-23/messages";

            $data = array(
                'to' => $to_phone,
                'from' => $this->yabbr_virtual_num, ##Note: when updating please update CI also. And update Yabbr virtual number
                'content' => "$sms_msg",
                'type' => 'sms'
            );

            $payload = json_encode($data);

		}else{ ##old sms api key

			$reply_url = trim($this->reply_url);
			$dlr_url = trim($this->dlr_url);

			$authorization = base64_encode("{$this->api_key}:{$this->api_secret}");
			$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");

			$api_endpoint = "https://app.wholesalesms.com.au/api/v2/send-sms.json";

			$data = array(
				'message' => $sms_msg,
				'to' => $to_phone,
				'reply_callback' => $reply_url,
				'dlr_callback' => $dlr_url			
			);
			
			$payload = http_build_query($data);

		}

		// define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,			
			CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload
		);

		// apply those options
		curl_setopt_array($ch, $optArray);

		// execute request and get response
		$result = curl_exec($ch);
		curl_close($ch);
		
		$json_dec = json_decode($result);
		return $json_dec;
		
	}
	
	
	public function captureSMSdata($sms_res,$job_id,$sms_msg,$to_mob,$sent_by,$sms_type){
		
		$today = date('Y-m-d H:i:s');
		
		//if($sms_res->message_id!=''){

			if(  $_SESSION['country_default']==1 && $this->yabbr_switch==1 ){ 

				$sms_count = strlen($sms_msg);
            	$sms_cost = ceil(strlen($sms_msg)/160);

				$sql_str = "
					INSERT INTO
					`sms_api_sent`(
						`job_id`,
						`message`,
						`mobile`,
						`send_at`,
						`sent_by`,
						`sms_type`,
						`recipients`,
						`sms`,
						`cost`,
						`delivery_stats_pending`,
						`cb_status`,
						`error_code`,
						`error_desc`,
						`created_date`
					)
					VALUES(
						'".mysql_real_escape_string($job_id)."',
						'".mysql_real_escape_string($sms_msg)."',
						'".mysql_real_escape_string($to_mob)."',
						'".mysql_real_escape_string($sms_res->created)."',
						'".mysql_real_escape_string($sent_by)."',
						'".mysql_real_escape_string($sms_type)."',
						'1',
						'".$sms_count."',
						'".$sms_cost."',
						'1',
						'pending',
						'".mysql_real_escape_string($sms_res->error->code)."',
						'".mysql_real_escape_string($sms_res->error->description)."',
						'".mysql_real_escape_string($today)."'
					)
				";

				/*$sql_str = "
					INSERT INTO
					`sms_api_sent`(
						`job_id`,
						`message`,
						`mobile`,
						`send_at`,
						`sent_by`,
						`sms_type`,
						`recipients`,
						`sms`,
						`cost`,
						`delivery_stats_pending`,
						`cb_status`
						`error_code`,
						`error_desc`,
						`created_date`
					)
					VALUES(
						'".mysql_real_escape_string($job_id)."',
						'".mysql_real_escape_string($sms_msg)."',
						'".mysql_real_escape_string($to_mob)."',
						'".mysql_real_escape_string($sms_res->created)."',
						'".mysql_real_escape_string($sent_by)."',
						'".mysql_real_escape_string($sms_type)."',
						'1',
						'".$sms_count."',
						'".$sms_cost."',
						'1',
						'pending',
						'".mysql_real_escape_string($sms_res->error->code)."',
						'".mysql_real_escape_string($sms_res->error->description)."',	
						'".mysql_real_escape_string($today)."'	
					)
				"; */

			}else{

				$sql_str = "
					INSERT INTO
					`sms_api_sent`(
						`job_id`,
						`message_id`,
						`message`,
						`mobile`,
						`send_at`,
						`sent_by`,
						`sms_type`,
						`recipients`,
						`sms`,
						`cost`,
						`delivery_stats_delivered`,
						`delivery_stats_bounced`,
						`delivery_stats_responses`,
						`delivery_stats_pending`,
						`delivery_stats_optouts`,
						`error_code`,
						`error_desc`,
						`created_date`
					)
					VALUES(
						'".mysql_real_escape_string($job_id)."',
						'".mysql_real_escape_string($sms_res->message_id)."',
						'".mysql_real_escape_string($sms_msg)."',
						'".mysql_real_escape_string($to_mob)."',
						'".mysql_real_escape_string($sms_res->send_at)."',
						'".mysql_real_escape_string($sent_by)."',
						'".mysql_real_escape_string($sms_type)."',
						'".mysql_real_escape_string($sms_res->recipients)."',
						'".mysql_real_escape_string($sms_res->sms)."',
						'".mysql_real_escape_string($sms_res->cost)."',
						'".mysql_real_escape_string($sms_res->delivery_stats->delivered)."',
						'".mysql_real_escape_string($sms_res->delivery_stats->bounced)."',
						'".mysql_real_escape_string($sms_res->delivery_stats->responses)."',
						'".mysql_real_escape_string($sms_res->delivery_stats->pending)."',
						'".mysql_real_escape_string($sms_res->delivery_stats->optouts)."',
						'".mysql_real_escape_string($sms_res->error->code)."',
						'".mysql_real_escape_string($sms_res->error->description)."',
						'".mysql_real_escape_string($today)."'	
					)
				";

			}

			mysql_query($sql_str);
			
		//}
		
	}
	
	
	public function getBalance(){
		
		// init curl object  
		$ch = curl_init();
		
		//$url = "https://app.wholesalesms.com.au/api/v2/get-balance.json";

		$authorization = base64_encode("{$this->api_key}:{$this->api_secret}");
		$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
		
		$api_endpoint = "https://app.wholesalesms.com.au/api/v2/get-balance.json";

		// define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true
		);

		// apply those options
		curl_setopt_array($ch, $optArray);

		// execute request and get response
		$result = curl_exec($ch);
		curl_close($ch);
		
		$json_data = json_decode($result);
		//print_r($json_data);
		return $json_data->balance;
	}
	
	
	public function getSMStype(){
		
		return mysql_query("
			SELECT *
			FROM `sms_api_type`
			WHERE `active` = 1
		");
		
	}
	
	
}


?>