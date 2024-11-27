<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$agency_id = mysql_real_escape_string($_POST['agency_id']);
$api_billable = mysql_real_escape_string($_POST['api_billable']);

if( $agency_id > 0 ){

    mysql_query("
    UPDATE `agency`
    SET `api_billable` = {$api_billable}
    WHERE `agency_id` = {$agency_id}
    ");

}

?>