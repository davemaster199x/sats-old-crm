<?php

include('inc/init_for_ajax.php');

$pr_id = mysql_real_escape_string($_POST['pr_id']);

$sql_str = "
	DELETE 
	FROM `postcode_regions_custom_pins`
	WHERE `postcode_region_id` = {$pr_id}
";

mysql_query($sql_str);

$sql_str2 = "
	UPDATE `postcode_regions`
	SET `gm_polygon_points` = NULL
	WHERE `postcode_region_id` = {$pr_id}
";

mysql_query($sql_str2);


?>