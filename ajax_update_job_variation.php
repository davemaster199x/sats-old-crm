<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$job_id = mysql_real_escape_string($_POST['job_id']);
$job_var_amount = mysql_real_escape_string($_POST['job_var_amount']);
$job_var_type = mysql_real_escape_string($_POST['job_var_type']);
$job_var_type_text = mysql_real_escape_string($_POST['job_var_type_text']);
$job_var_reason = mysql_real_escape_string($_POST['job_var_reason']);
$job_var_reason_text = mysql_real_escape_string($_POST['job_var_reason_text']);
$make_ym = mysql_real_escape_string($_POST['make_ym']);
$display_on = mysql_real_escape_string($_POST['display_on']);

$today = date('Y-m-d');
$today_time = date('H:i');
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];

if( $make_ym == 1 ){ // if make YM only

    if( $job_id > 0 ){

        // get current job
        $job_sql = mysql_query("
        SELECT `job_type`, `job_price`
        FROM `jobs`
        WHERE `id` = {$job_id}
        ");
        $job_row = mysql_fetch_object($job_sql);

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
            'Job Price Update',
            '" . date('Y-m-d') . "',
            'This will be a <b>{$job_row->job_type}</b> make YM. Job price increased from <b>\$".number_format($job_row->job_price,2)."</b> to <b>\$".number_format($job_var_amount,2)."</b> accordingly.',
            {$job_id},
            '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
            '" . date('H:i') . "'
        )
        ");

        // update job price
        mysql_query("
        UPDATE `jobs`
        SET `job_price` = {$job_var_amount}
        WHERE `id` = {$job_id}
        ");            

    }   

}else{ // default insert variation process

    // job variation
    $jv_sql = mysql_query("
    SELECT *
    FROM `job_variation`
    WHERE `job_id` = {$job_id}                    
    AND `active` = 1
    ");    

    if( mysql_num_rows($jv_sql) > 0 ){ // it exist, update

        // deactivate active job variation
        mysql_query("
        UPDATE `job_variation`
        SET `active` = 0
        WHERE `job_id` = {$job_id}  
        AND `active` = 1                  
        ");        

        // insert logs
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
            'Job Price Variation',
            '{$today}',
            '<b>{$job_var_type_text}</b> of \${$job_var_amount} applied to job because <b>{$job_var_reason_text}</b>, this overwrites the variation applied previously.',
            {$job_id},
            {$logged_user},
            '{$today_time}'
        )
        ");
       

    }else{ // insert

        // insert logs
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
            'Job Price Variation',
            '{$today}',
            '<b>{$job_var_type_text}</b> of \${$job_var_amount} applied to job because <b>{$job_var_reason_text}</b>',
            {$job_id},
            {$logged_user},
            '{$today_time}'
        )
        ");                                 

    }

    if( $job_var_amount > 0 ){    
                
        // insert new
        mysql_query("
        INSERT INTO 
        `job_variation`(
            `job_id`,
            `amount`,
            `type`,
            `reason`,
            `date_applied`
        )
        VALUES(
            {$job_id},
            {$job_var_amount},
            {$job_var_type},
            {$job_var_reason},
            '{$today}'
        )                 
        ");

        // job variation ID
        $jv_id = mysql_insert_id();

        if( $display_on > 0 && $jv_id > 0 ){

            // insert new 
            $dv_type = 2; // job

            mysql_query("
            INSERT INTO 
            `display_variation`(
                `variation_id`,
                `type`,
                `display_on`
            )
            VALUES(
                {$jv_id},
                {$dv_type},
                {$display_on}
            )                 
            ");

        }                                 

    }    

}

?>