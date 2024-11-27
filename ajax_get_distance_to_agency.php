<?php

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);

// get property address  
$prop_sql_str = "
SELECT `address_1`, `address_2`, `address_3`, `state`, `postcode`
FROM `property`
WHERE `property_id` = {$property_id}
";
 
$prop_sql = mysql_query($prop_sql_str);
$prop = mysql_fetch_array($prop_sql);
$start_add = "{$prop['address_1']} {$prop['address_2']} {$prop['address_3']} {$prop['state']} {$prop['postcode']}";

//echo "<br />";

// get agency address 
$agen_sql_str = "
SELECT `address_1`, `address_2`, `address_3`, `state`, `postcode`
FROM `agency`
WHERE `agency_id` = {$agency_id}
";
  
$agen_sql = mysql_query($agen_sql_str);
$agen = mysql_fetch_array($agen_sql);

$end_add = "{$agen['address_1']} {$agen['address_2']} {$agen['address_3']} {$agen['state']} {$agen['postcode']}";

//echo "<br />";

// display result
$gm_dist = getGoogleMapDistance($start_add,$end_add);
echo $distance = $gm_dist->rows[0]->elements[0]->distance->text;

?>