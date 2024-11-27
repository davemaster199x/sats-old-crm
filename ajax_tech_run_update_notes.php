<?php

include('inc/init_for_ajax.php');

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

// update notes	
// get current note
$tr_notes_sql = mysql_query("
	SELECT `notes`
	FROM `tech_run`
	WHERE `tech_run_id` = {$tr_id}
");
$orig_notes = mysql_fetch_array($tr_notes_sql);

// only update if notes is edited
if( $orig_notes['notes'] != $_POST['notes'] ){
	
	$notes = mysql_real_escape_string($_POST['notes']);
	// update
	mysql_query("
		UPDATE `tech_run`
		SET 
			`notes` = '{$notes}',
			`notes_updated_ts` = '".date('Y-m-d H:i:s')."',
			`notes_updated_by` = {$staff_id}
		WHERE `tech_run_id` = {$tr_id}
	");
	
	$sa_sql = mysql_query("
		SELECT *
		FROM `staff_accounts` AS sa
		WHERE sa.`StaffID` ={$staff_id}
	");
	$sa = mysql_fetch_array($sa_sql); 

	echo "{$sa['FirstName']} ".substr($sa['LastName'],0,1).". <span>".date('d/m/Y H:i')."</span>";
	
	
}



?>