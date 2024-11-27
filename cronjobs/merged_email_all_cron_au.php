<h1>Run Merge Cron</h1>
<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 1;

batchSendInvoicesCertificates($country_id);

?>