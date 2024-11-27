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

$job_id = mysql_real_escape_string($_POST['job_id']);
$allocate_opt_type = mysql_real_escape_string($_POST['allocate_opt_type']);
$allocate_opt_val = mysql_real_escape_string($_POST['allocate_opt_val']);
$country_id = $_SESSION['country_default'];

// get the previous status
$sql_3 = mysql_query("
	SELECT `status`
	FROM `jobs`
	WHERE `id` = {$job_id}
");
$row3 =  mysql_fetch_array($sql_3);

$status = $row3['status'];

// insert logs for Status
if ($status != 'Allocate') {
	$comments = "Job status updated from <strong>{$status}</strong> to <strong>Allocate</strong>";

	mysql_query("
		INSERT INTO
		`job_log` (
			`contact_type`,
			`eventdate`,
			`comments`,
			`job_id`,
			`staff_id`,
			`eventtime`
		)
		VALUES (
			'Job Update',
			'" . date('Y-m-d') . "',
			'{$comments}',
			{$job_id},
			'" . $_SESSION['USER_DETAILS']['StaffID'] . "',
			'" . date('H:i') . "'
		)
	");
}

$today = date('Y-m-d H:i:s');

$sql_str = "
	UPDATE `jobs`
	SET 
		`status` = 'Allocate',
		`{$allocate_opt_type}` = '{$allocate_opt_val}',
		`status_changed_timestamp` = '{$today}', 
		`allocate_timestamp` = '{$today}',
		`allocated_by` = ".$_SESSION['USER_DETAILS']['StaffID']."		
	WHERE `id` = {$job_id}
";

mysql_query($sql_str);

// send notication 
if( $row3['status']!='Allocate' ){ // job allocated
	
	// get allocate deadline
	$deadline = $crm->getAllocateDeadLine($allocate_opt_val,$today);
	$deadline_str = ($deadline!='')?date('d/m/Y @ H:i',strtotime($deadline)):'';
	$notf_msg = "New Job <a href=\"/view_job_details.php?id={$job_id}\"> #{$job_id}</a> Allocated on <a href=\"/allocate.php\">Allocate</a> Page, Deadline on {$deadline_str}";
	// get allocate personnel
	$jparams = array('country_id'=>$country_id);
	$gs_sql = $crm->getGlobalSettings($jparams);
	$gs = mysql_fetch_array($gs_sql);

	$gs_explode = explode(',',$gs['allocate_personnel']);

	foreach($gs_explode as $gs_row){

		// set notification
		$notf_type = 1; // General Notifications
		$jparams = array(
			'notf_type'=> $notf_type,
			'staff_id'=> $gs_row,
			'country_id'=> $country_id,
			'notf_msg'=> $notf_msg
		);
		$crm->insertNewNotification($jparams);

		// pusher notification
		$data['notif_type'] = $notf_type;
		$ch = "ch".$gs_row;
		$ev = "ev01";
		$out = $pusher->trigger($ch, $ev, $data);
		
	}

}else if( $row3['status'] == 'Allocate' && $allocate_opt_type='allocate_notes' ){ // job notes updated
	
	$notf_msg = "Job <a href=\"/view_job_details.php?id={$job_id}\"> #{$job_id}</a> Notes Has been Updated. Please go to <a href=\"/allocate.php\">Allocate</a> Page";
	// get allocate personnel
	$jparams = array('country_id'=>$country_id);
	$gs_sql = $crm->getGlobalSettings($jparams);
	$gs = mysql_fetch_array($gs_sql);

	$gs_explode = explode(',',$gs['allocate_personnel']);

	foreach($gs_explode as $gs_row){

		// set notification
		$notf_type = 1; // General Notifications
		$jparams = array(
			'notf_type'=> $notf_type,
			'staff_id'=> $gs_row,
			'country_id'=> $country_id,
			'notf_msg'=> $notf_msg
		);
		$crm->insertNewNotification($jparams);
		
		// pusher notification
		$data['notif_type'] = $notf_type;
		$ch = "ch".$gs_row;
		$ev = "ev01";
		$out = $pusher->trigger($ch, $ev, $data);

	}

	

}


?>