<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;

// data
$agency_api_token_id = mysql_real_escape_string($_POST['agency_api_token_id']);
$api_id = mysql_real_escape_string($_POST['api_id']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

if( $agency_api_token_id > 0 && $api_id > 0 && $agency_id > 0 ){

    // get agency API name
    $api_sql = mysql_query("
    SELECT `api_name`
    FROM `agency_api`
    WHERE `agency_api_id` = {$api_id}
    ");
    $api_row = mysql_fetch_array($api_sql);

    $sql_str = "
        DELETE 
        FROM `agency_api_tokens`     
        WHERE `agency_api_token_id` = {$agency_api_token_id}
        AND `api_id` = {$api_id}
        AND `agency_id` = {$agency_id}
    ";
    mysql_query($sql_str);

    // clear API related markers
    $clear_query = null;
    switch($api_id){
        case 1: // PME
            $clear_query = "
            UPDATE `agency`
            SET `pme_supplier_id` = NULL
            WHERE `agency_id` = {$agency_id}
            ";            
        break; 
        case 4: // PALACE   
            // copied from ajax_delete_agency_api_integration.php, only palace_diary_id to clear as ben's instruction          
            $clear_query = "
            UPDATE `agency`
            SET `palace_diary_id` = NULL
            WHERE `agency_id` = {$agency_id}
            ";            
        break;
    } 
    if( $clear_query != '' ){
        mysql_query($clear_query);  
    }

    // appended - insert logs
    mysql_query("
        INSERT INTO 
        `agency_event_log` 
        (
            `contact_type`,
            `eventdate`,
            `comments`,
            `agency_id`,
            `staff_id`,
            `date_created`,
            `hide_delete`
        ) 
        VALUES (
            'API Token',
            '".date('Y-m-d')."',
            '{$api_row['api_name']} API Token deleted',
            '{$agency_id}',
            '".$staff_id."',
            '".date('Y-m-d H:i:s')."',
            1
        );
    ");

}


?>