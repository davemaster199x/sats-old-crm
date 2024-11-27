<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$trr_id_arr = $_POST['trr_id_arr'];
$job_id_arr = $_POST['job_id_arr'];
$operation =  mysql_real_escape_string($_POST['operation']);

foreach( $trr_id_arr as $trr_id ){
	
	echo $mr_str = "
		DELETE 
		FROM `tech_run_rows`
		WHERE `tech_run_rows_id` = {$trr_id}
	";

	$mr_sql = mysql_query($mr_str);
	
}



?>