<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$staff_account_arr = $_POST['staff_account_arr'];
$menu_id = mysql_real_escape_string($_POST['menu_id']);
$denied = mysql_real_escape_string($_POST['denied']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

foreach( $staff_account_arr as $staff_account ){
	
	if( $staff_account != '' ){
		$sql = "
			INSERT INTO 
			`menu_permission_user` (
				`menu`,
				`user`,
				`denied`
			) 
			VALUES(
				{$menu_id},
				{$staff_account},
				{$denied}
			)	
		";
		mysql_query($sql);
	}
	
}

?>