<?php

include('inc/init_for_ajax.php');

function messageCheckAlreadyMarkAsRead($message_id,$staff_id){
	$sql = mysql_query("
		SELECT *
		FROM `message_read_by`
		WHERE `message_id` = {$message_id}
		AND `staff_id` = {$staff_id}
	");
	if(mysql_num_rows($sql)>0){
		return true;
	}else{
		return false;
	}
}

function messageMarkAsRead($message_id,$staff_id){
	mysql_query("
		INSERT INTO 
		`message_read_by`(
			`message_id`,
			`staff_id`,
			`date`
		)
		VALUES(
			{$message_id},
			{$staff_id},
			'".date("Y-m-d H:i:s")."'
		)
	");
}

$mh_id = $_POST['mh_id'];
$msg_id = $_POST['msg_id'];
$staff_id = $_POST['staff_id'];

// set it as read per user
mysql_query("
	UPDATE `message_group`
	SET `read` = 1,
		`read_date` = '".date("Y-m-d H:i:s")."'
	WHERE `message_header_id` = {$mh_id}
	AND `staff_id` = {$staff_id}
	AND `read` IS NULL
");

// update all read by status to read per user
mysql_query("
	UPDATE `message_read_by` AS mrb
	LEFT JOIN `message` AS m ON mrb.`message_id` = m.`message_id`
	LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id` 
	SET mrb.`read` = 1, 
		mrb.`date` = '".date("Y-m-d H:i:s")."'
	WHERE mh.`message_header_id` = {$mh_id}
	AND mrb.`read` IS NULL
	AND mrb.`staff_id` = {$staff_id}
");

?>