<?php

require_once('inc/init.php');

$et_id = mysql_real_escape_string($_POST['et_id']);
$template_name = mysql_real_escape_string($_POST['template_name']);
$subject = mysql_real_escape_string($_POST['subject']);
$temp_type = mysql_real_escape_string($_POST['temp_type']);
$et_body = mysql_real_escape_string($_POST['et_body']);
$show_to_call_centre = mysql_real_escape_string($_POST['show_to_call_centre']);
$active = mysql_real_escape_string($_POST['active']);

// add template
$str = "
	UPDATE `email_templates` 
	SET	
		`template_name` = '{$template_name}',
		`subject` = '{$subject}',
		`temp_type` = '{$temp_type}',
		`body` = '{$et_body}',
		`active` = '{$active}',
		`show_to_call_centre` = '{$show_to_call_centre}'
	WHERE `email_templates_id` = {$et_id}
";
mysql_query($str);

header("location: email_template_details.php?id={$et_id}&update=1");



?>