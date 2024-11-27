<?php

include('inc/init_for_ajax.php');

$encrypt = new cast128();
$encrypt->setkey(SALT);

$add_fg_name = $_POST['add_fg_name'];
$user = $_POST['add_fg_user'];
$pass = addslashes(utf8_encode($encrypt->encrypt($_POST['add_fg_pass'])));


$fg_sql = mysql_query("
	SELECT `username`
	FROM `franchise_groups` 
	WHERE `username` = '{$user}'
	AND `country_id` = {$_SESSION['country_default']}
");
// username must be unique
if(mysql_num_rows($fg_sql)==0){

	mysql_query("
		INSERT INTO 
		`franchise_groups`(
			`name`,
			`username`,
			`password`,
			`country_id`
		)
		VALUES(
			'{$add_fg_name}',
			'{$user}',
			'{$pass}',
			{$_SESSION['country_default']}
		)
	");
	$ret = 3;
}else{
	$ret = -3;
}

echo $ret;

?>