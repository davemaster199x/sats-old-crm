<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);
$old_prop_id = mysql_real_escape_string($_POST['old_prop_id']);

$p_str = "
	UPDATE `jobs`
	SET `property_id` = {$property_id}
	WHERE `id` = {$job_id}
";

mysql_query($p_str);

// job log
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
		'Job Moved',
		'".date('Y-m-d')."',
		'Job #<strong>{$job_id}</strong> moved from Property #<strong>{$old_prop_id}</strong> to Property #<strong>{$property_id}</strong>',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
");	

?>