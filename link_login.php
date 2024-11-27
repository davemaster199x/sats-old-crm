<?php

include('inc/init_for_ajax.php');

$staff_id = mysql_real_escape_string($_REQUEST['staff_id']);
$page = $_REQUEST['page'];

// login data needed, copy pasted from access_check.php
if( $staff_id > 0 ){

	$tmp = $user->getUserDetails($staff_id);

	if( $tmp['StaffID'] > 0 ){

        // session data on login
        $_SESSION['USER_DETAILS']['StaffID'] = $tmp['StaffID']; // most important

		$_SESSION['USER_DETAILS']['FirstName'] = $tmp['FirstName'];
		$_SESSION['USER_DETAILS']['LastName'] = $tmp['LastName'];
		$_SESSION['USER_DETAILS']['Email'] = $tmp['Email'];
		$_SESSION['USER_DETAILS']['ClassID'] = $tmp['ClassID'];
		$_SESSION['USER_DETAILS']['TechID'] = $tmp['TechID'];
		$_SESSION['USER_DETAILS']['ClassName'] = $tmp['ClassName'];
		$_SESSION['USER_DETAILS']['ContactNumber'] = $tmp['ContactNumber'];
		$_SESSION['USER_DETAILS']['States'] = $tmp['States'];		

		// capture login
		$capture_login_sql_str = "
		INSERT INTO 
		`crm_user_logins`(
			`user`,
			`ip`,
			`date_created`
		)
		VALUES(
			{$tmp['StaffID']},
			'{$_SERVER['REMOTE_ADDR']}',
			'".date('Y-m-d H:i:s')."'
		)
		";
		mysql_query($capture_login_sql_str);
		
		# redirect to main
        $redirect_url = "Location: " . URL . "{$page}";
		header($redirect_url);

	}	

}

?>