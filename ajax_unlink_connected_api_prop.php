<?php

include('inc/init_for_ajax.php');

$agency_id = $_POST['agency_id'];

if( $agency_id > 0 ){

    $update_sql_str = "
    UPDATE `property` 
    SET 
        `propertyme_prop_id` = NULL,
        `palace_prop_id` = NULL
    WHERE `agency_id` = {$agency_id}
    ";
    mysql_query($update_sql_str);

}

?>