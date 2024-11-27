<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$staff_id = mysql_real_escape_string($_POST['staff_id']);
$notf_type = mysql_real_escape_string($_POST['notf_type']);
$country_id = $_SESSION['country_default'];

$sql_str = "
	UPDATE `notifications`
	SET 
		`read` = 1
	WHERE `notify_to` = {$staff_id}
	AND `notf_type` = {$notf_type}
	AND `read` = 0
	AND `country_id` = {$country_id}
";

mysql_query($sql_str);


?>