<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);

echo $mr_str = "
	UPDATE `tech_run_rows`
	SET 
		`hidden` = 0,
		`dnd_sorted` = 0
	WHERE `tech_run_id` = '{$tr_id}'
	AND `hidden` =  1
";

$mr_sql = mysql_query($mr_str);


?>