<?php

	include('inc/init.php');
	
	$sa_sql = mysql_query("
		SELECT *
		FROM `site_accounts` 
		WHERE `country_id` = {$_SESSION['country_default']}
	");
	
	
	// filename
	$filename = "site_accounts_".date("d_m_Y").".csv";
	
	// send headers for download
	header("Content-Type: text/csv");
	header("Content-Disposition: Attachment; filename=$filename");
	header("Pragma: no-cache");
	
	// body content
	echo "Website/Email,Username,Password,Note,Expiry date,Last Updated\n";
	
	while ( $row = mysql_fetch_array($sa_sql) ){		
			$expiry_date = ($row['expiry_date']!="")?date("d/m/Y",strtotime($row['expiry_date'])):'----';
			$last_updated = ($row['expiry_date']!="")?date("d/m/Y",strtotime($row['last_updated'])):'----';
			echo "\"{$row['website_or_email']}\",\"{$row['username']}\",\"{$row['password']}\",\"{$row['notes']}\",\"{$expiry_date}\",\"{$last_updated}\"\n";			
	}		
		

?>

