<?php

include('inc/init_for_ajax.php');

$accomodation_id = $_POST['accomodation_id'];

mysql_query("
	DELETE 
	FROM `accomodation`
	WHERE `accomodation_id` = {$accomodation_id}
");

?>