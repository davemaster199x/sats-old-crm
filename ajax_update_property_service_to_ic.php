<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_to_upgrade_to_ic_service = mysql_real_escape_string($_POST['job_to_upgrade_to_ic_service']);
$today = date('Y-m-d H:i:s');

// job data
echo $job_sql_str = "
SELECT 
    j.`service` AS jservice,
    p.`property_id`,
    a.`agency_id`
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE `id` = {$job_to_upgrade_to_ic_service}
";
echo "<br />";

$job_sql = mysql_query($job_sql_str);
$job_row = mysql_fetch_array($job_sql);
$agency_id = $job_row['agency_id'];
$property_id = $job_row['property_id'];
$service_type = $job_row['jservice'];

// get last completed YM
echo $last_com_ym_job_sql_str = "
SELECT j.`date` AS jdate
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE p.`property_id` = {$property_id}
AND j.`job_type` = 'Yearly Maintenance'
AND j.`status` IN('Completed','Merged Certificates') 
ORDER BY j.`date` DESC
LIMIT 1
";
echo "<br />";
$last_com_ym_job_sql_sql = mysql_query($last_com_ym_job_sql_str);

if( mysql_num_rows($last_com_ym_job_sql_sql) > 0 ){

    $last_com_ym_job_sql_row = mysql_fetch_array($last_com_ym_job_sql_sql);
    $last_com_ym_job_date = $last_com_ym_job_sql_row['jdate']; // last complete YM job date

    // determine what IC service it should upgrade to
    $to_ic_service_type = null;
    switch( $service_type ){

        // SA        
        case 2:
            $to_ic_service_type = 12; // SA IC
        break;   
        
        // SASS        
        case 8:
            $to_ic_service_type = 13; // SASS IC
        break;

        // SACWSS        
        case 9:
            $to_ic_service_type = 14; // SACWSS IC
        break;

        // Smoke Alarms & Corded Windows        
        case 19:
            $to_ic_service_type = 20; // Smoke Alarms & Corded Windows (IC)
        break;

    }

    // check if IC service type is availble on agency
    echo $agency_serv_sql_str = "
    SELECT 
        ps.`agency_services_id`,
        ps.`price`,
        ajt.`id` AS ajt_id
    FROM `agency_services` AS ps
    LEFT JOIN `alarm_job_type` AS ajt ON ps.`service_id` = ajt.`id`
    WHERE ps.`agency_id` = {$agency_id}
    AND ps.`service_id` = {$to_ic_service_type}
    ";
    echo "<br />";
    $agency_serv_sql = mysql_query($agency_serv_sql_str);
    if( mysql_num_rows($agency_serv_sql) > 0 ){

        $agency_serv_row = mysql_fetch_array($agency_serv_sql);
        $agency_serv_price = $agency_serv_row['price']; // agency service price     

        $price_var_params = array(
            'service_type' => $agency_serv_row['ajt_id'],
            'property_id' => $property_id
        );
        $price_var_arr = $crm->get_property_price_variation($price_var_params);
        $job_price = $price_var_arr['dynamic_price_total'];

        if( $to_ic_service_type > 0 ){



           // update current job service type to IC service type and 'service to' SATS           
            $service_to = 1; // SATS

            // clear by property ID and service type, this will also fix issues on duplicates
            echo $delete_sql_str = "
            DELETE 
            FROM `property_services`
            WHERE `alarm_job_type_id` = {$to_ic_service_type} 
            AND `property_id` = {$property_id}  
            ";
            echo "<br />";
            mysql_query($delete_sql_str); 

            ## by AL
            $this_month_start = date("Y-m-01");
            $this_month_end = date("Y-m-t");
            $ps_sql_str = "
            SELECT COUNT(`property_services_id`) AS ps_count
            FROM `property_services`
            WHERE `property_id` = {$property_id} 
            AND `service`=1
            AND `is_payable` = 1
            AND `status_changed` BETWEEN '{$this_month_start}' AND '{$this_month_end} 23:59:59'
            ";
            $ps_sql = mysql_query($ps_sql_str);
            $ps_row = mysql_fetch_object($ps_sql);
            $ps_count =  $ps_row->ps_count;

            if($ps_count>0){
                $is_payable = 1;
            }else{
                $is_payable = 0;
            }
            ## by AL end

            // insert IC service type
            echo $insert_serv_type_sql_str = "
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
                {$to_ic_service_type},
                {$service_to},
                {$agency_serv_price},
                '{$today}',
                {$is_payable}
            )       
            ";  
            echo "<br />";
            mysql_query($insert_serv_type_sql_str); 


            

            // update current job service type to 'service to' No response
            $service_to = 2; // No Response    

            // clear by property ID and service type, this will also fix issues on duplicates            
            echo $delete_sql_str = "
            DELETE 
            FROM `property_services`
            WHERE `alarm_job_type_id` = {$service_type} 
            AND `property_id` = {$property_id}  
            ";
            echo "<br />";
            mysql_query($delete_sql_str);             
            
            // re-insert and service to NO response   
           /* ## Al: disable NR service insert
           echo $insert_serv_type_sql_str = "
            INSERT INTO
            `property_services` (
                `property_id`,
                `alarm_job_type_id`,
                `service`,
                `price`,
                `status_changed`
            )
            VALUE(
                {$property_id},
                {$service_type},
                {$service_to},
                {$agency_serv_price},
                '{$today}'
            )       
            ";  
            echo "<br />";
            mysql_query($insert_serv_type_sql_str); 
            */




            // create job - from create job function
            $assigned_tech = 1; // Other Supplier

            echo $create_job_sql = "
            INSERT INTO 
            jobs (
                `job_type`, 
                `property_id`, 
                `status`,
                `service`,            
                `job_price`,	
                `assigned_tech`,	
                `date`		
            ) 
            VALUES (
                'Yearly Maintenance', 
                '{$property_id}', 
                'Completed',
                '{$to_ic_service_type}',            
                '{$job_price}',
                {$assigned_tech},
                '{$last_com_ym_job_date}'            
            )";
            echo "<br />";
            mysql_query($create_job_sql);

            // job id
            $ic_job_id = mysql_insert_id();

            if( $ic_job_id > 0 ){

                // AUTO - UPDATE INVOICE DETAILS
                $crm->updateInvoiceDetails($ic_job_id);

                //  SYNC
                // get alarm job type
                $ajt_sql = mysql_query("
                SELECT *
                FROM `alarm_job_type`
                WHERE `id` = {$to_ic_service_type}
                ");
                $ajt = mysql_fetch_array($ajt_sql);


                // if bundle
                if($ajt['bundle']==1){

                    $b_ids = explode(",",trim($ajt['bundle_ids']));

                    // insert bundles
                    foreach($b_ids as $val){

                        mysql_query("
                            INSERT INTO
                            `bundle_services`(
                                `job_id`,
                                `alarm_job_type_id`
                            )
                            VALUES(
                                {$ic_job_id},
                                {$val}
                            )
                        ");
                                            
                        $bundle_id = mysql_insert_id();
                        $bs_id = $bundle_id;
                        $bs2_sql = getbundleServices($ic_job_id,$bs_id);
                        $bs2 = mysql_fetch_array($bs2_sql);
                        $ajt_id = $bs2['alarm_job_type_id'];
                                                            
                        // sync alarm
                        runSync($ic_job_id,$ajt_id,$bundle_id);

                    }	

                }else{
                    runSync($ic_job_id,$to_ic_service_type);
                }
                
            }            
            

        }
        

    }

}
?>