<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$staff_id = mysql_real_escape_string($_GET['staff_id']);
$tr_id = mysql_real_escape_string($_GET['tr_id']);
$page = trim(rawurldecode(mysql_real_escape_string($_GET['page'])));
$page_params = trim(rawurldecode(mysql_real_escape_string($_GET['page_params'])));

$user_row = $user->getUserDetails($staff_id);

// store session
$_SESSION['USER_DETAILS']['StaffID'] = $staff_id;
$_SESSION['USER_DETAILS']['FirstName'] = $user_row['FirstName'];
$_SESSION['USER_DETAILS']['LastName'] = $user_row['LastName'];
$_SESSION['USER_DETAILS']['Email'] = $user_row['Email'];
$_SESSION['USER_DETAILS']['ClassID'] = $user_row['ClassID'];
$_SESSION['USER_DETAILS']['TechID'] = $user_row['TechID'];
$_SESSION['USER_DETAILS']['ClassName'] = $user_row['ClassName'];
$_SESSION['USER_DETAILS']['ContactNumber'] = $user_row['ContactNumber'];
$_SESSION['USER_DETAILS']['States'] = $user_row['States'];
$_SESSION['USER_DETAILS']['has_tech_run'] = 1;
$_SESSION['USER_DETAILS']['tr_id'] = $tr_id;


//print_r($_SESSION);
//echo "<br /><br />";


$page_params2 = str_replace(':','=',$page_params); // replace : with =
$page_params3 = str_replace('-','&',$page_params2); // replace - with &

$redirect_page = ( isset($page) && $page != '' )?$page.( ( $_GET['page_params'] != '' )?"?{$page_params3}":null ):'main.php';
header("location: /{$redirect_page}");		

?>