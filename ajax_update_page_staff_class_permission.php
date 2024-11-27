<?php

// menu ajax update
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$page_id = mysql_real_escape_string($_POST['page_id']);
$staff_class = mysql_real_escape_string($_POST['staff_class']);
$allow = mysql_real_escape_string($_POST['allow']);

if( $allow == 1 ){
	
	$sql = "
		INSERT INTO 
		`crm_page_permission_class` (
			`page`,
			`staff_class`
		) 
		VALUES(
			{$page_id},
			{$staff_class}
		)	
	";
	mysql_query($sql);
	
}else if( $allow == 0 ){
	
	$sql = "
		DELETE
		FROM `crm_page_permission_class`
		WHERE `page` = {$page_id}
		AND `staff_class` = {$staff_class}
	";
	mysql_query($sql);
	
}

?>