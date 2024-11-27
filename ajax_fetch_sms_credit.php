<?php
include('inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;
$country_id = mysql_real_escape_string($_POST['country_id']);
$crm->updateSmsCredit($country_id);
?>