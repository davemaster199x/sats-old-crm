<?php
include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);
$non_active_ps_id_arr = $_POST['non_active_ps_id_arr'];
$non_active_service_status_arr = $_POST['non_active_service_status_arr'];
$today = date('Y-m-d H:i:s');

foreach( $non_active_ps_id_arr as $index => $non_active_ps_id ){

    if( $non_active_ps_id > 0 && is_numeric($non_active_service_status_arr[$index]) ){

        // insert service type
        $insert_serv_type_sql_str = "
        UPDATE `property_services` 
        SET `service` = ".mysql_real_escape_string($non_active_service_status_arr[$index])."
        WHERE `property_services_id` = ".mysql_real_escape_string($non_active_ps_id)."   
        AND `property_id` = {$property_id}
        ";          
        mysql_query($insert_serv_type_sql_str); 

    }    

}

?>