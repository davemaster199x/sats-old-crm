<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];
$preferred_time = $_POST['preferred_time'];
$out_of_tech_hours = $_POST['out_of_tech_hours'];

$preferred_time_query = "SELECT `preferred_time` FROM `jobs` where `id` = {$job_id}"; 
$result = mysql_query($preferred_time_query);

$row = mysql_fetch_row($result);

if (empty($row[0])) {
	$logs_sql = "
		INSERT INTO `job_log` (`contact_type`,`eventdate`,`comments`,`job_id`,`staff_id`,`eventtime`) 
		VALUES ('Job Update','" . date('Y-m-d') . "','Preferred time updated to <b>{$preferred_time}</b>',{$job_id},'" . $_SESSION['USER_DETAILS']['StaffID'] . "','" . date('H:i') . "'
	)";
} else {
	$logs_sql = "
		INSERT INTO `job_log` (`contact_type`,`eventdate`,`comments`,`job_id`,`staff_id`,`eventtime`) 
		VALUES ('Job Update','" . date('Y-m-d') . "','Preferred time updated from <b>{$row[0]}</b> to <b>{$preferred_time}</b>',{$job_id},'" . $_SESSION['USER_DETAILS']['StaffID'] . "','" . date('H:i') . "'
	)";
}
mysql_query($logs_sql);

// update preferred time
$sql = "
	UPDATE `jobs`
	SET
		`preferred_time` = '".mysql_real_escape_string($preferred_time)."',
		`preferred_time_ts` = '".date('Y-m-d H:i:s')."',
		`out_of_tech_hours` = '".mysql_real_escape_string($out_of_tech_hours)."'
	WHERE `id` = ".mysql_real_escape_string($job_id)."
";
mysql_query($sql);

?>