<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$colour_id = mysql_real_escape_string($_POST['colour_id']);
$time = mysql_real_escape_string($_POST['time']);
$jobs_num = mysql_real_escape_string($_POST['jobs_num']);
$no_keys = mysql_real_escape_string($_POST['no_keys']);
$booked_jobs = mysql_real_escape_string($_POST['booked_jobs']);

// compute booking status
$status_dif = $jobs_num-$booked_jobs;

if( $jobs_num>0 ){
	if( $status_dif>0 ){
		$status_dif_txt = "-{$status_dif}";
	}else{
		$status_dif_txt = "FULL";
	}
}


// check if this color already has values to determine update or insert
$sql = mysql_query("
	SELECT *
	FROM `colour_table`
	WHERE `tech_run_id` = {$tr_id}
	AND `colour_id` = {$colour_id}
");


if( mysql_num_rows($sql)>0 ){
	
	// data already exist, do update
	mysql_query("
		UPDATE `colour_table`
		SET 
			`colour_id` = '{$colour_id}',
			`time` = '{$time}',
			`jobs_num` = '{$jobs_num}',
			`no_keys` = '{$no_keys}',
			`booking_status` = '{$status_dif_txt}'
		WHERE `tech_run_id` = {$tr_id}
		AND `colour_id` = {$colour_id}
	");
	
}else{ // insert

	// kms
	mysql_query("
		INSERT INTO 
		`colour_table`(
			`tech_run_id`,
			`colour_id`,
			`time`,
			`jobs_num`,
			`no_keys`,
			`booking_status`
		)
		VALUES(
			'{$tr_id}',
			'{$colour_id}',
			'{$time}',
			'{$jobs_num}',
			'{$no_keys}',
			'{$status_dif_txt}'
		)
	");
	
}



?>