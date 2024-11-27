<?php

include('inc/init_for_ajax.php');

$job_id_arr = $_POST['job_id'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

foreach($job_id_arr as $job_id){
		
	// update job to merged
	mysql_query("
		UPDATE `jobs`
		SET `status` = 'Merged Certificates'
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
	  'Moved to <strong>Merged Certificates</strong>', 
	  '".date("Y-m-d")."', 
	  'Merged Certificates', 
	  '{$job_id}',
	  '".date("H:i")."'
	 )
	");
	
}

?>