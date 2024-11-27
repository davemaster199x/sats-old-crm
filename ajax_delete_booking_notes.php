<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$bn_id = mysql_real_escape_string($_REQUEST['bn_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

$sql = "
	UPDATE `booking_notes`
	SET `active` = 0
	WHERE `booking_notes_id` = {$bn_id}
";
mysql_query($sql);

// add booking notes log
$bnl_params = array(
	'bn_id' => $bn_id,
	'title' => 'Notes Deleted',
	'msg' => 'Booking notes has been deleted',
	'staff_id' => $staff_id,
	'country_id' => $country_id
);
$crm->addBookingNotesLog($bnl_params);

?>