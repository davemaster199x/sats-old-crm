<?php

include('inc/init.php');

$ms_staff = $_POST['ms_staff'];
$date = jFormatDateToBeDbReady($_POST['date']);

foreach( $ms_staff as $staff ){
	$sql = "
		INSERT INTO
		`call_centre_data` (
			`date`,
			`staff_id`,
			`country_id`,
			`date_created`,
			`deleted`,
			`active`
		)
		VALUES(
			'{$date}',
			{$staff},
			'{$_SESSION['country_default']}',
			'".date('Y-m-d H:i:s')."',
			0,
			1
		)
	";
	mysql_query($sql);
}




header("location: /call_centre_report.php?success=1&date={$_POST['date']}");

?>