<?php

// staff id
$me = $_SESSION['USER_DETAILS']['StaffID'];

/*
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

// get current page
$current_page = basename($_SERVER['PHP_SELF']);

if($current_page=='message_details.php'){
	
	$msg_h_id = $_GET['id'];
	$msg_id = $_GET['msg_id'];
	
	// set it as read
	mysql_query("
		UPDATE `message_header`
		SET `read` = 1,
			`read_date` = '".date("Y-m-d H:i:s")."'
		WHERE `message_header_id` = {$msg_h_id}
		AND `read` IS NULL
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
	
	// update all read by status to read per user
	mysql_query("
		UPDATE `message_read_by` AS mrb
		LEFT JOIN `message` AS m ON mrb.`message_id` = m.`message_id`
		LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id` 
		SET mrb.`read` = 1, 
			mrb.`date` = '".date("Y-m-d H:i:s")."'
		WHERE mh.`message_header_id` = {$msg_h_id}
		AND mrb.`read` IS NULL
		AND mrb.`staff_id` = {$me}
	");
	
}

	
// call check message
$msg_sql = check_new_message($me);	

// show if not empty
if(mysql_num_rows($msg_sql)>0){ 		
?>

	<a id="messagePopUp" href="#fbPopUp" style="display:none;">click</a>

	<div style="display:none">
		<div id="fbPopUp" style="margin: 10px;">
			
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
				
			
			
		</div>
	</div>

<?php	
}
*/
?>


<?php
// KMs pop up 
$kms_sql = mysql_query("
	SELECT *
	FROM `kms` AS k
	LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`
	WHERE v.`StaffID` ={$me}
	ORDER BY k.`kms_updated` DESC
	LIMIT 0, 1
");

$kms = mysql_fetch_array($kms_sql);
$kms_latest_date = $kms['kms_updated'];

if( date('Y-m-d',strtotime($kms_latest_date)) != date('Y-m-d') ){ ?>


	<a id="kmsPopUp" href="#kmsPopUp_div" style="display:none;">click</a>

	<div style="display:none">
		<div id="kmsPopUp_div" style="margin: 10px;">
			
				<h4 style="color:#c8262f;">1. CHECK EMAIL PLEASE</h4>
				<h4>2. Please enter your Vehicle KMs</h4>
			
		</div>
	</div>

<?php
}
?>





<?php
/*
// stock take pop up
$ts_sql = mysql_query("
	SELECT *
	FROM `tech_stock`
	WHERE `staff_id` = {$me}
	ORDER BY `date` DESC
	LIMIT 0, 1
");
$ts = mysql_fetch_array($ts_sql);
$ts_latest_date = $ts['date'];

if( date('D') == 'Mon' && date('Y-m-d',strtotime($ts_latest_date)) != date('Y-m-d')  ){ ?>

	<a id="stocktakePopUp" href="#stocktakePopUp_div" style="display:none;">click</a>

	<div style="display:none">
		<div id="stocktakePopUp_div" style="margin: 10px;">
			
				<h4>Your stocktake is due today</h4>
			
		</div>
	</div>

<?php
}
*/
?>


