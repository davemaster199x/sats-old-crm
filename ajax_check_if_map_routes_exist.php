<?php

include('inc/init_for_ajax.php');

$date = mysql_real_escape_string($_POST['date']);
$date2 = date('Y-m-d',strtotime(str_replace("/","-",$date)));
$tech_id = mysql_real_escape_string($_POST['tech_id']);

$mp_str = "
	SELECT *
	FROM `map_routes`
	WHERE `date` = '{$date2}'
	AND `tech_id` = {$tech_id}
";

$mp_sql = mysql_query($mp_str);


if( mysql_num_rows($mp_sql)>0 ){
	echo "1";
}else{
	echo "0";
}

?>