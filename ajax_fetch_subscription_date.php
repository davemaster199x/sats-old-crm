<?php

include('inc/init_for_ajax.php');

$prop_id = mysql_real_escape_string($_POST['prop_id']);

if( $prop_id > 0 ){

    // get subscription date
    $sql = mysql_query("
    SELECT 
        j.`assigned_tech`,
        j.`date` AS jdate
    FROM `jobs` AS j
    WHERE j.`property_id` = {$prop_id}
    AND j.`job_type` = 'Yearly Maintenance'
    AND j.`status` = 'Completed'
    AND j.`del_job` = 0
    ORDER BY j.`date` DESC
    LIMIT 1
    ");    

    if(  mysql_num_rows($sql) > 0 ){

        $row = mysql_fetch_object($sql);

        // encode to json
        $json_arr = array(
            "assigned_tech" => $row->assigned_tech,
            "jdate" => date('d/m/Y',strtotime($row->jdate))
        );
        echo json_encode($json_arr);

    }    

}

?>