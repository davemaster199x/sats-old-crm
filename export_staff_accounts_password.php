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

	$filename = "staff_accounts_passwords.csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename={$filename}");
	header("Pragma: no-cache");

	$export_str = '';
	
	$export_str .= "Staff ID,Password\n";
	
	$sql_str = "
		SELECT `StaffID`, `Password`
		FROM `staff_accounts`
		WHERE `Deleted` = 0
	";
	
	$sql = mysql_query($sql_str);
	  
	while( $row = mysql_fetch_array($sql) ){
		
		$password = decryt_password($row['Password']);

		$export_str .= "{$row['StaffID']},\"{$password}\"\n";
		
	}
	
	
	echo $export_str;
	

?>

