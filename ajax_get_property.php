<?php

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);

$p_str = "
	SELECT p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state`, p.`postcode`
	FROM `property` AS p
	LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`  
	WHERE p.`property_id` = {$property_id}
	AND a.`country_id` = {$_SESSION['country_default']}
";

$p_sql = mysql_query($p_str);

if( mysql_num_rows($p_sql)>0 ){
	
	$p = mysql_fetch_array($p_sql);
	echo "<a href='/view_property_details.php?id={$property_id}' style='color: #b4151b;'>{$p['p_address_1']} {$p['p_address_2']}, {$p['p_address_3']} {$p['state']} {$p['postcode']}</a>";
	
}

?>