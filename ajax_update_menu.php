<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$menu_id = mysql_real_escape_string($_POST['menu_id']);
$menu_name = mysql_real_escape_string($_POST['menu_name']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql = "
	UPDATE `menu` 
	SET `menu_name` = '{$menu_name}'
	WHERE `menu_id` = {$menu_id}
";
mysql_query($sql);


?>