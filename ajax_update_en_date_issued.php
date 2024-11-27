<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$en_date_issued = ($_POST['en_date_issued']!="")?"'".$crm->formatDate($_POST['en_date_issued'])."'":'NULL';

$sql_str = "
	UPDATE `jobs` 
	SET
		`en_date_issued` = {$en_date_issued}
	WHERE `id` = {$job_id}
";
mysql_query($sql_str);

?>