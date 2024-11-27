<?php

include('inc/init.php');

$cr_id = mysql_real_escape_string($_REQUEST['id']);

$sql = "
	DELETE 
	FROM `credit_requests`
	WHERE `credit_request_id` = {$cr_id}
";
mysql_query($sql);

header("location: credit_requests.php?del_success=1");

?>