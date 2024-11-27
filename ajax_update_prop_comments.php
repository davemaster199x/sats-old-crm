<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);
$prop_notes = mysql_real_escape_string($_POST['prop_notes']);

$sql_str = "
	UPDATE `property` 
	SET
		`comments` = '{$prop_notes}'
	WHERE `property_id` = {$prop_id}
";
mysql_query($sql_str);

//header("location: /figures.php?success=1");

?>