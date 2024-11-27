<?php 
include($_SERVER['DOCUMENT_ROOT'].'/inc/init_for_ajax.php');

// catch the GET data returned by SMS API
$message_id = mysql_real_escape_string($_REQUEST['message_id']);
$mobile = mysql_real_escape_string($_REQUEST['mobile']);
$datetime = mysql_real_escape_string($_REQUEST['datetime']);
$status = mysql_real_escape_string($_REQUEST['status']);

if($message_id!=''){
	
	mysql_query("
		UPDATE `sms_api_sent`
		SET 
			`cb_mobile` = '{$mobile}',
			`cb_datetime` = '{$datetime}',
			`cb_status` = '{$status}'
		WHERE `message_id` = {$message_id}
	");
	
}

?>