<?php

include('inc/init_for_ajax.php');

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$country_id = mysql_real_escape_string($_POST['country_id']);

$trr_sql = TechRunSortByColor($tr_id,$country_id);

// insert tech run rows
$i = 2;
while( $trr = mysql_fetch_array($trr_sql) ){

	// update
	echo $sql = "
		UPDATE `tech_run_rows`
		SET 
			`sort_order_num` = '{$i}',
			`dnd_sorted` = 0
		WHERE `tech_run_rows_id` = {$trr['tech_run_rows_id']}
		";
	mysql_query($sql);
	$i++;
}

?>