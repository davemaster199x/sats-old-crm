<?php

include('inc/init_for_ajax.php');

// data
$staff_id = mysql_real_escape_string($_POST['staff_id']);
$country_id = mysql_real_escape_string($_POST['country_id']);

// set all country default to null
clearCountryDefault($staff_id);

// get default country
setCountryDefault($staff_id,$country_id);

?>