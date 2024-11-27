<?php

include('inc/init_for_ajax.php');

$kr_id = $_POST['kr_id'];
$key_completed = ($_POST['key_completed']==1)?0:1;
$agency_staff = mysql_real_escape_string($_POST['agency_staff']);
$number_of_keys = mysql_real_escape_string($_POST['number_of_keys']);
$str = "";

$str .= ($agency_staff!="")?" , `agency_staff` = '{$agency_staff}' ":'';
$str .= ($number_of_keys!="")?" , `number_of_keys` = '{$number_of_keys}' ":'';

if($key_completed==1){
	$date = "'".date("Y-m-d H:i:s")."'";
}else{
	$date = 'NULL';
}

$sql = "
	UPDATE `key_routes`
	SET 
		`completed` = {$key_completed},
		`completed_date` = {$date}
		{$str}	
	WHERE `key_routes_id` = {$kr_id}
";
mysql_query($sql);

echo $key_completed;

?>