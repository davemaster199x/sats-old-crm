<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$calendar_id = $_POST['calendar_id'];
$staff_id = $_POST['staff_id'];

$start_date = ( $_POST['start_date'] != '' )?"'".date('Y-m-d',strtotime(str_replace('/','-',$_POST['start_date'])))."'":'NULL';
$start_time = mysql_real_escape_string($_POST['start_time']);
$finish_date = ( $_POST['finish_date'] != '' )?"'".date('Y-m-d',strtotime(str_replace('/','-',$_POST['finish_date'])))."'":'NULL';
$finish_time = mysql_real_escape_string($_POST['finish_time']);
$leave_type = mysql_real_escape_string($_POST['leave_type']);
$marked_as_leave = mysql_real_escape_string($_POST['marked_as_leave']);
$calendar_details = mysql_real_escape_string($_POST['calendar_details']);
$accomodation_radio =  ( is_numeric($_POST['accomodation_radio']) )?mysql_real_escape_string($_POST['accomodation_radio']):'NULL';;
$booking_staff = mysql_real_escape_string($_POST['booking_staff']);
$accomodation_dp = mysql_real_escape_string($_POST['accomodation_dp']);


$logged_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

if( $calendar_id > 0 ){

    echo $sql_str = "
    UPDATE `calendar` 
    SET 
        `date_start` = {$start_date},
        `date_start_time` = '{$start_time}',
        `date_finish` = {$finish_date},
        `date_finish_time` = '{$finish_time}',
        `region` = '{$leave_type}',
        `marked_as_leave` = '{$marked_as_leave}',
        `details` = '{$calendar_details}',
        `accomodation` = {$accomodation_radio},
        `booking_staff` = '{$booking_staff}',
        `accomodation_id` = '{$accomodation_dp}'
    WHERE `calendar_id` = {$calendar_id} 
    ";
    mysql_query($sql_str);


}

?>