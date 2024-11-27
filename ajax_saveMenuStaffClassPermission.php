<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$menu_id = mysql_real_escape_string($_POST['menu_id']);
$staff_class = mysql_real_escape_string($_POST['staff_class']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


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


?>