<?php
include('inc/init.php');

$crm = new Sats_Crm_Class;

$page_link = mysql_real_escape_string($_POST['page_link']);
$describe_issue = mysql_real_escape_string($_POST['describe_issue']);
$file = $_FILES['screenshot'];
$logged_user = $_SESSION['USER_DETAILS']['StaffID'];
$logged_user_fullname = $crm->formatStaffName($_SESSION['USER_DETAILS']['FirstName'],$_SESSION['USER_DETAILS']['LastName']);
$error = 0;
$success = 0;
$error_msg = '';

//print_r($file);



if( $file['name'] !='' ){
	
	

	$uploadOk = 1;

	// Check if image file is a actual image or fake image
	$check = getimagesize($file["tmp_name"]);
	if($check !== false) {
		//echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;		
	} else {
		$error_msg .= "File is not an image.<br />";
		$uploadOk = 0;	
		$error = 1;
	}

	// Check file size
	if ($file["size"] > 500000) {
		$error_msg .= "Sorry, your file is too large.<br />";
		$uploadOk = 0;
		$error = 1;
	}

	if ($uploadOk == 0) {
		
		//echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		
	} else{
		
		$upload_params = array(
			'files' => $file,
			'id' => $logged_user,
			'upload_folder' => 'crm_task_screenshots'
		);
		$upload = $crm->masterDynamicUpload($upload_params);
		
	}
	
	
}

if( $error == 0 ){
	
	$sql = "
		INSERT INTO
		`crm_tasks`(
			`page_link`,
			`describe_issue`,
			`requested_by`,
			`screenshot`,
			`date_created`
		)
		VALUES(
			'{$page_link}',
			'{$describe_issue}',
			{$logged_user},
			'{$upload['image_name']}',
			'".date("Y-m-d H:i:s")."'
		)
	";

	mysql_query($sql);

	//$to_email = "vaultdweller123@gmail.com";
	//$to_email = "vaultdweller123@gmail.com, vanessah@sats.com.au";
	//$to_email = "vaultdweller123@gmail.com, vanessah@sats.com.au, danielk@sats.com.au";
	$to_email = "vanessah@sats.com.au, danielk@sats.com.au";

	$subject = "New Crm Task";

	$email_content = "
	<html>
	<head>
	<title>CRM Task</title>
	</head>
	<body>
		<h2>New Crm Task has been submitted</h2>
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
				<th style='text-align: right; padding: 5px;'>Requested By: </th>
				<td style='text-align: left; padding: 5px;'>{$logged_user_fullname}</td>
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
	
	$success = 1;
	
}


header("Location: crm_tasks.php?success={$success}&error_msg={$error_msg}");


?>