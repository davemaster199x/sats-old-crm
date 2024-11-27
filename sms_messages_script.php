<?php

include('inc/init.php');

$title = mysql_real_escape_string($_POST['title']);
$msg = mysql_real_escape_string($_POST['msg']);

mysql_query("
	INSERT INTO 
	`sms_messages`(
		`title`,
		`message`,
		`country_id`
	)
	VALUES(
		'{$title}',
		'{$msg}',
		{$_SESSION['country_default']}
	)
");

header("Location: sms_messages.php?success=1");

?>