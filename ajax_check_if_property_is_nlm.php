<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];

$sql = mysql_query("
SELECT COUNT(`property_id`) AS p_count
FROM `property`
WHERE `property_id` = {$property_id}
AND `is_nlm` = 1
");

$row = mysql_fetch_object($sql);

echo ( $row->p_count > 0 )?1:0;

?>