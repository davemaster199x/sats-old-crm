<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

// data
$api_integration_id = mysql_real_escape_string($_POST['api_integration_id']);
$connected_service = mysql_real_escape_string($_POST['connected_service']);
$status = mysql_real_escape_string($_POST['status']);
$date_activated = ( $crm->isDateNotEmpty($_POST['date_activated']) )?"'".$crm->formatDate($_POST['date_activated'])."'":'NULL';


$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];
$agency_id = mysql_real_escape_string($_POST['agency_id']);


$sql_str = "
    UPDATE `agency_api_integration` 
    SET
        `connected_service` =  {$connected_service},
        `active` =  {$status},
        `date_activated` = {$date_activated}
    WHERE `api_integration_id` = {$api_integration_id}
";
mysql_query($sql_str);


// appended - insert logs
mysql_query("
    INSERT INTO 
    `agency_event_log` 
    (
        `contact_type`,
        `eventdate`,
        `comments`,
        `agency_id`,
        `staff_id`,
        `date_created`,
        `hide_delete`
    ) 
    VALUES (
        'API Integration',
        '".date('Y-m-d')."',
        'API integration updated',
        '{$agency_id}',
        '".$staff_id."',
        '".date('Y-m-d H:i:s')."',
        1
    );
");

?>