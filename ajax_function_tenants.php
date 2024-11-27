<?php 
include('inc/init_for_ajax.php');
//$sats_query = new Sats_query();

//$sats_query->addJSONResponseHeader();
$tenant_id = mysql_real_escape_string($_POST['tenant_id']);
$today = date('Y-m-d H:i:s');

if(isset($_GET['f']) AND $_GET['f'] == "deleteTenant"){
	
	echo $sql = "
		UPDATE `property_tenants` 
		SET 
			`active` = 0,
			`modifiedDate` = '{$today}'
		WHERE `property_tenant_id` = {$tenant_id}
	"; 
	mysql_query($sql);
	
}


if(isset($_GET['f']) AND $_GET['f'] == "reActivateTenant"){
	
	echo $sql = "
		UPDATE `property_tenants` 
		SET 
			`active` = 1,
			`modifiedDate` = '{$today}'
		WHERE `property_tenant_id` = {$tenant_id}
	"; 
	mysql_query($sql);

}

if(isset($_GET['f']) AND $_GET['f'] == "saveTenant"){  //UPDATE TENANTS ROW
	$tenant_id = intval(filter_input(INPUT_POST, 'tenant_id'));
	//$tenant_firstname = filter_input(INPUT_POST, 'tenant_firstname');
	//$tenant_lastname = filter_input(INPUT_POST, 'tenant_lastname');
	$tenant_firstname = mysql_real_escape_string($_POST['tenant_firstname']);
	$tenant_lastname = mysql_real_escape_string($_POST['tenant_lastname']);
	$tenant_mobile = filter_input(INPUT_POST, 'tenant_mobile');
	$tenant_landline = filter_input(INPUT_POST, 'tenant_landline');
	$tenant_email = filter_input(INPUT_POST, 'tenant_email');
	$tenant_priority = intval(filter_input(INPUT_POST, 'tenant_priority'));
	
	/*
	if(isset($tenant_id)){
		$data['tenant_firstname'] = $tenant_firstname;
		$data['tenant_lastname'] = $tenant_lastname;
		$data['tenant_mobile'] = $tenant_mobile;
		$data['tenant_landline'] = $tenant_landline;
		$data['tenant_email'] = $tenant_email;
		$where['id'] = $tenant_id;
		$result = $sats_query->dbUpdate('property_tenants', $data, $where);
		if($result){
			$response_data = array('success' => true, 'message' => 'Tenant has been updated.');
		} else {
			$response_data = array('success' => false, 'message' => 'Their\'s was an error in updating tenant. Please try again or contact your system administrator.');
		}
	} else {
		$response_data = array('success' => false, 'message' => 'Invalid tenant ID');
	}
	die(json_encode($response_data));
	*/
	
	echo $sql = "
		UPDATE `property_tenants` 
		SET 
			`tenant_firstname` = '{$tenant_firstname}',
			`tenant_lastname` = '{$tenant_lastname}',
			`tenant_mobile` = '{$tenant_mobile}',
			`tenant_landline` = '{$tenant_landline}',
			`tenant_email` = '{$tenant_email}',
			`modifiedDate` = '{$today}',
			`tenant_priority`	= {$tenant_priority}
		WHERE `property_tenant_id` = {$tenant_id}
	";
	mysql_query($sql);
}

/*
$response_data = array('success' => false, 'message' => 'Function not found.');
die(json_encode($response_data));
*/


if(isset($_GET['f']) AND $_GET['f'] == "newTenant"){

	$property_id = intval(filter_input(INPUT_POST, 'property_id'));
	//$tenant_firstname = filter_input(INPUT_POST, 'tenant_firstname');
	//$tenant_lastname = filter_input(INPUT_POST, 'tenant_lastname');
	$tenant_firstname = mysql_real_escape_string($_POST['tenant_firstname']);
	$tenant_lastname = mysql_real_escape_string($_POST['tenant_lastname']);
	$tenant_mobile = filter_input(INPUT_POST, 'tenant_mobile');
	$tenant_landline = filter_input(INPUT_POST, 'tenant_landline');
	$tenant_email = filter_input(INPUT_POST, 'tenant_email');	
	$active = intval(filter_input(INPUT_POST, 'active'));	
	$tenant_priority = intval(filter_input(INPUT_POST, 'tenant_priority'));

	 $sql = "
		INSERT INTO `property_tenants` 
		(`property_id`, `tenant_firstname`, `tenant_lastname`, `tenant_mobile`, `tenant_landline`, `tenant_email`, `active`, `createdDate`,`tenant_priority`) 
		VALUES
		(
			'$property_id',
			'{$tenant_firstname}',
			'{$tenant_lastname}',
			'$tenant_mobile',
			'$tenant_landline',
			'$tenant_email',
			'$active',
			'{$today}',
			'$tenant_priority'
		);
		
	";
	mysql_query($sql) or die(mysql_error());

}