<?php

// menu ajax update
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$menu_id = mysql_real_escape_string($_POST['menu_id']);
$staff_class = mysql_real_escape_string($_POST['staff_class']);
$allow = mysql_real_escape_string($_POST['allow']);

if( $allow == 1 ){
	$sql = "
		INSERT INTO 
		`menu_permission_class` (
			`menu`,
			`staff_class`
		) 
		VALUES(
			{$menu_id},
			{$staff_class}
		)	
	";
	mysql_query($sql);
}else if( $allow == 0 ){
	$sql = "
		DELETE
		FROM `menu_permission_class`
		WHERE `menu` = {$menu_id}
		AND `staff_class` = {$staff_class}
	";
	mysql_query($sql);
}

?>