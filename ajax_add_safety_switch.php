<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$ss_make = mysql_real_escape_string($_POST['ss_make']);
$ss_model = mysql_real_escape_string($_POST['ss_model']);
$ss_test = mysql_real_escape_string($_POST['ss_test']);

$ss_test = (is_numeric($ss_test))?$ss_test:'NULL';

$sql = "
	INSERT INTO
	`safety_switch` (
		`job_id`,
		`make`,
		`model`,
		`test`
	)
	VALUES (
		'{$job_id}',
		'{$ss_make}',
		'{$ss_model}',
		{$ss_test}			
	)
";

mysql_query($sql);

?>

	