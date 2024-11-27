<?php

include('inc/init_for_ajax.php');

$job_id = mysql_real_escape_string($_POST['job_id']);
$property_id = mysql_real_escape_string($_POST['property_id']);

$jparams = array(
    'job_id' => $job_id,
    'property_id' => $property_id
);

sync_alarms($jparams);

// job log
mysql_query("
    INSERT INTO 
    `job_log` (
        `contact_type`,
        `eventdate`,
        `comments`,
        `job_id`, 
        `staff_id`,
        `eventtime`
    ) 
    VALUES (
        'Sync Alarms',
        '" . date('Y-m-d') . "',
        'The alarms were synced with the previously completed job',
        {$job_id}, 
        '" . $_SESSION['USER_DETAILS']['StaffID'] . "',
        '" . date('H:i') . "'
    )
");

?>