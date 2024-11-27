<?php

include('inc/init_for_ajax.php');

// data
$postcode = mysql_real_escape_string($_POST['postcode']);

/* old table
$sql = mysql_query("
	SELECT * 
	FROM  `postcode_regions`
	WHERE `postcode_region_postcodes` LIKE '%{$postcode}%'
	AND `country_id` = {$_SESSION['country_default']}
");
*/

## new table (by:gherx)
$sql = mysql_query("
	SELECT *, sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id
	FROM  `sub_regions` as sr
	LEFT JOIN `postcode` AS pc ON sr.`sub_region_id` = pc.`sub_region_id`
	WHERE pc.`postcode` = {$postcode}
");

$row = mysql_fetch_array($sql);

echo json_encode($row);

?>