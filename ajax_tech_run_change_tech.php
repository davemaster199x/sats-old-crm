<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$trr_id_arr = $_POST['trr_id_arr'];
$trr_id_imp = implode(',',$trr_id_arr);
$tr_id = mysql_real_escape_string($_POST['tr_id']);
$change_to_tech_id = mysql_real_escape_string($_POST['change_to_tech_id']);
$from_tech_name = mysql_real_escape_string($_POST['from_tech_name']);
$change_to_tech_name = mysql_real_escape_string($_POST['change_to_tech_name']);

if( $change_to_tech_id > 0 && count($trr_id_arr) > 0 ){

    // get tech run rows
    echo $trr_sql_str = "
        SELECT 
            trr.`tech_run_rows_id`, 
            trr.`row_id_type`,
            trr.`row_id`,
            
            j.`status` AS j_status
        FROM `tech_run_rows` AS trr
        LEFT JOIN  `jobs` AS j ON ( trr.`row_id` = j.`id`  AND trr.`row_id_type` = 'job_id' )
        WHERE trr.`tech_run_rows_id` IN({$trr_id_imp}) 
    ";

    $trr_sql = mysql_query($trr_sql_str);

    while( $trr_row = mysql_fetch_array($trr_sql) ){

        // update job, move only booked jobs
        if( $trr_row['row_id_type'] == 'job_id' && $trr_row['j_status'] == 'Booked' ){

            $job_id = $trr_row['row_id'];

            if( $job_id > 0 ){

                // update tech
                mysql_query("
                    UPDATE `jobs`
                    SET `assigned_tech` = {$change_to_tech_id}
                    WHERE `id` = {$job_id}
                ");

                // insert job log
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
                    'Tech Update',
                    '".date('Y-m-d')."',
                    'Tech changed from {$from_tech_name} to {$change_to_tech_name}',
                    {$job_id}, 
                    '".$_SESSION['USER_DETAILS']['StaffID']."',
                    '".date('H:i')."'
                )
                ");
                
                // remove tech run row
                mysql_query("
                DELETE 
                FROM `tech_run_rows`
                WHERE `tech_run_rows_id` = {$trr_row['tech_run_rows_id']}
                ");

            }           

        }
        

    }
    

}

?>