<?php
include('inc/init.php');
$crm = new Sats_Crm_Class;

// data
$connected_service = mysql_real_escape_string($_POST['connected_service']);
$agency_id = mysql_real_escape_string($_POST['agency_id']);


$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];


$sql_str = "
    SELECT COUNT(`api_integration_id`) AS jcount 
    FROM `agency_api_integration` 
    WHERE `agency_id` = {$agency_id}
    AND `connected_service` = {$connected_service}
";
$sql = mysql_query($sql_str);
$row = mysql_fetch_array($sql);

echo $row['jcount'];

?>