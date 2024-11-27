<?php

include('inc/init_for_ajax.php');

$calendar_id = mysql_real_escape_string($_POST['calendar_id']);
$calendar_name = mysql_real_escape_string($_POST['calendar_name']);
$accomodation = mysql_real_escape_string($_POST['accomodation']);
$accomodation_id = mysql_real_escape_string($_POST['accomodation_id']);
$booking_staff = mysql_real_escape_string($_POST['booking_staff']);

$accomodation_str = ($accomodation!="")?"'{$accomodation}'":'NULL';
$accomodation_id_str = ($accomodation==1 || $accomodation==2)?"'{$accomodation_id}'":'NULL';

$update_str = "
	UPDATE `calendar`
	SET
		`region` = '{$calendar_name}',
		`accomodation` = {$accomodation_str},
		`accomodation_id` = {$accomodation_id_str},
		`booking_staff` = '{$booking_staff}'
	WHERE `calendar_id` = {$calendar_id}
";

mysql_query($update_str);


?>