<?php

include('inc/init_for_ajax.php');

$et_id = mysql_real_escape_string($_REQUEST['et_id']);

$sql = mysql_query("
	SELECT *
	FROM `email_templates`
	WHERE `active` = 1
	AND `email_templates_id` = {$et_id}
");
$email_temp = mysql_fetch_array($sql);

$emp_temp_arr = array(
	'email_templates_id' => $email_temp['email_templates_id'],
	'subject' => $email_temp['subject'],
	'body' => $email_temp['body'],
	'template_name' => $email_temp['template_name']
);

echo json_encode($emp_temp_arr);

?>