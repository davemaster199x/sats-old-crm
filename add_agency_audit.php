<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$comments = mysql_real_escape_string($_POST['comments']);
$added_by = mysql_real_escape_string($_POST['added_by']);

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_fullname = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);
$error = 0;
$success = 0;
$error_msg = '';

$sql = "
	INSERT INTO
	`agency_audits`(
		`agency_id`,
		`submitted_by`,
		`comments`,
		`date_created`
	)
	VALUES(
		{$agency_id},
		{$added_by},
		'{$comments}',
		'".date("Y-m-d H:i:s")."'
	)
";

mysql_query($sql);


header("Location: agency_audits.php?success={$success}");


?>