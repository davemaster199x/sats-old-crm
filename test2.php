<?php 
$start = microtime(true);
include('inc/init_for_ajax.php'); 
?>
<table>
<tr>
	<td>Job Id</td>
	<td>Job Type</td>
	<td>Job Status</td>
</tr>
<?php
echo $sql_str = "
	SELECT `id`
	FROM `jobs_copy` 
	WHERE `job_type` = 'Yearly Maintenance'
	AND `del_job` = 0
	AND `service` = 2
";
echo "<br /><br />";
$sql = mysql_query($sql_str);
echo "Total Jobs Queried: ".mysql_num_rows($sql);
?>
</table>
<?php
$time_elapsed_secs = microtime(true) - $start;
echo "<p style='text-align:center;'>Execution Time: {$time_elapsed_secs }</p>";
?>