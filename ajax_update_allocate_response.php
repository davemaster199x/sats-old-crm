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
$response = mysql_real_escape_string($_POST['response']);
$allocated_by = mysql_real_escape_string($_POST['allocated_by']);
$country_id = $_SESSION['country_default'];

// update status = escalate
$sql_str = "
	UPDATE `jobs`
	SET `allocate_response` = '{$response}'
	WHERE `id` = {$job_id}
";

mysql_query($sql_str);


// set notification
$jparams = array('country_id'=>$country_id);
$gs_sql = $crm->getGlobalSettings($jparams);
$gs = mysql_fetch_array($gs_sql);

$gs_allocate_personnel = $crm->formatStaffName($gs['FirstName'],$gs['LastName']);

$notf_msg = "{$gs_allocate_personnel} has responded to <a href=\"/allocate.php\">Allocate</a> job <a href=\"/view_job_details.php?id={$job_id}\"> #{$job_id}</a>";

$notf_type = 1; // General Notifications
$jparams = array(
	'notf_type'=> $notf_type,
	'staff_id'=> $allocated_by,
	'country_id'=> $country_id,
	'notf_msg'=> $notf_msg
);
$crm->insertNewNotification($jparams);

// pusher notification
$data['notif_type'] = $notf_type;
$ch = "ch".$allocated_by;
$ev = "ev01";
$out = $pusher->trigger($ch, $ev, $data);

?>