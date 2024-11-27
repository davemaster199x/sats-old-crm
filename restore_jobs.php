<?

include('inc/init.php');

$job_id = $_GET['job_id'];

// restore job
mysql_query("
	UPDATE `jobs`
	SET `del_job` = 0
	WHERE `id` = {$job_id}
");

// insert logs
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
		'Job Restored',
		'".date('Y-m-d')."',
		'Job <strong>Restored</strong>',
		{$job_id}, 
		'".$_SESSION['USER_DETAILS']['StaffID']."',
		'".date('H:i')."'
	)
");	

header("location:/view_job_details.php?id={$job_id}");

?>
