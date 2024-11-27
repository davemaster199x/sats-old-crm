<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$tools_id = mysql_real_escape_string($_POST['tools_id']);
$lockout_kit_checklist = $_POST['lockout_kit_checklist'];
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$checklist_due = mysql_real_escape_string($_POST['checklist_due']);
$checklist_due2 = $crm->formatDate($checklist_due);

$sql = "
INSERT INTO
`lockout_kit_check` (
	`tools_id`,
	`date`,
	`inspection_due`
)
VALUES (
	'{$tools_id}',
	'{$date2}',
	'{$checklist_due2}'
)
";
mysql_query($sql);

//echo "<br /><br />";

$lockout_kit_check_id = mysql_insert_id();
//$lockout_kit_check_id = 1;

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

header("location: /view_tool_details.php?id={$tools_id}&lockout_kit_success=1");

?>