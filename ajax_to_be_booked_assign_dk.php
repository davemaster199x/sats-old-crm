<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$job_id = $_POST['job_id'];
$tech_id = mysql_real_escape_string($_POST['tech_id']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = date("Y-m-d",strtotime(str_replace("/","-",$date)));
$date_dmy = date("d/m/Y",strtotime(str_replace("/","-",$date)));
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

foreach($job_id as $val){
	
	// update job
	echo $sql = "
		UPDATE `jobs`
		SET 
			`status` = 'To Be Booked',
			`assigned_tech` = '{$tech_id}',
			`date` = '{$date2}',
			`tech_notes` = 'Door Knock',
			`booked_with` = 'Agent',
			`booked_by` = '{$staff_id}',
			`door_knock` = 1
		WHERE `id` = {$val}
	";
	mysql_query($sql);
	
	echo "<br />";
	
	// get tech name
	$sa_sql = mysql_query("
		SELECT `FirstName`, `LastName`
		FROM `staff_accounts`
		WHERE `StaffID` = '{$tech_id}'
	");
	$sa = mysql_fetch_array($sa_sql);
	$tech_name = $crm->formatStaffName($sa['FirstName'],$sa['LastName']);
	
	echo $sql2 = "
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
			'Door Knock Booked',
			'".date('Y-m-d')."',
			'Door Knock Booked for {$date_dmy}. Technician {$tech_name}',
			'{$val}', 
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('H:i')."'
		)
	";
	mysql_query($sql2);
	
}


?>