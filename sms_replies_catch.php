<?php 
include('inc/init_for_ajax.php');
require __DIR__ . '/vendor/autoload.php';

$options = array(
	'cluster' => PUSHER_CLUSTER,
	'useTLS' => false
);
$pusher = new Pusher\Pusher(
	PUSHER_KEY,
	PUSHER_SECRET,
	PUSHER_APP_ID,
	$options
);

$crm = new Sats_Crm_Class();


// catch the GET data returned by SMS API
$message_id = mysql_real_escape_string($_REQUEST['message_id']);
$mobile = mysql_real_escape_string($_REQUEST['mobile']);
$datetime_entry = mysql_real_escape_string($_REQUEST['datetime_entry']);
$response = mysql_real_escape_string($_REQUEST['response']);
$longcode = mysql_real_escape_string($_REQUEST['longcode']);


$today = date('Y-m-d H:i:s');


if($message_id!=''){
	
	mysql_query("
		INSERT INTO
		`sms_api_replies`(
			`message_id`,
			`mobile`,
			`datetime_entry`,
			`response`,
			`longcode`,
			`created_date`
		)
		VALUES(
			'{$message_id}',
			'{$mobile}',
			'{$datetime_entry}',
			'{$response}',
			'{$longcode}',
			'{$today}'	
		)
	");
	
	// get SMS other infos on sms_api_sent table
	$sql = mysql_query("
		SELECT sas.`sent_by`, a.`country_id`, sas.`sms_type` 
		FROM `sms_api_sent` AS sas 
		LEFT JOIN `jobs` AS j ON sas.`job_id` = j.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE sas.`message_id` = {$message_id}
	");

	$row = mysql_fetch_array($sql);
	$notify_staff = $row['sent_by'];
	//$country_id = $row['country_id'];
	$country_id = CURRENT_COUNTRY;
	$mob_num = '0'.substr($mobile,2);
	
	
	// set SMS notification	
	//$notf_msg = "New <a href=\"incoming_sms.php\">SMS</a> from {$mob_num}";
	
	$crm_ci_page = 'sms/view_incoming_sms';
	$notf_msg = "New <a href=\"{$crm_ci_page}\">SMS</a> from {$mob_num}";

	$notf_type = 2; // SMS notification
	$jparams = array(
		'notf_type'=> $notf_type,
		'notf_msg'=> $notf_msg,
		'staff_id'=> $notify_staff,
		'country_id'=> $country_id		
	);
	$crm->insertNewNotification($jparams);	

	// pusher notification
	$data['notif_type'] = $notf_type;
	$ch = "ch".$notify_staff;
	$ev = "ev01";
	$out = $pusher->trigger($ch, $ev, $data);
	

	// If No Show
	if( $row['sms_type'] == 4 ){
		
		$cust_serv_pips_arr = [];
		$dom_url = $_SERVER['SERVER_NAME'];
		
		// customer service people
		if( $country_id == 1 ){ // AU
		
			/*
			AU:
			2175 - Thalia
			2058 - Jemma
			2191 - Ashlee R
			2209 - Hine
			*/
			if( strpos($dom_url,"crmdev")===false ){ // live
				$cust_serv_pips_arr = array(2175,2058,2191,2209);
			}else{ // dev
				$cust_serv_pips_arr = array(2070,11);
			}
			
		}else if( $country_id == 2 ){ // NZ
			
			/*
			NZ:
			2147 - Tiana
			2124 - Ashley O
			*/
			if( strpos($dom_url,"crmdev")===false ){ // live
				$cust_serv_pips_arr = array(2147,2124);
			}else{ // dev
				$cust_serv_pips_arr = array(2070,11);
			}
		}
		
		
		foreach( $cust_serv_pips_arr as $cust_serv_pips ){
			
			$notf_type = 3; // SMS No Show notification
			//$notf_msg = "New <a href=\"incoming_sms.php\">No Show SMS</a> from {$mob_num}";		

			$crm_ci_page = 'sms/view_incoming_sms';
			$notf_msg = "New <a href=\"{$crm_ci_page}\">SMS</a> from {$mob_num}";

			$jparams = array(
				'notf_type'=> $notf_type,
				'notf_msg'=> $notf_msg,
				'staff_id'=> $cust_serv_pips,
				'country_id'=> $country_id		
			);
			$crm->insertNewNotification($jparams);

			// pusher notification
			$data['notif_type'] = $notf_type;
			$ch = "ch".$cust_serv_pips;
			$ev = "ev01";
			$out = $pusher->trigger($ch, $ev, $data);
			
		}	
		
	}
	
	
	
}


?>