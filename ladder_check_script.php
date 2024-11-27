<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$tools_id = mysql_real_escape_string($_POST['tools_id']);
$ladder_inspection = $_POST['ladder_inspection'];
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$inspection_due = mysql_real_escape_string($_POST['inspection_due']);
$inspection_due2 = $crm->formatDate($inspection_due);

$sql = "
INSERT INTO
`ladder_check` (
	`tools_id`,
	`date`,
	`inspection_due`
)
VALUES (
	'{$tools_id}',
	'{$date2}',
	'{$inspection_due2}'
)
";
mysql_query($sql);

//echo "<br /><br />";

$ladder_check_id = mysql_insert_id();
//$ladder_check_id = 1;

foreach( $ladder_inspection as $index=>$li_id ){
	
	$ladder_opt = $_POST['ladder_opt'.($index+1)];
	
	$sql2 = "
		INSERT INTO 
		`ladder_inspection_selection` (
			`ladder_check_id`,
			`ladder_inspection_id`,
			`value`
		)
		VALUES (
			'{$ladder_check_id }',
			'{$li_id}',
			'{$ladder_opt}'
			
		)
	";
	mysql_query($sql2);
	
	//echo "<br />";
	
}

header("location: /view_tool_details.php?id={$tools_id}&ladder_success=1");

?>