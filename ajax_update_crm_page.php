<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$page_id = mysql_real_escape_string($_POST['page_id']);
$page_name = mysql_real_escape_string($_POST['page_name']);
$page_url = mysql_real_escape_string($_POST['page_url']);
$menu = mysql_real_escape_string($_POST['menu']);
$active = mysql_real_escape_string($_POST['active']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql = "
	UPDATE `crm_pages` 
	SET 
		`page_name` = '{$page_name}',
		`page_url` = '{$page_url}',
		`menu` = {$menu},
		`active` = {$active}
	WHERE `crm_page_id` = {$page_id}
";
mysql_query($sql);


?>