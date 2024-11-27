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

$me = $_SESSION['USER_DETAILS']['StaffID'];
$to = $_POST['to'];
$msg = mysql_real_escape_string($_POST['msg']);
$now = date("Y-m-d H:i:s");
$country_id = $_SESSION['country_default'];

// insert header
$sql = "
	INSERT INTO 
	`message_header`(
		`from`,
		`date`
	)
	VALUES(
		{$me},
		'{$now}'
	)
"; 
mysql_query($sql);
$msg_h_id = mysql_insert_id();

// insert message
$sql2 = "
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
		'{$now}'
	)
"; 
mysql_query($sql2);
$msg_id = mysql_insert_id();

// insert sender
mysql_query("
	INSERT INTO 
	`message_group`(
		`message_header_id`,
		`staff_id`
	)
	VALUES(
		{$msg_h_id},
		{$me}
	)
");

// inserts empty read by
mysql_query("
	INSERT INTO 
	`message_read_by`(
		`message_id`,
		`staff_id`,
		`read`,
		`date`
	)
	VALUES(
		{$msg_id},
		{$me},
		1,
		'".date("Y-m-d H:i:s")."'
	)
");


foreach($to as $staff){
	// add people
	$sql3 = "
		INSERT INTO 
		`message_group`(
			`message_header_id`,
			`staff_id`
		)
		VALUES(
			{$msg_h_id},
			{$staff}
		)
	"; 
	mysql_query($sql3);
	
	// inserts empty read by
	mysql_query("
		INSERT INTO 
		`message_read_by`(
			`message_id`,
			`staff_id`
		)
		VALUES(
			{$msg_id},
			{$staff}
		)
	");
	
	if( $staff!=$me ){
		
		// set notification
		$jparams = array(
			'staff_id'=> $me
		);
		$sa_sql =$crm->getStaffAccount($jparams);
		$sa = mysql_fetch_array($sa_sql);

		$staff_name = $crm->formatStaffName($sa['FirstName'],$sa['LastName']);
		
		$notf_msg = "New <a href=\"message_details.php?id={$msg_h_id}\">Message</a> From {$staff_name}";
		$jparams = array(
			'staff_id'=> $staff,
			'country_id'=> $country_id,
			'notf_msg'=> $notf_msg
		);
		$crm->insertNewNotification($jparams);

		// $data['staffId'] = $_SESSION['USER_DETAILS']['StaffID'];
		// $data['to'] = $staff;
		// $data['msg'] = mysql_real_escape_string($_POST['msg']);
		// $data['date'] = date("Y-m-d H:i:s");
		$data['notif_type'] = 1;
		$ch = "ch".$staff;
		$ev = "ev01";
		$out = $pusher->trigger($ch, $ev, $data);

	}
	
	
}

// set it as read per user
mysql_query("
	UPDATE `message_group`
	SET `read` = 1,
		`read_date` = '".date("Y-m-d H:i:s")."'
	WHERE `message_header_id` = {$msg_h_id}
	AND `staff_id` = {$me}
	AND `read` IS NULL
");



header("location:/messages.php?success=1");


?>