<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$no_bulk_match = mysql_real_escape_string($_POST['no_bulk_match']);

if( $agency_id > 0 ){

    mysql_query("
    UPDATE `agency`
    SET `no_bulk_match` = {$no_bulk_match}
    WHERE `agency_id` = {$agency_id}
    ");

}

?>