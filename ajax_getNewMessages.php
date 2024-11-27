<?php

include('inc/init_for_ajax.php');

// staff id
$me = mysql_real_escape_string($_POST['staff_id']);

// check new message
function check_new_message($staff_id){
	$sql = "		
		SELECT mh.`message_header_id` , m.`message_id` , m.`message` , sa.`FirstName` , sa.`LastName`
		FROM `message_read_by` AS mrb
		LEFT JOIN `message` AS m ON mrb.`message_id` = m.`message_id`
		LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`
		LEFT JOIN `staff_accounts` AS sa ON m.`author` = sa.`StaffID`
		WHERE mrb.`staff_id` ={$staff_id}
		AND mrb.`read` IS NULL
	";
	return mysql_query($sql);
}

// staff id
//$me = $_SESSION['USER_DETAILS']['StaffID'];	
// call check message
$msg_sql = check_new_message($me);	

// show if not empty
if(mysql_num_rows($msg_sql)>0){ 		

?>
	<h4>New Message!</h4>
	<table>
	<?php
	while($msg = mysql_fetch_array($msg_sql)){ ?>
		<tr>
			<td style="display:none;">
				<input type="hidden" name="mh_id" class="mh_id" value="<?php echo $msg['message_header_id']; ?>" />
				<input type="hidden" name="msg_id" class="msg_id" value="<?php echo $msg['message_id']; ?>" />
			</td>
			<td><?php echo "{$msg['FirstName']} {$msg['LastName']}"; ?>:</td>
			<td><a href="message_details.php?id=<?php echo $msg['message_header_id']; ?>&msg_id=<?php echo $msg['message_id']; ?>"><?php echo $msg['message']; ?></a></td>
		</tr>
	<?php	
	}
	?>							
	</table>
<?php	

}
	

?>