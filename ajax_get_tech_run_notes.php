<?php

include('inc/init_for_ajax.php');

$tr_id = mysql_real_escape_string($_POST['tr_id']);
$job_id = mysql_real_escape_string($_POST['job_id']);

/*$tr_str = "
	SELECT `notes`
	FROM `tech_run`
	WHERE `tech_run_id` = '{$tr_id}'
";*/

$tr_str = "
SELECT tr.`notes` , trr.`highlight_color` , trrc.`hex` 
FROM  `tech_run_rows` AS trr
LEFT JOIN  `tech_run` AS tr ON tr.`tech_run_id` = trr.`tech_run_id` 
LEFT JOIN  `tech_run_row_color` AS trrc ON trr.`highlight_color` = trrc.`tech_run_row_color_id` 
WHERE trr.`row_id_type` =  'job_id'
AND trr.`tech_run_id` ={$tr_id}
AND trr.`row_id` ={$job_id}
AND trr.`status` =1
AND trr.`hidden` =0
";

$tr_sql = mysql_query($tr_str);

$tr = mysql_fetch_array($tr_sql);


//echo "<div style='width:30px; height:30px; background-color:{$tr['hex']}; float:left;'></div>";
echo "<div style='float: left; margin: 8px 0 0 8px;'>{$tr['notes']}</div>";


?>