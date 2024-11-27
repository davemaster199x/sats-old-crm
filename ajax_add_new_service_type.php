<?php
include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$new_service_type = mysql_real_escape_string($_POST['new_service_type']);
$new_service_type_status = mysql_real_escape_string($_POST['new_service_type_status']);

$today = date('Y-m-d H:i:s');

// check if IC service type is availble on agency
$agency_serv_sql_str = "
SELECT 
    `agency_services_id`,
    `price`
FROM `agency_services` 
WHERE `agency_id` = {$agency_id}
AND `service_id` = {$new_service_type}
";
$agency_serv_sql = mysql_query($agency_serv_sql_str);
if( mysql_num_rows($agency_serv_sql) > 0 ){

    $agency_serv_row = mysql_fetch_array($agency_serv_sql);
    $agency_serv_price = $agency_serv_row['price']; // agency service price       

    if( $new_service_type > 0 ){

        // clear by property ID and service type, this will also fix issues on duplicates
        $delete_sql_str = "
        DELETE 
        FROM `property_services`
        WHERE `alarm_job_type_id` = {$new_service_type} 
        AND `property_id` = {$property_id}  
        ";
        mysql_query($delete_sql_str); 

        // new service type name
        $ajt_sql = mysql_query("
        SELECT `type`
        FROM `alarm_job_type`
        WHERE `id` = {$new_service_type}
        ");
        $ajt_row = mysql_fetch_array($ajt_sql);
        $service_type_new = $ajt_row['type'];

        // ben's mark/unmark payable logic
        $this_month_start = date("Y-m-01");
        $this_month_end = date("Y-m-t");
        $is_payable = 1;

        // check if it has any property services
        $ps_sql_str = "
        SELECT COUNT(`property_services_id`) AS ps_count
        FROM `property_services`
        WHERE `property_id` = {$property_id}         
        ";
        $ps_sql = mysql_query($ps_sql_str);
        $ps_row = mysql_fetch_object($ps_sql);
        $ps_count =  $ps_row->ps_count;

        if( $ps_count == 0 ){
            // is payable state for new service
            $is_payable = 1;
        }else{
            
            // check it has is payable status changed this month
            $ps_sql_str = "
            SELECT COUNT(`property_services_id`) AS ps_count
            FROM `property_services`
            WHERE `property_id` = {$property_id} 
            AND `is_payable` = 1
            AND DATE(`status_changed`) BETWEEN '{$this_month_start}' AND '{$this_month_end}'
            ";
            $ps_sql = mysql_query($ps_sql_str);
            $ps_row = mysql_fetch_object($ps_sql);
            $ps_count =  $ps_row->ps_count;

            if( $ps_count > 0 ){
                // is payable state for new service
                $is_payable = 0;
            }else{
                              
                // loop through existing property services                
                $ps_sql =  mysql_query("
                SELECT `service`, `status_changed` 
                FROM `property_services`                                 
                WHERE `property_id` = {$property_id}    
                ORDER BY `status_changed` DESC
                ");

                $non_sats_count = 0;                
                $sixty_one_days_ago = date("Y-m-d",strtotime("-61 days"));
                $sixt_one_days_older = false;

                while( $ps_row = mysql_fetch_object($ps_sql) ){

                    $status_changed = date('Y-m-d',strtotime($ps_row->status_changed));

                    if( $ps_row->service != 1 ){ // non SATS
                        $non_sats_count++;
                    }

                    if( $status_changed < $sixty_one_days_ago ){
                        $sixt_one_days_older = true;
                    }

                }

                if( mysql_num_rows($ps_sql) == $non_sats_count && $sixt_one_days_older ){

                    // loop through existing property services                
                    $ps_sql =  mysql_query("
                    SELECT 
                        ps.`is_payable`,
                        ajt.`type` AS service_type_name 
                    FROM `property_services` AS ps  
                    LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`              
                    WHERE ps.`property_id` = {$property_id}    
                    ");

                    while( $ps_row = mysql_fetch_object($ps_sql) ){

                        if( $ps_row->is_payable == 1 ){ 

                            // insert logs
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
                                'Property Sales Commission',
                                'Property Service <b>{$ps_row->service_type_name}</b> unmarked <b>payable</b>',
                                '".date('Y-m-d H:i:s')."',
                                1
                            )
                            ");

                        }                    

                    } 
                    
                    // clear is payable
                    mysql_query("
                    UPDATE `property_services`
                    SET `is_payable` = 0
                    WHERE `property_id` = {$property_id}    
                    ");

                    // is payable state for new service
                    $is_payable = 1;

                }else{

                    // is payable state for new service
                    $is_payable = 0;

                }                        

            }            

        }        

        // TO        
        //$service_to = 1; // SATS             
        $service_to = $new_service_type_status;

        // this is a totally new property service so it doesnt have a before is_payable state so should only log for its payable
        if( $is_payable == 1 ){

            // insert logs
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
                'Property Sales Commission',
                'Property Service <b>{$service_type_new}</b> marked <b>payable</b>',
                '".date('Y-m-d H:i:s')."',
                1
            )
            ");

        }        

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
            {$new_service_type},
            {$service_to},
            {$agency_serv_price},
            '{$today}',
            {$is_payable}
        )       
        ";  
        mysql_query($insert_serv_type_sql_str);    
                    
         
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
                'New Property Service',
                'New service added: <b>{$service_type_new}</b>',
                '".date('Y-m-d H:i:s')."',
                1
            )
        ");
        

    } 


}


?>