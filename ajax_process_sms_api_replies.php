<?php 
include('inc/init_for_ajax.php');

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$job_id = mysql_real_escape_string($_POST['job_id']);
$message_id = mysql_real_escape_string($_POST['message_id']);
$sas_id = mysql_real_escape_string($_POST['sas_id']);
$sar_id = mysql_real_escape_string($_POST['sar_id']);
$tenant_name = mysql_real_escape_string($_POST['tenant_name']);
$reply_msg = mysql_real_escape_string($_POST['reply_msg']);
$btn_used = mysql_real_escape_string($_POST['btn_used']);
$sms_type = mysql_real_escape_string($_POST['sms_type']);


// get SMS replies data
$sms_sql = mysql_query("
	SELECT *
	FROM `sms_api_replies`
	WHERE `message_id` = {$message_id}
	AND `sms_api_replies_id` = {$sar_id}
");
$sms = mysql_fetch_array($sms_sql);
$datetime_entry = $sms['created_date'];

$sms_reply_date = date('Y-m-d',strtotime($datetime_entry));
$sms_reply_time = date('H:i',strtotime($datetime_entry));


if( $btn_used == 'save' ){
	
	$jl_ct_txt = 'Saved';
	
	// mark as log saved
	mysql_query("
		UPDATE `sms_api_replies`
		SET `saved` = 1
		WHERE `sms_api_replies_id` = {$sar_id}
	");
	
}else{
	
	$jl_ct_txt = 'Processed';
	
	// update unread
	mysql_query("
		UPDATE `sms_api_replies`
		SET `unread` = NULL
		WHERE `sms_api_replies_id` = {$sar_id}
	");
	
}


// insert job logs
mysql_query("
	INSERT INTO 
	`job_log` (
		`contact_type`,
		`eventdate`,
		`comments`,
		`job_id`, 
		`staff_id`,
		`eventtime`
	) 
	VALUES (
		'SMS Replies {$jl_ct_txt}',
		'{$sms_reply_date}',
		'{$tenant_name} replied <strong>\"{$reply_msg}\"</strong>',
		{$job_id}, 
		{$staff_id},
		'{$sms_reply_time}'
	)
");

// do not delete Thank you SMS
$ty_sms_type_id = 18; // thank you SMS
if( $sms_type != $ty_sms_type_id ){
	
	// DELETE sms
	// SMS sent
	mysql_query("
		DELETE
		FROM `sms_api_sent`
		WHERE `message_id` = {$message_id}
		AND `sms_api_sent_id` = {$sas_id}
	");
	// SMS replies
	mysql_query("
		DELETE
		FROM `sms_api_replies`
		WHERE `message_id` = {$message_id}
		AND `sms_api_replies_id` = {$sar_id}
	");
	
}



?>

