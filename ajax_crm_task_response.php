<?php
include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class;

$ct_id = mysql_real_escape_string($_POST['ct_id']);
$page_link = mysql_real_escape_string($_POST['page_link']);
$describe_issue = mysql_real_escape_string($_POST['describe_issue']);
$response = mysql_real_escape_string($_POST['response']);
$email = mysql_real_escape_string($_POST['email']);
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_fullname = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);


mysql_query("
	UPDATE `crm_tasks`
	SET `response` = '{$response}'
	WHERE `crm_task_id` = {$ct_id}
");


//$to_email = "vaultdweller123@gmail.com";
//$to_email = "vaultdweller123@gmail.com, vanessah@sats.com.au";
//$to_email = "vaultdweller123@gmail.com, vanessah@sats.com.au, danielk@sats.com.au";
//$to_email = "vanessah@sats.com.au, danielk@sats.com.au";
$to_email = $email;

$subject = "CRM Task Response";

echo $email_content = "
<html>
<head>
<title>{$subject}</title>
</head>
<body>
	<h2>A response has been made from your submitted task</h2>
	<table>
		<tr>
			<th style='text-align: right; padding: 5px;'>Page Link: </th>
			<td style='text-align: left; padding: 5px;'>".$_POST['page_link']."</td>
		</tr>
		<tr>
			<th style='text-align: right; padding: 5px;'>Describe Issue: </th>
			<td style='text-align: left; padding: 5px;'>".nl2br($_POST['describe_issue'])."</td>
		</tr>
		<tr>
			<th style='text-align: right; padding: 5px;'>Response: </th>
			<td style='text-align: left; padding: 5px;'>".nl2br($_POST['response'])."</td>
		</tr>
	</table>
</body>
</html>
";

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
//$headers .= 'From: <webmaster@example.com>' . "\r\n";
//$headers .= 'Cc: myboss@example.com' . "\r\n";

mail($to_email,$subject,$email_content,$headers);

//header("Location: crm_tasks.php?success=1");
?>