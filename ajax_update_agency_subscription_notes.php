<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$subscription_notes = mysql_real_escape_string($_POST['subscription_notes']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

$sql = "
	UPDATE `agency` 
	SET 
		`subscription_notes` = '{$subscription_notes}',
		`subscription_notes_update_ts` = '".date('Y-m-d H:i:s')."',
		`subscription_notes_update_by` = {$staff_id}
    WHERE `agency_id` = {$agency_id}
";
mysql_query($sql);
?>