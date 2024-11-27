<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$mpu_id = mysql_real_escape_string($_POST['mpu_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql = "
	DELETE
	FROM `menu_permission_user`
	WHERE `mpu_id` = {$mpu_id}
";
mysql_query($sql);


?>