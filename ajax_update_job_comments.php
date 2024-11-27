<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$job_id = mysql_real_escape_string($_REQUEST['job_id']);
$jcomment = mysql_real_escape_string($_REQUEST['jcomment']);

mysql_query("
UPDATE `jobs`
SET `comments` = '{$jcomment}'
WHERE `id` = {$job_id}
");

?>