<?php

include('inc/init_for_ajax.php');

$agency_ids = mysql_real_escape_string(trim($_POST['agency_ids']));

$sql_str = "
	UPDATE `crm_settings`
	SET `agency_portal_vip_agencies` = '{$agency_ids}'
	WHERE `country_id` = {$_SESSION['country_default']}
";
mysql_query($sql_str);

header("location: agency_portal_special_agencies.php?update=1");

?>