<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$bn_id = mysql_real_escape_string($_REQUEST['bn_id']);
$bn_notes = mysql_real_escape_string($_REQUEST['bn_notes']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

$sql = "
	UPDATE `booking_notes`
	SET `notes` = '{$bn_notes}'
	WHERE `booking_notes_id` = {$bn_id}
";
mysql_query($sql);

// add booking notes log
$bnl_params = array(
	'bn_id' => $bn_id,
	'title' => 'Update Booking Notes',
	'msg' => 'Booking notes has been updated',
	'staff_id' => $staff_id,
	'country_id' => $country_id
);
$crm->addBookingNotesLog($bnl_params);

?>