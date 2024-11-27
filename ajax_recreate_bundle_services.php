<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$ajt_id = mysql_real_escape_string($_POST['ajt_id']);

if( $job_id > 0 && $ajt_id > 0 ){

    // get service type
    $ajt_sql = mysql_query("
    SELECT *
    FROM `alarm_job_type`
    WHERE `id` = {$ajt_id}
    ");
    $ajt = mysql_fetch_array($ajt_sql);

    if($ajt['bundle']==1){ // if bundle

        $bundle_ids_arr = explode(",",trim($ajt['bundle_ids'])); // split bundle service type to array

        // clear all bundle services of this job
        mysql_query("
            DELETE 
            FROM `bundle_services`
            WHERE `job_id` = {$job_id}
        ");

        // loop through each service type bundles to bundle services
        foreach($bundle_ids_arr as $service_type){

            // re-insert each service type bundles to bundle services
            mysql_query("
                INSERT INTO
                `bundle_services`(
                    `job_id`,
                    `alarm_job_type_id`
                )
                VALUES(
                    {$job_id},
                    {$service_type}
                )
            ");
            $bundle_id = mysql_insert_id();
            
            // sync alarm
            runSync($job_id,$service_type,$bundle_id);

        }	

        // job log
        mysql_query("
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
                'Recreate Bundle Services',
                '" . date('Y-m-d') . "',
                'Bundle services has been created',
                {$job_id}, 
                '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
                '" . date('H:i') . "'
            )
        ");

    }

}

?>