<?php

include('inc/init_for_ajax.php');

// data
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$show_hidden = mysql_real_escape_string($_POST['show_hidden']);


// update
echo $sql = "
	UPDATE `tech_run`
	SET `show_hidden` = '{$show_hidden}'
	WHERE `tech_run_id` = {$tr_id}
	";
mysql_query($sql);

?>