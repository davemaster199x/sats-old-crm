<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$job_type = mysql_real_escape_string($_POST['job_type']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// update job
mysql_query("
	UPDATE `jobs`
	SET `job_type` = '{$job_type}'
	WHERE `id` = {$job_id}
");

// insert job log
mysql_query("
	 INSERT INTO 
	 job_log (
	  `staff_id`, 
	  `comments`, 
	  `eventdate`, 
	  `contact_type`, 
	  `job_id`,
	  `eventtime`
	 ) 
	 VALUES (
	  '{$staff_id}', 
	  'Status Changed from 240v Rebook to {$job_type}', 
	  '".date("Y-m-d")."', 
	  '240v Rebook', 
	  '{$job_id}',
	  '".date("H:i")."'
	 )
");

?>