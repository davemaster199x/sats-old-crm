<?php

include('inc/init_for_ajax.php');

$auto_emails = mysql_real_escape_string($_POST['auto_emails']);

// toggle auto email
$set_to = ($auto_emails==1)?0:1;

// toggle it off
if( $set_to == 0 ){

	rename("cronjobs","cronjobs_disabled");
	
}else{
	
	// toggle ON
	rename("cronjobs_disabled","cronjobs");
	
}

mysql_query("
	UPDATE `crm_settings` 
	SET `auto_emails` = {$set_to}
");


?>