<?php 
include('inc/init_for_ajax.php');

// orig url
$orig_add = $_POST['orig_add'];
$dist_add = $_POST['dist_add'];

// get the distance
$gm_dist = getGoogleMapDistance($orig_add,$dist_add);
echo $distance = $gm_dist->rows[0]->elements[0]->distance->text;
?>