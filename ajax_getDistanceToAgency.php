<?php 

include('inc/init_for_ajax.php');

// orig url
$job_prop_add = $_POST['job_prop_add'];
$agency_add = $_POST['agency_add'];


// get the distance
$gm_dist = getGoogleMapDistance($job_prop_add,$agency_add);
echo $distance = $gm_dist->rows[0]->elements[0]->distance->text;

sleep(1);
?>