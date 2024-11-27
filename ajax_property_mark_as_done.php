<?php

include('inc/init_for_ajax.php');

$property_id = mysql_real_escape_string($_POST['property_id']);

if( $property_id > 0 ){


    // check if already marked as done
    $prop_sql_str = "
    SELECT `staff_marked_done`
    FROM `property`
    WHERE `property_id` = {$property_id}
    ";
    $prop_sql = mysql_query($prop_sql_str);
    $prop_row = mysql_fetch_array($prop_sql);

    if( $prop_row['staff_marked_done'] == 1 ){ // already mark as done, return a msg

        echo "This Property has already been marked done, you can refresh now or later to see the updated list";

    }else{ // not yet marked as done

        $sql_str = "
            UPDATE `property` 
            SET
                `staff_marked_done` = 1
            WHERE `property_id` = {$property_id}
        ";
        mysql_query($sql_str);

    }

   


}

?>