<?php

include('inc/init_for_ajax.php');

$date = mysql_real_escape_string($_POST['date']);
$date2 = date('Y-m-d',strtotime(str_replace("/","-",$date)));
$tech_id = mysql_real_escape_string($_POST['tech_id']);

$mr_str = "
	SELECT *
	FROM `tech_run`
	WHERE `date` = '{$date2}'
	AND `assigned_tech` = {$tech_id}
";

$mr_sql = mysql_query($mr_str);


if( mysql_num_rows($mr_sql)>0 ){
	echo "1";
}else{
	echo "0";
}

?>