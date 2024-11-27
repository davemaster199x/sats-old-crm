<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$sucess = "";
$error = "";

$url = mysql_real_escape_string($_POST['url']);
$title = mysql_real_escape_string($_POST['title']);
$heading = $_POST['heading'];
$states = implode(',',$_POST['states']);
$due_date = mysql_real_escape_string($_POST['due_date']);
$due_date2 = ($crm->isDateNotEmpty($due_date)==true)?"'".$crm->formatDate($due_date)."'":'NULL';
	
	
if(ifCountryHasState($_SESSION['country_default'])==true){
	$state_field = '`states`,';
	$state_val = "'{$states}',";
}else{
	$state_field = '';
	$state_val = '';
}

mysql_query("
	INSERT INTO
	`resources`(
		`type`,
		`url`,
		`title`,
		{$state_field}
		`date`,
		`resources_header_id`,
		`due_date`,
		`country_id`
	)
	VALUES(
		2,
		'{$url}',
		'{$title}',
		{$state_val}
		'".date("Y-m-d H:i:s")."',
		'{$heading}',
		{$due_date2},
		{$_SESSION['country_default']}
	)
");	


header("Location: resources.php?success=1");

?>