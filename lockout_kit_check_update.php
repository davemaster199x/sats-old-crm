<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$lockout_kit_check_id = mysql_real_escape_string($_POST['lockout_kit_check_id']);
$tools_id = mysql_real_escape_string($_POST['tools_id']);
$lockout_kit_checklist = $_POST['lockout_kit_checklist'];
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$inspection_due = mysql_real_escape_string($_POST['inspection_due']);
$inspection_due2 = $crm->formatDate($inspection_due);

$sql = "
UPDATE `lockout_kit_check` 
SET
	`date` = '{$date2}',
	`inspection_due` = '{$inspection_due2}'
WHERE `lockout_kit_check_id` = {$lockout_kit_check_id}
AND `tools_id` = {$tools_id}
";
mysql_query($sql);

//echo "<br /><br />";


// clear
$sql3 = "
	DELETE 
	FROM `lockout_kit_checklist_selection`
	WHERE `lockout_kit_check_id` = {$lockout_kit_check_id}
";
mysql_query($sql3);
//echo "<br /><br />";

foreach( $lockout_kit_checklist as $index=>$li_id ){
	
	$lockout_kit_opt = $_POST['lockout_kit_opt'.($index+1)];
	
	$sql2 = "
		INSERT INTO 
		`lockout_kit_checklist_selection` (
			`lockout_kit_check_id`,
			`lockout_kit_checklist_id`,
			`value`
		)
		VALUES (
			'{$lockout_kit_check_id }',
			'{$li_id}',
			'{$lockout_kit_opt}'
			
		)
	";
	mysql_query($sql2);
	
	//echo "<br />";
	
}

echo header("location: /lockout_kit_check_details.php?id={$lockout_kit_check_id}&tools_id={$tools_id}&update=1");

?>