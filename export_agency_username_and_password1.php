<?php

	include('inc/init.php');
	
	$country_id = $_SESSION['country_default'];
	
	$crm = new Sats_Crm_Class;
	
	
	
	function decryt_password($password){
	
		$encrypt = new cast128();
		$encrypt->setkey(SALT);
		
		if(UTF8_USED){
			$decr_pass = $encrypt->decrypt(utf8_decode($password));
		}else{
			$decr_pass = $encrypt->decrypt($password);
		}
		
		return $decr_pass;
	
	}
	
	
	// filename
	//$filename = "Agencies_".date("d/m/Y").".csv";
	$filename = "agency_passwords-1.csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename={$filename}");
	header("Pragma: no-cache");

	$export_str = '';
	
	$export_str .= "Agency ID,Agency Username,Agency Password\n";
	
	$sql_str = "
		SELECT `agency_id`, `login_id`, `password`
		FROM `agency`
		WHERE `country_id` = {$country_id}
		AND `status` = 'active'
		LIMIT 0, 200
	";
	
	$sql = mysql_query($sql_str);
	  
	while( $row = mysql_fetch_array($sql) ){
		
		$password = decryt_password($row['password']);

		$export_str .= "{$row['agency_id']},\"{$row['login_id']}\",\"{$password}\"\n";
		
	}
	
	
	echo $export_str;
	

?>

