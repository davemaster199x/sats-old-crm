<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$country_id = $_SESSION['country_default'];
$agency_booking_notes = mysql_real_escape_string($_REQUEST['agency_booking_notes']);
$agency_id = mysql_real_escape_string($_REQUEST['agency_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql = "
	INSERT INTO
	`booking_notes` (
		`notes`,
		`agency_id`,
		`created_date`,
		`country_id`
	)
	VALUES (
		'{$agency_booking_notes}',
		{$agency_id},
		'".date("Y-m-d H:i:s")."',
		{$country_id}
	)
";
mysql_query($sql);
$bn_id = mysql_insert_id();

// add booking notes log
$bnl_params = array(
	'bn_id' => $bn_id,
	'title' => 'Add Booking Notes',
	'msg' => 'New booking notes created',
	'staff_id' => $staff_id,
	'country_id' => $country_id
);
$crm->addBookingNotesLog($bnl_params);

// redirect back to page
header("location: agency_booking_notes.php?success=1");
?>

