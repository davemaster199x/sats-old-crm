<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$start_point = mysql_real_escape_string($_POST['start_point']);
$end_point = mysql_real_escape_string($_POST['end_point']);

techRunUpdateStartEndPoint($tr_id,$start_point,$end_point);

?>