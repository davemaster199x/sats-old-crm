<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$delete_reason = $_POST['delete_reason'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

/* disabled from hard delete to soft delete
mysql_query("
	DELETE 
	FROM `property`
	WHERE `property_id` = {$property_id}
");
*/

	//new delete function > dont hard delete
	$sql1 = "
		UPDATE property
		SET
			`deleted`=1, 
			`reason`= '" . $delete_reason . "',
			`deleted_date` = '" . date('Y-m-d H:i:s') . "',
			`agency_deleted`=0,
			`booking_comments` = 'Deleted as of " . date("d/m/Y") . " - by SATS.',
			`nlm_by_sats_staff` = '{$staff_id}'
		WHERE property_id = {$property_id}
		";
	mysql_query($sql1);

	// deactivate properties_from_other_company
	mysql_query("
	UPDATE `properties_from_other_company`
	SET `active` = 0
	WHERE `property_id` = {$property_id}
	");

?>