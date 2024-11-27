<?php

include('inc/init.php');

$agency_id = $_POST['agency_id'];
$properties = $_POST['properties'];
$status = $_POST['status'];
$details = $_POST['details'];
$sales_rep = $_POST['sales_rep'];
$insert_agency_log = $_POST['insert_agency_log'];

mysql_query("
	INSERT INTO 
	`sales_snapshot`(
		`agency_id`,
		`properties`,
		`sales_snapshot_status_id`,
		`details`,
		`date`,
		`sales_snapshot_sales_rep_id`,
		`country_id`
	)
	VALUES(
		'".mysql_real_escape_string($agency_id)."',
		'".mysql_real_escape_string($properties)."',
		'".mysql_real_escape_string($status)."',
		'".mysql_real_escape_string($details)."',
		'".date('Y-m-d H:i:s')."',
		'".mysql_real_escape_string($sales_rep)."',
		{$_SESSION['country_default']}
	)
");


if( $insert_agency_log == 1 ){
	
	// insert agency logs
	mysql_query("
		INSERT INTO 
		`agency_event_log` 
		(
			`contact_type`,
			`eventdate`,
			`comments`,
			`agency_id`,
			`staff_id`,
			`date_created`,
			`hide_delete`
		) 
		VALUES (
			'Sales Snapshot',
			'".date('Y-m-d')."',
			'Added New Sales Snapshot',
			'{$agency_id}',
			'".$_SESSION['USER_DETAILS']['StaffID']."',
			'".date('Y-m-d H:i:s')."',
			1
		);
	");
	
}

header("Location: sales_snapshot.php?success=1");

?>
