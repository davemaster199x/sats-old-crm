<?php

include('server_hardcoded_values.php');

include($_SERVER['DOCUMENT_ROOT'].'inc/init_for_ajax.php');

$country_id = 1;
//$staff_id = $_SESSION['USER_DETAILS']['StaffID'];

$cron_type_id = 4;
$current_week = intval(date('W'));
$current_year = date('Y');

$cl_sql = mysql_query("
	SELECT * 
	FROM cron_log 
	WHERE `type_id` = '{$cron_type_id}' 
	AND `week_no` = '{$current_week}' 
	AND `year` = '{$current_year}'
	AND CAST(`started` AS DATE) = '".date('Y-m-d')."' 
	AND `country_id` = {$country_id}
");

if(mysql_num_rows($cl_sql)==0){
	
	echo $sql_str = "
		SELECT j.`id` AS jid, j.`job_type`, a.`allow_upfront_billing`
		FROM  `jobs` AS j
		LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
		LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
		WHERE a.`country_id` ={$country_id}
		AND j.`status` =  'On Hold'
		AND p.`deleted` =0
		AND a.`status` =  'active'
		AND j.`del_job` = 0
		AND CURDATE( ) >= ( CAST( j.`start_date` AS Date ) - INTERVAL 1 DAY )
	";

	$sql = mysql_query($sql_str);

	echo "<br />";

	while( $row = mysql_fetch_array($sql) ){

		$job_status = 'To Be Booked';
		
		if( $row['allow_upfront_billing'] == 1 && $row['job_type'] == 'Yearly Maintenance' ){ 
			$job_status = 'To Be Invoiced';
		}else{
			$job_status = 'To Be Booked';
		}
		
		// update
		echo $update_sql = "
			UPDATE `jobs`
			SET `status` = '{$job_status}'
			WHERE `id` = {$row['jid']}
		";
		mysql_query($update_sql);
		
		echo "<br />";
		
		// insert job log
		echo $job_log_sql = "
			INSERT INTO 
			job_log (
				`staff_id`, 
				`comments`, 
				`eventdate`, 
				`contact_type`, 
				`job_id`,
				`eventtime`,
				`auto_process`
			) 
			VALUES (
				'', 
				'Job moved from <strong>On Hold</strong> to <strong>{$job_status}</strong>',
				'".date("Y-m-d")."', 
				'Moved',
				'{$row['jid']}',
				'".date("H:i")."',
				1
			)
		";
		mysql_query($job_log_sql);	
		
		echo "<br />";
		
	}

	echo "<br />";



	// insert cron logs
	echo $cron_log_sql = "INSERT INTO cron_log (type_id, week_no, year, started, `finished`, `country_id`) VALUES (" . $cron_type_id . "," . $current_week . ", " . $current_year . ", NOW(), NOW(), {$country_id})";
	mysql_query($cron_log_sql);
	
}



?>