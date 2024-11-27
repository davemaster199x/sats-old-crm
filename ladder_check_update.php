<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$ladder_check_id = mysql_real_escape_string($_POST['ladder_check_id']);
$tools_id = mysql_real_escape_string($_POST['tools_id']);
$ladder_inspection = $_POST['ladder_inspection'];
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$inspection_due = mysql_real_escape_string($_POST['inspection_due']);
$inspection_due2 = $crm->formatDate($inspection_due);

$sql = "
UPDATE `ladder_check` 
SET
	`date` = '{$date2}',
	`inspection_due` = '{$inspection_due2}'
WHERE `ladder_check_id` = {$ladder_check_id}
AND `tools_id` = {$tools_id}
";
mysql_query($sql);

//echo "<br /><br />";


// clear
$sql3 = "
	DELETE 
	FROM `ladder_inspection_selection`
	WHERE `ladder_check_id` = {$ladder_check_id}
";
mysql_query($sql3);
//echo "<br /><br />";

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
			'{$ladder_check_id}',
			'{$li_id}',
			'{$ladder_opt}'
			
		)
	";
	mysql_query($sql2);
	
	//echo "<br />";
	
}

header("location: /ladder_check_details.php?id={$ladder_check_id}&tools_id={$tools_id}&update=1");

?>