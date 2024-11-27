<?php

include('inc/init_for_ajax.php');

$country_id = mysql_real_escape_string($_POST['country_id']);
$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$sql = mysql_query("
	SELECT j.`id` AS jid
	FROM  `jobs` AS j
	LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
	LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
	WHERE a.`country_id` ={$country_id}
	AND j.`status` =  'On Hold'
	AND p.`deleted` =0
	AND a.`status` =  'active'
	AND j.`del_job` = 0
	AND CURDATE( ) >= (
	j.`start_date` - INTERVAL 3 
	DAY
	)
");

while( $row = mysql_fetch_array($sql) ){
	// update
	mysql_query("
		UPDATE `jobs`
		SET `status` = 'To Be Booked'
		WHERE `id` = {$row['jid']}
	");
	// insert job log
	mysql_query("
		INSERT INTO 
		job_log (
			`staff_id`, 
			`comments`, 
			`eventdate`, 
			`contact_type`, 
			`job_id`
		) 
		VALUES (
			'{$staff_id}', 
			'Job moved to To Be Booked @ ".date("H:i")."',
			'".date("Y-m-d")."', 
			'Moved',
			'{$row['jid']}'
		)
	");	
}

?>