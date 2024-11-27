<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$tenant_primary = $_POST['tenant_primary'];
$tenant_firstname = mysql_real_escape_string($_POST['tenant_firstname']);
$tenant_lastname = mysql_real_escape_string($_POST['tenant_lastname']);
$tenant_mobile = mysql_real_escape_string($_POST['tenant_mobile']);
$tenant_landline = mysql_real_escape_string($_POST['tenant_landline']);
$tenant_email = mysql_real_escape_string($_POST['tenant_email']);
$active = $_POST['active'];

$prop_tenants_sql = mysql_query("
SELECT *
FROM `property_tenants`
WHERE `property_id` = {$property_id}
AND `active` = 1
");

$isExist = false;

while( $prop_tenants_row = mysql_fetch_object($prop_tenants_sql) ){

	if( $prop_tenants_row->tenant_firstname == $tenant_firstname ) {
		if ( $prop_tenants_row->tenant_lastname == $tenant_lastname ) {
			$isExist = true;
		}
	}

}

if( $isExist == false ) {

	echo $insert_query_str = "
	INSERT INTO
	`property_tenants`(
		`property_id`,
		`tenant_priority`,
		`tenant_firstname`,
		`tenant_lastname`,
		`tenant_mobile`,
		`tenant_landline`,
		`tenant_email`,
		`active`
	)
	VALUES(
		{$property_id},
		'{$tenant_primary}',
		'{$tenant_firstname}',
		'{$tenant_lastname}',
		'{$tenant_mobile}',
		'{$tenant_landline}',
		'{$tenant_email}',
		1
	)
	";
	mysql_query($insert_query_str);

}

?>