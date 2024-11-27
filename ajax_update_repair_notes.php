<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$repair_notes = mysql_real_escape_string($_POST['repair_notes']);

echo $mr_str = "
	UPDATE `jobs`
	SET `repair_notes` = '{$repair_notes}'
	WHERE `id` = {$job_id}
";

$mr_sql = mysql_query($mr_str);


?>