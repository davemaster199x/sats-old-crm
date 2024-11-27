<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$mpc_id = mysql_real_escape_string($_POST['mpc_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql = "
	DELETE
	FROM `menu_permission_class`
	WHERE `mpc_id` = {$mpc_id}
";
mysql_query($sql);


?>