<?php
include('server_hardcoded_values.php');
include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');
$crm = new Sats_Crm_Class;
$country_id = 1;
$crm->updateSmsCredit($country_id);
?>



