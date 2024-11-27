<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$tech_notes = mysql_real_escape_string($_POST['tech_notes']);

echo $mr_str = "
	UPDATE `jobs`
	SET `tech_notes` = '{$tech_notes}'
	WHERE `id` = {$job_id}
";

$mr_sql = mysql_query($mr_str);


?>