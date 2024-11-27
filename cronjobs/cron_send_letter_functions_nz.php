<h1>Send Letter run Cron functions</h1>
<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');


$staff_id = '';
$country_id = 2;


echo "<br />";

// SEND TENANT EMAIL OR SMS
echo $sql_str = "
	SELECT j.`id` AS jid, p.`property_id`
	FROM  `jobs` AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE p.`deleted` =0
	AND a.`status` =  'active'
	AND j.`del_job` =0
	AND a.`country_id` ={$country_id}
	AND j.`status` =  'Send Letters'
	AND (
		j.`comments` =  ''
		OR j.`comments` IS NULL
	)
";

$sql = mysql_query($sql_str);

while( $row = mysql_fetch_array($sql) ){
	
	$has_tenants = 0;
	$has_tenant_email = 0;
	$has_mobile_num = 0;
	
	
	$pt_params = array( 
		'property_id' => $row['property_id'],
		'active' => 1
	 );
	$pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);
	
	while( $pt_row = mysql_fetch_array($pt_sql) ){
		
		// check if it has tenants
		if(  $pt_row['tenant_firstname'] != "" || $pt_row['tenant_lastname'] != ""  ){
			$has_tenants = 1;
		}
		
		// check if there is at least 1 tenant email
		if( $pt_row['tenant_email'] != ""  ){
			$has_tenant_email = 1;
		}
		
		// check if there is at least 1 tenant mobile
		if( $pt_row['tenant_mobile'] != "" ){
			$has_mobile_num = 1;
		}
		
		
	}
	
	// has mobile but no email
	if( $has_mobile_num == 1 && $has_tenant_email == 0 ){
		send_letters_send_tenant_sms($row['jid'],$staff_id,$country_id);
	}
	
	// has email
	if( $has_tenant_email == 1 ){
		send_letters_send_tenant_email($row['jid'],$staff_id,$country_id);
	}
	
	// no tenants
	if( $has_tenants == 0 ){
		send_letters_no_tenant_email($row['jid'],$staff_id,$country_id);
	}
	
}

?>