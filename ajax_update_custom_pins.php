<?php

include('inc/init_for_ajax.php');

$prcp_id = mysql_real_escape_string($_POST['prcp_id']);
$coordinates = mysql_real_escape_string($_POST['coordinates']);

$sql_str = "
	UPDATE `postcode_regions_custom_pins`
	SET `coordinates` = '{$coordinates}'
	WHERE `postcode_regions_custom_pins_id` = {$prcp_id}
";

mysql_query($sql_str);

?>