<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$tools_id = mysql_real_escape_string($_POST['tools_id']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$tnt_comp = mysql_real_escape_string($_POST['tnt_comp']);
$comment = mysql_real_escape_string($_POST['comment']);
$inspection_due = mysql_real_escape_string($_POST['inspection_due']);
$inspection_due2 = $crm->formatDate($inspection_due);


$sql = "
INSERT INTO
`test_and_tag` (
	`tools_id`,
	`date`,
	`tnt_completed`,
	`comment`,
	`inspection_due`
)
VALUES (
	'{$tools_id}',
	'{$date2}',
	'{$tnt_comp}',
	'{$comment}',
	'{$inspection_due2}'
)
";
mysql_query($sql);


header("location: /view_tool_details.php?id={$tools_id}&tnt_success=1");


?>