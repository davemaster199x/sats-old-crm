<?php

include('inc/init_for_ajax.php');

$ss_id = $_POST['ss_id'];
$job_id = $_POST['job_id'];
$ss_discard_reason = $_POST['ss_discard_reason'];

if( $ss_id > 0 && $job_id > 0 ){
    
    $update_sql_str = "
    UPDATE `safety_switch`    
    SET 
        `discarded` = 1,
        `ss_res_id` = {$ss_discard_reason}         
    WHERE `safety_switch_id` = {$ss_id}
    AND `job_id` = {$job_id}
    ";
    mysql_query($update_sql_str);   

}

?>