<?php

include('inc/init_for_ajax.php');

$iai_id = $_POST['iai_id'];
$incident_photos_id = $_POST['incident_photos_id'];

// update preferred time
$sql = "
	DELETE
	FROM `incident_photos`
	WHERE `incident_and_injury_id` = ".mysql_real_escape_string($iai_id)."
	AND `incident_photos_id` = ".mysql_real_escape_string($incident_photos_id)."
";

mysql_query($sql);


?>