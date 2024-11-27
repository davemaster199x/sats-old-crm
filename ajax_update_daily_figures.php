<?php

include('inc/init_for_ajax.php');

$df_id = mysql_real_escape_string($_POST['df_id']);

$date = mysql_real_escape_string($_POST['date']);
$month = mysql_real_escape_string($_POST['month']);
$budget = mysql_real_escape_string($_POST['budget']);
$working_days = mysql_real_escape_string($_POST['working_days']);
// number of jobs divided by number of techs
$country_id = $_SESSION['country_default'];

if( $df_id!=''  ){ // UPDATE

	$sql = "
		UPDATE `daily_figures` 
		SET
			`budget` = '{$budget}',
			`working_days` = '{$working_days}'
		WHERE `daily_figure_id` = {$df_id}
	";
	mysql_query($sql);
	
}else{ // daily_figure_per_date_id
	
	$sql = "
		INSERT INTO
		`daily_figures` (
			`month`,
			`budget`,
			`working_days`,
			`country_id`
		)
		VALUES (
			'{$month}',
			'{$budget}',
			'{$working_days}',
			'{$country_id}'
		)
	";
	mysql_query($sql);
	
}


?>