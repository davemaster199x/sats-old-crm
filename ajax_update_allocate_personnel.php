<?php

include('inc/init_for_ajax.php');

$staff_id = mysql_real_escape_string($_POST['staff_id']);
$country_id = $_SESSION['country_default'];
$current_logged_user = $_SESSION['USER_DETAILS']['StaffID'];

$sql = "
	UPDATE `global_settings`
	SET 
		`allocate_personnel` = {$staff_id},	
		`allocate_personnel_updated_by` = {$current_logged_user}
	WHERE `country_id` = {$country_id}
";

mysql_query($sql);

?>