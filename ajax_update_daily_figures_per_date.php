<?php

include('inc/init_for_ajax.php');

$dfpd_id = mysql_real_escape_string($_POST['dfpd_id']);

$working_day = mysql_real_escape_string($_POST['working_day']);
$date = mysql_real_escape_string($_POST['date']);
$techs = mysql_real_escape_string($_POST['techs']);
$jobs = mysql_real_escape_string($_POST['jobs']);
$sales = mysql_real_escape_string($_POST['sales']);
// number of jobs divided by number of techs
$country_id = $_SESSION['country_default'];

if( $dfpd_id!=''  ){ // UPDATE

	$sql = "
		UPDATE `daily_figures_per_date` 
		SET			
			`date` = '{$date}',
			`working_day` = '{$working_day}',
			`techs` = '{$techs}',
			`jobs` = '{$jobs}',
			`sales` = '{$sales}'
		WHERE `daily_figure_per_date_id` = {$dfpd_id}
	";
	mysql_query($sql);
	
}else{ // ADD
	
	$sql = "
		INSERT INTO
		`daily_figures_per_date` (
			`date`,
			`working_day`,
			`techs`,
			`jobs`,
			`sales`,
			`country_id`
		)
		VALUES (
			'{$date}',
			'{$working_day}',
			'{$techs}',
			'{$jobs}',
			'{$sales}',
			'{$country_id}'
		)
	";
	mysql_query($sql);
	
}


?>