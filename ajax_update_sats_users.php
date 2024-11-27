<?php

include('inc/init_for_ajax.php');

// staff accounts
$staff_id = $_POST['staff_id'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$dob = $_POST['dob'];
$email = $_POST['email'];
$phone_num = $_POST['phone_num'];
$user_class = $_POST['user_class'];
$active = $_POST['active'];
$start_date = date("Y-m-d",strtotime(str_replace("/","-",$_POST['start_date'])));

// states save
if(count($_POST['states'])>0){
	$states = $_POST['states'];
	mysql_query("
		DELETE
		FROM `staff_states`
		WHERE `StaffID` = {$staff_id}
	");
	foreach($states as $val){
		mysql_query("
			INSERT INTO
			`staff_states`(
				`StaffID`,
				`StateID`
			)
			VALUES(
				{$staff_id},
				{$val}
			)
		");
	}
	
}

// encrypt password
$encrypt = new cast128();
$encrypt->setkey(SALT);
$password = utf8_encode($encrypt->encrypt($_POST['password']));

$states = $_POST['states'];

// update staff
mysql_query("
	UPDATE `staff_accounts`
	SET `FirstName` = '".mysql_real_escape_string($fname)."',
		`LastName` = '".mysql_real_escape_string($lname)."',
		`dob` = '".mysql_real_escape_string(date("Y-m-d",strtotime(str_replace("/","-",$dob))))."',
		`Email` = '".mysql_real_escape_string($email)."',
		`ContactNumber` = '".mysql_real_escape_string($phone_num)."',
		`ClassID` = '".mysql_real_escape_string($user_class)."',
		`Password` = '".mysql_real_escape_string($password)."',
		`active` = '".mysql_real_escape_string($active)."',
		`start_date` = '".mysql_real_escape_string($start_date)."'
	WHERE `StaffID` = {$staff_id}
");


?>