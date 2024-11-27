<?php

include('inc/init_for_ajax.php');

$encrypt = new cast128();
$encrypt->setkey(SALT);

$fg_id = $_POST['fg_id'];
$fg_name = $_POST['fg_name'];
$user = $_POST['fg_user'];
if($_POST['fg_pass']!=""){
	$pass = addslashes(utf8_encode($encrypt->encrypt($_POST['fg_pass'])));
	$str = "
	,`username` = '{$user}',
	`password` = '{$pass}'
	";
}


mysql_query("
	UPDATE `franchise_groups`
	SET `name` = '{$fg_name}'
	{$str}
	WHERE `franchise_groups_id` = {$fg_id}
");

?>