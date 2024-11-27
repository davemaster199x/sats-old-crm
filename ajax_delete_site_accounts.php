<?php

include('inc/init_for_ajax.php');

$site_accounts_id = $_POST['site_accounts_id'];

mysql_query("
	DELETE 
	FROM `site_accounts`
	WHERE `site_accounts_id` = {$site_accounts_id}
");

?>