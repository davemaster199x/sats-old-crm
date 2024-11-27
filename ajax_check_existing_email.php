<?php

include('inc/init_for_ajax.php');

$orig_email = $_POST['orig_email'];
$email = $_POST['email'];

$oe_str = ($orig_email!="")?" AND `Email` != '{$orig_email}'":"";

$sql = mysql_query("
	SELECT *
	FROM `staff_accounts`
	WHERE `Email` = '{$email}'
	{$oe_str}
");

if(mysql_num_rows($sql)>0){
	echo 1;
}else{
	echo 0;
}

?>