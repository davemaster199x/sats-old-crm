<?php

include('inc/init_for_ajax.php');

$staff_id = mysql_real_escape_string($_POST['staff_id']);
$date = mysql_real_escape_string($_POST['date']);
$col_time_start = mysql_real_escape_string($_POST['col_time_start']);
$col_time_end = mysql_real_escape_string($_POST['col_time_end']);

$jl_sql = mysql_query("
	SELECT DISTINCT jl.`job_id`
	FROM  `job_log` AS jl
	LEFT JOIN jobs AS j ON jl.`job_id` = j.`id` 
	LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
	LEFT JOIN `agency` AS a ON a.`agency_id` = p.`agency_id`
	WHERE jl.`contact_type` =  'Job Booked'
	AND `booked_by` = {$staff_id}
	AND `eventdate` =  '{$date}'
	AND (
		REPLACE( eventtime,  ':',  '.' ) >={$col_time_start}	AND REPLACE( eventtime,  ':',  '.' ) <={$col_time_end}
	)
	AND p.`deleted` =0
	AND a.`status` = 'active'
	AND j.`del_job` = 0
	AND a.`country_id` = {$_SESSION['country_default']}
");

echo mysql_num_rows($jl_sql);

?>