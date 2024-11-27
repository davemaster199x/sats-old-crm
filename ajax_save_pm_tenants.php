<?php 

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$pm_tenant_id = mysql_real_escape_string($_POST['pm_tenant_id']);
$tenant_firstname = mysql_real_escape_string($_POST['pm_FirstName']);
$tenant_lastname = mysql_real_escape_string($_POST['LastName']);
$tenant_mobile = mysql_real_escape_string($_POST['pm_CellPhone']);
$tenant_landline = mysql_real_escape_string($_POST['pm_HomePhone']);
$tenant_email = mysql_real_escape_string($_POST['pm_Email']);
//$tenant_worknumber = mysql_real_escape_string($_POST['tenant_worknumber']);

echo $sql = "
	INSERT INTO
	`property_tenants`(
		`property_id`,
		`pm_tenant_id`,
		`tenant_firstname`,
		`tenant_lastname`,
		`tenant_mobile`,
		`tenant_landline`,
		`tenant_email`
	)
	VALUES(
		{$property_id},
		'{$pm_tenant_id}',
		'{$tenant_firstname}',
		'{$tenant_lastname}',
		'{$tenant_mobile}',
		'{$tenant_landline}',
		'{$tenant_email}'	
	)
";

mysql_query($sql);

?>
