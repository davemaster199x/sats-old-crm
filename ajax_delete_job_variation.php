<?php

include('inc/init_for_ajax.php');

$jv_id = $_POST['jv_id'];
$job_id = $_POST['job_id'];

if( $jv_id > 0 && $job_id > 0 ){

    // get job variation
    $dv_type = 2; // job
    $jv_sql_str = "
    SELECT 
        jv.`id`,
        jv.`type`,
        jv.`amount`,
        jv.`reason`,

        apvr.`reason` AS apvr_reason,

        dv.`id` AS dv_id,
        dv.`display_on`,

        disp_on.`location`
    FROM `job_variation` AS jv
    LEFT JOIN `agency_price_variation_reason` AS apvr ON jv.`reason` = apvr.`id`
    LEFT JOIN `display_variation` AS dv ON (  jv.`id` = dv.`variation_id` && dv.`type` = $dv_type )
    LEFT JOIN `display_on` AS disp_on ON dv.`display_on` = disp_on.`id`
    WHERE jv.`id` = {$jv_id}
    AND jv.`job_id` = {$job_id}                            
    ";
    $jv_sql = mysql_query($jv_sql_str);    
    $jv_row = mysql_fetch_object($jv_sql);

    $discount_str = ( $jv_row->type == 1 )?'Discount':'Surcharge';
    $display_on_str = ( $jv_row->display_on > 0 )?"on <b>{$jv_row->location}</b>":'<b>nowhere</b>';

    $log_details = "Job <b>{$discount_str}</b> of <b>\$".number_format($jv_row->amount, 2)."</b>, added for <b>{$jv_row->apvr_reason}</b>, displaying {$display_on_str} has been deleted";

    // insert log
    $insert_log_sql_str = "
    INSERT INTO
    `job_log` (
        `contact_type`,
        `eventdate`,
        `comments`,
        `job_id`,
        `staff_id`,
        `eventtime`
    )
    VALUES (
        'Job Variation Update',
        '" . date('Y-m-d') . "',
        '{$log_details}',
        {$job_id},
        '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
        '" . date('H:i') . "'
    )
    ";
    mysql_query($insert_log_sql_str);


    // deactivate job variation
    echo $update_sql_str = "
    UPDATE `job_variation`
    SET `active` = 0
    WHERE `id` = {$jv_id}
    AND `job_id` = {$job_id}
    ";
    // insert log
    mysql_query($update_sql_str);

   

}

?>