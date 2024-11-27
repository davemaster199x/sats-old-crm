<?php

include('inc/init_for_ajax.php');

// new tenants switch
//$new_tenants = 0;
$new_tenants = NEW_TENANTS;

if( $new_tenants == 1 ){ // NEW TENANTS

	$pt_id = mysql_real_escape_string($_POST['pt_id']);
	$prop_id = mysql_real_escape_string($_POST['prop_id']);
	$prop_field = mysql_real_escape_string($_POST['prop_field']);
	$prop_val = mysql_real_escape_string($_POST['prop_val']);

	$sql = "
		UPDATE `property_tenants`
		SET `{$prop_field}` = '{$prop_val}'
		WHERE `property_tenant_id` = {$pt_id}
		AND `property_id` = {$prop_id}
	";
	mysql_query($sql);

}else{ // OLD TENANTS

	$prop_id = mysql_real_escape_string($_POST['prop_id']);
	$prop_field = mysql_real_escape_string($_POST['prop_field']);
	$prop_val = mysql_real_escape_string($_POST['prop_val']);

	$sql = "
		UPDATE `property`
		SET `{$prop_field}` = '{$prop_val}'
		WHERE `property_id` = {$prop_id}
	";
	mysql_query($sql);
	
}



?>