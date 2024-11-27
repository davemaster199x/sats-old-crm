<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$trr_id_arr = $_POST['trr_id_arr'];
$job_id_arr = $_POST['job_id_arr'];
$operation =  mysql_real_escape_string($_POST['operation']);

if( $operation == 'hide' ){
	
	foreach( $trr_id_arr as $trr_id ){
	
	echo $mr_str = "
		UPDATE `tech_run_rows`
		SET `hidden` = 1
		WHERE `tech_run_id` = '{$tr_id}'
		AND `tech_run_rows_id` = {$trr_id}
	";

	$mr_sql = mysql_query($mr_str);
		
	}
	
}else{
	
	foreach( $trr_id_arr as $index => $trr_id ){
	
	echo $mr_str = "
		UPDATE `tech_run_rows`
		SET `hidden` = 0
		WHERE `tech_run_id` = '{$tr_id}'
		AND `tech_run_rows_id` = {$trr_id}
	";

	$mr_sql = mysql_query($mr_str);
	
	echo $jsql = "
		UPDATE `jobs`
		SET 
			`unavailable` = NULL,
			`unavailable_date` = NULL
		WHERE `id` = {$job_id_arr[$index]}
	";
	mysql_query($jsql);
	
	echo $jlsql = "
		DELETE 
		FROM `job_log`
		WHERE `job_id` = {$job_id_arr[$index]}
		AND `comments` LIKE  '%- Unavailable%'
	";
	mysql_query($jlsql);
		
	}
	
}




?>