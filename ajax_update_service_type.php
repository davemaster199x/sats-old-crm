<?php
include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$from_service_type = mysql_real_escape_string($_POST['from_service_type']);
$to_service_type = mysql_real_escape_string($_POST['to_service_type']);

$today = date('Y-m-d H:i:s');
$this_month_start = date("Y-m-01");
$this_month_end = date("Y-m-t");

// check if IC service type is availble on agency
$agency_serv_sql_str = "
SELECT 
    `agency_services_id`,
    `price`
FROM `agency_services` 
WHERE `agency_id` = {$agency_id}
AND `service_id` = {$to_service_type}
";

$agency_serv_sql = mysql_query($agency_serv_sql_str);
if( mysql_num_rows($agency_serv_sql) > 0 ){

    $agency_serv_row = mysql_fetch_array($agency_serv_sql);
    $agency_serv_price = $agency_serv_row['price']; // agency service price       

    if( $to_service_type > 0 ){
        
        // get status changed date          
        $ps_sql_str = "
        SELECT `status_changed` 
        FROM `property_services`
        WHERE `alarm_job_type_id` = {$from_service_type} 
        AND `property_id` = {$property_id}  
        ";        
        $ps_sql = mysql_query($ps_sql_str); 
        $ps_sql_row = mysql_fetch_object($ps_sql);
        $status_changed = date('Y-m-d',strtotime($ps_sql_row->status_changed));

        // if status changed is within the current month its payable
        $is_payable = ( $status_changed >= $this_month_start && $status_changed <= $this_month_end )?1:0;          
        
        // update service
        $service_to = 1; // SATS

        // clear, this will also fix issues on duplicates
        $delete_sql_str = "
        DELETE 
        FROM `property_services`
        WHERE `alarm_job_type_id` = {$from_service_type} 
        AND `property_id` = {$property_id}  
        ";
        mysql_query($delete_sql_str); 

        $delete_sql_str = "
        DELETE 
        FROM `property_services`
        WHERE `alarm_job_type_id` = {$to_service_type} 
        AND `property_id` = {$property_id}  
        ";
        mysql_query($delete_sql_str);

        // insert service type
        $insert_serv_type_sql_str = "
        INSERT INTO
        `property_services` (
            `property_id`,
            `alarm_job_type_id`,
            `service`,
            `price`,
            `status_changed`,
            `is_payable`
        )
        VALUE(
            {$property_id},
            {$to_service_type},
            {$service_to},
            {$agency_serv_price},
            '{$today}',
            {$is_payable}
        )       
        ";  
        mysql_query($insert_serv_type_sql_str); 
        
        
        // from service type
        $ajt_sql = mysql_query("
        SELECT `type`
        FROM `alarm_job_type`
        WHERE `id` = {$from_service_type}
        ");
        $ajt_row = mysql_fetch_array($ajt_sql);
        $service_type_from = $ajt_row['type'];

        // to service type
        $ajt_sql = mysql_query("
        SELECT `type`
        FROM `alarm_job_type`
        WHERE `id` = {$to_service_type}
        ");
        $ajt_row = mysql_fetch_array($ajt_sql);
        $service_type_to = $ajt_row['type'];
         
        // insert property log
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
                {$_SESSION['USER_DETAILS']['StaffID']},
                'Property Service Update',
                'Property Service Updated from <b>{$service_type_from}</b> to <b>{$service_type_to}</b>',
                '".date('Y-m-d H:i:s')."',
                1
            )
        ");

    } 


}


?>