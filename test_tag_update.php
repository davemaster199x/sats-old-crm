<?php

include('inc/init.php');

$crm = new Sats_Crm_Class;

$test_and_tag_id = mysql_real_escape_string($_POST['test_and_tag_id']);
$tools_id = mysql_real_escape_string($_POST['tools_id']);
$date = mysql_real_escape_string($_POST['date']);
$date2 = $crm->formatDate($date);
$tnt_comp = mysql_real_escape_string($_POST['tnt_comp']);
$comment = mysql_real_escape_string($_POST['comment']);
$inspection_due = mysql_real_escape_string($_POST['inspection_due']);
$inspection_due2 = $crm->formatDate($inspection_due);


$sql = "
UPDATE `test_and_tag` 
SET
	`date` = '{$date2}',
	`tnt_completed` = '{$tnt_comp}',
	`comment` = '{$comment}',
	`inspection_due` = '{$inspection_due2}'
WHERE `test_and_tag_id` = {$test_and_tag_id}
AND `tools_id` = {$tools_id}
";
mysql_query($sql);


header("location: /test_tag_details.php?id={$test_and_tag_id}&tools_id={$tools_id}&update=1");


?>