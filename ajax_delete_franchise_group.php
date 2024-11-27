<?php

include('inc/init_for_ajax.php');

$fg_id = $_POST['fg_id'];

$a_sql = mysql_query("
	SELECT *
	FROM `agency`
	WHERE `franchise_groups_id` ={$fg_id}
");

if(mysql_num_rows($a_sql)==0){
	mysql_query("
		DELETE 
		FROM `franchise_groups`
		WHERE `franchise_groups_id` = {$fg_id}
	");	
	$ret = 0;
}else{
	$ret = 1;
}

echo $ret;

?>