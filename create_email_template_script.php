<?php

require_once('inc/init.php');

$template_name = mysql_real_escape_string($_POST['template_name']);
$subject = mysql_real_escape_string($_POST['subject']);
$temp_type = mysql_real_escape_string($_POST['temp_type']);
$show_to_call_centre = mysql_real_escape_string($_POST['show_to_call_centre']);
$et_body = mysql_real_escape_string($_POST['et_body']);

// add template
$str = "
	INSERT INTO
	`email_templates` (
		`template_name`,
		`subject`,
		`temp_type`,
		`body`,
		`show_to_call_centre`
	)
	VALUES (
		'{$template_name}',
		'{$subject}',
		'{$temp_type}',
		'{$et_body}',
		'{$show_to_call_centre}'
	)
";
mysql_query($str);

$id = mysql_insert_id();
header("location: email_template_details.php?id={$id}&success=1");



?>