<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);

// first clear the CW
$prev_job_sql = getPrevCordedWindow($property_id);

// clear CW first, before syncing
$sql = "
DELETE 
FROM `corded_window`
WHERE `job_id` = {$job_id}
";
mysql_query($sql);

SnycCordedWindowData($job_id,$prev_job_sql);

?>