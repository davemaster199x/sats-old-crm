<?php
include('inc/init.php');
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
$country_id = $_SESSION['country_default'];

$msg_h_id = $_POST['msg_h_id'];
$me = $_SESSION['USER_DETAILS']['StaffID'];
$msg = mysql_real_escape_string($_POST['msg']);

// new message sent
$sql = "
	INSERT INTO 
	`message`(
		`message_header_id`,
		`author`,
		`message`,
		`date`
	)
	VALUES(
		{$msg_h_id},
		{$me},
		'{$msg}',
		'".date("Y-m-d H:i:s")."'
	)
"; 

mysql_query($sql);

$msg_id = mysql_insert_id();

// new message sent, need to reset all read to null
mysql_query("
	UPDATE `message_group`
	SET `read` = NULL,
		`read_date` = NULL
	WHERE `message_header_id` = {$msg_h_id}
");

// set it as read per user
mysql_query("
	UPDATE `message_group`
	SET `read` = 1,
		`read_date` = '".date("Y-m-d H:i:s")."'
	WHERE `message_header_id` = {$msg_h_id}
	AND `staff_id` = {$me}
	AND `read` IS NULL
");

// get users
$mg_sql = mysql_query("
	SELECT *
	FROM `message_group`
	WHERE `message_header_id` = {$msg_h_id}
");

// inserts empty read by
while($mg = mysql_fetch_array($mg_sql)){
	if($mg['staff_id']==$me){
		
		$str_fiel = "
			,
		`read`,
		`date`
		";
		
		$str_val = "
			,
			1,
			'".date("Y-m-d H:i:s")."'
		";
	}
	mysql_query("
		INSERT INTO 
		`message_read_by`(
			`message_id`,
			`staff_id`
			{$str_fiel}
		)
		VALUES(
			{$msg_id},
			{$mg['staff_id']}
			{$str_val}
		)
	");
	
	if( $mg['staff_id']!=$me ){
		// set notification
		$jparams = array(
			'staff_id'=> $me
		);
		$sa_sql =$crm->getStaffAccount($jparams);
		$sa = mysql_fetch_array($sa_sql);

		$staff_name = $crm->formatStaffName($sa['FirstName'],$sa['LastName']);
		
		$notf_msg = "New <a href=\"message_details.php?id={$msg_h_id}\">Message</a> From {$staff_name}";

		$jparams = array(
			'staff_id'=> $mg['staff_id'],
			'country_id'=> $country_id,
			'notf_msg'=> $notf_msg
		);
		$crm->insertNewNotification($jparams);

		$data['notif_type'] = 1;
		$ch = "ch".$mg['staff_id'];
		$ev = "ev01";
		$out = $pusher->trigger($ch, $ev, $data);
	}
	
	
}

header("location:/message_details.php?id={$msg_h_id}");

?>