<?php

include('inc/init_for_ajax.php');
require __DIR__ . '/vendor/autoload.php';

$options = array(
	'cluster' => PUSHER_CLUSTER,
	'useTLS' => false
);
$pusher = new Pusher\Pusher(
	PUSHER_KEY,
	PUSHER_SECRET,
	PUSHER_APP_ID,
	$options
);

$crm = new Sats_Crm_Class();

// data
$tech_run_id = mysql_real_escape_string($_POST['tech_run_id']);
$run_type = mysql_real_escape_string($_POST['run_type']);
$status = mysql_real_escape_string($_POST['status']);
$status_txt = ( $status == 1 )?'Activated':'Deactivated';

$tech_name = mysql_real_escape_string($_POST['tech_name']);
$booking_staff = mysql_real_escape_string($_POST['booking_staff']);
$run_type_name = mysql_real_escape_string($_POST['run_type_name']);

$country_id = $_SESSION['country_default'];

$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$today_full = date('Y-m-d H:i:s');

// check if route already set
$str = "
	SELECT *
	FROM `tech_run`
	WHERE `tech_run_id` = {$tech_run_id}
";

$mp_sql = mysql_query($str);

if( mysql_num_rows($mp_sql)>0 ){
	
	$mp = mysql_fetch_array($mp_sql);

	// update
	$sql = "
		UPDATE `tech_run`
		SET `{$run_type}` = {$status}
		WHERE `tech_run_id` = {$mp['tech_run_id']}
		";
	mysql_query($sql);

	// insert tech run logs
	mysql_query("
	INSERT INTO
	`tech_run_logs` (
		`tech_run_id`,
		`description`,
		`created_by`,
		`created`
	)
	VALUES (
		'{$mp['tech_run_id']}',
		'{$run_type_name} {$status_txt}',
		'{$logged_user}',
		'{$today_full}'
	)
	");
	
	if( $run_type=='run_complete' ){
		
		
		$temp_str = "
			UPDATE `jobs` AS j
			RIGHT JOIN `tech_run_rows` AS trr ON j.`id` = trr.`row_id` AND trr.`row_id_type` = 'job_id'
			SET 
				j.`date` = NULL, 
				j.`assigned_tech` = NULL
			WHERE j.`status` = 'To Be Booked'
			AND j.`date` = '".$mp['date']."'
			AND j.`assigned_tech` = ".$mp['assigned_tech']."
			AND trr.`tech_run_id` = ".$mp['tech_run_id']."
			AND j.`door_knock` = 0
		";
		mysql_query($temp_str);
		
	}	
	
	if( $run_type=='ready_to_book' || $run_type=='run_reviewed' || $run_type=='additional_call_over' ){
		
		if( $status==1 ){

			// send notification to call center agent watching the tech run
			$day_txt = date("l",strtotime($mp['date'])); // day
			$notf_msg = "{$tech_name} {$day_txt} <a href=\"set_tech_run.php?tr_id={$tech_run_id}\">status changed to</a> \"{$run_type_name}\"";
			
			$notf_type = 1; // General Notifications
			$jparams = array(
				'notf_type'=> $notf_type,
				'notf_msg'=> $notf_msg,
				'staff_id'=> $booking_staff,
				'country_id'=> $country_id		
			);
			$crm->insertNewNotification($jparams);

			// pusher notification
			$data['notif_type'] = $notf_type;
			$ch = "ch".$booking_staff;
			$ev = "ev01";
			$out = $pusher->trigger($ch, $ev, $data);

		}
		
	}	
	
	
}



?>