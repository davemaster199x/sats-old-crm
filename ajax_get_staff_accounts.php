<?php

include('inc/init_for_ajax.php');

$staff_id = mysql_real_escape_string($_REQUEST['staff_id']);

$user_arr = array();

$user_sql = mysql_query("
	SELECT *
	FROM staff_accounts 
	WHERE `Deleted` = 0
	AND `active` = 1			
	AND `StaffID` = {$staff_id}
");

while( $user = mysql_fetch_array($user_sql) ){
	$user_arr['address'] = $user['address'];
	$user_arr['email'] = $user['Email'];
	$user_arr['fname'] = $user['FirstName'];
	$user_arr['lname'] = $user['LastName'];
	$user_arr['fullname'] = "{$user['FirstName']} ".( ($user['LastName']!="")?strtoupper(substr($user['LastName'],0,1)).'.':'' );
	$user_arr['fullname2'] = "{$user['FirstName']} {$user['LastName']}";
}

echo json_encode($user_arr);

?>