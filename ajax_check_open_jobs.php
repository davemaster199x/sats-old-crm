<?php

include('inc/init_for_ajax.php');

$job_id = $_POST['job_id'];

//echo $job_id;

$prop = mysql_query("
SELECT `property_id`
FROM `jobs`
WHERE `id` = {$job_id}
");

$row = mysql_fetch_object($prop);
$property_id = $row->property_id;

//Count open jobs
$jobs = mysql_query("
SELECT COUNT(`id`) AS p_count
FROM `jobs`
WHERE `property_id` = {$property_id}
AND `status` != 'Completed'
AND `status` != 'Cancelled'
AND `del_job` = 0
AND `id` != {$job_id}
");

$row = mysql_fetch_object($jobs);

echo ( $row->p_count > 0 )?1:0;


?>