<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$sms_msg_id = mysql_real_escape_string($_POST['sms_msg_id']);

echo getParsedSmsMsg($job_id,$sms_msg_id);

?>