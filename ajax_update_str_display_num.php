<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$display_num = ($_POST['display_num']!="")?"'".mysql_real_escape_string($_POST['display_num'])."'":'NULL';

$sql = "
	UPDATE `tech_run`
	SET `display_num` = {$display_num}
	WHERE `tech_run_id` = {$tr_id}
";
mysql_query($sql);

?>