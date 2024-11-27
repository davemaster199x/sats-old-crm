<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$job_notes = mysql_real_escape_string($_POST['job_notes']);

$sql_str = "
	UPDATE `jobs` 
	SET
		`tech_comments` = '{$job_notes}'
	WHERE `id` = {$job_id}
";
mysql_query($sql_str);

//header("location: /figures.php?success=1");

?>