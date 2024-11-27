<?php
include('inc/init_for_ajax.php');

// fetch all jobs that marked as printed
$sql_str = "
	SELECT `id` AS jid
	FROM `jobs`
	WHERE `is_printed` = 1
";
$sql = mysql_query($sql_str);

while( $row = mysql_fetch_array($sql) ){
	
	$job_id = $row['jid'];

	// unmark to be printed
	$sql2_str = "
		UPDATE `jobs`
		SET
			`to_be_printed` = 0,
			`is_printed` = 0
		WHERE `id` = {$job_id}
	";
	mysql_query($sql2_str);

	// job log
	$sql3_str = "
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
			'To Be Printed',
			'".date('Y-m-d')."',
			'Job has been cleared from to be printed',
			{$job_id}, 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	";
	mysql_query($sql3_str);
	
}

?>