<?php

include('inc/init_for_ajax.php');
//include('inc/ws_sms_class.php');

$crm = new Sats_Crm_Class;
// GET BLINK ACCESS TOKEN
//$blink_access_token = $crm->getBlinkAccessToken();

$job_ids = $_POST['job_ids'];
$str_tech = $_POST['str_tech'];
$str_date = jFormatDateToBeDbReady($_POST['str_date']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$country_id = CURRENT_COUNTRY;
$sent_by = $staff_id;
$today = date("Y-m-d");
$today_full = date("Y-m-d H:i:s");
$en_time_arr = $_POST['en_time_arr'];


foreach ($job_ids AS $index => $job_id) {

    if( $job_id > 0 ){

        // clear arrays
        $email_job_details = array();
        $entrynotice_toemails = array();
        $tenants_emails = array();
        $tenant_mob_arr = array();
        $tent_full_mob_num = array();
        $agency_emails_arr = [];
        $en_bcc_emails = [];
        $tenant_email_empty = false;
        

        unset($email_job_details);
        unset($entrynotice_toemails);
        unset($tenants_emails);
        unset($tenant_mob_arr);
        unset($tent_full_mob_num);
        unset($agency_emails_arr);
        unset($en_bcc_emails);

        $booked_by = $staff_id;
        $booked_with = 'Agent';
        
        $en_time = $en_time_arr[$index];
        $en_date = $str_date;               

        // get job details, needs to use another clean query because the old query is weird
        $job_sql = mysql_query("
            SELECT *, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`postcode` AS p_postcode, j.`service` AS jservice, j.`date` AS jdate, a.`agency_id`
            FROM `jobs` AS j 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            WHERE j.`id` = {$job_id}
        ");
        $job = mysql_fetch_array($job_sql);

        $property_id = $job['property_id'];
        $agency_id = $job['agency_id'];
        $serv_name = getServiceFullName($job['jservice']);
        $paddress = "{$job['p_address_1']} {$job['p_address_2']} {$job['p_address_3']}";        
        $agency_name = $job['agency_name'];


        // tenant mobiles
        // get phone prefix
        $prefix = $job['phone_prefix'];
        // get SMS provider
        $sms_provider = SMS_PROVIDER;

        // get tenants
        $tenant_name_arr = [];

        $pt_params = array(
            'property_id' => $property_id,
            'active' => 1
        );
        $pt_sql = $crm->getNewTenantsData($pt_params);

        while ($pt_row = mysql_fetch_array($pt_sql)) {

            // tenants email
            $tenant_email = trim($pt_row["tenant_email"]);
            if (filter_var(trim($tenant_email), FILTER_VALIDATE_EMAIL)) {
                $entrynotice_toemails[] = $tenant_email;
                $tenants_emails[] = $tenant_email;
            }

            // tenant mobile 
            $ten_mob = trim($pt_row["tenant_mobile"]);
            if ($ten_mob != '') {
                $trimmed_mob = str_replace(' ', '', $ten_mob);
                // reformat number
                $remove_zero = substr($trimmed_mob, 1);
                $mob = $prefix . $remove_zero;

                $tenant_mob_arr[] = "{$mob}{$sms_provider}";
                $tent_full_mob_num[] = $mob;

                // tenant name
                $tenant_name_arr[] = $pt_row['tenant_firstname'];
            }
        }

        $tenant_mobiles = null;
        if( count($tenant_mob_arr) > 0 ){
            $tenant_mobiles = implode(",", $tenant_mob_arr);
        }
    
                
        // get PM
        $pm_id = $job['pm_id_new']; // pm id   
        $pm_email = null;
        $pm_sql = mysql_query("
            SELECT `email`
            FROM `agency_user_accounts`
            WHERE `agency_user_account_id` = {$pm_id}    
            AND `agency_id` = {$agency_id}    
        ");
        if (mysql_num_rows($pm_sql) > 0) {

            // sanitize email            
            $pm_row = mysql_fetch_array($pm_sql);                      
            if (filter_var(trim($pm_row['email']), FILTER_VALIDATE_EMAIL)) {
                $pm_email = $pm_row['email'];                                 
            }

        }    

    
        // agency email
        $agency_emails_imp = explode("\n", trim($job['agency_emails']));
        foreach ($agency_emails_imp as $agency_email) {            
            if (filter_var(trim($agency_email), FILTER_VALIDATE_EMAIL)) {                
                $agency_emails_arr[] = $agency_email;                
            }
        }

        if( $job['en_to_pm'] == 1 ){ // send to PM - YEs

            // PM exist, only send to PM
            if( $pm_email != '' ){ 

                $en_bcc_emails[] = $pm_email;

            }else{ // PM doesnt exist, send to agency
                
                if( count($agency_emails_arr) > 0 ){
                    $en_bcc_emails = $agency_emails_arr;   
                }
                                
            }

        }else{ // send to PM - NO

            if ($job['send_en_to_agency'] == 1) {
                if( count($agency_emails_arr) > 0 ){
                    $en_bcc_emails = $agency_emails_arr;   
                }
            }

        }   
        
        $proceed_en_operation = true; // defaul to run EN
        if( $country_id == 2 && count($tenants_emails) == 0 ){ // on NZ dont run EN if no tenant emails
            $proceed_en_operation = false;
        }

        if( $proceed_en_operation == true ){ // proceed EN

            // update job, this update needs to happen before the EN's are sent
            mysql_query("
            UPDATE `jobs`
            SET 
                `assigned_tech` = {$str_tech},
                `date` = '{$en_date}',
                `time_of_day` = '{$en_time}',
                `job_entry_notice` = 1,
                `key_access_required` = 1,
                `key_access_details` = 'Entry Notice',
                `tech_notes` = 'EN - Keys',
                `booked_by` = {$booked_by},
                `booked_with` = '{$booked_with}',
                `en_date_issued` = '{$today}'
            WHERE `id` ={$job_id}
            ");

            // EMAIL entry notice, query is from old dev
            # Get Job Details
            $Query = getJobDetails($job_id, true);
            $email_job_details = mysqlSingleRow($Query);            

            // send EMAIL
            $email_sent = false;
            if ( count($entrynotice_toemails) > 0 || count($en_bcc_emails) > 0 ) {

                if (sendEntryNoticeEmail($email_job_details, $entrynotice_toemails, $en_bcc_emails)) {

                    $email_sent = true;

                    if ( count($entrynotice_toemails) > 0 ){

                        // insert logs
                        $job_log_str3 = "
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
                            'Email Entry Notice',
                            '" . date('Y-m-d') . "',
                            'Entry Notice emailed to <strong>Tenants</strong>',
                            {$job_id}, 
                            {$staff_id},
                            '" . date('H:i') . "'
                        )
                        ";
                        mysql_query($job_log_str3);                

                    }

                    if ( $job['send_en_to_agency'] == 1 ) {

                        // insert logs
                        $job_log_str3 = "
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
                                'Email Entry Notice',
                                '" . date('Y-m-d') . "',
                                'Entry Notice emailed to <strong>{$agency_name}</strong>',
                                {$job_id}, 
                                {$staff_id},
                                '" . date('H:i') . "'
                            )
                        ";
                        mysql_query($job_log_str3);
                        
                    }   
                    
                    // insert email sent timestamp
                    mysql_query("
                    UPDATE `jobs`
                    SET `entry_notice_emailed` = '{$today_full}'
                    WHERE `id` = {$job_id}
                    ");

                }
            }


            // SEND SMS       
            if( $email_job_details['job_type']=="IC Upgrade" && $country_id==1 ){
                $sms_type = 47; // Entry Notice (SMS EN) IC UPgrade
            }else{
                $sms_type = 10; // Entry Notice (SMS EN)
            }
            
            $sms_temp_params = array(
                'sms_api_type_id' => $sms_type,
                'job_id' => $job_id
            );        
            $sms_message = $crm->get_parsed_sms_template($sms_temp_params);

            $sms_sent = false;
            foreach ($tent_full_mob_num as $tent_mob) {

                if( $tent_mob != '' ){

                    // send SMS via API
                    $ws_sms = new WS_SMS($country_id, $sms_message, $tent_mob);
                    $sms_res = $ws_sms->sendSMS();
                    $ws_sms->captureSMSdata($sms_res, $job_id, $sms_message, $tent_mob, $sent_by, $sms_type);
                    $sms_sent = true;

                    sleep(1);   

                }                 

            }

            if( count($tent_full_mob_num) > 0 && $sms_sent == true ){

                $tenant_name_imp = implode(', ', $tenant_name_arr);
                // insert logs
                $job_log_str1 = "
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
                        'SMS Entry Notice',
                        '" . date('Y-m-d') . "',
                        'SMS to " . mysql_real_escape_string(trim($tenant_name_imp)) . " <strong>\"" . mysql_real_escape_string(trim($sms_message)) . "\"</strong>', 
                        '{$job_id}',
                        '{$staff_id}',
                        '" . date("H:i") . "'
                    )
                ";
                mysql_query($job_log_str1);

                // insert SMS sent timestamp
                mysql_query("
                UPDATE `jobs`
                SET `sms_sent` = '{$today_full}'
                WHERE `id` ={$job_id}
                ");

            }    
            
            // insert EN Date Issued either email or SMS sent
            if( $email_sent == true || $sms_sent == true ){      
                
                // update job as booked
                mysql_query("
                UPDATE `jobs`
                SET `status` = 'Booked'
                WHERE `id` ={$job_id}
                ");

                // insert logs
                $job_log_str2 = "
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
                        'Entry Notice',
                        '" . date('Y-m-d') . "',
                        'EN Booked via Key Access with <strong>{$booked_with}</strong> for <strong>" . ( $crm->isDateNotEmpty($en_date) ? date("d/m/Y", strtotime($en_date)) : null ) . "</strong> @ <strong>{$en_time}</strong>. Technician <strong>{$tech_name}</strong>', 
                        '{$job_id}',
                        '{$staff_id}',
                        '" . date("H:i") . "'
                    )
                ";
                mysql_query($job_log_str2);

            }else{

                // reset job updates if neither SMS or email EN is sent
                mysql_query("
                UPDATE `jobs`
                SET 
                    `assigned_tech` = NULL,
                    `date` = NULL,
                    `time_of_day` = NULL,
                    `job_entry_notice` = 0,
                    `key_access_required` = 0,
                    `key_access_details` = NULL,
                    `tech_notes` = NULL,
                    `booked_by` = NULL,
                    `booked_with` = NULL,
                    `en_date_issued` = NULL
                WHERE `id` ={$job_id}
                ");

            }
        

        }        

    }

}
?>