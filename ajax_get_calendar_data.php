<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$calendar_id = $_POST['calendar_id'];
$staff_id = $_POST['staff_id'];
$logged_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = $_SESSION['country_default'];

if( $calendar_id > 0 ){

    $sql_str = "
    SELECT 
        c.`calendar_id`,
        c.`staff_id`,
        c.`region`,
        c.`date_start`,
        DATE_FORMAT(c.`date_start`, '%d/%m/%Y') AS date_start_dmy,
        c.`date_start_time`,
        c.`date_finish_time`,
        c.`booking_staff`,
        c.`date_finish`,
        DATE_FORMAT(c.`date_finish`, '%d/%m/%Y') AS date_finish_dmy,
        c.`booking_target`,
        c.`details`,
        c.`accomodation`,
        c.`marked_as_leave`,
        s.`firstname`,
        s.`lastname`,
        s.`classid`,
        acco.`accomodation_id`,
        acco.`name`    AS `acco_name`,
        acco.`area`    AS `acco_area`,
        acco.`address` AS `acco_address`,
        acco.`phone`   AS `acco_phone`
    FROM   `calendar` AS c
    INNER JOIN `staff_accounts` AS s ON s.`staffid` = c.`staff_id`    
    LEFT JOIN `accomodation` AS acco ON acco.`accomodation_id` = c.`accomodation_id`
    WHERE  c.`calendar_id` = {$calendar_id} 
    ";
    $sql = mysql_query($sql_str);
    $sql_row = mysql_fetch_array($sql);

    echo json_encode($sql_row);

}

?>