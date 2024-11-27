<?php

include('inc/init_for_ajax.php');

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$trr_id_arr = $_POST['trr_id_arr'];

foreach( $trr_id_arr as $trr_id ){
	
	// update
	echo $sql = "
	UPDATE `tech_run_rows`
	SET `highlight_color` = NULL
	WHERE `tech_run_id` = {$tr_id}
	AND `tech_run_rows_id` = ".mysql_real_escape_string($trr_id)."
	";
	mysql_query($sql);
	
}

?>