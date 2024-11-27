<?

include('init.php');

$query = "SELECT agency_id, password FROM agency WHERE p_converted = 0";

$agencies = mysqlMultiRows($query);

echo "<pre>";

$encrypt = new cast128();
$encrypt->setkey(SALT);

foreach($agencies as $agent)
{
	if($agent['password'] == "password")
	{
		# Generate new password
		$agent['password'] = generatePassword();
	}
	
	# encrypt password
	$agent['encrypted'] = addslashes($encrypt->encrypt($agent['password']));
	
	$query = "UPDATE agency SET password = '{$agent['encrypted']}', p_converted = 1 WHERE agency_id = {$agent['agency_id']} LIMIT 1";
	
	mysql_query($query) or die(mysql_error());

}


?>