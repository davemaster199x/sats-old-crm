<?php
include('inc/init.php');
$crm = new Sats_Crm_Class;

// data
$connected_service = mysql_real_escape_string($_POST['connected_service']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

// get agency API name
$api_sql = mysql_query("
    SELECT `api_name`
    FROM `agency_api`
    WHERE `agency_api_id` = {$connected_service}
");
$api_row = mysql_fetch_array($api_sql);

$sql_str = "
    INSERT INTO 
    `agency_api_integration` (
        `connected_service`,
        `agency_id`,
        `date_activated`
    )
    VALUES(
        {$connected_service},
        {$agency_id},
        '".date('Y-m-d')."'
    )
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
        '{$api_row['api_name']} API integration added',
        '{$agency_id}',
        '".$staff_id."',
        '".date('Y-m-d H:i:s')."',
        1
    );
");

header("location: /view_agency_details.php?id={$agency_id}&api_integ_add=1");

?>