<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$agency_id = mysql_real_escape_string($_POST['agency_id']);
$onboarding_id = mysql_real_escape_string($_POST['onboarding_id']);
$is_ticked = mysql_real_escape_string($_POST['is_ticked']);

$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];

$today = date('Y-m-d H:i:s');

// clear
$delete_sql_str = "
DELETE
FROM `agency_onboarding_selected`
WHERE `agency_id` = {$agency_id}
AND `onboarding_id` = {$onboarding_id}
";
mysql_query($delete_sql_str);

//echo "<br /><br />";

if( $is_ticked == 1 ){    

    $insert_sql_str = "
    INSERT INTO 
    `agency_onboarding_selected`(
        `onboarding_id`,
        `agency_id`,
        `updated_date`,
        `updated_by`
    )
    VALUES(
        {$onboarding_id},
        {$agency_id},
        '{$today}',
        {$staff_id}
    )
    ";
    mysql_query($insert_sql_str);

}

$updated_by = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);		
$updated_date = date('d/m/Y H:i',strtotime($today));
$arr = array(
    "updated_by" => $updated_by,
    "updated_date" => $updated_date,
);
echo json_encode($arr);

?>