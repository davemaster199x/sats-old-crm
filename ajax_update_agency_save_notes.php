<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$save_notes_chk = mysql_real_escape_string($_POST['save_notes_chk']);
$escalate_notes = mysql_real_escape_string($_POST['escalate_notes']);

$sql = "
	UPDATE `agency` 
	SET 
		`save_notes` = '{$save_notes_chk}',
		`escalate_notes` = '{$escalate_notes}',
		`escalate_notes_ts` = '".date('Y-m-d H:i:s')."'
	WHERE `agency_id` = {$agency_id}
";
mysql_query($sql);	

?>