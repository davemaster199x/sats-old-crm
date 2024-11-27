<?php

include('inc/init_for_ajax.php');

$property_id = $_POST['property_id'];
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$today_full = date("Y-m-d H:i:s");

if( $property_id > 0 ){

    // get property variation, before it gets deleted 
    $scope = 1; // property
    $pv_sql = mysql_query("
    SELECT * 
    FROM `property_variation` AS pv
    LEFT JOIN `agency_price_variation` AS apv ON pv.`agency_price_variation` = apv.`id`
    WHERE pv.`property_id` = {$property_id}
    AND apv.`scope` = {$scope}
    ");
    $pv_row = mysql_fetch_object($pv_sql);

    /*
    // delete
    $dele_sql_str = "
    DELETE
    FROM `property_variation`
    WHERE `property_id` = {$property_id}
    ";
    mysql_query($dele_sql_str);
    */

    // update to SOFT delete, instruction by ben
    $dele_sql_str = "
    UPDATE `property_variation`
    SET 
        `active` = 0,
        `deleted_ts` = '{$today_full}'
    WHERE `property_id` = {$property_id}
    ";
    mysql_query($dele_sql_str);

    // insert log
    mysql_query("
    INSERT INTO
    `property_event_log`
    (
        `property_id`,
        `staff_id`,
        `event_type`,
        `event_details`,
        `log_date`,
        `hide_delete`
    )
    VALUES(
        {$property_id},
        {$staff_id},
        'Property Variation Update',
        'Variation of <b>\$".$pv_row->amount."</b> removed',
        '".date('Y-m-d H:i:s')."',
        1
    )"
    );

}

?>