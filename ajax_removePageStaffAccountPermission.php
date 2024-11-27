<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$cppu_id = mysql_real_escape_string($_POST['cppu_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql = "
	DELETE
	FROM `crm_page_permission_user`
	WHERE `cppu_id` = {$cppu_id}
";
mysql_query($sql);


?>