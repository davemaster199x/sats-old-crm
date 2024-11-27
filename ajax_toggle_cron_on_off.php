<?php

include('inc/init_for_ajax.php');

$cron_status = mysql_real_escape_string($_POST['cron_status']);
$cron_file = mysql_real_escape_string($_POST['cron_file']);
$db_field = mysql_real_escape_string($_POST['db_field']);
$country_id = $_SESSION['country_default'];
$cron_folder = 'cronjobs';
$cntry_append_txt = ($country_id==1)?'_au':'_nz';
$disabled_append_txt = '_disabled.php';
$php_file_ext = '.php';


/*
echo "
Cron File: {$cron_file}<br/>
Cron Status: {$cron_status}<br >
Db Field: {$db_field}<br >
Country: {$country_id}<br />
";
*/


if( $cron_status == 1 ){

	// Enable
	$from = "{$cron_folder}/{$cron_file}{$cntry_append_txt}{$disabled_append_txt}";
	$to = "{$cron_folder}/{$cron_file}{$cntry_append_txt}{$php_file_ext}";
	
}else{
	
	// Disable
	$from = "{$cron_folder}/{$cron_file}{$cntry_append_txt}{$php_file_ext}";
	$to = "{$cron_folder}/{$cron_file}{$cntry_append_txt}{$disabled_append_txt}";
	
}

//echo "From: {$from} - {$to}<br />";
rename($from,$to);


//echo "<br />";
$sql = "
	UPDATE `crm_settings` 
	SET `{$db_field}` = {$cron_status}
	WHERE `country_id` = {$country_id}
";
mysql_query($sql);


?>