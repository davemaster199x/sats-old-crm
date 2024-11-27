<?php

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$api_used = mysql_real_escape_string($_POST['api_used']);

/* Disabled > use new generic table instead
if ($api_used == 1) {

	$mr_str = "
		UPDATE `property`
		SET `propertyme_prop_id` = NULL
		WHERE `property_id` = {$property_id}
	";

	$mr_sql = mysql_query($mr_str);

}else if ($api_used == 4) {
	
	$mr_str = "
		UPDATE `property`
		SET `palace_prop_id` = NULL
		WHERE `property_id` = {$property_id}
	";

	$mr_sql = mysql_query($mr_str);

}else if ($api_used == 6) {
	
	$mr_str = "
		UPDATE `api_property_data`
		SET `api_prop_id` = NULL
		WHERE `crm_prop_id` = {$property_id}
	";

	$mr_sql = mysql_query($mr_str);

}
*/

## New > use new generic table
	$mr_str = "
		UPDATE `api_property_data`
		SET `api_prop_id` = NULL, `active` = 0
		WHERE `crm_prop_id` = {$property_id}
	";

	$mr_sql = mysql_query($mr_str);

	// console API, clear CRM property ID to unlink from console properties table
	mysql_query("
	UPDATE `console_properties`
	SET `crm_prop_id` = NULL
	WHERE `crm_prop_id` = {$property_id}
	");


?>