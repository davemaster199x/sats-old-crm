<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

// data
$property_id = mysql_real_escape_string($_POST['property_id']);
$reason_they_left = mysql_real_escape_string($_POST['reason_they_left']);
$other_reason = mysql_real_escape_string($_POST['other_reason']);
$nlm_from = mysql_real_escape_string($_POST['nlm_from']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];
$staff_name = $_SESSION['USER_DETAILS']['FirstName']." ".$_SESSION['USER_DETAILS']['LastName'];
$return = [];

echo $crm->NLM_Property($property_id, $reason_they_left, $other_reason, $nlm_from);

?>